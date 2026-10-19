<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // RESTRICT: riwayat aktivitas tidak boleh ikut hilang saat user dihapus.
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 100); // login, logout, upload, verify, save, edit, delete, export
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
