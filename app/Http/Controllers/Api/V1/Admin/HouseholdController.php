<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdRequest;
use App\Http\Resources\HouseholdResource;
use App\Models\Household;
use App\Models\PaymentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseholdController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return HouseholdResource::collection(Household::withCount(['payments', 'members'])->ordered()->get());
    }

    public function store(HouseholdRequest $request): JsonResponse
    {
        $household = Household::create($request->payload());

        return (new HouseholdResource($household))
            ->additional(['message' => "Rumah {$household->number} ditambahkan."])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Household $household): HouseholdResource
    {
        return new HouseholdResource($household->loadCount(['payments', 'members']));
    }

    public function update(HouseholdRequest $request, Household $household): HouseholdResource
    {
        $household->update($request->payload());

        return (new HouseholdResource($household))->additional(['message' => "Data rumah {$household->number} disimpan."]);
    }

    public function destroy(Household $household): JsonResponse
    {
        if ($household->payments()->exists()) {
            return response()->json(['message' => "Rumah {$household->number} sudah punya catatan iuran, jadi tidak bisa dihapus. Nonaktifkan saja kalau sudah pindah."], 409);
        }

        if (PaymentSubmission::pending()->where('household_id', $household->id)->exists()) {
            return response()->json(['message' => "Rumah {$household->number} masih punya bukti bayar yang menunggu konfirmasi. Proses dulu di menu Konfirmasi Bayar."], 409);
        }

        $household->delete();

        return response()->json(['message' => "Rumah {$household->number} dihapus."]);
    }
}
