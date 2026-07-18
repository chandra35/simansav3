<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Services\SekolahDataEnrichmentService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SekolahAsalController extends Controller
{
    /**
     * Display a listing of sekolah asal with siswa count
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            
            // Load GTK with kelas relation if user is GTK
            $isWaliKelas = false;
            if ($user->hasRole('GTK') && $user->gtk) {
                $user->gtk->load('kelas.siswaAktif');
                $isWaliKelas = $user->gtk->kelas !== null;
            }
            
            if ($isWaliKelas) {
                // GTK Wali Kelas: only show sekolah from their class students
                $siswaKelasIds = $user->gtk->kelas->siswaAktif->pluck('id');
                
                $sekolah = Sekolah::query()
                    ->whereHas('siswa', function ($query) use ($siswaKelasIds) {
                        $query->whereIn('id', $siswaKelasIds);
                    })
                    ->withCount(['siswa' => function ($query) use ($siswaKelasIds) {
                        // Count only students in their class
                        $query->whereIn('id', $siswaKelasIds);
                    }])
                    ->orderBy('siswa_count', 'desc');
            } else {
                // Admin/Super Admin: show all sekolah
                $sekolah = Sekolah::query()
                    ->withCount('siswa')
                    ->orderBy('siswa_count', 'desc');
            }

            return DataTables::of($sekolah)
                ->addIndexColumn()
                ->addColumn('identity', function ($row) {
                    return '
                        <div class="school-name">' . e($row->nama) . '</div>
                        <div class="school-meta">
                            NPSN: ' . e($row->npsn) . ' <span>|</span> NSM: ' . e($row->nsm ?: '-') . '
                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.sekolah-asal.show', $row->npsn);
                    $syncUrl = route('admin.sekolah-asal.enrich', $row->npsn);

                    return '
                        <div class="btn-group btn-group-sm">
                            <button type="button"
                                class="btn btn-primary btn-enrich-school"
                                data-url="' . e($syncUrl) . '"
                                data-npsn="' . e($row->npsn) . '"
                                data-school="' . e($row->nama) . '"
                                title="Lengkapi data sekolah">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="' . e($url) . '" class="btn btn-outline-primary" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    ';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = $row->status === 'NEGERI' ? 'primary' : 'success';
                    $status = $row->status ?? '-';
                    return '<span class="badge badge-' . $color . '">' . 
                           $status . '</span>';
                })
                ->addColumn('siswa_count_badge', function ($row) {
                    $count = $row->siswa_count;
                    $color = 'secondary';
                    if ($count > 50) {
                        $color = 'success';
                    } elseif ($count > 20) {
                        $color = 'primary';
                    } elseif ($count > 0) {
                        $color = 'info';
                    }
                    
                    $badge = '<span class="badge badge-' . $color . 
                             ' badge-pill">' . $count . ' siswa</span>';
                    return $badge;
                })
                ->addColumn('wilayah', function ($row) {
                    return collect([$row->kabupaten_kota, $row->provinsi])->filter()->implode(', ')
                        ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('kelengkapan_badge', function ($row) {
                    $fields = [
                        'nama',
                        'status',
                        'bentuk_pendidikan',
                        'alamat_jalan',
                        'desa_kelurahan',
                        'kecamatan',
                        'kabupaten_kota',
                        'provinsi',
                    ];
                    $filled = collect($fields)->filter(fn ($field) => filled($row->{$field}))->count();
                    $percent = (int) round(($filled / count($fields)) * 100);
                    $color = $percent >= 90 ? 'success' : ($percent >= 60 ? 'info' : 'warning');

                    return '
                        <span class="badge badge-' . $color . ' badge-pill">' . $percent . '%</span>
                        <div class="school-meta mt-1">' . ($row->last_fetched_at ? 'Update ' . $row->last_fetched_at->format('d/m/Y') : 'Belum dicek') . '</div>
                    ';
                })
                ->editColumn('bentuk_pendidikan', fn ($row) => $row->bentuk_pendidikan ?: '-')
                ->editColumn('nsm', fn ($row) => $row->nsm ?: '-')
                ->filterColumn('identity', function ($query, $keyword) {
                    $query->where(function ($schoolQuery) use ($keyword) {
                        $schoolQuery->where('nama', 'like', "%{$keyword}%")
                            ->orWhere('npsn', 'like', "%{$keyword}%")
                            ->orWhere('nsm', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['identity', 'action', 'status_badge', 'siswa_count_badge', 'wilayah', 'kelengkapan_badge'])
                ->make(true);
        }

        $stats = [
            'total' => Sekolah::count(),
            'lengkap' => Sekolah::whereNotNull('alamat_jalan')
                ->whereNotNull('kecamatan')
                ->whereNotNull('kabupaten_kota')
                ->whereNotNull('provinsi')
                ->count(),
            'nsm' => Sekolah::whereNotNull('nsm')->count(),
            'perlu_update' => Sekolah::whereNull('last_fetched_at')->count(),
        ];

        return view('admin.sekolah-asal.index', compact('stats'));
    }

    public function enrich(Request $request, Sekolah $sekolah, SekolahDataEnrichmentService $service)
    {
        $this->authorize('edit-siswa');

        $result = $service->enrich($sekolah);

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Data sekolah belum berhasil dilengkapi.',
                'warnings' => $result['warnings'] ?? [],
            ], 422);
        }

        $sekolah = $result['data'];

        return response()->json([
            'success' => true,
            'complete' => $result['complete'] ?? true,
            'partial' => $result['partial'] ?? false,
            'message' => $result['message'],
            'sources' => $result['sources'] ?? [],
            'warnings' => $result['warnings'] ?? [],
            'data' => [
                'npsn' => $sekolah->npsn,
                'nsm' => $sekolah->nsm,
                'nama' => $sekolah->nama,
                'bentuk_pendidikan' => $sekolah->bentuk_pendidikan,
                'wilayah' => collect([$sekolah->kabupaten_kota, $sekolah->provinsi])->filter()->implode(', '),
                'last_fetched_at' => optional($sekolah->last_fetched_at)->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Display the specified sekolah with all siswa from that school
     */
    public function show($npsn)
    {
        $user = auth()->user();
        
        // Load GTK with kelas relation if user is GTK
        $isWaliKelas = false;
        if ($user->hasRole('GTK') && $user->gtk) {
            $user->gtk->load('kelas.siswaAktif');
            $isWaliKelas = $user->gtk->kelas !== null;
        }
        
        if ($isWaliKelas) {
            // GTK Wali Kelas: only show students from their class
            $siswaKelasIds = $user->gtk->kelas->siswaAktif->pluck('id');

            $sekolah = Sekolah::withCount(['siswa' => function ($query) use ($siswaKelasIds) {
                $query->whereIn('id', $siswaKelasIds);
            }])->findOrFail($npsn);

            $siswaBaseQuery = Siswa::query()
                ->where('npsn_asal_sekolah', $npsn)
                ->whereIn('id', $siswaKelasIds);

            // Get statistics only for their class
            $stats = [
                'total' => $sekolah->siswa_count,
                'aktif' => (clone $siswaBaseQuery)->where('status_siswa', 'aktif')->count(),
                'lulus' => (clone $siswaBaseQuery)->where('status_siswa', 'lulus')->count(),
                'keluar' => (clone $siswaBaseQuery)->whereIn('status_siswa', ['keluar', 'mutasi_keluar'])->count(),
                'laki' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'L')->count(),
                'perempuan' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'P')->count(),
            ];
        } else {
            // Admin: show all students, but keep detail page lightweight.
            $sekolah = Sekolah::withCount('siswa')->findOrFail($npsn);

            $siswaBaseQuery = Siswa::query()
                ->where('npsn_asal_sekolah', $npsn);

            // Get statistics for all students
            $stats = [
                'total' => $sekolah->siswa_count,
                'aktif' => (clone $siswaBaseQuery)->where('status_siswa', 'aktif')->count(),
                'lulus' => (clone $siswaBaseQuery)->where('status_siswa', 'lulus')->count(),
                'keluar' => (clone $siswaBaseQuery)->whereIn('status_siswa', ['keluar', 'mutasi_keluar'])->count(),
                'laki' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'L')->count(),
                'perempuan' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'P')->count(),
            ];
        }

        // Build class distribution with aggregation instead of loading all siswa into memory.
        $siswaPerKelas = (clone $siswaBaseQuery)
            ->leftJoin('kelas', 'siswa.kelas_saat_ini_id', '=', 'kelas.id')
            ->selectRaw("COALESCE(kelas.nama_kelas, 'Belum Ada Kelas') as nama_kelas, COUNT(*) as total_siswa")
            ->groupBy('nama_kelas')
            ->orderBy('nama_kelas')
            ->get()
            ->keyBy('nama_kelas')
            ->sortKeys();

        return view('admin.sekolah-asal.show', compact('sekolah', 'stats', 'siswaPerKelas'));
    }

    /**
     * Get siswa data for DataTables in detail view
     */
    public function getSiswaData($npsn)
    {
        $user = auth()->user();
        
        // Load GTK with kelas relation if user is GTK
        $isWaliKelas = false;
        if ($user->hasRole('GTK') && $user->gtk) {
            $user->gtk->load('kelas.siswaAktif');
            $isWaliKelas = $user->gtk->kelas !== null;
        }
        
        $query = Siswa::with(['kelasSaatIni.tahunPelajaran', 'user'])
            ->where('npsn_asal_sekolah', $npsn);
        
        if ($isWaliKelas) {
            // GTK Wali Kelas: only show students from their class
            $siswaKelasIds = $user->gtk->kelas->siswaAktif->pluck('id');
            $query->whereIn('id', $siswaKelasIds);
        }
        
        $siswa = $query->select('siswa.*');

        return DataTables::of($siswa)
            ->addIndexColumn()
            ->addColumn('kelas_saat_ini', function ($row) {
                if ($row->kelasSaatIni) {
                    $tahunPelajaran = $row->kelasSaatIni->tahunPelajaran->nama ?? null;
                    return $tahunPelajaran
                        ? $row->kelasSaatIni->nama_kelas . ' (' . $tahunPelajaran . ')'
                        : $row->kelasSaatIni->nama_kelas;
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('jenis_kelamin_badge', function ($row) {
                $color = $row->jenis_kelamin === 'L' ? 'primary' : 'danger';
                $text = $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                return '<span class="badge badge-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'aktif' => 'success',
                    'lulus' => 'primary',
                    'keluar' => 'warning',
                    'mutasi_keluar' => 'info',
                    'alumni' => 'secondary',
                ];
                $color = $badges[$row->status_siswa] ?? 'secondary';
                $status = ucfirst(str_replace('_', ' ', $row->status_siswa));
                return '<span class="badge badge-' . $color . '">' . $status . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button onclick="showSiswa(\'' . $row->id . '\')" 
                       class="btn btn-sm btn-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                ';
            })
            ->rawColumns(['kelas_saat_ini', 'jenis_kelamin_badge', 'status_badge', 'action'])
            ->make(true);
    }
}
