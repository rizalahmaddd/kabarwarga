@if (session('status'))
    <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-950 flex items-start gap-3 shadow-xs">
        <div class="size-6 rounded-full bg-emerald-200/80 text-emerald-800 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="flex-1 text-sm font-semibold leading-relaxed">
            {{ session('status') }}
        </div>
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

