<?php

namespace App\Support;

use App\Models\DuesType;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentRecorder
{
    /**
     * @param  array{household_id: int|string, periods?: array<int, string>|null, amount: int, paid_on: string, method: string, note?: string|null}  $data
     * @return array{created: array<int, string>, skipped: array<int, string>, payments: array<int, Payment>}
     */
    public function record(DuesType $type, array $data, User $recorder): array
    {
        $periods = $type->isMonthly() ? array_values(array_unique($data['periods'])) : [''];

        return DB::transaction(function () use ($type, $data, $recorder, $periods) {
            $existing = Payment::where('household_id', $data['household_id'])
                ->where('dues_type_id', $type->id)
                ->whereIn('period', $periods)
                ->lockForUpdate()
                ->get();
            $newPeriods = array_values(array_diff($periods, $existing->pluck('period')->all()));

            // The form sends the total received; it all belongs to the months not yet recorded, otherwise the cashbook comes up short.
            $count = max(count($newPeriods), 1);
            $share = intdiv($data['amount'], $count);
            $remainder = $data['amount'] % $count;

            $payments = [];
            foreach ($newPeriods as $i => $period) {
                $payments[] = Payment::create([
                    'household_id' => $data['household_id'],
                    'dues_type_id' => $type->id,
                    'period' => $period,
                    'amount' => $share + ($i < $remainder ? 1 : 0),
                    'paid_on' => $data['paid_on'],
                    'method' => $data['method'],
                    'note' => $data['note'] ?? null,
                    'recorded_by' => $recorder->id,
                ]);
            }

            return [
                'created' => array_map(fn (Payment $payment) => $payment->periodLabel(), $payments),
                'skipped' => $existing->sortBy('period')->map->periodLabel()->values()->all(),
                'payments' => $payments,
            ];
        });
    }
}
