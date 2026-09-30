@props(['post'])
<article class="sheet relative p-4 sm:p-5 transition-all hover:border-slate-300 hover:shadow-xs group">
    @if ($post->is_pinned)
        <span class="pin" aria-hidden="true"></span>
    @endif
    <div class="flex gap-4 items-start">
        @if ($post->category === 'kegiatan' && $post->event_starts_at)
            <x-event-date :at="$post->event_starts_at" class="self-start mt-0.5" />
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-500 mb-1.5">
                <x-stamp :category="$post->category" />
                @if ($post->is_pinned)
                    <span class="inline-flex items-center gap-1 font-bold text-terakota text-[11px] bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" x2="12" y1="17" y2="22"/>
                            <path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"/>
                        </svg>
                        Dipasang
                    </span>
                @endif
                <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->translatedFormat('j M Y') }}</time>
            </div>
            <h3 class="font-bold text-lg text-slate-900 group-hover:text-daun transition-colors leading-snug break-words">
                <a href="{{ route('posts.show', $post) }}" class="no-underline text-inherit hover:text-daun">{{ $post->title }}</a>
            </h3>
            @if ($post->category === 'kegiatan' && $post->event_starts_at)
                <p class="text-xs font-semibold text-slate-700 mt-1 flex items-center gap-1.5">
                    <svg class="size-3.5 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    {{ $post->event_starts_at->translatedFormat('l, j F Y · H.i') }} WIB
                    @if ($post->event_location) · <span class="text-slate-500 font-normal truncate">{{ $post->event_location }}</span> @endif
                </p>
            @endif
            <p class="mt-2 text-sm text-slate-600 line-clamp-2 leading-relaxed break-words">{{ $post->excerpt() }}</p>
            <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100">
                <a href="{{ route('posts.show', $post) }}" class="inline-flex items-center gap-1 text-xs font-bold text-daun hover:text-daun-dark no-underline">
                    Baca selengkapnya
                    <svg class="size-3.5 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</article>
