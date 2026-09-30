<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'household_id', 'dues_type_id', 'periods', 'approved_periods', 'unit_amount', 'bank_account_id', 'payer_name', 'phone', 'note', 'proof_path', 'status', 'reject_reason', 'reviewed_by', 'reviewed_at'])]
class PaymentSubmission extends Model
{
    public const PENDING = 'menunggu';

    public const APPROVED = 'diterima';

    public const REJECTED = 'ditolak';

    public const STATUSES = [
        self::PENDING => 'Menunggu Konfirmasi',
        self::APPROVED => 'Diterima',
        self::REJECTED => 'Ditolak',
    ];

    protected function casts(): array
    {
        return [
            'periods' => 'array',
            'approved_periods' => 'array',
            'unit_amount' => 'integer',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function duesType(): BelongsTo
    {
        return $this->belongsTo(DuesType::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', self::PENDING);
    }

    public static function newCode(): string
    {
        do {
            $code = collect(range(1, 8))
                ->map(fn () => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'[random_int(0, 31)])
                ->implode('');
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Periods of this dues type that are still waiting for review, for one household.
     *
     * @return array<int, string>
     */
    public static function pendingPeriods(int $householdId, int $duesTypeId): array
    {
        return self::pending()
            ->where('household_id', $householdId)
            ->where('dues_type_id', $duesTypeId)
            ->pluck('periods')
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    public function total(): int
    {
        return $this->unit_amount * count($this->periods);
    }

    /**
     * @param  array<int, string>|null  $periods
     */
    public function periodsLabel(?array $periods = null): string
    {
        $periods ??= $this->periods;

        if ($periods === ['']) {
            return 'Sekali bayar';
        }

        return collect($periods)
            ->sort()
            ->map(fn ($p) => CarbonImmutable::createFromFormat('!Y-m', $p)->translatedFormat('M Y'))
            ->implode(', ');
    }

    /**
     * @return array<int, string>
     */
    public function acceptedPeriods(): array
    {
        return $this->approved_periods ?? $this->periods;
    }

    /**
     * @return array<int, string>
     */
    public function declinedPeriods(): array
    {
        return $this->status === self::APPROVED
            ? array_values(array_diff($this->periods, $this->acceptedPeriods()))
            : [];
    }

    public function isPartial(): bool
    {
        return $this->declinedPeriods() !== [];
    }

    public function acceptedTotal(): int
    {
        return $this->unit_amount * count($this->acceptedPeriods());
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
