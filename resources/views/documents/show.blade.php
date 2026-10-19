@extends('layouts.app')

@section('title', $document->title)
@section('heading', 'Detail Berkas Arsip')
@section('subheading', $document->title)

@php
    $isImage = str_starts_with($document->file_mime_type, 'image/');
    $fileUrl = route('documents.file', $document->id);
    $dash = fn ($value) => filled($value) ? $value : '-';
@endphp

@section('content')
    {{-- Header Action Bar --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $document->title }}</h2>
                @if ($document->isDraft())
                    <span class="badge-amber">Draft</span>
                @else
                    <span class="badge-emerald">Tersimpan</span>
                @endif
                @if ($document->importance_level === 'rahasia')
                    <span class="badge-rose">Rahasia</span>
                @elseif ($document->importance_level === 'penting')
                    <span class="badge-amber">Penting</span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500 font-medium">
                Diarsipkan pada {{ $document->created_at->translatedFormat('d F Y, H:i') }} WIB oleh <span class="font-bold text-slate-700">{{ $document->creator->name }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if ($document->isDraft())
                <a href="{{ route('documents.verify', $document->id) }}" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Lanjutkan Verifikasi</span>
                </a>
            @endif

            <a href="{{ $fileUrl }}?download=1" class="btn-secondary">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                <span>Unduh Berkas</span>
            </a>

            @if (auth()->user()->isPemilik())
                <a href="{{ route('documents.edit', $document->id) }}" class="btn-secondary">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                    <span>Edit Data</span>
                </a>

                <form method="POST" action="{{ route('documents.destroy', $document->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas dokumen ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        <span>Hapus</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('documents.index') }}" class="btn-secondary !px-3" title="Kembali ke daftar arsip">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Dual Column Main View --}}
    <div class="grid gap-8 lg:grid-cols-12 items-start">
        
        {{-- Metadata and Items Column --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Primary Details Card --}}
            <div class="card-base p-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100">
                    Informasi & Metadata Dokumen
                </h3>

                <div class="grid gap-y-4 gap-x-6 sm:grid-cols-2 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Nomor Dokumen</span>
                        <span class="mt-0.5 block font-mono font-bold text-slate-900">{{ $dash($document->document_number) }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Supplier / Instansi</span>
                        <span class="mt-0.5 block font-bold text-slate-900">{{ $dash($document->entity_name) }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Kategori Arsip</span>
                        <span class="mt-0.5 inline-block badge-slate font-medium">
                            {{ $dash($document->category?->name) }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Status Pembayaran</span>
                        <div class="mt-0.5">
                            @if ($document->payment_status === 'lunas')
                                <span class="badge-emerald">
                                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                    Lunas
                                </span>
                            @elseif ($document->payment_status === 'belum_lunas')
                                <span class="badge-amber">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Belum Lunas
                                </span>
                            @else
                                <span class="badge-slate">Bukan Tagihan (N/A)</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Tanggal Dokumen</span>
                        <span class="mt-0.5 block font-medium text-slate-800">{{ $dash($document->document_date?->translatedFormat('d F Y')) }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-slate-400">Batas Jatuh Tempo</span>
                        <span class="mt-0.5 block font-medium text-slate-800">
                            @if ($document->due_date)
                                {{ $document->due_date->translatedFormat('d F Y') }}
                                @if ($document->payment_status === 'belum_lunas')
                                    @php
                                        $diff = now()->startOfDay()->diffInDays($document->due_date->startOfDay(), false);
                                    @endphp
                                    @if ($diff < 0)
                                        <span class="ml-1 text-xs font-bold text-rose-600">(Lewat {{ abs($diff) }} hari)</span>
                                    @elseif ($diff == 0)
                                        <span class="ml-1 text-xs font-bold text-amber-600">(Hari ini!)</span>
                                    @else
                                        <span class="ml-1 text-xs text-slate-500">({{ $diff }} hari lagi)</span>
                                    @endif
                                @endif
                            @else
                                -
                            @endif
                        </span>
                    </div>

                    <div class="sm:col-span-2 rounded-xl bg-slate-50 p-4 border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Total Akumulasi Nominal</span>
                            <span class="text-xs text-slate-400">Termasuk pajak & biaya terkait nota</span>
                        </div>
                        <span class="text-2xl font-mono font-extrabold text-slate-900">{{ $document->formattedAmount() }}</span>
                    </div>
                </div>
            </div>

            {{-- Document Items Breakdown (If Present) --}}
            @if ($document->items->isNotEmpty())
                <div class="card-base overflow-hidden">
                    <div class="border-b border-slate-200/80 bg-slate-50/70 px-6 py-3.5 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">Rincian Barang / Komponen Nota</h3>
                        <span class="badge-slate font-mono">{{ $document->items->count() }} Item</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                            <thead class="bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-6 py-3">Nama Barang</th>
                                    <th class="px-6 py-3 text-center">Jumlah</th>
                                    <th class="px-6 py-3 text-right">Harga Satuan</th>
                                    <th class="px-6 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($document->items as $item)
                                    <tr>
                                        <td class="px-6 py-3.5 font-medium text-slate-900">{{ $item->item_name }}</td>
                                        <td class="px-6 py-3.5 text-center font-mono text-slate-600">{{ $item->quantity }}</td>
                                        <td class="px-6 py-3.5 text-right font-mono text-slate-600">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-3.5 text-right font-mono font-bold text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Document Preview Column (Sticky on Large Screens) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="card-base p-4">
                <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-slate-900">Pratinjau Dokumen Asli</h3>
                    </div>
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-xs font-semibold text-brand-600 hover:underline">
                        Layar Penuh &rarr;
                    </a>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-slate-950 flex items-center justify-center min-h-[420px] max-h-[70vh]">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="Dokumen {{ $document->title }}" class="max-h-[70vh] w-full object-contain">
                    @else
                        <iframe src="{{ $fileUrl }}" title="Dokumen {{ $document->title }}" class="h-[70vh] w-full"></iframe>
                    @endif
                </div>

                @if ($document->ocr_text)
                    <details class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50/60 p-3">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700 select-none flex items-center justify-between">
                            <span>Teks Hasil Ekstraksi OCR</span>
                            <span class="text-[10px] text-slate-400 font-normal">Klik untuk buka/tutup</span>
                        </summary>
                        <pre class="mt-2.5 max-h-56 overflow-auto font-mono text-[11px] leading-relaxed text-slate-700 whitespace-pre-wrap rounded-lg bg-white p-3 border border-slate-200/80">{{ $document->ocr_text }}</pre>
                    </details>
                @endif
            </div>
        </div>
    </div>
@endsection