@extends('layouts.app')

@section('title', 'Daftar Dokumen')
@section('heading', 'Arsip Dokumen Operasional')
@section('subheading', 'Daftar Dokumen')

@section('content')
    {{-- Header Banner & Action --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Pencarian, pemfilteran, dan pemantauan seluruh arsip nota faktur, kwitansi, dan kontrak toko.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('documents.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Unggah Dokumen Baru</span>
            </a>
        </div>
    </div>

    {{-- Filter & Search Toolbar Card --}}
    <div class="card-base mb-6 p-4.5">
        <form method="GET" action="{{ route('documents.index') }}" class="grid gap-3 sm:grid-cols-12 items-end">
            <div class="sm:col-span-5">
                <label for="search" class="field-label">Cari Arsip</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input id="search" name="search" type="text" value="{{ request('search') }}" 
                           placeholder="Ketik judul, nomor nota, atau nama supplier..." 
                           class="field-input !pl-10">
                </div>
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

            <div class="sm:col-span-2">
                <label for="payment_status" class="field-label">Status Bayar</label>
                <select id="payment_status" name="payment_status" class="field-input">
                    <option value="">Semua Status</option>
                    <option value="lunas" @selected(request('payment_status') == 'lunas')>Lunas</option>
                    <option value="belum_lunas" @selected(request('payment_status') == 'belum_lunas')>Belum Lunas</option>
                    <option value="n/a" @selected(request('payment_status') == 'n/a')>N/A (Bukan Tagihan)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="btn-primary w-full !py-2.5">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'payment_status']))
                    <a href="{{ route('documents.index') }}" class="btn-secondary px-3 !py-2.5 text-slate-500" title="Reset filter">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>

        {{-- Active Filter Tags --}}
        @if(request()->hasAny(['search', 'category_id', 'payment_status']))
            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                <span class="font-semibold">Filter aktif:</span>
                @if(request('search'))
                    <span class="badge-slate">Pencarian: "{{ request('search') }}"</span>
                @endif
                @if(request('category_id'))
                    @php $activeCat = $categories->firstWhere('id', request('category_id')); @endphp
                    @if($activeCat)
                        <span class="badge-slate">Kategori: {{ $activeCat->name }}</span>
                    @endif
                @endif
                @if(request('payment_status'))
                    <span class="badge-slate">Status: {{ ucfirst(str_replace('_', ' ', request('payment_status'))) }}</span>
                @endif
                <a href="{{ route('documents.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Hapus Semua Filter</a>
            </div>
        @endif
    </div>

    {{-- Main Document Table Card --}}
    <div class="card-base overflow-hidden">
        @if ($documents->isEmpty())
            <div class="px-6 py-20 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-slate-900">Tidak ada dokumen yang sesuai</h3>
                <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                    Coba ubah kata kunci pencarian, ganti filter kategori, atau unggah dokumen baru.
                </p>
                <div class="mt-5 flex justify-center gap-3">
                    @if(request()->hasAny(['search', 'category_id', 'payment_status']))
                        <a href="{{ route('documents.index') }}" class="btn-secondary">Reset Pencarian</a>
                    @endif
                    <a href="{{ route('documents.create') }}" class="btn-primary">Unggah Nota Baru</a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Judul Dokumen & Nomor</th>
                            <th class="px-6 py-3.5">Supplier / Instansi</th>
                            <th class="px-6 py-3.5">Kategori</th>
                            <th class="px-6 py-3.5">Tanggal Nota</th>
                            <th class="px-6 py-3.5">Total Nominal</th>
                            <th class="px-6 py-3.5">Status Pembayaran</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
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
                                            <div class="font-mono text-xs text-slate-400 font-medium">
                                                {{ $doc->document_number ?? 'Tanpa Nomor' }}
                                                @if($doc->importance_level === 'rahasia')
                                                    <span class="ml-1 text-[10px] font-bold text-rose-600 uppercase bg-rose-50 px-1.5 py-0.5 rounded">Rahasia</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 font-medium">
                                    {{ $doc->entity_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge-slate font-medium">
                                        {{ $doc->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium">
                                    {{ $doc->document_date ? \Carbon\Carbon::parse($doc->document_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    {{ $doc->total_amount > 0 ? 'Rp ' . number_format((float)$doc->total_amount, 0, ',', '.') : '-' }}
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
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('documents.show', $doc->id) }}" class="btn-secondary !px-2.5 !py-1.5 !text-xs font-semibold" title="Buka Detail">
                                            <span>Detail</span>
                                        </a>
                                        @if(auth()->user()->isPemilik())
                                            <a href="{{ route('documents.edit', $doc->id) }}" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800" title="Edit Dokumen">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Custom Pagination Bar --}}
            @if ($documents->hasPages())
                <div class="border-t border-slate-200/80 bg-slate-50/50 px-6 py-4">
                    {{ $documents->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection