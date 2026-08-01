<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\SiswaKelas;
use App\Models\User;
use App\Services\ActivityLogService;
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
                'waliKelas',
                'ketuaKelasRecord.siswa',
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
                    return e($row->nama_lengkap) . $row->asrama_badge;
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
                ->addColumn('ketua_kelas', function ($row) {
                    $nama = $row->ketuaKelasRecord?->siswa?->nama_lengkap;

                    return $nama
                        ? '<span class="font-weight-semibold"><i class="fas fa-crown text-warning mr-1"></i>' . e($nama) . '</span>'
                        : '<span class="text-muted">Belum ditetapkan</span>';
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
                    
                    // Jadwal Pelajaran button
                    if (auth()->user()->can('view-jadwal-pelajaran')) {
                        $tahunAktifJadwal = TahunPelajaran::where('is_active', true)->first();
                        $jadwalUrl = route('admin.jadwal-pelajaran.timetable', [
                            'kelas_id'           => $row->id,
                            'tahun_pelajaran_id' => $tahunAktifJadwal?->id ?? $row->tahun_pelajaran_id,
                            'semester'           => 1,
                        ]);
                        $actions .= '<a href="' . $jadwalUrl . '" class="btn btn-sm btn-primary" title="Jadwal Pelajaran">
                                        <i class="fas fa-table"></i>
                                    </a>';
                    }

                    // Cetak Absensi button
                    if (auth()->user()->can('view-kelas')) {
                        $actions .= '<a href="' . route('admin.kelas.cetak-absensi', $row->id) . '" class="btn btn-sm btn-success" title="Cetak Absensi" target="_blank">
                                        <i class="fas fa-print"></i>
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
                ->rawColumns(['nama_lengkap', 'jurusan_nama', 'wali_kelas', 'ketua_kelas', 'kapasitas_info', 'status_badge', 'action'])
                ->make(true);
        }

        // Get filter options
        $tahunPelajarans = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $jurusans = Jurusan::where('is_active', true)->get();
        $tingkatOptions = [10 => 'X', 11 => 'XI', 12 => 'XII'];
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $stats = [
            'total' => Kelas::count(),
            'aktif' => Kelas::where('is_active', true)->count(),
            'tahun_aktif' => $tahunAktif
                ? Kelas::where('tahun_pelajaran_id', $tahunAktif->id)->count()
                : 0,
            'kapasitas_penuh' => Kelas::withCount('siswaAktif')
                ->get()
                ->filter(fn ($kelas) => $kelas->siswa_aktif_count >= $kelas->kapasitas)
                ->count(),
        ];

        return view('admin.kelas.index', compact(
            'tahunPelajarans',
            'kurikulums',
            'jurusans',
            'tingkatOptions',
            'tahunAktif',
            'stats'
        ));
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
        $this->authorize('create-kelas');
        
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
            'is_asrama' => 'boolean',
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
            
            // Get jurusan code
            if ($request->jurusan_id) {
                $jurusan = Jurusan::find($request->jurusan_id);
                // Use singkatan if kode_jurusan is empty
                $jurusanKode = $jurusan 
                    ? ($jurusan->kode_jurusan ?: $jurusan->singkatan ?: 'UMUM')
                    : 'UMUM';
            } else {
                $jurusanKode = 'UMUM';
            }
            
            // Find the highest nomor for this kode pattern
            $pattern = $request->tingkat . '-' . $jurusanKode . '-%' . $tahunPelajaran->tahun_mulai;
            
            // IMPORTANT: Only check non-deleted kelas (exclude soft deleted)
            // This allows nomor reuse after kelas deletion
            $existingKelas = Kelas::where('kode_kelas', 'LIKE', $pattern)
                ->whereNull('deleted_at') // Explicitly exclude soft deleted
                ->get();
            
            // Extract nomor from kode_kelas and find max
            $maxNomor = 0;
            foreach ($existingKelas as $k) {
                // Kode kelas format: 10-UMUM-1-2025 or 10-IPA-2-2025
                $parts = explode('-', $k->kode_kelas);
                if (count($parts) >= 4) {
                    // Get the third part (nomor urut)
                    $nomor = (int) $parts[2];
                    if ($nomor > $maxNomor) {
                        $maxNomor = $nomor;
                    }
                }
            }
            
            $nomor = $maxNomor + 1;
            
            // Generate kode kelas
            $kodeKelas = Kelas::generateKodeKelas(
                $request->tingkat,
                $jurusanKode,
                $nomor,
                $tahunPelajaran->tahun_mulai
            );
            
            // Check if there's a soft deleted kelas with same kode
            $softDeletedKelas = Kelas::where('kode_kelas', $kodeKelas)
                ->whereNotNull('deleted_at')
                ->withTrashed()
                ->first();
            
            if ($softDeletedKelas && !$request->has('force_create')) {
                // Ada kelas yang sudah dihapus dengan kode yang sama
                // Return info dan minta konfirmasi user
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('warning', 'Ditemukan kelas yang sudah dihapus dengan kode yang sama.')
                    ->with('soft_deleted_kelas', [
                        'id' => $softDeletedKelas->id,
                        'kode_kelas' => $softDeletedKelas->kode_kelas,
                        'nama_kelas' => $softDeletedKelas->nama_kelas,
                        'deleted_at' => $softDeletedKelas->deleted_at->format('d/m/Y H:i'),
                        'jumlah_siswa' => $softDeletedKelas->siswaKelas()->count(),
                    ]);
            }
            
            // Double check for duplicate (safety check)
            // Only check non-deleted kelas
            $attempts = 0;
            while (Kelas::where('kode_kelas', $kodeKelas)->whereNull('deleted_at')->exists() && $attempts < 10) {
                $nomor++;
                $kodeKelas = Kelas::generateKodeKelas(
                    $request->tingkat,
                    $jurusanKode,
                    $nomor,
                    $tahunPelajaran->tahun_mulai
                );
                $attempts++;
            }
            
            if ($attempts >= 10) {
                throw new \Exception('Gagal generate kode kelas unik setelah 10 percobaan. Pattern: ' . $pattern);
            }

            $kelas = Kelas::create([
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'kurikulum_id' => $request->kurikulum_id,
                'jurusan_id' => $request->jurusan_id,
                'nama_kelas' => $request->nama_kelas,
                'tingkat' => $request->tingkat,
                'jenis_kelas' => 'reguler',
                'kode_kelas' => $kodeKelas,
                'wali_kelas_id' => $request->wali_kelas_id,
                'kapasitas' => $request->kapasitas,
                'ruang_kelas' => $request->ruang_kelas,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->is_active ?? true,
                'is_asrama' => $request->boolean('is_asrama'),
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

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_kelas,
                    'tingkat' => $kelas->tingkat,
                    'tahun_pelajaran' => $kelas->tahunPelajaran->nama ?? null,
                    'wali_kelas' => $kelas->waliKelas->name ?? null,
                ])
                ->log('Membuat kelas baru: ' . $kelas->nama_lengkap . ' (' . $kelas->kode_kelas . ')');

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
            'ketuaKelasRecord.siswa',
            'siswaAktif.sekolahAsal'
        ]);

        $students = $kelas->siswaAktif
            ->sortBy('nama_lengkap', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $kelasAsalBySiswa = collect();
        if ((int) $kelas->tingkat > 10 && $kelas->tahunPelajaran) {
            $previousYearId = TahunPelajaran::query()
                ->where('tahun_mulai', $kelas->tahunPelajaran->tahun_mulai - 1)
                ->latest('tanggal_mulai')
                ->value('id');

            if ($previousYearId) {
                $kelasAsalBySiswa = SiswaKelas::query()
                    ->with('kelas.jurusan')
                    ->whereIn('siswa_id', $students->pluck('id'))
                    ->where('tahun_pelajaran_id', $previousYearId)
                    ->where('tingkat', (int) $kelas->tingkat - 1)
                    ->latest('created_at')
                    ->get()
                    ->unique('siswa_id')
                    ->mapWithKeys(fn (SiswaKelas $record) => [
                        $record->siswa_id => $record->kelas?->nama_lengkap,
                    ]);
            }
        }

        $availableGtk = collect();
        $waliKelasRombelByUser = collect();
        if (Auth::user()->can('assign-wali-kelas')) {
            $availableGtk = User::query()
                ->where('is_active', true)
                ->whereHas('gtk', function ($query) {
                    $query->where('kategori_ptk', 'Pendidik')
                        ->whereIn('jenis_ptk', ['Guru Mapel', 'Guru BK']);
                })
                ->with('gtk')
                ->orderBy('name')
                ->get();

            $waliKelasRombelByUser = Kelas::query()
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('is_active', true)
                ->whereIn('wali_kelas_id', $availableGtk->pluck('id'))
                ->orderBy('nama_kelas')
                ->get(['wali_kelas_id', 'nama_kelas'])
                ->groupBy('wali_kelas_id')
                ->map(fn ($classes) => $classes->pluck('nama_kelas')->filter()->values());
        }

        // Statistics
        $stats = [
            'total_siswa' => $students->count(),
            'sisa_tempat' => $kelas->sisa_tempat,
            'percentage_filled' => $kelas->percentage_filled,
            'laki_laki' => $students->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $students->where('jenis_kelamin', 'P')->count(),
        ];

        $transferClasses = Auth::user()->can('transfer-siswa-kelas')
            ? Kelas::query()
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('tingkat', $kelas->tingkat)
                ->where('is_active', true)
                ->where('id', '<>', $kelas->id)
                ->withCount('siswaAktif')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        return view('admin.kelas.show', compact(
            'kelas',
            'students',
            'kelasAsalBySiswa',
            'availableGtk',
            'waliKelasRombelByUser',
            'stats',
            'transferClasses'
        ));
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
            'is_asrama' => 'boolean',
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
                'jenis_kelas' => 'reguler',
                'wali_kelas_id' => $newWaliKelasId,
                'kapasitas' => $request->kapasitas,
                'ruang_kelas' => $request->ruang_kelas,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->is_active ?? true,
                'is_asrama' => $request->boolean('is_asrama'),
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

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_values' => [
                        'wali_kelas_id' => $oldWaliKelasId,
                    ],
                    'new_values' => [
                        'nama_kelas' => $kelas->nama_kelas,
                        'tingkat' => $kelas->tingkat,
                        'kapasitas' => $kelas->kapasitas,
                        'wali_kelas_id' => $newWaliKelasId,
                        'is_active' => $kelas->is_active,
                    ],
                ])
                ->log('Mengupdate kelas: ' . $kelas->nama_lengkap);

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
        $this->authorize('delete-kelas');
        
        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Check if kelas has active students in current tahun pelajaran
        if ($tahunPelajaranAktif) {
            $siswaAktifCount = $kelas->siswaKelas()
                ->where('tahun_pelajaran_id', $tahunPelajaranAktif->id)
                ->where('status', 'aktif')
                ->whereNull('deleted_at')
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
            $kodeKelas = $kelas->kode_kelas;
            
            $kelas->delete();

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kodeKelas,
                    'nama_kelas' => $namaKelas,
                ])
                ->log('Menghapus kelas: ' . $namaKelas . ' (' . $kodeKelas . ')');

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
     * Restore soft deleted kelas
     */
    public function restore($id)
    {
        $this->authorize('create-kelas'); // Use same permission as create
        
        try {
            $kelas = Kelas::withTrashed()->findOrFail($id);
            
            if (!$kelas->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas tidak dalam status terhapus.'
                ], 422);
            }
            
            // Check if kode_kelas already exists in active kelas
            $existingKelas = Kelas::where('kode_kelas', $kelas->kode_kelas)
                ->whereNull('deleted_at')
                ->first();
            
            if ($existingKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode kelas sudah digunakan oleh kelas lain. Tidak dapat restore.'
                ], 422);
            }
            
            $kelas->restore();
            $kelas->load(['tahunPelajaran', 'kurikulum', 'jurusan', 'waliKelas']);

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_lengkap,
                ])
                ->log('Restore kelas: ' . $kelas->nama_lengkap . ' (' . $kelas->kode_kelas . ')');

            return response()->json([
                'success' => true,
                'message' => "Kelas {$kelas->nama_lengkap} berhasil di-restore.",
                'redirect' => route('admin.kelas.show', $kelas->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal restore kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available siswa for Select2 (AJAX)
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

        $query = Siswa::where(function ($query) use ($kelas) {
            $query->whereDoesntHave('siswaKelasRecords', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif');
            })->orWhereHas('siswaKelasRecords', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif')
                      ->whereNull('siswa_kelas.kelas_id')
                      ->where('siswa_kelas.tingkat', $kelas->tingkat);
            });
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
        $availableSiswa = Siswa::where(function ($query) use ($kelas) {
            $query->whereDoesntHave('siswaKelasRecords', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif');
            })->orWhereHas('siswaKelasRecords', function ($query) use ($kelas) {
                $query->where('siswa_kelas.tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->where('siswa_kelas.status', 'aktif')
                      ->whereNull('siswa_kelas.kelas_id')
                      ->where('siswa_kelas.tingkat', $kelas->tingkat);
            });
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
                $activeRecord = SiswaKelas::where('siswa_id', $siswaId)
                    ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->where('status', 'aktif')
                    ->first();

                if ($activeRecord && $activeRecord->kelas_id && $activeRecord->kelas_id !== $kelas->id) {
                    continue;
                }

                if ($activeRecord && $activeRecord->kelas_id === $kelas->id) {
                    continue;
                }

                // Get next nomor absen
                $lastAbsen = $kelas->siswas()
                    ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->max('siswa_kelas.nomor_urut_absen') ?? 0;

                if ($activeRecord) {
                    $activeRecord->update([
                        'kelas_id' => $kelas->id,
                        'tingkat' => $kelas->tingkat,
                        'nomor_urut_absen' => $lastAbsen + 1,
                        'catatan_perpindahan' => trim(($activeRecord->catatan_perpindahan ? $activeRecord->catatan_perpindahan . ' ' : '') . 'Ditempatkan ke rombel ' . $kelas->nama_kelas . '.'),
                        'keberadaan_diverifikasi_at' => null,
                        'keberadaan_diverifikasi_by' => null,
                    ]);
                } else {
                    $kelas->siswas()->attach($siswaId, [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                        'tingkat' => $kelas->tingkat,
                        'tanggal_masuk' => $tanggalMasuk,
                        'status' => 'aktif',
                        'nomor_urut_absen' => $lastAbsen + 1,
                    ]);
                }

                // Update kelas_saat_ini_id pada tabel siswa
                Siswa::where('id', $siswaId)->update([
                    'kelas_saat_ini_id' => $kelas->id
                ]);

                $successCount++;
            }

            DB::commit();

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_lengkap,
                    'jumlah_siswa' => $successCount,
                    'siswa_ids' => $request->siswa_ids,
                ])
                ->log('Menambahkan ' . $successCount . ' siswa ke kelas: ' . $kelas->nama_lengkap);

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

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errors = [];

            $siswaByNisn = Siswa::query()
                ->whereIn('nisn', $nisnArray)
                ->get()
                ->keyBy('nisn');

            $siswaIds = $siswaByNisn->pluck('id');
            $activeRecords = SiswaKelas::query()
                ->whereIn('siswa_id', $siswaIds)
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('status', 'aktif')
                ->get()
                ->keyBy('siswa_id');

            $assignable = collect();
            foreach ($nisnArray as $nisn) {
                $siswa = $siswaByNisn->get($nisn);

                if (!$siswa) {
                    $errors[] = [
                        'nisn' => $nisn,
                        'error' => 'NISN tidak ditemukan'
                    ];
                    continue;
                }

                $activeRecord = $activeRecords->get($siswa->id);
                if ($activeRecord && $activeRecord->kelas_id && $activeRecord->kelas_id !== $kelas->id) {
                    $errors[] = [
                        'nisn' => $nisn,
                        'error' => 'Siswa sudah terdaftar di kelas lain'
                    ];
                    continue;
                }

                if ($activeRecord && $activeRecord->kelas_id === $kelas->id) {
                    $errors[] = [
                        'nisn' => $nisn,
                        'error' => 'Siswa sudah terdaftar di kelas ini'
                    ];
                    continue;
                }

                $assignable->push([
                    'nisn' => $nisn,
                    'siswa' => $siswa,
                    'active_record' => $activeRecord,
                ]);
            }

            $currentCount = SiswaKelas::query()
                ->where('kelas_id', $kelas->id)
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('status', 'aktif')
                ->count();
            if (($currentCount + $assignable->count()) > $kelas->kapasitas) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Kapasitas kelas tidak mencukupi. Sisa tempat: ' . max(0, $kelas->kapasitas - $currentCount) . ', siswa yang bisa ditambahkan: ' . $assignable->count()
                ], 422);
            }

            $lastAbsen = SiswaKelas::query()
                ->where('kelas_id', $kelas->id)
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->max('nomor_urut_absen') ?? 0;

            $now = now();
            $insertRows = [];
            $successSiswaIds = [];

            foreach ($assignable as $row) {
                $siswa = $row['siswa'];
                $activeRecord = $row['active_record'];
                $nextAbsen = ++$lastAbsen;

                if ($activeRecord) {
                    $activeRecord->update([
                        'kelas_id' => $kelas->id,
                        'tingkat' => $kelas->tingkat,
                        'nomor_urut_absen' => $nextAbsen,
                        'catatan_perpindahan' => trim(($activeRecord->catatan_perpindahan ? $activeRecord->catatan_perpindahan . ' ' : '') . 'Ditempatkan ke rombel ' . $kelas->nama_kelas . '.'),
                        'keberadaan_diverifikasi_at' => null,
                        'keberadaan_diverifikasi_by' => null,
                    ]);
                } else {
                    $insertRows[] = [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelas->id,
                        'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                        'tingkat' => $kelas->tingkat,
                        'tanggal_masuk' => $tanggalMasuk,
                        'status' => 'aktif',
                        'nomor_urut_absen' => $nextAbsen,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $successSiswaIds[] = $siswa->id;
                $successCount++;
            }

            if (!empty($insertRows)) {
                DB::table('siswa_kelas')->insert($insertRows);
            }

            if (!empty($successSiswaIds)) {
                Siswa::whereIn('id', $successSiswaIds)->update([
                    'kelas_saat_ini_id' => $kelas->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_lengkap,
                    'jumlah_siswa' => $successCount,
                    'total_nisn' => $nisnArray->count(),
                    'gagal' => count($errors),
                ])
                ->log('Bulk import siswa ke kelas: ' . $kelas->nama_lengkap . ' - Berhasil: ' . $successCount . ', Gagal: ' . count($errors));

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
        DB::beginTransaction();
        try {
            $siswaName = $siswa->nama_lengkap;

            // Langsung keluarkan dari kelas (tanpa pilihan status/tanggal)
            // Siswa bisa langsung di-assign ke kelas lain setelah ini
            $enrollment = SiswaKelas::query()
                ->where('siswa_id', $siswa->id)
                ->where('kelas_id', $kelas->id)
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->firstOrFail();

            $enrollment->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'keluar',
                'ketua_kelas_selesai_at' => $enrollment->sedangMenjabatKetuaKelas()
                    ? now()
                    : $enrollment->ketua_kelas_selesai_at,
                'catatan_perpindahan' => 'Dikeluarkan dari kelas',
            ]);

            $this->syncSiswaCurrentClassFromPivot($siswa, $kelas->tahun_pelajaran_id);

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_lengkap,
                    'siswa_name' => $siswaName,
                    'siswa_nisn' => $siswa->nisn,
                    'tanggal_keluar' => now()->toDateString(),
                ])
                ->log('Mengeluarkan siswa ' . $siswaName . ' dari kelas: ' . $kelas->nama_lengkap);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dikeluarkan dari kelas.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer an active student to another class in the same academic period and level.
     */
    public function transferSiswa(Request $request, Kelas $kelas, Siswa $siswa)
    {
        $this->authorize('transfer-siswa-kelas');

        $validator = Validator::make($request->all(), [
            'target_kelas_id' => ['required', 'uuid', 'exists:kelas,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'target_kelas_id.required' => 'Pilih rombel tujuan.',
            'target_kelas_id.exists' => 'Rombel tujuan tidak ditemukan.',
            'reason.max' => 'Catatan perpindahan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $kelas, $siswa) {
                $sourceClass = Kelas::query()->lockForUpdate()->findOrFail($kelas->id);
                $targetClass = Kelas::query()->lockForUpdate()->findOrFail($request->target_kelas_id);

                if ($sourceClass->id === $targetClass->id) {
                    throw new \DomainException('Rombel tujuan harus berbeda dari rombel asal.');
                }

                if (
                    $targetClass->tahun_pelajaran_id !== $sourceClass->tahun_pelajaran_id
                    || (int) $targetClass->tingkat !== (int) $sourceClass->tingkat
                    || ! $targetClass->is_active
                ) {
                    throw new \DomainException('Rombel tujuan harus aktif, satu tingkat, dan berada pada tahun pelajaran yang sama.');
                }

                $sourceEnrollment = SiswaKelas::query()
                    ->where('siswa_id', $siswa->id)
                    ->where('kelas_id', $sourceClass->id)
                    ->where('tahun_pelajaran_id', $sourceClass->tahun_pelajaran_id)
                    ->where('status', 'aktif')
                    ->lockForUpdate()
                    ->first();

                if (! $sourceEnrollment) {
                    throw new \DomainException('Siswa tidak lagi tercatat aktif pada rombel asal. Muat ulang halaman.');
                }

                $targetCount = SiswaKelas::query()
                    ->where('kelas_id', $targetClass->id)
                    ->where('tahun_pelajaran_id', $targetClass->tahun_pelajaran_id)
                    ->where('status', 'aktif')
                    ->count();

                if ($targetCount >= $targetClass->kapasitas) {
                    throw new \DomainException("Rombel {$targetClass->nama_lengkap} sudah penuh.");
                }

                $reason = trim((string) $request->reason);
                $reasonText = $reason !== '' ? " Alasan: {$reason}" : '';
                $transferDate = now()->toDateString();
                $nextAttendanceNumber = ((int) SiswaKelas::query()
                    ->where('kelas_id', $targetClass->id)
                    ->where('tahun_pelajaran_id', $targetClass->tahun_pelajaran_id)
                    ->where('status', 'aktif')
                    ->max('nomor_urut_absen')) + 1;

                $sourceEnrollment->update([
                    'status' => 'keluar',
                    'tanggal_keluar' => $transferDate,
                    'ketua_kelas_selesai_at' => $sourceEnrollment->sedangMenjabatKetuaKelas()
                        ? now()
                        : $sourceEnrollment->ketua_kelas_selesai_at,
                    'catatan_perpindahan' => trim(($sourceEnrollment->catatan_perpindahan ? $sourceEnrollment->catatan_perpindahan.' ' : '')."Pindah rombel ke {$targetClass->nama_lengkap}.{$reasonText}"),
                ]);

                $targetEnrollment = SiswaKelas::withTrashed()
                    ->where('siswa_id', $siswa->id)
                    ->where('kelas_id', $targetClass->id)
                    ->where('tahun_pelajaran_id', $targetClass->tahun_pelajaran_id)
                    ->lockForUpdate()
                    ->first();

                $targetPayload = [
                    'tingkat' => $targetClass->tingkat,
                    'tanggal_masuk' => $transferDate,
                    'tanggal_keluar' => null,
                    'status' => 'aktif',
                    'nomor_urut_absen' => $nextAttendanceNumber,
                    'catatan_perpindahan' => "Pindah rombel dari {$sourceClass->nama_lengkap}.{$reasonText}",
                    'keberadaan_diverifikasi_at' => null,
                    'keberadaan_diverifikasi_by' => null,
                ];

                if ($targetEnrollment) {
                    if ($targetEnrollment->trashed()) {
                        $targetEnrollment->restore();
                    }
                    $targetEnrollment->update($targetPayload);
                } else {
                    $targetEnrollment = SiswaKelas::create($targetPayload + [
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $targetClass->id,
                        'tahun_pelajaran_id' => $targetClass->tahun_pelajaran_id,
                    ]);
                }

                $siswa->update(['kelas_saat_ini_id' => $targetClass->id]);

                activity()
                    ->performedOn($siswa)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'tahun_pelajaran_id' => $sourceClass->tahun_pelajaran_id,
                        'kelas_asal' => ['id' => $sourceClass->id, 'nama' => $sourceClass->nama_lengkap],
                        'kelas_tujuan' => ['id' => $targetClass->id, 'nama' => $targetClass->nama_lengkap],
                        'tanggal_pindah' => $transferDate,
                        'nomor_absen_baru' => $nextAttendanceNumber,
                        'alasan' => $reason ?: null,
                        'pelaksana_id' => Auth::id(),
                    ])
                    ->log("Memindahkan siswa {$siswa->nama_lengkap} dari {$sourceClass->nama_lengkap} ke {$targetClass->nama_lengkap}");

                return [
                    'message' => "{$siswa->nama_lengkap} berhasil dipindahkan ke {$targetClass->nama_lengkap}.",
                    'redirect' => route('admin.kelas.show', $targetClass),
                ];
            });

            return response()->json(['success' => true] + $result);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Gagal memindahkan rombel siswa', [
                'siswa_id' => $siswa->id,
                'kelas_asal_id' => $kelas->id,
                'kelas_tujuan_id' => $request->target_kelas_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan siswa. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Toggle hasil verifikasi keberadaan fisik siswa pada rombel aktif.
     */
    public function toggleKeberadaanSiswa(Kelas $kelas, Siswa $siswa)
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
        $this->authorize('edit-kelas');

        $enrollment = SiswaKelas::query()
            ->where('siswa_id', $siswa->id)
            ->where('kelas_id', $kelas->id)
            ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->where('status', 'aktif')
            ->first();

        if (! $enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak lagi tercatat aktif pada rombel ini. Muat ulang halaman.',
            ], 422);
        }

        $wasVerified = $enrollment->keberadaan_diverifikasi_at !== null;
        $enrollment->update([
            'keberadaan_diverifikasi_at' => $wasVerified ? null : now(),
            'keberadaan_diverifikasi_by' => $wasVerified ? null : Auth::id(),
        ]);

        activity()
            ->performedOn($siswa)
            ->causedBy(Auth::user())
            ->withProperties([
                'kelas_id' => $kelas->id,
                'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                'keberadaan_terverifikasi' => ! $wasVerified,
            ])
            ->log(
                $wasVerified
                    ? "Membatalkan verifikasi keberadaan {$siswa->nama_lengkap} di {$kelas->nama_lengkap}"
                    : "Memverifikasi keberadaan {$siswa->nama_lengkap} di {$kelas->nama_lengkap}"
            );

        return response()->json([
            'success' => true,
            'keberadaan_terverifikasi' => ! $wasVerified,
            'verified_at' => $enrollment->keberadaan_diverifikasi_at?->format('d/m/Y H:i'),
            'message' => $wasVerified
                ? 'Verifikasi keberadaan dibatalkan.'
                : "{$siswa->nama_lengkap} ditandai ada di rombel.",
        ]);
    }

    /**
     * Mark all active students in the class as physically verified.
     */
    public function verifikasiKeberadaanSemua(Kelas $kelas)
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
        $this->authorize('edit-kelas');

        $verifiedAt = now();
        $verifiedCount = DB::transaction(function () use ($kelas, $verifiedAt): int {
            return SiswaKelas::query()
                ->where('kelas_id', $kelas->id)
                ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('status', 'aktif')
                ->whereNull('keberadaan_diverifikasi_at')
                ->update([
                    'keberadaan_diverifikasi_at' => $verifiedAt,
                    'keberadaan_diverifikasi_by' => Auth::id(),
                ]);
        });

        activity()
            ->performedOn($kelas)
            ->causedBy(Auth::user())
            ->withProperties([
                'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                'jumlah_diverifikasi' => $verifiedCount,
                'diverifikasi_at' => $verifiedAt->toDateTimeString(),
            ])
            ->log("Memverifikasi keberadaan {$verifiedCount} siswa di {$kelas->nama_lengkap}");

        return response()->json([
            'success' => true,
            'verified_count' => $verifiedCount,
            'message' => $verifiedCount > 0
                ? "{$verifiedCount} siswa berhasil ditandai ada di rombel."
                : 'Semua siswa aktif sudah diverifikasi keberadaannya.',
        ]);
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

        $waliKelas = User::query()
            ->whereKey($request->wali_kelas_id)
            ->where('is_active', true)
            ->whereHas('gtk', function ($query) {
                $query->where('kategori_ptk', 'Pendidik')
                    ->whereIn('jenis_ptk', ['Guru Mapel', 'Guru BK']);
            })
            ->first();

        if (!$waliKelas) {
            return response()->json([
                'success' => false,
                'message' => 'Wali kelas harus dipilih dari GTK aktif yang berstatus guru.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldWaliKelasId = $kelas->wali_kelas_id;
            $oldWaliKelasName = $kelas->waliKelas->name ?? 'Tidak ada';
            
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

            // Log activity
            activity()
                ->performedOn($kelas)
                ->causedBy(Auth::user())
                ->withProperties([
                    'kode_kelas' => $kelas->kode_kelas,
                    'nama_kelas' => $kelas->nama_lengkap,
                    'old_wali_kelas' => [
                        'id' => $oldWaliKelasId,
                        'name' => $oldWaliKelasName,
                    ],
                    'new_wali_kelas' => [
                        'id' => $waliKelas->id,
                        'name' => $waliKelas->name,
                    ],
                ])
                ->log('Assign wali kelas: ' . $waliKelas->name . ' ke kelas ' . $kelas->nama_lengkap . ' (sebelumnya: ' . $oldWaliKelasName . ')');

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
     * Toggle tanda Rombel Asrama (Kampus 2) dari halaman detail kelas.
     */
    public function toggleAsrama(Request $request, Kelas $kelas)
    {
        $this->authorize('edit-kelas');

        $kelas->update(['is_asrama' => $request->boolean('is_asrama')]);

        activity()
            ->performedOn($kelas)
            ->causedBy(Auth::user())
            ->log(($kelas->is_asrama ? 'Menandai' : 'Menghapus tanda').' Rombel Asrama: '.$kelas->nama_kelas);

        return back()->with('success', $kelas->is_asrama
            ? 'Rombel '.$kelas->nama_kelas.' ditandai sebagai Rombel Asrama (Kampus 2).'
            : 'Tanda Rombel Asrama dihapus dari '.$kelas->nama_kelas.'.');
    }

    /**
     * Tetapkan atau kosongkan ketua kelas dari siswa aktif pada rombel ini.
     */
    public function assignKetuaKelas(Request $request, Kelas $kelas)
    {
        $this->authorize('edit-kelas');

        $validated = $request->validate([
            'ketua_kelas_id' => 'nullable|uuid|exists:siswa,id',
        ]);

        try {
            $result = DB::transaction(function () use ($validated, $kelas) {
                $records = SiswaKelas::query()
                    ->with('siswa')
                    ->where('kelas_id', $kelas->id)
                    ->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->where('status', 'aktif')
                    ->lockForUpdate()
                    ->get();

                $current = $records->first(
                    fn (SiswaKelas $record) => $record->sedangMenjabatKetuaKelas()
                );
                $selectedId = $validated['ketua_kelas_id'] ?? null;
                $selected = filled($selectedId)
                    ? $records->firstWhere('siswa_id', $selectedId)
                    : null;

                if (filled($selectedId) && ! $selected) {
                    throw new \DomainException('Ketua kelas harus dipilih dari siswa yang aktif pada rombel ini.');
                }

                if ($current && $selected && $current->is($selected)) {
                    return [
                        'message' => "{$selected->siswa->nama_lengkap} sudah menjadi Ketua Kelas {$kelas->nama_lengkap}.",
                        'name' => $selected->siswa->nama_lengkap,
                    ];
                }

                if ($current) {
                    $current->update(['ketua_kelas_selesai_at' => now()]);

                    ActivityLogService::log([
                        'activity_type' => 'selesai_jabatan_ketua_kelas',
                        'model_type' => Siswa::class,
                        'model_id' => $current->siswa_id,
                        'description' => "Mengakhiri jabatan Ketua Kelas {$kelas->nama_lengkap}",
                        'properties' => [
                            'kelas_id' => $kelas->id,
                            'kelas' => $kelas->nama_lengkap,
                            'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                            'mulai_at' => $current->ketua_kelas_mulai_at?->toIso8601String(),
                            'selesai_at' => $current->ketua_kelas_selesai_at?->toIso8601String(),
                        ],
                    ]);
                }

                if (! $selected) {
                    return [
                        'message' => "Penugasan Ketua Kelas {$kelas->nama_lengkap} berhasil dikosongkan.",
                        'name' => null,
                    ];
                }

                $selected->update([
                    'is_ketua_kelas' => true,
                    'ketua_kelas_mulai_at' => now(),
                    'ketua_kelas_selesai_at' => null,
                    'ketua_kelas_ditetapkan_by' => Auth::id(),
                ]);

                ActivityLogService::log([
                    'activity_type' => 'penetapan_ketua_kelas',
                    'model_type' => Siswa::class,
                    'model_id' => $selected->siswa_id,
                    'description' => "Ditetapkan sebagai Ketua Kelas {$kelas->nama_lengkap}",
                    'properties' => [
                        'kelas_id' => $kelas->id,
                        'kelas' => $kelas->nama_lengkap,
                        'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                        'mulai_at' => $selected->ketua_kelas_mulai_at?->toIso8601String(),
                        'ditetapkan_by' => Auth::id(),
                    ],
                ]);

                activity()
                    ->performedOn($kelas)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'ketua_kelas_lama' => $current?->siswa?->nama_lengkap,
                        'ketua_kelas_baru' => $selected->siswa->nama_lengkap,
                        'siswa_id' => $selected->siswa_id,
                        'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                    ])
                    ->log("Menetapkan {$selected->siswa->nama_lengkap} sebagai Ketua Kelas {$kelas->nama_lengkap}");

                return [
                    'message' => "{$selected->siswa->nama_lengkap} berhasil ditetapkan sebagai Ketua Kelas.",
                    'name' => $selected->siswa->nama_lengkap,
                ];
            });

            return response()->json(['success' => true] + $result);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Gagal menetapkan ketua kelas', [
                'kelas_id' => $kelas->id,
                'siswa_id' => $validated['ketua_kelas_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menetapkan Ketua Kelas. Silakan coba lagi.',
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
                    'ketua_kelas_selesai_at' => $siswa->pivot->is_ketua_kelas
                        ? now()
                        : $siswa->pivot->ketua_kelas_selesai_at,
                    'catatan_perpindahan' => 'Pengosongan Kelas: ' . $alasan,
                ]);

                $this->syncSiswaCurrentClassFromPivot($siswa, $kelas->tahun_pelajaran_id);
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

    /**
     * Cetak Absensi Kelas
     */
    public function cetakAbsensi(Kelas $kelas)
    {
        $this->authorize('view-kelas');
        $user = auth()->user();

        if (
            $user &&
            $user->hasRole('Wali Kelas') &&
            !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']) &&
            $kelas->wali_kelas_id !== $user->id
        ) {
            abort(403, 'Anda hanya dapat mencetak absensi untuk kelas yang Anda ampu.');
        }
        
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '256M');
        
        // Load relasi yang dibutuhkan
        $kelas->load([
            'tahunPelajaran',
            'kurikulum',
            'jurusan',
            'waliKelas',
            'siswas' => function($query) use ($kelas) {
                $query->wherePivot('status', 'aktif')
                      ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                      ->orderBy('nama_lengkap');
            }
        ]);
        
        // Load app settings untuk kop surat
        $setting = \App\Models\AppSetting::first();
        
        // Convert logo to base64 to avoid memory issues
        $logoKemenagBase64 = null;
        $logoSekolahBase64 = null;
        
        if ($setting && $setting->logo_kemenag_path) {
            $logoPath = storage_path('app/public/' . $setting->logo_kemenag_path);
            if (file_exists($logoPath)) {
                // Resize image to reduce memory
                $image = imagecreatefromstring(file_get_contents($logoPath));
                if ($image !== false) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $newHeight = $setting->logo_kemenag_height ?? 100; // Dari setting atau default 100
                    $newWidth = ($width / $height) * $newHeight;
                    
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    ob_start();
                    imagepng($resized, null, 6);
                    $imageData = ob_get_clean();
                    $logoKemenagBase64 = 'data:image/png;base64,' . base64_encode($imageData);
                    
                    imagedestroy($image);
                    imagedestroy($resized);
                }
            }
        }
        
        if ($setting && $setting->logo_sekolah_path) {
            $logoPath = storage_path('app/public/' . $setting->logo_sekolah_path);
            if (file_exists($logoPath)) {
                // Resize image to reduce memory
                $image = imagecreatefromstring(file_get_contents($logoPath));
                if ($image !== false) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $newHeight = $setting->logo_sekolah_height ?? 100; // Dari setting atau default 100
                    $newWidth = ($width / $height) * $newHeight;
                    
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    ob_start();
                    imagepng($resized, null, 6);
                    $imageData = ob_get_clean();
                    $logoSekolahBase64 = 'data:image/png;base64,' . base64_encode($imageData);
                    
                    imagedestroy($image);
                    imagedestroy($resized);
                }
            }
        }
        
        $data = [
            'kelas' => $kelas,
            'setting' => $setting,
            'logoKemenagBase64' => $logoKemenagBase64,
            'logoSekolahBase64' => $logoSekolahBase64,
        ];
        
        // Generate PDF
        $pdf = \PDF::loadView('admin.kelas.cetak-absensi', $data);
        $pdf->setPaper('legal', 'portrait'); // Legal Portrait: 8.5" x 14" (216mm x 356mm)
        
        return $pdf->stream('Absensi_' . $kelas->nama_lengkap . '.pdf');
    }

    private function syncSiswaCurrentClassFromPivot(Siswa $siswa, ?string $tahunPelajaranId = null): void
    {
        $nextKelasId = SiswaKelas::query()
            ->where('siswa_id', $siswa->id)
            ->whereNull('deleted_at')
            ->where('status', 'aktif')
            ->when($tahunPelajaranId, function ($query) use ($tahunPelajaranId) {
                $query->where('tahun_pelajaran_id', $tahunPelajaranId);
            })
            ->latest('created_at')
            ->value('kelas_id');

        $siswa->update([
            'kelas_saat_ini_id' => $nextKelasId,
        ]);
    }
}
