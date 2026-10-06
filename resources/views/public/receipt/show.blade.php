<x-layouts.public :title="'Kuitansi ' . $payment->receiptNumber() . ' · ' . $payment->household->head_name">
    {{-- Print-only CSS rules to strictly hide any headers, footers, and navigations --}}
    <style>
        @media print {
            @page {
                margin: 0.8cm;
                size: auto;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            header, footer, nav, [role="navigation"], .print\:hidden {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .receipt-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .receipt-card {
                background: #ffffff !important;
                border: 2px solid #0f172a !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                padding: 1.5rem !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }
    </style>

    <div class="receipt-container max-w-2xl mx-auto">
        {{-- Navigation & Action Bar (Hidden on print) --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <a href="{{ url()->previous(route('dues.index')) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-daun transition-colors">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" x2="5" y1="12" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali
            </a>

            <div class="flex items-center gap-2">
                @php
                    $cleanPhone = clean_phone($payment->household->phone);
                    $waText = rawurlencode("Halo Bpk/Ibu {$payment->household->head_name}, berikut bukti tanda terima pembayaran iuran {$payment->duesType->name} ({$payment->periodLabel()}): " . route('receipt.show', $payment));
                    $waUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text={$waText}" : "https://wa.me/?text={$waText}";
                @endphp
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                   class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs">
                    <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    Bagikan ke WA
                </a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5 shadow-xs">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect width="12" height="8" x="6" y="14"/>
                    </svg>
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>

        {{-- Receipt Printable Card --}}
        <div class="receipt-card sheet p-6 sm:p-10 bg-[#fffbf2] border-2 border-[#d9cfbb] shadow-sm relative overflow-hidden">
            {{-- Kop Surat / Header Kuitansi --}}
            <div class="border-b-2 border-slate-900 pb-5 mb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 font-serif tracking-tight">
                        {{ setting('site_name') }}
                    </h2>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">Papan Informasi & Pengelolaan Kas Warga RT/RW</p>
                    @if (setting('address'))
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ setting('address') }}</p>
                    @endif
                </div>
                <div class="sm:text-right shrink-0">
                    <span class="inline-block text-[11px] font-extrabold uppercase tracking-wider text-daun bg-daun-soft px-2.5 py-1 rounded-md print:border print:border-slate-800">
                        Kuitansi Resmi
                    </span>
                    <p class="font-mono text-xs font-bold text-slate-700 mt-1">No: {{ $payment->receiptNumber() }}</p>
                </div>
            </div>

            {{-- Title & Stamp Bar (Flex container ensures ZERO text collision) --}}
            <div class="flex items-center justify-between gap-4 my-4 pb-1">
                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-bold uppercase tracking-wider text-slate-900 font-serif underline underline-offset-4 decoration-2">
                        Tanda Terima Pembayaran Iuran
                    </h1>
                </div>
                <div class="shrink-0 rotate-[-6deg] pointer-events-none select-none">
                    <div class="border-2 sm:border-3 border-dashed border-emerald-700 text-emerald-800 px-3.5 py-1 rounded-xl text-center bg-emerald-50/70 shadow-2xs print:border-emerald-800 print:bg-transparent">
                        <div class="text-base sm:text-lg font-black tracking-widest uppercase font-serif leading-none">✓ LUNAS</div>
                        <div class="text-[9px] font-bold text-emerald-700 tracking-wider uppercase mt-0.5">
                            {{ $payment->paid_on->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Body Details --}}
            <dl class="space-y-3.5 text-sm my-5 divide-y divide-slate-200/80">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-2">
                    <dt class="text-slate-500 font-medium">Telah Diterima Dari</dt>
                    <dd class="sm:col-span-2 font-bold text-slate-900 flex items-center gap-2">
                        <span>{{ $payment->household->head_name }}</span>
                        <span class="text-xs font-extrabold px-2 py-0.5 rounded bg-slate-900 text-white print:border print:border-black">
                            Rumah {{ $payment->household->number }}
                        </span>
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-3.5">
                    <dt class="text-slate-500 font-medium">Untuk Pembayaran</dt>
                    <dd class="sm:col-span-2 font-bold text-slate-900">
                        {{ $payment->duesType->name }}
                        <span class="text-daun-dark font-semibold">({{ $payment->periodLabel() }})</span>
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-3.5">
                    <dt class="text-slate-500 font-medium">Metode Pembayaran</dt>
                    <dd class="sm:col-span-2 font-semibold text-slate-800">
                        {{ \App\Models\Payment::METHODS[$payment->method] ?? ucfirst($payment->method) }}
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-3.5">
                    <dt class="text-slate-500 font-medium">Tanggal Diterima</dt>
                    <dd class="sm:col-span-2 font-semibold text-slate-900">
                        {{ $payment->paid_on->translatedFormat('l, d F Y') }}
                    </dd>
                </div>

                @if ($payment->note)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-3.5">
                        <dt class="text-slate-500 font-medium">Catatan / Keterangan</dt>
                        <dd class="sm:col-span-2 text-slate-700 italic">
                            "{{ $payment->note }}"
                        </dd>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 pt-3.5 bg-emerald-50/60 p-3.5 rounded-xl border border-emerald-200 print:bg-slate-50 print:border-slate-300">
                    <dt class="text-emerald-900 font-bold self-center print:text-slate-900">Jumlah Pembayaran</dt>
                    <dd class="sm:col-span-2">
                        <span class="font-extrabold text-xl sm:text-2xl text-emerald-900 tabular-nums print:text-slate-900">
                            {{ rupiah($payment->amount) }}
                        </span>
                        <p class="text-xs font-semibold text-emerald-800 italic mt-0.5 print:text-slate-700">
                            # {{ ucwords(terbilang($payment->amount)) }} Rupiah #
                        </p>
                    </dd>
                </div>
            </dl>

            {{-- Signatures / Verification --}}
            <div class="mt-8 pt-5 border-t border-dashed border-slate-300 flex flex-col sm:flex-row items-end justify-between gap-6">
                <div class="text-[11px] text-slate-500 max-w-xs">
                    <p class="font-semibold text-slate-700">Verifikasi Sistem</p>
                    <p class="mt-0.5">Dokumen ini diterbitkan secara otomatis dan sah sebagai bukti setoran iuran warga {{ setting('site_name') }}.</p>
                    <p class="mt-1 font-mono text-[10px] text-slate-400 break-all">{{ route('receipt.show', $payment) }}</p>
                </div>

                <div class="text-center sm:text-right w-full sm:w-auto">
                    <p class="text-xs text-slate-500">{{ setting('site_name') }}, {{ $payment->paid_on->translatedFormat('d F Y') }}</p>
                    <p class="text-xs font-bold text-slate-700 mt-1">Dicatat oleh:</p>
                    <div class="h-10 flex items-center justify-center sm:justify-end">
                        <span class="text-xs italic text-slate-400 font-serif">[ Tanda Terima Digital ]</span>
                    </div>
                    <p class="font-bold text-sm text-slate-900 underline underline-offset-2">
                        {{ $payment->recorder ? $payment->recorder->name : 'Pengurus RT' }}
                    </p>
                    <p class="text-[11px] text-slate-500">Pengurus / Bendahara</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
