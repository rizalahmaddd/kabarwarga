<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentSubmissionResource;
use App\Models\PaymentSubmission;
use App\Support\SubmissionReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'status' => ['nullable', Rule::in(array_keys(PaymentSubmission::STATUSES))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $status = $request->query('status', PaymentSubmission::PENDING);

        $submissions = PaymentSubmission::with(['household', 'duesType', 'bankAccount', 'reviewer'])
            ->where('status', $status)
            ->when($status === PaymentSubmission::PENDING, fn ($q) => $q->oldest(), fn ($q) => $q->latest('reviewed_at')->latest('id'))
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return PaymentSubmissionResource::collection($submissions)->additional([
            'counts' => collect(PaymentSubmission::STATUSES)->map(fn () => 0)
                ->merge(PaymentSubmission::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'))
                ->map(fn ($total) => (int) $total),
        ]);
    }

    public function show(PaymentSubmission $submission): PaymentSubmissionResource
    {
        return new PaymentSubmissionResource($submission->load(['household', 'duesType', 'bankAccount', 'reviewer']));
    }

    public function proof(PaymentSubmission $submission): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($submission->proof_path), 404);

        return Storage::disk('local')->response($submission->proof_path);
    }

    public function approve(Request $request, PaymentSubmission $submission, SubmissionReview $review): JsonResponse
    {
        if (! $submission->isPending()) {
            return $this->alreadyProcessed($submission);
        }

        $accepted = $review->acceptedPeriods($submission, (array) $request->input('periods', []));
        $data = $request->validate($review->approvalRules($submission, $accepted), SubmissionReview::APPROVAL_MESSAGES);

        $result = $review->approve($submission, $accepted, $data['paid_on'], $data['reject_reason'] ?? null, $request->user());

        if ($result === null) {
            return $this->alreadyProcessed($submission);
        }

        return (new PaymentSubmissionResource($submission->fresh(['household', 'duesType', 'bankAccount', 'reviewer'])))
            ->additional([
                'message' => "Pengajuan #{$submission->code} dikonfirmasi.",
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ])
            ->response();
    }

    public function reject(Request $request, PaymentSubmission $submission, SubmissionReview $review): JsonResponse
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ], SubmissionReview::REJECTION_MESSAGES);

        if (! $submission->isPending()) {
            return $this->alreadyProcessed($submission);
        }

        $review->reject($submission, $data['reject_reason'], $request->user());

        return (new PaymentSubmissionResource($submission->fresh(['household', 'duesType', 'bankAccount', 'reviewer'])))
            ->additional(['message' => "Pengajuan #{$submission->code} ditolak. Warga bisa mengirim ulang."])
            ->response();
    }

    public function cancel(PaymentSubmission $submission, SubmissionReview $review): JsonResponse
    {
        if ($submission->status !== PaymentSubmission::APPROVED) {
            return response()->json(['message' => "Pengajuan #{$submission->code} belum diterima, jadi tidak ada yang bisa dibatalkan."], 409);
        }

        $removed = $review->cancel($submission);

        return (new PaymentSubmissionResource($submission->fresh(['household', 'duesType', 'bankAccount'])))
            ->additional([
                'message' => "Konfirmasi #{$submission->code} dibatalkan, {$removed} catatan lunas dihapus. Pengajuan kembali ke daftar menunggu.",
                'removed_payments' => $removed,
            ])
            ->response();
    }

    public function reopen(PaymentSubmission $submission, SubmissionReview $review): JsonResponse
    {
        if ($submission->status !== PaymentSubmission::REJECTED) {
            return response()->json(['message' => "Pengajuan #{$submission->code} tidak dalam status ditolak."], 409);
        }

        $review->reopen($submission);

        return (new PaymentSubmissionResource($submission->fresh(['household', 'duesType', 'bankAccount'])))
            ->additional(['message' => "Pengajuan #{$submission->code} dikembalikan ke daftar menunggu."])
            ->response();
    }

    private function alreadyProcessed(PaymentSubmission $submission): JsonResponse
    {
        return response()->json(['message' => "Pengajuan #{$submission->code} sudah diproses sebelumnya."], 409);
    }
}
