<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Services\SekolahDataEnrichmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;

class SekolahAsalController extends Controller
{
    public function index(Request $request)
    {
        $classIds = $this->waliClassIds();
        $isWaliScope = $classIds !== null;
        $canEnrich = !$isWaliScope && auth()->user()->can('edit-siswa');
        $schoolQuery = $this->schoolQuery($classIds);

        if ($request->ajax()) {
            return DataTables::of($schoolQuery->orderBy('siswa_count', 'desc'))
                ->addIndexColumn()
                ->addColumn('identity', function ($row) {
                    return '
                        <div class="school-name">' . e($row->nama) . '</div>
                        <div class="school-meta">NPSN: ' . e($row->npsn) . ' <span>|</span> NSM: ' . e($row->nsm ?: '-') . '</div>
                    ';
                })
                ->addColumn('action', function ($row) use ($canEnrich) {
                    $items = [];

                    if ($canEnrich) {
                        $items[] = '<button type="button" class="dropdown-item simansa-school-action-item btn-enrich-school"
                            data-url="' . e(route('admin.sekolah-asal.enrich', $row->npsn)) . '"
                            data-npsn="' . e($row->npsn) . '" data-school="' . e($row->nama) . '">
                            <i class="fas fa-sync-alt text-primary"></i><span>Lengkapi data</span></button>';
                    }

                    $items[] = '<a href="' . e(route('admin.sekolah-asal.show', $row->npsn)) . '" class="dropdown-item simansa-school-action-item">
                        <i class="fas fa-eye text-info"></i><span>Lihat detail</span></a>';

                    return '<div class="btn-group simansa-school-action-menu">'
                        . '<button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle simansa-school-action-toggle"'
                        . ' title="Pilih aksi untuk ' . e($row->nama) . '" aria-haspopup="true" aria-expanded="false">'
                        . '<i class="fas fa-ellipsis-v mr-1"></i>Aksi</button>'
                        . '<div class="dropdown-menu dropdown-menu-right simansa-school-action-dropdown">'
                        . implode('', $items)
                        . '</div></div>';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = $row->status === 'NEGERI' ? 'primary' : 'success';
                    return '<span class="badge badge-' . $color . '">' . e($row->status ?? '-') . '</span>';
                })
                ->addColumn('siswa_count_badge', function ($row) {
                    $count = $row->siswa_count;
                    $color = $count > 50 ? 'success' : ($count > 20 ? 'primary' : ($count > 0 ? 'info' : 'secondary'));
                    return '<span class="badge badge-' . $color . ' badge-pill">' . $count . ' siswa</span>';
                })
                ->addColumn('wilayah', fn ($row) => collect([$row->kabupaten_kota, $row->provinsi])->filter()->implode(', ') ?: '<span class="text-muted">-</span>')
                ->addColumn('kelengkapan_badge', function ($row) {
                    $fields = ['nama', 'status', 'bentuk_pendidikan', 'alamat_jalan', 'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi'];
                    $filled = collect($fields)->filter(fn ($field) => filled($row->{$field}))->count();
                    $percent = (int) round(($filled / count($fields)) * 100);
                    $color = $percent >= 90 ? 'success' : ($percent >= 60 ? 'info' : 'warning');

                    return '<span class="badge badge-' . $color . ' badge-pill">' . $percent . '%</span>
                        <div class="school-meta mt-1">' . ($row->last_fetched_at ? 'Update ' . $row->last_fetched_at->format('d/m/Y') : 'Belum dicek') . '</div>';
                })
                ->editColumn('bentuk_pendidikan', fn ($row) => $row->bentuk_pendidikan ?: '-')
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
            'total' => (clone $schoolQuery)->count(),
            'lengkap' => (clone $schoolQuery)->whereNotNull('alamat_jalan')->whereNotNull('kecamatan')->whereNotNull('kabupaten_kota')->whereNotNull('provinsi')->count(),
            'nsm' => (clone $schoolQuery)->whereNotNull('nsm')->count(),
            'perlu_update' => (clone $schoolQuery)->whereNull('last_fetched_at')->count(),
        ];

        return view('admin.sekolah-asal.index', compact('stats', 'isWaliScope', 'canEnrich'));
    }

    public function enrich(Request $request, Sekolah $sekolah, SekolahDataEnrichmentService $service)
    {
        abort_if($this->isWaliScopedUser(), 403, 'Akun GTK Wali Kelas hanya memiliki akses baca.');
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

    public function show($npsn)
    {
        $classIds = $this->waliClassIds();
        $isWaliScope = $classIds !== null;
        $canEnrich = !$isWaliScope && auth()->user()->can('edit-siswa');
        $siswaBaseQuery = $this->studentQuery($classIds)->where('npsn_asal_sekolah', $npsn);

        $sekolah = $this->schoolQuery($classIds)->whereKey($npsn)->firstOrFail();
        $stats = [
            'total' => (clone $siswaBaseQuery)->count(),
            'aktif' => (clone $siswaBaseQuery)->where('status_siswa', 'aktif')->count(),
            'lulus' => (clone $siswaBaseQuery)->where('status_siswa', 'lulus')->count(),
            'keluar' => (clone $siswaBaseQuery)->whereIn('status_siswa', ['keluar', 'mutasi_keluar'])->count(),
            'laki' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $siswaBaseQuery)->where('jenis_kelamin', 'P')->count(),
        ];

        $siswaPerKelas = (clone $siswaBaseQuery)
            ->leftJoin('kelas', 'siswa.kelas_saat_ini_id', '=', 'kelas.id')
            ->selectRaw("COALESCE(kelas.nama_kelas, 'Belum Ada Kelas') as nama_kelas, COUNT(*) as total_siswa")
            ->groupBy('nama_kelas')
            ->orderBy('nama_kelas')
            ->get()->keyBy('nama_kelas')->sortKeys();

        return view('admin.sekolah-asal.show', compact('sekolah', 'stats', 'siswaPerKelas', 'isWaliScope', 'canEnrich'));
    }

    public function getSiswaData($npsn)
    {
        $classIds = $this->waliClassIds();
        $isWaliScope = $classIds !== null;

        $this->schoolQuery($classIds)->whereKey($npsn)->firstOrFail();
        $siswa = $this->studentQuery($classIds)
            ->with(['kelasSaatIni.tahunPelajaran', 'user'])
            ->where('npsn_asal_sekolah', $npsn)
            ->select('siswa.*');

        return DataTables::of($siswa)
            ->addIndexColumn()
            ->addColumn('kelas_saat_ini', function ($row) {
                if (!$row->kelasSaatIni) return '<span class="text-muted">-</span>';
                $year = $row->kelasSaatIni->tahunPelajaran->nama ?? null;
                return $row->kelasSaatIni->nama_kelas . ($year ? " ({$year})" : '');
            })
            ->addColumn('jenis_kelamin_badge', function ($row) {
                $color = $row->jenis_kelamin === 'L' ? 'primary' : 'danger';
                $text = $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                return '<span class="badge badge-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $color = ['aktif' => 'success', 'lulus' => 'primary', 'keluar' => 'warning', 'mutasi_keluar' => 'info', 'alumni' => 'secondary'][$row->status_siswa] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . e(ucfirst(str_replace('_', ' ', $row->status_siswa))) . '</span>';
            })
            ->addColumn('action', function ($row) use ($isWaliScope) {
                if ($isWaliScope) {
                    return '<a href="' . e(route('admin.gtk.wali.siswa.show', $row->id)) . '" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>';
                }

                return '<button onclick="showSiswa(\'' . e($row->id) . '\')" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></button>';
            })
            ->rawColumns(['kelas_saat_ini', 'jenis_kelamin_badge', 'status_badge', 'action'])
            ->make(true);
    }

    private function schoolQuery(?Collection $classIds): Builder
    {
        $query = Sekolah::query();
        if ($classIds === null) return $query->withCount('siswa');

        return $query
            ->whereHas('siswa', fn ($students) => $this->applyClassScope($students, $classIds))
            ->withCount(['siswa' => fn ($students) => $this->applyClassScope($students, $classIds)]);
    }

    private function studentQuery(?Collection $classIds): Builder
    {
        $query = Siswa::query();
        return $classIds === null ? $query : $this->applyClassScope($query, $classIds);
    }

    private function applyClassScope(Builder $query, Collection $classIds): Builder
    {
        return $query->whereHas('kelasTahunAktif', fn ($classes) => $classes->whereIn('kelas.id', $classIds));
    }

    private function waliClassIds(): ?Collection
    {
        if (!$this->isWaliScopedUser()) return null;

        $classIds = auth()->user()->activeWaliKelasClasses()->pluck('id');
        abort_if($classIds->isEmpty(), 403, 'Anda bukan wali kelas aktif.');

        return $classIds;
    }

    private function isWaliScopedUser(): bool
    {
        $user = auth()->user();
        $isManager = $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])
            || in_array($user->role, ['super_admin', 'admin', 'operator'], true);

        return !$isManager && $user->hasAnyRole(['GTK', 'Wali Kelas']);
    }
}
