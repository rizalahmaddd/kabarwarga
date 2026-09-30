@php
    $catLabel = (is_string($category) && isset(\App\Models\Post::CATEGORIES[$category])) ? \App\Models\Post::CATEGORIES[$category] : null;
@endphp
<x-layouts.public :title="$catLabel ?? 'Kabar Warga'">
    <div class="mb-5">
        <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                <path d="M18 14h-8"/>
                <path d="M15 18h-5"/>
                <path d="M10 6h8v4h-8V6Z"/>
            </svg>
            Pusat Informasi Warga
        </div>
        <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Kabar & Pengumuman</h1>
        <p class="mt-1 text-sm text-slate-600">Berita lingkungan, agenda kegiatan, pengumuman RT/RW, dan informasi iuran warga.</p>
    </div>

    {{-- Horizontal Scrollable Category Filter Chips --}}
    <div class="mb-6 -mx-4 px-4 sm:mx-0 sm:px-0 overflow-x-auto no-scrollbar">
        <nav aria-label="Saring kategori" class="flex items-center gap-2 min-w-max pb-1">
            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition-all no-underline {{ ! $category ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                Semua Kabar
            </a>
            @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                @php $active = ($category === $key); @endphp
                <a href="{{ route('posts.index', ['kategori' => $key]) }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition-all no-underline {{ $active ? 'bg-daun text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Post Feed --}}
    <div class="max-w-3xl space-y-4">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="sheet p-8 text-center">
                <div class="size-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" x2="16" y1="12" y2="12"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-700">
                    {{ $catLabel ? 'Belum ada kabar kategori '.$catLabel : 'Belum ada kabar yang dipasang' }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    @if ($category)
                        <a href="{{ route('posts.index') }}" class="font-bold text-daun underline">Lihat semua kategori</a>
                    @else
                        Pengurus belum memasang pengumuman apapun.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8 max-w-3xl">{{ $posts->links() }}</div>
</x-layouts.public>

