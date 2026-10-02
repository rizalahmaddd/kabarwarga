<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'head_name', 'occupancy_status', 'kk_number', 'phone', 'is_active', 'note', 'kk_image_path'])]
class Household extends Model
{
    use HasFactory;

    public const OCCUPANCY_STATUSES = [
        'pemilik' => 'Pemilik',
        'kontrak' => 'Kontrak',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function occupancyLabel(): string
    {
        return self::OCCUPANCY_STATUSES[$this->occupancy_status] ?? 'Pemilik';
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class)->orderByRaw("CASE 
            WHEN family_relation = 'Kepala Keluarga' THEN 1
            WHEN family_relation = 'Suami' THEN 2
            WHEN family_relation = 'Istri' THEN 3
            WHEN family_relation = 'Anak' THEN 4
            ELSE 5 END");
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
