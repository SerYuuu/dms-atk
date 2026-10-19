<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Category;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\OcrEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DocumentController extends Controller
{
    private const DISK = 'private';

    public function __construct(private OcrEngineService $ocr)
    {
    }

    public function index(Request $request): View
    {
        $query = Document::with(['category', 'creator'])
            ->where('status', '!=', Document::STATUS_DRAFT);

        // Filter pencarian berdasarkan judul, nomor dokumen, atau supplier
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('entity_name', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kategori
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter berdasarkan status pembayaran
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $documents = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('documents.index', [
            'documents' => $documents,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('documents.create');
    }

    /** Simpan file sebagai draft. OCR dijalankan terpisah dari halaman verifikasi (lihat ocr()). */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        $path = $file->store('documents', self::DISK);

        $document = Document::create([
            'title' => Str::limit(pathinfo($originalName, PATHINFO_FILENAME), 255, ''),
            'status' => Document::STATUS_DRAFT,
            'file_path' => $path,
            'file_mime_type' => $mime,
            'file_size' => $file->getSize(),
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::log('upload', $document->id, "Mengunggah file \"{$originalName}\"");

        $redirect = redirect()->route('documents.verify', $document->id);

        if (! $this->ocr->canRead($mime)) {
            $redirect->with('warning', 'File PDF tidak dibaca otomatis. Isi data dokumen secara manual.');
        }

        return $redirect;
    }

    /**
     * Jalankan OCR lokal dan kembalikan hasilnya sebagai JSON untuk mengisi form verifikasi.
     */
    public function ocr(int $id): JsonResponse
    {
        @set_time_limit(180);

        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        if (! $this->ocr->canRead($document->file_mime_type)) {
            return response()->json([
                'ok' => false,
                'message' => 'File PDF tidak dibaca otomatis. Isi data dokumen secara manual.',
            ], 422);
        }

        try {
            $result = $this->ocr->analyze(Storage::disk(self::DISK)->path($document->file_path));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'OCR gagal dijalankan. Isi data dokumen secara manual.',
            ], 500);
        }

        $document->update(array_merge(
            ['ocr_text' => $result['text']],
            array_filter($result['fields'], fn ($value) => $value !== null)
        ));

        AuditLogger::log('ocr', $document->id, 'Menjalankan OCR pada dokumen');

        return response()->json([
            'ok' => true,
            'text' => $result['text'],
            'fields' => $result['fields'],
            'sources' => $result['sources'],
            'warnings' => $result['warnings'],
        ]);
    }

    public function verify(int $id): View
    {
        $document = Document::with('items')->findOrFail($id);
        $this->authorizeAccess($document);

        return view('documents.verify', [
            'document' => $document,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updateDraft(UpdateDocumentRequest $request, int $id): RedirectResponse
    {
        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        $data = $request->validated();

        $items = collect($data['items'] ?? [])
            ->filter(fn ($item) => filled($item['item_name'] ?? null))
            ->map(fn ($item) => [
                'item_name' => $item['item_name'],
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
            ])
            ->values();

        unset($data['items']);

        $total = $data['total_amount'] ?? null;
        if (($total === null || (float) $total === 0.0) && $items->isNotEmpty()) {
            $total = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        }

        DB::transaction(function () use ($document, $data, $items, $total) {
            $document->update(array_merge($data, [
                'total_amount' => $total ?? 0,
                'status' => Document::STATUS_TERSIMPAN,
            ]));

            $document->items()->delete();

            if ($items->isNotEmpty()) {
                $document->items()->createMany($items->all());
            }
        });

        AuditLogger::log('save', $document->id, "Menyimpan dokumen \"{$document->title}\" setelah verifikasi");

        return redirect()
            ->route('documents.show', $document->id)
            ->with('status', 'Dokumen berhasil disimpan ke arsip.');
    }

    public function show(int $id): View
    {
        $document = Document::with(['category', 'creator', 'items'])->findOrFail($id);
        $this->authorizeAccess($document);

        return view('documents.show', ['document' => $document]);
    }

    public function streamFile(Request $request, int $id): Response
    {
        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        $disk = Storage::disk(self::DISK);
        abort_unless($disk->exists($document->file_path), 404, 'File tidak ditemukan.');

        if ($request->boolean('download')) {
            $name = Str::slug($document->title) ?: 'dokumen';
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);

            return $disk->download($document->file_path, $name . '.' . $extension);
        }

        return $disk->response($document->file_path);
    }

    /** Form edit dokumen khusus Pemilik sesuai PRD */
    public function edit(int $id): View
    {
        $this->authorizePemilik();

        $document = Document::with('items')->findOrFail($id);
        $this->authorizeAccess($document);

        return view('documents.edit', [
            'document' => $document,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** Update data dokumen khusus Pemilik sesuai PRD */
    public function update(UpdateDocumentRequest $request, int $id): RedirectResponse
    {
        $this->authorizePemilik();

        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        $data = $request->validated();

        $items = collect($data['items'] ?? [])
            ->filter(fn ($item) => filled($item['item_name'] ?? null))
            ->map(fn ($item) => [
                'item_name' => $item['item_name'],
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
            ])
            ->values();

        unset($data['items']);

        $total = $data['total_amount'] ?? null;
        if (($total === null || (float) $total === 0.0) && $items->isNotEmpty()) {
            $total = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        }

        DB::transaction(function () use ($document, $data, $items, $total) {
            $document->update(array_merge($data, [
                'total_amount' => $total ?? 0,
            ]));

            $document->items()->delete();

            if ($items->isNotEmpty()) {
                $document->items()->createMany($items->all());
            }
        });

        AuditLogger::log('edit', $document->id, "Memperbarui data dokumen \"{$document->title}\"");

        return redirect()
            ->route('documents.show', $document->id)
            ->with('status', 'Data dokumen berhasil diperbarui.');
    }

    /** Hapus dokumen beserta file fisiknya khusus Pemilik sesuai PRD */
    public function destroy(int $id): RedirectResponse
    {
        $this->authorizePemilik();

        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        $title = $document->title;

        if (Storage::disk(self::DISK)->exists($document->file_path)) {
            Storage::disk(self::DISK)->delete($document->file_path);
        }

        $document->delete();

        AuditLogger::log('delete', null, "Menghapus dokumen \"{$title}\" beserta berkas fisiknya");

        return redirect()
            ->route('documents.index')
            ->with('status', "Dokumen \"{$title}\" berhasil dihapus.");
    }

    /** Dokumen "rahasia" hanya bisa dibuka Pemilik dan pengunggahnya. */
    private function authorizeAccess(Document $document): void
    {
        $user = auth()->user();

        abort_if(
            $document->importance_level === 'rahasia' && ! $user->isPemilik() && $document->created_by !== $user->id,
            403,
            'Dokumen ini bersifat rahasia.'
        );
    }

    /** Memastikan hanya pengguna dengan role Pemilik yang dapat memanipulasi dokumen. */
    private function authorizePemilik(): void
    {
        abort_if(! auth()->user()->isPemilik(), 403, 'Akses ditolak. Fitur ini khusus untuk Pemilik.');
    }
}