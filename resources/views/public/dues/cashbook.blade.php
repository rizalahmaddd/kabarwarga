<x-layouts.public title="Kas Warga">
    @php
        $siteName = setting('site_name');
        $topExpenses = $expenses->take(3);
        $expenseLines = '';
        if ($topExpenses->isNotEmpty()) {
            $expenseLines .= "\n📋 *Pengeluaran Terakhir:*\n";
            foreach ($topExpenses as $exp) {
                $expenseLines .= "• " . $exp->description . " (" . rupiah($exp->amount) . ")\n";
            }
        }
        $cashbookUrl = route('dues.cashbook', ['tahun' => $year]);
        $waSummaryText = "📢 *LAPORAN KAS RT TAHUN {$year}*\n"
            . "*{$siteName}*\n"
            . "───────────────────────────\n"
            . "💰 *Saldo Awal:* " . rupiah($book['opening']) . "\n"
            . "📥 *Total Pemasukan:* " . rupiah($book['total_in']) . "\n"
            . "📤 *Total Pengeluaran:* " . rupiah($book['total_out']) . "\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "💵 *Sisa Saldo Kas:* " . rupiah($book['closing']) . "\n"
            . $expenseLines . "\n"
            . "🔗 *Buku kas transparan lengkap:* \n{$cashbookUrl}";
    @endphp

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" x2="12" y1="2" y2="22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Transparansi Keuangan
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Kas Warga Tahun {{ $year }}</h1>
            <p class="mt-1 text-sm text-slate-600 max-w-2xl">
                Laporan uang kas RT/RW terbuka. Uang masuk bersumber dari iuran yang dicatat bendahara, uang keluar dari pengeluaran resmi kas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
            <button type="button" id="btn-copy-wa" data-text="{{ $waSummaryText }}"
                    class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                </svg>
                <span id="btn-copy-label">Salin Format WA</span>
            </button>

            <a href="{{ route('dues.cashbook.export', ['tahun' => $year]) }}"
               class="btn btn-sm btn-quiet text-xs font-bold border border-slate-200 text-slate-700 hover:text-daun inline-flex items-center gap-1.5">
                <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" x2="12" y1="15" y2="3"/>
                </svg>
                Unduh CSV
            </a>

            @if (count($years) > 1)
                @php
                    $yearOptions = [];
                    foreach ($years as $y) {
                        $yearOptions[$y] = 'Tahun ' . $y;
                    }
                @endphp
                <form method="GET" class="w-32">
                    <label for="tahun" class="sr-only">Pilih Tahun</label>
                    <x-dropdown name="tahun" :value="$year" :options="$yearOptions" autosubmit />
                    <noscript><button class="btn btn-quiet text-xs font-bold mt-1">Pilih</button></noscript>
                </form>
            @endif
        </div>
    </div>

    {{-- Fintech-style Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-8">
        {{-- Saldo Saat Ini --}}
        <div class="sheet p-5 bg-gradient-to-br from-emerald-600 via-daun to-emerald-800 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 size-24 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="flex items-center justify-between text-emerald-100 text-xs font-semibold mb-2">
                <span>{{ $year === now()->year ? 'Saldo Kas Aktif' : 'Saldo Akhir '.$year }}</span>
                <span class="size-6 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ rupiah($book['closing']) }}</div>
            <p class="mt-2 text-[11px] text-emerald-100/80">Saldo riil kas per hari ini</p>
        </div>

        {{-- Total Uang Masuk --}}
        <div class="sheet p-5 bg-white border-slate-200">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>Total Pemasukan {{ $year }}</span>
                <span class="size-6 rounded-full bg-emerald-100 text-daun flex items-center justify-center">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="19" y2="5"/>
                        <polyline points="5 12 12 5 19 12"/>
                    </svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ rupiah($book['total_in']) }}</div>
            <p class="mt-2 text-[11px] text-emerald-600 font-medium">Dari iuran warga tercatat</p>
        </div>

        {{-- Total Uang Keluar --}}
        <div class="sheet p-5 bg-white border-slate-200">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>Total Pengeluaran {{ $year }}</span>
                <span class="size-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/>
                        <polyline points="19 12 12 19 5 12"/>
                    </svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ rupiah($book['total_out']) }}</div>
            <p class="mt-2 text-[11px] text-slate-400 font-medium">{{ $expenses->count() }} transaksi pengeluaran</p>
        </div>
    </div>

    {{-- Monthly Ledger Table --}}
    <section aria-labelledby="per-bulan" class="mb-10">
        <div class="flex items-center gap-2 mb-3">
            <div class="size-2 rounded-full bg-daun"></div>
            <h2 id="per-bulan" class="font-bold text-lg text-slate-900">Rekap Kas Bulanan {{ $year }}</h2>
        </div>

        @if (empty($book['rows']))
            <div class="sheet p-8 text-center">
                <p class="text-sm font-bold text-slate-700">Belum ada mutasi masuk atau keluar di tahun {{ $year }}</p>
            </div>
        @else
            <div class="sheet relative overflow-x-auto shadow-xs">
                <table class="w-full text-sm text-right tabular-nums">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 text-xs uppercase font-bold">
                            <th scope="col" class="text-left px-4 py-3">Bulan</th>
                            <th scope="col" class="px-4 py-3 text-emerald-700 font-bold">Uang Masuk</th>
                            <th scope="col" class="px-4 py-3 text-amber-700 font-bold">Uang Keluar</th>
                            <th scope="col" class="px-4 py-3 text-slate-900 font-bold">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if ($book['opening'])
                            <tr class="bg-slate-50/50 text-slate-500 italic text-xs">
                                <th scope="row" class="text-left font-medium px-4 py-2.5">Saldo pindahan dari tahun lalu</th>
                                <td>-</td><td>-</td>
                                <td class="px-4 py-2.5 font-bold text-slate-700">{{ rupiah($book['opening']) }}</td>
                            </tr>
                        @endif
                        @foreach ($book['rows'] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <th scope="row" class="text-left font-bold text-slate-900 px-4 py-3 whitespace-nowrap">
                                    {{ $row['month']->translatedFormat('F Y') }}
                                </th>
                                <td class="px-4 py-3 whitespace-nowrap text-daun-dark font-medium">
                                    {{ $row['in'] ? '+'.rupiah($row['in']) : '–' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-amber-700 font-medium">
                                    {{ $row['out'] ? '-'.rupiah($row['out']) : '–' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-900 bg-slate-50/40">
                                    {{ rupiah($row['balance']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Expense Details --}}
    <section aria-labelledby="rincian-keluar">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="size-2 rounded-full bg-amber-500"></div>
                <h2 id="rincian-keluar" class="font-bold text-lg text-slate-900">Rincian Pengeluaran {{ $year }}</h2>
            </div>
            <span class="text-xs text-slate-500 font-semibold">{{ $expenses->count() }} catatan</span>
        </div>

        <div class="sheet divide-y divide-slate-100 shadow-xs">
            @forelse ($expenses as $expense)
                <div class="flex items-center justify-between gap-4 p-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="size-9 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" x2="8" y1="13" y2="13"/>
                                <line x1="16" x2="8" y1="17" y2="17"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-slate-800 break-words">{{ $expense->description }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                                <svg class="size-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                {{ $expense->spent_on->translatedFormat('j F Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-extrabold text-sm text-amber-900 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/80">
                            -{{ rupiah($expense->amount) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-slate-500">
                    Belum ada pengeluaran yang tercatat di tahun {{ $year }}.
                </div>
            @endforelse
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btn-copy-wa');
            const label = document.getElementById('btn-copy-label');
            if (btn && label) {
                btn.addEventListener('click', function () {
                    const text = btn.getAttribute('data-text');
                    const onSuccess = function () {
                        const orig = label.textContent;
                        label.textContent = '✓ Tersalin!';
                        setTimeout(function () { label.textContent = orig; }, 2500);
                    };

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(onSuccess).catch(function () {
                            fallbackCopy(text, onSuccess);
                        });
                    } else {
                        fallbackCopy(text, onSuccess);
                    }
                });
            }

            function fallbackCopy(text, cb) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    cb();
                } catch (e) {}
                document.body.removeChild(ta);
            }
        });
    </script>
</x-layouts.public>

