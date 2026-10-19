<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /** Tampilkan riwayat aktivitas sistem (Khusus Pemilik) */
    public function index(Request $request): View
    {
        $query = AuditLog::with(['user', 'document']);

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        return view('audit_logs.index', [
            'logs' => $logs,
        ]);
    }
}