<?php

namespace App\Http\Resources;

use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Household */
class HouseholdResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user() !== null;

        return [
            'id' => $this->id,
            'number' => $this->number,
            'head_name' => $this->head_name,
            'label' => $this->label(),
            'is_active' => $this->is_active,
            'unique_code' => $this->uniqueCode(),
            'phone' => $this->when($isAdmin, $this->phone),
            'note' => $this->when($isAdmin, $this->note),
            'payments_count' => $this->whenCounted('payments'),
        ];
    }
}
