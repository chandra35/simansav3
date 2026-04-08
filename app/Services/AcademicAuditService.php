<?php

namespace App\Services;

use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\DB;

class AcademicAuditService
{
    public function run(): array
    {
        $tahunAktif = TahunPelajaran::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$tahunAktif) {
            return [
                'ok' => false,
                'message' => 'Tidak ada tahun pelajaran aktif.',
                'tahun_pelajaran_aktif' => null,
                'checks' => [],
                'samples' => [],
                'summary' => [
                    'total_checks' => 0,
                    'issues' => 0,
                    'warnings' => 0,
                    'healthy_checks' => 0,
                ],
            ];
        }

        $audit = $this->buildAudit($tahunAktif->id, $tahunAktif->nama);
        $checks = collect($audit['checks']);
        $issues = $checks->where('count', '>', 0)->count();

        return array_merge($audit, [
            'ok' => true,
            'message' => $issues === 0
                ? 'Tidak ditemukan masalah prioritas tinggi.'
                : 'Ada data yang perlu dibersihkan sebelum rollover tahun ajaran.',
            'summary' => [
                'total_checks' => $checks->count(),
                'issues' => $issues,
                'warnings' => collect($audit['samples'])->filter(fn ($rows) => !empty($rows))->count(),
                'healthy_checks' => $checks->where('count', 0)->count(),
            ],
        ]);
    }

    private function buildAudit(string $tahunAktifId, string $tahunAktifNama): array
    {
        $missingMapelBySemester = $this->findMissingConfiguredMapelBySemester();

        $duplicateCurrentYear = DB::table('siswa_kelas')
            ->select('siswa_id', DB::raw('COUNT(*) as total'))
            ->where('tahun_pelajaran_id', $tahunAktifId)
            ->where('status', 'aktif')
            ->whereNull('deleted_at')
            ->groupBy('siswa_id')
            ->havingRaw('COUNT(*) > 1');

        $duplicateAllYears = DB::table('siswa_kelas')
            ->select('siswa_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'aktif')
            ->whereNull('deleted_at')
            ->groupBy('siswa_id')
            ->havingRaw('COUNT(*) > 1');

        $checks = [
            [
                'key' => 'duplicate_current_year',
                'label' => 'Siswa dengan >1 kelas aktif di tahun berjalan',
                'count' => (clone $duplicateCurrentYear)->count(),
            ],
            [
                'key' => 'active_without_current_class',
                'label' => 'Siswa aktif tanpa kelas aktif di tahun berjalan',
                'count' => DB::table('siswa')
                    ->whereNull('deleted_at')
                    ->where('status_siswa', 'aktif')
                    ->whereNotExists(function ($query) use ($tahunAktifId) {
                        $query->select(DB::raw(1))
                            ->from('siswa_kelas')
                            ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                            ->where('siswa_kelas.tahun_pelajaran_id', $tahunAktifId)
                            ->where('siswa_kelas.status', 'aktif')
                            ->whereNull('siswa_kelas.deleted_at');
                    })
                    ->count(),
            ],
            [
                'key' => 'kelas_cache_mismatch',
                'label' => 'Mismatch kelas_saat_ini_id vs pivot tahun berjalan',
                'count' => DB::table('siswa')
                    ->leftJoin('siswa_kelas', function ($join) use ($tahunAktifId) {
                        $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                            ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunAktifId)
                            ->where('siswa_kelas.status', '=', 'aktif')
                            ->whereNull('siswa_kelas.deleted_at');
                    })
                    ->whereNull('siswa.deleted_at')
                    ->whereNotNull('siswa.kelas_saat_ini_id')
                    ->whereNotNull('siswa_kelas.kelas_id')
                    ->whereColumn('siswa.kelas_saat_ini_id', '!=', 'siswa_kelas.kelas_id')
                    ->distinct('siswa.id')
                    ->count('siswa.id'),
            ],
            [
                'key' => 'inactive_with_active_class',
                'label' => 'Siswa nonaktif/lulus/mutasi yang masih punya kelas aktif',
                'count' => DB::table('siswa')
                    ->whereNull('deleted_at')
                    ->whereIn('status_siswa', ['lulus', 'mutasi_keluar', 'keluar', 'alumni'])
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('siswa_kelas')
                            ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                            ->where('siswa_kelas.status', 'aktif')
                            ->whereNull('siswa_kelas.deleted_at');
                    })
                    ->count(),
            ],
            [
                'key' => 'approved_mutasi_out_status_mismatch',
                'label' => 'Mutasi keluar approved tapi status siswa belum mutasi_keluar',
                'count' => DB::table('mutasi_siswa')
                    ->join('siswa', 'siswa.id', '=', 'mutasi_siswa.siswa_id')
                    ->where('mutasi_siswa.jenis_mutasi', 'keluar')
                    ->where('mutasi_siswa.status_verifikasi', 'approved')
                    ->where('siswa.status_siswa', '!=', 'mutasi_keluar')
                    ->count(),
            ],
            [
                'key' => 'mutasi_keluar_user_still_active',
                'label' => 'Siswa mutasi_keluar tapi user masih aktif',
                'count' => DB::table('siswa')
                    ->join('users', 'users.id', '=', 'siswa.user_id')
                    ->where('siswa.status_siswa', 'mutasi_keluar')
                    ->where('users.is_active', true)
                    ->count(),
            ],
            [
                'key' => 'active_class_outside_year',
                'label' => 'Kelas aktif siswa di luar tahun pelajaran aktif',
                'count' => DB::table('siswa_kelas')
                    ->where('status', 'aktif')
                    ->whereNull('deleted_at')
                    ->where('tahun_pelajaran_id', '!=', $tahunAktifId)
                    ->count(),
            ],
            [
                'key' => 'duplicate_all_years',
                'label' => 'Siswa dengan >1 kelas aktif lintas tahun',
                'count' => (clone $duplicateAllYears)->count(),
            ],
            [
                'key' => 'nilai_out_of_range_semester',
                'label' => 'Data nilai dengan semester di luar rentang 1-5',
                'count' => DB::table('nilai_siswa')
                    ->whereNull('deleted_at')
                    ->where(function ($query) {
                        $query->where('semester', '<', 1)
                            ->orWhere('semester', '>', 5);
                    })
                    ->count(),
            ],
            [
                'key' => 'nilai_missing_tahun_pelajaran',
                'label' => 'Data nilai tanpa tahun pelajaran',
                'count' => DB::table('nilai_siswa')
                    ->whereNull('deleted_at')
                    ->whereNull('tahun_pelajaran_id')
                    ->count(),
            ],
            [
                'key' => 'nilai_duplicate_keys',
                'label' => 'Duplikasi nilai per siswa-mapel-tahun-semester',
                'count' => DB::table('nilai_siswa')
                    ->select(
                        'siswa_id',
                        'mata_pelajaran_id',
                        'tahun_pelajaran_id',
                        'semester',
                        DB::raw('COUNT(*) as total')
                    )
                    ->whereNull('deleted_at')
                    ->groupBy('siswa_id', 'mata_pelajaran_id', 'tahun_pelajaran_id', 'semester')
                    ->havingRaw('COUNT(*) > 1')
                    ->get()
                    ->count(),
            ],
            [
                'key' => 'kelas12_without_sem5',
                'label' => 'Siswa aktif kelas 12 tanpa nilai semester 5',
                'count' => DB::table('siswa')
                    ->join('siswa_kelas', function ($join) use ($tahunAktifId) {
                        $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                            ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunAktifId)
                            ->where('siswa_kelas.status', '=', 'aktif')
                            ->whereNull('siswa_kelas.deleted_at');
                    })
                    ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
                    ->whereNull('siswa.deleted_at')
                    ->where('siswa.status_siswa', 'aktif')
                    ->where('kelas.tingkat', 12)
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('nilai_siswa')
                            ->whereColumn('nilai_siswa.siswa_id', 'siswa.id')
                            ->where('nilai_siswa.semester', 5)
                            ->whereNull('nilai_siswa.deleted_at');
                    })
                    ->count(),
            ],
            [
                'key' => 'nilai_missing_configured_mapel',
                'label' => 'Kode mapel di config semester yang belum muncul di data nilai',
                'count' => collect($missingMapelBySemester)->sum(fn (array $codes) => count($codes)),
            ],
        ];

        return [
            'tahun_pelajaran_aktif' => $tahunAktifNama,
            'checks' => $checks,
            'samples' => [
                'Duplikat kelas aktif tahun berjalan' => $this->sampleDuplicateRows($duplicateCurrentYear, $tahunAktifId),
                'Siswa aktif tanpa kelas tahun berjalan' => $this->sampleActiveWithoutCurrentClass($tahunAktifId),
                'Mismatch kelas_saat_ini_id' => $this->sampleCacheMismatch($tahunAktifId),
                'Siswa nonaktif dengan kelas aktif' => $this->sampleInactiveWithActiveClass(),
                'Mutasi keluar approved tapi status belum sinkron' => $this->sampleApprovedMutasiStatusMismatch(),
                'Mutasi keluar tapi user masih aktif' => $this->sampleMutasiKeluarUserActive(),
                'Siswa aktif kelas 12 tanpa nilai semester 5' => $this->sampleKelas12WithoutSemester5($tahunAktifId),
                'Kode mapel config yang belum muncul di data nilai' => $this->sampleMissingConfiguredMapel($missingMapelBySemester),
            ],
        ];
    }

    private function sampleDuplicateRows($baseQuery, string $tahunAktifId): array
    {
        $ids = (clone $baseQuery)->limit(10)->pluck('siswa_id');

        if ($ids->isEmpty()) {
            return [];
        }

        return DB::table('siswa_kelas')
            ->join('siswa', 'siswa.id', '=', 'siswa_kelas.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->whereIn('siswa_kelas.siswa_id', $ids)
            ->where('siswa_kelas.tahun_pelajaran_id', $tahunAktifId)
            ->where('siswa_kelas.status', 'aktif')
            ->whereNull('siswa_kelas.deleted_at')
            ->select('siswa.nisn', 'siswa.nama_lengkap', 'kelas.nama_kelas')
            ->orderBy('siswa.nama_lengkap')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleActiveWithoutCurrentClass(string $tahunAktifId): array
    {
        return DB::table('siswa')
            ->whereNull('deleted_at')
            ->where('status_siswa', 'aktif')
            ->whereNotExists(function ($query) use ($tahunAktifId) {
                $query->select(DB::raw(1))
                    ->from('siswa_kelas')
                    ->whereColumn('siswa_kelas.siswa_id', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', $tahunAktifId)
                    ->where('siswa_kelas.status', 'aktif')
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->select('nisn', 'nama_lengkap', 'status_siswa')
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleCacheMismatch(string $tahunAktifId): array
    {
        return DB::table('siswa')
            ->join('siswa_kelas', function ($join) use ($tahunAktifId) {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunAktifId)
                    ->where('siswa_kelas.status', '=', 'aktif')
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->leftJoin('kelas as kelas_cache', 'kelas_cache.id', '=', 'siswa.kelas_saat_ini_id')
            ->leftJoin('kelas as kelas_pivot', 'kelas_pivot.id', '=', 'siswa_kelas.kelas_id')
            ->whereNull('siswa.deleted_at')
            ->whereNotNull('siswa.kelas_saat_ini_id')
            ->whereColumn('siswa.kelas_saat_ini_id', '!=', 'siswa_kelas.kelas_id')
            ->select(
                'siswa.nisn',
                'siswa.nama_lengkap',
                'kelas_cache.nama_kelas as kelas_cache',
                'kelas_pivot.nama_kelas as kelas_pivot'
            )
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleInactiveWithActiveClass(): array
    {
        return DB::table('siswa')
            ->join('siswa_kelas', function ($join) {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.status', '=', 'aktif')
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->whereNull('siswa.deleted_at')
            ->whereIn('siswa.status_siswa', ['lulus', 'mutasi_keluar', 'keluar', 'alumni'])
            ->select('siswa.nisn', 'siswa.nama_lengkap', 'siswa.status_siswa', 'kelas.nama_kelas')
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleApprovedMutasiStatusMismatch(): array
    {
        return DB::table('mutasi_siswa')
            ->join('siswa', 'siswa.id', '=', 'mutasi_siswa.siswa_id')
            ->where('mutasi_siswa.jenis_mutasi', 'keluar')
            ->where('mutasi_siswa.status_verifikasi', 'approved')
            ->where('siswa.status_siswa', '!=', 'mutasi_keluar')
            ->select(
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.status_siswa',
                'mutasi_siswa.tanggal_mutasi'
            )
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleMutasiKeluarUserActive(): array
    {
        return DB::table('siswa')
            ->join('users', 'users.id', '=', 'siswa.user_id')
            ->where('siswa.status_siswa', 'mutasi_keluar')
            ->where('users.is_active', true)
            ->select(
                'siswa.nisn',
                'siswa.nama_lengkap',
                'users.username',
                'users.is_active'
            )
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleKelas12WithoutSemester5(string $tahunAktifId): array
    {
        return DB::table('siswa')
            ->join('siswa_kelas', function ($join) use ($tahunAktifId) {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunAktifId)
                    ->where('siswa_kelas.status', '=', 'aktif')
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->whereNull('siswa.deleted_at')
            ->where('siswa.status_siswa', 'aktif')
            ->where('kelas.tingkat', 12)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('nilai_siswa')
                    ->whereColumn('nilai_siswa.siswa_id', 'siswa.id')
                    ->where('nilai_siswa.semester', 5)
                    ->whereNull('nilai_siswa.deleted_at');
            })
            ->select('siswa.nisn', 'siswa.nama_lengkap', 'kelas.nama_kelas')
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function sampleMissingConfiguredMapel(array $missingMapelBySemester): array
    {
        return collect($missingMapelBySemester)
            ->flatMap(function (array $codes, int $semester) {
                return collect($codes)->map(fn (string $code) => [
                    'semester' => $semester,
                    'kode_mapel' => $code,
                ]);
            })
            ->values()
            ->all();
    }

    private function findMissingConfiguredMapelBySemester(): array
    {
        $semesterConfigs = [
            1 => config('nilai.urutan_mapel_sem_1_2', []),
            2 => config('nilai.urutan_mapel_sem_1_2', []),
            3 => config('nilai.urutan_mapel_sem_3', []),
            4 => config('nilai.urutan_mapel_sem_4', []),
            5 => config('nilai.urutan_mapel_sem_5', []),
        ];

        $missing = [];

        foreach ($semesterConfigs as $semester => $configuredCodes) {
            $usedCodes = DB::table('nilai_siswa')
                ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'nilai_siswa.mata_pelajaran_id')
                ->where('nilai_siswa.semester', $semester)
                ->whereNull('nilai_siswa.deleted_at')
                ->distinct()
                ->pluck('mata_pelajaran.kode_mapel')
                ->all();

            $diff = array_values(array_diff($configuredCodes, $usedCodes));

            if (!empty($diff)) {
                $missing[$semester] = $diff;
            }
        }

        return $missing;
    }
}
