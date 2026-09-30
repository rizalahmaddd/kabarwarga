<x-layouts.admin title="Pengeluaran Kas">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Pengeluaran Kas</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Transparan dicatat agar warga dapat memantau di <a href="{{ route('dues.cashbook') }}" class="text-emerald-700 font-semibold underline underline-offset-2 hover:text-emerald-800">Buku Kas Umum</a>.
            </p>
        </div>
        <a href="{{ route('admin.expenses.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm shadow-sm transition active:scale-[0.98]">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            <span>Catat Pengeluaran</span>
        </a>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse ($expenses as $expense)
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="size-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0 mt-0.5 sm:mt-0 font-bold text-sm">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-900 text-base break-words">{{ $expense->description }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $expense->spent_on->translatedFormat('d M Y') }}
                            </span>
                            <span>•</span>
                            <span class="font-bold text-rose-600 text-sm tabular-nums">{{ rupiah($expense->amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center shrink-0 pt-1 sm:pt-0">
                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition active:scale-95 shadow-xs">
                        Ubah
                    </a>
                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" data-confirm="Hapus pengeluaran &quot;{{ $expense->description }}&quot;?">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition active:scale-95" data-busy="Menghapus...">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="size-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <p class="font-bold text-slate-800 text-base">Belum ada catatan pengeluaran</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Catat belanja lampu jalan, honor satpam, atau konsumsi kegiatan warga di sini.</p>
                <a href="{{ route('admin.expenses.create') }}" class="mt-4 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium text-xs hover:bg-emerald-700 shadow-sm transition">
                    + Catat Pengeluaran Pertama
                </a>
            </div>
        @endforelse
    </div>

    @if ($expenses->hasPages())
        <div class="mt-6">{{ $expenses->links() }}</div>
    @endif
</x-layouts.admin>
