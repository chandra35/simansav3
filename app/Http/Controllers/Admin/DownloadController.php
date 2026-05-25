<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\DownloadSetting;
use App\Services\DownloadStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    public function __construct(private DownloadStorageService $storageService)
    {
    }

    public function index(Request $request)
    {
        $query = Download::query()->with('category')->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category_id', $request->string('category')->toString());
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'published') {
                $query->where('is_published', true);
            }
            if ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source')->toString());
        }

        if ($request->filled('q')) {
            $q = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('file_name_original', 'like', "%{$q}%");
            });
        }

        $downloads = $query->paginate(15)->withQueryString();
        $categories = DownloadCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.downloads.index', compact('downloads', 'categories'));
    }

    public function create()
    {
        $categories = DownloadCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $settings = DownloadSetting::getInstance();

        return view('admin.downloads.create', compact('categories', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:download_categories,id',
            'title' => 'required|string|max:180',
            'description' => 'nullable|string',
            'source' => 'required|in:local,gdrive',
            'file' => 'required|file|max:153600',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $settings = DownloadSetting::getInstance();
        $meta = $this->storageService->storeUploadedFile($request->file('file'), $validated['source'], $settings);

        $slug = $this->makeUniqueSlug($validated['title']);

        Download::create([
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'source' => $meta['source'],
            'file_name_original' => $meta['file_name_original'],
            'file_extension' => $meta['file_extension'],
            'mime_type' => $meta['mime_type'],
            'file_size' => $meta['file_size'],
            'local_disk' => $meta['local_disk'],
            'local_path' => $meta['local_path'],
            'gdrive_file_id' => $meta['gdrive_file_id'],
            'gdrive_file_url' => $meta['gdrive_file_url'],
            'is_published' => $request->boolean('is_published', true),
            'is_featured' => $request->boolean('is_featured', false),
            'published_at' => $validated['published_at'] ?? now(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.downloads.index')->with('success', 'File download berhasil ditambahkan.');
    }

    public function edit(Download $download)
    {
        $categories = DownloadCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $settings = DownloadSetting::getInstance();

        return view('admin.downloads.edit', compact('download', 'categories', 'settings'));
    }

    public function update(Request $request, Download $download)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:download_categories,id',
            'title' => 'required|string|max:180',
            'description' => 'nullable|string',
            'source' => 'required|in:local,gdrive',
            'file' => 'nullable|file|max:153600',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $settings = DownloadSetting::getInstance();

        $payload = [
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_published' => $request->boolean('is_published', false),
            'is_featured' => $request->boolean('is_featured', false),
            'published_at' => $validated['published_at'] ?? $download->published_at,
            'updated_by' => Auth::id(),
        ];

        if ($download->title !== $validated['title']) {
            $payload['slug'] = $this->makeUniqueSlug($validated['title'], $download->id);
        }

        if ($request->hasFile('file')) {
            $oldMeta = [
                'source' => $download->source,
                'local_disk' => $download->local_disk,
                'local_path' => $download->local_path,
                'gdrive_file_id' => $download->gdrive_file_id,
            ];

            $meta = $this->storageService->replaceUploadedFile($oldMeta, $request->file('file'), $validated['source'], $settings);

            $payload = array_merge($payload, [
                'source' => $meta['source'],
                'file_name_original' => $meta['file_name_original'],
                'file_extension' => $meta['file_extension'],
                'mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'local_disk' => $meta['local_disk'],
                'local_path' => $meta['local_path'],
                'gdrive_file_id' => $meta['gdrive_file_id'],
                'gdrive_file_url' => $meta['gdrive_file_url'],
            ]);
        }

        $download->update($payload);

        return redirect()->route('admin.downloads.index')->with('success', 'File download berhasil diperbarui.');
    }

    public function destroy(Download $download)
    {
        $meta = [
            'source' => $download->source,
            'local_disk' => $download->local_disk,
            'local_path' => $download->local_path,
            'gdrive_file_id' => $download->gdrive_file_id,
        ];

        $this->storageService->deleteByMeta($meta, DownloadSetting::getInstance());
        $download->delete();

        return redirect()->route('admin.downloads.index')->with('success', 'File download berhasil dihapus.');
    }

    private function makeUniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (
            Download::query()
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
