<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\DownloadCategory;
use App\Services\DownloadStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicDownloadController extends Controller
{
    public function __construct(private DownloadStorageService $storageService)
    {
    }

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

    public function play(Download $download)
    {
        abort_unless($download->is_video && $download->is_published && (!$download->published_at || $download->published_at->isPast()), 404);

        $videoUrl = $download->source === 'gdrive' && $download->gdrive_file_id
            ? 'https://drive.google.com/file/d/'.$download->gdrive_file_id.'/preview?autoplay=1'
            : route('downloads.stream', ['download' => $download, 'filename' => $download->play_route_filename]);

        return view('downloads.play', compact('download', 'videoUrl'));
    }

    public function stream(Download $download, ?string $filename = null)
    {
        abort_unless($download->is_video && $download->is_published && (!$download->published_at || $download->published_at->isPast()), 404);

        if ($download->source === 'gdrive') {
            abort(404);
        }

        if (!$download->local_path || !Storage::disk($download->local_disk)->exists($download->local_path)) {
            abort(404, 'File video tidak ditemukan.');
        }

        return response()->file(Storage::disk($download->local_disk)->path($download->local_path), [
            'Content-Type' => $this->storageService->resolveMimeType($download->file_extension, $download->mime_type),
            'Content-Disposition' => 'inline; filename="'.$download->download_route_filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Download $download, ?string $filename = null)
    {
        if (!$download->is_published) {
            abort(404);
        }

        $download->increment('download_count');
        $mimeType = $this->storageService->resolveMimeType($download->file_extension, $download->mime_type);

        // Bersihkan semua output buffer (termasuk UTF-8 BOM dari file PHP
        // yang ter-save dengan BOM encoding) agar tidak mencemari binary response.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if ($download->source === 'gdrive' && !empty($download->gdrive_file_id)) {
            $fileContent = $this->storageService->downloadFromGoogleDrive($download->gdrive_file_id);
            $tempFile = tempnam(sys_get_temp_dir(), 'simansa-download-');

            if ($tempFile === false) {
                abort(500, 'Gagal menyiapkan file unduhan sementara.');
            }

            file_put_contents($tempFile, $fileContent);

            return response()->download($tempFile, $download->file_name_original, [
                'Content-Type' => $mimeType,
                'Content-Transfer-Encoding' => 'binary',
                'X-Content-Type-Options' => 'nosniff',
            ])->deleteFileAfterSend(true);
        }

        if (!$download->local_path || !Storage::disk($download->local_disk)->exists($download->local_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $absolutePath = Storage::disk($download->local_disk)->path($download->local_path);

        return response()->download($absolutePath, $download->file_name_original, [
            'Content-Type' => $mimeType,
            'Content-Transfer-Encoding' => 'binary',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
