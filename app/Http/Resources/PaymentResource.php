<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'household_id' => $this->household_id,
            'dues_type_id' => $this->dues_type_id,
            'period' => $this->period === '' ? null : $this->period,
            'period_label' => $this->periodLabel(),
            'amount' => $this->amount,
            'paid_on' => $this->paid_on->toDateString(),
            'method' => $this->method,
            'method_label' => Payment::METHODS[$this->method] ?? $this->method,
            'note' => $this->when($request->user() !== null, $this->note),
            'payment_submission_id' => $this->when($request->user() !== null, $this->payment_submission_id),
            'household' => new HouseholdResource($this->whenLoaded('household')),
            'dues_type' => new DuesTypeResource($this->whenLoaded('duesType')),
            'recorder' => new UserResource($this->whenLoaded('recorder')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
