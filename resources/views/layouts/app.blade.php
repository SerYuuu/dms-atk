@php
    $icons = [
        'home'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
        'upload'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>',
        'tag'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z"/>',
        'chart'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        'clock'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];

    $sections = [
        'OPERASIONAL' => [
            ['label' => 'Dashboard',       'route' => 'dashboard',        'match' => 'dashboard',   'icon' => 'home'],
            ['label' => 'Arsip Dokumen',   'route' => 'documents.index',  'match' => 'documents.index|documents.show|documents.edit', 'icon' => 'document'],
            ['label' => 'Unggah Dokumen',  'route' => 'documents.create', 'match' => 'documents.create|documents.verify', 'icon' => 'upload'],
        ],
        'MASTER DATA' => [
            ['label' => 'Kategori Dokumen','route' => 'categories.index', 'match' => 'categories.*', 'icon' => 'tag'],
        ],
        'ANALITIK & SISTEM' => [
            ['label' => 'Laporan Rekap',   'route' => 'reports.index',    'match' => 'reports.*',    'icon' => 'chart'],
            ['label' => 'Log Aktivitas',   'route' => 'audit-logs.index', 'match' => 'audit-logs.*', 'icon' => 'clock', 'only' => 'pemilik'],
        ],
    ];

    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · DMS UMKM Toko ATK</title>
    
    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

{{-- Mobile Sidebar Backdrop --}}
<div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm transition-opacity lg:hidden"></div>

{{-- Sidebar Navigation --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-800/80 bg-surface-900 text-slate-300 shadow-2xl transition-transform duration-300 ease-in-out lg:translate-x-0">
    
    {{-- Logo / App Brand Header --}}
    <div class="flex h-20 items-center justify-between border-b border-slate-800/60 px-6">
        <a href="{{ route('dashboard') }}" class="group flex items-center gap-3.5">
            <div class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-brand-600 to-teal-400 shadow-md shadow-brand-500/20 ring-1 ring-white/20 transition-transform group-hover:scale-105">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div>
                <span class="block text-base font-bold tracking-tight text-white">DMS Toko ATK</span>
                <span class="block text-[11px] font-medium tracking-wider text-slate-400 uppercase">Sistem Arsip & OCR</span>
            </div>
        </a>

        <span class="inline-flex items-center rounded-full bg-brand-500/10 px-2 py-0.5 text-[10px] font-bold text-brand-400 ring-1 ring-inset ring-brand-500/30">
            PRO
        </span>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6" aria-label="Menu Navigasi">
        @foreach ($sections as $title => $items)
            @php
                $visibleItems = collect($items)->filter(fn($item) => !isset($item['only']) || $user->role === $item['only'] || $user->isPemilik());
            @endphp

            @if ($visibleItems->isNotEmpty())
                <div>
                    <h3 class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        {{ $title }}
                    </h3>
                    <div class="space-y-1">
                        @foreach ($visibleItems as $item)
                            @php
                                $exists = Route::has($item['route']);
                                $active = $exists && collect(explode('|', $item['match']))->contains(fn ($p) => request()->routeIs($p));
                            @endphp

                            @if ($exists)
                                <a href="{{ route($item['route']) }}"
                                   @if ($active) aria-current="page" @endif
                                   class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-150
                                          {{ $active 
                                              ? 'bg-gradient-to-r from-brand-500/20 to-brand-500/5 text-brand-400 shadow-sm ring-1 ring-inset ring-brand-500/30 font-semibold' 
                                              : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg transition-colors
                                                {{ $active ? 'bg-brand-500/20 text-brand-400' : 'text-slate-400 group-hover:text-slate-200' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                            {!! $icons[$item['icon']] !!}
                                        </svg>
                                    </div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    @if ($active)
                                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-brand-400 shadow-[0_0_8px_#34d399]"></span>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- User Profile Card & Logout Footer --}}
    <div class="border-t border-slate-800/80 p-4 bg-slate-950/40">
        <div class="flex items-center gap-3 rounded-xl bg-slate-900/80 p-3 ring-1 ring-white/5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-brand-600 to-emerald-400 text-sm font-bold text-white shadow-inner">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-white">{{ $user->name }}</p>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                    <span class="text-xs text-slate-400 capitalize">{{ $user->roleLabel() }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" title="Keluar dari sesi">
                @csrf
                <button type="submit"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-500/10 hover:text-red-400 focus:outline-none">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main Page Content Wrapper --}}
<div class="flex min-h-screen flex-col lg:pl-72">
    
    {{-- Top Navbar Bar --}}
    <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 backdrop-blur-md lg:px-8 shadow-xs">
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" type="button" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition-colors hover:bg-slate-100 lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">@yield('heading', 'Dashboard')</h1>
                    @hasSection('subheading')
                        <span class="text-slate-300">/</span>
                        <span class="text-sm font-medium text-slate-500">@yield('subheading')</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Date display badge --}}
            <div class="hidden items-center gap-2 rounded-xl bg-slate-100/80 px-3.5 py-1.5 text-xs font-semibold text-slate-600 sm:flex ring-1 ring-slate-200/50">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253 18.75m3-18.75H3.75a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25V5.25a2.25 2.25 0 00-2.25-2.25z"/>
                </svg>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>

            @if (!request()->routeIs('documents.create'))
                <a href="{{ route('documents.create') }}" class="btn-primary !py-2 !text-xs sm:!text-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Unggah Nota</span>
                </a>
            @endif
        </div>
    </header>

    {{-- Main Body --}}
    <main class="flex-1 px-4 py-8 lg:px-8 max-w-7xl w-full mx-auto">
        
        {{-- Flash Feedback Alerts with Crisp Icons & Dismiss button --}}
        @if (session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-emerald-900 shadow-sm" role="status">
                <svg class="h-5 w-5 shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm font-medium leading-relaxed">{{ session('status') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-rose-900 shadow-sm" role="alert">
                <svg class="h-5 w-5 shrink-0 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <div class="text-sm font-medium leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/90 p-4 text-amber-900 shadow-sm" role="status">
                <svg class="h-5 w-5 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <div class="text-sm font-medium leading-relaxed">{{ session('warning') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Subtle Page Footer --}}
    <footer class="border-t border-slate-200/70 bg-white/50 py-4 px-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Sistem Manajemen Dokumen (DMS) UMKM Toko ATK. Powered by Tesseract OCR & Laravel.
    </footer>
</div>

<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const toggle = document.getElementById('sidebar-toggle');

        function setOpen(open) {
            sidebar.classList.toggle('-translate-x-full', !open);
            backdrop.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', String(open));
        }

        toggle.addEventListener('click', () => setOpen(sidebar.classList.contains('-translate-x-full')));
        backdrop.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    })();
</script>
@stack('scripts')
</body>
</html>
