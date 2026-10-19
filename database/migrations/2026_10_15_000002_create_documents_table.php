<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');                              // Judul/nama dokumen
            $table->string('document_number', 100)->nullable();   // No. invoice / kontrak / surat
            $table->string('entity_name')->nullable();            // Supplier / instansi / klien
            $table->date('document_date')->nullable();
            $table->date('due_date')->nullable();                 // Jatuh tempo (tagihan/kontrak)
            $table->decimal('total_amount', 15, 2)->nullable()->default(0);

            // Nullable: saat upload (status draft) kategori belum dipilih,
            // pengguna memilihnya manual di halaman verifikasi.
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();

            $table->enum('importance_level', ['penting', 'biasa', 'rahasia'])->default('biasa');
            $table->enum('status', ['draft', 'tersimpan', 'archived'])->default('draft');
            $table->enum('payment_status', ['n/a', 'belum_lunas', 'lunas'])->default('n/a');

            $table->string('file_path');
            $table->string('file_mime_type', 100);
            $table->unsignedInteger('file_size');                 // bytes
            $table->longText('ocr_text')->nullable();

            // RESTRICT: user yang punya dokumen tidak bisa dihapus begitu saja.
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('document_number');
            $table->index(['document_date', 'due_date'], 'idx_dates');
            $table->index('status');
            $table->fullText(['title', 'entity_name', 'ocr_text'], 'ft_documents_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
