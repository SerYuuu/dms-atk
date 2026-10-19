@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard Ringkasan')

@section('content')
    {{-- Hero Welcome Banner --}}
    <div class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-surface-900 via-slate-900 to-surface-850 p-6 text-white shadow-xl ring-1 ring-white/10 sm:p-8">
        {{-- Ambient decorative background glow --}}
        <div class="pointer-events-none absolute -bottom-12 -right-12 h-64 w-64 rounded-full bg-brand-500/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -top-12 -left-12 h-56 w-56 rounded-full bg-teal-500/10 blur-2xl"></div>

        <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-brand-500/15 px-3 py-1 text-xs font-semibold text-brand-300 ring-1 ring-inset ring-brand-500/30">
                    <span class="h-2 w-2 rounded-full bg-brand-400 animate-pulse"></span>
                    Sistem Arsip & OCR Otomatis Aktif
                </div>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Halo, {{ auth()->user()->name }}! 👋
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300 sm:text-base">
                    Kelola seluruh faktur supplier, surat kontrak, dan nota kasir ATK di satu tempat terpadu. Teks dan rincian transaksi terekstraksi otomatis dengan OCR cerdas.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <a href="{{ route('documents.create') }}" class="btn-primary !px-5 !py-3 !text-sm shadow-lg shadow-brand-600/30">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Pindai Nota Baru</span>
                </a>
                <a href="{{ route('documents.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm font-semibold text-slate-200 backdrop-blur-sm transition-all hover:bg-slate-700 hover:text-white">
                    <span>Lihat Semua Arsip</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Modern Stat Cards Grid --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Total Dokumen --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Arsip</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($totalDocuments, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-slate-500">Berkas Dokumen</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Tersimpan aman di penyimpanan privat</p>
        </div>

        {{-- Card 2: Akumulasi Transaksi --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-1">
                <span class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Rp {{ number_format($totalAmountSum, 0, ',', '.') }}</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Nilai seluruh faktur yang telah diverifikasi</p>
        </div>

        {{-- Card 3: Tagihan Belum Lunas --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Belum Lunas</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-amber-600">{{ $unpaidCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Nota Terutang</span>
            </div>
            <p class="mt-2 text-xs text-amber-700/80 font-medium">
                @if ($unpaidCount > 0)
                    ⚠️ Perlu penyelesaian pembayaran
                @else
                    ✅ Seluruh tagihan beres
                @endif
            </p>
        </div>

        {{-- Card 4: Jatuh Tempo Minggu Ini --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jatuh Tempo (7 Hari)</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition-colors group-hover:bg-rose-600 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-rose-600">{{ count($dueSoonDocuments ?? []) }}</span>
                <span class="text-xs font-semibold text-slate-500">Tagihan Kritis</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">
                @if (($overdueCount ?? 0) > 0)
                    <span class="font-bold text-rose-600">{{ $overdueCount }} lewat jatuh tempo!</span>
                @else
                    Tidak ada yang lewat tempo
                @endif
            </p>
        </div>
    </div>

    {{-- Widget Pengingat Jatuh Tempo (Jika Ada) --}}
    @if (!empty($dueSoonDocuments) && $dueSoonDocuments->isNotEmpty())
        <div class="mb-8 overflow-hidden rounded-2xl border border-amber-200/90 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/40 p-5 shadow-card">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-amber-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-amber-950">Perhatian: Tagihan Mendekati atau Melewati Jatuh Tempo</h3>
                        <p class="text-xs text-amber-700">Segera lakukan pembayaran untuk menjaga kelancaran stok barang dengan supplier.</p>
                    </div>
                </div>
                <a href="{{ route('documents.index', ['payment_status' => 'belum_lunas']) }}" class="text-xs font-bold text-amber-800 hover:text-amber-900 hover:underline">
                    Lihat Semua Tagihan &rarr;
                </a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($dueSoonDocuments as $dueDoc)
                    @php
                        $dueDate = \Carbon\Carbon::parse($dueDoc->due_date);
                        $isOverdue = $dueDate->isPast() && !$dueDate->isToday();
                        $diff = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
                    @endphp
                    <div class="flex items-center justify-between rounded-xl border border-amber-200/70 bg-white p-3.5 shadow-xs transition-all hover:border-amber-300 hover:shadow-sm">
                        <div class="min-w-0 pr-3">
                            <a href="{{ route('documents.show', $dueDoc->id) }}" class="truncate block text-xs font-bold text-slate-900 hover:text-brand-600">
                                {{ $dueDoc->title }}
                            </a>
                            <p class="mt-0.5 truncate text-[11px] text-slate-500 font-medium">
                                {{ $dueDoc->entity_name ?? 'Supplier tanpa nama' }} · <span class="font-bold text-slate-800">{{ $dueDoc->formattedAmount() }}</span>
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            @if ($isOverdue)
                                <span class="badge-rose !text-[10px]">Lewat {{ abs($diff) }} hari</span>
                            @elseif ($dueDate->isToday())
                                <span class="badge-amber !text-[10px]">Hari ini!</span>
                            @else
                                <span class="badge-amber !text-[10px]">{{ $diff }} hari lagi</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Arsip Dokumen Terbaru --}}
    <div class="card-base overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-slate-50/60 px-6 py-4.5">
            <div>
                <h3 class="text-base font-bold text-slate-900">Arsip Dokumen Terbaru</h3>
                <p class="text-xs text-slate-500 font-medium">Dokumen dan nota operasional yang baru saja diverifikasi dan disimpan.</p>
            </div>
            <a href="{{ route('documents.index') }}" class="btn-secondary !py-2 !text-xs">
                <span>Buka Seluruh Dokumen</span>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        @if ($documents->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <h4 class="mt-4 text-base font-bold text-slate-900">Belum ada dokumen tersimpan</h4>
                <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                    Mulai arsipkan nota pembelian ATK, faktur supplier, atau surat kontrak pertama Anda sekarang.
                </p>
                <a href="{{ route('documents.create') }}" class="btn-primary mt-5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Unggah Nota Pertama</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Dokumen</th>
                            <th class="px-6 py-3.5">Supplier / Instansi</th>
                            <th class="px-6 py-3.5">Kategori</th>
                            <th class="px-6 py-3.5">Tanggal</th>
                            <th class="px-6 py-3.5">Nominal Transaksi</th>
                            <th class="px-6 py-3.5">Status Pembayaran</th>
                            <th class="px-6 py-3.5 text-right">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($documents as $doc)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-200/70">
                                            @if ($doc->file_mime_type === 'application/pdf')
                                                <span class="text-[10px] font-bold text-red-600">PDF</span>
                                            @else
                                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('documents.show', $doc->id) }}" class="truncate block font-semibold text-slate-900 transition-colors hover:text-brand-600">
                                                {{ $doc->title }}
                                            </a>
                                            <span class="font-mono text-xs text-slate-400 font-medium">{{ $doc->document_number ?? 'Tanpa Nomor Dokumen' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 font-medium">
                                    {{ $doc->entity_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge-slate font-medium">
                                        {{ $doc->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium">
                                    {{ $doc->document_date ? \Carbon\Carbon::parse($doc->document_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    {{ $doc->total_amount > 0 ? 'Rp ' . number_format($doc->total_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($doc->payment_status === 'lunas')
                                        <span class="badge-emerald">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                            Lunas
                                        </span>
                                    @elseif ($doc->payment_status === 'belum_lunas')
                                        <span class="badge-amber">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Belum Lunas
                                        </span>
                                    @else
                                        <span class="badge-slate">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('documents.show', $doc->id) }}" class="btn-secondary !px-3 !py-1.5 !text-xs">
                                        <span>Detail</span>
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection