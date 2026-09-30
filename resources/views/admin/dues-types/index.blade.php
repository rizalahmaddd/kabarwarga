<x-layouts.admin title="Jenis Iuran">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Jenis Iuran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola iuran rutin bulanan (kebersihan, satpam) atau iuran insidental (PHBN 17-an).</p>
        </div>
        <a href="{{ route('admin.dues-types.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm shadow-sm transition active:scale-[0.98]">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            <span>Tambah Jenis Iuran</span>
        </a>
    </div>

    @if ($types->isEmpty())
        <div class="mt-6 p-12 bg-white rounded-2xl border border-slate-200/80 shadow-sm text-center">
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
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($types as $type)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:border-emerald-200 transition">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase {{ $type->isMonthly() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $type->isMonthly() ? 'Bulanan' : 'Sekali Bayar' }}
                            </span>
                            @unless ($type->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    Nonaktif
                                </span>
                            @endunless
                        </div>

                        <h2 class="font-bold text-slate-900 text-lg mt-2.5">{{ $type->name }}</h2>
                        <div class="text-2xl font-bold tracking-tight text-emerald-800 mt-1 tabular-nums">
                            {{ rupiah($type->amount) }}
                            <span class="text-xs font-normal text-slate-500">/ {{ $type->isMonthly() ? 'bulan' : 'rumah' }}</span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-500">
                            @if ($type->isMonthly() && $type->starts_on)
                                <div class="flex items-center justify-between">
                                    <span>Mulai berlaku:</span>
                                    <span class="font-semibold text-slate-700">{{ $type->starts_on->translatedFormat('F Y') }}</span>
                                </div>
                            @endif
                            @if (! $type->isMonthly() && $type->due_on)
                                <div class="flex items-center justify-between">
                                    <span>Batas bayar:</span>
                                    <span class="font-semibold text-slate-700">{{ $type->due_on->translatedFormat('d M Y') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span>Total terkumpul:</span>
                                <span class="font-bold text-slate-800 tabular-nums">{{ rupiah($type->payments_sum_amount) }} ({{ $type->payments_count }}x)</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <a href="{{ route('admin.payments.ledger', ['jenis' => $type->id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition active:scale-95 shadow-xs">
                            <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>Buku Iuran</span>
                        </a>
                        <a href="{{ route('admin.dues-types.edit', $type) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold hover:bg-emerald-100 transition active:scale-95">
                            Ubah
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
