<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Models\Kelas;
use App\Models\AlumniProfile;
use App\Models\SiswaKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class NilaiController extends Controller
{
    /**
     * Daftar mapel yang benar-benar mempunyai nilai pada pasangan semester/tahun.
     * Ini menjaga rekap dan export tetap cocok dengan ID mapel K13 maupun Merdeka.
     *
     * @param  array<int, string>  $periods semester => tahun_pelajaran_id
     */
    private function getActualMapelList(array $periods, bool $useLegacyFallback = true): Collection
    {
        $periods = array_filter($periods);
        if (!$periods) {
            return collect();
        }

        $mapelIds = NilaiSiswa::query()
            ->where(function ($query) use ($periods) {
                foreach ($periods as $semester => $tahunPelajaranId) {
                    $query->orWhere(function ($periodQuery) use ($semester, $tahunPelajaranId) {
                        $periodQuery->where('semester', (int) $semester)
                            ->where('tahun_pelajaran_id', $tahunPelajaranId);
                    });
                }
            })
            ->distinct()
            ->pluck('mata_pelajaran_id');

        if ($mapelIds->isEmpty()) {
            if (!$useLegacyFallback) {
                return collect();
            }

            $semester = (int) array_key_first($periods);
            $tahunPelajaranId = $periods[$semester] ?? null;
            $sourceGrade = 10 + intdiv(max(1, $semester) - 1, 2);
            $curriculumIds = Kelas::query()
                ->where('tahun_pelajaran_id', $tahunPelajaranId)
                ->where('tingkat', $sourceGrade)
                ->whereNotNull('kurikulum_id')
                ->distinct()
                ->pluck('kurikulum_id');

            return MataPelajaran::query()
                ->with('kurikulum:id,kode')
                ->where('is_active', true)
                ->whereJsonContains('tingkat', $sourceGrade)
                ->when(
                    $curriculumIds->isNotEmpty(),
                    fn ($query) => $query->whereIn('kurikulum_id', $curriculumIds),
                    fn ($query) => $query->whereHas('rdmMappings')
                )
                ->get()
                ->sortBy(fn ($mapel) => $this->mapelAcademicSortKey($mapel))
                ->values();
        }

        return MataPelajaran::withTrashed()
            ->with('kurikulum:id,kode')
            ->whereIn('id', $mapelIds)
            ->get()
            ->sortBy(fn ($mapel) => $this->mapelAcademicSortKey($mapel))
            ->values();
    }

    private function mapelAcademicSortKey(MataPelajaran $mapel): string
    {
        $name = mb_strtolower($mapel->nama_mapel);
        $priorities = [
            'qur' => 10,
            'akidah' => 20,
            'fikih' => 30,
            'kebudayaan islam' => 40,
            'bahasa arab' => 50,
            'pancasila' => 60,
            'bahasa indonesia' => 70,
            'matematika' => 80,
            'fisika' => 90,
            'kimia' => 100,
            'biologi' => 110,
            'sosiologi' => 120,
            'ekonomi' => 130,
            'geografi' => 140,
            'bahasa inggris' => 150,
            'jasmani' => 160,
            'sejarah' => 170,
            'informatika' => 180,
            'seni' => 190,
            'prakarya' => 200,
            'lampung' => 210,
            'keterampilan agama' => 220,
            'tahf' => 230,
        ];

        foreach ($priorities as $needle => $priority) {
            if (str_contains($name, $needle)) {
                return sprintf('%03d:%s:%s', $priority, $name, $mapel->kode_mapel);
            }
        }

        return sprintf('900:%s:%s', $name, $mapel->kode_mapel);
    }

    /**
     * Build a compact score lookup without retaining thousands of Eloquent
     * models and nested Collections during a multi-semester Excel export.
     *
     * @param  Collection<int, string>  $siswaIds
     * @param  array<int, string>  $periods semester => tahun_pelajaran_id
     * @return array<string, float|string>
     */
    private function getLeggerNilaiLookup(Collection $siswaIds, array $periods): array
    {
        if ($siswaIds->isEmpty() || !$periods) {
            return [];
        }

        $lookup = [];

        DB::table('nilai_siswa as nilai_siswa')
            ->join('mata_pelajaran as mata_pelajaran', 'mata_pelajaran.id', '=', 'nilai_siswa.mata_pelajaran_id')
            ->select(['nilai_siswa.id', 'nilai_siswa.siswa_id', 'nilai_siswa.semester', 'mata_pelajaran.kode_mapel', 'nilai_siswa.nilai'])
            ->whereIn('siswa_id', $siswaIds)
            ->where(function ($query) use ($periods) {
                foreach ($periods as $semester => $tahunPelajaranId) {
                    $query->orWhere(function ($periodQuery) use ($semester, $tahunPelajaranId) {
                        $periodQuery->where('nilai_siswa.semester', (int) $semester)
                            ->where('nilai_siswa.tahun_pelajaran_id', $tahunPelajaranId);
                    });
                }
            })
            ->lazyById(1000, 'nilai_siswa.id', 'id')
            ->each(function ($row) use (&$lookup) {
                $key = $this->leggerNilaiKey($row->siswa_id, (int) $row->semester, $row->kode_mapel);
                $lookup[$key] ??= $row->nilai;
            });

        return $lookup;
    }

    private function leggerNilaiKey(string $siswaId, int $semester, string $kodeMapel): string
    {
        return $siswaId.'|'.$semester.'|'.$kodeMapel;
    }

    /**
     * Semester config per tingkat kelas
     */
    private function getSemesterConfig($tingkat, $tahunAktif, bool $includeSemester6 = false)
    {
        $tahunAktifMulai = $tahunAktif ? $tahunAktif->tahun_mulai : date('Y');
        
        $configs = [
            12 => [
                1 => ['label' => 'Sem 1 (X-1)', 'offset' => -2],
                2 => ['label' => 'Sem 2 (X-2)', 'offset' => -2],
                3 => ['label' => 'Sem 3 (XI-1)', 'offset' => -1],
                4 => ['label' => 'Sem 4 (XI-2)', 'offset' => -1],
                5 => ['label' => 'Sem 5 (XII-1)', 'offset' => 0],
            ],
            11 => [
                1 => ['label' => 'Sem 1 (X-1)', 'offset' => -1],
                2 => ['label' => 'Sem 2 (X-2)', 'offset' => -1],
                3 => ['label' => 'Sem 3 (XI-1)', 'offset' => 0],
                4 => ['label' => 'Sem 4 (XI-2)', 'offset' => 0],
            ],
            10 => [
                1 => ['label' => 'Sem 1 (X-1)', 'offset' => 0],
                2 => ['label' => 'Sem 2 (X-2)', 'offset' => 0],
            ],
        ];
        
        $config = $configs[$tingkat] ?? $configs[12];
        if ((int) $tingkat === 12 && $includeSemester6) {
            $config[6] = ['label' => 'Sem 6 (XII-2)', 'offset' => 0];
        }
        return $config;
    }

    /**
     * Get tahun pelajaran by offset from tahun aktif
     */
    private function getTahunPelajaranByOffset($tahunAktif, $offset)
    {
        if (!$tahunAktif) return null;
        
        $targetTahun = $tahunAktif->tahun_mulai + $offset;
        return TahunPelajaran::where('tahun_mulai', $targetTahun)->first();
    }

    /**
     * Mapel template mengikuti data aktual. Jika periode belum memiliki nilai,
     * gunakan periode akademik terdekat dari kohor yang sama, lalu mapping RDM.
     */
    private function getTemplateMapelList(int $semester, TahunPelajaran $tahunPelajaran): Collection
    {
        $actual = $this->getActualMapelList([$semester => $tahunPelajaran->id], false);
        if ($actual->isNotEmpty()) {
            return $actual;
        }

        $fallbackPeriods = [];
        if ($semester % 2 === 0) {
            $fallbackPeriods[$semester - 1] = $tahunPelajaran->id;
        } elseif ($semester > 1) {
            $previousYear = TahunPelajaran::where('tahun_mulai', $tahunPelajaran->tahun_mulai - 1)->first();
            if ($previousYear) {
                $fallbackPeriods[$semester - 1] = $previousYear->id;
            }
        } else {
            $fallbackPeriods[2] = $tahunPelajaran->id;
        }

        $fallback = $this->getActualMapelList($fallbackPeriods, false);
        if ($fallback->isNotEmpty()) {
            return $fallback;
        }

        $sourceGrade = 10 + intdiv(max(1, $semester) - 1, 2);

        return MataPelajaran::query()
            ->with('kurikulum:id,kode')
            ->where('is_active', true)
            ->whereJsonContains('tingkat', $sourceGrade)
            ->whereHas('rdmMappings')
            ->get()
            ->sortBy(fn ($mapel) => $this->mapelAcademicSortKey($mapel))
            ->values();
    }

    /**
     * ID siswa yang benar-benar aktif pada roster tingkat dan tahun berjalan.
     * Nilai alumni tetap tersimpan, tetapi tidak masuk tampilan leger aktif.
     */
    private function getActiveRosterStudentIds(?TahunPelajaran $tahunAktif, int $tingkat)
    {
        if (!$tahunAktif || !in_array($tingkat, [10, 11, 12], true)) {
            return collect();
        }

        return Siswa::query()
            ->whereHas('kelasAktif', function ($query) use ($tahunAktif, $tingkat) {
                $query->where('kelas.tahun_pelajaran_id', $tahunAktif->id)
                    ->whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id')
                    ->where('kelas.tingkat', $tingkat);
            })
            ->pluck('siswa.id');
    }

    /**
     * Data ranking memakai roster aktif sebagai populasi dan pasangan periode
     * kohor yang sama dengan leger. Nilai kumulatif adalah rata-rata dari
     * rata-rata semester agar perbedaan jumlah mapel tidak mengubah bobot semester.
     */
    private function buildRankingData(TahunPelajaran $tahunAktif, int $tingkat, string $mode, int $semester): array
    {
        $semesterConfig = $this->getSemesterConfig($tingkat, $tahunAktif);
        $periods = [];
        foreach ($semesterConfig as $sem => $config) {
            $tahun = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
            if ($tahun) {
                $periods[$sem] = $tahun->id;
            }
        }

        $requestedPeriods = $mode === 'semester'
            ? array_intersect_key($periods, [$semester => true])
            : $periods;

        $roster = DB::table('siswa_kelas as sk')
            ->join('siswa as s', 's.id', '=', 'sk.siswa_id')
            ->join('kelas as k', 'k.id', '=', 'sk.kelas_id')
            ->where('sk.tahun_pelajaran_id', $tahunAktif->id)
            ->where('k.tahun_pelajaran_id', $tahunAktif->id)
            ->where('k.tingkat', $tingkat)
            ->where('sk.status', 'aktif')
            ->whereNull('sk.deleted_at')
            ->whereNull('s.deleted_at')
            ->whereNull('k.deleted_at')
            ->select([
                's.id as siswa_id',
                's.nisn',
                's.nis_lokal as nis',
                's.nama_lengkap',
                's.jenis_kelamin',
                'k.id as kelas_id',
                'k.nama_kelas',
            ])
            ->orderBy('k.nama_kelas')
            ->orderBy('s.nama_lengkap')
            ->get()
            ->unique('siswa_id')
            ->values();

        $aggregates = collect();
        if ($roster->isNotEmpty() && $requestedPeriods) {
            $aggregates = DB::table('nilai_siswa')
                ->whereNull('deleted_at')
                ->whereIn('siswa_id', $roster->pluck('siswa_id'))
                ->where(function ($query) use ($requestedPeriods) {
                    foreach ($requestedPeriods as $sem => $tahunId) {
                        $query->orWhere(function ($periodQuery) use ($sem, $tahunId) {
                            $periodQuery->where('semester', (int) $sem)
                                ->where('tahun_pelajaran_id', $tahunId);
                        });
                    }
                })
                ->selectRaw('siswa_id, semester, AVG(nilai) as rata_rata, SUM(nilai) as total_nilai, COUNT(DISTINCT mata_pelajaran_id) as jumlah_mapel')
                ->groupBy('siswa_id', 'semester')
                ->get();
        }

        $byStudent = $aggregates->groupBy('siswa_id');
        $availableSemesters = collect(array_keys($requestedPeriods))
            ->filter(fn ($sem) => $aggregates->contains(fn ($row) => (int) $row->semester === (int) $sem))
            ->values()
            ->all();
        $expectedMapelCounts = collect($availableSemesters)->mapWithKeys(function ($sem) use ($aggregates) {
            $frequency = $aggregates
                ->filter(fn ($row) => (int) $row->semester === (int) $sem)
                ->countBy(fn ($row) => (int) $row->jumlah_mapel);
            $highestFrequency = $frequency->max();
            $expected = $frequency
                ->filter(fn ($count) => $count === $highestFrequency)
                ->keys()
                ->map(fn ($count) => (int) $count)
                ->max();

            return [(int) $sem => (int) $expected];
        })->all();

        $rows = $roster->map(function ($student) use (
            $byStudent,
            $requestedPeriods,
            $availableSemesters,
            $expectedMapelCounts
        ) {
            $semesterRows = $byStudent->get($student->siswa_id, collect())->keyBy(
                fn ($row) => (int) $row->semester
            );
            $semesterValues = [];
            $semesterMapelCounts = [];

            foreach (array_keys($requestedPeriods) as $sem) {
                $aggregate = $semesterRows->get((int) $sem);
                $semesterValues[$sem] = $aggregate ? round((float) $aggregate->rata_rata, 4) : null;
                $semesterMapelCounts[$sem] = $aggregate ? (int) $aggregate->jumlah_mapel : 0;
            }

            $valuesForRanking = collect($availableSemesters)
                ->map(fn ($sem) => $semesterValues[$sem] ?? null)
                ->filter(fn ($value) => $value !== null);
            $isComplete = count($availableSemesters) > 0
                && $valuesForRanking->count() === count($availableSemesters)
                && collect($availableSemesters)->every(
                    fn ($sem) => ($semesterMapelCounts[$sem] ?? 0) >= ($expectedMapelCounts[$sem] ?? 1)
                );

            return [
                'siswa_id' => $student->siswa_id,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'nama' => $student->nama_lengkap,
                'jenis_kelamin' => $student->jenis_kelamin,
                'kelas_id' => $student->kelas_id,
                'kelas' => $student->nama_kelas,
                'semester_values' => $semesterValues,
                'semester_mapel_counts' => $semesterMapelCounts,
                'score' => $valuesForRanking->isNotEmpty() ? round($valuesForRanking->avg(), 4) : null,
                'semester_complete' => $valuesForRanking->count(),
                'semester_expected' => count($availableSemesters),
                'is_complete' => $isComplete,
                'rank_grade' => null,
                'rank_class' => null,
            ];
        })->keyBy('siswa_id');

        $eligible = $rows->filter(fn ($row) => $row['is_complete'] && $row['score'] !== null);
        $gradeRanks = $this->competitionRanks($eligible);
        $classRanks = [];
        foreach ($eligible->groupBy('kelas_id') as $classRows) {
            $classRanks += $this->competitionRanks($classRows);
        }

        $rows = $rows->map(function ($row) use ($gradeRanks, $classRanks) {
            $row['rank_grade'] = $gradeRanks[$row['siswa_id']] ?? null;
            $row['rank_class'] = $classRanks[$row['siswa_id']] ?? null;
            return $row;
        })->values();

        return [
            'rows' => $rows,
            'periods' => $periods,
            'requested_periods' => $requestedPeriods,
            'available_semesters' => $availableSemesters,
            'expected_mapel_counts' => $expectedMapelCounts,
            'missing_semesters' => array_values(array_diff(array_keys($requestedPeriods), $availableSemesters)),
            'eligible_count' => $eligible->count(),
        ];
    }

    /**
     * Competition ranking: nilai sama mendapat peringkat sama (1, 1, 3).
     */
    private function competitionRanks(Collection $rows): array
    {
        $sorted = $rows->sort(function ($left, $right) {
            $scoreCompare = $right['score'] <=> $left['score'];
            return $scoreCompare !== 0 ? $scoreCompare : strcmp($left['nama'], $right['nama']);
        })->values();

        $ranks = [];
        $previousScore = null;
        $previousRank = 0;
        foreach ($sorted as $index => $row) {
            $scoreKey = number_format((float) $row['score'], 4, '.', '');
            $rank = $previousScore === $scoreKey ? $previousRank : $index + 1;
            $ranks[$row['siswa_id']] = $rank;
            $previousScore = $scoreKey;
            $previousRank = $rank;
        }

        return $ranks;
    }

    public function ranking(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->firstOrFail();
        $tingkat = in_array($request->integer('tingkat', 12), [10, 11, 12], true)
            ? $request->integer('tingkat', 12)
            : 12;
        $mode = $request->input('mode') === 'semester' ? 'semester' : 'cumulative';
        $semesterMax = count($this->getSemesterConfig($tingkat, $tahunAktif));
        $semester = min(max($request->integer('semester', $semesterMax), 1), $semesterMax);
        $kelasId = $request->input('kelas_id');

        $kelasList = Kelas::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('tingkat', $tingkat)
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        if ($kelasId && !$kelasList->contains('id', $kelasId)) {
            $kelasId = null;
        }

        $ranking = $this->buildRankingData($tahunAktif, $tingkat, $mode, $semester);
        $rows = $ranking['rows']
            ->when($kelasId, fn ($items) => $items->where('kelas_id', $kelasId))
            ->sortBy(function ($row) use ($kelasId) {
                $rank = $kelasId ? $row['rank_class'] : $row['rank_grade'];
                return sprintf('%08d|%s', $rank ?? PHP_INT_MAX, $row['nama']);
            })
            ->values();

        return view('admin.nilai.ranking', compact(
            'tahunAktif',
            'tingkat',
            'mode',
            'semester',
            'kelasId',
            'kelasList',
            'ranking',
            'rows'
        ));
    }

    public function exportRanking(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->firstOrFail();
        $tingkat = in_array($request->integer('tingkat', 12), [10, 11, 12], true)
            ? $request->integer('tingkat', 12)
            : 12;
        $mode = $request->input('mode') === 'semester' ? 'semester' : 'cumulative';
        $semesterMax = count($this->getSemesterConfig($tingkat, $tahunAktif));
        $semester = min(max($request->integer('semester', $semesterMax), 1), $semesterMax);
        $kelasId = $request->input('kelas_id');

        $kelas = $kelasId
            ? Kelas::where('tahun_pelajaran_id', $tahunAktif->id)->where('tingkat', $tingkat)->find($kelasId)
            : null;
        $ranking = $this->buildRankingData($tahunAktif, $tingkat, $mode, $semester);
        $rows = $ranking['rows']
            ->when($kelas, fn ($items) => $items->where('kelas_id', $kelas->id))
            ->sortBy(function ($row) use ($kelas) {
                $rank = $kelas ? $row['rank_class'] : $row['rank_grade'];
                return sprintf('%08d|%s', $rank ?? PHP_INT_MAX, $row['nama']);
            })
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Peringkat Nilai');
        $semesterColumns = $mode === 'semester' ? [$semester] : array_keys($ranking['requested_periods']);
        $headers = ['Peringkat', 'Peringkat Rombel', 'Peringkat Tingkat', 'NISN', 'NIS', 'Nama', 'L/P', 'Rombel'];
        foreach ($semesterColumns as $sem) {
            $headers[] = "Rata-rata S{$sem}";
            $headers[] = "Mapel S{$sem}";
        }
        $headers[] = $mode === 'semester' ? "Nilai Ranking S{$semester}" : 'Rata-rata S1-'.$semesterMax;
        $headers[] = 'Kelengkapan';

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        foreach ($rows as $index => $row) {
            $data = [
                $kelas ? $row['rank_class'] : $row['rank_grade'],
                $row['rank_class'],
                $row['rank_grade'],
                "'".$row['nisn'],
                $row['nis'],
                $row['nama'],
                $row['jenis_kelamin'],
                $row['kelas'],
            ];
            foreach ($semesterColumns as $sem) {
                $data[] = $row['semester_values'][$sem] !== null ? round($row['semester_values'][$sem], 2) : '';
                $data[] = $row['semester_mapel_counts'][$sem] ?? 0;
            }
            $data[] = $row['score'] !== null ? round($row['score'], 4) : '';
            $data[] = "{$row['semester_complete']}/{$row['semester_expected']}";

            foreach ($data as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $index + 2], $value);
            }
        }

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9EAD3');
        $sheet->freezePane('I2');
        foreach (range(1, count($headers)) as $column) {
            $dimension = $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column)
            );
            if ($column <= 8) {
                $dimension->setAutoSize(true);
            } else {
                $dimension->setWidth(14);
            }
        }

        $scope = $kelas ? str_replace(' ', '-', $kelas->nama_kelas) : "kelas-{$tingkat}";
        $modeName = $mode === 'semester' ? "semester-{$semester}" : 'semester-1-'.$semesterMax;
        $filename = "peringkat-{$scope}-{$modeName}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            try {
                $writer->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Display nilai index with tingkat filter
     */
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        $tingkat = $request->tingkat;
        $semesterList = [];
        $overviewStats = [];
        
        if ($tingkat) {
            $tingkat = (int) $tingkat;
            $activeRosterIds = $this->getActiveRosterStudentIds($tahunAktif, $tingkat);
            // Get semester config for selected tingkat
            $semesterConfig = $this->getSemesterConfig($tingkat, $tahunAktif);
            
            foreach ($semesterConfig as $sem => $config) {
                $tahunPelajaran = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
                
                $jumlahSiswa = 0;
                if ($tahunPelajaran) {
                    $jumlahSiswa = NilaiSiswa::where('semester', $sem)
                        ->where('tahun_pelajaran_id', $tahunPelajaran->id)
                        ->whereIn('siswa_id', $activeRosterIds)
                        ->distinct('siswa_id')
                        ->count('siswa_id');
                }
                
                $semesterList[$sem] = [
                    'label' => $config['label'],
                    'tahun_pelajaran' => $tahunPelajaran ? $tahunPelajaran->nama : 'Tidak ada',
                    'tahun_pelajaran_id' => $tahunPelajaran ? $tahunPelajaran->id : null,
                    'jumlah_siswa' => $jumlahSiswa,
                ];
            }
        } else {
            // Overview hanya roster aktif; nilai alumni tersedia melalui detail arsip.
            $overviewStats = [
                'kelas_12' => NilaiSiswa::whereIn('siswa_id', $this->getActiveRosterStudentIds($tahunAktif, 12))
                    ->whereIn('semester', [1,2,3,4,5])->distinct('siswa_id')->count('siswa_id'),
                'kelas_11' => NilaiSiswa::whereIn('siswa_id', $this->getActiveRosterStudentIds($tahunAktif, 11))
                    ->whereIn('semester', [1,2,3,4])->distinct('siswa_id')->count('siswa_id'),
                'kelas_10' => NilaiSiswa::whereIn('siswa_id', $this->getActiveRosterStudentIds($tahunAktif, 10))
                    ->whereIn('semester', [1,2])->distinct('siswa_id')->count('siswa_id'),
            ];
        }
        
        return view('admin.nilai.index', compact('tahunPelajarans', 'tahunAktif', 'semesterList', 'overviewStats'));
    }

    /**
     * Show nilai per semester
     */
    public function semester(Request $request, $semester)
    {
        $semester = (int) $semester;
        
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tingkat = $request->integer('tingkat');
        
        // Jika ada request tahun_pelajaran_id, gunakan itu
        $selectedTahun = null;
        if ($request->tahun_pelajaran_id) {
            $selectedTahun = TahunPelajaran::find($request->tahun_pelajaran_id);
        } else {
            // Dari halaman leger aktif, tahun ditentukan oleh tingkat + posisi semester.
            // Contoh kelas XII semester 1 selalu dua tahun sebelum tahun aktif.
            $semesterConfig = $tingkat ? $this->getSemesterConfig($tingkat, $tahunAktif, true) : [];
            if (isset($semesterConfig[$semester])) {
                $selectedTahun = $this->getTahunPelajaranByOffset(
                    $tahunAktif,
                    $semesterConfig[$semester]['offset']
                );
            }

            // Mode arsip tanpa konteks tingkat: pilih tahun terbaru yang memiliki nilai.
            if (!$selectedTahun) {
                $selectedTahun = TahunPelajaran::query()
                    ->whereIn('id', NilaiSiswa::query()
                        ->where('semester', $semester)
                        ->select('tahun_pelajaran_id'))
                    ->orderByDesc('tahun_mulai')
                    ->first() ?: $tahunAktif;
            }

            if ($selectedTahun && !$request->ajax()) {
                return redirect()->route('admin.nilai.semester', array_filter([
                    'semester' => $semester,
                    'tahun_pelajaran_id' => $selectedTahun->id,
                    'tingkat' => $tingkat ?: null,
                ]));
            }
        }
        
        $semesterLabel = NilaiSiswa::SEMESTER_LABELS[$semester] ?? "Semester {$semester}";
        
        // Gunakan mapel yang benar-benar tersimpan agar kode Merdeka (M-*) tidak
        // dipaksa cocok dengan daftar kode lama. Config hanya menjadi fallback saat kosong.
        $mapelList = $selectedTahun
            ? $this->getActualMapelList([$semester => $selectedTahun->id])
            : collect();
        $urutanMapel = $mapelList->pluck('kode_mapel')->all();
        
        if ($request->ajax()) {
            return $this->getSemesterData($request, $semester, $selectedTahun, $urutanMapel);
        }
        
        return view('admin.nilai.semester', compact(
            'semester', 
            'semesterLabel', 
            'tahunPelajarans', 
            'selectedTahun',
            'mapelList',
            'urutanMapel'
        ));
    }

    /**
     * Get data for DataTable
     */
    private function getSemesterData(Request $request, $semester, $selectedTahun, $urutanMapel = null)
    {
        $semester = (int) $semester;
        
        if ($urutanMapel === null) {
            $urutanMapel = $selectedTahun
                ? $this->getActualMapelList([$semester => $selectedTahun->id], false)->pluck('kode_mapel')->all()
                : [];
        }
        
        // Get siswa yang punya nilai di semester ini
        $siswaIds = NilaiSiswa::where('semester', $semester)
            ->when($selectedTahun, function ($q) use ($selectedTahun) {
                return $q->where('tahun_pelajaran_id', $selectedTahun->id);
            })
            ->distinct()
            ->pluck('siswa_id');
        
        $query = Siswa::whereIn('id', $siswaIds)
            ->with(['nilaiSiswa' => function ($q) use ($semester, $selectedTahun) {
                $q->where('semester', $semester)
                    ->when($selectedTahun, function ($q2) use ($selectedTahun) {
                        return $q2->where('tahun_pelajaran_id', $selectedTahun->id);
                    })
                    ->with('mataPelajaran');
            }])
            ->orderBy('nama_lengkap');

        $tingkat = $request->integer('tingkat');
        if ($tingkat) {
            $query->whereIn('id', $this->getActiveRosterStudentIds(
                TahunPelajaran::where('is_active', true)->first(),
                $tingkat
            ));
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nisn', fn($siswa) => $siswa->nisn)
            ->addColumn('nama', fn($siswa) => $siswa->nama_lengkap)
            ->addColumn('nilai_list', function ($siswa) use ($urutanMapel) {
                $nilai = [];
                // Urutkan sesuai config
                foreach ($urutanMapel as $kode) {
                    $found = $siswa->nilaiSiswa->first(fn($n) => $n->mataPelajaran && $n->mataPelajaran->kode_mapel === $kode);
                    $nilai[$kode] = $found ? $found->nilai : null;
                }
                return $nilai;
            })
            ->addColumn('rata_rata', function ($siswa) {
                $nilaiList = $siswa->nilaiSiswa->pluck('nilai')->filter();
                return $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : '-';
            })
            ->addColumn('action', function ($siswa) use ($semester, $request, $selectedTahun) {
                $params = ['semester' => $semester];
                if ($request->filled('tingkat')) {
                    $params['tingkat'] = $request->tingkat;
                }
                if ($selectedTahun) {
                    $params['tahun_pelajaran_id'] = $selectedTahun->id;
                }

                return '<a href="' . route('admin.nilai.siswa', array_merge([$siswa->id], $params)) . '" 
                    class="btn btn-sm btn-info" title="Detail">
                    <i class="fas fa-eye"></i>
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show form upload excel
     */
    public function uploadForm(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        return view('admin.nilai.upload', compact('tahunPelajarans', 'tahunAktif'));
    }

    /**
     * Download template Excel untuk upload nilai
     */
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'semester' => 'required|integer|min:1|max:6',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        $semester = (int) $request->semester;
        $tahunPelajaran = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);
        $mapelList = $this->getTemplateMapelList($semester, $tahunPelajaran);

        if ($mapelList->isEmpty()) {
            return back()->with('error', 'Mapel untuk periode ini belum dapat dideteksi. Pastikan mapping RDM sudah tersedia.');
        }

        $urutanMapel = $mapelList->pluck('kode_mapel')->all();
        $mapels = $mapelList->keyBy('kode_mapel');
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Nilai');
        
        // Header row
        $headers = ['No', 'NIS', 'NISN', 'Nama', 'JK'];
        foreach ($urutanMapel as $kode) {
            $headers[] = $kode;
        }
        
        // Write header
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('CCCCCC');
            $col++;
        }
        
        // Sample data row
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', '12345');
        $sheet->setCellValue('C2', '0012345678');
        $sheet->setCellValue('D2', 'Nama Siswa');
        $sheet->setCellValue('E2', 'L');
        
        // Auto width - use column index for multi-letter columns (AA, AB, etc)
        $lastColIndex = count($headers);
        for ($i = 1; $i <= $lastColIndex; $i++) {
            $columnID = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Add second sheet with mapel reference
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Kode Mapel');
        $refSheet->setCellValue('A1', 'No');
        $refSheet->setCellValue('B1', 'Kode');
        $refSheet->setCellValue('C1', 'Nama Mapel');
        $refSheet->setCellValue('D1', 'Kurikulum');
        $refSheet->getStyle('A1:D1')->getFont()->setBold(true);
        
        $row = 2;
        foreach ($urutanMapel as $index => $kode) {
            $mapel = $mapels[$kode] ?? null;
            $refSheet->setCellValue('A' . $row, $index + 1);
            $refSheet->setCellValue('B' . $row, $kode);
            $refSheet->setCellValue('C' . $row, $mapel ? $mapel->nama_mapel : '-');
            $refSheet->setCellValue('D' . $row, $mapel?->kurikulum?->kode ?? '-');
            $row++;
        }
        
        $refSheet->getColumnDimension('A')->setAutoSize(true);
        $refSheet->getColumnDimension('B')->setAutoSize(true);
        $refSheet->getColumnDimension('C')->setAutoSize(true);
        $refSheet->getColumnDimension('D')->setAutoSize(true);

        $refSheet->setCellValue('F1', 'Semester');
        $refSheet->setCellValue('G1', $semester);
        $refSheet->setCellValue('F2', 'Tahun Pelajaran');
        $refSheet->setCellValue('G2', $tahunPelajaran->nama);
        
        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);
        
        $tahunSafe = str_replace('/', '-', $tahunPelajaran->nama);
        $filename = "template_nilai_semester_{$semester}_{$tahunSafe}.xlsx";
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    /**
     * Process upload excel - Preview data sebelum disimpan
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'semester' => 'required|integer|min:1|max:5',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        $spreadsheet = null;

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File Excel kosong atau format tidak sesuai.');
            }

            $semester = (int) $request->semester;
            
            $kolomNisn = config('nilai.kolom_nisn', 2);
            $kolomNilaiMulai = config('nilai.kolom_nilai_mulai', 5);

            // Kolom mapel dibaca dari header, bukan posisi config K13 lama.
            $header = $rows[0] ?? [];
            $urutanMapel = collect(array_slice($header, $kolomNilaiMulai))
                ->map(fn ($kode) => mb_strtoupper(trim((string) $kode)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!$urutanMapel) {
                return back()->with('error', 'Header mapel tidak ditemukan mulai kolom F. Gunakan template sesuai semester dan tahun pelajaran.');
            }

            $mapelByKode = MataPelajaran::withTrashed()
                ->whereIn('kode_mapel', $urutanMapel)
                ->get()
                ->keyBy('kode_mapel');
            
            // Validasi apakah semua mapel ada di database
            $missingMapel = [];
            foreach ($urutanMapel as $kode) {
                if (!isset($mapelByKode[$kode])) {
                    $missingMapel[] = $kode;
                }
            }
            
            if (!empty($missingMapel)) {
                return back()->with('error', 'Kode mapel pada header Excel tidak ditemukan: ' . implode(', ', $missingMapel) . '. Download ulang template untuk periode yang dipilih.');
            }

            // Baris data dimulai dari config
            $barisDataMulai = config('nilai.baris_data_mulai', 2);
            $dataStartRow = $barisDataMulai - 1;

            $tahunPelajaranId = $request->tahun_pelajaran_id;
            $tahunPelajaran = TahunPelajaran::find($tahunPelajaranId);
            
            // Parse data untuk preview
            $previewData = [];
            $notFoundNisn = [];
            $foundCount = 0;

            for ($i = $dataStartRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nisn = trim(strval($row[$kolomNisn] ?? ''));
                
                if (empty($nisn) || !is_numeric($nisn)) continue;

                $siswa = Siswa::where('nisn', $nisn)->first();
                
                if (!$siswa) {
                    $notFoundNisn[] = $nisn;
                    continue;
                }

                $nilaiSiswa = [];
                $nilaiCount = 0;
                
                for ($mapelIndex = 0; $mapelIndex < count($urutanMapel); $mapelIndex++) {
                    $kodeMapel = $urutanMapel[$mapelIndex];
                    $colIndex = $kolomNilaiMulai + $mapelIndex;
                    $nilai = $row[$colIndex] ?? null;
                    $nilaiStr = trim(strval($nilai));
                    
                    if ($nilaiStr !== '' && is_numeric($nilaiStr)) {
                        $nilaiSiswa[$kodeMapel] = floatval($nilaiStr);
                        $nilaiCount++;
                    } else {
                        $nilaiSiswa[$kodeMapel] = null;
                    }
                }
                
                if ($nilaiCount > 0) {
                    $previewData[] = [
                        'siswa_id' => $siswa->id,
                        'nisn' => $siswa->nisn,
                        'nama' => $siswa->nama_lengkap,
                        'nilai' => $nilaiSiswa,
                        'jumlah_mapel' => $nilaiCount,
                    ];
                    $foundCount++;
                }
            }

            if (empty($previewData)) {
                return back()->with('error', 'Tidak ada data nilai yang valid untuk diimport.');
            }

            // Simpan ke session untuk digunakan saat confirm
            session([
                'nilai_preview' => [
                    'data' => $previewData,
                    'semester' => $semester,
                    'tahun_pelajaran_id' => $tahunPelajaranId,
                    'tahun_pelajaran_nama' => $tahunPelajaran->nama,
                    'not_found_nisn' => $notFoundNisn,
                    'urutan_mapel' => $urutanMapel,
                ]
            ]);

            return redirect()->route('admin.nilai.preview');

        } catch (\Exception $e) {
            Log::error('Error parsing nilai: ' . $e->getMessage());
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        } finally {
            $spreadsheet?->disconnectWorksheets();
        }
    }

    /**
     * Show preview before saving
     */
    public function preview()
    {
        $preview = session('nilai_preview');
        
        if (!$preview) {
            return redirect()->route('admin.nilai.upload-form')
                ->with('error', 'Tidak ada data untuk di-preview. Silakan upload file Excel terlebih dahulu.');
        }
        
        $semesterLabel = NilaiSiswa::SEMESTER_LABELS[$preview['semester']] ?? "Semester {$preview['semester']}";
        
        return view('admin.nilai.preview', [
            'previewData' => $preview['data'],
            'semester' => $preview['semester'],
            'semesterLabel' => $semesterLabel,
            'tahunPelajaranNama' => $preview['tahun_pelajaran_nama'],
            'notFoundNisn' => $preview['not_found_nisn'],
            'urutanMapel' => $preview['urutan_mapel'],
            'totalSiswa' => count($preview['data']),
            'totalNilai' => array_sum(array_column($preview['data'], 'jumlah_mapel')),
        ]);
    }

    /**
     * Confirm and save uploaded data
     */
    public function confirmUpload(Request $request)
    {
        $preview = session('nilai_preview');
        
        if (!$preview) {
            return redirect()->route('admin.nilai.upload-form')
                ->with('error', 'Session expired. Silakan upload ulang file Excel.');
        }

        try {
            DB::beginTransaction();

            $semester = $preview['semester'];
            $tahunPelajaranId = $preview['tahun_pelajaran_id'];
            $urutanMapel = $preview['urutan_mapel'];
            $importedAt = now();
            
            // Get mapel
            $mapelByKode = MataPelajaran::whereIn('kode_mapel', $urutanMapel)
                ->where('is_active', true)
                ->get()
                ->keyBy('kode_mapel');

            $nilaiCount = 0;
            $updatedCount = 0;
            $successCount = 0;

            foreach ($preview['data'] as $item) {
                $siswaHasNilai = false;
                
                foreach ($item['nilai'] as $kodeMapel => $nilai) {
                    if ($nilai === null) continue;
                    
                    $mapel = $mapelByKode[$kodeMapel] ?? null;
                    if (!$mapel) continue;
                    
                    // Cari existing record termasuk yang soft deleted
                    $existingNilai = NilaiSiswa::withTrashed()
                        ->where('siswa_id', $item['siswa_id'])
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->where('semester', $semester)
                        ->first();
                    
                    if ($existingNilai) {
                        // Restore jika soft deleted dan update
                        if ($existingNilai->trashed()) {
                            $existingNilai->restore();
                        }
                        $existingNilai->update([
                            'nilai' => $nilai,
                            'predikat' => NilaiSiswa::hitungPredikat($nilai),
                            'sumber_data' => 'import_excel',
                            'imported_at' => $importedAt,
                        ]);
                        $updatedCount++;
                    } else {
                        // Create baru
                        NilaiSiswa::create([
                            'siswa_id' => $item['siswa_id'],
                            'mata_pelajaran_id' => $mapel->id,
                            'tahun_pelajaran_id' => $tahunPelajaranId,
                            'semester' => $semester,
                            'nilai' => $nilai,
                            'predikat' => NilaiSiswa::hitungPredikat($nilai),
                            'sumber_data' => 'import_excel',
                            'imported_at' => $importedAt,
                        ]);
                    }
                    $nilaiCount++;
                    $siswaHasNilai = true;
                }
                
                if ($siswaHasNilai) {
                    $successCount++;
                }
            }

            DB::commit();
            
            // Clear session
            session()->forget('nilai_preview');
            
            $message = "Berhasil menyimpan {$nilaiCount} nilai untuk {$successCount} siswa.";
            if ($updatedCount > 0) {
                $message .= " ({$updatedCount} data diperbarui)";
            }

            return redirect()->route('admin.nilai.semester', $semester)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving nilai: ' . $e->getMessage());
            return redirect()->route('admin.nilai.preview')
                ->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Cancel upload preview
     */
    public function cancelUpload()
    {
        session()->forget('nilai_preview');
        return redirect()->route('admin.nilai.upload-form')
            ->with('info', 'Upload dibatalkan.');
    }

    /**
     * Show nilai detail per siswa
     */
    public function siswa(Request $request, Siswa $siswa)
    {
        $semester = $request->semester;
        
        $nilaiList = NilaiSiswa::with(['mataPelajaran', 'tahunPelajaran'])
            ->where('siswa_id', $siswa->id)
            ->when($semester, function ($q) use ($semester) {
                return $q->where('semester', $semester);
            })
            ->orderBy('semester')
            ->get()
            ->groupBy('semester');
        
        return view('admin.nilai.siswa', compact('siswa', 'nilaiList', 'semester'));
    }

    /**
     * Delete nilai per semester
     */
    public function deleteSemester(Request $request, $semester)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        try {
            $deleted = NilaiSiswa::where('semester', $semester)
                ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deleted} data nilai semester {$semester}."
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting nilai: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview export nilai semester
     */
    public function exportSemesterPreview(Request $request, $semester)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nisn_list' => 'required|string',
            'mapel_list' => 'required|array|min:1',
        ]);

        $semester = (int) $semester;
        $tahunPelajaran = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);
        
        // Parse NISN dari textarea (per baris)
        $nisnList = array_filter(array_map('trim', explode("\n", $request->nisn_list)));
        
        if (empty($nisnList)) {
            return back()->with('error', 'NISN tidak boleh kosong.');
        }

        // Get mapel yang dipilih dengan urutan sesuai checklist
        $mapelCodes = $request->mapel_list;

        // Get nilai siswa berdasarkan urutan NISN
        $exportData = [];
        $notFoundNisn = [];
        $noNilaiNisn = [];
        
        foreach ($nisnList as $nisn) {
            $nisn = trim($nisn);
            if (empty($nisn)) continue;
            
            $siswa = Siswa::where('nisn', $nisn)->first();
            
            if (!$siswa) {
                $notFoundNisn[] = $nisn;
                $row = ['nisn' => $nisn, 'nama' => 'TIDAK DITEMUKAN', 'found' => false, 'total_nilai_semester' => 0, 'total_mapel_semester' => 0];
                foreach ($mapelCodes as $kode) {
                    $row[$kode] = '';
                }
                $exportData[] = $row;
                continue;
            }
            
            // Get SEMUA nilai siswa untuk semester ini (untuk total nilai & mapel)
            $semuaNilaiSiswa = NilaiSiswa::where('siswa_id', $siswa->id)
                ->where('semester', $semester)
                ->where('tahun_pelajaran_id', $tahunPelajaran->id)
                ->with('mataPelajaran')
                ->get();
            
            // Hitung total nilai dan mapel di semester ini
            $totalNilaiSemester = $semuaNilaiSiswa->sum('nilai');
            $totalMapelSemester = $semuaNilaiSiswa->count();
            
            $row = [
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama_lengkap,
                'found' => true,
                'total_nilai_semester' => $totalNilaiSemester,
                'total_mapel_semester' => $totalMapelSemester,
            ];
            
            $hasNilai = false;
            foreach ($mapelCodes as $kode) {
                $nilai = $semuaNilaiSiswa->first(fn($n) => $n->mataPelajaran && $n->mataPelajaran->kode_mapel === $kode);
                $row[$kode] = $nilai ? $nilai->nilai : '';
                if ($nilai) $hasNilai = true;
            }
            
            if (!$hasNilai) {
                $noNilaiNisn[] = $nisn;
            }
            
            $exportData[] = $row;
        }

        // Hitung statistik per mapel
        $mapelStats = [];
        foreach ($mapelCodes as $kode) {
            $count = 0;
            foreach ($exportData as $row) {
                if (!empty($row[$kode]) && $row[$kode] !== '') {
                    $count++;
                }
            }
            $mapelStats[$kode] = $count;
        }

        $semesterLabel = NilaiSiswa::SEMESTER_LABELS[$semester] ?? "Semester {$semester}";
        
        // Simpan data ke session untuk download
        session([
            'export_nilai_data' => $exportData,
            'export_nilai_mapel' => $mapelCodes,
            'export_nilai_stats' => $mapelStats,
            'export_nilai_semester' => $semester,
            'export_nilai_tahun' => $tahunPelajaran->nama,
            'export_nilai_tahun_id' => $tahunPelajaran->id,
        ]);

        return view('admin.nilai.export-preview', compact(
            'exportData', 
            'mapelCodes', 
            'mapelStats', 
            'semester', 
            'semesterLabel',
            'tahunPelajaran',
            'notFoundNisn',
            'noNilaiNisn'
        ));
    }

    /**
     * Download export nilai semester dari session
     */
    public function exportSemesterDownload(Request $request, $semester)
    {
        $exportData = session('export_nilai_data');
        $mapelCodes = session('export_nilai_mapel');
        $mapelStats = session('export_nilai_stats');
        $tahunNama = session('export_nilai_tahun');
        
        if (!$exportData || !$mapelCodes) {
            return redirect()->route('admin.nilai.semester', $semester)
                ->with('error', 'Data export tidak ditemukan. Silakan ulangi proses export.');
        }

        // Generate filename
        $tahunNamaSafe = str_replace(['/', '\\'], '-', $tahunNama);
        $filename = "nilai_semester_{$semester}_{$tahunNamaSafe}_" . date('Y-m-d_His') . ".xlsx";
        
        // Clear session
        session()->forget(['export_nilai_data', 'export_nilai_mapel', 'export_nilai_stats', 'export_nilai_semester', 'export_nilai_tahun', 'export_nilai_tahun_id']);
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\NilaiSemesterExport($exportData, $mapelCodes, $mapelStats, $semester, $tahunNama),
            $filename
        );
    }

    /**
     * Show export legger form
     */
    public function exportLeggerForm(Request $request)
    {
        $tingkat = $request->tingkat ?? 12;
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }
        
        $semesterConfig = $this->getSemesterConfig($tingkat, $tahunAktif);
        $periods = [];
        foreach ($semesterConfig as $semester => $config) {
            $tahunPelajaran = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
            if ($tahunPelajaran) {
                $periods[$semester] = $tahunPelajaran->id;
            }
        }
        $mapelList = $this->getActualMapelList($periods);
        $urutanMapel = $mapelList->pluck('kode_mapel')->all();
        
        // Get kelas tingkat 12 dengan count siswa
        $kelasList = \App\Models\Kelas::where('tingkat', $tingkat)
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->withCount('siswas')
            ->orderBy('nama_kelas')
            ->get();
        
        // Count siswa kelas 12
        $totalSiswa = Siswa::whereHas('kelasAktif', function($q) use ($tingkat, $tahunAktif) {
            $q->where('kelas.tingkat', $tingkat)
              ->where('kelas.tahun_pelajaran_id', $tahunAktif->id)
              ->whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id');
        })->count();
        
        return view('admin.nilai.export-legger', compact(
            'tingkat', 
            'tahunAktif', 
            'semesterConfig', 
            'mapelList', 
            'kelasList',
            'totalSiswa',
            'urutanMapel'
        ));
    }

    /**
     * Export Legger untuk SPAN-PTKIN/SNBP
     */
    public function exportLegger(Request $request)
    {
        $tingkat = $request->tingkat ?? 12;
        $alumniContext = $request->attributes->get('alumni_legger_context');
        $tahunAktif = $alumniContext['tahun_pelajaran'] ?? TahunPelajaran::where('is_active', true)->first();
        
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }
        
        $semesterConfig = $this->getSemesterConfig(
            $tingkat,
            $tahunAktif,
            $request->boolean('include_semester_6')
        );
        
        // Get selected kelas atau semua
        $selectedKelas = $request->kelas ?? [];
        
        // Collect tahun pelajaran IDs for all semesters
        $tahunIds = [];
        foreach ($semesterConfig as $sem => $config) {
            $tahunPelajaran = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
            if ($tahunPelajaran) {
                $tahunIds[$sem] = $tahunPelajaran->id;
            }
        }

        $availableMapels = $this->getActualMapelList($tahunIds);
        $availableMapelsByCode = $availableMapels->keyBy('kode_mapel');
        $requestedMapels = is_array($request->mapel) ? $request->mapel : [];
        $selectedMapel = $requestedMapels
            ? collect($requestedMapels)->filter(fn ($kode) => $availableMapelsByCode->has($kode))->values()->all()
            : $availableMapels->pluck('kode_mapel')->all();
        // EloquentCollection::only() membaca primary key model, bukan kode mapel.
        // Ubah menjadi collection biasa supaya kode seperti QH/AA/FIK dipilih benar.
        $mapels = collect($availableMapelsByCode->all())->only($selectedMapel);
        
        if ($alumniContext) {
            $siswaList = $alumniContext['siswa_list'] ?? collect([$alumniContext['siswa']]);
            $siswaList->each(function (Siswa $siswa) use ($alumniContext) {
                $kelas = $alumniContext['kelas_by_siswa'][$siswa->id] ?? $alumniContext['kelas'] ?? null;
                $siswa->setRelation('kelas', collect($kelas ? [$kelas] : []));
            });
        } else {
            // Get siswa kelas 12 (dari kelas, bukan dari nilai)
            $siswaQuery = Siswa::whereHas('kelasAktif', function($q) use ($tingkat, $tahunAktif, $selectedKelas) {
                $q->where('kelas.tingkat', $tingkat)
                  ->where('kelas.tahun_pelajaran_id', $tahunAktif->id)
                  ->whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id');
                if (!empty($selectedKelas)) {
                    $q->whereIn('kelas.id', $selectedKelas);
                }
            })
            ->with(['kelas' => function($q) use ($tahunAktif) {
                $q->where('kelas.tahun_pelajaran_id', $tahunAktif->id);
            }])
            ->orderBy('nama_lengkap');

            $siswaList = $siswaQuery->get();
        }
        
        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa kelas ' . $tingkat . ' yang ditemukan.');
        }
        
        $siswaIds = $siswaList->pluck('id');
        
        $nilaiLookup = $this->getLeggerNilaiLookup($siswaIds, $tahunIds);
        
        // Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Legger Kelas ' . $tingkat);
        
        // Build multi-row header
        // Row 1: Mapel names (merged cells)
        // Row 2: Semester numbers
        
        $staticCols = ['No', 'NISN', 'NIS', 'Nama', 'L/P', 'Kelas'];
        $staticColCount = count($staticCols);
        $semesterCount = count($semesterConfig);
        
        // Write static column headers (merged row 1-2)
        $col = 'A';
        foreach ($staticCols as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->mergeCells($col . '1:' . $col . '2');
            $sheet->getStyle($col . '1:' . $col . '2')->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        
        // Write mapel headers with semester sub-headers
        $colIndex = $staticColCount;
        foreach ($selectedMapel as $kode) {
            $mapel = $mapels[$kode] ?? null;
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $semesterCount + 1); // +1 for AVG
            
            // Row 1: Mapel name
            $sheet->setCellValue($startCol . '1', $mapel?->nama_mapel ?: $kode);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
            $sheet->getStyle($startCol . '1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            
            // Row 2: Semester numbers
            $semCol = $colIndex;
            foreach (array_keys($semesterConfig) as $sem) {
                $semColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($semCol + 1);
                $sheet->setCellValue($semColStr . '2', "S{$sem}");
                $sheet->getStyle($semColStr . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $semCol++;
            }
            // AVG column
            $avgColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($semCol + 1);
            $sheet->setCellValue($avgColStr . '2', 'Avg');
            $sheet->getStyle($avgColStr . '2')->getFont()->setBold(true);
            
            $colIndex += $semesterCount + 1;
        }
        
        // Total AVG column
        $totalAvgCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($totalAvgCol . '1', 'RATA2');
        $sheet->mergeCells($totalAvgCol . '1:' . $totalAvgCol . '2');
        $sheet->getStyle($totalAvgCol . '1')->getFont()->setBold(true);
        $sheet->getStyle($totalAvgCol . '1')->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Style header rows
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->getStyle('A1:' . $lastCol . '2')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9EAD3');
        $sheet->getStyle('A1:' . $lastCol . '2')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getRowDimension(1)->setRowHeight(32);
        
        // Write data starting from row 3
        $row = 3;
        foreach ($siswaList as $index => $siswa) {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $index + 1);
            $sheet->setCellValue($col++ . $row, "'" . $siswa->nisn); // Force text
            $sheet->setCellValue($col++ . $row, $siswa->nis ?? '');
            $sheet->setCellValue($col++ . $row, $siswa->nama_lengkap);
            $sheet->setCellValue($col++ . $row, $siswa->jenis_kelamin == 'L' ? 'L' : 'P');
            $sheet->setCellValue($col++ . $row, $siswa->kelas->first()->nama_kelas ?? '-');
            
            $totalNilai = 0;
            $totalCount = 0;
            
            foreach ($selectedMapel as $kode) {
                $mapel = $mapels[$kode] ?? null;
                $mapelNilai = [];
                
                foreach (array_keys($semesterConfig) as $sem) {
                    $nilai = $mapel
                        ? ($nilaiLookup[$this->leggerNilaiKey($siswa->id, $sem, $kode)] ?? null)
                        : null;
                    $sheet->setCellValue($col++ . $row, $nilai !== null ? round($nilai, 0) : '');
                    if ($nilai !== null) {
                        $mapelNilai[] = $nilai;
                    }
                }
                
                // Average per mapel
                $avg = count($mapelNilai) > 0 ? round(array_sum($mapelNilai) / count($mapelNilai), 2) : null;
                $sheet->setCellValue($col++ . $row, $avg ?? '');
                
                if ($avg !== null) {
                    $totalNilai += $avg;
                    $totalCount++;
                }
            }
            
            // Total average
            $totalAvg = $totalCount > 0 ? round($totalNilai / $totalCount, 2) : null;
            $sheet->setCellValue($col . $row, $totalAvg ?? '');
            
            $row++;
        }
        
        // Add borders to data
        $lastDataRow = $row - 1;
        $sheet->getStyle('A3:' . $lastCol . $lastDataRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Auto width for static columns
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // Set width for nilai columns
        for ($i = 7; $i <= $colIndex + 1; $i++) {
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colStr)->setWidth(5);
        }
        
        // Freeze header rows and first columns
        $sheet->freezePane('G3');
        
        // Add summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Info Legger');
        $summarySheet->setCellValue('A1', 'Informasi Legger');
        $summarySheet->setCellValue('A3', 'Tingkat Kelas');
        $summarySheet->setCellValue('B3', $tingkat);
        $summarySheet->setCellValue('A4', $alumniContext ? 'Tahun Kelulusan' : 'Tahun Pelajaran Aktif');
        $summarySheet->setCellValue('B4', $tahunAktif->nama);
        $summarySheet->setCellValue('A5', 'Jumlah Siswa');
        $summarySheet->setCellValue('B5', $siswaList->count());
        $summarySheet->setCellValue('A6', 'Tanggal Export');
        $summarySheet->setCellValue('B6', now()->format('d-m-Y H:i:s'));
        
        $summarySheet->setCellValue('A8', 'Mapping Semester:');
        $rowSum = 9;
        foreach ($semesterConfig as $sem => $config) {
            $tahunPelajaran = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
            $summarySheet->setCellValue('A' . $rowSum, "Semester {$sem}");
            $summarySheet->setCellValue('B' . $rowSum, $config['label']);
            $summarySheet->setCellValue('C' . $rowSum, $tahunPelajaran ? $tahunPelajaran->nama : 'Tidak ada');
            $rowSum++;
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $filenamePrefix = $alumniContext
            ? ($alumniContext['filename_prefix'] ?? ('legger_alumni_' . $alumniContext['alumni']->nisn))
            : "legger_kelas_{$tingkat}";
        $isXls = ($alumniContext['format'] ?? 'xlsx') === 'xls';
        $filename = $filenamePrefix . '_' . date('Y-m-d_His') . ($isXls ? '.xls' : '.xlsx');

        $writer = $isXls ? new Xls($spreadsheet) : new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            try {
                $writer->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, [
            'Content-Type' => $isXls ? 'application/vnd.ms-excel' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Export leger lengkap seorang alumni dari riwayat kelas XII-nya. */
    public function exportAlumniLegger(Request $request, AlumniProfile $alumni)
    {
        $this->authorize('view-siswa');
        $alumni->load('siswa');

        if (!$alumni->siswa) {
            return back()->with('error', 'Legger hanya tersedia untuk alumni yang masih terhubung ke data siswa SIMANSA.');
        }

        $kelasXii = $alumni->siswa->siswaKelasRecords()
            ->with(['kelas', 'tahunPelajaran'])
            ->whereHas('kelas', fn ($query) => $query->where('tingkat', 12))
            ->orderByDesc('tanggal_keluar')
            ->first();

        if (!$kelasXii?->kelas || !$kelasXii->tahunPelajaran) {
            return back()->with('error', 'Riwayat kelas XII alumni tidak ditemukan, sehingga leger tidak dapat dibuat.');
        }

        $request->merge([
            'tingkat' => 12,
            'include_semester_6' => $request->boolean('include_semester_6', true),
        ]);
        $request->attributes->set('alumni_legger_context', [
            'alumni' => $alumni,
            'siswa' => $alumni->siswa,
            'kelas' => $kelasXii->kelas,
            'tahun_pelajaran' => $kelasXii->tahunPelajaran,
        ]);

        return $this->exportLegger($request);
    }

    /** Export satu file XLS berisi leger seluruh alumni pada kohor kelulusan yang dipilih. */
    public function exportAlumniLeggerBulk(Request $request)
    {
        $this->authorize('view-siswa');
        // Writer XLS membutuhkan waktu lebih panjang untuk kohor besar karena format
        // lama ini menyusun seluruh sel sebelum pengunduhan dimulai.
        @set_time_limit(300);
        $request->validate(['tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id']]);
        $tahunPelajaran = TahunPelajaran::findOrFail($request->input('tahun_pelajaran_id'));

        $profiles = AlumniProfile::query()
            ->with('siswa')
            ->where('angkatan', $tahunPelajaran->nama)
            ->whereNotNull('siswa_id')
            ->orderBy('nama_lengkap')
            ->get();

        $siswaIds = $profiles->pluck('siswa_id')->filter()->values();
        $kelasXiiBySiswa = SiswaKelas::query()
            ->with('kelas')
            ->whereIn('siswa_id', $siswaIds)
            ->where('tahun_pelajaran_id', $tahunPelajaran->id)
            ->whereHas('kelas', fn ($query) => $query->where('tingkat', 12))
            ->get()
            ->keyBy('siswa_id');

        $siswaList = $profiles
            ->map(fn (AlumniProfile $profile) => $profile->siswa)
            ->filter(fn (?Siswa $siswa) => $siswa && $kelasXiiBySiswa->has($siswa->id))
            ->values();

        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada alumni terhubung dengan riwayat kelas XII pada tahun yang dipilih.');
        }

        $request->merge(['tingkat' => 12, 'include_semester_6' => true]);
        $request->attributes->set('alumni_legger_context', [
            'siswa_list' => $siswaList,
            'kelas_by_siswa' => $kelasXiiBySiswa->mapWithKeys(fn (SiswaKelas $record) => [$record->siswa_id => $record->kelas])->all(),
            'tahun_pelajaran' => $tahunPelajaran,
            'filename_prefix' => 'legger_alumni_' . str_replace('/', '-', $tahunPelajaran->nama),
            'format' => 'xls',
        ]);

        return $this->exportLegger($request);
    }

    /**
     * Export legger format SPAN-PTKIN
     * Format: Semester 1 | mapel1, mapel2... | Jumlah Mapel | Total Nilai | Semester 2 | ...
     */
    public function exportSpan(Request $request)
    {
        $tingkat = 12; // SPAN hanya untuk kelas 12
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }
        
        $semesterConfig = $this->getSemesterConfig($tingkat, $tahunAktif);
        
        // Collect tahun pelajaran IDs for all semesters
        $tahunIds = [];
        foreach ($semesterConfig as $sem => $config) {
            $tahunPelajaran = $this->getTahunPelajaranByOffset($tahunAktif, $config['offset']);
            if ($tahunPelajaran) {
                $tahunIds[$sem] = $tahunPelajaran->id;
            }
        }

        // Setiap semester dapat mempunyai struktur mapel berbeda. Gunakan mapel
        // aktual per periode agar export tidak membuat kolom kosong dari kode lama.
        $mapelsBySemester = collect();
        foreach ($tahunIds as $sem => $tahunPelajaranId) {
            $mapelsBySemester[$sem] = $this->getActualMapelList(
                [$sem => $tahunPelajaranId],
                false
            );
        }
        
        // Get siswa kelas 12
        $siswaList = Siswa::whereHas('kelasAktif', function($q) use ($tingkat, $tahunAktif) {
            $q->where('kelas.tingkat', $tingkat)
              ->where('kelas.tahun_pelajaran_id', $tahunAktif->id)
              ->whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id');
        })
        ->with(['kelas' => function($q) use ($tahunAktif) {
            $q->where('kelas.tahun_pelajaran_id', $tahunAktif->id);
        }])
        ->orderBy('nama_lengkap')
        ->get();
        
        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa kelas 12 yang ditemukan.');
        }
        
        $siswaIds = $siswaList->pluck('id');
        
        $nilaiLookup = $this->getLeggerNilaiLookup($siswaIds, $tahunIds);
        
        // Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Legger SPAN-PTKIN');
        
        // Mapping semester ke kelas
        $semesterKelas = [
            1 => 'Kelas 10 Semester 1',
            2 => 'Kelas 10 Semester 2',
            3 => 'Kelas 11 Semester 1',
            4 => 'Kelas 11 Semester 2',
            5 => 'Kelas 12 Semester 1',
        ];
        
        // Static columns
        $staticCols = ['No', 'NISN', 'Nama Lengkap'];
        $staticColCount = count($staticCols);
        
        // Row 1: Static headers (merged 1-2) + Semester headers (merged across mapel columns)
        // Row 2: Static headers (merged) + Mapel names + Jumlah + Total
        
        // Write static column headers (merged row 1-2)
        $col = 'A';
        foreach ($staticCols as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->mergeCells($col . '1:' . $col . '2');
            $sheet->getStyle($col . '1:' . $col . '2')->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        
        // Write semester headers (Row 1) and mapel headers (Row 2)
        $colIndex = $staticColCount + 1; // 1-based index for Coordinate functions
        
        foreach ($semesterConfig as $sem => $config) {
            $semLabel = $semesterKelas[$sem] ?? "Semester {$sem}";
            $semesterMapels = $mapelsBySemester->get($sem, collect());
            $colsPerSemester = $semesterMapels->count() + 2;
            
            // Calculate start and end columns for this semester
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $colsPerSemester - 1);
            
            // Row 1: Semester header (merged)
            $sheet->setCellValue($startCol . '1', $semLabel);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
            $sheet->getStyle($startCol . '1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($startCol . '1')->getFont()->setBold(true);
            
            // Row 2: Mapel names
            $mapelColIndex = $colIndex;
            foreach ($semesterMapels as $mapel) {
                $mapelCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($mapelColIndex);
                $sheet->setCellValue($mapelCol . '2', $mapel->nama_mapel);
                $mapelColIndex++;
            }
            
            // Jumlah Mata Pelajaran column
            $jumlahCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($mapelColIndex);
            $sheet->setCellValue($jumlahCol . '2', 'Jumlah Mapel');
            $sheet->getStyle($jumlahCol . '2')->getFont()->setBold(true);
            $mapelColIndex++;
            
            // Total Nilai column
            $totalCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($mapelColIndex);
            $sheet->setCellValue($totalCol . '2', 'Total Nilai');
            $sheet->getStyle($totalCol . '2')->getFont()->setBold(true);
            
            $colIndex += $colsPerSemester;
        }
        
        // Calculate last column
        $lastColIndex = $colIndex - 1;
        $lastColPrev = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);
        
        // Style header rows
        $sheet->getStyle('A1:' . $lastColPrev . '2')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColPrev . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4A90D9'); // Blue for semester row
        $sheet->getStyle('A1:' . $lastColPrev . '1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A2:' . $lastColPrev . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9EAD3'); // Light green for mapel row
        $sheet->getStyle('A1:' . $lastColPrev . '2')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A2:' . $lastColPrev . '2')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(40);
        
        // Write data starting from row 3
        $row = 3;
        foreach ($siswaList as $index => $siswa) {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $index + 1);
            $sheet->setCellValue($col++ . $row, "'" . $siswa->nisn); // Force text
            $sheet->setCellValue($col++ . $row, $siswa->nama_lengkap);
            
            // Write nilai per semester
            foreach ($semesterConfig as $sem => $config) {
                $nilaiCount = 0;
                $nilaiTotal = 0;
                $semesterMapels = $mapelsBySemester->get($sem, collect());
                
                foreach ($semesterMapels as $mapel) {
                    $nilai = $nilaiLookup[
                        $this->leggerNilaiKey($siswa->id, $sem, $mapel->kode_mapel)
                    ] ?? null;
                    
                    $sheet->setCellValue($col++ . $row, $nilai !== null ? round($nilai, 0) : '');
                    
                    if ($nilai !== null) {
                        $nilaiCount++;
                        $nilaiTotal += $nilai;
                    }
                }
                
                // Jumlah Mata Pelajaran yang ada nilainya
                $sheet->setCellValue($col++ . $row, $nilaiCount);
                
                // Total Nilai
                $sheet->setCellValue($col++ . $row, $nilaiTotal > 0 ? $nilaiTotal : '');
            }
            
            $row++;
        }
        
        // Add borders to data
        $lastDataRow = $row - 1;
        $sheet->getStyle('A3:' . $lastColPrev . $lastDataRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Auto width for first columns
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(30);
        
        // Set width for mapel columns (narrower)
        $colIndex = 4;
        foreach ($semesterConfig as $sem => $config) {
            $semesterMapelCount = $mapelsBySemester->get($sem, collect())->count();
            for ($i = 0; $i < $semesterMapelCount; $i++) {
                $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($colStr)->setWidth(8);
                $colIndex++;
            }
            // Jumlah & Total columns wider
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colStr)->setWidth(12);
            $colIndex++;
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colStr)->setWidth(12);
            $colIndex++;
        }
        
        // Freeze pane (freeze after row 2 headers and column C)
        $sheet->freezePane('D3');
        
        $filename = "legger_span_ptkin_" . date('Y-m-d_His') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            try {
                $writer->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
