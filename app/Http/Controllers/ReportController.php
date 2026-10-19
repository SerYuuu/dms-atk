<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Tampilkan halaman rekapitulasi laporan dokumen */
   public function index(Request $request): View
    {
        $query = Document::with(['category', 'creator'])
            ->where('status', '!=', Document::STATUS_DRAFT);

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('document_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('document_date', '<=', $endDate);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $documents = $query->latest('document_date')->get();
        $categories = Category::orderBy('name')->get();

        $totalAmount = $documents->sum('total_amount');
        $totalCount = $documents->count();

        return view('reports.index', [
            'documents' => $documents,
            'categories' => $categories,
            'totalAmount' => $totalAmount,
            'totalCount' => $totalCount,
        ]);
    }

    /** Tampilkan halaman cetak / export laporan sesuai PRD */
    public function exportPdf(Request $request)
    {
        $query = Document::with(['category', 'creator'])
            ->where('status', '!=', Document::STATUS_DRAFT);

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('document_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('document_date', '<=', $endDate);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $documents = $query->latest('document_date')->get();
        $totalAmount = $documents->sum('total_amount');

        AuditLogger::log('export', null, "Mengekspor laporan rekapitulasi dokumen (PDF)");

        $pdf = Pdf::loadView('reports.pdf', [
            'documents' => $documents,
            'totalAmount' => $totalAmount,
            'startDate' => $request->input('start_date'),
            'endDate' => $request->input('end_date'),
        ]);

        return $pdf->download('laporan-dokumen-' . now()->format('Y-m-d') . '.pdf');
    }
}