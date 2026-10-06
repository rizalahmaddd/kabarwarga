<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdRequest;
use App\Models\Household;
use App\Models\PaymentSubmission;

class HouseholdController extends Controller
{
    public function index()
    {
        $households = Household::withCount(['payments', 'members'])->ordered()->get();

        return view('admin.households.index', compact('households'));
    }

    public function export()
    {
        $households = Household::withCount('members')->ordered()->get();
        $filename = 'daftar-rumah-warga-'.now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($households) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nomor Rumah', 'Nama Kepala Keluarga', 'Status Hunian', 'Nomor KK', 'No. WhatsApp / HP', 'Jumlah Anggota Keluarga', 'Status Rumah', 'Catatan']);

            foreach ($households as $household) {
                fputcsv($handle, [
                    $household->number,
                    $household->head_name,
                    ucfirst($household->occupancy_status ?? 'pemilik'),
                    $household->kk_number ?? '-',
                    $household->phone ?? '-',
                    $household->members_count,
                    $household->is_active ? 'Aktif' : 'Nonaktif',
                    $household->note ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create()
    {
        return view('admin.households.form', ['household' => new Household(['is_active' => true])]);
    }

    public function store(HouseholdRequest $request)
    {
        $household = Household::create($request->payload());

        return redirect()->route('admin.households.index')->with('status', "Rumah {$household->number} ditambahkan.");
    }

    public function edit(Household $household)
    {
        return view('admin.households.form', compact('household'));
    }

    public function update(HouseholdRequest $request, Household $household)
    {
        $household->update($request->payload());

        return redirect()->route('admin.households.index')->with('status', "Data rumah {$household->number} disimpan.");
    }

    public function destroy(Household $household)
    {
        if ($household->payments()->exists()) {
            return back()->with('warning', "Rumah {$household->number} sudah punya catatan iuran, jadi tidak bisa dihapus. Nonaktifkan saja kalau sudah pindah.");
        }

        if (PaymentSubmission::pending()->where('household_id', $household->id)->exists()) {
            return back()->with('warning', "Rumah {$household->number} masih punya bukti bayar yang menunggu konfirmasi. Proses dulu di menu Konfirmasi Bayar.");
        }

        $household->delete();

        return redirect()->route('admin.households.index')->with('status', "Rumah {$household->number} dihapus.");
    }
}
