<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index()
    {
        $beritas = Berita::latest()->get();
        return view('admin.ppdb.berita.index', compact('beritas'));
    }

    public function create()
    {
        $kategoris = ['Pengumuman', 'Informasi', 'Kegiatan', 'Prestasi', 'Akademik'];
        return view('admin.ppdb.berita.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:beritas,slug',
            'konten' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'kategori' => 'nullable|string|max:100',
            'penulis' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_featured' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['gambar', 'slug']);
        $data['slug'] = $request->slug ?? Str::slug($request->judul);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['penulis'] = $request->penulis ?? auth()->user()->name;

        // Upload gambar
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('berita', $filename, 'public');
            $data['gambar'] = $path;
        }

        $berita = Berita::create($data);

        // Log activity
        activity()
            ->performedOn($berita)
            ->causedBy(auth()->user())
            ->log('Created berita: ' . $berita->judul);

        return redirect()->route('admin.ppdb.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(Berita $berita)
    {
        $kategoris = ['Pengumuman', 'Informasi', 'Kegiatan', 'Prestasi', 'Akademik'];
        return view('admin.ppdb.berita.edit', compact('berita', 'kategoris'));
    }

    public function update(Request $request, Berita $berita)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:beritas,slug,' . $berita->id,
            'konten' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'kategori' => 'nullable|string|max:100',
            'penulis' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_featured' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['gambar', 'slug']);
        $data['slug'] = $request->slug ?? Str::slug($request->judul);
        $data['is_featured'] = $request->boolean('is_featured');

        // Upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('berita', $filename, 'public');
            $data['gambar'] = $path;
        }

        $berita->update($data);

        // Log activity
        activity()
            ->performedOn($berita)
            ->causedBy(auth()->user())
            ->log('Updated berita: ' . $berita->judul);

        return redirect()->route('admin.ppdb.berita.index')
            ->with('success', 'Berita berhasil diupdate.');
    }

    public function destroy(Berita $berita)
    {
        // Hapus gambar
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['judul' => $berita->judul])
            ->log('Deleted berita');

        return redirect()->route('admin.ppdb.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    public function toggleStatus(Berita $berita)
    {
        $berita->status = $berita->status === 'active' ? 'inactive' : 'active';
        $berita->save();

        return response()->json([
            'success' => true,
            'status' => $berita->status,
            'message' => 'Status berita berhasil diubah.'
        ]);
    }

    /**
     * Share berita to Facebook.
     */
    public function shareToFacebook(Berita $berita)
    {
        try {
            $fbService = new FacebookService();
            
            if (!$fbService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook belum dikonfigurasi. Silakan atur di Pengaturan Site.',
                ]);
            }

            $message = $berita->judul . "\n\n" . $berita->excerpt;
            $link = route('ppdb.berita.detail', $berita->slug);
            $imageUrl = $berita->gambar ? url('storage/' . $berita->gambar) : null;

            $result = $fbService->postToPage($message, $link, $imageUrl);

            if ($result['success']) {
                $berita->update([
                    'shared_to_facebook' => true,
                    'facebook_post_id' => $result['post_id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Berita berhasil dibagikan ke Facebook!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
