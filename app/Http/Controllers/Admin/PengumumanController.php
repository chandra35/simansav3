<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Pengumuman::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kategori_badge', function ($row) {
                    return '<span class="badge badge-' . $row->kategori_badge . '">' . ucfirst($row->kategori) . '</span>';
                })
                ->addColumn('prioritas_badge', function ($row) {
                    return '<span class="badge badge-' . $row->prioritas_badge . '">' . ucfirst($row->prioritas) . '</span>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_aktif) {
                        return '<span class="badge badge-success">Aktif</span>';
                    }
                    return '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('pinned', function ($row) {
                    return $row->is_pinned 
                        ? '<i class="fas fa-thumbtack text-warning"></i>' 
                        : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.pengumuman.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.pengumuman.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['kategori_badge', 'prioritas_badge', 'status', 'pinned', 'action'])
                ->make(true);
        }

        return view('admin.pengumuman.index');
    }

    public function create()
    {
        $kategori = Pengumuman::KATEGORI;
        $prioritas = Pengumuman::PRIORITAS;
        $target = Pengumuman::TARGET;

        return view('admin.pengumuman.create', compact('kategori', 'prioritas', 'target'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'required|in:umum,akademik,kegiatan,pengumuman,penting',
            'prioritas' => 'required|in:normal,tinggi,urgent',
            'target' => 'required|in:semua,siswa,guru,wali',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_pinned' => 'boolean',
            'lampiran' => 'nullable|file|max:10240',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_pinned'] = $request->boolean('is_pinned');

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        Pengumuman::create($validated);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('admin.pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman)
    {
        $kategori = Pengumuman::KATEGORI;
        $prioritas = Pengumuman::PRIORITAS;
        $target = Pengumuman::TARGET;

        return view('admin.pengumuman.edit', compact('pengumuman', 'kategori', 'prioritas', 'target'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'required|in:umum,akademik,kegiatan,pengumuman,penting',
            'prioritas' => 'required|in:normal,tinggi,urgent',
            'target' => 'required|in:semua,siswa,guru,wali',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_pinned' => 'boolean',
            'lampiran' => 'nullable|file|max:10240',
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        if ($request->hasFile('lampiran')) {
            // Delete old file
            if ($pengumuman->lampiran) {
                \Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $validated['lampiran'] = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $pengumuman->update($validated);

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return response()->json(['success' => true, 'message' => 'Pengumuman berhasil dihapus']);
    }
}
