<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DownloadCategoryController extends Controller
{
    public function index()
    {
        $categories = DownloadCategory::query()
            ->withCount('downloads')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.download_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        DownloadCategory::create([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: 'fas fa-folder-open',
            'color' => $validated['color'] ?: '#0ea5e9',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, DownloadCategory $downloadCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: 'fas fa-folder-open',
            'color' => $validated['color'] ?: '#0ea5e9',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', false),
        ];

        if ($downloadCategory->name !== $validated['name']) {
            $payload['slug'] = $this->makeUniqueSlug($validated['name'], $downloadCategory->id);
        }

        $downloadCategory->update($payload);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, DownloadCategory $downloadCategory)
    {
        if ($downloadCategory->downloads()->count() > 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori tidak bisa dihapus karena masih digunakan file download.',
                ], 422);
            }

            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan file download.');
        }

        $downloadCategory->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    private function makeUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            DownloadCategory::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
