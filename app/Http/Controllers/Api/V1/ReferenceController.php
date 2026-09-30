<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\DuesTypeResource;
use App\Http\Resources\HouseholdResource;
use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Household;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceController extends Controller
{
    public function households(): AnonymousResourceCollection
    {
        return HouseholdResource::collection(Household::active()->ordered()->get());
    }

    public function duesTypes(): AnonymousResourceCollection
    {
        return DuesTypeResource::collection(DuesType::active()->orderBy('name')->get());
    }

    public function bankAccounts(): AnonymousResourceCollection
    {
        return BankAccountResource::collection(BankAccount::active()->ordered()->get());
    }
}
