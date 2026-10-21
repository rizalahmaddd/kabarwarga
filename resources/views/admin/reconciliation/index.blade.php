<x-layouts.admin title="Cocokkan Mutasi">
    <div class="mb-5">
        <a href="{{ route('admin.submissions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 mb-2">← Konfirmasi Bayar</a>
        <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Cocokkan Mutasi Rekening</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Tempel mutasi dari m-banking/internet banking atau unggah CSV-nya. Sistem mencocokkan nominal (termasuk kode unik) dengan {{ $pendingCount }} bukti bayar yang menunggu. Mutasi tidak disimpan.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.reconciliation.check') }}" enctype="multipart/form-data" class="sheet p-5 space-y-4">
        @csrf
        <div>
            <label for="statement" class="field-label">Teks mutasi</label>
            <textarea id="statement" name="statement" rows="6" class="input font-mono text-xs" placeholder="15/10 TRSF E-BANKING CR 1510/FTSCY/WS95051 50,007.00 CR&#10;16/10 SWITCHING CR TRANSFER DR 014 BUDI 75.012,00" @error('statement') aria-invalid="true" @enderror>{{ old('statement', request('statement')) }}</textarea>
            @error('statement') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label for="file" class="field-label">atau file CSV/TXT dari bank</label>
                <input id="file" name="file" type="file" accept=".csv,.txt,text/csv,text/plain" class="input py-2 text-sm" @error('file') aria-invalid="true" @enderror>
                @error('file') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <button class="btn btn-primary" data-busy="Mencocokkan...">Cocokkan</button>
        </div>
        <p class="text-xs text-slate-500">Baris debet (DB/DEBET) diabaikan. Hasil tetap perlu dicek sekilas sebelum dikonfirmasi.</p>
    </form>

    @if ($result)
        @php
            $matches = $result['matches'];
            $found = $matches->where('status', 'matched');
        @endphp

        <form method="POST" action="{{ route('admin.reconciliation.approve') }}" class="mt-6 space-y-4" data-confirm="Konfirmasi lunas semua pengajuan yang dicentang?">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-lg text-slate-900">Bukti bayar yang menunggu</h2>
                <div class="flex gap-2 text-xs">
                    <span class="badge badge-success">{{ $found->count() }} cocok</span>
                    <span class="badge badge-warning">{{ $matches->where('status', 'ambiguous')->count() }} perlu dicek</span>
                    <span class="badge badge-neutral">{{ $matches->where('status', 'missing')->count() }} belum ada di mutasi</span>
                </div>
            </div>

            <div class="sheet overflow-hidden divide-y divide-slate-100">
                @forelse ($matches as $match)
                    @php $submission = $match['submission']; @endphp
                    <label class="p-4 flex items-start gap-3 {{ $match['status'] === 'missing' ? 'opacity-70' : 'cursor-pointer hover:bg-slate-50' }}">
                        <input type="checkbox" name="approve[]" value="{{ $submission->id }}" class="mt-1 size-4 rounded border-slate-300"
                               @checked($match['status'] === 'matched') @disabled($match['status'] === 'missing')>
                        <span class="flex-1 min-w-0">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-900">Rumah {{ $submission->household->number }}</span>
                                <span class="text-sm text-slate-600">{{ $submission->duesType->name }} · {{ $submission->periodsLabel() }}</span>
                                @if ($match['status'] === 'matched')
                                    <span class="badge badge-success">Cocok</span>
                                @elseif ($match['status'] === 'ambiguous')
                                    <span class="badge badge-warning">Nominal sama dengan pengajuan/mutasi lain</span>
                                @else
                                    <span class="badge badge-neutral">Belum ada di mutasi</span>
                                @endif
                            </span>
                            <span class="block mt-1 text-sm">
                                <b class="tabular-nums">{{ rupiah($submission->transferTotal()) }}</b>
                                <span class="text-slate-500">ke {{ $submission->bankAccount?->bank_name ?? '-' }} · #{{ $submission->code }}</span>
                            </span>
                            @if ($match['row'])
                                <span class="block mt-1 font-mono text-[11px] text-slate-500 break-all">{{ $match['row']['line'] }}</span>
                                <input type="hidden" name="paid_on[{{ $submission->id }}]" value="{{ $match['row']['date'] }}">
                            @endif
                        </span>
                    </label>
                @empty
                    <p class="p-6 text-sm text-slate-500 text-center">Tidak ada bukti bayar yang menunggu konfirmasi.</p>
                @endforelse
            </div>

            @error('approve') <span class="field-error">{{ $message }}</span> @enderror
            @if ($matches->whereIn('status', ['matched', 'ambiguous'])->isNotEmpty())
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button class="btn btn-primary" data-busy="Mengonfirmasi...">Konfirmasi yang dicentang</button>
                    <span class="text-xs text-slate-500">Semua bulan di pengajuan dicatat lunas dengan tanggal dari mutasi. Untuk terima sebagian, pakai halaman Konfirmasi Bayar.</span>
                </div>
            @endif
        </form>

        @if ($result['unmatched']->isNotEmpty())
            <section class="mt-8">
                <h2 class="font-bold text-lg text-slate-900">Uang masuk tanpa bukti bayar</h2>
                <p class="text-xs text-slate-500 mt-1">Mungkin warga transfer tanpa mengirim bukti. Tebakan rumah diambil dari 3 digit terakhir (kode unik).</p>
                <div class="mt-3 sheet overflow-hidden divide-y divide-slate-100">
                    @foreach ($result['unmatched'] as $item)
                        <div class="p-4 text-sm">
                            <p class="font-mono text-[11px] text-slate-600 break-all">{{ $item['row']['line'] }}</p>
                            <p class="mt-1">
                                @foreach ($item['row']['amounts'] as $amount)
                                    <span class="font-semibold tabular-nums mr-2">{{ rupiah($amount) }}</span>
                                @endforeach
                                @foreach ($item['households'] as $household)
                                    <a href="{{ route('admin.home', ['rumah' => $household->id]) }}" class="badge badge-neutral no-underline mr-1">Rumah {{ $household->number }}?</a>
                                @endforeach
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</x-layouts.admin>
