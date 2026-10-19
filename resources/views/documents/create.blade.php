@extends('layouts.app')

@section('title', 'Unggah Dokumen Baru')
@section('heading', 'Unggah Dokumen & Nota Baru')
@section('subheading', 'Langkah 1: Upload Berkas')

@section('content')
    <div class="mx-auto max-w-3xl">
        {{-- Stepper Progress Indicator --}}
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white shadow-md shadow-brand-500/20">1</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Unggah Berkas</h3>
                    <p class="text-xs text-slate-500 font-medium">Pilih foto nota atau dokumen</p>
                </div>
            </div>
            <div class="h-0.5 w-12 bg-slate-200 sm:w-20"></div>
            <div class="flex items-center gap-3 opacity-60">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600">2</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Pemindaian OCR</h3>
                    <p class="text-xs text-slate-500 font-medium">Ekstraksi teks otomatis</p>
                </div>
            </div>
            <div class="h-0.5 w-12 bg-slate-200 sm:w-20"></div>
            <div class="flex items-center gap-3 opacity-60">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600">3</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Verifikasi & Simpan</h3>
                    <p class="text-xs text-slate-500 font-medium">Koreksi & pilih kategori</p>
                </div>
            </div>
        </div>

        {{-- Upload Container Card --}}
        <div class="card-base p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Pilih Berkas Dokumen Toko</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Sistem mendukung gambar foto nota (JPG, PNG, WEBP) untuk pembacaan OCR otomatis, serta file PDF untuk pengarsipan langsung.
                </p>
            </div>

            <form id="upload-form" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- Drag and Drop Upload Zone --}}
                <div id="drop-zone"
                     class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 p-8 text-center transition-all hover:border-brand-500 hover:bg-brand-50/20">
                    
                    <input id="document" name="document" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf" required
                           class="absolute inset-0 h-full w-full cursor-pointer opacity-0">

                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-md ring-1 ring-slate-200/80 transition-transform group-hover:scale-110">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-bold text-slate-900">
                        <span class="text-brand-600 hover:underline">Klik untuk memilih berkas</span> atau seret file ke sini
                    </h3>
                    <p class="mt-1.5 text-xs text-slate-500">
                        Format yang didukung: <span class="font-semibold text-slate-700">JPG, PNG, WEBP, PDF</span> (Maksimal 10 MB)
                    </p>

                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <span class="badge-emerald !text-[11px]">✨ OCR Otomatis untuk Gambar</span>
                        <span class="badge-slate !text-[11px]">🔒 Penyimpanan Terenkripsi Privat</span>
                    </div>
                </div>

                @error('document')
                    <div class="mt-3 flex items-center gap-2 text-sm text-red-600">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- Interactive File Selected Preview --}}
                <div id="file-info" class="mt-6 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center gap-4">
                        <div id="preview-wrap" class="hidden h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <img id="preview" alt="Pratinjau berkas" class="h-full w-full object-cover">
                        </div>
                        <div id="pdf-icon-wrap" class="hidden flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 border border-red-200">
                            <span class="text-xs font-bold font-mono">PDF</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="file-name" class="truncate text-sm font-bold text-slate-900">nama-dokumen.jpg</p>
                            <p id="file-size" class="mt-0.5 text-xs text-slate-500">0 KB</p>
                            <span id="ocr-badge" class="mt-1.5 inline-block badge-emerald !text-[10px]">Siap Ekstraksi OCR</span>
                        </div>
                        <button type="button" id="clear-file" class="rounded-lg p-2 text-slate-400 hover:bg-slate-200/60 hover:text-slate-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Action Submit Button --}}
                <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a href="{{ route('documents.index') }}" class="btn-secondary">Batal</a>
                    <button id="submit-btn" type="submit" class="btn-primary !px-6">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Lanjut ke Verifikasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('document');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const previewWrap = document.getElementById('preview-wrap');
        const preview = document.getElementById('preview');
        const pdfIcon = document.getElementById('pdf-icon-wrap');
        const clearBtn = document.getElementById('clear-file');
        const ocrBadge = document.getElementById('ocr-badge');
        const form = document.getElementById('upload-form');
        const btn = document.getElementById('submit-btn');

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) {
                fileInfo.classList.add('hidden');
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            fileInfo.classList.remove('hidden');

            if (file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                previewWrap.classList.remove('hidden');
                pdfIcon.classList.add('hidden');
                ocrBadge.textContent = '✨ Siap Ekstraksi OCR Otomatis';
                ocrBadge.className = 'mt-1.5 inline-block badge-emerald !text-[10px]';
            } else {
                previewWrap.classList.add('hidden');
                pdfIcon.classList.remove('hidden');
                ocrBadge.textContent = '📄 Dokumen PDF (Input Manual)';
                ocrBadge.className = 'mt-1.5 inline-block badge-slate !text-[10px]';
            }
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            fileInfo.classList.add('hidden');
        });

        form.addEventListener('submit', () => {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Mengunggah Berkas…</span>
            `;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        });
    })();
</script>
@endpush