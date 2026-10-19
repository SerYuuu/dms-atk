@extends('layouts.app')

@section('title', 'Verifikasi & Koreksi OCR')
@section('heading', 'Verifikasi Dokumen')
@section('subheading', 'Langkah 2: Pemeriksaan Hasil OCR')

@php
    $isImage = str_starts_with($document->file_mime_type, 'image/');
    $fileUrl = route('documents.file', $document->id);
    $autoOcr = $isImage && ! $document->ocr_text && ! $errors->any();
    $dateValue = fn ($field) => old($field, $document->{$field}?->format('Y-m-d'));
    $rows = old('items', $document->items->map(fn ($i) => [
        'item_name' => $i->item_name,
        'quantity' => $i->quantity,
        'unit_price' => (float) $i->unit_price,
    ])->all());
@endphp

@section('content')
    {{-- Header Banner Info --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Bandingkan hasil pembacaan otomatis dengan berkas asli. Periksa metadata, sesuaikan rincian barang, pilih kategori arsip, lalu simpan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge-amber">Status: DRAFT</span>
        </div>
    </div>

    {{-- Interactive OCR Status Panel (Khusus Gambar) --}}
    @if ($isImage)
        <div class="card-base mb-6 overflow-hidden border-brand-200/80 bg-gradient-to-r from-brand-50/50 via-white to-brand-50/20 p-4.5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div id="ocr-icon-container" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-sm shadow-brand-500/30">
                        <svg id="ocr-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Mesin OCR (Optical Character Recognition)</h3>
                            <span id="ocr-badge-status" class="badge-slate !text-[10px]">Siap</span>
                        </div>
                        <p id="ocr-status" role="status" aria-live="polite" class="mt-0.5 text-xs text-slate-600">
                            @if ($document->ocr_text)
                                Teks berhasil diekstrak dan tersimpan. Klik "Pindai Ulang OCR" jika ingin menganalisis kembali.
                            @else
                                Menyiapkan analisis berkas gambar nota...
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="ocr-run" class="btn-secondary !py-2 !text-xs font-semibold">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <span>Pindai Ulang OCR</span>
                    </button>
                </div>
            </div>

            <ul id="ocr-warnings" class="mt-3 hidden list-disc space-y-1 rounded-xl border border-amber-200 bg-amber-50/90 py-2.5 pl-8 pr-4 text-xs text-amber-900"></ul>
        </div>
    @endif

    {{-- Side-by-Side Dual Column Grid --}}
    <div class="grid gap-8 lg:grid-cols-12 items-start">

        {{-- Left Column: Dokumen Viewer (Sticky on Large Screens) --}}
        <section class="lg:col-span-5 lg:sticky lg:top-24 space-y-4" aria-label="Pratinjau Dokumen Asli">
            <div class="card-base overflow-hidden p-4">
                <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                        </span>
                        <h2 class="text-sm font-bold text-slate-900">Dokumen Asli</h2>
                    </div>
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                        <span>Buka Layar Penuh</span>
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-slate-950 flex items-center justify-center min-h-[380px] max-h-[65vh]">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="Dokumen yang diunggah" class="max-h-[65vh] w-full object-contain">
                    @else
                        <iframe src="{{ $fileUrl }}" title="Dokumen PDF" class="h-[65vh] w-full"></iframe>
                    @endif
                </div>

                {{-- Collapsible Raw OCR Output Text --}}
                <details id="ocr-details" class="mt-4 {{ $document->ocr_text ? '' : 'hidden' }} rounded-xl border border-slate-200/80 bg-slate-50/60 p-3">
                    <summary class="cursor-pointer text-xs font-bold text-slate-700 select-none flex items-center justify-between">
                        <span>Lihat Hasil Teks OCR Mentah</span>
                        <span class="text-[10px] text-slate-400 font-normal">Klik untuk buka/tutup</span>
                    </summary>
                    <div class="mt-2.5 relative">
                        <pre id="ocr-text" class="max-h-52 overflow-auto font-mono text-[11px] leading-relaxed text-slate-700 whitespace-pre-wrap rounded-lg bg-white p-3 border border-slate-200/80">{{ $document->ocr_text }}</pre>
                    </div>
                </details>
            </div>
        </section>

        {{-- Right Column: Verification & Data Correction Form --}}
        <section class="lg:col-span-7" aria-label="Form Koreksi Data Dokumen">
            <form id="verify-form" method="POST" action="{{ route('documents.verify.update', $document->id) }}" class="card-base p-6 sm:p-7 space-y-6" novalidate>
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-xs font-medium text-rose-800" role="alert">
                        <div class="flex items-center gap-2 font-bold text-rose-900 mb-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span>Periksa isian form di bawah ini:</span>
                        </div>
                        <p>Ada beberapa kolom yang wajib diisi atau formatnya belum sesuai.</p>
                    </div>
                @endif

                {{-- Basic Document Header Info --}}
                <div>
                    <label for="title" class="field-label">Judul Dokumen / Nama Arsip <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title', $document->title) }}"
                           placeholder="Contoh: Faktur Pembelian Kertas & Alat Tulis"
                           class="field-input @error('title') border-rose-400 @enderror">
                    @error('title') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="document_number" class="field-label !mb-0">Nomor Dokumen / Nota</label>
                            <span id="badge-document_number" class="hidden text-[10px] font-bold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">OCR Otomatis</span>
                        </div>
                        <input id="document_number" name="document_number" type="text" value="{{ old('document_number', $document->document_number) }}"
                               placeholder="Contoh: INV-2026/10/089"
                               class="field-input font-mono @error('document_number') border-rose-400 @enderror">
                        @error('document_number') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="entity_name" class="field-label !mb-0">Nama Supplier / Klien</label>
                            <span id="badge-entity_name" class="hidden text-[10px] font-bold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">OCR Otomatis</span>
                        </div>
                        <input id="entity_name" name="entity_name" type="text" value="{{ old('entity_name', $document->entity_name) }}"
                               placeholder="Contoh: PT. Sinar Dunia Perkasa"
                               class="field-input @error('entity_name') border-rose-400 @enderror">
                        @error('entity_name') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="document_date" class="field-label !mb-0">Tanggal Dokumen</label>
                            <span id="badge-document_date" class="hidden text-[10px] font-bold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">OCR Otomatis</span>
                        </div>
                        <input id="document_date" name="document_date" type="date" value="{{ $dateValue('document_date') }}"
                               class="field-input @error('document_date') border-rose-400 @enderror">
                        @error('document_date') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="due_date" class="field-label">Tanggal Jatuh Tempo</label>
                        <input id="due_date" name="due_date" type="date" value="{{ $dateValue('due_date') }}"
                               class="field-input @error('due_date') border-rose-400 @enderror">
                        <p class="mt-1 text-[11px] text-slate-400">Diisi jika dokumen memiliki batas akhir pembayaran</p>
                        @error('due_date') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Categorization & Financial Classification --}}
                <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-slate-100">
                    <div>
                        <label for="category_id" class="field-label">Kategori Dokumen <span class="text-rose-500">*</span></label>
                        <select id="category_id" name="category_id" required class="field-input @error('category_id') border-rose-400 @enderror">
                            <option value="">-- Pilih Kategori Arsip --</option>
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
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="total_amount" class="field-label !mb-0">Total Nominal Transaksi (Rp)</label>
                            <span id="badge-total_amount" class="hidden text-[10px] font-bold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">OCR Otomatis</span>
                        </div>
                        <input id="total_amount" name="total_amount" type="number" min="0" step="any" 
                               value="{{ old('total_amount', $document->total_amount > 0 ? (float) $document->total_amount : '') }}"
                               placeholder="0"
                               class="field-input font-mono font-bold text-slate-900 @error('total_amount') border-rose-400 @enderror">
                        <p class="mt-1 text-[11px] text-slate-400">Kosongkan jika ingin dihitung dari total rincian barang.</p>
                        @error('total_amount') <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="payment_status" class="field-label !mb-0">Status Pembayaran</label>
                            <span id="badge-payment_status" class="hidden text-[10px] font-bold text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">Cap Stempel Lunas</span>
                        </div>
                        <select id="payment_status" name="payment_status" class="field-input">
                            @foreach (['n/a' => 'Bukan Tagihan (N/A)', 'belum_lunas' => 'Belum Lunas (Terutang)', 'lunas' => 'Lunas'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_status', $document->payment_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Invoice Items Breakdown Section --}}
                <fieldset class="pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <legend class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Barang / Item Nota</legend>
                            <p class="text-[11px] text-slate-500">Opsional: Catat barang individual untuk faktur pembelian ATK</p>
                        </div>
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

                {{-- Action Submit Footer --}}
                <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                    <a href="{{ route('documents.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary !px-7 !py-3 shadow-lg shadow-brand-600/25">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <span>Simpan Permanen ke Arsip</span>
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

@if ($isImage)
@push('scripts')
<script>
    (function () {
        const form = document.getElementById('verify-form');
        const runButton = document.getElementById('ocr-run');
        const statusEl = document.getElementById('ocr-status');
        const badgeStatus = document.getElementById('ocr-badge-status');
        const warningsEl = document.getElementById('ocr-warnings');
        const details = document.getElementById('ocr-details');
        const textEl = document.getElementById('ocr-text');
        const token = form.querySelector('input[name="_token"]').value;
        const url = @json(route('documents.ocr', $document->id));

        const labels = {
            entity_name: 'Supplier / Instansi',
            document_number: 'Nomor Dokumen',
            document_date: 'Tanggal Dokumen',
            total_amount: 'Total Nominal',
            payment_status: 'Status Pembayaran',
        };

        const dirty = new Set();
        Object.keys(labels).forEach((name) => {
            const el = form.elements[name];
            if (!el) return;
            const markDirty = () => {
                dirty.add(name);
                el.classList.remove('ring-2', 'ring-brand-400');
            };
            el.addEventListener('input', markDirty);
            el.addEventListener('change', markDirty);
        });

        function showWarnings(list) {
            warningsEl.replaceChildren();
            list.forEach((message) => {
                const li = document.createElement('li');
                li.textContent = message;
                warningsEl.appendChild(li);
            });
            warningsEl.classList.toggle('hidden', list.length === 0);
        }

        function applyFields(fields) {
            const filled = [];
            Object.keys(labels).forEach((name) => {
                const value = fields[name];
                const el = form.elements[name];
                const badge = document.getElementById('badge-' + name);
                if (!el || value === null || value === undefined || dirty.has(name)) return;
                el.value = value;
                el.classList.add('ring-2', 'ring-brand-400');
                if (badge) badge.classList.remove('hidden');
                filled.push(labels[name]);
            });
            return filled;
        }

        async function runOcr() {
            runButton.disabled = true;
            runButton.classList.add('opacity-60', 'cursor-not-allowed');
            badgeStatus.textContent = 'Memindai...';
            badgeStatus.className = 'badge-amber animate-pulse !text-[10px]';
            statusEl.textContent = 'Memproses dokumen dengan Tesseract OCR... Mohon tunggu 5 - 15 detik.';
            showWarnings([]);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                });

                let data = null;
                try { data = await response.json(); } catch (_) {}

                if (!response.ok || !data || !data.ok) {
                    throw new Error((data && data.message) || 'Proses OCR gagal dijalankan.');
                }

                const filled = applyFields(data.fields || {});
                textEl.textContent = data.text || '';
                details.classList.toggle('hidden', !data.text);
                showWarnings(data.warnings || []);

                badgeStatus.textContent = 'Selesai';
                badgeStatus.className = 'badge-emerald !text-[10px]';

                if (filled.length) {
                    statusEl.textContent = 'Berhasil diekstrak otomatis: ' + filled.join(', ') + '.';
                } else {
                    statusEl.textContent = 'OCR selesai. Tidak ada teks spesifik yang terbaca, silakan isi data manual.';
                }
            } catch (error) {
                badgeStatus.textContent = 'Gagal';
                badgeStatus.className = 'badge-rose !text-[10px]';
                statusEl.textContent = error.message;
            } finally {
                runButton.disabled = false;
                runButton.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        }

        runButton.addEventListener('click', runOcr);
        @if ($autoOcr)
        runOcr();
        @endif
    })();
</script>
@endpush
@endif