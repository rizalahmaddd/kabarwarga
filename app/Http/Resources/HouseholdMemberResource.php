<?php

namespace App\Http\Resources;

use App\Models\HouseholdMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HouseholdMember */
class HouseholdMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'household_id' => $this->household_id,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date?->toDateString(),
            'religion' => $this->religion,
            'education' => $this->education,
            'job' => $this->job,
            'marital_status' => $this->marital_status,
            'family_relation' => $this->family_relation,
            'occupancy_status' => $this->occupancy_status,
            'phone' => $this->phone,
        ];
    }
}
