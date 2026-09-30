<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Storage;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('admin.bank-accounts.index', ['accounts' => BankAccount::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.bank-accounts.form', ['account' => new BankAccount(['is_active' => true])]);
    }

    public function store(BankAccountRequest $request)
    {
        $account = BankAccount::create($request->payload() + ['position' => (int) BankAccount::max('position') + 1]);

        return redirect()->route('admin.bank-accounts.index')->with('status', "Rekening {$account->label()} ditambahkan.");
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('admin.bank-accounts.form', ['account' => $bankAccount]);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount)
    {
        $bankAccount->update($request->payload());

        return redirect()->route('admin.bank-accounts.index')->with('status', "Rekening {$bankAccount->label()} disimpan.");
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->qris_path) {
            Storage::disk('public')->delete($bankAccount->qris_path);
        }
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')->with('status', "Rekening {$bankAccount->label()} dihapus.");
    }
}
