<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Sistem Manajemen Dokumen (DMS) Toko ATK</title>
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white">
<div class="grid min-h-screen lg:grid-cols-12">

    {{-- Left Showcase Branding Panel (Visible on Desktop) --}}
    <aside class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-surface-950 via-surface-900 to-slate-900 p-12 text-white lg:col-span-5 lg:flex xl:col-span-5 shadow-2xl">
        {{-- Ambient decorative background glows --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-brand-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-teal-500/15 blur-3xl"></div>

        {{-- Top App Brand Header --}}
        <div class="relative z-10 flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-400 shadow-lg shadow-brand-500/25 ring-1 ring-white/20">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div>
                <span class="block text-lg font-bold tracking-tight text-white">DMS Toko ATK</span>
                <span class="block text-xs font-semibold tracking-wider text-slate-400 uppercase">Document Management System</span>
            </div>
        </div>

        {{-- Main Feature Highlights in Center --}}
        <div class="relative z-10 my-auto py-12 max-w-lg">
            <div class="inline-flex items-center gap-2 rounded-full bg-brand-500/15 px-3.5 py-1.5 text-xs font-bold text-brand-300 ring-1 ring-inset ring-brand-500/30 mb-6">
                <span class="h-2 w-2 rounded-full bg-brand-400"></span>
                Sistem Arsip & OCR Operasional
            </div>

            <h1 class="text-3xl font-extrabold leading-snug tracking-tight text-white xl:text-4xl">
                Pengarsipan Faktur & Nota Toko Jadi Cepat, Rapi, & Akurat.
            </h1>
            <p class="mt-4 text-sm leading-relaxed text-slate-300">
                Solusi manajemen dokumen digital khusus operasional toko alat tulis. Dilengkapi ekstraksi teks OCR instan untuk memindai nama supplier, nomor invoice, tanggal, dan nominal transaksi secara otomatis.
            </p>

            <div class="mt-8 space-y-3.5">
                <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-3.5 backdrop-blur-sm ring-1 ring-white/10">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-500/20 text-brand-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                    </span>
                    <div class="text-xs">
                        <span class="block font-bold text-white">Ekstraksi Teks OCR Otomatis</span>
                        <span class="text-slate-400">Kenali teks nota dan deteksi cap stempel lunas dalam hitungan detik.</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-3.5 backdrop-blur-sm ring-1 ring-white/10">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 text-blue-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="text-xs">
                        <span class="block font-bold text-white">Pemantau Jatuh Tempo Pembayaran</span>
                        <span class="text-slate-400">Hindari denda tagihan supplier dengan widget pengingat jatuh tempo.</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-3.5 backdrop-blur-sm ring-1 ring-white/10">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-2.171 7.106A9.012 9.012 0 0112 21a9.012 9.012 0 01-8.829-6.894A9.012 9.012 0 0112 3a9.012 9.012 0 018.829 11.106z"/>
                        </svg>
                    </span>
                    <div class="text-xs">
                        <span class="block font-bold text-white">Penyimpanan Terisolasi & Audit Trail</span>
                        <span class="text-slate-400">Seluruh berkas privat dan aktivitas terekam dalam jejak audit aman.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Disclaimer --}}
        <div class="relative z-10 flex items-center justify-between text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} Toko ATK Indonesia</span>
            <span>Versi 1.0 (Laravel 10)</span>
        </div>
    </aside>

    {{-- Right Login Form Panel --}}
    <main class="flex items-center justify-center px-6 py-12 lg:col-span-7 xl:col-span-7 bg-slate-50/70">
        <div class="w-full max-w-md">
            
            {{-- Mobile Brand Header --}}
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-md">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-base font-bold text-slate-900">DMS Toko ATK</span>
                    <span class="block text-[11px] font-semibold text-slate-500">Arsip & OCR Nota</span>
                </div>
            </div>

            {{-- Login Card Container --}}
            <div class="card-base p-8 sm:p-10 shadow-elevated">
                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Selamat Datang Kembali</h2>
                    <p class="mt-1.5 text-sm text-slate-500">
                        Masuk menggunakan kredensial akun Pemilik atau Admin toko Anda.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="username" class="field-label">Username Akun</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <input id="username" name="username" type="text" value="{{ old('username') }}"
                                   autocomplete="username" autofocus required placeholder="Masukkan username"
                                   class="field-input !pl-10 @error('username') border-rose-400 @enderror">
                        </div>
                        @error('username')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="field-label !mb-0">Kata Sandi</label>
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                            </div>
                            <input id="password" name="password" type="password"
                                   autocomplete="current-password" required placeholder="••••••••"
                                   class="field-input !pl-10 !pr-10 @error('password') border-rose-400 @enderror">
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3 !text-sm shadow-lg shadow-brand-600/25">
                        <span>Masuk ke Dashboard</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>

                {{-- Demo Kredensial Helper Box --}}
                <div class="mt-8 rounded-2xl border border-slate-200/90 bg-slate-50/80 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Akun Pengujian Demo:</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-white p-2.5 border border-slate-200/70">
                            <span class="block font-bold text-slate-800">Role Pemilik</span>
                            <span class="font-mono text-slate-500">pemilik / pemilik123</span>
                        </div>
                        <div class="rounded-xl bg-white p-2.5 border border-slate-200/70">
                            <span class="block font-bold text-slate-800">Role Admin</span>
                            <span class="font-mono text-slate-500">admin / admin123</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    (function () {
        const toggle = document.getElementById('toggle-password');
        const input = document.getElementById('password');
        if (toggle && input) {
            toggle.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
            });
        }
    })();
</script>
</body>
</html>
