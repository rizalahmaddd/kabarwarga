<?php

namespace App\Http\Resources;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Expense */
class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'spent_on' => $this->spent_on->toDateString(),
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->categoryLabel(),
            'amount' => $this->amount,
            'receipt_url' => $this->receiptUrl(),
            'recorder' => new UserResource($this->whenLoaded('recorder')),
        ];
    }
}
