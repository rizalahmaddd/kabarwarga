<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentSubmissionRequest;
use App\Http\Resources\PaymentSubmissionResource;
use App\Models\PaymentSubmission;
use App\Support\PaymentSubmitter;
use Illuminate\Http\JsonResponse;

class PaymentSubmissionController extends Controller
{
    public function store(StorePaymentSubmissionRequest $request, PaymentSubmitter $submitter): JsonResponse
    {
        $submission = $submitter->submit($request->validated(), $request->file('proof'));
        $submission->refresh()->load(['household', 'duesType', 'bankAccount']);

        return (new PaymentSubmissionResource($submission))
            ->additional(['message' => 'Bukti pembayaran terkirim. Pengurus akan mengecek dan mengonfirmasinya.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $code): PaymentSubmissionResource
    {
        $submission = PaymentSubmission::with(['household', 'duesType', 'bankAccount'])
            ->where('code', strtoupper($code))
            ->firstOrFail();

        return new PaymentSubmissionResource($submission);
    }
}
