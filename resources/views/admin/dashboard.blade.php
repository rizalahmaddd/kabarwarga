<x-layouts.admin title="Ringkasan">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Ringkasan Lingkungan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Posisi kas, iuran bulan {{ now()->translatedFormat('F Y') }}, dan hal yang perlu ditindaklanjuti.</p>
    </div>

    <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
        <div class="sheet p-4">
            <p class="text-xs font-semibold text-slate-500">Saldo kas</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold tabular-nums {{ $balance < 0 ? 'text-terakota' : 'text-slate-900' }}">{{ rupiah($balance) }}</p>
            <a href="{{ route('dues.cashbook') }}" class="text-[11px] font-semibold text-daun hover:underline">Buku kas umum</a>
        </div>
        <div class="sheet p-4">
            <p class="text-xs font-semibold text-slate-500">Masuk bulan ini</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold tabular-nums text-daun-dark">{{ rupiah($monthIn) }}</p>
            <p class="text-[11px] text-slate-500">Keluar {{ rupiah($monthOut) }}</p>
        </div>
        <a href="{{ route('admin.submissions.index') }}" class="sheet p-4 no-underline hover:border-amber-300 transition-colors">
            <p class="text-xs font-semibold text-slate-500">Bukti bayar menunggu</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold tabular-nums {{ $pendingCount ? 'text-amber-700' : 'text-slate-900' }}">{{ $pendingCount }}</p>
            <p class="text-[11px] text-slate-500">{{ $pendingCount ? 'Perlu dicek' : 'Semua sudah diproses' }}</p>
        </a>
        <a href="{{ route('admin.arrears.index') }}" class="sheet p-4 no-underline hover:border-terakota/40 transition-colors">
            <p class="text-xs font-semibold text-slate-500">Rumah menunggak</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold tabular-nums {{ $owingCount ? 'text-terakota' : 'text-slate-900' }}">{{ $owingCount }}</p>
            <p class="text-[11px] text-slate-500">Total {{ rupiah($owingTotal) }}</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12 items-start">
        <div class="lg:col-span-7 space-y-6">
            <section class="sheet p-5">
                <h2 class="font-bold text-slate-900">Iuran bulan ini</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($progress as $item)
                        @php $percent = $item['total'] ? round($item['paid'] / $item['total'] * 100) : 0; @endphp
                        <div>
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-800">{{ $item['type']->name }} <span class="font-normal text-slate-500">· {{ $item['label'] }}</span></span>
                                <span class="tabular-nums text-slate-600 shrink-0">{{ $item['paid'] }}/{{ $item['total'] }} rumah</span>
                            </div>
                            <div class="mt-1.5 h-2.5 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kelunasan {{ $item['type']->name }}">
                                <div class="h-full rounded-full bg-daun" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada jenis iuran aktif. <a href="{{ route('admin.dues-types.create') }}" class="font-semibold text-daun">Buat jenis iuran</a>.</p>
                    @endforelse
                </div>
            </section>

            <section class="sheet p-5">
                <h2 class="font-bold text-slate-900">Arus kas 6 bulan terakhir</h2>
                @php $peak = max(1, $trend->max('in'), $trend->max('out')); @endphp
                <div class="mt-4 space-y-3">
                    @foreach ($trend as $row)
                        <div class="grid grid-cols-[4.5rem_1fr] items-center gap-3 text-xs">
                            <span class="font-semibold text-slate-600">{{ $row['month']->translatedFormat('M Y') }}</span>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 rounded-full bg-daun" style="width: {{ $row['in'] / $peak * 100 }}%; min-width: {{ $row['in'] ? '4px' : '0' }}"></div>
                                    <span class="tabular-nums text-slate-700 shrink-0">{{ rupiah($row['in']) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 rounded-full bg-terakota/70" style="width: {{ $row['out'] / $peak * 100 }}%; min-width: {{ $row['out'] ? '4px' : '0' }}"></div>
                                    <span class="tabular-nums text-slate-500 shrink-0">{{ rupiah($row['out']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-4 text-[11px] text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-daun"></span> Masuk</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-terakota/70"></span> Keluar</span>
                    <a href="{{ route('admin.reports.show') }}" class="ml-auto font-semibold text-daun hover:underline">Cetak laporan kas</a>
                </div>
            </section>
        </div>

        <div class="lg:col-span-5 space-y-6">
            <section class="sheet p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-900">Tunggakan terbesar</h2>
                    <a href="{{ route('admin.arrears.index') }}" class="text-xs font-semibold text-daun hover:underline">Lihat semua</a>
                </div>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse ($topOwing as $row)
                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0 truncate"><b>{{ $row['household']->number }}</b> · {{ $row['household']->head_name }}</span>
                            <span class="shrink-0 text-right">
                                <span class="block font-bold tabular-nums text-terakota">{{ rupiah($row['total']) }}</span>
                                <span class="block text-[11px] text-slate-500">{{ $row['months'] }} tagihan</span>
                            </span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">Tidak ada tunggakan. Semua rumah lunas sampai bulan ini.</li>
                    @endforelse
                </ul>
            </section>

            @if ($pending->isNotEmpty())
                <section class="sheet p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-bold text-slate-900">Bukti bayar terlama</h2>
                        <a href="{{ route('admin.submissions.index') }}" class="text-xs font-semibold text-daun hover:underline">Periksa</a>
                    </div>
                    <ul class="mt-3 divide-y divide-slate-100">
                        @foreach ($pending as $submission)
                            <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0">
                                    <span class="block truncate"><b>{{ $submission->household->number }}</b> · {{ $submission->duesType->name }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ $submission->created_at->diffForHumans() }}</span>
                                </span>
                                <span class="shrink-0 font-semibold tabular-nums">{{ rupiah($submission->transferTotal()) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="sheet p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-900">Catatan bayar terbaru</h2>
                    <a href="{{ route('admin.payments.index') }}" class="text-xs font-semibold text-daun hover:underline">Riwayat</a>
                </div>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse ($recent as $payment)
                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0">
                                <span class="block truncate"><b>{{ $payment->household->number }}</b> · {{ $payment->duesType->name }}</span>
                                <span class="block text-[11px] text-slate-500">{{ $payment->periodLabel() }} · {{ $payment->paid_on->translatedFormat('j M') }}</span>
                            </span>
                            <span class="shrink-0 font-semibold tabular-nums text-daun-dark">{{ rupiah($payment->amount) }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">Belum ada pembayaran tercatat.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.admin>
