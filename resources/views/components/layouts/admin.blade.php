@props(['title'])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · Pengurus {{ setting('site_name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-900 pb-20 lg:pb-0 antialiased selection:bg-daun-soft selection:text-daun-dark">
    <a href="#isi" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 btn btn-primary z-50 shadow-lg">Langsung ke isi</a>

    @php
        $primaryMenu = [
            'admin.home' => ['Catat Bayar', 'admin.home'],
            'admin.submissions.index' => ['Konfirmasi Bayar', 'admin.submissions.*'],
            'admin.payments.ledger' => ['Buku Iuran', 'admin.payments.ledger'],
            'admin.payments.index' => ['Riwayat Bayar', 'admin.payments.index'],
            'admin.expenses.index' => ['Kas & Pengeluaran', 'admin.expenses.*'],
            'admin.posts.index' => ['Kabar Warga', 'admin.posts.*'],
            'admin.households.index' => ['Rumah', 'admin.households.*'],
            'admin.dues-types.index' => ['Jenis Iuran', 'admin.dues-types.*'],
            'admin.bank-accounts.index' => ['Rekening', 'admin.bank-accounts.*'],
            'admin.settings.edit' => ['Pengaturan', 'admin.settings.*'],
            'admin.users.index' => ['Pengurus', 'admin.users.*'],
        ];
        $pendingCount = \App\Models\PaymentSubmission::pending()->count();
    @endphp

    {{-- Top Admin Bar --}}
    <header class="sticky top-0 z-30 bg-slate-900 text-white shadow-md">
        <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('admin.home') }}" class="size-9 rounded-xl bg-gradient-to-br from-daun to-emerald-700 flex items-center justify-center text-white shadow-xs shrink-0 no-underline">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white leading-tight truncate">{{ setting('site_name') }}</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-daun/30 text-emerald-300 border border-emerald-500/30">Pengurus</span>
                    </div>
                    <span class="block text-xs text-slate-400 truncate">{{ auth()->user()->name ?? 'Administrator' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition-colors no-underline">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" x2="21" y1="14" y2="3"/>
                    </svg>
                    <span class="hidden sm:inline">Lihat Web Warga</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-300 hover:text-red-200 hover:bg-red-950/40 transition-colors cursor-pointer">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" x2="9" y1="12" y2="12"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        {{-- Desktop Sub-Nav --}}
        <div class="hidden lg:block border-t border-slate-800 bg-slate-900/90">
            <nav aria-label="Menu pengurus" class="mx-auto max-w-6xl px-4">
                <ul class="flex items-center gap-1 py-1.5 overflow-x-auto no-scrollbar">
                    @foreach ($primaryMenu as $route => [$label, $pattern])
                        @php $active = request()->routeIs($pattern); @endphp
                        <li>
                            <a href="{{ route($route) }}"
                               @if ($active) aria-current="page" @endif
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors no-underline {{ $active ? 'bg-daun text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                {{ $label }}
                                @if ($route === 'admin.submissions.index' && $pendingCount)
                                    <span class="ml-1.5 px-1.5 rounded-full bg-amber-500 text-white text-[11px] font-bold">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </header>

    {{-- Main Content Container --}}
    <main id="isi" class="flex-1 mx-auto w-full max-w-6xl px-4 py-5 sm:py-8 focus:outline-none">
        <x-flash />
        @if ($pendingCount && ! request()->routeIs('admin.submissions.*'))
            <a href="{{ route('admin.submissions.index') }}" class="mb-6 flex items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-950 no-underline hover:bg-amber-100/70 transition-colors">
                <span class="text-sm font-semibold">{{ $pendingCount }} bukti bayar dari warga menunggu konfirmasi.</span>
                <span class="btn btn-sm bg-amber-500 text-white text-xs shrink-0">Cek sekarang</span>
            </a>
        @endif
        {{ $slot }}
    </main>

    {{-- Mobile Bottom Navigation Bar for Admin --}}
    <nav aria-label="Navigasi bawah pengurus" class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200 shadow-lg px-2 pt-1 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
        <div class="grid grid-cols-5 items-center justify-around">
            {{-- Tab 1: Catat Bayar (Prominent) --}}
            @php $isCatat = request()->routeIs('admin.home'); @endphp
            <a href="{{ route('admin.home') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isCatat ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <div class="size-8 rounded-lg {{ $isCatat ? 'bg-daun text-white shadow-xs' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" x2="12" y1="5" y2="19"/>
                            <line x1="5" x2="19" y1="12" y2="12"/>
                        </svg>
                    </div>
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Catat Bayar</span>
            </a>

            @php $isConfirm = request()->routeIs('admin.submissions.*'); @endphp
            <a href="{{ route('admin.submissions.index') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isConfirm ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isConfirm ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    @if ($pendingCount)
                        <span class="absolute -top-1 -right-2 min-w-4.5 h-4.5 px-1 rounded-full bg-amber-500 text-white text-[10px] font-bold leading-[1.125rem]">{{ $pendingCount }}</span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Konfirmasi</span>
            </a>

            {{-- Tab 2: Buku Iuran --}}
            @php $isLedger = request()->routeIs('admin.payments.ledger') || request()->routeIs('admin.payments.index'); @endphp
            <a href="{{ route('admin.payments.ledger') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isLedger ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isLedger ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                        <path d="M3 9h18"/>
                        <path d="M3 15h18"/>
                        <path d="M9 3v18"/>
                    </svg>
                    @if ($isLedger)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Buku Iuran</span>
            </a>

            {{-- Tab 4: Kabar --}}
            @php $isPosts = request()->routeIs('admin.posts.*'); @endphp
            <a href="{{ route('admin.posts.index') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 {{ $isPosts ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
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

            {{-- Tab 5: Menu Sheet Trigger --}}
            @php $isOther = request()->routeIs('admin.expenses.*') || request()->routeIs('admin.bank-accounts.*') || request()->routeIs('admin.households.*') || request()->routeIs('admin.dues-types.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.users.*'); @endphp
            <button type="button" data-admin-drawer-trigger class="flex flex-col items-center justify-center py-1 rounded-xl text-center no-underline transition-all active:scale-95 cursor-pointer {{ $isOther ? 'text-daun font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                <div class="relative p-1">
                    <svg class="size-5.5 {{ $isOther ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="14" rx="1"/>
                        <rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                    @if ($isOther)
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 size-1 rounded-full bg-daun"></span>
                    @endif
                </div>
                <span class="text-[11px] leading-tight mt-0.5">Menu Lain</span>
            </button>
        </div>
    </nav>

    {{-- Mobile Admin Bottom Sheet Drawer --}}
    <div id="admin-drawer" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
        {{-- Backdrop --}}
        <div id="admin-drawer-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity opacity-0"></div>

        {{-- Sheet Container --}}
        <div id="admin-drawer-panel" class="absolute inset-x-0 bottom-0 bg-white rounded-t-3xl shadow-2xl p-5 pb-8 transition-transform translate-y-full max-h-[85vh] overflow-y-auto">
            {{-- Drag Handle --}}
            <div class="mx-auto w-12 h-1.5 rounded-full bg-slate-300 mb-4"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-lg text-slate-900">Menu Pengurus</h2>
                    <p class="text-xs text-slate-500">Akses pengelolaan data RT/RW</p>
                </div>
                <button type="button" id="admin-drawer-close" class="size-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center cursor-pointer hover:bg-slate-200">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" x2="6" y1="6" y2="18"/>
                        <line x1="6" x2="18" y1="6" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 my-4">
                {{-- Rumah Warga --}}
                <a href="{{ route('admin.households.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-emerald-100 text-daun-dark flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Rumah Warga</span>
                        <span class="block text-[11px] text-slate-500">Daftar & nomor HP</span>
                    </div>
                </a>

                {{-- Jenis Iuran --}}
                <a href="{{ route('admin.dues-types.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Jenis Iuran</span>
                        <span class="block text-[11px] text-slate-500">Tarif & periode</span>
                    </div>
                </a>

                {{-- Riwayat Bayar --}}
                <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Riwayat Catat</span>
                        <span class="block text-[11px] text-slate-500">Koreksi & audit</span>
                    </div>
                </a>

                <a href="{{ route('admin.expenses.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" x2="12" y1="2" y2="22"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Pengeluaran</span>
                        <span class="block text-[11px] text-slate-500">Kas keluar</span>
                    </div>
                </a>

                <a href="{{ route('admin.bank-accounts.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-emerald-100 text-daun-dark flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Rekening</span>
                        <span class="block text-[11px] text-slate-500">Tujuan transfer warga</span>
                    </div>
                </a>

                {{-- Pengurus RT --}}
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Pengurus</span>
                        <span class="block text-[11px] text-slate-500">Akun pengelola</span>
                    </div>
                </a>

                {{-- Pengaturan Website --}}
                <a href="{{ route('admin.settings.edit') }}" class="col-span-2 flex items-center gap-3 p-3 rounded-2xl border border-slate-200/80 bg-slate-50 hover:bg-emerald-50 hover:border-daun/40 no-underline text-slate-800 transition-colors">
                    <div class="size-10 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block font-bold text-sm leading-snug">Pengaturan RT & Kontak</span>
                        <span class="block text-[11px] text-slate-500">Nama portal, petunjuk bayar, nomor WA</span>
                    </div>
                </a>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="btn btn-quiet flex-1 text-xs font-bold text-slate-700">
                    Buka Halaman Warga
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button class="btn btn-danger w-full text-xs font-bold">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
