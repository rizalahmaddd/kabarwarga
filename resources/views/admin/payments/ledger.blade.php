<x-layouts.admin title="Buku Iuran">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                    <path d="M3 9h18"/>
                    <path d="M3 15h18"/>
                    <path d="M9 3v18"/>
                </svg>
                Rekap RT/RW
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Buku Iuran Warga</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Cek kelunasan iuran tiap rumah. Tekan tanda <strong>+</strong> atau tombol catat untuk langsung input pembayaran.</p>
        </div>
        <div>
            <a href="{{ route('admin.home') }}" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5 shadow-xs">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" x2="12" y1="5" y2="19"/>
                    <line x1="5" x2="19" y1="12" y2="12"/>
                </svg>
                Catat Pembayaran Baru
            </a>
        </div>
    </div>

    @if (! $type)
        <div class="sheet p-8 text-center mt-6">
            <h3 class="font-bold text-slate-800">Belum ada jenis iuran</h3>
            <p class="text-xs text-slate-500 mt-1">
                <a href="{{ route('admin.dues-types.create') }}" class="font-bold text-daun underline">Buat jenis iuran pertama</a> untuk memulai.
            </p>
        </div>
    @else
        <div class="sheet p-4 bg-white mb-4">
            @include('partials.dues-filter')
        </div>
        @include('partials.dues-grid', ['admin' => true])
    @endif
</x-layouts.admin>

