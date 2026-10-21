<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists($request->query('status'), Letter::STATUSES) ? $request->query('status') : Letter::PENDING;

        return view('admin.letters.index', [
            'status' => $status,
            'letters' => Letter::with(['household', 'reviewer'])
                ->where('status', $status)
                ->when($status === Letter::PENDING, fn ($q) => $q->oldest(), fn ($q) => $q->latest('reviewed_at')->latest('id'))
                ->paginate(20)
                ->withQueryString(),
            'counts' => Letter::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function approve(Request $request, Letter $letter)
    {
        if (! $letter->approve($request->user())) {
            return back()->with('warning', 'Pengajuan surat ini sudah diproses sebelumnya.');
        }

        return back()->with('status', "Surat {$letter->number} untuk {$letter->person['name']} disetujui.")
            ->with('letter_print_url', route('admin.letters.print', $letter));
    }

    public function reject(Request $request, Letter $letter)
    {
        $data = $request->validateWithBag("reject{$letter->id}", [
            'reject_reason' => ['required', 'string', 'max:255'],
        ], ['reject_reason.required' => 'Tulis alasan penolakan supaya warga tahu yang harus diperbaiki.']);

        if (! $letter->reject($data['reject_reason'], $request->user())) {
            return back()->with('warning', 'Pengajuan surat ini sudah diproses sebelumnya.');
        }

        return back()->with('status', "Pengajuan surat {$letter->person['name']} ditolak.");
    }

    public function print(Letter $letter)
    {
        abort_unless($letter->isApproved(), 404);

        return view('letters.print', ['letter' => $letter->load('household')]);
    }
}
