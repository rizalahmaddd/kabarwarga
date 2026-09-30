<?php

namespace App\Support;

use App\Models\DuesType;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PaymentSubmitter
{
    /**
     * @param  array{household_id: int|string, dues_type_id: int|string, periods?: array<int, string>|null, bank_account_id: int|string, payer_name?: string|null, phone?: string|null, note?: string|null}  $data
     */
    public function submit(array $data, UploadedFile $proof): PaymentSubmission
    {
        $type = DuesType::findOrFail($data['dues_type_id']);
        $periods = $this->checkedPeriods($type, (int) $data['household_id'], $data['periods'] ?? []);

        return PaymentSubmission::create([
            'code' => PaymentSubmission::newCode(),
            'household_id' => $data['household_id'],
            'dues_type_id' => $type->id,
            'periods' => $periods,
            'unit_amount' => $type->amount,
            'bank_account_id' => $data['bank_account_id'],
            'payer_name' => $data['payer_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'note' => $data['note'] ?? null,
            'proof_path' => $proof->store('bukti-bayar', 'local'),
        ]);
    }

    /**
     * @param  array<int, string>  $requested
     * @return array<int, string>
     */
    private function checkedPeriods(DuesType $type, int $householdId, array $requested): array
    {
        $periods = $type->isMonthly() ? array_values(array_unique($requested)) : [''];
        sort($periods);

        if ($type->isMonthly() && $periods === []) {
            throw ValidationException::withMessages(['periods' => 'Centang minimal satu bulan yang dibayar.']);
        }

        foreach ($periods as $period) {
            $year = (int) substr($period, 0, 4);
            if ($period !== '' && ($year < now()->year - 5 || $year > now()->year + 1)) {
                throw ValidationException::withMessages(['periods' => 'Tahun yang dipilih di luar rentang yang bisa dibayar.']);
            }
            if ($period !== '' && ! $type->appliesToMonth(CarbonImmutable::createFromFormat('!Y-m', $period))) {
                throw ValidationException::withMessages(['periods' => 'Ada bulan yang dipilih sebelum iuran ini mulai berlaku.']);
            }
        }

        $paid = Payment::where('household_id', $householdId)
            ->where('dues_type_id', $type->id)
            ->whereIn('period', $periods)
            ->pluck('period')
            ->all();
        $pending = array_intersect($periods, PaymentSubmission::pendingPeriods($householdId, $type->id));

        $label = fn (array $list) => $type->isMonthly()
            ? collect($list)->map(fn ($p) => CarbonImmutable::createFromFormat('!Y-m', $p)->translatedFormat('F Y'))->implode(', ')
            : $type->name;

        if ($paid) {
            throw ValidationException::withMessages(['periods' => 'Sudah tercatat lunas: '.$label($paid).'. Hapus centangnya lalu kirim lagi.']);
        }
        if ($pending) {
            throw ValidationException::withMessages(['periods' => 'Sedang menunggu konfirmasi pengurus: '.$label($pending).'. Tidak perlu dikirim ulang.']);
        }

        return $periods;
    }
}
