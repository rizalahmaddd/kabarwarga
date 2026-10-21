<?php

namespace App\Support;

use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class Arrears
{
    /**
     * Unpaid dues per active household up to this month. Months already waiting for review are not counted.
     *
     * @return Collection<int, array{household: Household, items: Collection<int, array{type: DuesType, periods: array<int, string>, amount: int}>, months: int, total: int}>
     */
    public function all(?Household $only = null): Collection
    {
        $types = DuesType::active()->orderBy('name')->get();
        $households = $only ? collect([$only]) : Household::active()->ordered()->get();

        if ($types->isEmpty() || $households->isEmpty()) {
            return collect();
        }

        $settled = [];
        Payment::whereIn('dues_type_id', $types->pluck('id'))
            ->whereIn('household_id', $households->pluck('id'))
            ->get(['household_id', 'dues_type_id', 'period'])
            ->each(function (Payment $payment) use (&$settled) {
                $settled[$payment->household_id][$payment->dues_type_id][$payment->period] = true;
            });
        PaymentSubmission::pending()
            ->whereIn('household_id', $households->pluck('id'))
            ->get(['household_id', 'dues_type_id', 'periods'])
            ->each(function (PaymentSubmission $submission) use (&$settled) {
                foreach ($submission->periods as $period) {
                    $settled[$submission->household_id][$submission->dues_type_id][$period] = true;
                }
            });

        $due = $types->mapWithKeys(fn (DuesType $type) => [$type->id => $this->duePeriods($type)]);

        return $households
            ->map(function (Household $household) use ($types, $due, $settled) {
                $items = $types
                    ->map(function (DuesType $type) use ($household, $due, $settled) {
                        $periods = array_values(array_filter(
                            $due[$type->id],
                            fn (string $period) => ! isset($settled[$household->id][$type->id][$period]),
                        ));

                        return ['type' => $type, 'periods' => $periods, 'amount' => count($periods) * $type->amount];
                    })
                    ->filter(fn (array $item) => $item['periods'] !== [])
                    ->values();

                return [
                    'household' => $household,
                    'items' => $items,
                    'months' => $items->sum(fn (array $item) => count($item['periods'])),
                    'total' => $items->sum('amount'),
                ];
            })
            ->filter(fn (array $row) => $row['items']->isNotEmpty())
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  array<int, string>  $periods
     */
    public static function periodsLabel(array $periods): string
    {
        if ($periods === ['']) {
            return 'sekali bayar';
        }

        return collect($periods)
            ->map(fn (string $period) => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'))
            ->implode(', ');
    }

    /**
     * @return array<int, string>
     */
    private function duePeriods(DuesType $type): array
    {
        $now = CarbonImmutable::now()->startOfMonth();

        if (! $type->isMonthly()) {
            return $type->starts_on && $type->starts_on->isFuture() ? [] : [''];
        }

        // Without a start month, count from when the dues type was created, not from the beginning of time.
        $from = ($type->starts_on ?? CarbonImmutable::parse($type->created_at))->startOfMonth();
        if ($from->gt($now)) {
            return [];
        }

        return collect(CarbonPeriod::create($from, '1 month', $now))
            ->map(fn ($month) => $month->format('Y-m'))
            ->all();
    }
}
