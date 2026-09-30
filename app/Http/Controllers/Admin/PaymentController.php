<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DuesController;
use App\Http\Requests\StorePaymentRequest;
use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Support\DuesLedger;
use App\Support\PaymentRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        $households = Household::active()->ordered()->get();
        $types = DuesType::active()->orderBy('name')->get();

        $household = $households->firstWhere('id', (int) $request->query('rumah'));
        $type = $types->firstWhere('id', (int) $request->query('jenis'));
        $year = (int) $request->query('tahun', now()->year);
        $year = $year >= 2000 && $year <= now()->year + 1 ? $year : now()->year;

        $months = collect();
        $existing = collect();

        if ($household && $type) {
            $existing = Payment::where('household_id', $household->id)
                ->where('dues_type_id', $type->id)
                ->get()
                ->keyBy('period');

            if ($type->isMonthly()) {
                $months = collect(range(1, 12))->map(fn ($m) => CarbonImmutable::create($year, $m, 1));
            }
        }

        return view('admin.payments.create', [
            'households' => $households,
            'types' => $types,
            'household' => $household,
            'type' => $type,
            'year' => $year,
            'months' => $months,
            'existing' => $existing,
            'preselect' => (array) $request->query('bulan', []),
            'recent' => Payment::with(['household', 'duesType'])->latest()->take(8)->get(),
        ]);
    }

    public function store(StorePaymentRequest $request, PaymentRecorder $recorder)
    {
        $type = $request->duesType();
        ['created' => $created, 'skipped' => $skipped] = $recorder->record($type, $request->validated(), $request->user());

        $household = Household::find($request->validated('household_id'));
        $message = $created
            ? "Tercatat: {$type->name} rumah {$household->number}, ".implode(', ', $created).'.'
            : 'Tidak ada yang baru dicatat.';
        if ($skipped) {
            $message .= ' Sudah tercatat sebelumnya: '.implode(', ', $skipped).'.';
        }

        return redirect()
            ->route('admin.home', ['rumah' => $household->id, 'jenis' => $type->id, 'tahun' => $request->integer('year') ?: null])
            ->with($created ? 'status' : 'warning', $message);
    }

    public function index(Request $request)
    {
        $payments = Payment::with(['household', 'duesType', 'recorder'])
            ->when($request->integer('rumah'), fn ($q, $id) => $q->where('household_id', $id))
            ->when($request->integer('jenis'), fn ($q, $id) => $q->where('dues_type_id', $id))
            ->latest('paid_on')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'households' => Household::ordered()->get(),
            'types' => DuesType::orderBy('name')->get(),
        ]);
    }

    public function destroy(Payment $payment)
    {
        $label = "{$payment->duesType->name} rumah {$payment->household->number} ({$payment->periodLabel()})";
        $payment->delete();

        return back()->with('status', "Catatan {$label} dihapus.");
    }

    public function ledger(Request $request, DuesLedger $ledger)
    {
        return view('admin.payments.ledger', DuesController::ledgerData($request, $ledger));
    }
}
