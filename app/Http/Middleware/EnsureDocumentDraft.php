<?php

namespace App\Http\Middleware;

use App\Models\Document;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya dokumen berstatus draft yang boleh diverifikasi.
 * Dipasang di route /documents/{id}/verify (GET & PUT).
 */
class EnsureDocumentDraft
{
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('id') ?? $request->route('document');
        $document = $param instanceof Document ? $param : Document::findOrFail($param);

        if (! $document->isDraft()) {
            return redirect()
                ->route('documents.show', $document->id)
                ->with('error', 'Dokumen ini sudah tersimpan dan tidak bisa diverifikasi ulang.');
        }

        return $next($request);
    }
}
