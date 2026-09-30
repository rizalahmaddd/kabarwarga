@props(['at'])
<div {{ $attributes->class('shrink-0 w-14 sm:w-16 text-center rounded-xl border border-slate-200 bg-white overflow-hidden shadow-2xs') }} aria-hidden="true">
    <div class="text-[10px] sm:text-xs font-bold bg-gradient-to-r from-daun to-emerald-700 text-white uppercase py-0.5 tracking-wider">{{ $at->translatedFormat('M') }}</div>
    <div class="py-1">
        <div class="font-bold text-xl sm:text-2xl leading-none text-slate-900 tracking-tight">{{ $at->format('j') }}</div>
        <div class="text-[10px] text-slate-500 font-medium capitalize mt-0.5">{{ $at->translatedFormat('D') }}</div>
    </div>
</div>

