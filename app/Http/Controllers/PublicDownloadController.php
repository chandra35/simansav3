<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\DownloadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicDownloadController extends Controller
{
    public function index(Request $request)
    {
        $query = Download::query()
            ->with('category')
            ->published();

        if ($request->filled('category')) {
            $query->whereHas('category', function ($builder) use ($request) {
                $builder->where('slug', $request->string('category')->toString());
            });
        }

        if ($request->filled('q')) {
            $q = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('file_extension', 'like', "%{$q}%");
            });
        }

        $sort = $request->string('sort', 'latest')->toString();
        if ($sort === 'popular') {
            $query->orderByDesc('download_count')->orderByDesc('published_at');
        } else {
            $query->orderByDesc('published_at')->orderByDesc('created_at');
        }

        $downloads = $query->paginate(18)->withQueryString();
        $categories = DownloadCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_files' => Download::published()->count(),
            'total_downloads' => (int) Download::published()->sum('download_count'),
            'total_categories' => $categories->count(),
        ];

        return view('downloads.index', compact('downloads', 'categories', 'stats', 'sort'));
    }

    public function download(Download $download)
    {
        if (!$download->is_published) {
            abort(404);
        }

        $download->increment('download_count');

        if ($download->source === 'gdrive' && !empty($download->gdrive_file_url)) {
            return redirect()->away($download->gdrive_file_url);
        }

        if (!$download->local_path || !Storage::disk($download->local_disk)->exists($download->local_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $absolutePath = Storage::disk($download->local_disk)->path($download->local_path);

        return response()->download($absolutePath, $download->file_name_original);
    }
}
