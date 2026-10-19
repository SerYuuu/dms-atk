@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')
@section('heading', 'Riwayat Jejak Audit Sistem')
@section('subheading', 'Khusus Pemilik Toko')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                Catatan komprehensif seluruh interaksi pengguna (login, unggah, proses OCR, edit, hingga penghapusan berkas) untuk menjamin akuntabilitas operasional.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge-emerald">🛡️ Audit Trail Aktif</span>
        </div>
    </div>

    {{-- Filter Toolbar Card --}}
    <div class="card-base mb-6 p-4.5">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-72">
                <label for="action" class="field-label">Filter Jenis Tindakan</label>
                <select id="action" name="action" class="field-input">
                    <option value="">Semua Tindakan</option>
                    <option value="login" @selected(request('action') === 'login')>Login (Masuk)</option>
                    <option value="logout" @selected(request('action') === 'logout')>Logout (Keluar)</option>
                    <option value="upload" @selected(request('action') === 'upload')>Unggah Dokumen</option>
                    <option value="ocr" @selected(request('action') === 'ocr')>Pindai OCR</option>
                    <option value="save" @selected(request('action') === 'save')>Simpan Arsip</option>
                    <option value="edit" @selected(request('action') === 'edit')>Edit Dokumen</option>
                    <option value="delete" @selected(request('action') === 'delete')>Hapus Dokumen</option>
                    <option value="export" @selected(request('action') === 'export')>Ekspor Laporan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary !py-2.5">
                    Filter
                </button>
                @if(request('action'))
                    <a href="{{ route('audit-logs.index') }}" class="btn-secondary !py-2.5">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Audit Log Activity Feed Table --}}
    <div class="card-base overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3.5">Waktu & Tanggal</th>
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Aksi / Tindakan</th>
                        <th class="px-6 py-3.5">Keterangan Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($logs as $log)
                        @php
                            $actionColors = [
                                'login'  => 'badge-slate',
                                'logout' => 'badge-slate',
                                'upload' => 'badge-emerald',
                                'ocr'    => 'badge-emerald',
                                'save'   => 'badge-emerald',
                                'edit'   => 'badge-amber',
                                'delete' => 'badge-rose',
                                'export' => 'badge-pill bg-indigo-50 text-indigo-700 border border-indigo-200/70',
                            ];
                            $badgeClass = $actionColors[$log->action] ?? 'badge-slate';
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-slate-500 font-medium">
                                {{ $log->created_at->translatedFormat('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-900">{{ $log->user->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="{{ $badgeClass }} uppercase !text-[10px]">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                {{ $log->description ?? '-' }}
                                @if($log->document)
                                    <a href="{{ route('documents.show', $log->document->id) }}" class="ml-1 text-brand-600 hover:underline font-bold">
                                        (Buka Dokumen #{{ $log->document->id }})
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                                Belum ada catatan aktivitas sistem yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200/80 bg-slate-50/50 px-6 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection