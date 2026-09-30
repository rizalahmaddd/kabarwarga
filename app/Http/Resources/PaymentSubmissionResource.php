<?php

namespace App\Http\Resources;

use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PaymentSubmission */
class PaymentSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user() !== null;

        return [
            'id' => $this->when($isAdmin, $this->id),
            'code' => $this->code,
            'status' => $this->status,
            'status_label' => PaymentSubmission::STATUSES[$this->status] ?? $this->status,
            'is_one_time' => $this->periods === [''],
            'periods' => $this->publicPeriods($this->periods),
            'periods_label' => $this->periodsLabel(),
            'approved_periods' => $this->approved_periods === null ? null : $this->publicPeriods($this->approved_periods),
            'declined_periods' => $this->publicPeriods($this->declinedPeriods()),
            'is_partial' => $this->isPartial(),
            'unit_amount' => $this->unit_amount,
            'total' => $this->total(),
            'accepted_total' => $this->acceptedTotal(),
            'payer_name' => $this->payer_name,
            'phone' => $this->when($isAdmin, $this->phone),
            'note' => $this->note,
            'reject_reason' => $this->reject_reason,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'proof_url' => $this->when($isAdmin, fn () => route('api.v1.admin.payment-submissions.proof', $this->resource)),
            'household' => new HouseholdResource($this->whenLoaded('household')),
            'dues_type' => new DuesTypeResource($this->whenLoaded('duesType')),
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * One-time dues are stored as a single empty period; expose them as an empty list.
     *
     * @param  array<int, string>  $periods
     * @return array<int, string>
     */
    private function publicPeriods(array $periods): array
    {
        return array_values(array_filter($periods, fn ($p) => $p !== ''));
    }
}
