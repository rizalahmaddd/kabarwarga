@if (session('status'))
    <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-start gap-3 flex-1">
            <div class="size-6 rounded-full bg-emerald-200/80 text-emerald-800 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="text-sm font-semibold leading-relaxed">
                {{ session('status') }}
            </div>
        </div>
        @if (session('whatsapp_url') || session('receipt_url'))
            <div class="flex flex-wrap items-center gap-2 pl-9 sm:pl-0 shrink-0">
                @if (session('whatsapp_url'))
                    <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-xs">
                        <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        Kirim WA ke {{ session('whatsapp_recipient') ?? 'Warga' }}
                    </a>
                @endif
                @if (session('receipt_url'))
                    <a href="{{ session('receipt_url') }}" target="_blank"
                       class="btn btn-sm btn-quiet bg-white/90 border border-emerald-300 text-emerald-900 font-bold text-xs inline-flex items-center gap-1">
                        🧾 Buka Kuitansi
                    </a>
                @endif
            </div>
        @endif
    </div>
@endif
@if (session('warning'))
    <div role="alert" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-amber-950 flex items-start gap-3 shadow-xs">
        <div class="size-6 rounded-full bg-amber-200/80 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" x2="12" y1="8" y2="12"/>
                <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
        </div>
        <div class="flex-1 text-sm font-semibold leading-relaxed">
            {{ session('warning') }}
        </div>
    </div>
@endif

