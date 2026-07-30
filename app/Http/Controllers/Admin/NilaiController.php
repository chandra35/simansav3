<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            $legacyOrder = $this->getMapelBySemester($semester);

            return MataPelajaran::query()
                ->with('kurikulum:id,kode')
                ->whereIn('kode_mapel', $legacyOrder)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn ($mapel) => array_search($mapel->kode_mapel, $legacyOrder, true))
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

        DB::table('nilai_siswa')
            ->select(['id', 'siswa_id', 'semester', 'mata_pelajaran_id', 'nilai'])
            ->whereIn('siswa_id', $siswaIds)
            ->where(function ($query) use ($periods) {
                foreach ($periods as $semester => $tahunPelajaranId) {
                    $query->orWhere(function ($periodQuery) use ($semester, $tahunPelajaranId) {
                        $periodQuery->where('semester', (int) $semester)
                            ->where('tahun_pelajaran_id', $tahunPelajaranId);
                    });
                }
            })
            ->lazyById(1000)
            ->each(function ($row) use (&$lookup) {
                $key = $this->leggerNilaiKey($row->siswa_id, (int) $row->semester, $row->mata_pelajaran_id);
                $lookup[$key] ??= $row->nilai;
            });

        return $lookup;
    }

    private function leggerNilaiKey(string $siswaId, int $semester, string $mapelId): string
    {
        return $siswaId.'|'.$semester.'|'.$mapelId;
    }

    /**
     * Get urutan mapel berdasarkan semester
     */
    private function getMapelBySemester($semester)
    {
        $semester = (int) $semester;
        
        if (in_array($semester, [5, 6], true)) {
            return config('nilai.urutan_mapel_sem_5');
        } elseif ($semester === 4) {
            return config('nilai.urutan_mapel_sem_4');
        } elseif ($semester === 3) {
            return config('nilai.urutan_mapel_sem_3');
        } else {
            // Semester 1-2
            return config('nilai.urutan_mapel_sem_1_2', config('nilai.urutan_mapel'));
        }
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
        
        if (!$urutanMapel) {
            $urutanMapel = $this->getMapelBySemester($semester);
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
    public function uploadForm()
    {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Get mapel sesuai urutan di config untuk referensi
        $urutanMapel = config('nilai.urutan_mapel');
        $mapelList = MataPelajaran::whereIn('kode_mapel', $urutanMapel)
            ->where('is_active', true)
            ->get()
            ->sortBy(function($mapel) use ($urutanMapel) {
                return array_search($mapel->kode_mapel, $urutanMapel);
            })
            ->values();
        
        return view('admin.nilai.upload', compact('tahunPelajarans', 'tahunAktif', 'mapelList', 'urutanMapel'));
    }

    /**
     * Download template Excel untuk upload nilai
     */
    public function downloadTemplate(Request $request)
    {
        $semester = (int) $request->input('semester', 1);
        
        // Pilih config berdasarkan semester
        $urutanMapel = $this->getMapelBySemester($semester);
        
        // Get mapel dari database sesuai urutan
        $mapels = MataPelajaran::whereIn('kode_mapel', $urutanMapel)
            ->where('is_active', true)
            ->get()
            ->keyBy('kode_mapel');
        
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
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);
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
        $refSheet->getStyle('A1:C1')->getFont()->setBold(true);
        
        $row = 2;
        foreach ($urutanMapel as $index => $kode) {
            $mapel = $mapels[$kode] ?? null;
            $refSheet->setCellValue('A' . $row, $index + 1);
            $refSheet->setCellValue('B' . $row, $kode);
            $refSheet->setCellValue('C' . $row, $mapel ? $mapel->nama_mapel : '-');
            $row++;
        }
        
        $refSheet->getColumnDimension('A')->setAutoSize(true);
        $refSheet->getColumnDimension('B')->setAutoSize(true);
        $refSheet->getColumnDimension('C')->setAutoSize(true);
        
        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);
        
        $filename = 'template_nilai_semester_' . $semester . '.xlsx';
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

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File Excel kosong atau format tidak sesuai.');
            }

            $semester = (int) $request->semester;
            
            // Pilih config berdasarkan semester
            $urutanMapel = $this->getMapelBySemester($semester);
            
            $kolomNisn = config('nilai.kolom_nisn', 2);
            $kolomNilaiMulai = config('nilai.kolom_nilai_mulai', 5);
            
            // Get semua mapel dengan kode sesuai urutan
            $mapelByKode = MataPelajaran::whereIn('kode_mapel', $urutanMapel)
                ->where('is_active', true)
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
                return back()->with('error', 'Kode mapel tidak ditemukan di database: ' . implode(', ', $missingMapel) . '. Silakan tambahkan mapel tersebut terlebih dahulu.');
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
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
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
        $mapels = $availableMapelsByCode->only($selectedMapel);
        
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
            $sheet->setCellValue($startCol . '1', $kode);
            $sheet->mergeCells($startCol . '1:' . $endCol . '1');
            $sheet->getStyle($startCol . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
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
                        ? ($nilaiLookup[$this->leggerNilaiKey($siswa->id, $sem, $mapel->id)] ?? null)
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
        $summarySheet->setCellValue('A4', 'Tahun Pelajaran Aktif');
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
        
        $filename = "legger_kelas_{$tingkat}_" . date('Y-m-d_His') . '.xlsx';
        
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
                        $this->leggerNilaiKey($siswa->id, $sem, $mapel->id)
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
