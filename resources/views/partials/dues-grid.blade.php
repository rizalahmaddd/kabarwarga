@php
    $admin = $admin ?? false;
    $pending = $pending ?? [];
    $today = now()->startOfMonth();
    $currentPeriodStr = $today->format('Y-m');
@endphp

{{-- Dues Summary & Control Bar --}}
<div class="mt-6 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex-1">
        <div class="flex items-center gap-2">
            <span class="font-bold text-slate-900 text-lg sm:text-xl">{{ rupiah($type->amount) }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-daun-dark border border-emerald-200">
                {{ $type->isMonthly() ? 'per rumah / bulan' : 'sekali bayar' }}
            </span>
        </div>
        @if (! $type->isMonthly() && $type->due_on)
            <p class="text-xs text-amber-700 mt-1 font-medium">Batas pembayaran: {{ $type->due_on->translatedFormat('j F Y') }}</p>
        @endif
        @if ($type->description)
            <p class="text-xs text-slate-500 mt-1">{{ $type->description }}</p>
        @endif
    </div>

    {{-- View Switcher (Cards vs Matrix) --}}
    @if ($type->isMonthly() && $households->isNotEmpty())
        <div class="inline-flex p-1 rounded-xl bg-slate-200/70 border border-slate-200 shrink-0 self-start sm:self-center" role="tablist">
            <button type="button" data-view-switcher="cards" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="7" height="7" x="3" y="3" rx="1"/>
                    <rect width="7" height="7" x="14" y="3" rx="1"/>
                    <rect width="7" height="7" x="14" y="14" rx="1"/>
                    <rect width="7" height="7" x="3" y="14" rx="1"/>
                </svg>
                <span>Kartu Rumah</span>
            </button>
            <button type="button" data-view-switcher="matrix" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                    <line x1="3" x2="21" y1="9" y2="9"/>
                    <line x1="3" x2="21" y1="15" y2="15"/>
                    <line x1="9" x2="9" y1="3" y2="21"/>
                </svg>
                <span>Tabel Lengkap</span>
            </button>
        </div>
    @endif
</div>

{{-- Search Box --}}
@if ($households->count() > 3)
    <div class="mb-5">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                </svg>
            </div>
            <input id="cari-rumah" type="search" class="input pl-10 text-sm" placeholder="Ketik nomor rumah (mis. A-1) atau nama kepala keluarga..." autocomplete="off"
                   value="{{ request('cari') }}" data-filter="[data-household-row]" data-filter-empty="cari-kosong">
        </div>
    </div>
@endif

@if ($households->isEmpty())
    <div class="sheet p-8 text-center">
        <h3 class="font-bold text-slate-800">Belum ada data rumah warga</h3>
        <p class="text-xs text-slate-500 mt-1">
            @if ($admin)
                <a href="{{ route('admin.households.create') }}" class="font-bold text-daun underline">Tambahkan rumah warga</a> terlebih dahulu supaya iuran bisa dicatat.
            @else
                Pengurus belum memasukkan daftar rumah warga.
            @endif
        </p>
    </div>
@elseif ($type->isMonthly())
    {{-- VIEW MODE 1: MOBILE CARDS VIEW (Clean & Thumb-friendly) --}}
    <div data-view-target="cards" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($households as $household)
            @php
                $paidThisMonth = isset($paid[$household->id][$currentPeriodStr]);
                $pendingThisMonth = isset($pending[$household->id][$currentPeriodStr]);
                $paidCount = 0;
                foreach ($months as $m) {
                    if (isset($paid[$household->id][$m->format('Y-m')])) {
                        $paidCount++;
                    }
                }
            @endphp
            <div class="sheet p-4 transition-all hover:border-slate-300" data-household-row data-search="{{ strtolower($household->number.' '.$household->head_name) }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center font-extrabold text-base px-2.5 py-0.5 rounded-lg bg-slate-900 text-white shadow-2xs">
                                {{ $household->number }}
                            </span>
                            @unless ($household->is_active)
                                <span class="text-[10px] uppercase font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">Nonaktif</span>
                            @endunless
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mt-1.5 truncate">{{ $household->head_name }}</h4>
                    </div>

                    {{-- Current Month Status Badge --}}
                    @if ($paidThisMonth)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-daun-dark bg-daun-soft px-2.5 py-1 rounded-full shrink-0">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Lunas {{ now()->translatedFormat('M') }}
                        </span>
                    @elseif ($pendingThisMonth)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-900 bg-amber-50 border border-amber-300 px-2.5 py-1 rounded-full shrink-0">
                            Dicek {{ now()->translatedFormat('M') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-full shrink-0">
                            <span class="size-2 rounded-full bg-amber-500"></span>
                            Belum {{ now()->translatedFormat('M') }}
                        </span>
                    @endif
                </div>

                {{-- Month Progress Chips --}}
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                        <span>Status {{ $year }}:</span>
                        <span class="font-semibold text-slate-700">{{ $paidCount }} / {{ $months->count() }} bulan lunas</span>
                    </div>
                    <div class="grid grid-cols-6 gap-1">
                        @foreach ($months as $month)
                            @php
                                $p = $month->format('Y-m');
                                $isPaid = isset($paid[$household->id][$p]);
                                $isPending = ! $isPaid && isset($pending[$household->id][$p]);
                                $isNow = $month->equalTo($today);
                            @endphp
                            <div class="text-center py-1 rounded-md text-[10px] font-bold border transition-colors {{ $isPaid ? 'bg-emerald-50 border-emerald-300 text-daun-dark' : ($isPending ? 'bg-amber-50 border-amber-400 border-dashed text-amber-900' : ($isNow ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-400')) }}"
                                 title="{{ $month->translatedFormat('F Y') }}: {{ $isPaid ? 'Lunas' : ($isPending ? 'Sedang dicek pengurus' : 'Belum') }}">
                                {{ $month->translatedFormat('M') }}
                                <span class="block text-[8px] leading-tight {{ $isPaid ? 'text-daun' : ($isPending || $isNow ? 'text-amber-700' : 'text-slate-300') }}">
                                    {{ $isPaid ? '✓' : ($isPending ? '…' : '–') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-3.5 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    @if ($admin)
                        <a href="{{ route('admin.home', ['rumah' => $household->id, 'jenis' => $type->id, 'tahun' => $year]) }}"
                           class="btn btn-sm btn-primary w-full text-xs font-bold">
                            + Catat Pembayaran
                        </a>
                    @else
                        <a href="{{ route('pay.create', ['rumah' => $household->id, 'jenis' => $type->id]) }}" class="btn btn-sm btn-primary flex-1 text-xs font-bold">
                            Bayar Online
                        </a>
                        @if (setting('treasurer_contact'))
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', setting('treasurer_contact'));
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62'.substr($cleanPhone, 1);
                                }
                                $waText = rawurlencode("Halo Bendahara ".setting('site_name').", saya ingin konfirmasi status iuran untuk Rumah {$household->number} ({$household->head_name}).");
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
                               class="btn btn-sm btn-quiet flex-1 text-xs font-bold text-slate-700 hover:text-daun flex items-center justify-center gap-1.5">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                                Tanya Bendahara
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- VIEW MODE 2: SPREADSHEET MATRIX (Accountant View) --}}
    <div data-view-target="matrix" class="sheet relative overflow-x-auto shadow-xs" tabindex="0" role="region" data-scroll-current aria-label="Tabel iuran {{ $type->name }} {{ $year }}">
        <table class="w-full text-sm border-collapse">
            <caption class="sr-only">Status iuran {{ $type->name }} tahun {{ $year }} per rumah</caption>
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <th scope="col" class="sticky left-0 z-10 bg-slate-50 text-left px-3.5 py-3 min-w-44 font-bold border-r border-slate-200/80">Rumah & Warga</th>
                    @foreach ($months as $month)
                        @php $isCurrent = $month->equalTo($today); @endphp
                        <th scope="col" class="px-2 py-3 font-bold text-center min-w-16 {{ $isCurrent ? 'bg-emerald-100/70 text-daun-dark' : '' }}" @if ($isCurrent) data-current-month @endif>
                            <abbr title="{{ $month->translatedFormat('F') }}" class="no-underline">{{ $month->translatedFormat('M') }}</abbr>
                            @if ($isCurrent)
                                <span class="block text-[9px] uppercase font-bold text-daun">Bulan Ini</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($households as $household)
                    <tr class="hover:bg-slate-50/80 transition-colors" data-household-row data-search="{{ strtolower($household->number.' '.$household->head_name) }}">
                        <th scope="row" class="sticky left-0 z-10 bg-white group-hover:bg-slate-50 text-left font-normal px-3.5 py-2.5 border-r border-slate-200/80">
                            <span class="font-extrabold text-slate-900">{{ $household->number }}</span>
                            <span class="block text-xs text-slate-500 truncate max-w-40 font-medium">{{ $household->head_name }}</span>
                        </th>
                        @foreach ($months as $month)
                            @php
                                $period = $month->format('Y-m');
                                $payment = $paid[$household->id][$period] ?? null;
                                $applies = $type->appliesToMonth($month);
                                $future = $month->greaterThan($today);
                                $isCurrent = $month->equalTo($today);
                            @endphp
                            <td class="px-1.5 py-2 text-center {{ $isCurrent ? 'bg-emerald-50/40' : '' }}">
                                @if ($payment)
                                    <span class="inline-flex items-center justify-center size-7 rounded-lg bg-daun-soft text-daun-dark font-bold text-xs shadow-2xs" title="Dibayar {{ $payment->paid_on->translatedFormat('j M Y') }}">
                                        ✓
                                    </span>
                                @elseif (isset($pending[$household->id][$period]))
                                    @if ($admin)
                                        <a href="{{ route('admin.submissions.index') }}" class="text-[11px] font-semibold text-amber-900 bg-amber-50 border border-dashed border-amber-400 px-1.5 py-0.5 rounded no-underline" title="Bukti bayar menunggu konfirmasi">dicek</a>
                                    @else
                                        <span class="text-[11px] font-semibold text-amber-900 bg-amber-50 border border-dashed border-amber-400 px-1.5 py-0.5 rounded" title="Bukti bayar sedang dicek pengurus">dicek</span>
                                    @endif
                                @elseif (! $applies)
                                    <span class="text-slate-300 text-xs" title="Iuran belum berlaku">–</span>
                                @elseif ($admin)
                                    <a href="{{ route('admin.home', ['rumah' => $household->id, 'jenis' => $type->id, 'tahun' => $year, 'bulan' => [$period]]) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg bg-slate-100 hover:bg-daun hover:text-white text-slate-600 font-extrabold text-sm transition-colors shadow-2xs"
                                       aria-label="Catat {{ $type->name }} {{ $household->number }} {{ $month->translatedFormat('F Y') }}">+</a>
                                @elseif ($future)
                                    <span class="text-slate-300 text-xs">·</span>
                                @else
                                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">belum</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    {{-- Non-monthly (Sekali Bayar) List --}}
    <div class="sheet divide-y divide-slate-100">
        @foreach ($households as $household)
            @php $payment = $paid[$household->id] ?? null; @endphp
            <div class="flex items-center justify-between gap-4 p-4" data-household-row data-search="{{ strtolower($household->number.' '.$household->head_name) }}">
                <div class="min-w-0">
                    <span class="inline-block font-extrabold text-sm px-2.5 py-0.5 rounded-lg bg-slate-900 text-white mr-2">
                        {{ $household->number }}
                    </span>
                    <span class="font-bold text-slate-800 text-sm">{{ $household->head_name }}</span>
                </div>
                @if ($payment)
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-daun-dark bg-daun-soft px-3 py-1 rounded-full shrink-0" title="Dibayar {{ $payment->paid_on->translatedFormat('j M Y') }}">
                        ✓ Lunas ({{ $payment->paid_on->translatedFormat('j M Y') }})
                    </span>
                @elseif (isset($pending[$household->id]))
                    <span class="text-xs font-semibold text-amber-900 bg-amber-50 border border-dashed border-amber-400 px-2.5 py-1 rounded-full shrink-0">
                        Sedang dicek
                    </span>
                @elseif ($admin)
                    <a href="{{ route('admin.home', ['rumah' => $household->id, 'jenis' => $type->id]) }}" class="btn btn-sm btn-primary shrink-0">
                        + Catat Bayar
                    </a>
                @else
                    <a href="{{ route('pay.create', ['rumah' => $household->id, 'jenis' => $type->id]) }}" class="btn btn-sm btn-primary shrink-0 text-xs">
                        Belum lunas · Bayar
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<p id="cari-kosong" class="mt-4 text-center text-sm text-slate-500 py-6 sheet" hidden>
    Tidak ada rumah yang cocok dengan pencarian Anda.
</p>

