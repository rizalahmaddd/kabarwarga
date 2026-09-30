<?php

namespace App\Http\Controllers;

use App\Models\DuesType;
use App\Models\Expense;
use App\Support\DuesLedger;
use Illuminate\Http\Request;

class DuesController extends Controller
{
    public function index(Request $request, DuesLedger $ledger)
    {
        return view('public.dues.index', $this->ledgerData($request, $ledger));
    }

    public function cashbook(Request $request, DuesLedger $ledger)
    {
        $years = $ledger->years();
        $year = in_array((int) $request->query('tahun'), $years) ? (int) $request->query('tahun') : $years[0];

        return view('public.dues.cashbook', [
            'years' => $years,
            'year' => $year,
            'book' => $ledger->cashbook($year),
            'expenses' => Expense::whereYear('spent_on', $year)->latest('spent_on')->get(),
        ]);
    }

    public static function ledgerData(Request $request, DuesLedger $ledger): array
    {
        $types = DuesType::orderByDesc('is_active')->orderBy('frequency')->orderBy('name')->get();
        $type = $types->firstWhere('id', (int) $request->query('jenis')) ?? $types->first();

        $years = $ledger->years();
        $year = in_array((int) $request->query('tahun'), $years) ? (int) $request->query('tahun') : $years[0];

        $data = ['types' => $types, 'type' => $type, 'years' => $years, 'year' => $year];

        if ($type) {
            $data += $type->isMonthly() ? $ledger->monthlyGrid($type, $year) : $ledger->onceList($type);
        }

        return $data;
    }
}
