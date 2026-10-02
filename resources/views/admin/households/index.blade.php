<x-layouts.admin title="Rumah">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
                Data Penduduk
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Daftar Rumah Warga</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Nomor rumah dan nama kepala keluarga tampil di halaman iuran warga.</p>
        </div>
        <div>
            <a href="{{ route('admin.households.create') }}" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5 shadow-xs">
                + Tambah Rumah Baru
            </a>
        </div>
    </div>

    @if ($households->isEmpty())
        <div class="sheet p-8 text-center mt-6">
            <h3 class="font-bold text-slate-800">Belum ada rumah terdaftar</h3>
            <p class="text-xs text-slate-500 mt-1">
                <a href="{{ route('admin.households.create') }}" class="font-bold text-daun underline">Tambahkan rumah pertama</a> untuk memulai.
            </p>
        </div>
    @else
        <div class="mb-4 max-w-sm">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                    </svg>
                </div>
                <input id="cari" type="search" class="input pl-10 text-sm" placeholder="Cari nomor rumah atau nama warga..." autocomplete="off"
                       data-filter="[data-household-row]" data-filter-empty="cari-kosong">
            </div>
        </div>

        <div class="sheet divide-y divide-slate-100 shadow-xs">
            @foreach ($households as $household)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 hover:bg-slate-50 transition-colors"
                     data-household-row data-search="{{ strtolower($household->number.' '.$household->head_name.' '.($household->occupancy_status ?? 'pemilik')) }}">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center font-extrabold text-xs px-2.5 py-0.5 rounded-lg bg-slate-900 text-white">
                                {{ $household->number }}
                            </span>
                            <span class="font-bold text-sm text-slate-800">{{ $household->head_name }}</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ ($household->occupancy_status ?? 'pemilik') === 'kontrak' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ($household->occupancy_status ?? 'pemilik') === 'kontrak' ? 'Kontrak' : 'Pemilik' }}
                            </span>
                            @if ($household->kk_number)
                                <span class="hidden sm:inline-block text-[11px] font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded" title="Nomor Kartu Keluarga">
                                    KK: {{ $household->kk_number }}
                                </span>
                            @endif
                            @unless ($household->is_active)
                                <span class="text-[10px] uppercase font-bold text-terakota bg-terakota-soft px-1.5 py-0.5 rounded">
                                    Nonaktif
                                </span>
                            @endunless
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mt-1.5">
                            <a href="{{ route('admin.households.members.index', $household) }}" class="inline-flex items-center gap-1 font-semibold {{ $household->members_count ? 'text-indigo-600 hover:underline' : 'text-slate-400 hover:text-slate-600 hover:underline' }}">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                {{ $household->members_count ? $household->members_count . ' penghuni' : 'Belum didata' }}
                            </a>
                            <span>·</span>
                            @if ($household->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $household->phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62'.substr($cleanPhone, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-emerald-700 font-semibold hover:underline">
                                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                    </svg>
                                    {{ $household->phone }}
                                </a>
                            @else
                                <span class="text-slate-400">Tanpa nomor HP</span>
                            @endif
                            <span>· {{ $household->payments_count }} riwayat bayar</span>
                            @if ($household->note)
                                <span class="italic text-slate-400 truncate max-w-xs">"{{ \Illuminate\Support\Str::limit($household->note, 50) }}"</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <a href="{{ route('admin.households.members.index', $household) }}" class="btn btn-sm btn-quiet text-xs font-bold text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100 border border-indigo-200/60">
                            Penghuni ({{ $household->members_count }})
                        </a>
                        <a href="{{ route('admin.home', ['rumah' => $household->id]) }}" class="btn btn-sm btn-primary text-xs font-bold">
                            + Catat Bayar
                        </a>
                        <a href="{{ route('admin.payments.index', ['rumah' => $household->id]) }}" class="btn btn-sm btn-quiet text-xs font-bold">
                            Riwayat
                        </a>
                        <a href="{{ route('admin.households.edit', $household) }}" class="btn btn-sm btn-quiet text-xs font-bold">
                            Ubah
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <p id="cari-kosong" class="mt-4 text-center text-sm text-slate-500 py-6 sheet" hidden>
            Tidak ada rumah yang cocok dengan pencarian.
        </p>
    @endif
</x-layouts.admin>

