<x-layouts.public title="Iuran Warga">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/>
                <line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
            Transparansi RT/RW
        </div>
        <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Catatan Iuran Warga</h1>
        <p class="mt-1.5 text-sm text-slate-600 max-w-2xl leading-relaxed">
            Catatan penerimaan iuran per rumah yang dicatat oleh bendahara. Tanda <strong>dicek</strong> berarti bukti transfer sudah dikirim dan menunggu konfirmasi pengurus.
        </p>
        <a href="{{ route('pay.create') }}" class="btn btn-primary mt-4">Bayar Iuran Online</a>
    </div>

    {{-- Info Cara Bayar --}}
    @if (setting('payment_info') || setting('treasurer_contact'))
        <div class="mb-6 sheet p-4 sm:p-5 bg-gradient-to-br from-emerald-50/50 via-white to-white border-emerald-200/80">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-lg bg-emerald-100 text-daun-dark flex items-center justify-center shrink-0">
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" x2="12" y1="2" y2="22"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm sm:text-base text-slate-900">Petunjuk Pembayaran Iuran</h2>
                        <p class="text-xs text-slate-500">Transfer atau tunai kepada pengurus RT</p>
                    </div>
                </div>

                @if (setting('treasurer_contact'))
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', setting('treasurer_contact'));
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62'.substr($cleanPhone, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ rawurlencode('Halo Bendahara '.setting('site_name').', saya ingin konfirmasi pembayaran iuran warga.') }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-primary text-xs font-bold shrink-0 hidden sm:inline-flex">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        Hubungi Bendahara
                    </a>
                @endif
            </div>

            @if (setting('payment_info'))
                <div class="mt-3 text-xs sm:text-sm text-slate-700 bg-white/80 p-3 rounded-xl border border-emerald-100/80 whitespace-pre-line leading-relaxed font-sans">
                    {{ setting('payment_info') }}
                </div>
            @endif

            @if (setting('treasurer_contact'))
                <div class="mt-3 flex sm:hidden">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ rawurlencode('Halo Bendahara '.setting('site_name').', saya ingin konfirmasi pembayaran iuran warga.') }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-primary w-full text-xs font-bold flex items-center justify-center gap-1.5">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        Chat Bendahara ({{ setting('treasurer_contact') }})
                    </a>
                </div>
            @endif
        </div>
    @endif

    @if (! $type)
        <div class="sheet p-8 text-center mt-6">
            <h3 class="font-bold text-slate-800">Belum ada jenis iuran aktif</h3>
            <p class="text-xs text-slate-500 mt-1">Pengurus belum mengatur jenis iuran warga.</p>
        </div>
    @else
        <div class="sheet p-4 bg-white mb-4">
            @include('partials.dues-filter')
        </div>

        @include('partials.dues-grid')

        @if ($type->isMonthly())
            <div class="mt-4 p-3 rounded-xl bg-slate-100/70 border border-slate-200 text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                    <span class="size-2 rounded-full bg-daun"></span>
                    <strong>✓ Lunas</strong>: Sudah tercatat
                </span>
                <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                    <span class="size-2 rounded-full bg-amber-500"></span>
                    <strong>Belum</strong>: Belum tercatat
                </span>
                <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                    <span class="size-2 rounded-full border border-dashed border-amber-500"></span>
                    <strong>Dicek</strong>: Bukti bayar menunggu konfirmasi
                </span>
                <span>• Kolom / kartu bertanda hijau adalah bulan berjalan</span>
            </div>
        @endif
    @endif
</x-layouts.public>

