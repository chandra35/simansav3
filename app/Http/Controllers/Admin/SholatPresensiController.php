<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SholatPetugasKelas;
use App\Models\SholatRecord;
use App\Models\SholatSession;
use App\Models\SholatSetting;
use App\Models\SholatUdzurRecord;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SholatPresensiController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    /**
     * Lembar Input / Fast Batch Checklist Kehadiran Sholat
     */
    public function index(Request $request)
    {
        $this->authorize('sholat-input');

        $user = $request->user();
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($tanggal);

        // Map nama hari Indonesia ke key
        $dayMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        $currentDayKey = $dayMap[$carbonDate->dayOfWeek] ?? 'senin';

        $tahunAktif = TahunPelajaran::query()->active()->first();

        // Ambil pengaturan sholat yang aktif
        $activeSettings = SholatSetting::query()->active()->ordered()->get();
        if ($activeSettings->isEmpty()) {
            return view('admin.sholat.input', [
                'activeSettings' => collect(),
                'message' => 'Belum ada jenis sholat yang diaktifkan. Silakan aktifkan sholat terlebih dahulu di Pengaturan Sholat.',
            ]);
        }

        $selectedSettingId = $request->input('sholat_setting_id', $activeSettings->first()->id);
        $selectedSetting = $activeSettings->firstWhere('id', $selectedSettingId) ?? $activeSettings->first();

        // Cek kelas yang dapat diakses oleh user
        $accessibleClasses = $this->getAccessibleClasses($user, $tahunAktif);

        if ($accessibleClasses->isEmpty()) {
            abort(403, 'Anda tidak memiliki hak akses pencatatan sholat untuk rombel manapun.');
        }

        $selectedKelasId = (string) $request->input('kelas_id', $accessibleClasses->first()->id);
        $selectedKelas = $accessibleClasses->firstWhere('id', $selectedKelasId) ?? $accessibleClasses->first();

        // Cari sesi sholat yang sudah tersimpan (jika ada)
        $session = SholatSession::query()
            ->with(['records', 'recorder'])
            ->where('tanggal', $tanggal)
            ->where('sholat_setting_id', $selectedSetting->id)
            ->where('kelas_id', $selectedKelas->id)
            ->first();

        // Ambil daftar siswa aktif di kelas ini
        $querySiswa = $selectedKelas->siswas()
            ->where('siswa_kelas.status', 'aktif');

        // Filter gender jika jenis sholat khusus (L/P)
        if ($selectedSetting->target_gender === 'L') {
            $querySiswa->where('siswa.jenis_kelamin', 'L');
        } elseif ($selectedSetting->target_gender === 'P') {
            $querySiswa->where('siswa.jenis_kelamin', 'P');
        }

        $students = $querySiswa->select(
            'siswa.id',
            'siswa.nama_lengkap',
            'siswa.nisn',
            'siswa.jenis_kelamin',
            'siswa_kelas.nomor_urut_absen'
        )
        ->orderByRaw('COALESCE(siswa_kelas.nomor_urut_absen, 999)')
        ->orderBy('siswa.nama_lengkap')
        ->get();

        // Cek riwayat udzur aktif pada tanggal ini untuk siswi putri di rombel ini
        $activeUdzurs = SholatUdzurRecord::query()
            ->whereIn('siswa_id', $students->pluck('id'))
            ->activeOnDate($tanggal)
            ->pluck('siswa_id')
            ->flip()
            ->all();

        // Petakan status siswa
        $existingRecords = $session ? $session->records->keyBy('siswa_id') : collect();

        $studentStatuses = [];
        $summary = [
            'total' => $students->count(),
            'berjamaah' => 0,
            'masbuq' => 0,
            'munfarid' => 0,
            'udzur' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
        ];

        foreach ($students as $student) {
            $isUdzur = isset($activeUdzurs[$student->id]);
            $rec = $existingRecords->get($student->id);

            if ($rec) {
                $status = $rec->status;
                $catatan = $rec->catatan;
            } else {
                // Default value jika belum ada catatan:
                // Jika siswi sedang berstatus udzur syar'i -> otomatis 'udzur'
                // Selain itu -> otomatis 'berjamaah'
                $status = ($isUdzur && $student->jenis_kelamin === 'P')
                    ? SholatRecord::STATUS_UDZUR
                    : SholatRecord::STATUS_BERJAMAAH;
                $catatan = $isUdzur ? 'Udzur Syar\'i' : null;
            }

            if (isset($summary[$status])) {
                $summary[$status]++;
            }

            $studentStatuses[$student->id] = [
                'status' => $status,
                'catatan' => $catatan,
                'is_udzur' => $isUdzur,
            ];
        }

        $statusLabels = SholatRecord::statusLabels();

        return view('admin.sholat.input', compact(
            'tanggal',
            'carbonDate',
            'currentDayKey',
            'activeSettings',
            'selectedSetting',
            'accessibleClasses',
            'selectedKelas',
            'session',
            'students',
            'studentStatuses',
            'statusLabels',
            'summary',
            'tahunAktif'
        ));
    }

    /**
     * Simpan / Perbarui Presensi Sholat
     */
    public function store(Request $request)
    {
        $this->authorize('sholat-input');

        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'sholat_setting_id' => ['required', 'exists:sholat_settings,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'in:berjamaah,masbuq,munfarid,udzur,sakit,izin,alpa'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
            'session_catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $tahunAktif = TahunPelajaran::query()->active()->first();

        $accessibleClasses = $this->getAccessibleClasses($user, $tahunAktif);
        if (! $accessibleClasses->contains('id', $data['kelas_id'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyimpan presensi sholat kelas ini.');
        }

        $kelas = Kelas::findOrFail($data['kelas_id']);
        $setting = SholatSetting::findOrFail($data['sholat_setting_id']);

        DB::transaction(function () use ($data, $user, $tahunAktif, $setting) {
            $session = SholatSession::updateOrCreate(
                [
                    'tanggal' => $data['tanggal'],
                    'sholat_setting_id' => $setting->id,
                    'kelas_id' => $data['kelas_id'],
                ],
                [
                    'tahun_pelajaran_id' => $tahunAktif?->id,
                    'semester' => (int) ($tahunAktif?->semester ?: 1),
                    'status' => 'finalized',
                    'recorded_by' => $user->id,
                    'catatan' => $data['session_catatan'] ?? null,
                ]
            );

            foreach ($data['statuses'] as $siswaId => $status) {
                SholatRecord::updateOrCreate(
                    [
                        'sholat_session_id' => $session->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'status' => $status,
                        'catatan' => $data['catatan'][$siswaId] ?? null,
                    ]
                );
            }
        });

        $formattedTanggal = Carbon::parse($data['tanggal'])->translatedFormat('d F Y');

        return redirect()->route('admin.sholat.input', [
            'tanggal' => $data['tanggal'],
            'sholat_setting_id' => $setting->id,
            'kelas_id' => $kelas->id,
        ])->with('success', "Kehadiran {$setting->nama} untuk kelas {$kelas->nama_kelas} pada tanggal {$formattedTanggal} berhasil disimpan.");
    }

    /**
     * Rekapitulasi Kehadiran Sholat per Kelas / Periode
     */
    public function rekap(Request $request)
    {
        $this->authorize('sholat-view');

        $tahunAktif = TahunPelajaran::query()->active()->first();
        $bulan = $request->input('bulan', Carbon::now()->format('Y-m'));
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);

        $activeSettings = SholatSetting::query()->ordered()->get();
        $selectedSettingId = $request->input('sholat_setting_id', $activeSettings->first()?->id);
        $selectedSetting = $activeSettings->firstWhere('id', $selectedSettingId) ?? $activeSettings->first();

        $kelasList = Kelas::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $user = $request->user();
        if ($user->hasRole('Wali Kelas') && ! $this->isFullManager($user)) {
            $myClasses = $user->activeWaliKelasClasses();
            if ($myClasses->isNotEmpty()) {
                $kelasList = $myClasses;
            }
        }

        $selectedKelasId = (string) $request->input('kelas_id', $kelasList->first()?->id);
        $selectedKelas = $kelasList->firstWhere('id', $selectedKelasId);

        $students = collect();
        $rekapData = [];
        $totalSessions = 0;

        if ($selectedKelas && $selectedSetting) {
            $startDate = $carbonBulan->copy()->startOfMonth()->toDateString();
            $endDate = $carbonBulan->copy()->endOfMonth()->toDateString();

            // Hitung total sesi yang sudah tercatat
            $totalSessions = SholatSession::query()
                ->where('kelas_id', $selectedKelas->id)
                ->where('sholat_setting_id', $selectedSetting->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->count();

            // Ambil daftar siswa
            $querySiswa = $selectedKelas->siswas()->where('siswa_kelas.status', 'aktif');
            if ($selectedSetting->target_gender === 'L') {
                $querySiswa->where('siswa.jenis_kelamin', 'L');
            } elseif ($selectedSetting->target_gender === 'P') {
                $querySiswa->where('siswa.jenis_kelamin', 'P');
            }

            $students = $querySiswa->select('siswa.id', 'siswa.nama_lengkap', 'siswa.nisn', 'siswa.jenis_kelamin')
                ->orderBy('siswa.nama_lengkap')
                ->get();

            // Ambil seluruh rekaman kehadiran sholat di bulan ini
            $records = DB::table('sholat_records as sr')
                ->join('sholat_sessions as ss', 'sr.sholat_session_id', '=', 'ss.id')
                ->where('ss.kelas_id', $selectedKelas->id)
                ->where('ss.sholat_setting_id', $selectedSetting->id)
                ->whereBetween('ss.tanggal', [$startDate, $endDate])
                ->whereIn('sr.siswa_id', $students->pluck('id'))
                ->select('sr.siswa_id', 'sr.status', DB::raw('count(*) as count'))
                ->groupBy('sr.siswa_id', 'sr.status')
                ->get();

            foreach ($students as $siswa) {
                $siswaRecords = $records->where('siswa_id', $siswa->id);
                $counts = [
                    'berjamaah' => (int) ($siswaRecords->firstWhere('status', 'berjamaah')?->count ?: 0),
                    'masbuq' => (int) ($siswaRecords->firstWhere('status', 'masbuq')?->count ?: 0),
                    'munfarid' => (int) ($siswaRecords->firstWhere('status', 'munfarid')?->count ?: 0),
                    'udzur' => (int) ($siswaRecords->firstWhere('status', 'udzur')?->count ?: 0),
                    'sakit' => (int) ($siswaRecords->firstWhere('status', 'sakit')?->count ?: 0),
                    'izin' => (int) ($siswaRecords->firstWhere('status', 'izin')?->count ?: 0),
                    'alpa' => (int) ($siswaRecords->firstWhere('status', 'alpa')?->count ?: 0),
                ];

                // Hitung persentase kehadiran (Berjamaah + Masbuq + Munfarid) terhadap sesi wajib (Total sesi - Udzur - Sakit - Izin)
                $hadirSholat = $counts['berjamaah'] + $counts['masbuq'] + $counts['munfarid'];
                $sesiWajib = max(0, $totalSessions - ($counts['udzur'] + $counts['sakit'] + $counts['izin']));
                $persentase = $sesiWajib > 0 ? round(($hadirSholat / $sesiWajib) * 100, 1) : ($totalSessions > 0 ? 100 : 0);

                $rekapData[$siswa->id] = array_merge($counts, [
                    'hadir_total' => $hadirSholat,
                    'sesi_wajib' => $sesiWajib,
                    'persentase' => $persentase,
                ]);
            }
        }

        $statusLabels = SholatRecord::statusLabels();

        return view('admin.sholat.rekap', compact(
            'bulan',
            'carbonBulan',
            'activeSettings',
            'selectedSetting',
            'kelasList',
            'selectedKelas',
            'students',
            'rekapData',
            'totalSessions',
            'statusLabels',
            'tahunAktif'
        ));
    }

    /**
     * Export Rekapitulasi Sholat ke Excel
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('sholat-export');

        $bulan = $request->input('bulan', Carbon::now()->format('Y-m'));
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $settingId = $request->input('sholat_setting_id');
        $kelasId = $request->input('kelas_id');

        $setting = SholatSetting::findOrFail($settingId);
        $kelas = Kelas::findOrFail($kelasId);

        $startDate = $carbonBulan->copy()->startOfMonth()->toDateString();
        $endDate = $carbonBulan->copy()->endOfMonth()->toDateString();

        $totalSessions = SholatSession::query()
            ->where('kelas_id', $kelas->id)
            ->where('sholat_setting_id', $setting->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->count();

        $querySiswa = $kelas->siswas()->where('siswa_kelas.status', 'aktif');
        if ($setting->target_gender === 'L') {
            $querySiswa->where('siswa.jenis_kelamin', 'L');
        } elseif ($setting->target_gender === 'P') {
            $querySiswa->where('siswa.jenis_kelamin', 'P');
        }

        $students = $querySiswa->select('siswa.id', 'siswa.nama_lengkap', 'siswa.nisn', 'siswa.jenis_kelamin')
            ->orderBy('siswa.nama_lengkap')
            ->get();

        $records = DB::table('sholat_records as sr')
            ->join('sholat_sessions as ss', 'sr.sholat_session_id', '=', 'ss.id')
            ->where('ss.kelas_id', $kelas->id)
            ->where('ss.sholat_setting_id', $setting->id)
            ->whereBetween('ss.tanggal', [$startDate, $endDate])
            ->whereIn('sr.siswa_id', $students->pluck('id'))
            ->select('sr.siswa_id', 'sr.status', DB::raw('count(*) as count'))
            ->groupBy('sr.siswa_id', 'sr.status')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Sholat');

        // Header Dokumen
        $sheet->setCellValue('A1', 'REKAPITULASI KEHADIRAN IBADAH SHOLAT SISWA');
        $sheet->setCellValue('A2', "MADRASAH ALIYAH NEGERI 1 METRO");
        $sheet->setCellValue('A3', "Kelas: {$kelas->nama_kelas} | Ibadah: {$setting->nama} | Periode: " . $carbonBulan->translatedFormat('F Y'));
        $sheet->setCellValue('A4', "Total Sesi Terlaksana: {$totalSessions} Sesi");

        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->mergeCells('A4:L4');

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Header Tabel
        $headers = ['No', 'NISN', 'Nama Siswa', 'JK', 'Berjamaah', 'Masbuq', 'Munfarid', 'Udzur', 'Sakit', 'Izin', 'Alpa', '% Kehadiran'];
        $sheet->fromArray($headers, null, 'A6');

        $sheet->getStyle('A6:L6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2C3E50');
        $sheet->getStyle('A6:L6')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A6:L6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 7;
        $no = 1;

        foreach ($students as $siswa) {
            $siswaRecords = $records->where('siswa_id', $siswa->id);
            $berjamaah = (int) ($siswaRecords->firstWhere('status', 'berjamaah')?->count ?: 0);
            $masbuq = (int) ($siswaRecords->firstWhere('status', 'masbuq')?->count ?: 0);
            $munfarid = (int) ($siswaRecords->firstWhere('status', 'munfarid')?->count ?: 0);
            $udzur = (int) ($siswaRecords->firstWhere('status', 'udzur')?->count ?: 0);
            $sakit = (int) ($siswaRecords->firstWhere('status', 'sakit')?->count ?: 0);
            $izin = (int) ($siswaRecords->firstWhere('status', 'izin')?->count ?: 0);
            $alpa = (int) ($siswaRecords->firstWhere('status', 'alpa')?->count ?: 0);

            $hadirSholat = $berjamaah + $masbuq + $munfarid;
            $sesiWajib = max(0, $totalSessions - ($udzur + $sakit + $izin));
            $persentase = $sesiWajib > 0 ? round(($hadirSholat / $sesiWajib) * 100, 1) : ($totalSessions > 0 ? 100 : 0);

            $sheet->fromArray([
                $no++,
                $siswa->nisn ?: '-',
                $siswa->nama_lengkap,
                $siswa->jenis_kelamin,
                $berjamaah,
                $masbuq,
                $munfarid,
                $udzur,
                $sakit,
                $izin,
                $alpa,
                $persentase . '%',
            ], null, 'A' . $row);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($alpa >= 3) {
                // Highlight merah muda untuk siswa yang sering alpa
                $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2F2');
                $sheet->getStyle("K{$row}")->getFont()->setBold(true)->getColor()->setRGB('C0392B');
            }

            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A6:L{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Rekap_Sholat_{$kelas->nama_kelas}_{$setting->kode}_{$bulan}.xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Dashboard Monitoring Real-time Kehadiran Sholat Madrasah
     */
    public function monitoring(Request $request)
    {
        $this->authorize('sholat-monitoring');

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($tanggal);
        $tahunAktif = TahunPelajaran::query()->active()->first();

        $activeSettings = SholatSetting::query()->ordered()->get();
        $selectedSettingId = $request->input('sholat_setting_id', $activeSettings->where('is_active', true)->first()?->id);
        $selectedSetting = $activeSettings->firstWhere('id', $selectedSettingId) ?? $activeSettings->first();

        $kelasList = Kelas::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $classReports = collect();
        $kpi = [
            'total_rombel' => $kelasList->count(),
            'rombel_tercatat' => 0,
            'rombel_belum' => 0,
            'total_jamaah' => 0,
            'total_masbuq_munfarid' => 0,
            'total_udzur' => 0,
            'total_alpa' => 0,
            'persentase_partisipasi' => 0,
        ];

        if ($selectedSetting) {
            // Ambil semua sesi pada tanggal & sholat ini
            $sessions = SholatSession::query()
                ->with(['records', 'recorder'])
                ->where('tanggal', $tanggal)
                ->where('sholat_setting_id', $selectedSetting->id)
                ->get()
                ->keyBy('kelas_id');

            $kpi['rombel_tercatat'] = $sessions->count();
            $kpi['rombel_belum'] = max(0, $kpi['total_rombel'] - $kpi['rombel_tercatat']);

            foreach ($kelasList as $kelas) {
                $session = $sessions->get($kelas->id);
                $counts = [
                    'berjamaah' => 0,
                    'masbuq' => 0,
                    'munfarid' => 0,
                    'udzur' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                    'total' => 0,
                ];

                if ($session) {
                    foreach ($session->records as $record) {
                        if (isset($counts[$record->status])) {
                            $counts[$record->status]++;
                        }
                        $counts['total']++;
                    }

                    $kpi['total_jamaah'] += $counts['berjamaah'];
                    $kpi['total_masbuq_munfarid'] += ($counts['masbuq'] + $counts['munfarid']);
                    $kpi['total_udzur'] += $counts['udzur'];
                    $kpi['total_alpa'] += $counts['alpa'];
                }

                $classReports->push([
                    'kelas' => $kelas,
                    'is_recorded' => ! is_null($session),
                    'session' => $session,
                    'counts' => $counts,
                ]);
            }

            $totalSiswaTercatat = $kpi['total_jamaah'] + $kpi['total_masbuq_munfarid'] + $kpi['total_alpa'];
            if ($totalSiswaTercatat > 0) {
                $kpi['persentase_partisipasi'] = round((($kpi['total_jamaah'] + $kpi['total_masbuq_munfarid']) / $totalSiswaTercatat) * 100, 1);
            }
        }

        // Siswa yang sering alpa (>= 3 kali dalam 30 hari terakhir) untuk perhatian Wali Kelas & BK
        $thirtyDaysAgo = Carbon::today()->subDays(30)->toDateString();
        $alertStudents = DB::table('sholat_records as sr')
            ->join('sholat_sessions as ss', 'sr.sholat_session_id', '=', 'ss.id')
            ->join('siswa as s', 'sr.siswa_id', '=', 's.id')
            ->join('kelas as k', 'ss.kelas_id', '=', 'k.id')
            ->where('sr.status', 'alpa')
            ->where('ss.tanggal', '>=', $thirtyDaysAgo)
            ->select('s.id', 's.nama_lengkap', 's.nisn', 's.jenis_kelamin', 'k.nama_kelas', DB::raw('count(*) as alpa_count'))
            ->groupBy('s.id', 's.nama_lengkap', 's.nisn', 's.jenis_kelamin', 'k.nama_kelas')
            ->having('alpa_count', '>=', 3)
            ->orderByDesc('alpa_count')
            ->limit(20)
            ->get();

        return view('admin.sholat.monitoring', compact(
            'tanggal',
            'carbonDate',
            'activeSettings',
            'selectedSetting',
            'kpi',
            'classReports',
            'alertStudents',
            'tahunAktif'
        ));
    }

    /**
     * Cek kelas yang dapat diinput oleh pengguna
     */
    protected function getAccessibleClasses($user, ?TahunPelajaran $tahunAktif): Collection
    {
        $query = Kelas::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas');

        // Super Admin, Admin, WAKA, Kepala Madrasah, atau GTK umum: akses seluruh kelas
        if ($this->isFullManager($user) || $user->hasRole('GTK')) {
            return $query->get();
        }

        // Wali Kelas: akses kelas binaan
        if ($user->hasRole('Wali Kelas')) {
            $waliClasses = $user->activeWaliKelasClasses();
            if ($waliClasses->isNotEmpty()) {
                return $waliClasses;
            }
        }

        // Siswa: akses kelas jika terdaftar sebagai Seksi Kerohanian aktif
        if ($user->hasRole('Siswa') && $user->siswa) {
            $petugasClassIds = SholatPetugasKelas::query()
                ->where('siswa_id', $user->siswa->id)
                ->where('is_active', true)
                ->pluck('kelas_id');

            if ($petugasClassIds->isNotEmpty()) {
                return $query->whereIn('id', $petugasClassIds)->get();
            }
        }

        return collect();
    }

    protected function isFullManager($user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Madrasah', 'WAKA'])
            || $user->can('sholat-manage-setting');
    }
}
