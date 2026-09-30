<?php

namespace App\Support;

use App\Models\DuesType;
use App\Models\Payment;
use App\Models\User;

class PaymentRecorder
{
    /**
     * @param  array{household_id: int|string, periods?: array<int, string>|null, amount: int, paid_on: string, method: string, note?: string|null}  $data
     * @return array{created: array<int, string>, skipped: array<int, string>}
     */
    public function record(DuesType $type, array $data, User $recorder): array
    {
        $periods = $type->isMonthly() ? array_unique($data['periods']) : [''];
        $created = [];
        $skipped = [];

        // The form sends the total received, so split it across the months to keep the cashbook sum right.
        $share = intdiv($data['amount'], count($periods));
        $remainder = $data['amount'] % count($periods);

        foreach (array_values($periods) as $i => $period) {
            $payment = Payment::firstOrCreate(
                ['household_id' => $data['household_id'], 'dues_type_id' => $type->id, 'period' => $period],
                [
                    'amount' => $share + ($i < $remainder ? 1 : 0),
                    'paid_on' => $data['paid_on'],
                    'method' => $data['method'],
                    'note' => $data['note'] ?? null,
                    'recorded_by' => $recorder->id,
                ],
            );

            if ($payment->wasRecentlyCreated) {
                $created[] = $payment->periodLabel();
            } else {
                $skipped[] = $payment->periodLabel();
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
