<x-layouts.admin title="Rekening">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="5" rx="2"/>
                    <line x1="2" x2="22" y1="10" y2="10"/>
                </svg>
                Bayar Online Warga
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Rekening Pembayaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Rekening aktif tampil di halaman Bayar Iuran. Tanpa rekening aktif, bayar online tidak dibuka.</p>
        </div>
        <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-sm btn-primary text-xs font-bold self-start sm:self-auto">+ Tambah Rekening</a>
    </div>

    @if ($accounts->isEmpty())
        <div class="sheet p-8 text-center">
            <h3 class="font-bold text-slate-800">Belum ada rekening</h3>
            <p class="text-xs text-slate-500 mt-1">
                <a href="{{ route('admin.bank-accounts.create') }}" class="font-bold text-daun underline">Tambahkan rekening</a> supaya warga bisa bayar lewat transfer.
            </p>
        </div>
    @else
        <div class="sheet divide-y divide-slate-100">
            @foreach ($accounts as $account)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($account->qrisUrl())
                            <img src="{{ $account->qrisUrl() }}" alt="" class="size-14 rounded-lg border border-slate-200 object-cover shrink-0">
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $account->bank_name }}</span>
                                @unless ($account->is_active)
                                    <span class="text-[10px] uppercase font-bold text-terakota bg-terakota-soft px-1.5 py-0.5 rounded">Nonaktif</span>
                                @endunless
                            </div>
                            @if ($account->account_number)
                                <p class="font-mono text-sm font-semibold text-slate-800">{{ $account->account_number }}</p>
                            @endif
                            <p class="text-xs text-slate-500">
                                {{ $account->account_name ? 'a.n. '.$account->account_name : '' }}
                                {{ $account->qris_path ? '· ada QRIS' : '' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="btn btn-sm btn-quiet text-xs font-bold self-end sm:self-center">Ubah</a>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
