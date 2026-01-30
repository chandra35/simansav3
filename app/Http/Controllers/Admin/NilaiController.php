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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        
        // Jika ada request tahun_pelajaran_id, gunakan itu
        // Jika tidak, coba gunakan tahun aktif
        // Jika tidak ada tahun aktif, cari tahun pelajaran yang ada nilainya
        $selectedTahun = null;
        if ($request->tahun_pelajaran_id) {
            $selectedTahun = TahunPelajaran::find($request->tahun_pelajaran_id);
        } elseif ($tahunAktif) {
            $selectedTahun = $tahunAktif;
        } else {
            // Cari tahun pelajaran yang ada nilainya
            $tahunIdWithNilai = NilaiSiswa::where('semester', $semester)
                ->distinct()
                ->first(['tahun_pelajaran_id']);
            if ($tahunIdWithNilai) {
                $selectedTahun = TahunPelajaran::find($tahunIdWithNilai->tahun_pelajaran_id);
            }
        }
        
        $semesterLabel = NilaiSiswa::SEMESTER_LABELS[$semester] ?? "Semester {$semester}";
        
        // Get mapel sesuai urutan di config
        $urutanMapel = config('nilai.urutan_mapel');
        $mapelList = MataPelajaran::whereHas('nilaiSiswa', function ($q) use ($semester, $selectedTahun) {
            $q->where('semester', $semester);
            if ($selectedTahun) {
                $q->where('tahun_pelajaran_id', $selectedTahun->id);
            }
        })
        ->whereIn('kode_mapel', $urutanMapel)
        ->get()
        ->sortBy(function($mapel) use ($urutanMapel) {
            return array_search($mapel->kode_mapel, $urutanMapel);
        })
        ->values();
        
        if ($request->ajax()) {
            return $this->getSemesterData($request, $semester, $selectedTahun, $urutanMapel);
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
    private function getSemesterData(Request $request, $semester, $selectedTahun, $urutanMapel = null)
    {
        if (!$urutanMapel) {
            $urutanMapel = config('nilai.urutan_mapel');
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
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nisn', fn($siswa) => $siswa->nisn)
            ->addColumn('nama', fn($siswa) => $siswa->nama_lengkap)
            ->addColumn('nilai_list', function ($siswa) use ($urutanMapel) {
                $nilai = [];
                // Urutkan sesuai config
                foreach ($urutanMapel as $kode) {
                    $found = $siswa->nilaiSiswa->first(fn($n) => $n->mataPelajaran->kode_mapel === $kode);
                    $nilai[$kode] = $found ? $found->nilai : null;
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
        $urutanMapel = config('nilai.urutan_mapel');
        
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
        
        // Auto width
        foreach (range('A', $col) as $columnID) {
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
        
        $filename = 'template_nilai_span_ptkin.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
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

            // Ambil config urutan mapel
            $urutanMapel = config('nilai.urutan_mapel');
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

            $semester = $request->semester;
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
}
