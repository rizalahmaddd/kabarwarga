@props(['category'])
@php
    $tone = match ($category) {
        'kegiatan' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'pengumuman' => 'bg-amber-50 text-amber-900 border-amber-200',
        'berita' => 'bg-blue-50 text-blue-800 border-blue-200',
        'iuran' => 'bg-teal-50 text-teal-800 border-teal-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp
<span {{ $attributes->class(['inline-flex items-center text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border', $tone]) }}>
    {{ \App\Models\Post::CATEGORIES[$category] ?? $category }}
</span>
