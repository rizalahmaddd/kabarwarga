<?php

namespace App\Models;

use App\Support\Qris;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['bank_name', 'account_number', 'account_name', 'qris_path', 'qris_payload', 'is_active', 'position'])]
class BankAccount extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    public function label(): string
    {
        return collect([$this->bank_name, $this->account_number])->filter()->implode(' · ');
    }

    public function qrisUrl(): ?string
    {
        return $this->qris_path ? Storage::disk('public')->url($this->qris_path) : null;
    }

    public function hasDynamicQris(): bool
    {
        return $this->qris_payload !== null && Qris::isStatic($this->qris_payload);
    }

    public function qrisFor(int $amount): ?string
    {
        return $this->hasDynamicQris() ? Qris::withAmount($this->qris_payload, $amount) : null;
    }
}
