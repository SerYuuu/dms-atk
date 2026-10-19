<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dokumen Index (Daftar, Pencarian, & Filter)
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');

    // Dokumen. Rute statis ('upload') harus didaftarkan sebelum rute {id}.
    Route::get('/documents/upload', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/upload', [DocumentController::class, 'store'])->name('documents.store');

    // Verifikasi hanya untuk dokumen berstatus draft.
    Route::middleware('document.draft')->group(function () {
        Route::get('/documents/{id}/verify', [DocumentController::class, 'verify'])->whereNumber('id')->name('documents.verify');
        Route::put('/documents/{id}/verify', [DocumentController::class, 'updateDraft'])->whereNumber('id')->name('documents.verify.update');
        Route::post('/documents/{id}/ocr', [DocumentController::class, 'ocr'])->whereNumber('id')->name('documents.ocr');
    });

    Route::get('/documents/{id}', [DocumentController::class, 'show'])->whereNumber('id')->name('documents.show');
    Route::get('/documents/{id}/file', [DocumentController::class, 'streamFile'])->whereNumber('id')->name('documents.file');

    // Modul Laporan Rekapitulasi Dokumen
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Master Kategori Dokumen
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->whereNumber('id')->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->whereNumber('id')->name('categories.destroy');

    // Khusus Pemilik: Edit, Update, Hapus Dokumen, & Log Aktivitas
    Route::middleware('role:pemilik')->group(function () {
        Route::get('/documents/{id}/edit', [DocumentController::class, 'edit'])->whereNumber('id')->name('documents.edit');
        Route::put('/documents/{id}/update', [DocumentController::class, 'update'])->whereNumber('id')->name('documents.update');
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->whereNumber('id')->name('documents.destroy');
        
        // Log Aktivitas Sistem
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});