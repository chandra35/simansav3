<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource with DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Kelas::with([
                'tahunPelajaran',
                'kurikulum',
                'jurusan',
                'waliKelas'
            ])->withCount('siswaAktif');

            // Apply filters
            if ($request->filled('tahun_pelajaran_id')) {
                $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
            }

            if ($request->filled('tingkat')) {
                $query->where('tingkat', $request->tingkat);
            }

            if ($request->filled('jurusan_id')) {
                $query->where('jurusan_id', $request->jurusan_id);
            }

            if ($request->filled('kurikulum_id')) {
                $query->where('kurikulum_id', $request->kurikulum_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nama_lengkap', function ($row) {
                    return $row->nama_lengkap;
                })
                ->addColumn('tingkat_romawi', function ($row) {
                    return $row->getTingkatRomawi();
                })
                ->addColumn('tahun_pelajaran', function ($row) {
                    return $row->tahunPelajaran->nama ?? '-';
                })
                ->addColumn('kurikulum_nama', function ($row) {
                    return $row->kurikulum->nama_kurikulum ?? '-';
                })
                ->addColumn('jurusan_nama', function ($row) {
                    return $row->jurusan ? '<span class="badge badge-info">' . $row->jurusan->singkatan . '</span>' : '<span class="badge badge-secondary">-</span>';
                })
                ->addColumn('wali_kelas', function ($row) {
                    return $row->waliKelas ? $row->waliKelas->name : '<span class="text-muted">Belum ditugaskan</span>';
                })
                ->addColumn('kapasitas_info', function ($row) {
                    $siswa = $row->siswa_aktif_count;
                    $kapasitas = $row->kapasitas;
                    $percentage = $row->percentage_filled;
                    $badgeColor = $row->capacity_badge_color;
                    
                    return '<span class="badge badge-' . $badgeColor . '">' . $siswa . '/' . $kapasitas . '</span>
                            <small class="d-block text-muted">' . $percentage . '%</small>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active 
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Aktif</span>' 
                        : '<span class="badge badge-secondary">Non-Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="btn-group" role="group">';
                    
                    // View button (always visible)
                    $actions .= '<a href="' . route('admin.kelas.show', $row->id) . '" class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>';
                    
                    // Edit button
                    if (auth()->user()->can('edit-kelas')) {
                        $actions .= '<a href="' . route('admin.kelas.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                    }
                    
                    // Delete button (check siswa aktif in current tahun pelajaran)
                    if (auth()->user()->can('delete-kelas')) {
                        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
                        $canDelete = true;
                        
                        if ($tahunPelajaranAktif) {
                            $siswaAktifCount = $row->siswaKelas()
                                ->where('tahun_pelajaran_id', $tahunPelajaranAktif->id)
                                ->where('status', 'aktif')
                                ->count();
                            $canDelete = ($siswaAktifCount == 0);
                        }
                        
                        if ($canDelete) {
                            $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama_lengkap) . '" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>';
                        } else {
                            $actions .= '<button type="button" class="btn btn-sm btn-secondary" disabled title="Tidak dapat dihapus (masih ada siswa aktif)">
                                            <i class="fas fa-trash"></i>
                                        </button>';
                        }
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['jurusan_nama', 'wali_kelas', 'kapasitas_info', 'status_badge', 'action'])
                ->make(true);
        }

        // Get filter options
        $tahunPelajarans = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];

        return view('admin.kelas.index', compact('tahunPelajarans', 'kurikulums', 'jurusans', 'tingkatOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tahunPelajarans = TahunPelajaran::where('is_active', true)
            ->orWhere('status', 'berlangsung')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->orderBy('urutan')->get();
        $waliKelas = User::role(['Wali Kelas', 'Guru'])->orderBy('name')->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];

        return view('admin.kelas.create', compact('tahunPelajarans', 'kurikulums', 'jurusans', 'waliKelas', 'tingkatOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|integer|in:10,11,12',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'kapasitas' => 'required|integer|min:1|max:50',
            'ruang_kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate kode_kelas
            $tahunPelajaran = TahunPelajaran::find($request->tahun_pelajaran_id);
            $jurusanKode = $request->jurusan_id 
                ? Jurusan::find($request->jurusan_id)->kode_jurusan 
                : 'UMUM';
            
            // Get nomor urut kelas untuk tingkat dan jurusan yang sama
            $lastKelas = Kelas::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                ->where('tingkat', $request->tingkat)
                ->where('jurusan_id', $request->jurusan_id)
                ->count();
            
            $nomor = $lastKelas + 1;
            $kodeKelas = Kelas::generateKodeKelas(
                $request->tingkat,
                $jurusanKode,
                $nomor,
                $tahunPelajaran->tahun_mulai
            );

            $kelas = Kelas::create([
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'kurikulum_id' => $request->kurikulum_id,
                'jurusan_id' => $request->jurusan_id,
                'nama_kelas' => $request->nama_kelas,
                'tingkat' => $request->tingkat,
                'kode_kelas' => $kodeKelas,
                'wali_kelas_id' => $request->wali_kelas_id,
                'kapasitas' => $request->kapasitas,
                'ruang_kelas' => $request->ruang_kelas,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->is_active ?? true,
            ]);

            // Load relationships to prevent "Attempt to read property on null" errors
            $kelas->load(['tahunPelajaran', 'kurikulum', 'jurusan', 'waliKelas']);

            DB::commit();

            return redirect()->route('admin.kelas.show', $kelas->id)
                ->with('success', 'Kelas berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        $kelas->load([
            'tahunPelajaran',
            'kurikulum.jurusans',
            'jurusan',
            'waliKelas',
            'siswaAktif.user'
        ]);

        // Statistics
        $stats = [
            'total_siswa' => $kelas->siswaAktif()->count(),
            'sisa_tempat' => $kelas->sisa_tempat,
            'percentage_filled' => $kelas->percentage_filled,
            'laki_laki' => $kelas->siswaAktif()->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $kelas->siswaAktif()->where('jenis_kelamin', 'P')->count(),
        ];

        return view('admin.kelas.show', compact('kelas', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kurikulums = Kurikulum::all();
        $jurusans = Jurusan::where('is_active', true)->orderBy('urutan')->get();
        $waliKelas = User::role(['Wali Kelas', 'Guru'])->orderBy('name')->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];

        return view('admin.kelas.edit', compact('kelas', 'tahunPelajarans', 'kurikulums', 'jurusans', 'waliKelas', 'tingkatOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|integer|in:10,11,12',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'kapasitas' => 'required|integer|min:1|max:50',
            'ruang_kelas' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if reducing capacity below current student count
        $currentSiswaCount = $kelas->siswaAktif()->count();
        if ($request->kapasitas < $currentSiswaCount) {
            return redirect()->back()
                ->with('error', 'Kapasitas tidak boleh lebih kecil dari jumlah siswa saat ini (' . $currentSiswaCount . ' siswa).')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $kelas->update([
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'kurikulum_id' => $request->kurikulum_id,
                'jurusan_id' => $request->jurusan_id,
                'nama_kelas' => $request->nama_kelas,
                'tingkat' => $request->tingkat,
                'wali_kelas_id' => $request->wali_kelas_id,
                'kapasitas' => $request->kapasitas,
                'ruang_kelas' => $request->ruang_kelas,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->is_active ?? true,
            ]);

            DB::commit();

            return redirect()->route('admin.kelas.show', $kelas->id)
                ->with('success', 'Kelas berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Check if kelas has active students in current tahun pelajaran
        if ($tahunPelajaranAktif) {
            $siswaAktifCount = $kelas->siswaKelas()
                ->where('tahun_pelajaran_id', $tahunPelajaranAktif->id)
                ->where('status', 'aktif')
                ->count();
            
            if ($siswaAktifCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus kelas yang masih memiliki {$siswaAktifCount} siswa aktif di tahun pelajaran {$tahunPelajaranAktif->nama}."
                ], 422);
            }
        }

        try {
            $namaKelas = $kelas->nama_lengkap;
            $kelas->delete();

            return response()->json([
                'success' => true,
                'message' => "Kelas {$namaKelas} berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form to assign siswa to kelas
     */
    public function assignSiswa(Kelas $kelas)
    {
        // Get siswa yang belum ada di kelas manapun untuk tahun pelajaran ini
        // atau siswa yang sudah lulus dari tingkat sebelumnya
        $availableSiswa = Siswa::whereDoesntHave('kelas', function ($query) use ($kelas) {
                $query->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('status', 'aktif');
            })
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.kelas.assign-siswa', compact('kelas', 'availableSiswa'));
    }

    /**
     * Store siswa to kelas
     */
    public function storeSiswa(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,uuid',
            'tanggal_masuk' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check capacity
        $currentCount = $kelas->siswaAktif()->count();
        $newCount = count($request->siswa_ids);
        if (($currentCount + $newCount) > $kelas->kapasitas) {
            return response()->json([
                'success' => false,
                'message' => 'Kapasitas kelas tidak mencukupi. Sisa tempat: ' . $kelas->sisa_tempat
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->siswa_ids as $siswaId) {
                // Get next nomor absen
                $lastAbsen = $kelas->siswas()
                    ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->max('nomor_urut_absen') ?? 0;

                $kelas->siswas()->attach($siswaId, [
                    'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'status' => 'aktif',
                    'nomor_urut_absen' => $lastAbsen + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->siswa_ids) . ' siswa berhasil ditambahkan ke kelas.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove siswa from kelas
     */
    public function removeSiswa(Request $request, Kelas $kelas, Siswa $siswa)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_keluar' => 'required|date',
            'status' => 'required|in:naik_kelas,tinggal_kelas,lulus,keluar',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update pivot table
            $kelas->siswas()->updateExistingPivot($siswa->uuid, [
                'tanggal_keluar' => $request->tanggal_keluar,
                'status' => $request->status,
                'catatan_perpindahan' => $request->catatan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dikeluarkan dari kelas.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign wali kelas
     */
    public function assignWaliKelas(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'wali_kelas_id' => 'required|exists:users,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $kelas->update([
                'wali_kelas_id' => $request->wali_kelas_id
            ]);

            $waliKelas = User::where('uuid', $request->wali_kelas_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Wali kelas berhasil ditugaskan.',
                'wali_kelas_name' => $waliKelas->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan wali kelas: ' . $e->getMessage()
            ], 500);
        }
    }
}
