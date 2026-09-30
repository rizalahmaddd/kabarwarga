<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'amount', 'frequency', 'starts_on', 'due_on', 'is_active', 'description'])]
class DuesType extends Model
{
    public const MONTHLY = 'bulanan';

    public const ONCE = 'sekali';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_active' => 'boolean',
            'starts_on' => 'immutable_date',
            'due_on' => 'immutable_date',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function isMonthly(): bool
    {
        return $this->frequency === self::MONTHLY;
    }

    public function appliesToMonth(CarbonImmutable $month): bool
    {
        return ! $this->starts_on || $this->starts_on->startOfMonth()->lte($month->startOfMonth());
    }
}
