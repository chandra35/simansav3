<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\SnbpMenu;
use App\Models\SiswaLulusan;
use App\Models\SpanPtkinMenu;
use App\Models\TahunPelajaran;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class LulusanController extends Controller
{
    /**
     * Cohort kelas XII tetap menjadi bagian laporan setelah finalisasi akhir tahun.
     * Status "lulus" adalah histori final dari status "aktif", bukan data yang
     * harus dikeluarkan dari laporan lulusan.
     */
    private const LULUSAN_CLASS_STATUSES = ['aktif', 'lulus'];

    public function index(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $selectedTahun = $this->resolveSelectedTahun($request, $tahunPelajaranList);

        return view('admin.lulusan.index', [
            'tahunPelajaranList' => $tahunPelajaranList,
            'selectedTahun' => $selectedTahun,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
            'checkerLinksByTahun' => $this->buildCheckerLinksByTahun($tahunPelajaranList),
        ]);
    }

    public function data(Request $request)
    {
        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return DataTables::of(collect())->make(true);
        }

        $query = $this->buildBaseQuery($request, $selectedTahun->id);
        $trackerType = $this->resolveTrackerType($request);

        return DataTables::query($query)
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
            ->addColumn('checker_badge', function ($row) use ($trackerType) {
                $trackerData = $this->extractRowTrackerData($row, $trackerType);

                if (!$trackerData['has_number']) {
                    return '<span class="text-muted">-</span>';
                }

                return '<span class="badge badge-' . $this->statusBadgeColor($trackerData['status']) . '">' . e($this->formatTrackerStatusLabel($trackerData['status'], $trackerData['type'])) . '</span>';
            })
            ->addColumn('result_badge', function ($row) use ($trackerType) {
                $trackerData = $this->extractRowTrackerData($row, $trackerType);

                if (!$trackerData['has_number']) {
                    return '<span class="text-muted">-</span>';
                }

                return '<span class="badge badge-' . $this->resultBadgeColor($trackerData['status']) . '">' . e($this->formatTrackerResultLabel($trackerData['status'])) . '</span>';
            })
            ->editColumn('nama_universitas', fn ($row) => $row->nama_universitas ?: '-')
            ->editColumn('jurusan_fakultas', fn ($row) => $row->jurusan_fakultas ?: '-')
            ->editColumn('program_studi', fn ($row) => $row->program_studi ?: '-')
            ->rawColumns(['status_badge', 'jalur_badge', 'checker_badge', 'result_badge'])
            ->make(true);
    }

    public function stats(Request $request)
    {
        $report = $this->buildReportData($request);

        if (!$report['selectedTahun']) {
            return response()->json([
                'summary' => $this->emptySummary(),
                'per_jalur' => [],
                'per_kelas' => [],
                'top_universitas' => [],
                'checker_status' => $this->emptyCheckerStatus(),
                'top_tracker_universitas' => [],
                'top_tracker_prodi' => [],
                'tracker_meta' => $this->defaultTrackerMeta(),
            ]);
        }

        return response()->json([
            'summary' => $report['summary'],
            'per_jalur' => $report['per_jalur'],
            'per_kelas' => $report['per_kelas'],
            'top_universitas' => $report['top_universitas'],
            'checker_status' => $report['checker_status'],
            'top_tracker_universitas' => $report['top_tracker_universitas'],
            'top_tracker_prodi' => $report['top_tracker_prodi'],
            'tracker_meta' => $report['tracker_meta'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $report = $this->buildReportData($request);

        if (!$report['selectedTahun']) {
            return redirect()->route('admin.lulusan.index')
                ->with('error', 'Tahun pelajaran tidak ditemukan untuk export laporan.');
        }

        $spreadsheet = new Spreadsheet();
        $this->buildSummarySheet($spreadsheet->getActiveSheet(), $report);
        $this->buildDetailSheet($spreadsheet, $report);
        $this->buildEligibleSheet($spreadsheet, $report);
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'laporan_lulusan_' . $this->sanitizeFilenameSegment($report['selectedTahun']->nama) . '_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'simansa_lulusan_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->streamDownload(function () use ($tempFile) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            $stream = fopen($tempFile, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }

            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReportData($request);

        if (!$report['selectedTahun']) {
            return redirect()->route('admin.lulusan.index')
                ->with('error', 'Tahun pelajaran tidak ditemukan untuk export laporan.');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $pdf = \PDF::loadView('admin.lulusan.export-pdf', $report);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'laporan_lulusan_' . $this->sanitizeFilenameSegment($report['selectedTahun']->nama) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    public function sendGraduationEmails(Request $request, EmailService $emailService)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'nullable|string',
            'status_pengisian' => 'nullable|in:sudah_isi,belum_isi',
            'jalur_masuk' => 'nullable|string',
            'q' => 'nullable|string|max:255',
            'catatan_admin' => 'nullable|string|max:5000',
        ]);

        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun pelajaran tidak ditemukan.',
            ], 422);
        }

        if (!$emailService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP belum dikonfigurasi atau belum aktif.',
            ], 422);
        }

        if (!EmailTemplate::getByCode('graduation_announcement')) {
            EmailTemplate::seedDefaults();
        }

        $rows = collect(
            $this->buildBaseQuery($request, $selectedTahun->id)
                ->whereNotNull('users.email')
                ->where('users.email', '<>', '')
                ->orderBy('kelas_nama')
                ->orderBy('nama_lengkap')
                ->get()
        );

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada siswa yang sesuai filter dan memiliki email tujuan.',
            ], 422);
        }

        $note = trim((string) ($validated['catatan_admin'] ?? ''));
        $defaultNote = 'Silakan periksa kembali data Anda di aplikasi SIMANSA dan hubungi admin/operator jika ada informasi yang perlu diperbaiki.';

        $stats = [
            'total' => $rows->count(),
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        $failures = [];

        foreach ($rows as $row) {
            if (blank($row->email)) {
                $stats['skipped']++;
                continue;
            }

            $payload = [
                '[nama_siswa]' => $row->nama_lengkap,
                '[nisn]' => $row->nisn,
                '[kelas]' => $row->kelas_nama ?: '-',
                '[email_siswa]' => $row->email,
                '[status_kelulusan]' => (int) $row->is_filled === 1 ? 'Data lulusan sudah tercatat' : 'Menunggu kelengkapan data lulusan',
                '[jalur_masuk]' => $row->jalur_masuk ?: '-',
                '[nama_universitas]' => $row->nama_universitas ?: '-',
                '[jurusan_fakultas]' => $row->jurusan_fakultas ?: '-',
                '[program_studi]' => $row->program_studi ?: '-',
                '[catatan_admin]' => $note !== '' ? nl2br(e($note)) : $defaultNote,
                '[tahun_pelajaran_lulusan]' => $selectedTahun->nama,
            ];

            $result = $emailService->sendGraduationAnnouncement($row->email, $payload);

            if ($result['success']) {
                $stats['sent']++;
                continue;
            }

            $stats['failed']++;
            $failures[] = [
                'nama' => $row->nama_lengkap,
                'email' => $row->email,
                'message' => $result['message'] ?? 'Gagal mengirim email.',
            ];

            Log::warning('Graduation announcement email failed', [
                'siswa_id' => $row->siswa_id,
                'email' => $row->email,
                'message' => $result['message'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses kirim email pengumuman kelulusan selesai.',
            'stats' => $stats,
            'failures' => array_slice($failures, 0, 10),
        ]);
    }

    private function buildReportData(Request $request): array
    {
        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return [
                'selectedTahun' => null,
                'filters' => $this->formatFilters($request, null),
                'summary' => $this->emptySummary(),
                'per_jalur' => [],
                'per_kelas' => [],
                'top_universitas' => [],
                'checker_status' => $this->emptyCheckerStatus(),
                'top_tracker_universitas' => [],
                'top_tracker_prodi' => [],
                'tracker_meta' => $this->defaultTrackerMeta(),
                'tracker_type' => 'ALL',
                'accepted_students' => [],
                'rows' => collect(),
                'eligible_rows' => collect(),
                'generated_at' => now(),
            ];
        }

        $rows = collect(
            $this->buildBaseQuery($request, $selectedTahun->id)
                ->orderBy('kelas_nama')
                ->orderBy('nama_lengkap')
                ->get()
        );

        $trackerType = $this->resolveTrackerType($request);
        $trackerRows = $this->buildTrackerRows($request, $selectedTahun->id, $trackerType);

        $summary = [
            'total' => $rows->count(),
            'sudah_isi' => $rows->where('is_filled', 1)->count(),
            'belum_isi' => $rows->where('is_filled', 0)->count(),
            'total_universitas' => $rows->pluck('nama_universitas')->filter()->unique()->count(),
            'eligible_total' => $trackerRows->count(),
            'eligible_sudah_isi_nomor' => $trackerRows->filter(fn ($row) => filled($row->nomor_pendaftaran))->count(),
            'eligible_belum_isi_nomor' => $trackerRows->filter(fn ($row) => blank($row->nomor_pendaftaran))->count(),
            'eligible_lulus' => $trackerRows->where('check_status', 'lulus')->count(),
            'eligible_tidak_lulus' => $trackerRows->where('check_status', 'tidak_lulus')->count(),
            'eligible_gagal_cek' => $trackerRows->where('check_status', 'gagal_cek')->count(),
            'eligible_belum_dicek' => $trackerRows->where('check_status', 'belum_dicek')->count(),
            'total_ptn_diterima' => $trackerRows->where('check_status', 'lulus')->pluck('nama_universitas')->filter()->unique()->count(),
        ];

        $perJalur = $this->buildPerJalurStats($rows);

        $trackerByClass = $trackerRows->groupBy(fn ($row) => $row->kelas_nama ?: 'Tanpa Kelas');

        $perKelas = $rows
            ->groupBy(fn ($row) => $row->kelas_nama ?: 'Tanpa Kelas')
            ->map(function (Collection $kelasRows, string $kelasNama) use ($perJalur, $trackerByClass) {
                $jalur = [];
                foreach (array_keys($perJalur) as $jalurMasuk) {
                    $jalur[$jalurMasuk] = $kelasRows->where('jalur_masuk', $jalurMasuk)->count();
                }

                $kelasEligibleRows = $trackerByClass->get($kelasNama, collect());

                return [
                    'kelas_nama' => $kelasNama,
                    'total' => $kelasRows->count(),
                    'sudah_isi' => $kelasRows->where('is_filled', 1)->count(),
                    'belum_isi' => $kelasRows->where('is_filled', 0)->count(),
                    'eligible' => $kelasEligibleRows->count(),
                    'eligible_lulus' => $kelasEligibleRows->where('check_status', 'lulus')->count(),
                    'eligible_tidak_lulus' => $kelasEligibleRows->where('check_status', 'tidak_lulus')->count(),
                    'jalur' => $jalur,
                ];
            })
            ->values()
            ->all();

        $acceptedStudents = $trackerRows
            ->where('check_status', 'lulus')
            ->sortBy([
                ['nama_universitas', 'asc'],
                ['program_studi', 'asc'],
                ['nama_lengkap', 'asc'],
            ])
            ->map(function ($row) {
                return [
                    'nama_lengkap' => $row->nama_lengkap,
                    'nisn' => $row->nisn,
                    'kelas_nama' => $row->kelas_nama ?: '-',
                    'nama_universitas' => $row->nama_universitas ?: '-',
                    'program_studi' => $row->program_studi ?: '-',
                    'initials' => $this->makeInitials($row->nama_lengkap),
                    'photo_path' => $this->resolveStudentPhotoPath($row->foto_profile ?? null),
                ];
            })
            ->values()
            ->all();

        return [
            'selectedTahun' => $selectedTahun,
            'filters' => $this->formatFilters($request, $selectedTahun),
            'summary' => $summary,
            'per_jalur' => $perJalur,
            'per_kelas' => $perKelas,
            'top_universitas' => $this->buildTopList($rows, 'nama_universitas', 10),
            'checker_status' => [
                'belum_dicek' => $trackerRows->where('check_status', 'belum_dicek')->count(),
                'lulus' => $trackerRows->where('check_status', 'lulus')->count(),
                'tidak_lulus' => $trackerRows->where('check_status', 'tidak_lulus')->count(),
                'gagal_cek' => $trackerRows->where('check_status', 'gagal_cek')->count(),
            ],
            'top_tracker_universitas' => $this->buildTopList($trackerRows->where('check_status', 'lulus'), 'nama_universitas', 10),
            'top_tracker_prodi' => $this->buildTopList($trackerRows->where('check_status', 'lulus'), 'program_studi', 10),
            'tracker_meta' => $this->buildTrackerMeta($trackerType),
            'tracker_type' => $trackerType,
            'accepted_students' => $acceptedStudents,
            'rows' => $rows,
            'eligible_rows' => $trackerRows,
            'generated_at' => now(),
        ];
    }

    private function buildSummarySheet($sheet, array $report): void
    {
        $sheet->setTitle('Ringkasan');

        $summary = $report['summary'];
        $filters = $report['filters'];
        $perJalur = $report['per_jalur'];
        $checkerStatus = $report['checker_status'];
        $trackerMeta = $report['tracker_meta'];

        $sheet->setCellValue('A1', 'Laporan Statistik Lulusan');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $sheet->setCellValue('A2', 'Tahun Pelajaran');
        $sheet->setCellValue('B2', $report['selectedTahun']->nama);
        $sheet->setCellValue('D2', 'Diexport');
        $sheet->setCellValue('E2', $report['generated_at']->format('d-m-Y H:i:s'));

        $row = 4;
        $sheet->setCellValue("A{$row}", 'Filter Aktif');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($filters as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }

        $row++;
        $sheet->setCellValue("A{$row}", 'Ringkasan Utama');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        foreach ([
            'Total Siswa Kelas 12' => $summary['total'],
            'Sudah Mengisi Lulusan' => $summary['sudah_isi'],
            'Belum Mengisi Lulusan' => $summary['belum_isi'],
            'Total Universitas Tujuan' => $summary['total_universitas'],
            $trackerMeta['summary_total_label'] => $summary['eligible_total'],
            $trackerMeta['summary_number_label'] => $summary['eligible_sudah_isi_nomor'],
            $trackerMeta['summary_missing_number_label'] => $summary['eligible_belum_isi_nomor'],
            $trackerMeta['summary_passed_label'] => $summary['eligible_lulus'],
            $trackerMeta['summary_failed_label'] => $summary['eligible_tidak_lulus'],
            $trackerMeta['summary_error_label'] => $summary['eligible_gagal_cek'],
            $trackerMeta['summary_pending_label'] => $summary['eligible_belum_dicek'],
            $trackerMeta['accepted_university_summary_label'] => $summary['total_ptn_diterima'],
        ] as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }

        $row++;
        $sheet->setCellValue("A{$row}", 'Statistik Per Jalur');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue("A{$row}", 'Jalur');
        $sheet->setCellValue("B{$row}", 'Jumlah');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($perJalur as $jalur => $jumlah) {
            $sheet->setCellValue("A{$row}", $jalur);
            $sheet->setCellValue("B{$row}", $jumlah);
            $row++;
        }

        $row++;
        $sheet->setCellValue("A{$row}", $trackerMeta['checker_title']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue("A{$row}", 'Status');
        $sheet->setCellValue("B{$row}", 'Jumlah');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ([
            'Belum Dicek' => $checkerStatus['belum_dicek'],
            'Lulus' => $checkerStatus['lulus'],
            'Tidak Lulus' => $checkerStatus['tidak_lulus'],
            'Gagal Cek' => $checkerStatus['gagal_cek'],
        ] as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }

        $this->writeTopTable($sheet, 'D4', 'Top Universitas Tujuan', 'Universitas', $report['top_universitas']);
        $this->writeTopTable($sheet, 'G4', $trackerMeta['top_university_title'], $trackerMeta['accepted_university_short_label'], $report['top_tracker_universitas']);
        $this->writeTopTable($sheet, 'J4', $trackerMeta['top_program_title'], 'Program Studi', $report['top_tracker_prodi']);
        $this->writeMatrixTable($sheet, 'D20', $report['per_kelas'], $trackerMeta['matrix_tracker_label']);

        foreach (range(1, 12) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }

        $sheet->freezePane('A4');
    }

    private function buildDetailSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Daftar Lulusan');
        // Gunakan nilai mesin (ALL/SNBP/SPAN-PTKIN), bukan label tampilan
        // seperti "Semua Jalur", agar kolom checker mengambil sumber yang benar.
        $trackerType = $report['tracker_type'] ?? 'ALL';
        $trackerMeta = $report['tracker_meta'];

        $headers = ['NISN', 'Nama Siswa', 'Kelas', 'Tanggal Lahir', 'Status Pengisian', 'Jalur Masuk', 'Universitas', 'Jurusan/Fakultas', 'Program Studi', $trackerMeta['number_column_label'], $trackerMeta['checker_title'], 'Hasil Checker', 'Terakhir Dicek'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
        $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:M1');

        $rowNumber = 2;
        foreach ($report['rows'] as $row) {
            $trackerData = $this->extractRowTrackerData($row, $trackerType);
            $this->setTextCell($sheet, "A{$rowNumber}", $row->nisn);
            $sheet->setCellValue("B{$rowNumber}", $row->nama_lengkap);
            $sheet->setCellValue("C{$rowNumber}", $row->kelas_nama);
            $sheet->setCellValue("D{$rowNumber}", $this->formatDateValue($row->tanggal_lahir));
            $sheet->setCellValue("E{$rowNumber}", $row->is_filled ? 'Sudah Isi' : 'Belum Isi');
            $sheet->setCellValue("F{$rowNumber}", $row->jalur_masuk ?: '-');
            $sheet->setCellValue("G{$rowNumber}", $row->nama_universitas ?: '-');
            $sheet->setCellValue("H{$rowNumber}", $row->jurusan_fakultas ?: '-');
            $sheet->setCellValue("I{$rowNumber}", $row->program_studi ?: '-');
            $this->setTextCell($sheet, "J{$rowNumber}", $trackerData['number'] ?: '-');
            $sheet->setCellValue("K{$rowNumber}", $this->formatCheckStatusLabel($trackerData['status'], $trackerData['has_number'], $trackerData['type']));
            $sheet->setCellValue("L{$rowNumber}", $this->formatTrackerResultLabel($trackerData['status'], $trackerData['has_number']));
            $sheet->setCellValue("M{$rowNumber}", $this->formatDateTimeValue($trackerData['last_checked_at']));
            $rowNumber++;
        }

        $sheet->getStyle('A1:M' . max(1, $rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:M' . max(1, $rowNumber - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        foreach (range(1, 13) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
    }

    private function buildEligibleSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet();
        $trackerMeta = $report['tracker_meta'];
        $sheet->setTitle($trackerMeta['monitoring_sheet_title']);

        $headers = ['NISN', 'Nama Siswa', 'Kelas', 'Tanggal Lahir', $trackerMeta['number_column_label'], $trackerMeta['checker_title'], 'Hasil Checker', 'Terakhir Dicek', 'Status Lulusan', 'Jalur Masuk', $trackerMeta['accepted_university_short_label'], 'Program Studi'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2F3');
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:L1');

        $rowNumber = 2;
        foreach ($report['eligible_rows'] as $row) {
            $rowTrackerType = $row->tracker_type ?? ($report['tracker_type'] ?? 'SNBP');
            $this->setTextCell($sheet, "A{$rowNumber}", $row->nisn);
            $sheet->setCellValue("B{$rowNumber}", $row->nama_lengkap);
            $sheet->setCellValue("C{$rowNumber}", $row->kelas_nama ?: '-');
            $sheet->setCellValue("D{$rowNumber}", $this->formatDateValue($row->tanggal_lahir));
            $this->setTextCell($sheet, "E{$rowNumber}", $row->nomor_pendaftaran ?: '-');
            $sheet->setCellValue("F{$rowNumber}", $this->formatCheckStatusLabel($row->check_status, true, $rowTrackerType));
            $sheet->setCellValue("G{$rowNumber}", $this->formatTrackerResultLabel($row->check_status, true));
            $sheet->setCellValue("H{$rowNumber}", $this->formatDateTimeValue($row->last_checked_at));
            $sheet->setCellValue("I{$rowNumber}", $row->is_filled ? 'Sudah Isi' : 'Belum Isi');
            $sheet->setCellValue("J{$rowNumber}", $row->jalur_masuk ?: '-');
            $sheet->setCellValue("K{$rowNumber}", $row->nama_universitas ?: '-');
            $sheet->setCellValue("L{$rowNumber}", $row->program_studi ?: '-');
            $rowNumber++;
        }

        $sheet->getStyle('A1:L' . max(1, $rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:L' . max(1, $rowNumber - 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        foreach (range(1, 12) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
    }

    private function writeTopTable($sheet, string $startCell, string $title, string $labelHeader, array $rows): void
    {
        [$column, $row] = Coordinate::coordinateFromString($startCell);
        $columnIndex = Coordinate::columnIndexFromString($column);
        $labelColumn = Coordinate::stringFromColumnIndex($columnIndex);
        $valueColumn = Coordinate::stringFromColumnIndex($columnIndex + 1);

        $sheet->setCellValue($labelColumn . $row, $title);
        $sheet->getStyle($labelColumn . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue($labelColumn . $row, $labelHeader);
        $sheet->setCellValue($valueColumn . $row, 'Jumlah');
        $sheet->getStyle($labelColumn . $row . ':' . $valueColumn . $row)->getFont()->setBold(true);
        $row++;

        if (empty($rows)) {
            $sheet->setCellValue($labelColumn . $row, 'Belum ada data');
            return;
        }

        foreach ($rows as $item) {
            $sheet->setCellValue($labelColumn . $row, $item['label']);
            $sheet->setCellValue($valueColumn . $row, $item['jumlah']);
            $row++;
        }
    }

    private function writeMatrixTable($sheet, string $startCell, array $perKelas, string $trackerLabel = 'Lulus SNBP'): void
    {
        [$column, $row] = Coordinate::coordinateFromString($startCell);
        $startIndex = Coordinate::columnIndexFromString($column);

        $sheet->setCellValue($this->cellAddress($startIndex, $row), 'Matriks Per Kelas');
        $sheet->getStyle($this->cellAddress($startIndex, $row))->getFont()->setBold(true);
        $row++;

        foreach (['Kelas', 'Eligible', $trackerLabel, 'Tidak Lulus', 'Sudah Isi', 'Belum Isi', 'Total'] as $offset => $header) {
            $sheet->setCellValue($this->cellAddress($startIndex + $offset, $row), $header);
        }
        $sheet->getStyle($this->cellRange($startIndex, $row, $startIndex + 6, $row))->getFont()->setBold(true);
        $row++;

        if (empty($perKelas)) {
            $sheet->setCellValue($this->cellAddress($startIndex, $row), 'Belum ada data');
            return;
        }

        foreach ($perKelas as $item) {
            $sheet->setCellValue($this->cellAddress($startIndex, $row), $item['kelas_nama']);
            $sheet->setCellValue($this->cellAddress($startIndex + 1, $row), $item['eligible']);
            $sheet->setCellValue($this->cellAddress($startIndex + 2, $row), $item['eligible_lulus']);
            $sheet->setCellValue($this->cellAddress($startIndex + 3, $row), $item['eligible_tidak_lulus'] ?? 0);
            $sheet->setCellValue($this->cellAddress($startIndex + 4, $row), $item['sudah_isi']);
            $sheet->setCellValue($this->cellAddress($startIndex + 5, $row), $item['belum_isi']);
            $sheet->setCellValue($this->cellAddress($startIndex + 6, $row), $item['total']);
            $row++;
        }
    }

    private function cellAddress(int $columnIndex, int $row): string
    {
        return Coordinate::stringFromColumnIndex($columnIndex) . $row;
    }

    private function cellRange(int $startColumnIndex, int $startRow, int $endColumnIndex, int $endRow): string
    {
        return $this->cellAddress($startColumnIndex, $startRow) . ':' . $this->cellAddress($endColumnIndex, $endRow);
    }

    private function setTextCell($sheet, string $cell, mixed $value): void
    {
        $sheet->setCellValueExplicit($cell, (string) ($value ?? ''), DataType::TYPE_STRING);
    }

    private function buildTopList(Collection $rows, string $field, int $limit = 10): array
    {
        return $rows
            ->pluck($field)
            ->filter()
            ->groupBy(fn ($value) => $value)
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'jumlah' => $group->count(),
            ])
            ->sortByDesc('jumlah')
            ->take($limit)
            ->values()
            ->all();
    }

    private function makeInitials(?string $name): string
    {
        $words = collect(preg_split('/\s+/', trim((string) $name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)));

        return $words->isNotEmpty() ? $words->implode('') : 'S';
    }

    private function resolveStudentPhotoPath(?string $photo): ?string
    {
        if (blank($photo)) {
            return null;
        }

        $path = storage_path('app/public/' . ltrim($photo, '/'));

        return File::exists($path) ? $path : null;
    }

    private function formatFilters(Request $request, ?TahunPelajaran $selectedTahun): array
    {
        return [
            'Tahun Pelajaran' => $selectedTahun?->nama ?? '-',
            'Status Pengisian' => match ($request->status_pengisian) {
                'sudah_isi' => 'Sudah Isi',
                'belum_isi' => 'Belum Isi',
                default => 'Semua Status',
            },
            'Mode Checker' => $this->buildTrackerMeta($this->resolveTrackerType($request))['type'],
            'Jalur Masuk' => $request->jalur_masuk ?: 'Semua Jalur',
            'Kelas' => $request->kelas_nama ?: 'Semua Kelas',
            'Pencarian' => $request->q ?: '-',
        ];
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
            ->leftJoin('users', 'users.id', '=', 'siswa.user_id')
            ->join('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->leftJoin('siswa_lulusan', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_lulusan.siswa_id', '=', 'siswa.id')
                    ->where('siswa_lulusan.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereNull('siswa_lulusan.deleted_at');
            })
            ->leftJoin('snbp_registrations', function ($join) use ($tahunPelajaranId) {
                $join->on('snbp_registrations.siswa_id', '=', 'siswa.id')
                    ->where('snbp_registrations.tahun_pelajaran_id', '=', $tahunPelajaranId);
            })
            ->leftJoin('span_ptkin_registrations', function ($join) use ($tahunPelajaranId) {
                $join->on('span_ptkin_registrations.siswa_id', '=', 'siswa.id')
                    ->where('span_ptkin_registrations.tahun_pelajaran_id', '=', $tahunPelajaranId);
            })
            ->where('siswa_kelas.tahun_pelajaran_id', $tahunPelajaranId)
            ->whereNull('siswa_kelas.deleted_at')
            ->whereIn('siswa_kelas.status', self::LULUSAN_CLASS_STATUSES)
            ->where('kelas.tingkat', 12)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.tanggal_lahir',
                'users.email',
                'kelas.nama_kelas as kelas_nama',
                'siswa_lulusan.jalur_masuk',
                DB::raw("COALESCE(NULLIF(siswa_lulusan.nama_universitas_manual, ''), siswa_lulusan.nama_universitas) as nama_universitas"),
                'siswa_lulusan.jurusan_fakultas',
                DB::raw("COALESCE(NULLIF(siswa_lulusan.program_studi_manual, ''), siswa_lulusan.program_studi) as program_studi"),
                DB::raw('CASE WHEN siswa_lulusan.id IS NULL THEN 0 ELSE 1 END as is_filled'),
                'snbp_registrations.nomor_pendaftaran',
                'snbp_registrations.last_checked_at',
                DB::raw("COALESCE(snbp_registrations.check_status, 'belum_dicek') as snbp_check_status"),
                DB::raw("CASE WHEN snbp_registrations.nomor_pendaftaran IS NULL OR snbp_registrations.nomor_pendaftaran = '' THEN 0 ELSE 1 END as has_snbp_number"),
                'span_ptkin_registrations.nomor_pendaftaran as span_ptkin_nomor_pendaftaran',
                'span_ptkin_registrations.last_checked_at as span_ptkin_last_checked_at',
                DB::raw("COALESCE(span_ptkin_registrations.check_status, 'belum_dicek') as span_ptkin_check_status"),
                DB::raw("CASE WHEN span_ptkin_registrations.nomor_pendaftaran IS NULL OR span_ptkin_registrations.nomor_pendaftaran = '' THEN 0 ELSE 1 END as has_span_ptkin_number"),
            ])
            ->distinct();

        $this->applyCommonFilters($query, $request);

        return $query;
    }

    private function buildEligibleQuery(Request $request, string $tahunPelajaranId)
    {
        $query = DB::table('snbp_siswa')
            ->join('snbp_menus', 'snbp_menus.id', '=', 'snbp_siswa.snbp_menu_id')
            ->join('siswa', 'siswa.id', '=', 'snbp_siswa.siswa_id')
            ->leftJoin('siswa_kelas', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereIn('siswa_kelas.status', self::LULUSAN_CLASS_STATUSES)
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->leftJoin('snbp_registrations', function ($join) use ($tahunPelajaranId) {
                $join->on('snbp_registrations.siswa_id', '=', 'siswa.id')
                    ->where('snbp_registrations.tahun_pelajaran_id', '=', $tahunPelajaranId);
            })
            ->leftJoin('span_ptkin_registrations', function ($join) use ($tahunPelajaranId) {
                $join->on('span_ptkin_registrations.siswa_id', '=', 'siswa.id')
                    ->where('span_ptkin_registrations.tahun_pelajaran_id', '=', $tahunPelajaranId);
            })
            ->leftJoin('siswa_lulusan', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_lulusan.siswa_id', '=', 'siswa.id')
                    ->where('siswa_lulusan.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereNull('siswa_lulusan.deleted_at');
            })
            ->where('snbp_menus.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('snbp_siswa.is_eligible', true)
            ->where('kelas.tingkat', 12)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.tanggal_lahir',
                'siswa.foto_profile',
                'kelas.nama_kelas as kelas_nama',
                'snbp_registrations.nomor_pendaftaran',
                DB::raw("COALESCE(snbp_registrations.check_status, 'belum_dicek') as check_status"),
                'snbp_registrations.last_checked_at',
                'siswa_lulusan.jalur_masuk',
                DB::raw("COALESCE(NULLIF(siswa_lulusan.nama_universitas_manual, ''), siswa_lulusan.nama_universitas) as nama_universitas"),
                DB::raw("COALESCE(NULLIF(siswa_lulusan.program_studi_manual, ''), siswa_lulusan.program_studi) as program_studi"),
                DB::raw('CASE WHEN siswa_lulusan.id IS NULL THEN 0 ELSE 1 END as is_filled'),
            ])
            ->distinct();

        $this->applyCommonFilters($query, $request);

        return $query;
    }

    private function buildSpanPtkinQuery(Request $request, string $tahunPelajaranId)
    {
        $query = DB::table('span_ptkin_registrations')
            ->join('siswa', 'siswa.id', '=', 'span_ptkin_registrations.siswa_id')
            ->leftJoin('siswa_kelas', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_kelas.siswa_id', '=', 'siswa.id')
                    ->where('siswa_kelas.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereIn('siswa_kelas.status', self::LULUSAN_CLASS_STATUSES)
                    ->whereNull('siswa_kelas.deleted_at');
            })
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->leftJoin('snbp_registrations', function ($join) use ($tahunPelajaranId) {
                $join->on('snbp_registrations.siswa_id', '=', 'siswa.id')
                    ->where('snbp_registrations.tahun_pelajaran_id', '=', $tahunPelajaranId);
            })
            ->leftJoin('siswa_lulusan', function ($join) use ($tahunPelajaranId) {
                $join->on('siswa_lulusan.siswa_id', '=', 'siswa.id')
                    ->where('siswa_lulusan.tahun_pelajaran_id', '=', $tahunPelajaranId)
                    ->whereNull('siswa_lulusan.deleted_at');
            })
            ->where('span_ptkin_registrations.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('kelas.tingkat', 12)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.tanggal_lahir',
                'siswa.foto_profile',
                'kelas.nama_kelas as kelas_nama',
                'span_ptkin_registrations.nomor_pendaftaran',
                DB::raw("COALESCE(span_ptkin_registrations.check_status, 'belum_dicek') as check_status"),
                'span_ptkin_registrations.last_checked_at',
                'siswa_lulusan.jalur_masuk',
                DB::raw("COALESCE(NULLIF(siswa_lulusan.nama_universitas_manual, ''), siswa_lulusan.nama_universitas) as nama_universitas"),
                DB::raw("COALESCE(NULLIF(siswa_lulusan.program_studi_manual, ''), siswa_lulusan.program_studi) as program_studi"),
                DB::raw('CASE WHEN siswa_lulusan.id IS NULL THEN 0 ELSE 1 END as is_filled'),
            ])
            ->distinct();

        $this->applyCommonFilters($query, $request);

        return $query;
    }

    private function buildTrackerQuery(Request $request, string $tahunPelajaranId, ?string $trackerType)
    {
        if ($trackerType === 'SPAN-PTKIN') {
            return $this->buildSpanPtkinQuery($request, $tahunPelajaranId);
        }

        return $this->buildEligibleQuery($request, $tahunPelajaranId);
    }

    private function buildTrackerRows(Request $request, string $tahunPelajaranId, ?string $trackerType): Collection
    {
        if ($trackerType === 'ALL') {
            $snbpRows = collect(
                $this->buildEligibleQuery($request, $tahunPelajaranId)
                    ->orderBy('kelas_nama')
                    ->orderBy('nama_lengkap')
                    ->get()
            )->map(function ($row) {
                $row->tracker_type = 'SNBP';
                return $row;
            });

            $spanRows = collect(
                $this->buildSpanPtkinQuery($request, $tahunPelajaranId)
                    ->orderBy('kelas_nama')
                    ->orderBy('nama_lengkap')
                    ->get()
            )->map(function ($row) {
                $row->tracker_type = 'SPAN-PTKIN';
                return $row;
            });

            return $snbpRows
                ->concat($spanRows)
                ->sortBy([
                    ['kelas_nama', 'asc'],
                    ['nama_lengkap', 'asc'],
                    ['tracker_type', 'asc'],
                ])
                ->values();
        }

        return collect(
            $this->buildTrackerQuery($request, $tahunPelajaranId, $trackerType)
                ->orderBy('kelas_nama')
                ->orderBy('nama_lengkap')
                ->get()
        )->map(function ($row) use ($trackerType) {
            $row->tracker_type = $trackerType ?: 'SNBP';
            return $row;
        });
    }

    private function buildPerJalurStats(Collection $rows): array
    {
        return collect(SiswaLulusan::JALUR_MASUK)
            ->mapWithKeys(fn (string $jalur) => [$jalur => $rows->where('jalur_masuk', $jalur)->count()])
            ->all();
    }

    private function buildCheckerLinksByTahun(Collection $tahunPelajaranList): array
    {
        $tahunIds = $tahunPelajaranList->pluck('id');
        $snbpMenus = SnbpMenu::whereIn('tahun_pelajaran_id', $tahunIds)
            ->get(['id', 'tahun_pelajaran_id'])
            ->keyBy('tahun_pelajaran_id');
        $spanMenus = SpanPtkinMenu::whereIn('tahun_pelajaran_id', $tahunIds)
            ->get(['id', 'tahun_pelajaran_id'])
            ->keyBy('tahun_pelajaran_id');

        return $tahunPelajaranList->mapWithKeys(function (TahunPelajaran $tahun) use ($snbpMenus, $spanMenus) {
            $snbpMenu = $snbpMenus->get($tahun->id);
            $spanMenu = $spanMenus->get($tahun->id);

            return [$tahun->id => [
                'snbp' => $snbpMenu ? route('admin.snbp-menu.show', $snbpMenu) : route('admin.snbp-menu.index'),
                'span_ptkin' => $spanMenu ? route('admin.span-ptkin-menu.show', $spanMenu) : route('admin.span-ptkin-menu.index'),
                'has_snbp' => (bool) $snbpMenu,
                'has_span_ptkin' => (bool) $spanMenu,
            ]];
        })->all();
    }

    private function applyCommonFilters($query, Request $request): void
    {
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
                    ->orWhere('snbp_registrations.nomor_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('span_ptkin_registrations.nomor_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.nama_universitas', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.nama_universitas_manual', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.jurusan_fakultas', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.program_studi', 'like', "%{$search}%")
                    ->orWhere('siswa_lulusan.program_studi_manual', 'like', "%{$search}%");
            });
        }
    }

    private function emptySummary(): array
    {
        return [
            'total' => 0,
            'sudah_isi' => 0,
            'belum_isi' => 0,
            'total_universitas' => 0,
            'eligible_total' => 0,
            'eligible_sudah_isi_nomor' => 0,
            'eligible_belum_isi_nomor' => 0,
            'eligible_lulus' => 0,
            'eligible_tidak_lulus' => 0,
            'eligible_gagal_cek' => 0,
            'eligible_belum_dicek' => 0,
            'total_ptn_diterima' => 0,
        ];
    }

    private function emptyCheckerStatus(): array
    {
        return [
            'belum_dicek' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'gagal_cek' => 0,
        ];
    }

    private function defaultTrackerMeta(): array
    {
        return $this->buildTrackerMeta('ALL');
    }

    private function buildTrackerMeta(?string $trackerType): array
    {
        if ($trackerType === 'ALL') {
            return [
                'type' => 'Semua Jalur',
                'summary_total_label' => 'Peserta Checker',
                'summary_number_label' => 'Sudah Ada Nomor',
                'summary_missing_number_label' => 'Belum Ada Nomor',
                'summary_passed_label' => 'Lulus Checker',
                'summary_failed_label' => 'Tidak Lulus Checker',
                'summary_error_label' => 'Gagal Cek',
                'summary_pending_label' => 'Belum Dicek',
                'checker_title' => 'Status Checker Semua Jalur',
                'top_university_title' => 'Top Kampus Diterima Checker',
                'top_program_title' => 'Top Prodi Diterima Checker',
                'checker_column_label' => 'Checker',
                'result_column_label' => 'Hasil Checker',
                'matrix_tracker_label' => 'Lulus Checker',
                'empty_university_text' => 'Belum ada siswa diterima via checker.',
                'empty_program_text' => 'Belum ada prodi dari checker.',
                'status_passed_label' => 'Lulus Checker',
                'number_column_label' => 'Nomor Pendaftaran',
                'accepted_university_short_label' => 'Kampus',
                'accepted_university_summary_label' => 'Kampus Diterima dari Checker',
                'monitoring_sheet_title' => 'Monitoring Checker',
            ];
        }

        if ($trackerType === 'SPAN-PTKIN') {
            return [
                'type' => 'SPAN-PTKIN',
                'summary_total_label' => 'Peserta SPAN-PTKIN',
                'summary_number_label' => 'Sudah Import Nomor',
                'summary_missing_number_label' => 'Belum Import Nomor',
                'summary_passed_label' => 'Lulus SPAN-PTKIN',
                'summary_failed_label' => 'Tidak Lulus SPAN-PTKIN',
                'summary_error_label' => 'Gagal Cek SPAN-PTKIN',
                'summary_pending_label' => 'Belum Dicek SPAN-PTKIN',
                'checker_title' => 'Status Checker SPAN-PTKIN',
                'top_university_title' => 'Top PTKIN Diterima SPAN-PTKIN',
                'top_program_title' => 'Top Prodi Diterima SPAN-PTKIN',
                'checker_column_label' => 'Checker SPAN-PTKIN',
                'result_column_label' => 'Hasil SPAN-PTKIN',
                'matrix_tracker_label' => 'Lulus SPAN-PTKIN',
                'empty_university_text' => 'Belum ada siswa diterima via SPAN-PTKIN.',
                'empty_program_text' => 'Belum ada prodi SPAN-PTKIN.',
                'status_passed_label' => 'Lulus SPAN-PTKIN',
                'number_column_label' => 'Nomor Pendaftaran SPAN-PTKIN',
                'accepted_university_short_label' => 'PTKIN',
                'accepted_university_summary_label' => 'PTKIN Diterima dari SPAN-PTKIN',
                'monitoring_sheet_title' => 'Monitoring SPAN-PTKIN',
            ];
        }

        return [
            'type' => 'SNBP',
            'summary_total_label' => 'Eligible SNBP',
            'summary_number_label' => 'Sudah Isi Nomor SNBP',
            'summary_missing_number_label' => 'Belum Isi Nomor SNBP',
            'summary_passed_label' => 'Lulus SNBP',
            'summary_failed_label' => 'Tidak Lulus SNBP',
            'summary_error_label' => 'Gagal Cek SNBP',
            'summary_pending_label' => 'Belum Dicek SNBP',
            'checker_title' => 'Status Checker SNBP',
            'top_university_title' => 'Top PTN Diterima SNBP',
            'top_program_title' => 'Top Prodi Diterima SNBP',
            'checker_column_label' => 'Checker SNBP',
            'result_column_label' => 'Hasil SNBP',
            'matrix_tracker_label' => 'Lulus SNBP',
            'empty_university_text' => 'Belum ada siswa diterima via SNBP.',
            'empty_program_text' => 'Belum ada prodi SNBP.',
            'status_passed_label' => 'Lulus SNBP',
            'number_column_label' => 'Nomor Pendaftaran SNBP',
            'accepted_university_short_label' => 'PTN',
            'accepted_university_summary_label' => 'PTN Diterima dari SNBP',
            'monitoring_sheet_title' => 'Monitoring Eligible',
        ];
    }

    private function resolveTrackerType(Request $request): ?string
    {
        if (in_array($request->input('tracker_type'), ['ALL', 'SNBP', 'SPAN-PTKIN'], true)) {
            return $request->input('tracker_type');
        }

        return match ($request->input('jalur_masuk')) {
            'SPAN-PTKIN' => 'SPAN-PTKIN',
            'SNBP' => 'SNBP',
            default => 'ALL',
        };
    }

    private function statusBadgeColor(?string $status): string
    {
        return match ($status) {
            'lulus' => 'success',
            'tidak_lulus' => 'danger',
            'gagal_cek' => 'warning',
            default => 'secondary',
        };
    }

    private function resultBadgeColor(?string $status): string
    {
        return match ($status) {
            'lulus' => 'success',
            'tidak_lulus' => 'danger',
            'gagal_cek' => 'warning',
            default => 'secondary',
        };
    }

    private function formatTrackerStatusLabel(?string $status, string $trackerType): string
    {
        return match ($status) {
            'lulus' => 'Lulus ' . $trackerType,
            'tidak_lulus' => 'Tidak Lulus',
            'gagal_cek' => 'Gagal Cek',
            default => 'Belum Dicek',
        };
    }

    private function formatTrackerResultLabel(?string $status, bool $hasNumber = true): string
    {
        if (!$hasNumber) {
            return '-';
        }

        return match ($status) {
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'gagal_cek' => 'Gagal Cek',
            default => 'Belum Dicek',
        };
    }

    private function formatDateValue($value): string
    {
        if (!$value) {
            return '-';
        }

        return Carbon::parse($value)->format('d-m-Y');
    }

    private function formatDateTimeValue($value): string
    {
        if (!$value) {
            return '-';
        }

        return Carbon::parse($value)->format('d-m-Y H:i');
    }

    private function formatCheckStatusLabel(?string $status, bool $hasTrackerNumber, ?string $trackerType = 'SNBP'): string
    {
        if (!$hasTrackerNumber) {
            return '-';
        }

        return match ($status) {
            'lulus' => 'Lulus ' . $trackerType,
            'tidak_lulus' => 'Tidak Lulus',
            'gagal_cek' => 'Gagal Cek',
            default => 'Belum Dicek',
        };
    }

    private function extractRowTrackerData(object $row, ?string $trackerType): array
    {
        $resolvedTracker = $trackerType;

        if (!$resolvedTracker) {
            $resolvedTracker = match ($row->jalur_masuk) {
                'SPAN-PTKIN' => 'SPAN-PTKIN',
                'SNBP' => 'SNBP',
                default => 'SNBP',
            };
        }

        if ($resolvedTracker === 'ALL') {
            if (($row->has_snbp_number ?? false) && ($row->snbp_check_status ?? null) === 'lulus') {
                $resolvedTracker = 'SNBP';
            } elseif (($row->has_span_ptkin_number ?? false) && ($row->span_ptkin_check_status ?? null) === 'lulus') {
                $resolvedTracker = 'SPAN-PTKIN';
            } elseif ($row->jalur_masuk === 'SPAN-PTKIN' || ($row->has_span_ptkin_number ?? false)) {
                $resolvedTracker = 'SPAN-PTKIN';
            } else {
                $resolvedTracker = 'SNBP';
            }
        }

        if ($resolvedTracker === 'SPAN-PTKIN') {
            return [
                'type' => 'SPAN-PTKIN',
                'number' => $row->span_ptkin_nomor_pendaftaran ?? null,
                'has_number' => (bool) ($row->has_span_ptkin_number ?? false),
                'status' => $row->span_ptkin_check_status ?: 'belum_dicek',
                'last_checked_at' => $row->span_ptkin_last_checked_at ?? null,
            ];
        }

        return [
            'type' => 'SNBP',
            'number' => $row->nomor_pendaftaran ?? null,
            'has_number' => (bool) ($row->has_snbp_number ?? false),
            'status' => $row->snbp_check_status ?: 'belum_dicek',
            'last_checked_at' => $row->last_checked_at ?? null,
        ];
    }

    private function sanitizeFilenameSegment(string $value): string
    {
        return trim(str_replace(['\\', '/', ' '], ['-', '-', '_'], $value), '-_');
    }
}
