<x-layouts.admin title="Jenis Iuran">
    @php
        $totalTerkumpul = $types->sum('payments_sum_amount') ?? 0;
        $totalTransaksi = $types->sum('payments_count') ?? 0;
        $totalAktif = $types->where('is_active', true)->count();
        $totalBulanan = $types->where('frequency', \App\Models\DuesType::MONTHLY)->count();
        $totalSekali = $types->where('frequency', '!=', \App\Models\DuesType::MONTHLY)->count();
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Jenis Iuran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola iuran rutin bulanan (kebersihan, satpam) atau iuran insidental (PHBN 17-an).</p>
        </div>
        <a href="{{ route('admin.dues-types.create') }}" class="btn btn-primary self-start sm:self-auto gap-2">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            <span>Tambah Jenis Iuran</span>
        </a>
    </div>

    @if ($types->isEmpty())
        <div class="p-12 bg-white rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <div class="size-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <p class="font-bold text-slate-800 text-base">Belum ada jenis iuran</p>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Buat jenis iuran pertama untuk mulai menagih dan mencatat pembayaran warga.</p>
            <a href="{{ route('admin.dues-types.create') }}" class="mt-4 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium text-xs hover:bg-emerald-700 shadow-sm transition">
                + Tambah Jenis Iuran Pertama
            </a>
        </div>
    @else
        {{-- Summary Stats Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                <div class="size-11 rounded-xl bg-emerald-50 text-daun border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="2" y2="22"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Terkumpul</p>
                    <p class="text-lg sm:text-xl font-black text-slate-900 tabular-nums leading-tight truncate">{{ rupiah($totalTerkumpul) }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $totalTransaksi }}x transaksi tercatat</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                <div class="size-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <polyline points="16 11 18 13 22 9"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Iuran Aktif</p>
                    <p class="text-lg sm:text-xl font-black text-slate-900 leading-tight truncate">{{ $totalAktif }} Jenis</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $types->count() - $totalAktif }} nonaktif</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                <div class="size-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
                    <p class="text-lg sm:text-xl font-black text-slate-900 leading-tight truncate">{{ $totalBulanan }} Bulanan</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $totalSekali }} Sekali Bayar</p>
                </div>
            </div>
        </div>

        {{-- Toolbar: Header & View Toggle (Grid / Table) --}}
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Iuran</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200/70 text-slate-700">{{ $types->count() }}</span>
            </div>

            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200/80 text-xs" id="dues-view-switcher">
                <button type="button" data-view="grid" class="view-btn px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition bg-white text-slate-900 shadow-2xs">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="14" rx="1"/>
                        <rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                    <span>Kartu</span>
                </button>
                <button type="button" data-view="table" class="view-btn px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1.5 text-slate-500 hover:text-slate-800 transition">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" x2="21" y1="6" y2="6"/>
                        <line x1="3" x2="21" y1="12" y2="12"/>
                        <line x1="3" x2="21" y1="18" y2="18"/>
                    </svg>
                    <span>Tabel</span>
                </button>
            </div>
        </div>

        {{-- 1. Compact Proportional Cards Grid --}}
        <div id="dues-grid-view" class="grid gap-4 sm:gap-5 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($types as $type)
                <div class="group bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-emerald-300 transition-all duration-200 flex flex-col justify-between p-4 sm:p-5 relative overflow-hidden">
                    <div>
                        {{-- Top Badge & Status --}}
                        <div class="flex items-center justify-between gap-2">
                            @if ($type->isMonthly())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-emerald-50 text-daun-dark border border-emerald-200/80">
                                    <span class="size-1.5 rounded-full bg-daun"></span>
                                    Bulanan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-amber-50 text-amber-800 border border-amber-200/80">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                                    Sekali Bayar
                                </span>
                            @endif

                            @if ($type->is_active)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50/50 px-2 py-0.5 rounded-md">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        {{-- Title & Nominal --}}
                        <h2 class="font-bold text-slate-900 text-base mt-3 line-clamp-1 group-hover:text-emerald-800 transition-colors" title="{{ $type->name }}">
                            {{ $type->name }}
                        </h2>

                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight tabular-nums">{{ rupiah($type->amount) }}</span>
                            <span class="text-xs font-semibold text-slate-400">/ {{ $type->isMonthly() ? 'bulan' : 'rumah' }}</span>
                        </div>

                        {{-- Compact Info Box --}}
                        <div class="mt-3.5 bg-slate-50/80 rounded-xl p-3 border border-slate-100/90 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <svg class="size-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    {{ $type->isMonthly() ? 'Mulai:' : 'Batas bayar:' }}
                                </span>
                                <span class="font-semibold text-slate-800">
                                    @if ($type->isMonthly())
                                        {{ $type->starts_on ? $type->starts_on->translatedFormat('F Y') : '-' }}
                                    @else
                                        {{ $type->due_on ? $type->due_on->translatedFormat('d M Y') : 'Fleksibel' }}
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-slate-600 pt-1.5 border-t border-slate-200/60">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <svg class="size-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" x2="12" y1="2" y2="22"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    Terkumpul:
                                </span>
                                <span class="font-bold text-slate-800 tabular-nums">
                                    {{ rupiah($type->payments_sum_amount ?? 0) }}
                                    <span class="text-[11px] font-normal text-slate-400">({{ $type->payments_count }}x)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <a href="{{ route('admin.payments.ledger', ['jenis' => $type->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition active:scale-95 shadow-2xs">
                            <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>Buku Iuran</span>
                        </a>
                        <a href="{{ route('admin.dues-types.edit', $type) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-xs font-semibold hover:bg-emerald-100 transition active:scale-95">
                            <svg class="size-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                            <span>Ubah</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 2. Compact Table View Alternative --}}
        <div id="dues-table-view" class="hidden bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="px-5 py-3.5">Nama Jenis Iuran</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Tarif</th>
                            <th class="px-4 py-3.5">Periode / Batas</th>
                            <th class="px-4 py-3.5">Total Terkumpul</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($types as $type)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-slate-900 text-sm whitespace-nowrap">
                                    {{ $type->name }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($type->isMonthly())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-daun-dark border border-emerald-200">
                                            <span class="size-1.5 rounded-full bg-daun"></span>
                                            Bulanan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="size-1.5 rounded-full bg-amber-500"></span>
                                            Sekali Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                    {{ rupiah($type->amount) }}
                                    <span class="font-normal text-slate-400 text-[11px]">/ {{ $type->isMonthly() ? 'bln' : 'rumah' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                                    @if ($type->isMonthly())
                                        {{ $type->starts_on ? $type->starts_on->translatedFormat('F Y') : '-' }}
                                    @else
                                        {{ $type->due_on ? $type->due_on->translatedFormat('d M Y') : 'Fleksibel' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 tabular-nums">{{ rupiah($type->payments_sum_amount ?? 0) }}</span>
                                    <span class="text-slate-400 text-[11px] block">{{ $type->payments_count }}x transaksi</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($type->is_active)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="{{ route('admin.payments.ledger', ['jenis' => $type->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-2xs">
                                            <span>Buku Iuran</span>
                                        </a>
                                        <a href="{{ route('admin.dues-types.edit', $type) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-xs font-semibold hover:bg-emerald-100 transition">
                                            Ubah
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const switcher = document.getElementById('dues-view-switcher');
                const gridView = document.getElementById('dues-grid-view');
                const tableView = document.getElementById('dues-table-view');
                if (!switcher || !gridView || !tableView) return;

                const buttons = switcher.querySelectorAll('.view-btn');
                const storedView = localStorage.getItem('dues_types_view_pref') || 'grid';

                function setView(view) {
                    if (view === 'table') {
                        gridView.classList.add('hidden');
                        tableView.classList.remove('hidden');
                    } else {
                        tableView.classList.add('hidden');
                        gridView.classList.remove('hidden');
                    }

                    buttons.forEach(btn => {
                        const isMatch = btn.getAttribute('data-view') === view;
                        if (isMatch) {
                            btn.className = 'view-btn px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition bg-white text-slate-900 shadow-2xs';
                        } else {
                            btn.className = 'view-btn px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1.5 text-slate-500 hover:text-slate-800 transition';
                        }
                    });

                    localStorage.setItem('dues_types_view_pref', view);
                }

                setView(storedView);

                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        setView(this.getAttribute('data-view'));
                    });
                });
            });
        </script>
    @endif
</x-layouts.admin>
