<x-layouts.public>
    {{-- Hero Section: Portal Komunitas Warga (Warm Civic Editorial) --}}
    <section class="mb-8 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-9 shadow-2xs">
        <div>
            {{-- Top pill & date --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-daun-dark border border-emerald-200/80 text-xs font-bold">
                    <span class="size-2 rounded-full bg-daun"></span>
                    <span>Portal Resmi Lingkungan</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <svg class="size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                    <span>{{ now()->translatedFormat('l, j F Y') }}</span>
                </div>
            </div>

            {{-- Main Headline --}}
            <div class="mt-5 max-w-3xl">
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    {{ setting('site_name') }}
                </h1>
                <p class="text-sm sm:text-base text-slate-600 mt-2 leading-relaxed font-normal">
                    {{ setting('site_tagline') ?: 'Wadah pengumuman lingkungan, transparansi keuangan kas, dan kemudahan pembayaran iuran warga dalam satu pintu digital.' }}
                </p>
            </div>

            {{-- Form Pencarian Cepat Nomor Rumah --}}
            <form action="{{ route('dues.index') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2.5 max-w-2xl">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                        </svg>
                    </div>
                    <input type="search" name="cari" class="w-full h-12 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/80 text-slate-900 placeholder:text-slate-400 text-sm font-medium focus:bg-white focus:outline-none focus:border-daun focus:ring-2 focus:ring-daun/20 transition"
                           placeholder="Cari nomor rumah (mis. B-1) atau nama kepala keluarga..." autocomplete="off">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 h-12 rounded-xl bg-daun hover:bg-daun-dark text-white font-bold text-sm shadow-xs transition active:scale-[0.98] shrink-0 cursor-pointer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                    </svg>
                    <span>Cari Status Iuran</span>
                </button>
            </form>

            {{-- Quick Action Buttons Strip --}}
            <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 hidden sm:inline">Akses Cepat:</span>
                <a href="{{ route('pay.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-daun hover:bg-daun-dark text-xs font-bold text-white shadow-xs transition active:scale-95 no-underline">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                    <span>Bayar Iuran Online</span>
                </a>
                <a href="{{ route('dues.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition active:scale-95 no-underline">
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    <span>Cek Buku Iuran</span>
                </a>
                <a href="{{ route('dues.cashbook') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition active:scale-95 no-underline">
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span>Buku Kas Umum</span>
                </a>
                <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition active:scale-95 no-underline">
                    <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/>
                    </svg>
                    <span>Kabar Warga</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Transparency & Community Metrics Strip --}}
    <section aria-label="Statistik Lingkungan" class="mb-8 grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Card 1: Saldo Kas Bersama --}}
        <a href="{{ route('dues.cashbook') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-emerald-300 transition no-underline flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Saldo Kas RT</span>
                <div class="size-9 rounded-xl bg-emerald-50 text-daun border border-emerald-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="2" y2="22"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5">
                <p class="text-lg sm:text-xl font-black text-slate-900 tabular-nums leading-tight">{{ rupiah($totalBalance ?? 0) }}</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 group-hover:text-daun transition-colors">
                    <span>Kas transparan</span>
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </p>
            </div>
        </a>

        {{-- Card 2: Rumah & KK Terdata --}}
        <a href="{{ route('dues.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-blue-300 transition no-underline flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Warga Terdata</span>
                <div class="size-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5">
                <p class="text-lg sm:text-xl font-black text-slate-900 leading-tight">{{ $totalHouseholds ?? 0 }} Rumah / KK</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 group-hover:text-blue-600 transition-colors">
                    <span>Lihat daftar iuran</span>
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </p>
            </div>
        </a>

        {{-- Card 3: Agenda Kegiatan --}}
        <a href="{{ route('posts.index', ['kategori' => 'kegiatan']) }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-amber-300 transition no-underline flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Agenda Warga</span>
                <div class="size-9 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5">
                <p class="text-lg sm:text-xl font-black text-slate-900 leading-tight">{{ $upcoming->count() }} Kegiatan</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 group-hover:text-amber-700 transition-colors">
                    <span>{{ $upcoming->count() > 0 ? 'Jadwal mendatang' : 'Belum ada agenda' }}</span>
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </p>
            </div>
        </a>

        {{-- Card 4: Kabar & Pengumuman --}}
        <a href="{{ route('posts.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-purple-300 transition no-underline flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kabar Warga</span>
                <div class="size-9 rounded-xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/>
                        <path d="M18 14h-8"/>
                        <path d="M15 18h-5"/>
                        <path d="M10 6h8v4h-8V6Z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2.5">
                <p class="text-lg sm:text-xl font-black text-slate-900 leading-tight">{{ $latest->count() + $pinned->count() }} Terbitan</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 group-hover:text-purple-700 transition-colors">
                    <span>Informasi lingkungan</span>
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </p>
            </div>
        </a>
    </section>

    {{-- Pengumuman yang Disematkan (Pinned Headline) --}}
    @if ($pinned->isNotEmpty())
        <section aria-labelledby="dipasang" class="mb-8 space-y-4">
            <h2 id="dipasang" class="sr-only">Pengumuman Penting</h2>
            @foreach ($pinned as $post)
                <article class="bg-white rounded-3xl border border-amber-200/80 shadow-xs p-5 sm:p-6 transition hover:shadow-md relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-400 to-terakota"></div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                            <svg class="size-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                            Disematkan
                        </span>
                        <x-stamp :category="$post->category" />
                        <span>•</span>
                        <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                    </div>

                    <h3 class="font-bold text-lg sm:text-xl text-slate-900 leading-snug break-words">
                        <a href="{{ route('posts.show', $post) }}" class="text-slate-900 hover:text-daun transition no-underline">
                            {{ $post->title }}
                        </a>
                    </h3>

                    @if ($post->category === 'kegiatan' && $post->event_starts_at)
                        <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-900 text-xs font-semibold border border-emerald-100">
                            <svg class="size-3.5 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>{{ $post->event_starts_at->translatedFormat('l, j F Y · H.i') }} WIB @if ($post->event_location) · {{ $post->event_location }} @endif</span>
                        </div>
                    @endif

                    <p class="mt-2.5 text-xs sm:text-sm text-slate-600 leading-relaxed break-words">{{ $post->excerpt(260) }}</p>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('posts.show', $post) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-daun hover:text-daun-dark transition">
                            <span>Baca selengkapnya</span>
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    {{-- Main Grid Content: Latest News + Sidebar --}}
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] items-start">
        {{-- Left: Latest Posts --}}
        <section aria-labelledby="kabar-terbaru">
            <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-200/80">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-daun"></span>
                    <h2 id="kabar-terbaru" class="font-bold text-xl text-slate-900 tracking-tight">Kabar Terbaru</h2>
                </div>
                <a href="{{ route('posts.index') }}" class="text-xs font-bold text-daun hover:text-daun-dark flex items-center gap-1 no-underline">
                    <span>Semua kabar</span>
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="space-y-4">
                @forelse ($latest as $post)
                    <x-post-card :post="$post" />
                @empty
                    <div class="sheet p-8 text-center bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                        <div class="size-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-700">Belum ada kabar lain</h3>
                        <p class="text-xs text-slate-500 mt-1">Pengumuman dan info kegiatan warga akan segera ditampilkan di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Right: Sidebar (Dues Progress, Upcoming Events & Community Contacts) --}}
        <aside class="space-y-6">
            {{-- Dues Progress Card --}}
            <section aria-labelledby="iuran-bulan-ini" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
                <div class="flex items-center justify-between gap-2 mb-4 pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="size-7 rounded-lg bg-emerald-50 text-daun border border-emerald-100 flex items-center justify-center">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2"/>
                                <line x1="2" x2="22" y1="10" y2="10"/>
                            </svg>
                        </div>
                        <h2 id="iuran-bulan-ini" class="font-bold text-base text-slate-900">Iuran Berjalan</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Bulan ini</span>
                </div>

                @forelse ($progress as $row)
                    @php $pct = $row['total'] ? round($row['paid'] / $row['total'] * 100) : 0; @endphp
                    <a href="{{ route('dues.index', ['jenis' => $row['type']->id]) }}" class="block no-underline p-3 rounded-xl hover:bg-slate-50 transition-colors mb-3 last:mb-0 border border-slate-100 group">
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="font-bold text-sm text-slate-900 group-hover:text-daun transition-colors">{{ $row['type']->name }}</span>
                            <span class="text-xs font-bold text-daun-dark bg-daun-soft px-2 py-0.5 rounded-full">{{ $pct }}%</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full bg-gradient-to-r from-daun to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $row['paid'] }} dari {{ $row['total'] }} rumah sudah bayar</span>
                            <span class="text-[11px] text-slate-400">{{ $row['label'] }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-slate-500 py-2">Belum ada iuran yang aktif saat ini.</p>
                @endforelse

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('dues.index') }}" class="btn btn-sm btn-quiet w-full text-xs font-bold flex items-center justify-center gap-1.5">
                        <span>Lihat Status Semua Rumah</span>
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </section>

            {{-- Upcoming Events Card --}}
            <section aria-labelledby="kegiatan-mendatang" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100">
                    <div class="size-7 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" x2="16" y1="2" y2="6"/>
                            <line x1="8" x2="8" y1="2" y2="6"/>
                            <line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <h2 id="kegiatan-mendatang" class="font-bold text-base text-slate-900">Kegiatan Mendatang</h2>
                </div>

                <div class="space-y-3">
                    @forelse ($upcoming as $post)
                        <a href="{{ route('posts.show', $post) }}" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors no-underline text-slate-900 group border border-slate-100/70">
                            <x-event-date :at="$post->event_starts_at" />
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-sm text-slate-900 group-hover:text-daun transition-colors leading-snug line-clamp-2">{{ $post->title }}</p>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 truncate">
                                    <svg class="size-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $post->event_starts_at->format('H.i') }} WIB
                                    @if ($post->event_location) · {{ $post->event_location }} @endif
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-500 py-2">Belum ada kegiatan yang dijadwalkan.</p>
                    @endforelse
                </div>
            </section>

            {{-- Community Info & WhatsApp Help Card (Warm Civic Editorial) --}}
            <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-daun-dark">
                    <svg class="size-4 text-daun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Layanan Pengurus RT</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Butuh bantuan konfirmasi iuran, surat pengantar, atau informasi lingkungan? Hubungi pengurus RT langsung.
                </p>
                @if (setting('treasurer_contact'))
                    @php
                        $waClean = preg_replace('/[^0-9]/', '', setting('treasurer_contact'));
                        if (str_starts_with($waClean, '0')) {
                            $waClean = '62' . substr($waClean, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $waClean }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-daun hover:bg-daun-dark text-white font-bold text-xs shadow-xs transition active:scale-95 no-underline">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.146-.538-1.748-.723-2.883-2.484-2.969-2.6-.088-.117-.714-.95-.714-1.815 0-.866.452-1.293.613-1.469.16-.176.353-.22.47-.22.118 0 .235.001.338.006.108.005.253-.041.396.303.144.344.492 1.198.535 1.285.044.088.073.19.015.308-.058.118-.088.19-.175.293-.088.103-.186.23-.266.308-.088.088-.18.185-.078.361.103.176.457.755.981 1.222.676.602 1.246.788 1.423.876.176.088.279.073.382-.044.103-.117.44-.514.557-.69.118-.176.235-.147.397-.088.161.059 1.028.485 1.205.573.176.088.293.132.338.206.044.073.044.426-.1.831z"/>
                        </svg>
                        <span>WhatsApp Pengurus</span>
                    </a>
                @endif
            </section>
        </aside>
    </div>

    {{-- Modal Popup Pengumuman Otomatis --}}
    @if (isset($popupPost) && $popupPost)
        <div id="home-announcement-popup"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/65 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none"
             data-popup-id="{{ $popupPost->id }}"
             role="dialog"
             aria-modal="true"
             aria-labelledby="popup-title">

            @if ($popupPost->popupImageUrl())
                {{-- Mode Full Foto / Poster Popup --}}
                <div id="home-announcement-panel"
                     class="relative max-w-sm sm:max-w-md w-full bg-transparent rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl transform transition-all duration-300 scale-95 flex flex-col items-center">

                    {{-- Close Button --}}
                    <button type="button"
                            id="popup-close-btn"
                            class="absolute top-3 right-3 z-20 size-9 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center shadow-lg transition active:scale-90 cursor-pointer"
                            aria-label="Tutup Pengumuman">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    <h2 id="popup-title" class="sr-only">{{ $popupPost->title }}</h2>

                    {{-- Seluruh foto diklik langsung ke detail pengumuman --}}
                    <a href="{{ route('posts.show', $popupPost) }}"
                       class="block group relative w-full overflow-hidden rounded-2xl sm:rounded-3xl border border-white/20 bg-slate-900/60 focus:outline-hidden focus:ring-4 focus:ring-emerald-500/40 transition">
                        <img src="{{ $popupPost->popupImageUrl() }}"
                             alt="{{ $popupPost->title }}"
                             class="w-full max-h-[80vh] object-contain mx-auto rounded-2xl sm:rounded-3xl transition-transform duration-300 group-hover:scale-[1.015]">

                        {{-- Action bar di bagian bawah poster --}}
                        <div class="absolute inset-x-0 bottom-0 p-3 sm:p-4 bg-gradient-to-t from-black/85 via-black/45 to-transparent flex items-center justify-between gap-3 text-white">
                            <span class="text-xs sm:text-sm font-semibold truncate drop-shadow-xs">
                                {{ $popupPost->title }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-daun hover:bg-daun-dark text-white text-xs font-bold shrink-0 shadow-md transition group-hover:translate-x-0.5">
                                <span>Detail</span>
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </span>
                        </div>
                    </a>
                </div>
            @else
                {{-- Mode Kartu Pengumuman Standar --}}
                <div id="home-announcement-panel"
                     class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all duration-300 scale-95">

                    {{-- Close Button --}}
                    <button type="button"
                            id="popup-close-btn"
                            class="absolute top-4 right-4 z-10 size-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition active:scale-90"
                            aria-label="Tutup Pengumuman">
                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    @if ($popupPost->imageUrl())
                        <div class="relative h-44 sm:h-52 w-full overflow-hidden bg-slate-100">
                            <img src="{{ $popupPost->imageUrl() }}" alt="{{ $popupPost->title }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        </div>
                    @endif

                    <div class="p-6 sm:p-7">
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                Pengumuman Penting
                            </span>
                            <x-stamp :category="$popupPost->category" />
                        </div>

                        <h2 id="popup-title" class="font-bold text-xl sm:text-2xl text-slate-900 leading-snug break-words">
                            {{ $popupPost->title }}
                        </h2>

                        @if ($popupPost->category === 'kegiatan' && $popupPost->event_starts_at)
                            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-900 text-xs font-semibold border border-emerald-100">
                                <svg class="size-4 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span>{{ $popupPost->event_starts_at->translatedFormat('l, j F Y · H.i') }} WIB @if ($popupPost->event_location) · {{ $popupPost->event_location }} @endif</span>
                            </div>
                        @endif

                        <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3 break-words">
                            {{ $popupPost->excerpt(200) }}
                        </p>

                        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-2">
                            <a href="{{ route('posts.show', $popupPost) }}" class="btn btn-primary justify-center text-sm py-2.5 flex-1 shadow-sm">
                                <span>Baca Detail Lengkap</span>
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </a>
                            <button type="button" id="popup-dismiss-btn" class="btn btn-quiet justify-center text-xs py-2.5">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-layouts.public>
