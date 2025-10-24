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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource with DataTables
     */
    public function index(Request $request)
    {
        $this->authorize('view-kelas');

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
                                ->whereNull('deleted_at')
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
        $waliKelas = User::role(['Wali Kelas', 'GTK'])->orderBy('name')->get();
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

            // Auto-assign Wali Kelas role if wali kelas selected
            if ($request->wali_kelas_id) {
                $waliKelas = User::find($request->wali_kelas_id);
                
                if ($waliKelas && !$waliKelas->hasRole('Wali Kelas')) {
                    $waliKelasRole = \Spatie\Permission\Models\Role::where('name', 'Wali Kelas')->first();
                    
                    if ($waliKelasRole) {
                        $waliKelas->assignRole($waliKelasRole);
                        
                        \App\Models\TugasTambahan::create([
                            'user_id' => $waliKelas->id,
                            'role_id' => $waliKelasRole->id,
                            'mulai_tugas' => now()->format('Y-m-d'),
                            'is_active' => true,
                            'keterangan' => 'Otomatis dibuat saat buat kelas baru: ' . $request->nama_kelas,
                            'created_by' => Auth::id(),
                        ]);
                        
                        Log::info("Auto-assigned Wali Kelas role to {$waliKelas->name} via kelas create");
                    }
                }
            }

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
        $this->authorize('view-detail-kelas');

        $kelas->load([
            'tahunPelajaran',
            'kurikulum.jurusans',
            'jurusan',
            'waliKelas',
            'siswaAktif'
        ]);

        // Statistics
        $stats = [
            'total_siswa' => $kelas->siswaAktif->count(),
            'sisa_tempat' => $kelas->sisa_tempat,
            'percentage_filled' => $kelas->percentage_filled,
            'laki_laki' => $kelas->siswaAktif->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $kelas->siswaAktif->where('jenis_kelamin', 'P')->count(),
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
        $waliKelas = User::role(['Wali Kelas', 'GTK'])->orderBy('name')->get();
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
            // Store old wali_kelas_id before update
            $oldWaliKelasId = $kelas->wali_kelas_id;
            $newWaliKelasId = $request->wali_kelas_id;
            
            $kelas->update([
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'kurikulum_id' => $request->kurikulum_id,
                'jurusan_id' => $request->jurusan_id,
                'nama_kelas' => $request->nama_kelas,
                'tingkat' => $request->tingkat,
                'wali_kelas_id' => $newWaliKelasId,
                'kapasitas' => $request->kapasitas,
                'ruang_kelas' => $request->ruang_kelas,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->is_active ?? true,
            ]);

            // Auto-assign Wali Kelas role if wali kelas changed and new wali assigned
            if ($oldWaliKelasId != $newWaliKelasId && $newWaliKelasId) {
                $waliKelas = User::find($newWaliKelasId);
                
                if ($waliKelas && !$waliKelas->hasRole('Wali Kelas')) {
                    $waliKelasRole = \Spatie\Permission\Models\Role::where('name', 'Wali Kelas')->first();
                    
                    if ($waliKelasRole) {
                        $waliKelas->assignRole($waliKelasRole);
                        
                        \App\Models\TugasTambahan::create([
                            'user_id' => $waliKelas->id,
                            'role_id' => $waliKelasRole->id,
                            'mulai_tugas' => now()->format('Y-m-d'),
                            'is_active' => true,
                            'keterangan' => 'Otomatis dibuat saat edit kelas: ' . $kelas->nama_lengkap,
                            'created_by' => Auth::id(),
                        ]);
                        
                        Log::info("Auto-assigned Wali Kelas role to {$waliKelas->name} via kelas edit");
                    }
                }
            }

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
                ->wherePivot('tahun_pelajaran_id', $tahunPelajaranAktif->id)
                ->wherePivot('status', 'aktif')
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
     * Get available siswa for Select2 (AJAX)
     */
    public function getAvailableSiswa(Request $request, Kelas $kelas)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10); // Support custom per_page

        $query = Siswa::whereDoesntHave('kelas', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif');
            })
            // Tampilkan semua siswa (bukan hanya yang data_diri_completed)
            // ->where('data_diri_completed', true)
            ->orderBy('nama_lengkap');

        // Search by name or NISN
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        
        // If per_page > 1000, get all
        if ($perPage > 1000) {
            $siswa = $query->get();
        } else {
            $siswa = $query->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();
        }

        $items = $siswa->map(function($s) {
            return [
                'id' => $s->id, // Primary key (UUID)
                'text' => $s->nama_lengkap,
                'nisn' => $s->nisn,
                'jenis_kelamin' => $s->jenis_kelamin,
                'nama_lengkap' => $s->nama_lengkap
            ];
        });

        return response()->json([
            'items' => $items,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Show form to assign siswa to kelas
     */
    public function assignSiswa(Kelas $kelas)
    {
        // Get siswa yang belum ada di kelas manapun untuk tahun pelajaran ini
        // atau siswa yang sudah lulus dari tingkat sebelumnya
        $availableSiswa = Siswa::whereDoesntHave('kelas', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif');
            })
            // Tampilkan semua siswa (bukan hanya yang data_diri_completed)
            // ->where('data_diri_completed', true)
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
            'siswa_ids.*' => 'exists:siswa,id', // Primary key is 'id' (UUID)
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

        // Default tanggal masuk = hari ini (untuk siswa reguler, bukan mutasi)
        $tanggalMasuk = now()->format('Y-m-d');

        DB::beginTransaction();
        try {
            $successCount = 0;
            foreach ($request->siswa_ids as $siswaId) {
                // Check if already in kelas
                $exists = $kelas->siswas()
                    ->where('siswa.id', $siswaId)
                    ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->wherePivot('status', 'aktif')
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Get next nomor absen
                $lastAbsen = $kelas->siswas()
                    ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->max('siswa_kelas.nomor_urut_absen') ?? 0;

                $kelas->siswas()->attach($siswaId, [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                    'tanggal_masuk' => $tanggalMasuk,
                    'status' => 'aktif',
                    'nomor_urut_absen' => $lastAbsen + 1,
                ]);

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successCount . ' siswa berhasil ditambahkan ke kelas.'
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
     * Store siswa to kelas via NISN (bulk)
     */
    public function storeSiswaNISN(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'nisn_list' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Default tanggal masuk = hari ini (untuk siswa reguler, bukan mutasi)
        $tanggalMasuk = now()->format('Y-m-d');

        // Parse NISN list
        $nisnArray = collect(explode("\n", $request->nisn_list))
            ->map(function($nisn) {
                return preg_replace('/[^0-9]/', '', trim($nisn));
            })
            ->filter(function($nisn) {
                return !empty($nisn) && strlen($nisn) == 10;
            })
            ->unique()
            ->values();

        if ($nisnArray->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada NISN yang valid. NISN harus 10 digit angka.'
            ], 422);
        }

        // Check capacity
        $currentCount = $kelas->siswaAktif()->count();
        if (($currentCount + $nisnArray->count()) > $kelas->kapasitas) {
            return response()->json([
                'success' => false,
                'message' => 'Kapasitas kelas tidak mencukupi. Sisa tempat: ' . $kelas->sisa_tempat . ', NISN yang diinput: ' . $nisnArray->count()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errors = [];

            foreach ($nisnArray as $nisn) {
                try {
                    // Find siswa by NISN
                    $siswa = Siswa::where('nisn', $nisn)
                        // Tampilkan semua siswa (bukan hanya yang data_diri_completed)
                        // ->where('data_diri_completed', true)
                        ->first();

                    if (!$siswa) {
                        $errors[] = [
                            'nisn' => $nisn,
                            'error' => 'NISN tidak ditemukan'
                        ];
                        continue;
                    }

                    // Check if already in any kelas for this tahun pelajaran
                    $existsInKelas = $siswa->kelas()
                        ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                        ->wherePivot('status', 'aktif')
                        ->exists();

                    if ($existsInKelas) {
                        $errors[] = [
                            'nisn' => $nisn,
                            'error' => 'Siswa sudah terdaftar di kelas lain'
                        ];
                        continue;
                    }

                    // Get next nomor absen
                    $lastAbsen = $kelas->siswas()
                        ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                        ->max('nomor_urut_absen') ?? 0;

                    // Add to kelas
                    $kelas->siswas()->attach($siswa->id, [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                        'tanggal_masuk' => $tanggalMasuk,
                        'status' => 'aktif',
                        'nomor_urut_absen' => $lastAbsen + 1,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'nisn' => $nisn,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proses bulk import selesai',
                'success_count' => $successCount,
                'failed_count' => count($errors),
                'total' => $nisnArray->count(),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk import: ' . $e->getMessage()
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
            $kelas->siswas()->updateExistingPivot($siswa->id, [
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
            'wali_kelas_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for assign wali kelas', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $waliKelas = User::where('id', $request->wali_kelas_id)->first();
            
            if (!$waliKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'User dengan ID tersebut tidak ditemukan'
                ], 404);
            }
            
            // Update kelas dengan wali kelas baru
            $kelas->update([
                'wali_kelas_id' => $request->wali_kelas_id
            ]);

            // Otomatis assign role "Wali Kelas" jika belum punya
            if (!$waliKelas->hasRole('Wali Kelas')) {
                // Get Wali Kelas role
                $waliKelasRole = \Spatie\Permission\Models\Role::where('name', 'Wali Kelas')->first();
                
                if ($waliKelasRole) {
                    // Assign role
                    $waliKelas->assignRole($waliKelasRole);
                    
                    // Create tugas tambahan record
                    \App\Models\TugasTambahan::create([
                        'user_id' => $waliKelas->id,
                        'role_id' => $waliKelasRole->id,
                        'mulai_tugas' => now()->format('Y-m-d'),
                        'is_active' => true,
                        'keterangan' => 'Otomatis dibuat saat assign wali kelas ke ' . $kelas->nama_lengkap,
                        'created_by' => Auth::id(),
                    ]);
                    
                    Log::info("Auto-assigned Wali Kelas role to user: {$waliKelas->name} for class: {$kelas->nama_lengkap}");
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wali kelas berhasil ditugaskan.',
                'wali_kelas_name' => $waliKelas->name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning wali kelas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan wali kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kosongkan kelas - keluarkan semua siswa
     */
    public function kosongkanKelas(Request $request, Kelas $kelas)
    {
        $validator = Validator::make($request->all(), [
            'alasan' => 'required|string|min:10|max:500',
        ], [
            'alasan.required' => 'Alasan pengosongan kelas harus diisi',
            'alasan.min' => 'Alasan minimal 10 karakter',
            'alasan.max' => 'Alasan maksimal 500 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tanggalKeluar = now()->format('Y-m-d');
            $alasan = $request->alasan;
            
            // Get all active students
            $siswaAktif = $kelas->siswas()
                ->where('siswa_kelas.status', 'aktif')
                ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->get();

            $jumlahSiswa = $siswaAktif->count();

            if ($jumlahSiswa == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa aktif di kelas ini'
                ], 422);
            }

            // Update all siswa_kelas records to keluar
            foreach ($siswaAktif as $siswa) {
                $kelas->siswas()->updateExistingPivot($siswa->id, [
                    'status' => 'keluar',
                    'tanggal_keluar' => $tanggalKeluar,
                    'catatan_perpindahan' => 'Pengosongan Kelas: ' . $alasan,
                ]);
            }

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(auth()->user())
                ->withProperties([
                    'jumlah_siswa' => $jumlahSiswa,
                    'alasan' => $alasan,
                    'tanggal_keluar' => $tanggalKeluar,
                ])
                ->log('Mengosongkan kelas: ' . $kelas->nama_lengkap . ' (' . $jumlahSiswa . ' siswa dikeluarkan)');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dikosongkan',
                'jumlah_siswa' => $jumlahSiswa
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengosongkan kelas: ' . $e->getMessage()
            ], 500);
        }
    }
}
