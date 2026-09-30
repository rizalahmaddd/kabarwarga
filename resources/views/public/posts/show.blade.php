<x-layouts.public :title="$post->title" :description="$post->excerpt()">
    <article class="max-w-3xl">
        {{-- Back Navigation --}}
        <div class="mb-4">
            <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-daun no-underline transition-colors">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" x2="5" y1="12" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali ke Kabar Warga
            </a>
        </div>

        @unless ($post->isPublished())
            <div role="status" class="mb-5 rounded-2xl border border-amber-300 bg-amber-50 p-3.5 text-xs font-bold text-amber-900 flex items-center gap-2">
                <span class="size-2 rounded-full bg-amber-500"></span>
                Draf: Halaman ini belum dipublikasikan dan hanya terlihat oleh pengurus.
            </div>
        @endunless

        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mb-2">
            <x-stamp :category="$post->category" />
            @if ($post->is_pinned)
                <span class="inline-flex items-center gap-1 font-bold text-terakota text-[11px] bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                    📌 Dipasang Pengurus
                </span>
            @endif
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->translatedFormat('l, j F Y') }}</time>
            @endif
            @if ($post->author)
                <span>· Oleh <strong class="text-slate-700">{{ $post->author->name }}</strong></span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight break-words">
            {{ $post->title }}
        </h1>

        {{-- Event Detail Banner if Kegiatan --}}
        @if ($post->category === 'kegiatan' && $post->event_starts_at)
            <div class="mt-6 sheet p-4 sm:p-5 flex items-start gap-4 bg-gradient-to-r from-emerald-50/60 to-white border-emerald-200">
                <x-event-date :at="$post->event_starts_at" class="shrink-0" />
                <div class="min-w-0 flex-1">
                    <span class="text-[11px] font-bold text-daun uppercase tracking-wider block mb-0.5">Jadwal Acara Warga</span>
                    <p class="font-extrabold text-base text-slate-900">
                        {{ $post->event_starts_at->translatedFormat('l, j F Y') }}
                    </p>
                    <p class="text-xs text-slate-600 mt-1 flex items-center gap-1.5">
                        <svg class="size-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Pukul {{ $post->event_starts_at->format('H.i') }} WIB
                        @if ($post->event_location)
                            · <span class="font-semibold text-slate-800">{{ $post->event_location }}</span>
                        @endif
                    </p>
                </div>
            </div>
        @endif

        @if ($post->imageUrl())
            <div class="mt-6 rounded-2xl overflow-hidden border border-slate-200 shadow-xs">
                <img src="{{ $post->imageUrl() }}" alt="Foto: {{ $post->title }}" class="w-full h-auto object-cover max-h-96">
            </div>
        @endif

        {{-- Content Body --}}
        <div class="sheet p-5 sm:p-8 mt-6">
            <div class="prose-warga text-base sm:text-lg text-slate-700 leading-relaxed break-words">
                {{ $post->bodyHtml() }}
            </div>

            @if ($post->category === 'iuran')
                <div class="mt-8 p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 text-xs sm:text-sm text-emerald-950 flex items-center justify-between gap-3">
                    <span>Cek status pencatatan iuran rumah Anda di halaman iuran warga.</span>
                    <a href="{{ route('dues.index') }}" class="btn btn-sm btn-primary text-xs shrink-0">
                        Cek Iuran Sekarang →
                    </a>
                </div>
            @endif
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="kabar-lain" class="mt-12 max-w-3xl">
            <div class="flex items-center gap-2 mb-4">
                <div class="size-2 rounded-full bg-daun"></div>
                <h2 id="kabar-lain" class="font-bold text-xl text-slate-900">Kabar {{ strtolower($post->categoryLabel()) }} Lainnya</h2>
            </div>
            <div class="space-y-4">
                @foreach ($related as $item)
                    <x-post-card :post="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>

