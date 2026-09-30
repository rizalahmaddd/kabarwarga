<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Support\SubmissionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists($request->query('status'), PaymentSubmission::STATUSES)
            ? $request->query('status')
            : PaymentSubmission::PENDING;

        $submissions = PaymentSubmission::with(['household', 'duesType', 'bankAccount', 'reviewer'])
            ->where('status', $status)
            ->when($status === PaymentSubmission::PENDING, fn ($q) => $q->oldest(), fn ($q) => $q->latest('reviewed_at')->latest('id'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.submissions.index', [
            'status' => $status,
            'submissions' => $submissions,
            'counts' => PaymentSubmission::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function proof(PaymentSubmission $submission)
    {
        abort_unless(Storage::disk('local')->exists($submission->proof_path), 404);

        return Storage::disk('local')->response($submission->proof_path);
    }

    public function approve(Request $request, PaymentSubmission $submission, SubmissionReview $review)
    {
        if (! $submission->isPending()) {
            return back()->with('warning', "Pengajuan #{$submission->code} sudah diproses sebelumnya.");
        }

        $accepted = $review->acceptedPeriods($submission, (array) $request->input('periods', []));
        $isPartial = $review->isPartial($submission, $accepted);

        $data = $request->validateWithBag("approve{$submission->id}", $review->approvalRules($submission, $accepted), SubmissionReview::APPROVAL_MESSAGES);

        $result = $review->approve($submission, $accepted, $data['paid_on'], $data['reject_reason'] ?? null, $request->user());

        if ($result === null) {
            return back()->with('warning', "Pengajuan #{$submission->code} sudah diproses sebelumnya.");
        }

        ['created' => $created, 'skipped' => $skipped] = $result;
        $label = "{$submission->duesType->name} rumah {$submission->household->number}";
        $message = $created
            ? "Dikonfirmasi: {$label}, ".implode(', ', $created).'. Sudah tercatat lunas.'
            : "Pengajuan #{$submission->code} ditandai diterima.";
        if ($skipped) {
            $message .= ' Sudah tercatat sebelumnya (tidak dicatat dobel): '.implode(', ', $skipped).'.';
        }
        if ($isPartial) {
            $message .= ' Tidak diterima: '.$submission->periodsLabel(array_values(array_diff($submission->periods, $accepted))).', bulan itu kembali terbuka untuk dibayar.';
        }

        return back()->with($skipped ? 'warning' : 'status', $message);
    }

    public function reject(Request $request, PaymentSubmission $submission, SubmissionReview $review)
    {
        $data = $request->validateWithBag("reject{$submission->id}", [
            'reject_reason' => ['required', 'string', 'max:255'],
        ], SubmissionReview::REJECTION_MESSAGES);

        if (! $submission->isPending()) {
            return back()->with('warning', "Pengajuan #{$submission->code} sudah diproses sebelumnya.");
        }

        $review->reject($submission, $data['reject_reason'], $request->user());

        return back()->with('status', "Pengajuan #{$submission->code} rumah {$submission->household->number} ditolak. Warga bisa mengirim ulang.");
    }

    public function cancel(PaymentSubmission $submission, SubmissionReview $review)
    {
        abort_unless($submission->status === PaymentSubmission::APPROVED, 404);

        $removed = $review->cancel($submission);

        return redirect()->route('admin.submissions.index')
            ->with('status', "Konfirmasi #{$submission->code} dibatalkan, {$removed} catatan lunas dihapus. Pengajuan kembali ke daftar menunggu.");
    }

    public function reopen(PaymentSubmission $submission, SubmissionReview $review)
    {
        abort_unless($submission->status === PaymentSubmission::REJECTED, 404);

        $review->reopen($submission);

        return redirect()->route('admin.submissions.index')->with('status', "Pengajuan #{$submission->code} dikembalikan ke daftar menunggu.");
    }
}
