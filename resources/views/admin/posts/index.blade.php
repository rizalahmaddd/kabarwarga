<x-layouts.admin title="Kabar & Pengumuman">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Kabar & Pengumuman</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola informasi, agenda kegiatan warga, dan imbauan penting.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm shadow-sm transition active:scale-[0.98]">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            <span>Tulis Kabar Baru</span>
        </a>
    </div>

    <!-- Category Filter Tabs -->
    <nav aria-label="Saring kategori" class="mt-5 flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
        <a href="{{ route('admin.posts.index') }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ !request('kategori') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}" @unless (request('kategori')) aria-current="page" @endunless>Semua Kabar</a>
        @foreach (\App\Models\Post::CATEGORIES as $key => $label)
            <a href="{{ route('admin.posts.index', ['kategori' => $key]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ request('kategori') === $key ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}" @if (request('kategori') === $key) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse ($posts as $post)
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 transition">
                <div class="flex items-start gap-3.5 min-w-0">
                    @if ($post->imageUrl())
                        <img src="{{ $post->imageUrl() }}" alt="" class="size-14 rounded-xl object-cover border border-slate-200 shrink-0">
                    @else
                        <div class="size-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <x-stamp :category="$post->category" />
                            @if (! $post->isPublished())
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draf</span>
                            @else
                                <span class="text-xs text-slate-400">• {{ $post->published_at->translatedFormat('d M Y') }}</span>
                            @endif
                            @if ($post->is_pinned)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                                    Tersemat
                                </span>
                            @endif
                            @if ($post->is_popup)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                    Popup
                                </span>
                            @endif
                        </div>
                        <h2 class="font-bold text-slate-900 text-base leading-snug break-words">
                            <a href="{{ route('posts.show', $post) }}" class="hover:text-emerald-700 transition">
                                {{ $post->title }}
                            </a>
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center shrink-0 pt-1 sm:pt-0">
                    <a href="{{ route('posts.show', $post) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 transition active:scale-95 shadow-xs" target="_blank">
                        Lihat
                    </a>
                    <a href="{{ route('admin.posts.edit', $post) }}" class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition active:scale-95 shadow-xs">
                        Ubah
                    </a>
                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Hapus kabar &quot;{{ $post->title }}&quot;? Tidak bisa dibatalkan.">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition active:scale-95" data-busy="Menghapus...">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="size-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                </div>
                <p class="font-bold text-slate-800 text-base">Belum ada kabar{{ request('kategori') ? ' di kategori ini' : '' }}</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Sampaikan pengumuman kerja bakti, surat edaran, atau agenda warga di sini.</p>
                <a href="{{ route('admin.posts.create', ['kategori' => request('kategori')]) }}" class="mt-4 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium text-xs hover:bg-emerald-700 shadow-sm transition">
                    + Tulis Kabar Pertama
                </a>
            </div>
        @endforelse
    </div>

    @if ($posts->hasPages())
        <div class="mt-6">{{ $posts->links() }}</div>
    @endif
</x-layouts.admin>
