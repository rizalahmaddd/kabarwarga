<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Payment;
use App\Support\DuesLedger;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function show(Request $request, DuesLedger $ledger)
    {
        $years = $ledger->years();
        $year = in_array($request->integer('tahun'), $years, true) ? $request->integer('tahun') : $years[0];
        $month = $request->integer('bulan');
        $month = $month >= 1 && $month <= 12 ? $month : null;

        $from = $month ? CarbonImmutable::create($year, $month, 1) : CarbonImmutable::create($year, 1, 1);
        // endOf* keeps 23:59:59, so the last day still matches on SQLite where dates are stored with a time part.
        $until = $month ? $from->endOfMonth() : $from->endOfYear();

        $opening = (int) Payment::where('paid_on', '<', $from)->sum('amount')
            - (int) Expense::where('spent_on', '<', $from)->sum('amount');

        $income = Payment::whereBetween('paid_on', [$from, $until])
            ->selectRaw('dues_type_id, count(*) as records, sum(amount) as total')
            ->groupBy('dues_type_id')
            ->get();
        $typeNames = DuesType::whereIn('id', $income->pluck('dues_type_id'))->pluck('name', 'id');

        $expenses = Expense::whereBetween('spent_on', [$from, $until])
            ->orderBy('spent_on')
            ->orderBy('id')
            ->get();

        $totalIn = (int) $income->sum('total');
        $totalOut = (int) $expenses->sum('amount');

        return view('admin.reports.show', [
            'years' => $years,
            'year' => $year,
            'month' => $month,
            'periodLabel' => $month ? $from->translatedFormat('F Y') : "Tahun {$year}",
            'from' => $from,
            'until' => $until,
            'opening' => $opening,
            'income' => $income->map(fn ($row) => [
                'name' => $typeNames[$row->dues_type_id] ?? 'Iuran terhapus',
                'records' => (int) $row->records,
                'total' => (int) $row->total,
            ])->sortByDesc('total')->values(),
            'byCategory' => $expenses->groupBy('category')
                ->map(fn ($group) => ['label' => $group->first()->categoryLabel(), 'count' => $group->count(), 'total' => (int) $group->sum('amount')])
                ->sortByDesc('total')
                ->values(),
            'expenses' => $expenses,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'closing' => $opening + $totalIn - $totalOut,
        ]);
    }
}
