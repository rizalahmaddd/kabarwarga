<?php

namespace App\Support;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DuesLedger
{
    /**
     * @return array{months: Collection<int, CarbonImmutable>, households: Collection, paid: array<int, array<string, Payment>>, pending: array<int, array<string, true>>}
     */
    public function monthlyGrid(DuesType $type, int $year): array
    {
        $months = collect(range(1, 12))->map(fn ($m) => CarbonImmutable::create($year, $m, 1));

        $payments = Payment::where('dues_type_id', $type->id)
            ->where('period', 'like', "{$year}-%")
            ->get();

        $paid = [];
        foreach ($payments as $payment) {
            $paid[$payment->household_id][$payment->period] = $payment;
        }

        $pending = [];
        foreach ($this->pendingSubmissions($type) as $submission) {
            foreach ($submission->periods as $period) {
                $pending[$submission->household_id][$period] = true;
            }
        }

        return [
            'months' => $months,
            'households' => $this->households($payments->pluck('household_id')),
            'paid' => $paid,
            'pending' => $pending,
        ];
    }

    /**
     * @return array{households: Collection, paid: array<int, Payment>, pending: array<int, true>}
     */
    public function onceList(DuesType $type): array
    {
        $payments = Payment::where('dues_type_id', $type->id)->get()->keyBy('household_id');

        return [
            'households' => $this->households($payments->keys()),
            'paid' => $payments->all(),
            'pending' => $this->pendingSubmissions($type)->mapWithKeys(fn ($s) => [$s->household_id => true])->all(),
        ];
    }

    /**
     * Paid/total counts for every active dues type that applies right now.
     */
    public function currentProgress(): Collection
    {
        $month = CarbonImmutable::now()->startOfMonth();
        $totalHouseholds = Household::active()->count();

        return DuesType::active()->orderBy('name')->get()
            ->filter(fn (DuesType $type) => $type->isMonthly() ? $type->appliesToMonth($month) : true)
            ->map(function (DuesType $type) use ($month, $totalHouseholds) {
                $period = $type->isMonthly() ? $month->format('Y-m') : '';
                $paidCount = Payment::where('dues_type_id', $type->id)
                    ->where('period', $period)
                    ->whereHas('household', fn ($q) => $q->active())
                    ->count();

                return [
                    'type' => $type,
                    'label' => $type->isMonthly() ? $month->translatedFormat('F Y') : 'Sekali bayar',
                    'paid' => $paidCount,
                    'total' => $totalHouseholds,
                ];
            })
            ->values();
    }

    /**
     * Cash in (payments by paid_on) and out (expenses) per month for a year.
     */
    public function cashbook(int $year): array
    {
        $in = Payment::whereYear('paid_on', $year)->get(['paid_on', 'amount'])
            ->groupBy(fn ($p) => $p->paid_on->month)->map->sum('amount');
        $out = Expense::whereYear('spent_on', $year)->get(['spent_on', 'amount'])
            ->groupBy(fn ($e) => $e->spent_on->month)->map->sum('amount');

        $openingBalance = Payment::whereYear('paid_on', '<', $year)->sum('amount')
            - Expense::whereYear('spent_on', '<', $year)->sum('amount');

        $balance = $openingBalance;
        $rows = [];
        foreach (range(1, 12) as $m) {
            $monthIn = (int) ($in[$m] ?? 0);
            $monthOut = (int) ($out[$m] ?? 0);
            $balance += $monthIn - $monthOut;

            if ($monthIn || $monthOut) {
                $rows[] = [
                    'month' => CarbonImmutable::create($year, $m, 1),
                    'in' => $monthIn,
                    'out' => $monthOut,
                    'balance' => $balance,
                ];
            }
        }

        return [
            'opening' => (int) $openingBalance,
            'rows' => $rows,
            'total_in' => (int) $in->sum(),
            'total_out' => (int) $out->sum(),
            'closing' => (int) $balance,
        ];
    }

    public function years(): array
    {
        $first = collect([
            Payment::min('paid_on'),
            Expense::min('spent_on'),
            Payment::where('period', '!=', '')->min('period'),
        ])->filter()->map(fn ($d) => (int) substr($d, 0, 4))->min();

        $now = (int) now()->year;

        return range($now, min($first ?? $now, $now));
    }

    private function pendingSubmissions(DuesType $type): Collection
    {
        return PaymentSubmission::pending()->where('dues_type_id', $type->id)->get(['household_id', 'periods']);
    }

    private function households(Collection $withPayments): Collection
    {
        return Household::query()
            ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $withPayments->unique()))
            ->ordered()
            ->get();
    }
}
