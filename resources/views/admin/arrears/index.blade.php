<x-layouts.admin title="Tunggakan Iuran">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Tunggakan Iuran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Tagihan yang belum lunas sampai {{ now()->translatedFormat('F Y') }}. Bulan yang buktinya sedang menunggu konfirmasi tidak dihitung.
            </p>
        </div>
        <form method="GET" class="flex items-end gap-2">
            <div>
                <label for="jenis" class="field-label text-xs">Jenis iuran</label>
                <select id="jenis" name="jenis" class="input min-h-10 py-1.5 text-sm" data-autosubmit>
                    <option value="">Semua jenis</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <noscript><button class="btn btn-sm btn-quiet">Terapkan</button></noscript>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="sheet p-4">
            <p class="text-xs font-semibold text-slate-500">Rumah menunggak</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ $rows->count() }}</p>
        </div>
        <div class="sheet p-4">
            <p class="text-xs font-semibold text-slate-500">Total tunggakan</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-terakota">{{ rupiah($rows->sum('total')) }}</p>
        </div>
    </div>

    <div class="sheet overflow-hidden divide-y divide-slate-100">
        @forelse ($rows as $row)
            @php
                $household = $row['household'];
                $lines = $row['items']->map(fn ($item) => "- {$item['type']->name}: ".\App\Support\Arrears::periodsLabel($item['periods']).' ('.rupiah($item['amount']).')')->implode("\n");
                $waText = "Halo Bpk/Ibu {$household->head_name} (Rumah {$household->number}),\n"
                    ."Kami informasikan iuran yang belum tercatat lunas sampai ".now()->translatedFormat('F Y').":\n{$lines}\n"
                    ."Total: ".rupiah($row['total'])."\n\n"
                    ."Cek tagihan & bayar online: ".$household->portalUrl()."\n"
                    ."Kalau sudah membayar, mohon abaikan pesan ini. Terima kasih 🙏\n- Pengurus ".setting('site_name');
            @endphp
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-900">
                        Rumah {{ $household->number }} <span class="font-normal text-slate-600">· {{ $household->head_name }}</span>
                    </p>
                    <ul class="mt-1.5 space-y-1 text-sm text-slate-600">
                        @foreach ($row['items'] as $item)
                            <li>
                                <span class="font-semibold text-slate-800">{{ $item['type']->name }}</span>
                                <span class="text-slate-400">·</span>
                                {{ count($item['periods']) > 1 ? count($item['periods']).' bulan: ' : '' }}{{ \App\Support\Arrears::periodsLabel($item['periods']) }}
                                <span class="tabular-nums text-slate-500">({{ rupiah($item['amount']) }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="shrink-0 flex flex-col items-start sm:items-end gap-2">
                    <span class="text-lg font-bold tabular-nums text-terakota">{{ rupiah($row['total']) }}</span>
                    <div class="flex items-center gap-1.5">
                        @php $first = $row['items']->first(); @endphp
                        <a href="{{ route('admin.home', ['rumah' => $household->id, 'jenis' => $first['type']->id, 'tahun' => $first['periods'][0] !== '' ? substr($first['periods'][0], 0, 4) : null, 'bulan' => array_filter($first['periods'])]) }}"
                           class="btn btn-sm btn-quiet text-xs">Catat bayar</a>
                        @if ($household->phone)
                            <a href="https://wa.me/{{ clean_phone($household->phone) }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener noreferrer"
                               class="btn btn-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs">
                                Ingatkan via WA
                            </a>
                        @else
                            <span class="text-[11px] text-slate-400">Nomor HP belum ada</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <p class="font-bold text-slate-800">Tidak ada tunggakan</p>
                <p class="text-sm text-slate-500 mt-1">Semua rumah aktif sudah lunas sampai bulan ini.</p>
            </div>
        @endforelse
    </div>
</x-layouts.admin>
