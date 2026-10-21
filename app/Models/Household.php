<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['number', 'head_name', 'occupancy_status', 'kk_number', 'phone', 'is_active', 'note', 'kk_image_path'])]
#[Hidden(['access_token'])]
class Household extends Model
{
    use HasFactory;

    public const OCCUPANCY_STATUSES = [
        'pemilik' => 'Pemilik',
        'kontrak' => 'Kontrak',
    ];

    protected static function booted(): void
    {
        static::deleting(fn (Household $household) => $household->deleteKkImage());
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * KK scans hold every member's NIK, so they live on the private disk. Older uploads may still sit on the public disk.
     */
    public function deleteKkImage(): void
    {
        if ($this->kk_image_path) {
            Storage::disk('local')->delete($this->kk_image_path);
            Storage::disk('public')->delete($this->kk_image_path);
        }
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
     * Private link for the household page. The token is created on first use so existing houses need no backfill.
     */
    public function portalUrl(): string
    {
        if (! $this->access_token) {
            $this->forceFill(['access_token' => Str::random(32)])->saveQuietly();
        }

        return route('portal.show', $this->access_token);
    }

    public function resetAccessToken(): void
    {
        $this->forceFill(['access_token' => Str::random(32)])->saveQuietly();
    }

    /**
     * Added to QRIS amounts so the treasurer can tell which house a mutation came from.
     */
    public function uniqueCode(): int
    {
        return ($this->id - 1) % 999 + 1;
    }
}
