<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TahunPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TahunPelajaran::with('kurikulum')
                ->select('tahun_pelajaran.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('kurikulum_nama', function ($row) {
                    return $row->kurikulum ? $row->kurikulum->formatted_name : '-';
                })
                ->addColumn('periode', function ($row) {
                    return $row->formatted_name;
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = $row->badge_color;
                    $text = ucfirst($row->status);
                    $icon = $row->is_active ? '<i class="fas fa-check-circle"></i>' : '';
                    return "<span class='badge badge-{$badge}'>{$icon} {$text}</span>";
                })
                ->addColumn('semester_badge', function ($row) {
                    return $row->semester_badge;
                })
                ->addColumn('kuota_info', function ($row) {
                    $tersedia = $row->kuota_tersedia;
                    $total = $row->kuota_ppdb;
                    $percentage = $total > 0 ? round(($total - $tersedia) / $total * 100) : 0;
                    
                    $color = 'success';
                    if ($percentage >= 90) $color = 'danger';
                    elseif ($percentage >= 70) $color = 'warning';
                    
                    return "
                        <div class='text-center'>
                            <small class='text-muted'>Tersedia: <strong>{$tersedia}</strong> / {$total}</small>
                            <div class='progress progress-xs mt-1'>
                                <div class='progress-bar bg-{$color}' style='width: {$percentage}%'></div>
                            </div>
                        </div>
                    ";
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    
                    // Button Set Active (only if not active)
                    if (!$row->is_active && $row->status !== 'selesai') {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success btn-set-active" data-id="' . $row->id . '" title="Set Aktif">
                            <i class="fas fa-check-circle"></i>
                        </button> ';
                    }
                    
                    // Button Change Semester (only if active)
                    if ($row->is_active) {
                        $nextSemester = $row->semester_aktif === 'Ganjil' ? 'Genap' : 'Ganjil';
                        $buttons .= '<button type="button" class="btn btn-sm btn-info btn-change-semester" data-id="' . $row->id . '" data-semester="' . $nextSemester . '" title="Ganti ke Semester ' . $nextSemester . '">
                            <i class="fas fa-sync-alt"></i>
                        </button> ';
                    }
                    
                    // Button Edit
                    $buttons .= '<a href="' . route('admin.tahun-pelajaran.edit', $row->id) . '" class="btn btn-sm btn-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a> ';
                    
                    // Button Delete (only if not active and not selesai)
                    if (!$row->is_active && $row->status !== 'selesai') {
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'semester_badge', 'kuota_info', 'action'])
                ->make(true);
        }

        return view('admin.tahun-pelajaran.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kurikulums = Kurikulum::active()->orderBy('tahun_berlaku', 'desc')->get();
        return view('admin.tahun-pelajaran.create', compact('kurikulums'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'nama' => 'required|string|max:50|unique:tahun_pelajaran,nama',
            'tahun_mulai' => 'required|integer|min:2000|max:2100',
            'tahun_selesai' => 'required|integer|min:2000|max:2100|gt:tahun_mulai',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'semester_aktif' => 'required|in:Ganjil,Genap',
            'status' => 'required|in:aktif,non-aktif,selesai',
            'kuota_ppdb' => 'required|integer|min:0',
        ], [
            'kurikulum_id.required' => 'Kurikulum harus dipilih',
            'kurikulum_id.exists' => 'Kurikulum tidak valid',
            'nama.required' => 'Nama tahun pelajaran harus diisi',
            'nama.unique' => 'Tahun pelajaran sudah ada',
            'tahun_mulai.required' => 'Tahun mulai harus diisi',
            'tahun_selesai.required' => 'Tahun selesai harus diisi',
            'tahun_selesai.gt' => 'Tahun selesai harus lebih besar dari tahun mulai',
            'tanggal_mulai.required' => 'Tanggal mulai harus diisi',
            'tanggal_selesai.required' => 'Tanggal selesai harus diisi',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'semester_aktif.required' => 'Semester aktif harus dipilih',
            'status.required' => 'Status harus dipilih',
            'kuota_ppdb.required' => 'Kuota PPDB harus diisi',
            'kuota_ppdb.min' => 'Kuota PPDB minimal 0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $tahunPelajaran = TahunPelajaran::create([
                'kurikulum_id' => $request->kurikulum_id,
                'nama' => $request->nama,
                'tahun_mulai' => $request->tahun_mulai,
                'tahun_selesai' => $request->tahun_selesai,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'semester_aktif' => $request->semester_aktif,
                'status' => $request->status,
                'kuota_ppdb' => $request->kuota_ppdb,
                'is_active' => false, // Default tidak aktif, harus di-set manual
            ]);

            DB::commit();

            return redirect()->route('admin.tahun-pelajaran.index')
                ->with('success', 'Tahun pelajaran berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan tahun pelajaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TahunPelajaran $tahunPelajaran)
    {
        $tahunPelajaran->load(['kurikulum', 'kelas.jurusan', 'kelas.waliKelas']);
        
        // Statistik
        $stats = [
            'total_kelas' => $tahunPelajaran->kelas()->count(),
            'total_siswa' => $tahunPelajaran->siswas()->wherePivot('status', 'aktif')->count(),
            'mutasi_masuk' => $tahunPelajaran->mutasiMasuk()->count(),
            'mutasi_keluar' => $tahunPelajaran->mutasiKeluar()->count(),
            'kuota_tersedia' => $tahunPelajaran->kuota_tersedia,
        ];

        return view('admin.tahun-pelajaran.show', compact('tahunPelajaran', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TahunPelajaran $tahunPelajaran)
    {
        $kurikulums = Kurikulum::active()->orderBy('tahun_berlaku', 'desc')->get();
        return view('admin.tahun-pelajaran.edit', compact('tahunPelajaran', 'kurikulums'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $validator = Validator::make($request->all(), [
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'nama' => 'required|string|max:50|unique:tahun_pelajaran,nama,' . $tahunPelajaran->id,
            'tahun_mulai' => 'required|integer|min:2000|max:2100',
            'tahun_selesai' => 'required|integer|min:2000|max:2100|gt:tahun_mulai',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'semester_aktif' => 'required|in:Ganjil,Genap',
            'status' => 'required|in:aktif,non-aktif,selesai',
            'kuota_ppdb' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $tahunPelajaran->update([
                'kurikulum_id' => $request->kurikulum_id,
                'nama' => $request->nama,
                'tahun_mulai' => $request->tahun_mulai,
                'tahun_selesai' => $request->tahun_selesai,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'semester_aktif' => $request->semester_aktif,
                'status' => $request->status,
                'kuota_ppdb' => $request->kuota_ppdb,
            ]);

            DB::commit();

            return redirect()->route('admin.tahun-pelajaran.index')
                ->with('success', 'Tahun pelajaran berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui tahun pelajaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        // Tidak bisa hapus jika aktif atau sudah selesai
        if ($tahunPelajaran->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus tahun pelajaran yang sedang aktif'
            ], 422);
        }

        if ($tahunPelajaran->status === 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus tahun pelajaran yang sudah selesai'
            ], 422);
        }

        // Cek apakah sudah ada kelas
        if ($tahunPelajaran->kelas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus tahun pelajaran yang sudah memiliki kelas'
            ], 422);
        }

        try {
            $tahunPelajaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tahun pelajaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tahun pelajaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set tahun pelajaran as active (deactivate others)
     */
    public function setActive(TahunPelajaran $tahunPelajaran)
    {
        try {
            DB::beginTransaction();

            // Use model method to ensure only one active
            $tahunPelajaran->setAsActive();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tahun pelajaran ' . $tahunPelajaran->nama . ' berhasil diaktifkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan tahun pelajaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change semester (toggle Ganjil <-> Genap)
     */
    public function changeSemester(TahunPelajaran $tahunPelajaran)
    {
        if (!$tahunPelajaran->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tahun pelajaran aktif yang dapat mengubah semester'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Use model method to switch semester
            $tahunPelajaran->switchSemester();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semester berhasil diubah ke ' . $tahunPelajaran->semester_aktif,
                'semester' => $tahunPelajaran->semester_aktif
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah semester: ' . $e->getMessage()
            ], 500);
        }
    }
}
