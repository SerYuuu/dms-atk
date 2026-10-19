<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('documents')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        AuditLogger::log('save', null, "Menambahkan master kategori dokumen: \"{$category->name}\"");

        return redirect()->route('categories.index')->with('status', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        AuditLogger::log('edit', null, "Memperbarui kategori dokumen: \"{$category->name}\"");

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::withCount('documents')->findOrFail($id);

        if ($category->documents_count > 0) {
            return redirect()->route('categories.index')->with('error', "Kategori \"{$category->name}\" tidak dapat dihapus karena masih digunakan oleh {$category->documents_count} dokumen.");
        }

        $name = $category->name;
        $category->delete();

        AuditLogger::log('delete', null, "Menghapus kategori dokumen: \"{$name}\"");

        return redirect()->route('categories.index')->with('status', "Kategori \"{$name}\" berhasil dihapus.");
    }
}

