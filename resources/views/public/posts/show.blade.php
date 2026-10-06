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

            {{-- Share Bar --}}
            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bagikan Kabar Ini:</span>
                <div class="flex items-center gap-2">
                    @php
                        $postUrl = route('posts.show', $post);
                        $siteName = setting('site_name');
                        $waPostText = "📢 *{$post->title}*\n\n"
                            . ($post->excerpt() ? $post->excerpt() . "\n\n" : '')
                            . "Baca selengkapnya di papan warga {$siteName}:\n{$postUrl}";
                        $waPostUrl = 'https://wa.me/?text=' . rawurlencode($waPostText);
                    @endphp
                    <a href="{{ $waPostUrl }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-xs">
                        <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        Bagikan ke WhatsApp
                    </a>
                    <button type="button" id="btn-copy-post" data-url="{{ $postUrl }}"
                            class="btn btn-sm btn-quiet text-xs font-bold border border-slate-200 text-slate-700 hover:text-daun inline-flex items-center gap-1 cursor-pointer">
                        <span id="btn-copy-post-label">Salin Tautan</span>
                    </button>
                </div>
            </div>
        </div>
    </article>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btn-copy-post');
            const label = document.getElementById('btn-copy-post-label');
            if (btn && label) {
                btn.addEventListener('click', function () {
                    const url = btn.getAttribute('data-url');
                    const onSuccess = function () {
                        label.textContent = '✓ Tersalin!';
                        setTimeout(function () { label.textContent = 'Salin Tautan'; }, 2500);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(onSuccess).catch(function () {
                            fallbackCopy(url, onSuccess);
                        });
                    } else {
                        fallbackCopy(url, onSuccess);
                    }
                });
            }

            function fallbackCopy(text, cb) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    cb();
                } catch (e) {}
                document.body.removeChild(ta);
            }
        });
    </script>

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

