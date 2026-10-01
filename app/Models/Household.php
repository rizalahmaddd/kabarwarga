<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'head_name', 'phone', 'is_active', 'note'])]
class Household extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderByRaw('length(number)')->orderBy('number');
    }

    public function label(): string
    {
        return "{$this->number} · {$this->head_name}";
    }

    /**
     * Added to QRIS amounts so the treasurer can tell which house a mutation came from.
     */
    public function uniqueCode(): int
    {
        return ($this->id - 1) % 999 + 1;
    }
}
