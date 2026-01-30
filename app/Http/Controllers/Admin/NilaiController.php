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
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiController extends Controller
{
    /**
     * Display nilai index
     */
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Get summary per semester
        $summaryQuery = NilaiSiswa::select('semester', DB::raw('COUNT(DISTINCT siswa_id) as jumlah_siswa'))
            ->when($request->tahun_pelajaran_id, function ($q) use ($request) {
                return $q->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
            }, function ($q) use ($tahunAktif) {
                return $tahunAktif ? $q->where('tahun_pelajaran_id', $tahunAktif->id) : $q;
            })
            ->groupBy('semester')
            ->get()
            ->keyBy('semester');
        
        $summary = [];
        foreach (NilaiSiswa::SEMESTER_LABELS as $sem => $label) {
            $summary[$sem] = [
                'label' => $label,
                'jumlah_siswa' => $summaryQuery[$sem]->jumlah_siswa ?? 0
            ];
        }
        
        return view('admin.nilai.index', compact('tahunPelajarans', 'tahunAktif', 'summary'));
    }

    /**
     * Show nilai per semester
     */
    public function semester(Request $request, $semester)
    {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('tahun_mulai', 'desc')
            ->get();
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahun = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id) 
            : $tahunAktif;
        
        $semesterLabel = NilaiSiswa::SEMESTER_LABELS[$semester] ?? "Semester {$semester}";
        
        // Get mapel yang ada nilainya untuk semester ini
        $mapelList = MataPelajaran::whereHas('nilaiSiswa', function ($q) use ($semester, $selectedTahun) {
            $q->where('semester', $semester);
            if ($selectedTahun) {
                $q->where('tahun_pelajaran_id', $selectedTahun->id);
            }
        })->orderBy('kode_mapel')->get();
        
        if ($request->ajax()) {
            return $this->getSemesterData($request, $semester, $selectedTahun);
        }
        
        return view('admin.nilai.semester', compact(
            'semester', 
            'semesterLabel', 
            'tahunPelajarans', 
            'selectedTahun',
            'mapelList'
        ));
    }

    /**
     * Get data for DataTable
     */
    private function getSemesterData(Request $request, $semester, $selectedTahun)
    {
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
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nisn', fn($siswa) => $siswa->nisn)
            ->addColumn('nama', fn($siswa) => $siswa->nama_lengkap)
            ->addColumn('nilai_list', function ($siswa) {
                $nilai = [];
                foreach ($siswa->nilaiSiswa as $n) {
                    $nilai[$n->mataPelajaran->kode_mapel] = $n->nilai;
                }
                return $nilai;
            })
            ->addColumn('rata_rata', function ($siswa) {
                $nilaiList = $siswa->nilaiSiswa->pluck('nilai')->filter();
                return $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : '-';
            })
            ->addColumn('action', function ($siswa) use ($semester) {
                return '<a href="' . route('admin.nilai.siswa', [$siswa->id, 'semester' => $semester]) . '" 
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
        
        // Get mapel untuk referensi
        $mapelList = MataPelajaran::where('is_active', true)
            ->orderBy('kode_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);
        
        return view('admin.nilai.upload', compact('tahunPelajarans', 'tahunAktif', 'mapelList'));
    }

    /**
     * Process upload excel
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
                return back()->with('error', 'File Excel kosong atau tidak memiliki data.');
            }

            // Header row (baris pertama)
            $header = array_map('strtoupper', array_map('trim', $rows[0]));
            
            // Cari index kolom NISN
            $nisnIndex = array_search('NISN', $header);
            if ($nisnIndex === false) {
                return back()->with('error', 'Kolom NISN tidak ditemukan di header Excel.');
            }

            // Get semua mapel dengan kode
            $mapelByKode = MataPelajaran::where('is_active', true)
                ->get()
                ->keyBy(function ($item) {
                    return strtoupper($item->kode_mapel);
                });

            // Mapping header ke mapel
            $mapelMapping = [];
            foreach ($header as $index => $col) {
                if ($index <= $nisnIndex) continue; // Skip kolom sebelum dan termasuk NISN
                
                $kode = strtoupper(trim($col));
                if (isset($mapelByKode[$kode])) {
                    $mapelMapping[$index] = $mapelByKode[$kode];
                }
            }

            if (empty($mapelMapping)) {
                return back()->with('error', 'Tidak ada kode mapel yang cocok di header Excel. Pastikan kode mapel sesuai dengan data di sistem.');
            }

            DB::beginTransaction();

            $semester = $request->semester;
            $tahunPelajaranId = $request->tahun_pelajaran_id;
            $successCount = 0;
            $errorCount = 0;
            $notFoundNisn = [];
            $importedAt = now();

            // Process data rows (skip header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nisn = trim($row[$nisnIndex] ?? '');
                
                if (empty($nisn)) continue;

                // Cari siswa berdasarkan NISN
                $siswa = Siswa::where('nisn', $nisn)->first();
                
                if (!$siswa) {
                    $notFoundNisn[] = $nisn;
                    $errorCount++;
                    continue;
                }

                // Insert/update nilai untuk setiap mapel
                foreach ($mapelMapping as $colIndex => $mapel) {
                    $nilai = $row[$colIndex] ?? null;
                    
                    if ($nilai === null || $nilai === '' || !is_numeric($nilai)) {
                        continue;
                    }

                    $nilai = floatval($nilai);
                    
                    NilaiSiswa::updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'mata_pelajaran_id' => $mapel->id,
                            'tahun_pelajaran_id' => $tahunPelajaranId,
                            'semester' => $semester,
                        ],
                        [
                            'nilai' => $nilai,
                            'predikat' => NilaiSiswa::hitungPredikat($nilai),
                            'sumber_data' => 'import_excel',
                            'imported_at' => $importedAt,
                        ]
                    );
                }
                
                $successCount++;
            }

            DB::commit();

            $message = "Berhasil mengimport nilai untuk {$successCount} siswa.";
            
            if ($errorCount > 0) {
                $message .= " {$errorCount} NISN tidak ditemukan.";
            }
            
            if (!empty($notFoundNisn) && count($notFoundNisn) <= 10) {
                $message .= " NISN tidak ditemukan: " . implode(', ', $notFoundNisn);
            }

            return redirect()->route('admin.nilai.semester', $semester)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing nilai: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengimport nilai: ' . $e->getMessage());
        }
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
     * Download template excel
     */
    public function downloadTemplate(Request $request)
    {
        $semester = $request->semester ?? 1;
        
        // Get active mapel
        $mapelList = MataPelajaran::where('is_active', true)
            ->orderBy('kelompok')
            ->orderBy('kode_mapel')
            ->get(['kode_mapel', 'nama_mapel']);
        
        // Get siswa aktif
        $siswaList = Siswa::where('status_siswa', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['nisn', 'nama_lengkap']);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Nilai Sem ' . $semester);
        
        // Header
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, 1, 'No');
        $sheet->setCellValueByColumnAndRow($col++, 1, 'NIS');
        $sheet->setCellValueByColumnAndRow($col++, 1, 'Nisn');
        $sheet->setCellValueByColumnAndRow($col++, 1, 'Nama');
        $sheet->setCellValueByColumnAndRow($col++, 1, 'JK');
        
        foreach ($mapelList as $mapel) {
            $sheet->setCellValueByColumnAndRow($col++, 1, $mapel->kode_mapel);
        }
        
        // Data siswa
        $row = 2;
        $no = 1;
        foreach ($siswaList as $siswa) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $no++);
            $sheet->setCellValueByColumnAndRow($col++, $row, ''); // NIS
            $sheet->setCellValueByColumnAndRow($col++, $row, $siswa->nisn);
            $sheet->setCellValueByColumnAndRow($col++, $row, $siswa->nama_lengkap);
            $sheet->setCellValueByColumnAndRow($col++, $row, ''); // JK
            $row++;
        }
        
        // Style header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');
        
        // Auto width
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $filename = 'template_nilai_semester_' . $semester . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
