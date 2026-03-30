<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiswaLulusan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LulusanController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $selectedTahun = $this->resolveSelectedTahun($request, $tahunPelajaranList);

        return view('admin.lulusan.index', [
            'tahunPelajaranList' => $tahunPelajaranList,
            'selectedTahun' => $selectedTahun,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
        ]);
    }

    public function data(Request $request)
    {
        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return DataTables::of(collect())->make(true);
        }

        $query = $this->buildBaseQuery($request, $selectedTahun->id);

        return DataTables::queryBuilder($query)
            ->addColumn('status_badge', function ($row) {
                if ($row->is_filled) {
                    return '<span class="badge badge-success">Sudah Isi</span>';
                }

                return '<span class="badge badge-secondary">Belum Isi</span>';
            })
            ->addColumn('jalur_badge', function ($row) {
                if (!$row->jalur_masuk) {
                    return '<span class="text-muted">-</span>';
                }

                $colors = [
                    'SNBP' => 'primary',
                    'SNBT' => 'info',
                    'SPAN-PTKIN' => 'success',
                    'Poltekkes' => 'warning',
                    'Lainnya' => 'secondary',
                ];

                $color = $colors[$row->jalur_masuk] ?? 'secondary';

                return '<span class="badge badge-' . $color . '">' . e($row->jalur_masuk) . '</span>';
            })
            ->editColumn('nama_universitas', fn ($row) => $row->nama_universitas ?: '-')
            ->editColumn('jurusan_fakultas', fn ($row) => $row->jurusan_fakultas ?: '-')
            ->editColumn('program_studi', fn ($row) => $row->program_studi ?: '-')
            ->rawColumns(['status_badge', 'jalur_badge'])
            ->make(true);
    }

    public function stats(Request $request)
    {
        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return response()->json([
                'summary' => [
                    'total' => 0,
                    'sudah_isi' => 0,
                    'belum_isi' => 0,
                    'total_universitas' => 0,
                ],
                'per_jalur' => [],
                'per_kelas' => [],
                'top_universitas' => [],
            ]);
        }

        $rows = collect(
            $this->buildBaseQuery($request, $selectedTahun->id)
                ->orderBy('kelas_nama')
                ->orderBy('nama_lengkap')
                ->get()
        );

        $summary = [
            'total' => $rows->count(),
            'sudah_isi' => $rows->where('is_filled', 1)->count(),
            'belum_isi' => $rows->where('is_filled', 0)->count(),
            'total_universitas' => $rows->whereNotNull('nama_universitas')->pluck('nama_universitas')->filter()->unique()->count(),
        ];

        $perJalur = collect(SiswaLulusan::JALUR_MASUK)
            ->mapWithKeys(fn (string $jalur) => [$jalur => $rows->where('jalur_masuk', $jalur)->count()])
            ->all();

        $perKelas = $rows
            ->groupBy('kelas_nama')
            ->map(function ($kelasRows, $kelasNama) use ($perJalur) {
                $jalur = [];
                foreach (array_keys($perJalur) as $jalurMasuk) {
                    $jalur[$jalurMasuk] = $kelasRows->where('jalur_masuk', $jalurMasuk)->count();
                }

                return [
                    'kelas_nama' => $kelasNama,
                    'total' => $kelasRows->count(),
                    'sudah_isi' => $kelasRows->where('is_filled', 1)->count(),
                    'belum_isi' => $kelasRows->where('is_filled', 0)->count(),
                    'jalur' => $jalur,
                ];
            })
            ->values()
            ->all();

        $topUniversitas = $rows
            ->where('is_filled', 1)
            ->filter(fn ($row) => filled($row->nama_universitas))
            ->groupBy('nama_universitas')
            ->map(function ($group, $universitas) {
                return [
                    'nama_universitas' => $universitas,
                    'jumlah' => $group->count(),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values()
            ->all();

        return response()->json([
            'summary' => $summary,
            'per_jalur' => $perJalur,
            'per_kelas' => $perKelas,
            'top_universitas' => $topUniversitas,
        ]);
    }

    private function resolveSelectedTahun(Request $request, $tahunPelajaranList = null): ?TahunPelajaran
    {
        $tahunPelajaranList ??= TahunPelajaran::orderByDesc('tahun_mulai')->get();

        $selectedTahun = $request->filled('tahun_pelajaran_id')
            ? $tahunPelajaranList->firstWhere('id', $request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        return $selectedTahun ?: $tahunPelajaranList->first();
    }

    private function buildBaseQuery(Request $request, string $tahunPelajaranId)
    {
        $query = DB::table('siswa_kelas')
            ->join('siswa', 'siswa.id', '=', 'siswa_kelas.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->leftJoin('siswa_lulusan', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_lulusan.siswa_id', '=', 'siswa.id')
                    ->where('siswa_lulusan.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereNull('siswa_lulusan.deleted_at');
            })
            ->where('siswa_kelas.tahun_pelajaran_id', $tahunPelajaranId)
            ->whereNull('siswa_kelas.deleted_at')
            ->where('kelas.tingkat', 12)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'kelas.nama_kelas as kelas_nama',
                'siswa_lulusan.jalur_masuk',
                'siswa_lulusan.nama_universitas',
                'siswa_lulusan.jurusan_fakultas',
                'siswa_lulusan.program_studi',
                DB::raw('CASE WHEN siswa_lulusan.id IS NULL THEN 0 ELSE 1 END as is_filled'),
            ]);

        if ($request->filled('status_pengisian')) {
            if ($request->status_pengisian === 'sudah_isi') {
                $query->whereNotNull('siswa_lulusan.id');
            }

            if ($request->status_pengisian === 'belum_isi') {
                $query->whereNull('siswa_lulusan.id');
            }
        }

        if ($request->filled('jalur_masuk')) {
            $query->where('siswa_lulusan.jalur_masuk', $request->jalur_masuk);
        }

        if ($request->filled('kelas_nama')) {
            $query->where('kelas.nama_kelas', $request->kelas_nama);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('siswa.nisn', 'like', "%{$search}%")
                    ->orWhere('siswa.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kelas.nama_kelas', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.nama_universitas', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.jurusan_fakultas', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.program_studi', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
