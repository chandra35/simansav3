<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KurikulumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Kurikulum::withCount('jurusans')
                ->orderBy('tahun_berlaku', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('formatted_name', function ($row) {
                    return $row->formatted_name;
                })
                ->addColumn('has_jurusan_badge', function ($row) {
                    if ($row->has_jurusan) {
                        return '<span class="badge badge-success"><i class="fas fa-check"></i> Ya (' . $row->jurusans_count . ' jurusan)</span>';
                    }
                    return '<span class="badge badge-secondary"><i class="fas fa-times"></i> Tidak</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = $row->badge_color;
                    $text = $row->status_text;
                    return "<span class='badge badge-{$badge}'>{$text}</span>";
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    
                    // Button View
                    $buttons .= '<a href="' . route('admin.kurikulum.show', $row->id) . '" class="btn btn-sm btn-info" title="Detail">
                        <i class="fas fa-eye"></i>
                    </a> ';
                    
                    // Button Edit
                    if (auth()->user()->can('edit-kurikulum')) {
                        $buttons .= '<a href="' . route('admin.kurikulum.edit', $row->id) . '" class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Button Activate/Deactivate
                    if (auth()->user()->can('activate-kurikulum')) {
                        if ($row->is_active) {
                            $buttons .= '<button type="button" class="btn btn-sm btn-warning btn-deactivate" data-id="' . $row->id . '" title="Nonaktifkan">
                                <i class="fas fa-toggle-off"></i>
                            </button> ';
                        } else {
                            $buttons .= '<button type="button" class="btn btn-sm btn-success btn-activate" data-id="' . $row->id . '" title="Aktifkan">
                                <i class="fas fa-toggle-on"></i>
                            </button> ';
                        }
                    }
                    
                    // Button Delete (only if not active)
                    if (auth()->user()->can('delete-kurikulum') && !$row->is_active) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['has_jurusan_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.kurikulum.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kurikulum.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:20|unique:kurikulum,kode',
            'nama_kurikulum' => 'required|string|max:100',
            'tahun_berlaku' => 'required|integer|min:1990|max:2100',
            'has_jurusan' => 'required|boolean',
            'deskripsi' => 'nullable|string',
        ], [
            'kode.required' => 'Kode kurikulum harus diisi',
            'kode.unique' => 'Kode kurikulum sudah ada',
            'nama_kurikulum.required' => 'Nama kurikulum harus diisi',
            'tahun_berlaku.required' => 'Tahun berlaku harus diisi',
            'has_jurusan.required' => 'Pilihan peminatan/jurusan harus diisi',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            Kurikulum::create([
                'kode' => strtoupper($request->kode),
                'nama_kurikulum' => $request->nama_kurikulum,
                'tahun_berlaku' => $request->tahun_berlaku,
                'has_jurusan' => $request->has_jurusan,
                'deskripsi' => $request->deskripsi,
                'is_active' => false,
            ]);

            DB::commit();

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Kurikulum berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kurikulum: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kurikulum $kurikulum)
    {
        $kurikulum->load(['jurusans' => function($query) {
            $query->orderBy('urutan');
        }, 'tahunPelajarans', 'kelas']);

        $stats = [
            'total_jurusan' => $kurikulum->jurusans()->count(),
            'total_tahun_pelajaran' => $kurikulum->tahunPelajarans()->count(),
            'total_kelas' => $kurikulum->kelas()->count(),
            'tahun_pelajaran_aktif' => $kurikulum->tahunPelajarans()->where('is_active', true)->first(),
        ];

        return view('admin.kurikulum.show', compact('kurikulum', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kurikulum $kurikulum)
    {
        return view('admin.kurikulum.edit', compact('kurikulum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kurikulum $kurikulum)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:20|unique:kurikulum,kode,' . $kurikulum->id,
            'nama_kurikulum' => 'required|string|max:100',
            'tahun_berlaku' => 'required|integer|min:1990|max:2100',
            'has_jurusan' => 'required|boolean',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $kurikulum->update([
                'kode' => strtoupper($request->kode),
                'nama_kurikulum' => $request->nama_kurikulum,
                'tahun_berlaku' => $request->tahun_berlaku,
                'has_jurusan' => $request->has_jurusan,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();

            return redirect()->route('admin.kurikulum.index')
                ->with('success', 'Kurikulum berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui kurikulum: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kurikulum $kurikulum)
    {
        if ($kurikulum->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kurikulum yang sedang aktif'
            ], 422);
        }

        if ($kurikulum->tahunPelajarans()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kurikulum yang sudah memiliki tahun pelajaran'
            ], 422);
        }

        try {
            $kurikulum->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kurikulum berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kurikulum: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate(Kurikulum $kurikulum)
    {
        try {
            $kurikulum->update(['is_active' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Kurikulum berhasil diaktifkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deactivate(Kurikulum $kurikulum)
    {
        try {
            $kurikulum->update(['is_active' => false]);
            return response()->json([
                'success' => true,
                'message' => 'Kurikulum berhasil dinonaktifkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
