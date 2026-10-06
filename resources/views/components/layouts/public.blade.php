@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#16a34a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>{{ $title ? $title.' · ' : '' }}{{ setting('site_name') }}</title>
    <meta name="description" content="{{ $description ?? (setting('site_tagline') ?: 'Kabar, kegiatan, dan catatan iuran warga '.setting('site_name').'.') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-900 pb-20 lg:pb-0 antialiased selection:bg-daun-soft selection:text-daun-dark print:bg-white print:pb-0 print:text-black">
    <a href="#isi" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 btn btn-primary z-50 shadow-lg print:hidden">Langsung ke isi</a>

    {{-- Top App Bar --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-shadow print:hidden">
        <div class="mx-auto max-w-5xl px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 no-underline group min-w-0">
                <div class="size-10 rounded-xl bg-gradient-to-br from-daun to-emerald-700 flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform shrink-0">
                    <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block font-bold text-lg text-slate-900 leading-tight truncate tracking-tight">{{ setting('site_name') }}</span>
                    <span class="block text-xs text-slate-500 font-medium truncate">{{ setting('site_tagline') ?: 'Portal Warga Digital' }}</span>
                </div>
            </a>

            {{-- Desktop Navigation (hidden on mobile, native bottom bar used instead) --}}
            <nav aria-label="Menu utama" class="hidden lg:flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                @php
                    $navItems = [
                        'home' => ['Beranda', 'home'],
                        'posts.index' => ['Kabar', 'posts.*'],
                        'dues.index' => ['Iuran Warga', 'dues.index'],
                        'pay.create' => ['Bayar Iuran', 'pay.*'],
                        'dues.cashbook' => ['Kas RT', 'dues.cashbook'],
                    ];
                @endphp
                @foreach ($navItems as $route => [$label, $pattern])
                    @php $active = request()->routeIs($pattern); @endphp
                    <a href="{{ route($route) }}"
                       @if ($active) aria-current="page" @endif
                       class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all no-underline {{ $active ? 'bg-white text-daun-dark shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden sm:flex items-center gap-2">
                @auth
                    <a href="{{ route('admin.home') }}" class="btn btn-sm btn-quiet flex items-center gap-1.5 text-xs text-daun-dark font-bold">
                        <span class="size-2 rounded-full bg-daun animate-pulse"></span>
                        Panel Pengurus
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-quiet text-xs font-bold text-slate-700">
                        Masuk Pengurus
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main id="isi" class="flex-1 mx-auto w-full max-w-5xl px-4 py-5 sm:py-8 focus:outline-none print:p-0 print:m-0 print:max-w-none">
        <x-flash />
        {{ $slot }}
    </main>

    {{-- Desktop & Tablet Footer --}}
    <footer class="mt-auto border-t border-slate-200/80 bg-white print:hidden">
        <div class="mx-auto max-w-5xl px-4 py-8 text-sm text-slate-500">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <p class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-daun"></span>
                        {{ setting('site_name') }}
                    </p>
                    @if (setting('address'))
                        <p class="text-xs text-slate-500">{{ setting('address') }}</p>
                    @endif
                    @if (setting('treasurer_contact'))
                        <p class="text-xs text-slate-500">Kontak Bendahara: <strong class="text-slate-700">{{ setting('treasurer_contact') }}</strong></p>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-xs">
                    @auth
                        <a href="{{ route('admin.home') }}" class="font-bold text-daun hover:underline">Panel Pengurus</a>
                    @else
                        <a href="{{ route('login') }}" class="font-bold text-slate-600 hover:text-daun">Akses Khusus Pengurus</a>
                    @endauth
                    <span class="text-slate-300">·</span>
                    <span>Kabar Warga v2.0</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Mobile Bottom Navigation Bar (Native App Style) --}}
    <nav aria-label="Navigasi bawah mobile" class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200/90 shadow-lg px-2 pt-1 pb-[max(0.5rem,env(safe-area-inset-bottom))] print:hidden">
        <div class="grid grid-cols-5 items-center justify-around">
            {{-- Tab 1: Beranda --}}
            @php $isHome = request()->routeIs('home'); @endphp
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isHome ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isHome ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    @if ($isHome)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Beranda</span>
            </a>

            {{-- Tab 2: Kabar --}}
            @php $isPosts = request()->routeIs('posts.*'); @endphp
            <a href="{{ route('posts.index') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isPosts ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isPosts ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                        <path d="M18 14h-8"/>
                        <path d="M15 18h-5"/>
                        <path d="M10 6h8v4h-8V6Z"/>
                    </svg>
                    @if ($isPosts)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Kabar</span>
            </a>

            @php $isPay = request()->routeIs('pay.*'); @endphp
            <a href="{{ route('pay.create') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isPay ? 'text-daun font-bold' : 'text-slate-700 font-semibold' }}">
                <div class="p-0.5">
                    <div class="size-8 rounded-lg {{ $isPay ? 'bg-daun-dark' : 'bg-daun' }} text-white shadow-xs flex items-center justify-center">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                            <line x1="6" x2="10" y1="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Bayar</span>
            </a>

            {{-- Tab 3: Cek Iuran --}}
            @php $isDues = request()->routeIs('dues.index'); @endphp
            <a href="{{ route('dues.index') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isDues ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isDues ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="8" height="4" x="8" y="2" rx="1"/>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <path d="m9 14 2 2 4-4"/>
                    </svg>
                    @if ($isDues)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Cek Iuran</span>
            </a>

            {{-- Tab 4: Kas RT --}}
            @php $isCashbook = request()->routeIs('dues.cashbook'); @endphp
            <a href="{{ route('dues.cashbook') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isCashbook ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isCashbook ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="2" y2="22"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    @if ($isCashbook)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Kas RT</span>
            </a>
        </div>
    </nav>
</body>
</html>
