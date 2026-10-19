@extends('layouts.app')

@section('title', 'Kategori Dokumen')
@section('heading', 'Master Kategori Dokumen')
@section('subheading', 'Master Data Arsip')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Kelola kategori pengelompokan arsip seperti Faktur Supplier, Dokumen Pajak, Surat Kontrak, dan Katalog Harga.
            </p>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        {{-- Form Tambah Kategori Baru --}}
        <div class="lg:col-span-5">
            <div class="card-base p-6">
                <div class="mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-900">Tambah Kategori Baru</h3>
                </div>

                <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="field-label">Nama Kategori <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                               placeholder="Contoh: Bukti Transfer Bank"
                               class="field-input @error('name') border-rose-400 @enderror">
                        @error('name') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="field-label">Keterangan / Deskripsi</label>
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Penjelasan dokumen yang termasuk kategori ini..."
                                  class="field-input">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Simpan Kategori</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Kategori yang Ada --}}
        <div class="lg:col-span-7">
            <div class="card-base overflow-hidden">
                <div class="border-b border-slate-200/80 bg-slate-50/70 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Daftar Kategori Tersedia</h3>
                    <span class="badge-slate font-mono">{{ $categories->count() }} Kategori</span>
                </div>

                <div class="divide-y divide-slate-100 bg-white">
                    @forelse ($categories as $cat)
                        <div class="p-5 transition-colors hover:bg-slate-50/70">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900">{{ $cat->name }}</h4>
                                        <span class="badge-slate !text-[10px] font-mono">{{ $cat->slug }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                                        {{ $cat->description ?: 'Tidak ada keterangan tambahan.' }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="badge-emerald !text-[10px]">
                                            {{ $cat->documents_count }} Dokumen Tersimpan
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($cat->documents_count === 0 && auth()->user()->isPemilik())
                                        <form method="POST" action="{{ route('categories.destroy', $cat->id) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Hapus Kategori">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Belum ada kategori dokumen. Silakan tambahkan melalui form di samping.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

