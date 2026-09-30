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
            'amount' => $this->amount,
            'recorder' => new UserResource($this->whenLoaded('recorder')),
        ];
    }
}
