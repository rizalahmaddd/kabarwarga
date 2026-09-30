<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return ExpenseResource::collection(
            Expense::with('recorder')->latest('spent_on')->latest('id')->paginate($request->integer('per_page', 25))->withQueryString()
        );
    }

    public function store(ExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create($request->validated() + ['recorded_by' => $request->user()->id]);

        return (new ExpenseResource($expense))
            ->additional(['message' => "Pengeluaran \"{$expense->description}\" dicatat."])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($expense->load('recorder'));
    }

    public function update(ExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense->update($request->validated());

        return (new ExpenseResource($expense))->additional(['message' => 'Pengeluaran disimpan.']);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => "Pengeluaran \"{$expense->description}\" dihapus."]);
    }
}
