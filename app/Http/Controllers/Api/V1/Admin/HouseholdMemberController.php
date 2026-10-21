<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdMemberRequest;
use App\Http\Resources\HouseholdMemberResource;
use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseholdMemberController extends Controller
{
    public function index(Household $household): AnonymousResourceCollection
    {
        return HouseholdMemberResource::collection($household->members()->get());
    }

    public function store(HouseholdMemberRequest $request, Household $household): JsonResponse
    {
        $member = $household->members()->create($request->validated());

        return (new HouseholdMemberResource($member))
            ->additional(['message' => "Anggota keluarga {$member->name} ditambahkan."])
            ->response()
            ->setStatusCode(201);
    }

    public function update(HouseholdMemberRequest $request, Household $household, HouseholdMember $member): HouseholdMemberResource
    {
        abort_unless($member->household_id === $household->id, 404);

        $member->update($request->validated());

        return (new HouseholdMemberResource($member))->additional(['message' => "Data {$member->name} disimpan."]);
    }

    public function destroy(Household $household, HouseholdMember $member): JsonResponse
    {
        abort_unless($member->household_id === $household->id, 404);

        $member->delete();

        return response()->json(['message' => "Anggota keluarga {$member->name} dihapus."]);
    }
}
