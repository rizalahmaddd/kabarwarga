<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DuesTypeRequest;
use App\Models\DuesType;
use App\Models\PaymentSubmission;

class DuesTypeController extends Controller
{
    public function index()
    {
        $types = DuesType::withCount('payments')->withSum('payments', 'amount')->orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.dues-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.dues-types.form', ['duesType' => new DuesType(['frequency' => DuesType::MONTHLY, 'is_active' => true])]);
    }

    public function store(DuesTypeRequest $request)
    {
        $type = DuesType::create($request->payload());

        return redirect()->route('admin.dues-types.index')->with('status', "Jenis iuran \"{$type->name}\" ditambahkan.");
    }

    public function edit(DuesType $duesType)
    {
        return view('admin.dues-types.form', compact('duesType'));
    }

    public function update(DuesTypeRequest $request, DuesType $duesType)
    {
        $duesType->update($request->payload());

        return redirect()->route('admin.dues-types.index')->with('status', "Jenis iuran \"{$duesType->name}\" disimpan.");
    }

    public function destroy(DuesType $duesType)
    {
        if ($duesType->payments()->exists()) {
            return back()->with('warning', "\"{$duesType->name}\" sudah punya catatan pembayaran, jadi tidak bisa dihapus. Nonaktifkan saja.");
        }

        if (PaymentSubmission::pending()->where('dues_type_id', $duesType->id)->exists()) {
            return back()->with('warning', "Masih ada bukti bayar \"{$duesType->name}\" yang menunggu konfirmasi. Proses dulu di menu Konfirmasi Bayar.");
        }

        $duesType->delete();

        return redirect()->route('admin.dues-types.index')->with('status', "Jenis iuran \"{$duesType->name}\" dihapus.");
    }
}
