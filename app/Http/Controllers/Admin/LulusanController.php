<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiswaLulusan;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class LulusanController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $selectedTahun = $this->resolveSelectedTahun($request, $tahunPelajaranList);

        return view('admin.lulusan.index', [
            'tahunPelajaranList' => $tahunPelajaranList,
            'selectedTahun' => $selectedTahun,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
        ]);
    }

    public function data(Request $request)
    {
        $selectedTahun = $this->resolveSelectedTahun($request);

        if (!$selectedTahun) {
            return DataTables::of(collect())->make(true);
        }

        $query = $this->buildBaseQuery($request, $selectedTahun->id);

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
            ->addColumn('snbp_check_badge', function ($row) {
                if (!$row->has_snbp_number) {
                    return '<span class="text-muted">-</span>';
                }

                $badges = [
                    'lulus' => 'success',
                    'tidak_lulus' => 'danger',
                    'gagal_cek' => 'warning',
                    'belum_dicek' => 'secondary',
                ];

                $labels = [
                    'lulus' => 'Lulus SNBP',
                    'tidak_lulus' => 'Tidak Lulus',
                    'gagal_cek' => 'Gagal Cek',
                    'belum_dicek' => 'Belum Dicek',
                ];

                $status = $row->snbp_check_status ?: 'belum_dicek';

                return '<span class="badge badge-' . ($badges[$status] ?? 'secondary') . '">' . e($labels[$status] ?? 'Belum Dicek') . '</span>';
            })
            ->editColumn('nama_universitas', fn ($row) => $row->nama_universitas ?: '-')
            ->editColumn('jurusan_fakultas', fn ($row) => $row->jurusan_fakultas ?: '-')
            ->editColumn('program_studi', fn ($row) => $row->program_studi ?: '-')
            ->rawColumns(['status_badge', 'jalur_badge', 'snbp_check_badge'])
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
                'top_ptn_snbp' => [],
                'top_prodi_snbp' => [],
            ]);
        }

        return response()->json([
            'summary' => $report['summary'],
            'per_jalur' => $report['per_jalur'],
            'per_kelas' => $report['per_kelas'],
            'top_universitas' => $report['top_universitas'],
            'checker_status' => $report['checker_status'],
            'top_ptn_snbp' => $report['top_ptn_snbp'],
            'top_prodi_snbp' => $report['top_prodi_snbp'],
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
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
                'top_ptn_snbp' => [],
                'top_prodi_snbp' => [],
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

        $eligibleRows = collect(
            $this->buildEligibleQuery($request, $selectedTahun->id)
                ->orderBy('kelas_nama')
                ->orderBy('nama_lengkap')
                ->get()
        );

        $summary = [
            'total' => $rows->count(),
            'sudah_isi' => $rows->where('is_filled', 1)->count(),
            'belum_isi' => $rows->where('is_filled', 0)->count(),
            'total_universitas' => $rows->pluck('nama_universitas')->filter()->unique()->count(),
            'eligible_total' => $eligibleRows->count(),
            'eligible_sudah_isi_nomor' => $eligibleRows->filter(fn ($row) => filled($row->nomor_pendaftaran))->count(),
            'eligible_belum_isi_nomor' => $eligibleRows->filter(fn ($row) => blank($row->nomor_pendaftaran))->count(),
            'eligible_lulus' => $eligibleRows->where('check_status', 'lulus')->count(),
            'eligible_tidak_lulus' => $eligibleRows->where('check_status', 'tidak_lulus')->count(),
            'eligible_gagal_cek' => $eligibleRows->where('check_status', 'gagal_cek')->count(),
            'eligible_belum_dicek' => $eligibleRows->where('check_status', 'belum_dicek')->count(),
            'total_ptn_diterima' => $eligibleRows->where('check_status', 'lulus')->pluck('nama_universitas')->filter()->unique()->count(),
        ];

        $perJalur = collect(SiswaLulusan::JALUR_MASUK)
            ->mapWithKeys(fn (string $jalur) => [$jalur => $rows->where('jalur_masuk', $jalur)->count()])
            ->all();

        $eligibleByClass = $eligibleRows->groupBy(fn ($row) => $row->kelas_nama ?: 'Tanpa Kelas');

        $perKelas = $rows
            ->groupBy(fn ($row) => $row->kelas_nama ?: 'Tanpa Kelas')
            ->map(function (Collection $kelasRows, string $kelasNama) use ($perJalur, $eligibleByClass) {
                $jalur = [];
                foreach (array_keys($perJalur) as $jalurMasuk) {
                    $jalur[$jalurMasuk] = $kelasRows->where('jalur_masuk', $jalurMasuk)->count();
                }

                $kelasEligibleRows = $eligibleByClass->get($kelasNama, collect());

                return [
                    'kelas_nama' => $kelasNama,
                    'total' => $kelasRows->count(),
                    'sudah_isi' => $kelasRows->where('is_filled', 1)->count(),
                    'belum_isi' => $kelasRows->where('is_filled', 0)->count(),
                    'eligible' => $kelasEligibleRows->count(),
                    'eligible_lulus' => $kelasEligibleRows->where('check_status', 'lulus')->count(),
                    'jalur' => $jalur,
                ];
            })
            ->values()
            ->all();

        $acceptedStudents = $eligibleRows
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
                'belum_dicek' => $eligibleRows->where('check_status', 'belum_dicek')->count(),
                'lulus' => $eligibleRows->where('check_status', 'lulus')->count(),
                'tidak_lulus' => $eligibleRows->where('check_status', 'tidak_lulus')->count(),
                'gagal_cek' => $eligibleRows->where('check_status', 'gagal_cek')->count(),
            ],
            'top_ptn_snbp' => $this->buildTopList($eligibleRows->where('check_status', 'lulus'), 'nama_universitas', 10),
            'top_prodi_snbp' => $this->buildTopList($eligibleRows->where('check_status', 'lulus'), 'program_studi', 10),
            'accepted_students' => $acceptedStudents,
            'rows' => $rows,
            'eligible_rows' => $eligibleRows,
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
            'Total Eligible SNBP' => $summary['eligible_total'],
            'Eligible Sudah Isi Nomor' => $summary['eligible_sudah_isi_nomor'],
            'Eligible Belum Isi Nomor' => $summary['eligible_belum_isi_nomor'],
            'Eligible Lulus SNBP' => $summary['eligible_lulus'],
            'Eligible Belum Dicek' => $summary['eligible_belum_dicek'],
            'PTN Diterima dari SNBP' => $summary['total_ptn_diterima'],
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
        $sheet->setCellValue("A{$row}", 'Status Checker SNBP');
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
        $this->writeTopTable($sheet, 'G4', 'Top PTN Diterima SNBP', 'PTN', $report['top_ptn_snbp']);
        $this->writeTopTable($sheet, 'J4', 'Top Prodi Diterima SNBP', 'Program Studi', $report['top_prodi_snbp']);
        $this->writeMatrixTable($sheet, 'D20', $report['per_kelas']);

        foreach (range(1, 12) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
    }

    private function buildDetailSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Daftar Lulusan');

        $headers = ['NISN', 'Nama Siswa', 'Kelas', 'Tanggal Lahir', 'Status Pengisian', 'Jalur Masuk', 'Universitas', 'Jurusan/Fakultas', 'Program Studi', 'Nomor SNBP', 'Status Checker SNBP', 'Terakhir Dicek'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');

        $rowNumber = 2;
        foreach ($report['rows'] as $row) {
            $sheet->setCellValue("A{$rowNumber}", $row->nisn);
            $sheet->setCellValue("B{$rowNumber}", $row->nama_lengkap);
            $sheet->setCellValue("C{$rowNumber}", $row->kelas_nama);
            $sheet->setCellValue("D{$rowNumber}", $this->formatDateValue($row->tanggal_lahir));
            $sheet->setCellValue("E{$rowNumber}", $row->is_filled ? 'Sudah Isi' : 'Belum Isi');
            $sheet->setCellValue("F{$rowNumber}", $row->jalur_masuk ?: '-');
            $sheet->setCellValue("G{$rowNumber}", $row->nama_universitas ?: '-');
            $sheet->setCellValue("H{$rowNumber}", $row->jurusan_fakultas ?: '-');
            $sheet->setCellValue("I{$rowNumber}", $row->program_studi ?: '-');
            $sheet->setCellValue("J{$rowNumber}", $row->nomor_pendaftaran ?: '-');
            $sheet->setCellValue("K{$rowNumber}", $this->formatCheckStatusLabel($row->snbp_check_status, $row->has_snbp_number));
            $sheet->setCellValue("L{$rowNumber}", $this->formatDateTimeValue($row->last_checked_at));
            $rowNumber++;
        }

        $sheet->getStyle('A1:L' . max(1, $rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, 12) as $index) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
        }
    }

    private function buildEligibleSheet(Spreadsheet $spreadsheet, array $report): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Monitoring Eligible');

        $headers = ['NISN', 'Nama Siswa', 'Kelas', 'Tanggal Lahir', 'Nomor Pendaftaran SNBP', 'Status Checker', 'Terakhir Dicek', 'Status Lulusan', 'Jalur Masuk', 'PTN', 'Program Studi'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2F3');

        $rowNumber = 2;
        foreach ($report['eligible_rows'] as $row) {
            $sheet->setCellValue("A{$rowNumber}", $row->nisn);
            $sheet->setCellValue("B{$rowNumber}", $row->nama_lengkap);
            $sheet->setCellValue("C{$rowNumber}", $row->kelas_nama ?: '-');
            $sheet->setCellValue("D{$rowNumber}", $this->formatDateValue($row->tanggal_lahir));
            $sheet->setCellValue("E{$rowNumber}", $row->nomor_pendaftaran ?: '-');
            $sheet->setCellValue("F{$rowNumber}", $this->formatCheckStatusLabel($row->check_status, true));
            $sheet->setCellValue("G{$rowNumber}", $this->formatDateTimeValue($row->last_checked_at));
            $sheet->setCellValue("H{$rowNumber}", $row->is_filled ? 'Sudah Isi' : 'Belum Isi');
            $sheet->setCellValue("I{$rowNumber}", $row->jalur_masuk ?: '-');
            $sheet->setCellValue("J{$rowNumber}", $row->nama_universitas ?: '-');
            $sheet->setCellValue("K{$rowNumber}", $row->program_studi ?: '-');
            $rowNumber++;
        }

        $sheet->getStyle('A1:K' . max(1, $rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, 11) as $index) {
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

    private function writeMatrixTable($sheet, string $startCell, array $perKelas): void
    {
        [$column, $row] = Coordinate::coordinateFromString($startCell);
        $startIndex = Coordinate::columnIndexFromString($column);

        $sheet->setCellValueByColumnAndRow($startIndex, $row, 'Matriks Per Kelas');
        $sheet->getStyleByColumnAndRow($startIndex, $row)->getFont()->setBold(true);
        $row++;

        foreach (['Kelas', 'Eligible', 'Lulus SNBP', 'Sudah Isi', 'Belum Isi', 'Total'] as $offset => $header) {
            $sheet->setCellValueByColumnAndRow($startIndex + $offset, $row, $header);
        }
        $sheet->getStyleByColumnAndRow($startIndex, $row, $startIndex + 5, $row)->getFont()->setBold(true);
        $row++;

        if (empty($perKelas)) {
            $sheet->setCellValueByColumnAndRow($startIndex, $row, 'Belum ada data');
            return;
        }

        foreach ($perKelas as $item) {
            $sheet->setCellValueByColumnAndRow($startIndex, $row, $item['kelas_nama']);
            $sheet->setCellValueByColumnAndRow($startIndex + 1, $row, $item['eligible']);
            $sheet->setCellValueByColumnAndRow($startIndex + 2, $row, $item['eligible_lulus']);
            $sheet->setCellValueByColumnAndRow($startIndex + 3, $row, $item['sudah_isi']);
            $sheet->setCellValueByColumnAndRow($startIndex + 4, $row, $item['belum_isi']);
            $sheet->setCellValueByColumnAndRow($startIndex + 5, $row, $item['total']);
            $row++;
        }
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

    private function formatFilters(Request $request, ?TahunPelajaran $selectedTahun): array
    {
        return [
            'Tahun Pelajaran' => $selectedTahun?->nama ?? '-',
            'Status Pengisian' => match ($request->status_pengisian) {
                'sudah_isi' => 'Sudah Isi',
                'belum_isi' => 'Belum Isi',
                default => 'Semua Status',
            },
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
            ->where('siswa_kelas.tahun_pelajaran_id', $tahunPelajaranId)
            ->whereNull('siswa_kelas.deleted_at')
            ->where('siswa_kelas.status', 'aktif')
            ->where('kelas.tingkat', 12)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.tanggal_lahir',
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
                    ->where('siswa_kelas.status', '=', 'aktif')
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
            ->where('snbp_menus.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('snbp_siswa.is_eligible', true)
            ->select([
                'siswa.id as siswa_id',
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.tanggal_lahir',
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

    private function formatCheckStatusLabel(?string $status, bool $hasSnbpNumber): string
    {
        if (!$hasSnbpNumber) {
            return '-';
        }

        return match ($status) {
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'gagal_cek' => 'Gagal Cek',
            default => 'Belum Dicek',
        };
    }

    private function sanitizeFilenameSegment(string $value): string
    {
        return trim(str_replace(['\\', '/', ' '], ['-', '-', '_'], $value), '-_');
    }
}
