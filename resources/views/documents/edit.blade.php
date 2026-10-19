@extends('layouts.app')

@section('title', 'Edit Dokumen ' . $document->title)
@section('heading', 'Edit Dokumen Arsip')
@section('subheading', 'Khusus Pemilik')

@php
    $isImage = str_starts_with($document->file_mime_type, 'image/');
    $fileUrl = route('documents.file', $document->id);
    $dateValue = fn ($field) => old($field, $document->{$field}?->format('Y-m-d'));
    $rows = old('items', $document->items->map(fn ($i) => [
        'item_name' => $i->item_name,
        'quantity' => $i->quantity,
        'unit_price' => (float) $i->unit_price,
    ])->all());
@endphp

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Perbarui metadata, nomor nota, tanggal, atau rincian item dokumen ini.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('documents.show', $document->id) }}" class="btn-secondary">
                <span>Batal / Kembali</span>
            </a>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        {{-- Left Column: Pratinjau Dokumen Asli --}}
        <section class="lg:col-span-5 lg:sticky lg:top-24 space-y-4" aria-label="Pratinjau Dokumen Asli">
            <div class="card-base p-4">
                <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-slate-900">Dokumen Asli</h3>
                    </div>
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-xs font-semibold text-brand-600 hover:underline">
                        Layar Penuh &rarr;
                    </a>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-slate-950 flex items-center justify-center min-h-[380px] max-h-[65vh]">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="Dokumen {{ $document->title }}" class="max-h-[65vh] w-full object-contain">
                    @else
                        <iframe src="{{ $fileUrl }}" title="Dokumen PDF" class="h-[65vh] w-full"></iframe>
                    @endif
                </div>
            </div>
        </section>

        {{-- Right Column: Form Edit Dokumen --}}
        <section class="lg:col-span-7">
            <form method="POST" action="{{ route('documents.update', $document->id) }}" class="card-base p-6 sm:p-7 space-y-6" novalidate>
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-xs font-medium text-rose-800" role="alert">
                        <div class="flex items-center gap-2 font-bold text-rose-900 mb-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span>Ada isian yang perlu diperiksa kembali:</span>
                        </div>
                        <p>Mohon lengkapi kolom yang wajib diisi.</p>
                    </div>
                @endif

                <div>
                    <label for="title" class="field-label">Judul Dokumen <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title', $document->title) }}"
                           class="field-input @error('title') border-rose-400 @enderror">
                    @error('title') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="document_number" class="field-label">Nomor Dokumen / Nota</label>
                        <input id="document_number" name="document_number" type="text" value="{{ old('document_number', $document->document_number) }}"
                               class="field-input font-mono @error('document_number') border-rose-400 @enderror">
                        @error('document_number') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="entity_name" class="field-label">Supplier / Instansi</label>
                        <input id="entity_name" name="entity_name" type="text" value="{{ old('entity_name', $document->entity_name) }}"
                               class="field-input @error('entity_name') border-rose-400 @enderror">
                        @error('entity_name') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="document_date" class="field-label">Tanggal Dokumen</label>
                        <input id="document_date" name="document_date" type="date" value="{{ $dateValue('document_date') }}"
                               class="field-input @error('document_date') border-rose-400 @enderror">
                        @error('document_date') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="due_date" class="field-label">Tanggal Jatuh Tempo</label>
                        <input id="due_date" name="due_date" type="date" value="{{ $dateValue('due_date') }}"
                               class="field-input @error('due_date') border-rose-400 @enderror">
                        @error('due_date') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-slate-100">
                    <div>
                        <label for="category_id" class="field-label">Kategori Dokumen <span class="text-rose-500">*</span></label>
                        <select id="category_id" name="category_id" required class="field-input @error('category_id') border-rose-400 @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $document->category_id) === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="importance_level" class="field-label">Tingkat Kepentingan</label>
                        <select id="importance_level" name="importance_level" class="field-input">
                            @foreach (['biasa' => 'Biasa (Standar)', 'penting' => 'Penting (Prioritas)', 'rahasia' => 'Rahasia (Khusus Pemilik)'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('importance_level', $document->importance_level) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="total_amount" class="field-label">Total Nominal (Rp)</label>
                        <input id="total_amount" name="total_amount" type="number" min="0" step="any" 
                               value="{{ old('total_amount', $document->total_amount > 0 ? (float) $document->total_amount : '') }}"
                               class="field-input font-mono font-bold text-slate-900 @error('total_amount') border-rose-400 @enderror">
                        <p class="mt-1 text-[11px] text-slate-400">Kosongkan jika ingin dihitung dari total rincian barang.</p>
                        @error('total_amount') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="payment_status" class="field-label">Status Pembayaran</label>
                        <select id="payment_status" name="payment_status" class="field-input">
                            @foreach (['n/a' => 'Bukan Tagihan (N/A)', 'belum_lunas' => 'Belum Lunas', 'lunas' => 'Lunas'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_status', $document->payment_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Invoice Items Breakdown --}}
                <fieldset class="pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <legend class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Barang / Item Nota</legend>
                        <button type="button" id="add-item" class="btn-secondary !py-1.5 !px-3 !text-xs font-semibold">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span>Tambah Baris</span>
                        </button>
                    </div>

                    <div id="items" class="space-y-2">
                        @foreach ($rows as $index => $row)
                            @include('documents._item-row', ['index' => $index, 'row' => $row])
                        @endforeach
                    </div>
                    @error('items.*.item_name') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    @error('items.*.quantity') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    @error('items.*.unit_price') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </fieldset>

                {{-- Footer Submit Button --}}
                <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                    <a href="{{ route('documents.show', $document->id) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary !px-7 !py-3 shadow-lg shadow-brand-600/25">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <span>Simpan Perubahan Dokumen</span>
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- Template Row for Client-side Add Item --}}
    <template id="item-template">
        @include('documents._item-row', ['index' => '__INDEX__', 'row' => ['item_name' => '', 'quantity' => 1, 'unit_price' => '']])
    </template>
@endsection

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('items');
        const template = document.getElementById('item-template');
        let counter = Date.now();

        document.getElementById('add-item').addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__INDEX__', counter++);
            list.insertAdjacentHTML('beforeend', html);
            list.lastElementChild.querySelector('input').focus();
        });

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-item]');
            if (button) button.closest('[data-item-row]').remove();
        });
    })();
</script>
@endpush