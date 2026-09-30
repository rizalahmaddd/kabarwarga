<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DuesTypeResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\HouseholdResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentSubmissionResource;
use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Support\DuesLedger;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuesController extends Controller
{
    public function ledger(Request $request, DuesLedger $ledger): JsonResponse
    {
        $types = DuesType::orderByDesc('is_active')->orderBy('frequency')->orderBy('name')->get();
        $type = $types->firstWhere('id', $request->integer('dues_type_id')) ?? $types->first();
        $years = $ledger->years();
        $year = in_array($request->integer('year'), $years, true) ? $request->integer('year') : $years[0];

        $data = [
            'dues_types' => DuesTypeResource::collection($types),
            'dues_type' => $type ? new DuesTypeResource($type) : null,
            'years' => $years,
            'year' => $year,
            'months' => [],
            'rows' => [],
        ];

        if ($type?->isMonthly()) {
            $grid = $ledger->monthlyGrid($type, $year);
            $data['months'] = $grid['months']->map(fn (CarbonImmutable $m) => $m->format('Y-m'))->all();
            $data['rows'] = $grid['households']->map(fn (Household $household) => [
                'household' => new HouseholdResource($household),
                'months' => $grid['months']->map(fn (CarbonImmutable $month) => $this->cell(
                    $type,
                    $month,
                    $grid['paid'][$household->id][$month->format('Y-m')] ?? null,
                    isset($grid['pending'][$household->id][$month->format('Y-m')]),
                ))->all(),
            ])->all();
        } elseif ($type) {
            $list = $ledger->onceList($type);
            $data['rows'] = $list['households']->map(fn (Household $household) => [
                'household' => new HouseholdResource($household),
                ...$this->cell($type, null, $list['paid'][$household->id] ?? null, isset($list['pending'][$household->id])),
            ])->all();
        }

        return response()->json(['data' => $data]);
    }

    public function cashbook(Request $request, DuesLedger $ledger): JsonResponse
    {
        $years = $ledger->years();
        $year = in_array($request->integer('year'), $years, true) ? $request->integer('year') : $years[0];
        $book = $ledger->cashbook($year);

        return response()->json(['data' => [
            'years' => $years,
            'year' => $year,
            'opening' => $book['opening'],
            'total_in' => $book['total_in'],
            'total_out' => $book['total_out'],
            'closing' => $book['closing'],
            'rows' => collect($book['rows'])->map(fn (array $row) => [
                'month' => $row['month']->format('Y-m'),
                'month_label' => $row['month']->translatedFormat('F Y'),
                'in' => $row['in'],
                'out' => $row['out'],
                'balance' => $row['balance'],
            ]),
            'expenses' => ExpenseResource::collection(Expense::whereYear('spent_on', $year)->latest('spent_on')->get()),
        ]]);
    }

    /**
     * Payment state of one household for one dues type, used by the pay form and the admin record form.
     */
    public function status(Request $request, Household $household, DuesType $duesType): JsonResponse
    {
        $request->validate(['year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)]]);
        $year = $request->integer('year', now()->year);

        $paid = Payment::where('household_id', $household->id)->where('dues_type_id', $duesType->id)->get()->keyBy('period');
        $pending = PaymentSubmission::pendingPeriods($household->id, $duesType->id);

        $data = [
            'household' => new HouseholdResource($household),
            'dues_type' => new DuesTypeResource($duesType),
            'year' => $duesType->isMonthly() ? $year : null,
            'months' => [],
            'once' => null,
            'recent_submissions' => PaymentSubmissionResource::collection(
                PaymentSubmission::with('duesType')->where('household_id', $household->id)->latest()->take(5)->get()
            ),
        ];

        if ($duesType->isMonthly()) {
            $data['months'] = collect(range(1, 12))
                ->map(fn (int $m) => CarbonImmutable::create($year, $m, 1))
                ->map(fn (CarbonImmutable $month) => $this->cell(
                    $duesType,
                    $month,
                    $paid->get($month->format('Y-m')),
                    in_array($month->format('Y-m'), $pending, true),
                ))->all();
        } else {
            $data['once'] = $this->cell($duesType, null, $paid->get(''), in_array('', $pending, true));
        }

        return response()->json(['data' => $data]);
    }

    /**
     * @return array{period: string|null, period_label: string, status: string, payment: PaymentResource|null}
     */
    private function cell(DuesType $type, ?CarbonImmutable $month, ?Payment $payment, bool $isPending): array
    {
        $status = match (true) {
            $payment !== null => 'paid',
            $isPending => 'pending',
            $month !== null && ! $type->appliesToMonth($month) => 'not_applicable',
            default => 'unpaid',
        };

        return [
            'period' => $month?->format('Y-m'),
            'period_label' => $month ? $month->translatedFormat('F Y') : 'Sekali bayar',
            'status' => $status,
            'payment' => $payment ? new PaymentResource($payment) : null,
        ];
    }
}
