<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Support\Arrears;
use App\Support\DuesLedger;

class DashboardController extends Controller
{
    public function __invoke(DuesLedger $ledger, Arrears $arrears)
    {
        $month = now()->startOfMonth();
        $cashbook = $ledger->cashbook($month->year);
        $owing = $arrears->all();

        return view('admin.dashboard', [
            'balance' => $cashbook['closing'],
            'monthIn' => (int) Payment::whereBetween('paid_on', [$month, $month->copy()->endOfMonth()])->sum('amount'),
            'monthOut' => (int) Expense::whereBetween('spent_on', [$month, $month->copy()->endOfMonth()])->sum('amount'),
            'trend' => collect(range(5, 0))->map(function (int $back) use ($month) {
                $start = $month->copy()->subMonths($back);
                $end = $start->copy()->endOfMonth();

                return [
                    'month' => $start,
                    'in' => (int) Payment::whereBetween('paid_on', [$start, $end])->sum('amount'),
                    'out' => (int) Expense::whereBetween('spent_on', [$start, $end])->sum('amount'),
                ];
            }),
            'pending' => PaymentSubmission::pending()->with(['household', 'duesType'])->oldest()->take(5)->get(),
            'pendingCount' => PaymentSubmission::pending()->count(),
            'progress' => $ledger->currentProgress(),
            'owingCount' => $owing->count(),
            'owingTotal' => $owing->sum('total'),
            'topOwing' => $owing->take(5),
            'recent' => Payment::with(['household', 'duesType'])->latest()->take(6)->get(),
        ]);
    }
}
