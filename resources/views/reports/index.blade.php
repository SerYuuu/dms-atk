@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi')
@section('heading', 'Laporan Rekapitulasi Dokumen')
@section('subheading', 'Arsip & Finansial')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Rekapitulasi berkas operasional, nota belanja, dan pengeluaran toko berdasarkan rentang tanggal dan kategori.
            </p>
        </div>
        <form method="GET" action="{{ route('reports.export-pdf') }}" target="_blank">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
            <button type="submit" class="btn-primary flex items-center gap-2 shadow-md shadow-brand-600/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5z"/>
                </svg>
                <span>Cetak / Ekspor PDF</span>
            </button>
        </form>
    </div>

    {{-- Filter Toolbar Card --}}
    <div class="card-base mb-6 p-4.5">
        <form method="GET" action="{{ route('reports.index') }}" class="grid gap-3 sm:grid-cols-12 items-end">
            <div class="sm:col-span-3">
                <label for="start_date" class="field-label">Dari Tanggal</label>
                <input id="start_date" name="start_date" type="date" value="{{ request('start_date') }}" class="field-input">
            </div>

            <div class="sm:col-span-3">
                <label for="end_date" class="field-label">Sampai Tanggal</label>
                <input id="end_date" name="end_date" type="date" value="{{ request('end_date') }}" class="field-input">
            </div>

            <div class="sm:col-span-3">
                <label for="category_id" class="field-label">Kategori</label>
                <select id="category_id" name="category_id" class="field-input">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="btn-primary w-full !py-2.5">
                    Tampilkan Rekap
                </button>
                <a href="{{ route('reports.index') }}" class="btn-secondary px-3 !py-2.5 text-slate-500" title="Reset filter">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Metric Cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah Berkas Terdata</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($totalCount, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-slate-500">Berkas Dokumen</span>
            </div>
            <p class="mt-1 text-xs text-slate-400">Total dokumen pada periode ini</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Akumulasi Nominal</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-1">
                <span class="text-3xl font-extrabold tracking-tight text-brand-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <p class="mt-1 text-xs text-slate-400">Total akumulasi biaya & transaksi</p>
        </div>
    </div>

    {{-- Report Table Card --}}
    <div class="card-base overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">Dokumen</th>
                        <th class="px-6 py-3.5">Supplier / Instansi</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5 text-right">Nominal Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($documents as $doc)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <a href="{{ route('documents.show', $doc->id) }}" class="font-semibold text-slate-900 hover:text-brand-600">
                                    {{ $doc->title }}
                                </a>
                                <span class="block font-mono text-xs text-slate-400 font-medium">{{ $doc->document_number ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $doc->entity_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge-slate font-medium">
                                    {{ $doc->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ $doc->document_date ? $doc->document_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                                {{ $doc->total_amount > 0 ? 'Rp ' . number_format($doc->total_amount, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                                <h4 class="mt-3 font-bold text-slate-900">Tidak ada arsip dokumen pada periode ini</h4>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan tanggal awal dan akhir rekapitulasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection