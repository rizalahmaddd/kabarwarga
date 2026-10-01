<?php

namespace App\Http\Resources;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BankAccount */
class BankAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'label' => $this->label(),
            'qris_url' => $this->qrisUrl(),
            'qris_payload' => $this->qris_payload,
            'has_dynamic_qris' => $this->hasDynamicQris(),
            'is_active' => $this->is_active,
            'position' => $this->position,
        ];
    }
}
