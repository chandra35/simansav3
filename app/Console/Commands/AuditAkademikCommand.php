<?php

namespace App\Console\Commands;

use App\Models\TahunPelajaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditAkademikCommand extends Command
{
    protected $signature = 'akademik:audit {--json : Tampilkan hasil audit dalam format JSON}';

    protected $description = 'Audit kesehatan data akademik tahunan untuk siswa, kelas, dan histori siswa_kelas';

    public function handle(): int
    {
        $tahunAktif = TahunPelajaran::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$tahunAktif) {
            $this->error('Tidak ada tahun pelajaran aktif.');
            return self::FAILURE;
        }

        $audit = $this->buildAudit($tahunAktif->id, $tahunAktif->nama);

        if ($this->option('json')) {
            $this->line(json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $this->resolveExitCode($audit);
        }

        $this->info('Audit Akademik SIMANSA');
        $this->line('Tahun pelajaran aktif: ' . $audit['tahun_pelajaran_aktif']);
        $this->newLine();

        $this->table(
            ['Check', 'Jumlah', 'Status'],
            collect($audit['checks'])->map(function (array $check) {
                return [
                    $check['label'],
                    $check['count'],
                    $check['count'] === 0 ? 'OK' : 'PERLU DICEK',
                ];
            })->all()
        );

        if (!empty($audit['samples'])) {
            $this->newLine();
            $this->warn('Contoh data yang perlu dicek:');

            foreach ($audit['samples'] as $title => $rows) {
                if (empty($rows)) {
                    continue;
                }

                $this->newLine();
                $this->line($title);
                $this->table(
                    array_keys((array) $rows[0]),
                    array_map(fn ($row) => array_values((array) $row), $rows)
                );
            }
        }

        $this->newLine();
        if ($this->resolveExitCode($audit) === self::SUCCESS) {
            $this->info('Audit selesai. Tidak ditemukan masalah prioritas tinggi.');
        } else {
            $this->warn('Audit selesai. Ada data yang perlu dibersihkan sebelum rollover tahun ajaran.');
        }

        return $this->resolveExitCode($audit);
    }

    private function buildAudit(string $tahunAktifId, string $tahunAktifNama): array
    {
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
        ];

        return [
            'tahun_pelajaran_aktif' => $tahunAktifNama,
            'checks' => $checks,
            'samples' => [
                'Duplikat kelas aktif tahun berjalan' => $this->sampleDuplicateRows($duplicateCurrentYear, $tahunAktifId),
                'Siswa aktif tanpa kelas tahun berjalan' => $this->sampleActiveWithoutCurrentClass($tahunAktifId),
                'Mismatch kelas_saat_ini_id' => $this->sampleCacheMismatch($tahunAktifId),
                'Siswa nonaktif dengan kelas aktif' => $this->sampleInactiveWithActiveClass(),
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

    private function resolveExitCode(array $audit): int
    {
        foreach ($audit['checks'] as $check) {
            if (($check['count'] ?? 0) > 0) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
