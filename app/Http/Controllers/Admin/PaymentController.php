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
        $recordResult = $recorder->record($type, $request->validated(), $request->user());
        $created = $recordResult['created'];
        $skipped = $recordResult['skipped'];
        $payments = $recordResult['payments'] ?? [];

        $household = Household::find($request->validated('household_id'));
        $message = $created
            ? "Tercatat: {$type->name} rumah {$household->number}, ".implode(', ', $created).'.'
            : 'Tidak ada yang baru dicatat.';
        if ($skipped) {
            $message .= ' Sudah tercatat sebelumnya: '.implode(', ', $skipped).'.';
        }

        $redirect = redirect()
            ->route('admin.home', ['rumah' => $household->id, 'jenis' => $type->id, 'tahun' => $request->integer('year') ?: null])
            ->with($created ? 'status' : 'warning', $message);

        if ($created && ! empty($payments)) {
            $firstPayment = $payments[0];
            $receiptUrl = route('receipt.show', $firstPayment);
            $redirect->with('receipt_url', $receiptUrl);

            if ($household->phone) {
                $cleanPhone = clean_phone($household->phone);
                $totalAmount = array_sum(array_map(fn ($p) => $p->amount, $payments)) ?: (int) $request->validated('amount');
                $periodsLabel = implode(', ', $created);
                $paidOnDate = $firstPayment->paid_on->translatedFormat('j F Y');
                $siteName = setting('site_name');

                $waText = "Halo Bpk/Ibu {$household->head_name} (Rumah {$household->number}),\n"
                    ."Pembayaran iuran {$type->name} untuk periode {$periodsLabel} sebesar ".rupiah($totalAmount)." telah dicatat LUNAS pada {$paidOnDate}.\n\n"
                    ."Tanda terima resmi: {$receiptUrl}\n\n"
                    ."Terima kasih atas partisipasinya! 🙏\n- Pengurus {$siteName}";

                $redirect->with('whatsapp_url', "https://wa.me/{$cleanPhone}?text=".rawurlencode($waText))
                    ->with('whatsapp_recipient', "{$household->head_name} ({$household->phone})");
            }
        }

        return $redirect;
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
