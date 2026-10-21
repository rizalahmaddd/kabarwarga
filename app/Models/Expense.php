<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['spent_on', 'description', 'category', 'amount', 'receipt_path', 'recorded_by'])]
class Expense extends Model
{
    public const CATEGORIES = [
        'kebersihan' => 'Kebersihan & sampah',
        'keamanan' => 'Keamanan',
        'perbaikan' => 'Perbaikan fasilitas',
        'kegiatan' => 'Kegiatan warga',
        'sosial' => 'Sosial & duka',
        'operasional' => 'Operasional & ATK',
        'lainnya' => 'Lainnya',
    ];

    protected static function booted(): void
    {
        static::deleting(fn (Expense $expense) => $expense->deleteReceipt());
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'spent_on' => 'immutable_date',
        ];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['lainnya'];
    }

    public function receiptUrl(): ?string
    {
        return $this->receipt_path ? Storage::disk('public')->url($this->receipt_path) : null;
    }

    public function deleteReceipt(): void
    {
        if ($this->receipt_path) {
            Storage::disk('public')->delete($this->receipt_path);
        }
    }
}
