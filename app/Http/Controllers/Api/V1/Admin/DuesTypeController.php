<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DuesTypeRequest;
use App\Http\Resources\DuesTypeResource;
use App\Models\DuesType;
use App\Models\PaymentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DuesTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DuesTypeResource::collection(
            DuesType::withCount('payments')->withSum('payments', 'amount')->orderByDesc('is_active')->orderBy('name')->get()
        );
    }

    public function store(DuesTypeRequest $request): JsonResponse
    {
        $type = DuesType::create($request->payload());

        return (new DuesTypeResource($type))
            ->additional(['message' => "Jenis iuran \"{$type->name}\" ditambahkan."])
            ->response()
            ->setStatusCode(201);
    }

    public function show(DuesType $duesType): DuesTypeResource
    {
        return new DuesTypeResource($duesType->loadCount('payments')->loadSum('payments', 'amount'));
    }

    public function update(DuesTypeRequest $request, DuesType $duesType): DuesTypeResource
    {
        $duesType->update($request->payload());

        return (new DuesTypeResource($duesType))->additional(['message' => "Jenis iuran \"{$duesType->name}\" disimpan."]);
    }

    public function destroy(DuesType $duesType): JsonResponse
    {
        if ($duesType->payments()->exists()) {
            return response()->json(['message' => "\"{$duesType->name}\" sudah punya catatan pembayaran, jadi tidak bisa dihapus. Nonaktifkan saja."], 409);
        }

        if (PaymentSubmission::pending()->where('dues_type_id', $duesType->id)->exists()) {
            return response()->json(['message' => "Masih ada bukti bayar \"{$duesType->name}\" yang menunggu konfirmasi. Proses dulu di menu Konfirmasi Bayar."], 409);
        }

        $duesType->delete();

        return response()->json(['message' => "Jenis iuran \"{$duesType->name}\" dihapus."]);
    }
}
