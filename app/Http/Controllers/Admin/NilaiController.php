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

            if (count($rows) < 8) {
                return back()->with('error', 'File Excel kosong atau format tidak sesuai.');
            }

            // Format Excel RDM:
            // Baris 1: LEGGER NILAI XII.2
            // Baris 2: Kelas, Semester
            // Baris 3: Madrasah, Tahun Ajaran
            // Baris 4: kosong
            // Baris 5: kosong
            // Baris 6: No, NIS, Nisn, Nama, JK, PAI (merged), BAR, PP, ..., MULOK (merged), THF, KMPM (merged), KMPS (merged)
            // Baris 7: (kosong), (kosong), ..., QH, AA, FIK, SKI, ..., PRKW, ..., BIO, KIM, FIS, INFOP, MTL, EKO
            // Baris 8+: Data siswa

            // Cari baris header (baris yang mengandung "Nisn")
            $headerRowIndex = null;
            for ($i = 0; $i < min(10, count($rows)); $i++) {
                $row = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$i])));
                if (in_array('NISN', $row)) {
                    $headerRowIndex = $i;
                    break;
                }
            }

            if ($headerRowIndex === null) {
                return back()->with('error', 'Kolom NISN tidak ditemukan di header Excel. Pastikan format sesuai dengan Excel RDM.');
            }

            // Header row 1 (main header)
            $header1 = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex])));
            
            // Header row 2 (sub header for merged cells like PAI -> QH, AA, FIK, SKI)
            $header2 = [];
            if (isset($rows[$headerRowIndex + 1])) {
                $header2 = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex + 1])));
            }
            
            // Cari index kolom NISN
            $nisnIndex = array_search('NISN', $header1);
            
            // Get semua mapel dengan kode
            $mapelByKode = MataPelajaran::where('is_active', true)
                ->get()
                ->keyBy(function ($item) {
                    return strtoupper($item->kode_mapel);
                });

            // Mapping header ke mapel
            // Prioritas: header2 (detail) jika ada, fallback ke header1
            $mapelMapping = [];
            $skipColumns = ['NO', 'NIS', 'NISN', 'NAMA', 'JK', 'JUMLAH', 'PAI', 'KMPM', 'KMPS', ''];
            
            for ($index = 0; $index < max(count($header1), count($header2)); $index++) {
                // Cek header2 dulu (sub-header untuk detail mapel)
                $kode2 = isset($header2[$index]) ? strtoupper(trim($header2[$index])) : '';
                $kode1 = isset($header1[$index]) ? strtoupper(trim($header1[$index])) : '';
                
                // Gunakan header2 jika ada dan bukan kosong, else gunakan header1
                $kode = !empty($kode2) ? $kode2 : $kode1;
                
                // Handle MULOK PRKW (split across 2 rows: MULOK + PRKW)
                if ($kode1 === 'MULOK' && $kode2 === 'PRKW') {
                    $kode = 'MULOK PRKW';
                }
                
                // Skip kolom non-mapel
                if (in_array($kode, $skipColumns)) continue;
                
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

            // Tentukan baris data dimulai (setelah header2 jika ada sub-header, else setelah header1)
            $dataStartRow = $headerRowIndex + 1;
            // Cek apakah baris berikutnya adalah sub-header (mengandung QH, AA, dll) atau data
            if (isset($rows[$headerRowIndex + 1])) {
                $potentialSubHeader = array_map('strtoupper', array_map('trim', array_map('strval', $rows[$headerRowIndex + 1])));
                // Jika ada QH, AA, FIK, SKI atau BIO, KIM, dll di baris ini, ini adalah sub-header
                $subHeaderMapels = ['QH', 'AA', 'FIK', 'SKI', 'BIO', 'KIM', 'FIS', 'EKO', 'PRKW'];
                foreach ($potentialSubHeader as $val) {
                    if (in_array($val, $subHeaderMapels)) {
                        $dataStartRow = $headerRowIndex + 2;
                        break;
                    }
                }
            }

            // Process data rows
            for ($i = $dataStartRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nisn = trim(strval($row[$nisnIndex] ?? ''));
                
                // Skip baris kosong atau baris total
                if (empty($nisn) || !is_numeric($nisn)) continue;

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
     * Download template excel (format sesuai RDM)
     */
    public function downloadTemplate(Request $request)
    {
        $semester = $request->semester ?? 1;
        $semesterText = $semester % 2 == 1 ? 'Ganjil' : 'Genap';
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunAjaran = $tahunAktif ? $tahunAktif->nama : date('Y') . '/' . (date('Y') + 1);
        
        // Tentukan kelas berdasarkan semester
        $kelasRomawi = match(true) {
            $semester <= 2 => 'X',
            $semester <= 4 => 'XI',
            default => 'XII',
        };
        
        // Get mapel dengan urutan sesuai RDM
        // Urutan: QH, AA, FIK, SKI (PAI) | BAR, PP, BINDO, MTK, BING, PJOK, SEJ, SB, MULOK PRKW, THF | BIO, KIM, FIS, INFOP, MTL (KMPM) | EKO (KMPS)
        $mapelOrder = ['QH', 'AA', 'FIK', 'SKI', 'BAR', 'PP', 'BINDO', 'MTK', 'BING', 'PJOK', 'SEJ', 'SB', 'MULOK PRKW', 'THF', 'BIO', 'KIM', 'FIS', 'INFOP', 'MTL', 'EKO'];
        
        $mapelList = MataPelajaran::where('is_active', true)
            ->whereIn('kode_mapel', $mapelOrder)
            ->get()
            ->sortBy(function ($mapel) use ($mapelOrder) {
                return array_search($mapel->kode_mapel, $mapelOrder);
            });
        
        // Get siswa aktif
        $siswaList = Siswa::where('status_siswa', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['nisn', 'nama_lengkap', 'jenis_kelamin']);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LEGGER NILAI KELAS ' . $kelasRomawi);
        
        // Baris 1: kosong (skip)
        
        // Baris 2: Kelas dan Semester
        $sheet->setCellValue('A2', 'Kelas:');
        $sheet->setCellValue('B2', $kelasRomawi . '.1');
        $sheet->setCellValue('D2', 'Semester:');
        $sheet->setCellValue('E2', $semesterText);
        
        // Style baris 2 (kuning)
        $sheet->getStyle('B2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle('E2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        
        // Baris 3: Madrasah dan Tahun Ajaran
        $sheet->setCellValue('A3', 'Madrasah:');
        $sheet->setCellValue('B3', 'MAN 1 METRO');
        $sheet->setCellValue('D3', 'Tahun Ajaran:');
        $sheet->setCellValue('E3', $tahunAjaran);
        
        // Style baris 3 (kuning)
        $sheet->getStyle('B3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle('E3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        
        // Baris 4: kosong
        
        // Baris 5: Header grup (PAI, KMPM, KMPS)
        $sheet->setCellValue('F5', 'PAI');
        $sheet->mergeCells('F5:I5'); // QH, AA, FIK, SKI
        
        // Baris 6: Header kolom
        $headerRow = 6;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'No');
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'NIS');
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'Nisn');
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'Nama');
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'JK');
        
        foreach ($mapelList as $mapel) {
            $sheet->setCellValueByColumnAndRow($col++, $headerRow, $mapel->kode_mapel);
        }
        
        $sheet->setCellValueByColumnAndRow($col++, $headerRow, 'Jumlah');
        
        // Style header baris 6
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');
        
        // Data siswa mulai baris 7
        $row = 7;
        $no = 1;
        foreach ($siswaList as $siswa) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $no++);
            $sheet->setCellValueByColumnAndRow($col++, $row, ''); // NIS
            $sheet->setCellValueByColumnAndRow($col++, $row, $siswa->nisn);
            $sheet->setCellValueByColumnAndRow($col++, $row, $siswa->nama_lengkap);
            $sheet->setCellValueByColumnAndRow($col++, $row, $siswa->jenis_kelamin ?? ''); // JK
            $row++;
        }
        
        // Auto width
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $filename = 'LEGGER_NILAI_KELAS_' . $kelasRomawi . '_SEM_' . $semester . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
