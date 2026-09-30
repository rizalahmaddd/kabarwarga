<?php

namespace App\Http\Resources;

use App\Models\DuesType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DuesType */
class DuesTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'is_monthly' => $this->isMonthly(),
            'starts_on' => $this->starts_on?->format('Y-m'),
            'due_on' => $this->due_on?->toDateString(),
            'description' => $this->description,
            'is_active' => $this->is_active,
            'payments_count' => $this->whenCounted('payments'),
            'payments_sum_amount' => $this->whenAggregated('payments', 'amount', 'sum', fn ($sum) => (int) $sum),
        ];
    }
}
