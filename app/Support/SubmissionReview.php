<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubmissionReview
{
    public const APPROVAL_MESSAGES = [
        'periods.required' => 'Centang minimal satu bulan yang diterima. Kalau tidak ada, pakai tombol Tolak.',
        'reject_reason.required' => 'Tulis alasan kenapa sebagian bulan tidak diterima.',
    ];

    public const REJECTION_MESSAGES = [
        'reject_reason.required' => 'Tulis alasan penolakan supaya warga tahu yang harus diperbaiki.',
    ];

    /**
     * @param  array<int, mixed>  $requested
     * @return array<int, string>
     */
    public function acceptedPeriods(PaymentSubmission $submission, array $requested): array
    {
        return $submission->periods === ['']
            ? ['']
            : array_values(array_intersect($submission->periods, $requested));
    }

    /**
     * @param  array<int, string>  $accepted
     * @return array<string, array<int, mixed>>
     */
    public function approvalRules(PaymentSubmission $submission, array $accepted): array
    {
        return [
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'periods' => [Rule::requiredIf($submission->periods !== ['']), 'array'],
            'periods.*' => [Rule::in($submission->periods)],
            'reject_reason' => [Rule::requiredIf($this->isPartial($submission, $accepted)), 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<int, string>  $accepted
     */
    public function isPartial(PaymentSubmission $submission, array $accepted): bool
    {
        return count($accepted) < count($submission->periods);
    }

    /**
     * Returns null when the submission was already processed by someone else.
     *
     * @param  array<int, string>  $accepted
     * @return array{created: array<int, string>, skipped: array<int, string>}|null
     */
    public function approve(PaymentSubmission $submission, array $accepted, string $paidOn, ?string $rejectReason, User $reviewer): ?array
    {
        $isPartial = $this->isPartial($submission, $accepted);

        return DB::transaction(function () use ($submission, $accepted, $paidOn, $rejectReason, $reviewer, $isPartial) {
            $submission = PaymentSubmission::lockForUpdate()->findOrFail($submission->id);
            if (! $submission->isPending()) {
                return null;
            }

            $created = [];
            $skipped = [];
            foreach ($accepted as $period) {
                $payment = Payment::firstOrCreate(
                    ['household_id' => $submission->household_id, 'dues_type_id' => $submission->dues_type_id, 'period' => $period],
                    [
                        'amount' => $submission->unit_amount,
                        'paid_on' => $paidOn,
                        'method' => 'transfer',
                        'note' => trim("Bukti online #{$submission->code} ".($submission->note ?? '')),
                        'recorded_by' => $reviewer->id,
                        'payment_submission_id' => $submission->id,
                    ],
                );
                if ($payment->wasRecentlyCreated) {
                    $created[] = $payment->periodLabel();
                } else {
                    $skipped[] = $payment->periodLabel();
                }
            }

            $submission->update([
                'status' => PaymentSubmission::APPROVED,
                'approved_periods' => $accepted,
                'reject_reason' => $isPartial ? $rejectReason : null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return ['created' => $created, 'skipped' => $skipped];
        });
    }

    /**
     * Returns false when the submission was no longer pending.
     */
    public function reject(PaymentSubmission $submission, string $reason, User $reviewer): bool
    {
        return $this->whileLocked($submission, PaymentSubmission::PENDING, function (PaymentSubmission $locked) use ($reason, $reviewer) {
            $locked->update([
                'status' => PaymentSubmission::REJECTED,
                'reject_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return true;
        }) ?? false;
    }

    /**
     * Undoes an approval and returns how many payment records were removed, or null when it was no longer approved.
     */
    public function cancel(PaymentSubmission $submission): ?int
    {
        return $this->whileLocked($submission, PaymentSubmission::APPROVED, function (PaymentSubmission $locked) {
            $removed = Payment::where('payment_submission_id', $locked->id)->delete();
            $locked->update(['status' => PaymentSubmission::PENDING, 'approved_periods' => null, 'reject_reason' => null, 'reviewed_by' => null, 'reviewed_at' => null]);

            return $removed;
        });
    }

    /**
     * Returns why the submission cannot go back to pending, or null when it did.
     */
    public function reopen(PaymentSubmission $submission): ?string
    {
        return $this->whileLocked($submission, PaymentSubmission::REJECTED, function (PaymentSubmission $locked) {
            $paid = Payment::where('household_id', $locked->household_id)
                ->where('dues_type_id', $locked->dues_type_id)
                ->whereIn('period', $locked->periods)
                ->pluck('period')
                ->all();
            $pending = array_intersect($locked->periods, PaymentSubmission::pendingPeriods($locked->household_id, $locked->dues_type_id));

            // Residents usually resend after a rejection, so reopening blindly would leave the same months pending twice.
            if ($paid || $pending) {
                $what = $paid ? 'sudah tercatat lunas' : 'sedang menunggu di pengajuan lain';

                return "Tidak bisa dibuka lagi: {$locked->periodsLabel(array_values(array_merge($paid, $pending)))} {$what}.";
            }

            $locked->update(['status' => PaymentSubmission::PENDING, 'reject_reason' => null, 'reviewed_by' => null, 'reviewed_at' => null]);

            return null;
        }, "Pengajuan #{$submission->code} sudah tidak dalam status ditolak.");
    }

    /**
     * @template T
     *
     * @param  callable(PaymentSubmission): T  $callback
     * @return T|null
     */
    private function whileLocked(PaymentSubmission $submission, string $expectedStatus, callable $callback, mixed $whenStale = null): mixed
    {
        return DB::transaction(function () use ($submission, $expectedStatus, $callback, $whenStale) {
            $locked = PaymentSubmission::lockForUpdate()->findOrFail($submission->id);

            return $locked->status === $expectedStatus ? $callback($locked) : $whenStale;
        });
    }
}
