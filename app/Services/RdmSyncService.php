<?php

namespace App\Services;

use App\Models\MataPelajaran;
use App\Models\NilaiSiswa;
use App\Models\RdmMapelMapping;
use App\Models\RdmSyncRun;
use App\Models\RdmSyncStaging;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RdmSyncService
{
    private const CONNECTION = 'mysql_rdm';

    public function getRdmActivePeriod(): array
    {
        $tahun = DB::connection(self::CONNECTION)
            ->table('e_tahunajaran')
            ->select('tahunajaran_id', 'tahunajaran_nama')
            ->where('tahunajaran_status', 1)
            ->first();

        $semester = DB::connection(self::CONNECTION)
            ->table('e_semester')
            ->select('semester_id', 'semester_nama')
            ->where('semester_status', 1)
            ->first();

        return [
            'tahunajaran' => $tahun,
            'semester' => $semester,
        ];
    }

    public function getRdmReference(): array
    {
        $tahunList = DB::connection(self::CONNECTION)
            ->table('e_tahunajaran')
            ->select('tahunajaran_id', 'tahunajaran_nama', 'tahunajaran_status')
            ->orderByDesc('tahunajaran_id')
            ->limit(8)
            ->get();

        $semesterList = DB::connection(self::CONNECTION)
            ->table('e_semester')
            ->select('semester_id', 'semester_nama', 'semester_status')
            ->orderByDesc('semester_id')
            ->limit(8)
            ->get();

        $tingkatList = DB::connection(self::CONNECTION)
            ->table('e_tingkat')
            ->select('tingkat_id', 'tingkat_nama')
            ->orderBy('tingkat_id')
            ->get();

        return [
            'tahun' => $tahunList,
            'semester' => $semesterList,
            'tingkat' => $tingkatList,
        ];
    }

    public function previewSync(array $filters, ?string $initiatedBy): RdmSyncRun
    {
        $run = RdmSyncRun::create([
            'rdm_tahunajaran_id' => (int) $filters['rdm_tahunajaran_id'],
            'rdm_semester_id' => (int) $filters['rdm_semester_id'],
            'rdm_tingkat_id' => !empty($filters['rdm_tingkat_id']) ? (int) $filters['rdm_tingkat_id'] : null,
            'rdm_kelas_nama' => $filters['rdm_kelas_nama'] ?? null,
            'status' => 'preview',
            'started_at' => now(),
            'initiated_by' => $initiatedBy,
        ]);

        $rawRows = $this->fetchRdmRows($filters);

        if ($rawRows->isEmpty()) {
            $run->update([
                'finished_at' => now(),
                'status' => 'preview',
                'notes' => 'Tidak ada data nilai pada filter RDM yang dipilih.',
            ]);
            return $run;
        }

        $simansaTahun = $this->resolveTahunPelajaran((int) $filters['rdm_tahunajaran_id']);
        $siswaMap = $this->buildSiswaMap();
        $mapelMap = $this->buildMapelMap();

        $matched = 0;
        $mismatchSiswa = 0;
        $mismatchMapel = 0;
        $mismatchTahun = 0;

        $insertRows = [];
        $now = now();

        foreach ($rawRows as $row) {
            $normMapel = $this->normalizeText($row->rdm_mapel_nama ?? '');
            $normNisn = trim((string) ($row->rdm_nisn ?? ''));
            $normNis = trim((string) ($row->rdm_nis ?? ''));

            $simansaSiswaId = $siswaMap[$normNisn] ?? ($normNis ? ($siswaMap['NIS:' . $normNis] ?? null) : null);

            // Kurikulum-scoped mapel matching:
            // 1. Manual mapping by RDM_ID (highest priority)
            // 2. Kurikulum-scoped name match (RDM kurikulum_id: 1=K13, 2=Merdeka)
            // 3. Generic name fallback
            $simansaMapelId = $mapelMap['RDM_ID:' . $row->rdm_mapel_id] ?? null;
            if (!$simansaMapelId) {
                $kurikulumIndex = $mapelMap['__kurikulum_index'] ?? [];
                $rdmKurikulumKode = ((int) ($row->rdm_kurikulum_id ?? 0)) === 2 ? 'MERDEKA' : 'K13';
                $simansaMapelId = $kurikulumIndex[$rdmKurikulumKode][$normMapel] ?? $mapelMap[$normMapel] ?? null;
            }

            $simansaSemester = $this->mapSemester((int) ($row->rdm_tingkat_id ?? 0), (int) ($row->rdm_semester_id ?? 0));

            $status = 'matched';
            $note = null;

            if (!$simansaSiswaId) {
                $status = 'mismatch_siswa';
                $note = 'Siswa tidak ditemukan di SIMANSA (NISN/NIS tidak match).';
                $mismatchSiswa++;
            } elseif (!$simansaMapelId) {
                $status = 'mismatch_mapel';
                $note = 'Mapel tidak ditemukan di SIMANSA.';
                $mismatchMapel++;
            } elseif (!$simansaTahun || !$simansaSemester) {
                $status = 'mismatch_tahun';
                $note = 'Tahun pelajaran atau semester RDM belum bisa dipetakan ke SIMANSA.';
                $mismatchTahun++;
            } else {
                $matched++;
            }

            $insertRows[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'run_id' => $run->id,
                'rdm_siswa_id' => $row->rdm_siswa_id,
                'rdm_nisn' => $row->rdm_nisn,
                'rdm_nis' => $row->rdm_nis,
                'rdm_nama' => $row->rdm_nama,
                'rdm_kelas_nama' => $row->rdm_kelas_nama,
                'rdm_tingkat_id' => $row->rdm_tingkat_id,
                'rdm_mapel_id' => $row->rdm_mapel_id,
                'rdm_mapel_nama' => $row->rdm_mapel_nama,
                'rdm_nilai' => $row->rdm_nilai,
                'rdm_tahunajaran_id' => $row->rdm_tahunajaran_id,
                'rdm_semester_id' => $row->rdm_semester_id,
                'simansa_siswa_id' => $simansaSiswaId,
                'simansa_mata_pelajaran_id' => $simansaMapelId,
                'simansa_tahun_pelajaran_id' => $simansaTahun?->id,
                'simansa_semester' => $simansaSemester,
                'match_status' => $status,
                'match_notes' => $note,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($insertRows, 1000) as $chunk) {
            DB::table('rdm_sync_staging')->insert($chunk);
        }

        $run->update([
            'total_records' => count($insertRows),
            'matched_records' => $matched,
            'mismatch_siswa_count' => $mismatchSiswa,
            'mismatch_mapel_count' => $mismatchMapel,
            'mismatch_tahun_count' => $mismatchTahun,
            'finished_at' => now(),
            'notes' => 'Preview selesai. Periksa mismatch sebelum apply sync.',
            'meta' => [
                'rdm_sample_count' => count($insertRows),
                'simansa_tahun' => $simansaTahun?->nama,
            ],
        ]);

        return $run->fresh();
    }

    public function applySync(RdmSyncRun $run): RdmSyncRun
    {
        $rows = RdmSyncStaging::query()
            ->where('run_id', $run->id)
            ->where('match_status', 'matched')
            ->whereNotNull('simansa_siswa_id')
            ->whereNotNull('simansa_mata_pelajaran_id')
            ->whereNotNull('simansa_tahun_pelajaran_id')
            ->whereNotNull('simansa_semester')
            ->get();

        if ($rows->isEmpty()) {
            $run->update([
                'status' => 'failed',
                'notes' => 'Tidak ada data matched yang bisa di-apply.',
                'finished_at' => now(),
            ]);
            return $run;
        }

        // Preload kurikulum kode for all mata_pelajaran used in this batch
        $mapelIds = $rows->pluck('simansa_mata_pelajaran_id')->unique()->values()->toArray();
        $mapelKurikulumMap = MataPelajaran::query()
            ->whereIn('id', $mapelIds)
            ->with('kurikulum:id,kode')
            ->get()
            ->keyBy('id')
            ->map(fn ($mp) => strtoupper($mp->kurikulum?->kode ?? ''));

        $applied = 0;
        DB::transaction(function () use ($rows, $mapelKurikulumMap, &$applied) {
            foreach ($rows as $row) {
                $nilai = is_numeric($row->rdm_nilai) ? (float) $row->rdm_nilai : null;
                $kurikulumKode = $mapelKurikulumMap[$row->simansa_mata_pelajaran_id] ?? '';

                $nilaiSiswa = NilaiSiswa::withTrashed()->firstOrNew([
                    'siswa_id' => $row->simansa_siswa_id,
                    'mata_pelajaran_id' => $row->simansa_mata_pelajaran_id,
                    'tahun_pelajaran_id' => $row->simansa_tahun_pelajaran_id,
                    'semester' => $row->simansa_semester,
                ]);

                // Kurikulum-aware: Merdeka hanya menggunakan kolom nilai,
                // K13 menggunakan nilai_pengetahuan dan nilai_keterampilan
                if ($kurikulumKode === 'MERDEKA') {
                    $nilaiSiswa->fill([
                        'nilai' => $nilai,
                        'nilai_pengetahuan' => null,
                        'nilai_keterampilan' => null,
                        'predikat' => NilaiSiswa::hitungPredikat($nilai),
                        'sumber_data' => 'rdm_sync',
                        'imported_at' => now(),
                    ]);
                } else {
                    // K13: nilai pengetahuan & keterampilan
                    $nilaiSiswa->fill([
                        'nilai' => $nilai,
                        'nilai_pengetahuan' => $nilai,
                        'nilai_keterampilan' => $nilai,
                        'predikat' => NilaiSiswa::hitungPredikat($nilai),
                        'sumber_data' => 'rdm_sync',
                        'imported_at' => now(),
                    ]);
                }

                if ($nilaiSiswa->trashed()) {
                    $nilaiSiswa->restore();
                }

                $nilaiSiswa->save();

                $applied++;
            }
        });

        $run->update([
            'status' => 'applied',
            'applied_count' => $applied,
            'finished_at' => now(),
            'notes' => 'Apply sync berhasil ke tabel nilai_siswa.',
        ]);

        return $run->fresh();
    }

    private function fetchRdmRows(array $filters): Collection
    {
        return DB::connection(self::CONNECTION)
            ->table('e_rapor as r')
            ->join('e_siswa as s', 's.siswa_id', '=', 'r.siswa_id')
            ->leftJoin('e_kelas as k', 'k.kelas_id', '=', 'r.kelas_id')
            ->leftJoin('e_mapel as m', 'm.mapel_id', '=', 'r.mapel_id')
            ->select([
                'r.siswa_id as rdm_siswa_id',
                's.siswa_nisn as rdm_nisn',
                's.siswa_nis as rdm_nis',
                's.siswa_nama as rdm_nama',
                'k.kelas_nama as rdm_kelas_nama',
                'k.tingkat_id as rdm_tingkat_id',
                'r.mapel_id as rdm_mapel_id',
                'm.mapel_nama as rdm_mapel_nama',
                'm.kurikulum_id as rdm_kurikulum_id',
                'r.rapor_nilai as rdm_nilai',
                'r.tahunajaran_id as rdm_tahunajaran_id',
                'r.semester_id as rdm_semester_id',
            ])
            ->where('r.tahunajaran_id', (int) $filters['rdm_tahunajaran_id'])
            ->where('r.semester_id', (int) $filters['rdm_semester_id'])
            ->when(!empty($filters['rdm_tingkat_id']), function ($q) use ($filters) {
                $q->where('k.tingkat_id', (int) $filters['rdm_tingkat_id']);
            })
            ->when(!empty($filters['rdm_kelas_nama']), function ($q) use ($filters) {
                $q->where('k.kelas_nama', $filters['rdm_kelas_nama']);
            })
            ->whereNotNull('r.rapor_nilai')
            ->orderBy('s.siswa_nama')
            ->orderBy('m.mapel_nama')
            ->get();
    }

    private function resolveTahunPelajaran(int $rdmTahunAjaranId): ?TahunPelajaran
    {
        $rdmTahun = DB::connection(self::CONNECTION)
            ->table('e_tahunajaran')
            ->where('tahunajaran_id', $rdmTahunAjaranId)
            ->first();

        if (!$rdmTahun) {
            return null;
        }

        return TahunPelajaran::query()
            ->where('nama', trim((string) $rdmTahun->tahunajaran_nama))
            ->orWhere('tahun_mulai', $rdmTahunAjaranId)
            ->first();
    }

    private function buildSiswaMap(): array
    {
        $map = [];

        Siswa::query()
            ->select('id', 'nisn')
            ->whereNotNull('nisn')
            ->chunk(1000, function ($rows) use (&$map) {
                foreach ($rows as $row) {
                    $map[trim((string) $row->nisn)] = $row->id;
                }
            });

        Siswa::query()
            ->select('id')
            ->with(['user:id,username'])
            ->chunk(1000, function ($rows) use (&$map) {
                foreach ($rows as $row) {
                    $username = trim((string) ($row->user->username ?? ''));
                    if ($username !== '') {
                        $map['NIS:' . $username] = $row->id;
                    }
                }
            });

        return $map;
    }

    private function buildMapelMap(): array
    {
        $map = [];

        // Priority 1: Manual mapping table (rdm_mapel_id → simansa mata_pelajaran_id)
        RdmMapelMapping::query()
            ->select('rdm_mapel_id', 'mata_pelajaran_id')
            ->get()
            ->each(function ($item) use (&$map) {
                $map['RDM_ID:' . $item->rdm_mapel_id] = $item->mata_pelajaran_id;
            });

        // Priority 2: Kurikulum-scoped normalized name matching
        // Build separate indexes per kurikulum to avoid cross-kurikulum mismatches
        $kurikulumIndex = [];
        MataPelajaran::query()
            ->select('id', 'nama_mapel', 'kurikulum_id')
            ->with('kurikulum:id,kode')
            ->where('is_active', true)
            ->get()
            ->each(function ($item) use (&$kurikulumIndex) {
                $key = $this->normalizeText($item->nama_mapel ?? '');
                $kode = strtoupper($item->kurikulum?->kode ?? 'UNKNOWN');
                if ($key !== '') {
                    $kurikulumIndex[$kode][$key] = $item->id;
                }
            });

        // Priority 3: Generic fallback (first match across all kurikulum)
        MataPelajaran::query()
            ->select('id', 'nama_mapel')
            ->where('is_active', true)
            ->get()
            ->each(function ($item) use (&$map) {
                $key = $this->normalizeText($item->nama_mapel ?? '');
                if ($key !== '' && !isset($map[$key])) {
                    $map[$key] = $item->id;
                }
            });

        // Store kurikulum index in map for scoped lookup
        $map['__kurikulum_index'] = $kurikulumIndex;

        return $map;
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = str_replace(['`', "'", '’'], '', $text);
        $text = preg_replace('/[^a-z0-9]+/i', ' ', $text);
        return trim((string) $text);
    }

    private function mapSemester(int $rdmTingkatId, int $rdmSemesterId): ?int
    {
        if ($rdmSemesterId !== 1 && $rdmSemesterId !== 2) {
            return null;
        }

        return match ($rdmTingkatId) {
            12 => $rdmSemesterId === 1 ? 1 : 2, // X
            13 => $rdmSemesterId === 1 ? 3 : 4, // XI
            14 => $rdmSemesterId === 1 ? 5 : 6, // XII
            default => null,
        };
    }
}
