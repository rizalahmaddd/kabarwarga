<?php

namespace App\Http\Controllers;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Payment;
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

    public function exportCashbook(Request $request, DuesLedger $ledger)
    {
        $years = $ledger->years();
        $year = in_array((int) $request->query('tahun'), $years) ? (int) $request->query('tahun') : $years[0];

        $openingBalance = Payment::whereYear('paid_on', '<', $year)->sum('amount')
            - Expense::whereYear('spent_on', '<', $year)->sum('amount');

        $payments = Payment::with(['household', 'duesType'])
            ->whereYear('paid_on', $year)
            ->get()
            ->map(fn ($p) => [
                'date' => $p->paid_on->toDateString(),
                'type' => 'Pemasukan',
                'description' => "Iuran {$p->duesType->name} ({$p->periodLabel()}) - Rumah {$p->household->number} ({$p->household->head_name})",
                'in' => $p->amount,
                'out' => 0,
            ]);

        $expenses = Expense::whereYear('spent_on', $year)
            ->get()
            ->map(fn ($e) => [
                'date' => $e->spent_on->toDateString(),
                'type' => 'Pengeluaran',
                'description' => $e->description,
                'in' => 0,
                'out' => $e->amount,
            ]);

        $items = $payments->concat($expenses)->sortBy('date')->values();
        $filename = "buku-kas-{$year}-".now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($year, $openingBalance, $items) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'Jenis', 'Keterangan Transaksi', 'Pemasukan (Rp)', 'Pengeluaran (Rp)', 'Saldo (Rp)']);

            $balance = (int) $openingBalance;
            fputcsv($handle, ["{$year}-01-01", 'Saldo Awal', 'Saldo Pindahan dari Tahun Sebelumnya', 0, 0, $balance]);

            $totalIn = 0;
            $totalOut = 0;

            foreach ($items as $item) {
                $balance += $item['in'] - $item['out'];
                $totalIn += $item['in'];
                $totalOut += $item['out'];

                fputcsv($handle, [
                    $item['date'],
                    $item['type'],
                    $item['description'],
                    $item['in'],
                    $item['out'],
                    $balance,
                ]);
            }

            fputcsv($handle, ['TOTAL', '', "Total Mutasi Tahun {$year}", $totalIn, $totalOut, $balance]);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
