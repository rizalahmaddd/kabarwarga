<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['household_id', 'dues_type_id', 'period', 'amount', 'paid_on', 'method', 'note', 'recorded_by', 'payment_submission_id'])]
class Payment extends Model
{
    public const METHODS = ['tunai' => 'Tunai', 'transfer' => 'Transfer'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_on' => 'immutable_date',
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function periodLabel(): string
    {
        return $this->period === ''
            ? 'Sekali bayar'
            : CarbonImmutable::createFromFormat('!Y-m', $this->period)->translatedFormat('F Y');
    }
}
