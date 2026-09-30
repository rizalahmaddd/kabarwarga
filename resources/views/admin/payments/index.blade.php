<x-layouts.admin title="Riwayat Bayar">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Audit Transaksi
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Riwayat Pembayaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar semua pembayaran yang telah dicatat. Jika ada salah catat, hapus lalu input kembali.</p>
        </div>
        <div>
            <a href="{{ route('admin.home') }}" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5 shadow-xs">
                + Catat Pembayaran
            </a>
        </div>
    </div>

    @php
        $householdOptions = [];
        foreach ($households as $h) {
            $householdOptions[$h->id] = $h->label();
        }

        $typeOptions = [];
        foreach ($types as $t) {
            $typeOptions[$t->id] = $t->name;
        }
    @endphp

    {{-- Filter Card --}}
    <form method="GET" class="sheet p-4 bg-white mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 items-end">
        <div>
            <label for="f-rumah" class="field-label text-xs">Saring Rumah</label>
            <x-dropdown name="rumah" id="f-rumah" :value="request('rumah')" :options="$householdOptions" placeholder="Semua Rumah Warga" autosubmit />
        </div>
        <div>
            <label for="f-jenis" class="field-label text-xs">Saring Jenis Iuran</label>
            <x-dropdown name="jenis" id="f-jenis" :value="request('jenis')" :options="$typeOptions" placeholder="Semua Jenis Iuran" autosubmit />
        </div>
        @if (request('rumah') || request('jenis'))
            <div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-quiet text-xs font-bold w-full">
                    Reset Saringan
                </a>
            </div>
        @endif
        <noscript><button class="btn btn-quiet">Saring</button></noscript>
    </form>

    {{-- Payment History List --}}
    <div class="sheet divide-y divide-slate-100 shadow-xs">
        @forelse ($payments as $payment)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 hover:bg-slate-50/80 transition-colors">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="inline-flex items-center justify-center font-extrabold text-xs px-2.5 py-0.5 rounded-lg bg-slate-900 text-white">
                            {{ $payment->household->number }}
                        </span>
                        <span class="font-bold text-sm text-slate-800">{{ $payment->household->head_name }}</span>
                        <span class="text-xs text-slate-400">·</span>
                        <span class="text-xs font-bold text-daun-dark bg-daun-soft px-2 py-0.5 rounded">
                            {{ $payment->duesType->name }} ({{ $payment->periodLabel() }})
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mt-1">
                        <span class="font-bold text-slate-900 text-sm">{{ rupiah($payment->amount) }}</span>
                        <span class="inline-flex items-center uppercase font-bold text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                            {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}
                        </span>
                        <span>Diterima {{ $payment->paid_on->translatedFormat('j M Y') }}</span>
                        @if ($payment->recorder)
                            <span>· Oleh {{ $payment->recorder->name }}</span>
                        @endif
                        @if ($payment->note)
                            <span class="italic text-slate-400 truncate max-w-xs">"{{ $payment->note }}"</span>
                        @endif
                    </div>
                </div>

                <div class="self-end sm:self-center shrink-0">
                    <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}"
                          data-confirm="Hapus catatan {{ $payment->duesType->name }} rumah {{ $payment->household->number }} ({{ $payment->periodLabel() }})?">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger text-xs font-bold" data-busy="Menghapus...">
                            Hapus Baris
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <div class="size-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" x2="12" y1="8" y2="12"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-700">Tidak ada riwayat pembayaran{{ request('rumah') || request('jenis') ? ' yang cocok dengan saringan' : '' }}.</p>
                <div class="mt-3">
                    <a href="{{ route('admin.home') }}" class="btn btn-sm btn-primary text-xs font-bold">
                        Catat Pembayaran Baru
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $payments->links() }}</div>
</x-layouts.admin>

