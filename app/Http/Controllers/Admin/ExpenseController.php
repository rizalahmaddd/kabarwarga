<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest('spent_on')->latest('id')->paginate(25);

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('admin.expenses.form', ['expense' => new Expense(['spent_on' => now()])]);
    }

    public function store(ExpenseRequest $request)
    {
        $expense = Expense::create($request->validated() + ['recorded_by' => $request->user()->id]);

        return redirect()->route('admin.expenses.index')->with('status', "Pengeluaran \"{$expense->description}\" dicatat.");
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.form', compact('expense'));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()->route('admin.expenses.index')->with('status', 'Pengeluaran disimpan.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('status', "Pengeluaran \"{$expense->description}\" dihapus.");
    }
}
