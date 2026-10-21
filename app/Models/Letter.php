<?php

namespace App\Models;

use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable(['code', 'household_id', 'household_member_id', 'type', 'purpose', 'person', 'phone', 'status', 'reject_reason'])]
class Letter extends Model
{
    /** @use HasFactory<LetterFactory> */
    use HasFactory;

    public const PENDING = 'menunggu';

    public const APPROVED = 'disetujui';

    public const REJECTED = 'ditolak';

    public const STATUSES = [
        self::PENDING => 'Menunggu',
        self::APPROVED => 'Disetujui',
        self::REJECTED => 'Ditolak',
    ];

    /**
     * Title and the sentence that follows "adalah benar ..." in the letter body.
     */
    public const TYPES = [
        'domisili' => ['title' => 'Surat Keterangan Domisili', 'statement' => 'benar warga kami dan berdomisili di alamat tersebut di atas.'],
        'skck' => ['title' => 'Surat Pengantar SKCK', 'statement' => 'benar warga kami dan sepanjang pengetahuan kami berkelakuan baik serta tidak pernah tersangkut perkara pidana.'],
        'nikah' => ['title' => 'Surat Pengantar Nikah', 'statement' => 'benar warga kami dan bermaksud mengurus keperluan pernikahan di kelurahan/KUA.'],
        'usaha' => ['title' => 'Surat Keterangan Usaha', 'statement' => 'benar warga kami dan menjalankan usaha di lingkungan kami.'],
        'tidak_mampu' => ['title' => 'Surat Keterangan Tidak Mampu', 'statement' => 'benar warga kami dan sepanjang pengetahuan kami tergolong keluarga kurang mampu.'],
        'umum' => ['title' => 'Surat Pengantar', 'statement' => 'benar warga kami.'],
    ];

    protected function casts(): array
    {
        return [
            'person' => 'array',
            'issued_on' => 'immutable_date',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class, 'household_member_id');
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
            $code = collect(range(1, 12))
                ->map(fn () => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'[random_int(0, 31)])
                ->implode('');
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * @return array<string, string|null>
     */
    public static function personFrom(HouseholdMember $member): array
    {
        return [
            'name' => $member->name,
            'nik' => $member->nik,
            'gender' => $member->gender,
            'birth_place' => $member->birth_place,
            'birth_date' => $member->birth_date?->toDateString(),
            'religion' => $member->religion,
            'job' => $member->job,
            'marital_status' => $member->marital_status,
        ];
    }

    public function typeTitle(): string
    {
        return self::TYPES[$this->type]['title'] ?? 'Surat Pengantar';
    }

    public function statement(): string
    {
        return self::TYPES[$this->type]['statement'] ?? self::TYPES['umum']['statement'];
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function maskedNik(): string
    {
        $nik = (string) ($this->person['nik'] ?? '');

        return strlen($nik) > 8 ? substr($nik, 0, 4).str_repeat('•', strlen($nik) - 8).substr($nik, -4) : '-';
    }

    /**
     * Gives the letter the next number of the year. Returns false when someone else already processed it.
     */
    public function approve(User $reviewer): bool
    {
        return DB::transaction(function () use ($reviewer) {
            $letter = self::lockForUpdate()->findOrFail($this->id);
            if (! $letter->isPending()) {
                return false;
            }

            $issuedOn = now();
            $sequence = (int) self::where('status', self::APPROVED)->whereYear('issued_on', $issuedOn->year)->lockForUpdate()->max('sequence') + 1;
            $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $code = setting('letter_code') ?: 'RT';

            // Pengurus often fixes the KK data after seeing the request, so print what the record says now.
            $member = $letter->household_member_id ? HouseholdMember::find($letter->household_member_id) : null;

            $letter->forceFill([
                'person' => $member ? self::personFrom($member) : $letter->person,
                'status' => self::APPROVED,
                'sequence' => $sequence,
                'number' => sprintf('%03d/%s/%s/%d', $sequence, $code, $romanMonths[$issuedOn->month - 1], $issuedOn->year),
                'issued_on' => $issuedOn->toDateString(),
                'reject_reason' => null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => $issuedOn,
            ])->save();

            $this->setRawAttributes($letter->getAttributes(), true);

            return true;
        });
    }

    public function reject(string $reason, User $reviewer): bool
    {
        return DB::transaction(function () use ($reason, $reviewer) {
            $letter = self::lockForUpdate()->findOrFail($this->id);
            if (! $letter->isPending()) {
                return false;
            }

            $letter->forceFill([
                'status' => self::REJECTED,
                'reject_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ])->save();

            return true;
        });
    }
}
