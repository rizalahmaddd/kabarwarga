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
        $pendingCount = \App\Models\PaymentSubmission::pending()->count();

        $menuGroups = [
            'Keuangan & Iuran' => [
                'admin.home' => [
                    'label' => 'Catat Bayar',
                    'pattern' => 'admin.home',
                    'icon' => 'plus',
                ],
                'admin.submissions.index' => [
                    'label' => 'Konfirmasi Bayar',
                    'pattern' => 'admin.submissions.*',
                    'icon' => 'check-badge',
                    'badge' => $pendingCount,
                ],
                'admin.payments.ledger' => [
                    'label' => 'Buku Iuran',
                    'pattern' => 'admin.payments.ledger',
                    'icon' => 'ledger',
                ],
                'admin.payments.index' => [
                    'label' => 'Riwayat Bayar',
                    'pattern' => 'admin.payments.index',
                    'icon' => 'history',
                ],
                'admin.expenses.index' => [
                    'label' => 'Kas & Pengeluaran',
                    'pattern' => 'admin.expenses.*',
                    'icon' => 'expense',
                ],
            ],
            'Warga & Konten' => [
                'admin.posts.index' => [
                    'label' => 'Kabar Warga',
                    'pattern' => 'admin.posts.*',
                    'icon' => 'posts',
                ],
                'admin.households.index' => [
                    'label' => 'Rumah Warga',
                    'pattern' => 'admin.households.*',
                    'icon' => 'household',
                ],
            ],
            'Master & Pengaturan' => [
                'admin.dues-types.index' => [
                    'label' => 'Jenis Iuran',
                    'pattern' => 'admin.dues-types.*',
                    'icon' => 'tag',
                ],
                'admin.bank-accounts.index' => [
                    'label' => 'Rekening Bank',
                    'pattern' => 'admin.bank-accounts.*',
                    'icon' => 'bank',
                ],
                'admin.users.index' => [
                    'label' => 'Kelola Pengurus',
                    'pattern' => 'admin.users.*',
                    'icon' => 'users',
                ],
                'admin.settings.edit' => [
                    'label' => 'Pengaturan Portal',
                    'pattern' => 'admin.settings.*',
                    'icon' => 'settings',
                ],
            ],
        ];

        $renderIcon = function(string $icon): string {
            return match($icon) {
                'plus' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
                'check-badge' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                'ledger' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/></svg>',
                'history' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                'expense' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                'posts' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>',
                'household' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>',
                'tag' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
                'bank' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
                'users' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'settings' => '<svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
                default => '',
            };
        };
    @endphp

    {{-- Desktop Sidebar --}}    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:w-64 lg:flex lg:flex-col lg:bg-slate-900 lg:text-white lg:border-r lg:border-slate-800 lg:shadow-xl no-scrollbar [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        {{-- Brand / Header --}}
        <div class="h-16 px-4 flex items-center gap-3 border-b border-slate-800 shrink-0">
            <a href="{{ route('admin.home') }}" class="size-9 rounded-xl bg-gradient-to-br from-daun to-emerald-700 flex items-center justify-center text-white shadow-xs shrink-0 no-underline">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </a>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-sm text-white leading-tight truncate">{{ setting('site_name') }}</span>
                </div>
                <span class="inline-block text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 mt-0.5 rounded bg-daun/30 text-emerald-300 border border-emerald-500/30">Pengurus RT</span>
            </div>
        </div>

        {{-- User Info Card --}}
        <div class="px-4 py-3 border-b border-slate-800 bg-slate-950/40 shrink-0">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</p>
                    <p class="text-[11px] text-slate-400 truncate leading-tight mt-0.5">{{ auth()->user()->email ?? 'Pengurus' }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation Menu Groups --}}
        <nav id="admin-desktop-sidebar-nav" class="flex-1 overflow-y-auto no-scrollbar [scrollbar-width:none] [&::-webkit-scrollbar]:hidden px-3 py-4 space-y-5" aria-label="Menu samping pengurus">
            @foreach ($menuGroups as $groupTitle => $items)
                <div>
                    <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        {{ $groupTitle }}
                    </div>
                    <ul class="space-y-1">
                        @foreach ($items as $route => $item)
                            @php
                                $isActive = request()->routeIs($item['pattern']);
                            @endphp
                            <li>
                                <a href="{{ route($route) }}"
                                   @if ($isActive) aria-current="page" @endif
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all no-underline {{ $isActive ? 'bg-daun text-white shadow-xs font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/80' }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="{{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-emerald-400' }} transition-colors">
                                            {!! $renderIcon($item['icon']) !!}
                                        </span>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </div>
                                    @if (!empty($item['badge']) && $item['badge'] > 0)
                                        <span class="ml-2 px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white text-daun-dark' : 'bg-amber-500 text-white' }}">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <script>
            (function() {
                var nav = document.getElementById('admin-desktop-sidebar-nav');
                if (!nav) return;

                function restoreSidebarScroll() {
                    var saved = sessionStorage.getItem('admin_sidebar_scroll');
                    if (saved !== null) {
                        var target = parseInt(saved, 10);
                        if (!isNaN(target)) {
                            nav.scrollTop = target;
                        }
                    }
                }

                // Restore scroll at multiple stages to survive layout/CSS engine timing
                restoreSidebarScroll();
                if (window.requestAnimationFrame) {
                    requestAnimationFrame(restoreSidebarScroll);
                }
                setTimeout(restoreSidebarScroll, 0);
                setTimeout(restoreSidebarScroll, 30);
                setTimeout(restoreSidebarScroll, 100);

                window.addEventListener('DOMContentLoaded', restoreSidebarScroll);
                window.addEventListener('load', restoreSidebarScroll);
                window.addEventListener('pageshow', restoreSidebarScroll);

                // Save scroll position immediately on scrolling
                nav.addEventListener('scroll', function() {
                    sessionStorage.setItem('admin_sidebar_scroll', nav.scrollTop);
                }, { passive: true });

                // Save scroll position before navigating via any link inside sidebar
                nav.addEventListener('click', function(e) {
                    var link = e.target.closest('a');
                    if (link) {
                        sessionStorage.setItem('admin_sidebar_scroll', nav.scrollTop);
                    }
                });

                window.addEventListener('beforeunload', function() {
                    sessionStorage.setItem('admin_sidebar_scroll', nav.scrollTop);
                });
            })();
        </script>

        {{-- Sidebar Footer Actions --}}
        <div class="p-3 border-t border-slate-800 bg-slate-950/60 space-y-1 shrink-0">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors no-underline">
                <svg class="size-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/>
                    <line x1="10" x2="21" y1="14" y2="3"/>
                </svg>
                <span>Lihat Web Warga</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-300 hover:text-rose-200 hover:bg-rose-950/40 transition-colors cursor-pointer text-left">
                    <svg class="size-4 text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                    <span>Keluar dari Admin</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Layout Wrapper (offset on desktop for sidebar) --}}
    <div class="lg:pl-64 flex flex-col flex-1 min-w-0">
        {{-- Mobile Top Bar --}}
        <header class="lg:hidden sticky top-0 z-30 bg-slate-900 text-white shadow-md">
            <div class="px-4 h-16 flex items-center justify-between gap-4">
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
                        <span class="hidden sm:inline">Web Warga</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-300 hover:text-red-200 hover:bg-red-950/40 transition-colors cursor-pointer">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Desktop Top Bar (Clean breadcrumb/header & quick actions) --}}
        <header class="hidden lg:flex sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs font-medium text-slate-400">Pengurus RT</span>
                    <span class="text-xs text-slate-300">/</span>
                    <h1 class="text-sm font-bold text-slate-800 tracking-tight truncate">{{ $title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    @if ($pendingCount)
                        <a href="{{ route('admin.submissions.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold hover:bg-amber-100/80 transition-colors no-underline">
                            <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>{{ $pendingCount }} Konfirmasi Menunggu</span>
                        </a>
                    @endif

                    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:text-daun hover:border-daun/40 hover:bg-emerald-50/50 transition-colors no-underline">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" x2="21" y1="14" y2="3"/>
                        </svg>
                        <span>Lihat Web Warga</span>
                    </a>

                    <div class="h-4 w-px bg-slate-200"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors cursor-pointer">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Main Content Container --}}
        <main id="isi" class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-5 sm:py-8 focus:outline-none">
            <x-flash />
            @if ($pendingCount && ! request()->routeIs('admin.submissions.*'))
                <a href="{{ route('admin.submissions.index') }}" class="mb-6 flex items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-950 no-underline hover:bg-amber-100/70 transition-colors">
                    <span class="text-sm font-semibold">{{ $pendingCount }} bukti bayar dari warga menunggu konfirmasi.</span>
                    <span class="btn btn-sm bg-amber-500 text-white text-xs shrink-0">Cek sekarang</span>
                </a>
            @endif
            {{ $slot }}
        </main>
    </div>

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
        <div id="admin-drawer-panel" class="absolute inset-x-0 bottom-0 bg-white rounded-t-3xl shadow-2xl p-5 pb-8 transition-transform translate-y-full max-h-[85vh] overflow-y-auto no-scrollbar [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
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
