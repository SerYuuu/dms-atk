<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\ReminderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private ReminderService $reminderService)
    {
    }

    public function index(): View
    {
        // Mengambil dokumen terbaru yang sudah tersimpan / diarsipkan
        $documents = Document::with(['category', 'creator'])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->latest()
            ->take(8)
            ->get();

        // Statistik ringkas untuk dashboard
        $totalDocuments = Document::where('status', '!=', Document::STATUS_DRAFT)->count();
        $totalAmountSum = Document::where('status', '!=', Document::STATUS_DRAFT)->sum('total_amount');
        
        // Dokumen tagihan belum lunas
        $unpaidCount = Document::where('payment_status', 'belum_lunas')->count();

        // Pengingat tagihan mendekati & lewat jatuh tempo (ReminderService)
        $dueSoonDocuments = $this->reminderService->getDueSoon(7, 5);
        $overdueCount = $this->reminderService->countOverdue();

        return view('dashboard.index', [
            'documents' => $documents,
            'totalDocuments' => $totalDocuments,
            'totalAmountSum' => $totalAmountSum,
            'unpaidCount' => $unpaidCount,
            'dueSoonDocuments' => $dueSoonDocuments,
            'overdueCount' => $overdueCount,
        ]);
    }
}