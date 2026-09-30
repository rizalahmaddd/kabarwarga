<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentSubmissionRequest;
use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Support\PaymentSubmitter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class PayController extends Controller
{
    public function create(Request $request)
    {
        $households = Household::active()->ordered()->get();
        $types = DuesType::active()->orderBy('name')->get();

        $household = $households->firstWhere('id', (int) $request->query('rumah'));
        $type = $types->firstWhere('id', (int) $request->query('jenis'));

        $thisYear = now()->year;
        $year = (int) $request->query('tahun', $thisYear);
        $year = $year >= $thisYear - 1 && $year <= $thisYear + 1 ? $year : $thisYear;

        $months = collect();
        $paid = collect();
        $pending = [];

        if ($household && $type) {
            $paid = Payment::where('household_id', $household->id)
                ->where('dues_type_id', $type->id)
                ->get()
                ->keyBy('period');
            $pending = PaymentSubmission::pendingPeriods($household->id, $type->id);

            if ($type->isMonthly()) {
                $months = collect(range(1, 12))->map(fn ($m) => CarbonImmutable::create($year, $m, 1));
            }
        }

        return view('public.pay.create', [
            'households' => $households,
            'types' => $types,
            'household' => $household,
            'type' => $type,
            'year' => $year,
            'months' => $months,
            'paid' => $paid,
            'pending' => $pending,
            'accounts' => BankAccount::active()->ordered()->get(),
            'history' => $household
                ? PaymentSubmission::with('duesType')->where('household_id', $household->id)->latest()->take(5)->get()
                : collect(),
        ]);
    }

    public function store(StorePaymentSubmissionRequest $request, PaymentSubmitter $submitter)
    {
        $submission = $submitter->submit($request->validated(), $request->file('proof'));

        return redirect()->route('pay.show', $submission->code)
            ->with('status', 'Bukti pembayaran terkirim. Pengurus akan mengecek dan mengonfirmasinya.');
    }

    public function show(string $code)
    {
        $submission = PaymentSubmission::with(['household', 'duesType', 'bankAccount'])
            ->where('code', strtoupper($code))
            ->firstOrFail();

        return view('public.pay.show', compact('submission'));
    }
}
