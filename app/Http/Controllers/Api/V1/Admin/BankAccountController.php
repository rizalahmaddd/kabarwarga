<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class BankAccountController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BankAccountResource::collection(BankAccount::ordered()->get());
    }

    public function store(BankAccountRequest $request): JsonResponse
    {
        $account = BankAccount::create($request->payload() + ['position' => (int) BankAccount::max('position') + 1]);

        return (new BankAccountResource($account))
            ->additional(['message' => "Rekening {$account->label()} ditambahkan."])
            ->response()
            ->setStatusCode(201);
    }

    public function show(BankAccount $bankAccount): BankAccountResource
    {
        return new BankAccountResource($bankAccount);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount): BankAccountResource
    {
        $bankAccount->update($request->payload());

        return (new BankAccountResource($bankAccount))->additional(['message' => "Rekening {$bankAccount->label()} disimpan."]);
    }

    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        if ($bankAccount->qris_path) {
            Storage::disk('public')->delete($bankAccount->qris_path);
        }
        $bankAccount->delete();

        return response()->json(['message' => "Rekening {$bankAccount->label()} dihapus."]);
    }
}
