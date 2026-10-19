<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

class ReminderService
{
    /**
     * Ambil dokumen belum lunas yang jatuh tempo dalam $days hari ke depan (atau sudah lewat).
     */
    public function getDueSoon(int $days = 7, int $limit = 5): Collection
    {
        return Document::with(['category', 'creator'])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->jatuhTempo($days)
            ->orderBy('due_date', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Hitung total dokumen tagihan yang sudah lewat jatuh tempo.
     */
    public function countOverdue(): int
    {
        return Document::where('status', '!=', Document::STATUS_DRAFT)
            ->where('payment_status', 'belum_lunas')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();
    }

    /**
     * Hitung total dokumen yang jatuh tempo dalam $days hari ke depan.
     */
    public function countDueSoon(int $days = 7): int
    {
        return Document::where('status', '!=', Document::STATUS_DRAFT)
            ->jatuhTempo($days)
            ->count();
    }
}

