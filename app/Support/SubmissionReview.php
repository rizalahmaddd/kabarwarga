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

    public function reject(PaymentSubmission $submission, string $reason, User $reviewer): void
    {
        $submission->update([
            'status' => PaymentSubmission::REJECTED,
            'reject_reason' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Undoes an approval and returns how many payment records were removed.
     */
    public function cancel(PaymentSubmission $submission): int
    {
        return DB::transaction(function () use ($submission) {
            $removed = Payment::where('payment_submission_id', $submission->id)->delete();
            $submission->update(['status' => PaymentSubmission::PENDING, 'approved_periods' => null, 'reject_reason' => null, 'reviewed_by' => null, 'reviewed_at' => null]);

            return $removed;
        });
    }

    public function reopen(PaymentSubmission $submission): void
    {
        $submission->update(['status' => PaymentSubmission::PENDING, 'reject_reason' => null, 'reviewed_by' => null, 'reviewed_at' => null]);
    }
}
