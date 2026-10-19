<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Catat aktivitas pengguna.
     * Aksi yang dipakai: login, logout, upload, verify, save, edit, delete, export.
     */
    public static function log(
        string $action,
        ?int $documentId = null,
        ?string $description = null,
        ?int $userId = null
    ): ?AuditLog {
        $userId ??= Auth::id();

        if (! $userId) {
            return null;
        }

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'document_id' => $documentId,
            'description' => $description,
        ]);
    }
}
