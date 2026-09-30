<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Support\PaymentRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'household_id' => ['nullable', 'integer'],
            'dues_type_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $payments = Payment::with(['household', 'duesType', 'recorder'])
            ->when($request->integer('household_id'), fn ($q, $id) => $q->where('household_id', $id))
            ->when($request->integer('dues_type_id'), fn ($q, $id) => $q->where('dues_type_id', $id))
            ->latest('paid_on')
            ->latest('id')
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request, PaymentRecorder $recorder): JsonResponse
    {
        $type = $request->duesType();
        $result = $recorder->record($type, $request->validated(), $request->user());

        $message = $result['created']
            ? "Tercatat: {$type->name}, ".implode(', ', $result['created']).'.'
            : 'Tidak ada yang baru dicatat.';
        if ($result['skipped']) {
            $message .= ' Sudah tercatat sebelumnya: '.implode(', ', $result['skipped']).'.';
        }

        return response()->json([
            'message' => $message,
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ], $result['created'] ? 201 : 200);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $label = "{$payment->duesType->name} rumah {$payment->household->number} ({$payment->periodLabel()})";
        $payment->delete();

        return response()->json(['message' => "Catatan {$label} dihapus."]);
    }
}
