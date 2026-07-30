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
use Illuminate\Support\Str;

class RdmSyncService
{
    private const CONNECTION = 'mysql_rdm';

    public function __construct(private readonly RdmMatchingService $matchingService)
    {
    }

    public function getRdmActivePeriod(): array
    {
        return [
            'tahunajaran' => DB::connection(self::CONNECTION)->table('e_tahunajaran')
                ->select('tahunajaran_id', 'tahunajaran_nama')->where('tahunajaran_status', 1)->first(),
            'semester' => DB::connection(self::CONNECTION)->table('e_semester')
                ->select('semester_id', 'semester_nama')->where('semester_status', 1)->first(),
        ];
    }

    public function getRdmReference(): array
    {
        return [
            'tahun' => DB::connection(self::CONNECTION)->table('e_tahunajaran')
                ->select('tahunajaran_id', 'tahunajaran_nama', 'tahunajaran_status')
                ->orderByDesc('tahunajaran_id')->limit(10)->get(),
            'semester' => DB::connection(self::CONNECTION)->table('e_semester')
                ->select('semester_id', 'semester_nama', 'semester_status')
                ->whereIn('semester_id', [1, 2])->orderBy('semester_id')->get(),
            'tingkat' => DB::connection(self::CONNECTION)->table('e_tingkat')
                ->select('tingkat_id', 'tingkat_nama')->whereIn('tingkat_id', [12, 13, 14])
                ->orderBy('tingkat_id')->get(),
        ];
    }

    public function previewCohortSync(array $filters, ?string $initiatedBy): RdmSyncRun
    {
        $tahunRoster = TahunPelajaran::findOrFail($filters['simansa_tahun_pelajaran_id']);
        $tingkatAktif = (int) $filters['simansa_tingkat'];
        $semesterTerakhir = match ($tingkatAktif) {
            10 => 2,
            11 => 4,
            12 => 5,
        };
        $target = $this->targetStudents($filters);
        $plans = $this->buildCohortPeriodPlan($tahunRoster, $tingkatAktif, $semesterTerakhir);
        $firstPlan = $plans->first();

        $run = RdmSyncRun::create([
            'rdm_tahunajaran_id' => (int) ($firstPlan['rdm_tahunajaran_id'] ?? $firstPlan['source_year_start']),
            'rdm_semester_id' => (int) $firstPlan['rdm_semester_id'],
            'rdm_tingkat_id' => (int) $firstPlan['rdm_tingkat_id'],
            'simansa_tahun_pelajaran_id' => $tahunRoster->id,
            'simansa_kelas_id' => $filters['simansa_kelas_id'] ?? null,
            'status' => 'preview',
            'started_at' => now(),
            'initiated_by' => $initiatedBy,
        ]);

        if ($target->isEmpty()) {
            $run->update([
                'finished_at' => now(),
                'notes' => 'Tidak ada siswa aktif SIMANSA pada roster yang dipilih.',
                'meta' => [
                    'scope' => 'cohort_legger',
                    'semester_from' => 1,
                    'semester_to' => $semesterTerakhir,
                    'active_students' => 0,
                    'simansa_tingkat' => $tingkatAktif,
                    'periods' => $plans->values()->all(),
                ],
            ]);

            return $run->fresh();
        }

        $periods = [];
        $aggregate = [
            'total' => 0, 'matched' => 0, 'mapel' => 0, 'tahun' => 0,
            'insert' => 0, 'unchanged' => 0, 'conflict' => 0,
            'curriculum' => ['MERDEKA' => 0, 'K13' => 0, 'UNKNOWN' => 0],
        ];

        foreach ($plans as $plan) {
            if (!$plan['rdm_tahunajaran_id']) {
                $periods[] = array_merge($plan, [
                    'status' => 'period_unavailable',
                    'total_records' => 0,
                    'matched_students' => 0,
                    'missing_students' => $target->count(),
                ]);
                continue;
            }

            $child = $this->previewSync([
                'rdm_tahunajaran_id' => $plan['rdm_tahunajaran_id'],
                'rdm_semester_id' => $plan['rdm_semester_id'],
                'rdm_tingkat_id' => $plan['rdm_tingkat_id'],
                'rdm_kelas_nama' => null,
                'simansa_tahun_pelajaran_id' => $tahunRoster->id,
                'simansa_kelas_id' => $filters['simansa_kelas_id'] ?? null,
                'simansa_tingkat' => $tingkatAktif,
            ], $initiatedBy);
            $childMeta = $child->meta ?? [];

            DB::transaction(function () use ($child, $run) {
                DB::table('rdm_sync_staging')
                    ->where('run_id', $child->id)
                    ->update(['run_id' => $run->id]);
                $child->delete();
            });

            $period = array_merge($plan, [
                'status' => $child->total_records > 0 ? 'available' : 'no_values',
                'total_records' => (int) $child->total_records,
                'matched_students' => (int) data_get($childMeta, 'rdm_students_matched', 0),
                'missing_students' => (int) data_get($childMeta, 'active_students_without_rdm', $target->count()),
                'missing_students_sample' => data_get($childMeta, 'active_students_without_rdm_sample', []),
                'insert' => (int) data_get($childMeta, 'insert', 0),
                'unchanged' => (int) data_get($childMeta, 'unchanged', 0),
                'conflict' => (int) data_get($childMeta, 'conflict', 0),
                'curriculum_counts' => data_get($childMeta, 'curriculum_counts', []),
            ]);
            $periods[] = $period;

            $aggregate['total'] += (int) $child->total_records;
            $aggregate['matched'] += (int) $child->matched_records;
            $aggregate['mapel'] += (int) $child->mismatch_mapel_count;
            $aggregate['tahun'] += (int) $child->mismatch_tahun_count;
            foreach (['insert', 'unchanged', 'conflict'] as $key) {
                $aggregate[$key] += (int) data_get($childMeta, $key, 0);
            }
            foreach (array_keys($aggregate['curriculum']) as $curriculum) {
                $aggregate['curriculum'][$curriculum] += (int) data_get($childMeta, "curriculum_counts.{$curriculum}", 0);
            }
        }

        $availableSemesters = collect($periods)
            ->where('status', 'available')
            ->pluck('semester')
            ->values();
        $studentIdsWithHistory = RdmSyncStaging::query()
            ->where('run_id', $run->id)
            ->whereNotNull('simansa_siswa_id')
            ->distinct()
            ->pluck('simansa_siswa_id');
        $studentsComplete = 0;
        if ($availableSemesters->isNotEmpty()) {
            $studentsComplete = DB::query()->fromSub(
                RdmSyncStaging::query()
                    ->select('simansa_siswa_id')
                    ->where('run_id', $run->id)
                    ->whereIn('simansa_semester', $availableSemesters)
                    ->groupBy('simansa_siswa_id')
                    ->havingRaw('COUNT(DISTINCT simansa_semester) = ?', [$availableSemesters->count()]),
                'complete_students'
            )->count();
        }
        $missingAny = $target->whereNotIn('id', $studentIdsWithHistory)->values();

        $run->update([
            'total_records' => $aggregate['total'],
            'matched_records' => $aggregate['matched'],
            'mismatch_siswa_count' => $missingAny->count(),
            'mismatch_mapel_count' => $aggregate['mapel'],
            'mismatch_tahun_count' => $aggregate['tahun'],
            'finished_at' => now(),
            'notes' => "Preview leger Semester 1-{$semesterTerakhir} selesai. Periode RDM yang tersedia diproses otomatis.",
            'meta' => [
                'scope' => 'cohort_legger',
                'semester_from' => 1,
                'semester_to' => $semesterTerakhir,
                'active_students' => $target->count(),
                'simansa_tingkat' => $tingkatAktif,
                'simansa_roster_year' => $tahunRoster->nama,
                'rdm_students_matched' => $studentIdsWithHistory->count(),
                'students_with_history' => $studentIdsWithHistory->count(),
                'students_complete_available_periods' => $studentsComplete,
                'available_period_count' => $availableSemesters->count(),
                'active_students_without_rdm' => $missingAny->count(),
                'active_students_without_rdm_sample' => $missingAny->take(50)->map(fn ($student) => [
                    'id' => $student->id,
                    'nisn' => $student->nisn,
                    'nama' => $student->nama_lengkap,
                ])->all(),
                'insert' => $aggregate['insert'],
                'unchanged' => $aggregate['unchanged'],
                'conflict' => $aggregate['conflict'],
                'curriculum_counts' => $aggregate['curriculum'],
                'periods' => $periods,
            ],
        ]);

        return $run->fresh();
    }

    public function previewSync(array $filters, ?string $initiatedBy): RdmSyncRun
    {
        $run = RdmSyncRun::create([
            'rdm_tahunajaran_id' => (int) $filters['rdm_tahunajaran_id'],
            'rdm_semester_id' => (int) $filters['rdm_semester_id'],
            'rdm_tingkat_id' => (int) $filters['rdm_tingkat_id'],
            'rdm_kelas_nama' => $filters['rdm_kelas_nama'] ?? null,
            'simansa_tahun_pelajaran_id' => $filters['simansa_tahun_pelajaran_id'],
            'simansa_kelas_id' => $filters['simansa_kelas_id'] ?? null,
            'status' => 'preview',
            'started_at' => now(),
            'initiated_by' => $initiatedBy,
        ]);

        $target = $this->targetStudents($filters);
        $targetByNisn = $target->filter(fn ($s) => trim((string) $s->nisn) !== '')
            ->groupBy(fn ($s) => trim((string) $s->nisn));
        $duplicateSimansaNisn = $targetByNisn->filter(fn ($items) => $items->count() > 1)->keys();
        $targetByNisn = $targetByNisn->filter(fn ($items) => $items->count() === 1)
            ->map(fn ($items) => $items->first());

        $rawRows = $this->fetchRdmRows($filters);
        if ($rawRows->isEmpty() || $target->isEmpty()) {
            $run->update([
                'finished_at' => now(),
                'notes' => $target->isEmpty()
                    ? 'Tidak ada siswa aktif SIMANSA pada ruang lingkup yang dipilih.'
                    : 'Tidak ada nilai RDM pada periode yang dipilih.',
                'meta' => ['active_students' => $target->count()],
            ]);
            return $run->fresh();
        }

        $rdmStudents = $rawRows->unique('rdm_siswa_id')->values();
        $decrypted = $this->matchingService->decryptValues(
            $rdmStudents->pluck('rdm_nisn_encrypted')
                ->concat($rdmStudents->pluck('rdm_nama_encrypted'))->all()
        );
        $studentCount = $rdmStudents->count();
        $identity = [];
        foreach ($rdmStudents as $index => $student) {
            $identity[$student->rdm_siswa_id] = [
                'nisn' => trim((string) ($decrypted[$index] ?? '')),
                'nama' => trim((string) ($decrypted[$studentCount + $index] ?? '')),
            ];
        }

        // Hanya data siswa yang aktif pada roster SIMANSA yang boleh masuk staging.
        $eligibleIds = [];
        foreach ($identity as $rdmId => $item) {
            if (isset($targetByNisn[$item['nisn']])) {
                $eligibleIds[$rdmId] = $targetByNisn[$item['nisn']]->id;
            }
        }
        $eligibleSimansaIds = array_flip(array_values($eligibleIds));
        $missingStudents = $target
            ->reject(fn ($student) => isset($eligibleSimansaIds[$student->id]))
            ->take(50)
            ->map(fn ($student) => [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'nama' => $student->nama_lengkap,
            ])->values()->all();
        $eligibleRows = $rawRows->filter(fn ($row) => isset($eligibleIds[$row->rdm_siswa_id]));

        $simansaTahun = $this->resolveTahunPelajaran((int) $filters['rdm_tahunajaran_id']);
        $mapelMap = $this->buildMapelMap();
        $existing = $this->existingValues($eligibleIds, $simansaTahun?->id);
        $seenStudents = [];
        $stats = [
            'matched' => 0, 'mapel' => 0, 'tahun' => 0, 'conflict' => 0,
            'insert' => 0, 'unchanged' => 0, 'curriculum' => ['MERDEKA' => 0, 'K13' => 0, 'UNKNOWN' => 0],
        ];
        $insertRows = [];
        $now = now();

        $groups = $eligibleRows->groupBy(fn ($row) => implode(':', [
            $row->rdm_siswa_id, $row->rdm_mapel_id, $row->rdm_tahunajaran_id,
            $row->rdm_semester_id, $row->rdm_tingkat_id,
        ]));

        foreach ($groups as $rows) {
            $row = $rows->first();
            $seenStudents[$row->rdm_siswa_id] = true;
            $student = $identity[$row->rdm_siswa_id];
            $simansaSiswaId = $eligibleIds[$row->rdm_siswa_id];
            $simansaMapelId = $this->resolveMapel($row, $mapelMap);
            $simansaSemester = $this->mapSemester((int) $row->rdm_tingkat_id, (int) $row->rdm_semester_id);

            $curriculum = $this->detectCurriculum($rows);
            $stats['curriculum'][$curriculum]++;
            $merdekaNilai = $curriculum === 'MERDEKA' ? $rows->firstWhere('rdm_jenisnilai_id', 1) : null;
            $pengetahuan = $curriculum === 'K13' ? $rows->firstWhere('rdm_jenisnilai_id', 1) : null;
            $keterampilan = $curriculum === 'K13' ? $rows->firstWhere('rdm_jenisnilai_id', 2) : null;
            $nilaiUtama = $curriculum === 'MERDEKA'
                ? $this->numeric($merdekaNilai?->rdm_nilai ?? $row->rdm_nilai)
                : $this->average([$pengetahuan?->rdm_nilai, $keterampilan?->rdm_nilai]);

            $status = 'matched';
            $note = null;
            $action = 'insert';
            $naturalKey = implode(':', [$simansaSiswaId, $simansaMapelId, $simansaTahun?->id, $simansaSemester]);
            $old = $existing[$naturalKey] ?? null;

            if ($curriculum === 'UNKNOWN') {
                $status = 'mismatch_kurikulum';
                $note = 'Kurikulum RDM tidak dapat dikenali otomatis.';
                $action = 'skip';
                $stats['mapel']++;
            } elseif (!$simansaMapelId) {
                $status = 'mismatch_mapel';
                $note = 'Mapel RDM belum dipetakan ke mata pelajaran SIMANSA.';
                $action = 'skip';
                $stats['mapel']++;
            } elseif (!$simansaTahun || !$simansaSemester) {
                $status = 'mismatch_tahun';
                $note = 'Tahun pelajaran atau semester belum dapat dipetakan.';
                $action = 'skip';
                $stats['tahun']++;
            } elseif ($old && $this->sameValue($old->nilai, $nilaiUtama)) {
                $action = 'unchanged';
                $note = 'Nilai sama dengan data SIMANSA; tidak ditulis ulang.';
                $stats['matched']++;
                $stats['unchanged']++;
            } elseif ($old) {
                $status = 'conflict_existing';
                $action = 'conflict';
                $note = 'Nilai SIMANSA berbeda; ditahan agar tidak tertimpa otomatis.';
                $stats['conflict']++;
            } else {
                $stats['matched']++;
                $stats['insert']++;
            }

            $insertRows[] = [
                'id' => (string) Str::uuid(), 'run_id' => $run->id,
                'rdm_siswa_id' => $row->rdm_siswa_id, 'rdm_nisn' => $student['nisn'],
                'rdm_nis' => $row->rdm_nis, 'rdm_nama' => $student['nama'],
                'rdm_kelas_nama' => $row->rdm_kelas_nama, 'rdm_tingkat_id' => $row->rdm_tingkat_id,
                'rdm_mapel_id' => $row->rdm_mapel_id, 'rdm_mapel_nama' => $row->rdm_mapel_nama,
                'rdm_kurikulum_id' => $row->rdm_kurikulum_id,
                'rdm_kurikulum_kode' => $curriculum,
                'rdm_kurikulum_nama' => $row->rdm_kurikulum_nama,
                'rdm_nilai' => $nilaiUtama,
                'rdm_nilai_pengetahuan' => $this->numeric($pengetahuan?->rdm_nilai),
                'rdm_nilai_keterampilan' => $this->numeric($keterampilan?->rdm_nilai),
                'rdm_predikat' => $curriculum === 'MERDEKA'
                    ? ($merdekaNilai?->rdm_predikat ?? $row->rdm_predikat)
                    : ($pengetahuan?->rdm_predikat ?? $row->rdm_predikat),
                'rdm_deskripsi_pengetahuan' => $pengetahuan?->rdm_deskripsi,
                'rdm_deskripsi_keterampilan' => $keterampilan?->rdm_deskripsi,
                'rdm_tahunajaran_id' => $row->rdm_tahunajaran_id,
                'rdm_semester_id' => $row->rdm_semester_id,
                'simansa_siswa_id' => $simansaSiswaId,
                'simansa_mata_pelajaran_id' => $simansaMapelId,
                'simansa_tahun_pelajaran_id' => $simansaTahun?->id,
                'simansa_semester' => $simansaSemester, 'apply_action' => $action,
                'existing_nilai' => $old?->nilai,
                'existing_nilai_pengetahuan' => $old?->nilai_pengetahuan,
                'existing_nilai_keterampilan' => $old?->nilai_keterampilan,
                'match_status' => $status, 'match_notes' => $note,
                'created_at' => $now, 'updated_at' => $now,
            ];
        }

        foreach (array_chunk($insertRows, 500) as $chunk) {
            DB::table('rdm_sync_staging')->insert($chunk);
        }

        $run->update([
            'total_records' => count($insertRows),
            'matched_records' => $stats['matched'],
            'mismatch_siswa_count' => max(0, $target->count() - count($seenStudents)),
            'mismatch_mapel_count' => $stats['mapel'],
            'mismatch_tahun_count' => $stats['tahun'],
            'finished_at' => now(),
            'notes' => 'Preview aman selesai. Hanya siswa aktif SIMANSA; konflik tidak akan ditimpa.',
            'meta' => [
                'active_students' => $target->count(),
                'simansa_tingkat' => (int) $filters['simansa_tingkat'],
                'rdm_students_matched' => count($seenStudents),
                'active_students_without_rdm' => max(0, $target->count() - count($seenStudents)),
                'active_students_without_rdm_sample' => $missingStudents,
                'duplicate_simansa_nisn' => $duplicateSimansaNisn->count(),
                'insert' => $stats['insert'], 'unchanged' => $stats['unchanged'],
                'conflict' => $stats['conflict'], 'simansa_tahun' => $simansaTahun?->nama,
                'simansa_semester' => $this->mapSemester(
                    (int) $filters['rdm_tingkat_id'],
                    (int) $filters['rdm_semester_id']
                ),
                'curriculum_counts' => $stats['curriculum'],
            ],
        ]);

        return $run->fresh();
    }

    public function applySync(RdmSyncRun $run): RdmSyncRun
    {
        $rows = RdmSyncStaging::where('run_id', $run->id)
            ->where('match_status', 'matched')->where('apply_action', 'insert')->get();
        if ($rows->contains(fn ($row) => empty($row->rdm_kurikulum_kode))) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => 'Preview lama tidak memiliki metadata kurikulum. Jalankan preview ulang sebelum Apply.',
            ]);
            return $run->fresh();
        }
        if ($rows->isEmpty()) {
            $run->update(['status' => 'applied', 'applied_count' => 0, 'finished_at' => now(),
                'notes' => 'Tidak ada nilai baru. Data sama atau konflik ditahan.']);
            return $run->fresh();
        }

        $applied = 0;
        DB::transaction(function () use ($rows, &$applied) {
            foreach ($rows as $row) {
                $isMerdeka = $row->rdm_kurikulum_kode === 'MERDEKA';
                $created = NilaiSiswa::firstOrCreate([
                    'siswa_id' => $row->simansa_siswa_id,
                    'mata_pelajaran_id' => $row->simansa_mata_pelajaran_id,
                    'tahun_pelajaran_id' => $row->simansa_tahun_pelajaran_id,
                    'semester' => $row->simansa_semester,
                ], [
                    'nilai' => $row->rdm_nilai,
                    'nilai_pengetahuan' => $isMerdeka ? null : $row->rdm_nilai_pengetahuan,
                    'nilai_keterampilan' => $isMerdeka ? null : $row->rdm_nilai_keterampilan,
                    'predikat' => $row->rdm_predikat ?: NilaiSiswa::hitungPredikat($row->rdm_nilai),
                    'deskripsi_pengetahuan' => $isMerdeka ? null : $row->rdm_deskripsi_pengetahuan,
                    'deskripsi_keterampilan' => $isMerdeka ? null : $row->rdm_deskripsi_keterampilan,
                    'sumber_data' => 'rdm_sync', 'imported_at' => now(),
                ]);
                if ($created->wasRecentlyCreated) {
                    $applied++;
                }
            }
        });

        $run->update(['status' => 'applied', 'applied_count' => $applied, 'finished_at' => now(),
            'notes' => "Apply selesai: {$applied} nilai baru. Nilai lama dan konflik tidak diubah."]);
        return $run->fresh();
    }

    private function targetStudents(array $filters): Collection
    {
        return Siswa::query()->select('siswa.id', 'siswa.nisn', 'siswa.nama_lengkap')
            ->whereHas('kelasAktif', function ($query) use ($filters) {
                $query->where('kelas.tahun_pelajaran_id', $filters['simansa_tahun_pelajaran_id'])
                    ->whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id')
                    ->where('kelas.tingkat', (int) $filters['simansa_tingkat']);
                if (!empty($filters['simansa_kelas_id'])) {
                    $query->where('kelas.id', $filters['simansa_kelas_id']);
                }
            })->get();
    }

    private function fetchRdmRows(array $filters): Collection
    {
        return DB::connection(self::CONNECTION)->table('e_rapor as r')
            ->join('e_siswa as s', 's.siswa_id', '=', 'r.siswa_id')
            ->leftJoin('e_kelas as k', 'k.kelas_id', '=', 'r.kelas_id')
            ->leftJoin('e_mapel as m', 'm.mapel_id', '=', 'r.mapel_id')
            ->leftJoin('e_kurikulum as c', 'c.kurikulum_id', '=', 'm.kurikulum_id')
            ->select([
                'r.siswa_id as rdm_siswa_id', 's.siswa_nisn as rdm_nisn_encrypted',
                's.siswa_nis as rdm_nis', 's.siswa_nama as rdm_nama_encrypted',
                'k.kelas_nama as rdm_kelas_nama', 'r.tingkat_id as rdm_tingkat_id',
                'r.mapel_id as rdm_mapel_id', 'm.mapel_nama as rdm_mapel_nama',
                'm.kurikulum_id as rdm_kurikulum_id', 'r.jenisnilai_id as rdm_jenisnilai_id',
                'c.kurikulum_nama as rdm_kurikulum_nama',
                'c.kurikulum_alias as rdm_kurikulum_alias',
                'r.rapor_nilai as rdm_nilai', 'r.rapor_predikat as rdm_predikat',
                'r.rapor_deskripsi as rdm_deskripsi', 'r.rapor_deskmin as rdm_deskmin',
                'r.tahunajaran_id as rdm_tahunajaran_id', 'r.semester_id as rdm_semester_id',
            ])->where('r.tahunajaran_id', (int) $filters['rdm_tahunajaran_id'])
            ->where('r.semester_id', (int) $filters['rdm_semester_id'])
            ->where('r.tingkat_id', (int) $filters['rdm_tingkat_id'])
            ->when(!empty($filters['rdm_kelas_nama']), fn ($q) => $q->where('k.kelas_nama', $filters['rdm_kelas_nama']))
            ->whereNotNull('r.rapor_nilai')->get();
    }

    private function existingValues(array $eligibleIds, ?string $tahunId): array
    {
        if (!$tahunId || !$eligibleIds) return [];
        return NilaiSiswa::whereIn('siswa_id', array_values($eligibleIds))
            ->where('tahun_pelajaran_id', $tahunId)->get()
            ->keyBy(fn ($n) => implode(':', [$n->siswa_id, $n->mata_pelajaran_id, $n->tahun_pelajaran_id, $n->semester]))
            ->all();
    }

    private function resolveTahunPelajaran(int $id): ?TahunPelajaran
    {
        $rdm = DB::connection(self::CONNECTION)->table('e_tahunajaran')->where('tahunajaran_id', $id)->first();
        return $rdm ? TahunPelajaran::where('nama', trim((string) $rdm->tahunajaran_nama))->first() : null;
    }

    private function buildCohortPeriodPlan(
        TahunPelajaran $tahunRoster,
        int $tingkatAktif,
        int $semesterTerakhir
    ): Collection {
        $yearRows = DB::connection(self::CONNECTION)->table('e_tahunajaran')
            ->select('tahunajaran_id', 'tahunajaran_nama')
            ->get();

        return collect(range(1, $semesterTerakhir))->map(function (int $semester) use (
            $tahunRoster,
            $tingkatAktif,
            $yearRows
        ) {
            $gradeOffset = intdiv($semester - 1, 2);
            $sourceGrade = 10 + $gradeOffset;
            $sourceYearStart = (int) $tahunRoster->tahun_mulai - ($tingkatAktif - $sourceGrade);
            $sourceYearName = $sourceYearStart . '/' . ($sourceYearStart + 1);
            $rdmYear = $yearRows->first(fn ($row) =>
                trim((string) $row->tahunajaran_nama) === $sourceYearName
                || (int) $row->tahunajaran_id === $sourceYearStart
            );

            return [
                'semester' => $semester,
                'source_year_start' => $sourceYearStart,
                'source_year_name' => $sourceYearName,
                'source_grade' => $sourceGrade,
                'source_grade_label' => [10 => 'X', 11 => 'XI', 12 => 'XII'][$sourceGrade],
                'rdm_tahunajaran_id' => $rdmYear ? (int) $rdmYear->tahunajaran_id : null,
                'rdm_semester_id' => $semester % 2 === 1 ? 1 : 2,
                'rdm_semester_label' => $semester % 2 === 1 ? 'Ganjil' : 'Genap',
                'rdm_tingkat_id' => 12 + $gradeOffset,
                'status' => 'planned',
            ];
        });
    }

    private function buildMapelMap(): array
    {
        $map = [];
        foreach (RdmMapelMapping::all() as $m) {
            $map['ID:' . $m->rdm_mapel_id] = $m->mata_pelajaran_id;
        }
        MataPelajaran::with('kurikulum:id,kode')->where('is_active', true)->get()
            ->each(function ($m) use (&$map) {
            $normalized = $this->normalizeText($m->nama_mapel);
            $curriculum = strtoupper((string) $m->kurikulum?->kode);
            if ($curriculum !== '') {
                $map[$curriculum . ':' . $normalized] ??= $m->id;
            }
            $map[$normalized] ??= $m->id;
        });
        return $map;
    }

    private function resolveMapel(object $row, array $map): ?string
    {
        $normalized = $this->normalizeText($row->rdm_mapel_nama ?? '');
        $curriculum = (int) $row->rdm_kurikulum_id === 2 ? 'MERDEKA' : 'K13';
        return $map['ID:' . $row->rdm_mapel_id]
            ?? $map[$curriculum . ':' . $normalized]
            ?? $map[$normalized]
            ?? null;
    }

    private function normalizeText(string $text): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/i', ' ', mb_strtolower($text)));
    }

    private function mapSemester(int $tingkat, int $semester): ?int
    {
        return match ($tingkat) {
            12 => $semester,
            13 => $semester + 2,
            14 => $semester + 4,
            default => null,
        };
    }

    private function numeric(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function detectCurriculum(Collection $rows): string
    {
        $row = $rows->first();
        $name = mb_strtoupper(trim(implode(' ', array_filter([
            $row->rdm_kurikulum_nama ?? null,
            $row->rdm_kurikulum_alias ?? null,
        ]))), 'UTF-8');

        if (str_contains($name, 'MERDEKA')) {
            return 'MERDEKA';
        }
        if (str_contains($name, 'K13') || str_contains($name, '2013')) {
            return 'K13';
        }
        if ($rows->contains(fn ($item) => (int) $item->rdm_jenisnilai_id === 2)) {
            return 'K13';
        }
        if ((int) ($row->rdm_kurikulum_id ?? 0) === 2) {
            return 'MERDEKA';
        }

        return 'UNKNOWN';
    }

    private function average(array $values): ?float
    {
        $values = array_values(array_filter(array_map([$this, 'numeric'], $values), fn ($v) => $v !== null));
        return $values ? round(array_sum($values) / count($values), 2) : null;
    }

    private function sameValue(mixed $a, mixed $b): bool
    {
        return $a !== null && $b !== null && abs((float) $a - (float) $b) < 0.01;
    }
}
