<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaAudit;
use App\Models\ActivityLog;
use App\Models\AttendanceAlert;
use App\Models\AttendanceAnalysisRun;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\AttendanceInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentAttendanceAnalyticsController extends Controller
{
    public function __construct(private readonly AttendanceInsightService $insights) {}

    public function index(Request $request)
    {
        $this->authorize('view-attendance-analytics');
        $user = $request->user();
        $years = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get();
        $activeYear = $years->firstWhere('is_active', true);
        $year = $years->firstWhere('id', (string) $request->get('tahun_pelajaran_id')) ?: $activeYear ?: $years->first();
        abort_unless($year, 404, 'Tahun pelajaran belum tersedia.');

        $accessibleClassIds = $this->accessibleClassIds($user, $year->id);
        $classes = Kelas::query()->where('tahun_pelajaran_id', $year->id)
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereIn('id', $accessibleClassIds))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tingkat = in_array((int) $request->get('tingkat'), [10, 11, 12], true) ? (int) $request->get('tingkat') : null;
        $classId = (string) $request->get('kelas_id', '');
        if ($classId !== '' && ! $classes->contains('id', $classId)) {
            $classId = '';
        }

        $end = Carbon::parse($request->get('end_date', now()->toDateString()))->min(now())->endOfDay();
        $start = Carbon::parse($request->get('start_date', $end->copy()->subDays(29)->toDateString()))->startOfDay();
        if ($start->gt($end)) {
            $start = $end->copy()->subDays(29)->startOfDay();
        }

        $sessionScope = DB::table('absensi_siswa_sessions as sessions')
            ->whereNull('sessions.deleted_at')
            ->where('sessions.tahun_pelajaran_id', $year->id)
            ->where('sessions.status', 'final')
            ->whereBetween('sessions.tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereIn('sessions.kelas_id', $accessibleClassIds))
            ->when($tingkat, fn ($query) => $query->where('sessions.tingkat', $tingkat))
            ->when($classId !== '', fn ($query) => $query->where('sessions.kelas_id', $classId));

        $sessionSummary = (clone $sessionScope)->selectRaw("COUNT(*) total, SUM(mode='harian') daily, SUM(mode='mapel') subject")->first();
        $recordScope = DB::table('absensi_siswa_records as records')
            ->joinSub($sessionScope->select('sessions.id', 'sessions.tanggal', 'sessions.kelas_id', 'sessions.mode', 'sessions.tingkat'), 'scoped_sessions', 'scoped_sessions.id', '=', 'records.session_id')
            ->whereNull('records.deleted_at');
        $statusCounts = (clone $recordScope)->select('records.status', DB::raw('COUNT(*) total'))
            ->groupBy('records.status')->pluck('total', 'status');
        $totalRecords = (int) $statusCounts->sum();
        $presentRecords = collect(['hadir', 'terlambat', 'keluar_awal'])->sum(fn ($status) => (int) ($statusCounts[$status] ?? 0));

        $eligibleStudentUserIds = Siswa::query()
            ->whereNotNull('user_id')
            ->whereHas('siswaKelasRecords', fn ($membership) => $membership
                ->where('tahun_pelajaran_id', $year->id)
                ->whereNull('deleted_at')
                ->when($accessibleClassIds !== null, fn ($query) => $query->whereIn('kelas_id', $accessibleClassIds))
                ->when($classId !== '', fn ($query) => $query->where('kelas_id', $classId))
                ->when($tingkat, fn ($query) => $query->where('tingkat', $tingkat)))
            ->pluck('user_id');
        $dailyStatusCounts = DB::table('absensis')
            ->whereNull('deleted_at')->where('user_type', 'siswa')
            ->where('tahun_pelajaran_id', $year->id)
            ->whereIn('user_id', $eligibleStudentUserIds)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->select('status', DB::raw('COUNT(*) total'))->groupBy('status')->pluck('total', 'status');
        $dailyTotal = (int) $dailyStatusCounts->sum();
        $dailyPresent = collect(['hadir', 'terlambat'])->sum(fn ($status) => (int) ($dailyStatusCounts[$status] ?? 0));

        $studentRows = (clone $recordScope)
            ->join('siswa', 'siswa.id', '=', 'records.siswa_id')
            ->select([
                'siswa.id', 'siswa.nama_lengkap', 'siswa.nisn',
                DB::raw('COUNT(*) total_records'),
                DB::raw("SUM(records.status IN ('hadir','terlambat','keluar_awal')) present_records"),
                DB::raw("SUM(records.status='alpa') alpa"),
                DB::raw("SUM(records.status='terlambat') terlambat"),
                DB::raw("SUM(records.status='sakit') sakit"),
                DB::raw("SUM(records.status='izin') izin"),
            ])->groupBy('siswa.id', 'siswa.nama_lengkap', 'siswa.nisn')
            ->orderByDesc('alpa')->orderByDesc('terlambat')->limit(100)->get()
            ->map(function ($row) {
                $row->attendance_rate = $row->total_records ? round(($row->present_records / $row->total_records) * 100, 1) : 0;

                return $row;
            });

        $alertQuery = AttendanceAlert::query()->with(['siswa.kelasSaatIni', 'assignee'])
            ->where('tahun_pelajaran_id', $year->id)->where('is_active', true)
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereHas('siswa.siswaKelasRecords', fn ($membership) => $membership
                ->where('tahun_pelajaran_id', $year->id)->whereIn('kelas_id', $accessibleClassIds)))
            ->when($tingkat, fn ($query) => $query->whereHas('siswa.siswaKelasRecords.kelas', fn ($class) => $class
                ->where('kelas.tahun_pelajaran_id', $year->id)->where('kelas.tingkat', $tingkat)))
            ->when($classId !== '', fn ($query) => $query->whereHas('siswa.siswaKelasRecords', fn ($membership) => $membership->where('kelas_id', $classId)));
        $alerts = $alertQuery->orderByRaw("FIELD(severity, 'high', 'medium', 'low')")->orderByDesc('score')->limit(100)->get();
        $lastAnalysis = AttendanceAnalysisRun::with('actor')->where('tahun_pelajaran_id', $year->id)->latest()->first();

        $kpi = [
            'sessions' => (int) ($sessionSummary->total ?? 0),
            'daily_sessions' => (int) ($sessionSummary->daily ?? 0),
            'subject_sessions' => (int) ($sessionSummary->subject ?? 0),
            'records' => $totalRecords,
            'attendance_rate' => $totalRecords ? round(($presentRecords / $totalRecords) * 100, 1) : 0,
            'daily_records' => $dailyTotal,
            'daily_attendance_rate' => $dailyTotal ? round(($dailyPresent / $dailyTotal) * 100, 1) : 0,
            'active_alerts' => $alerts->count(),
            'high_alerts' => $alerts->where('severity', 'high')->count(),
        ];

        return view('admin.absensi.analytics', compact(
            'years', 'year', 'activeYear', 'classes', 'tingkat', 'classId', 'start', 'end',
            'statusCounts', 'dailyStatusCounts', 'studentRows', 'alerts', 'lastAnalysis', 'kpi'
        ));
    }

    public function student(Request $request, Siswa $siswa)
    {
        $this->authorize('view-attendance-analytics');
        $accessible = $this->accessibleStudent($request->user(), $siswa);
        abort_unless($accessible, 403);

        $subjectRecords = DB::table('absensi_siswa_records as records')
            ->join('absensi_siswa_sessions as sessions', 'sessions.id', '=', 'records.session_id')
            ->leftJoin('tahun_pelajaran', 'tahun_pelajaran.id', '=', 'sessions.tahun_pelajaran_id')
            ->whereNull('records.deleted_at')->whereNull('sessions.deleted_at')
            ->where('sessions.status', 'final')->where('records.siswa_id', $siswa->id)
            ->select([
                'records.*', 'sessions.tanggal', 'sessions.mode', 'sessions.tingkat',
                'sessions.kelas_snapshot', 'sessions.mapel_snapshot', 'sessions.guru_snapshot',
                'sessions.semester', 'tahun_pelajaran.nama as year_name',
            ])->orderByDesc('sessions.tanggal')->get();

        $memberships = $siswa->siswaKelasRecords()->with('kelas')->get();
        $dailyRecords = DB::table('absensis')
            ->leftJoin('tahun_pelajaran', 'tahun_pelajaran.id', '=', 'absensis.tahun_pelajaran_id')
            ->whereNull('absensis.deleted_at')->where('absensis.user_type', 'siswa')
            ->where('absensis.user_id', $siswa->user_id)
            ->select([
                'absensis.id', 'absensis.tahun_pelajaran_id', 'absensis.status', 'absensis.tanggal', 'absensis.waktu_masuk',
                'absensis.waktu_pulang', 'absensis.metode_masuk', 'absensis.metode_pulang',
                'tahun_pelajaran.nama as year_name',
            ])->orderByDesc('absensis.tanggal')->get();

        $records = $subjectRecords->map(function ($row) {
            $row->source_type = 'subject';

            return $row;
        })->concat($dailyRecords->map(function ($row) use ($memberships) {
            $date = Carbon::parse($row->tanggal)->toDateString();
            $membership = $memberships->first(function ($item) use ($row, $date) {
                return $item->tahun_pelajaran_id === $row->tahun_pelajaran_id
                    && (! $item->tanggal_masuk || Carbon::parse($item->tanggal_masuk)->toDateString() <= $date)
                    && (! $item->tanggal_keluar || Carbon::parse($item->tanggal_keluar)->toDateString() >= $date);
            });
            $row->source_type = 'daily_face';
            $row->mode = 'harian';
            $row->tingkat = $membership?->tingkat ?? $membership?->kelas?->tingkat;
            $row->kelas_snapshot = $membership?->kelas?->nama_kelas ?: 'Gerbang sekolah';
            $row->mapel_snapshot = 'Kehadiran harian';
            $row->guru_snapshot = 'Kiosk wajah';
            $row->semester = null;
            $row->late_minutes = null;

            return $row;
        }))->sortByDesc('tanggal')->values();

        $history = $records->groupBy(fn ($row) => ($row->year_name ?: 'Tanpa tahun').'|'.($row->tingkat ?: '-'))
            ->map(function ($rows, $key) {
                [$yearName, $level] = explode('|', $key, 2);
                $total = $rows->count();
                $present = $rows->whereIn('status', ['hadir', 'terlambat', 'keluar_awal'])->count();

                return [
                    'year_name' => $yearName,
                    'level' => $level,
                    'total' => $total,
                    'present' => $present,
                    'rate' => $total ? round(($present / $total) * 100, 1) : 0,
                    'alpa' => $rows->where('status', 'alpa')->count(),
                    'late' => $rows->where('status', 'terlambat')->count(),
                ];
            })->values();
        $alerts = AttendanceAlert::with(['tahunPelajaran', 'reviewer'])->where('siswa_id', $siswa->id)
            ->orderByDesc('last_detected_at')->get();
        $audits = $request->user()->can('view-attendance-audit')
            ? AbsensiSiswaAudit::with(['actor', 'session'])->where('siswa_id', $siswa->id)->latest()->limit(100)->get()
            : collect();

        return view('admin.absensi.student-analytics', compact('siswa', 'records', 'history', 'alerts', 'audits'));
    }

    public function generate(Request $request)
    {
        $this->authorize('manage-attendance-alerts');
        $year = TahunPelajaran::query()->active()->firstOrFail();
        $classIds = $this->accessibleClassIds($request->user(), $year->id);
        $result = $this->insights->generate($year, $classIds);
        AttendanceAnalysisRun::create([
            'tahun_pelajaran_id' => $year->id,
            'actor_user_id' => $request->user()->id,
            'source' => 'manual',
            'status' => 'completed',
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        $this->logAction($request, 'attendance_alerts_generated', null, $result);

        return response()->json(['success' => true, 'message' => 'Analisis smart selesai diperbarui.', 'data' => $result]);
    }

    public function updateAlert(Request $request, AttendanceAlert $alert)
    {
        $this->authorize('manage-attendance-alerts');
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,monitoring,resolved,dismissed'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $before = $alert->only(['status', 'review_notes', 'reviewed_by', 'reviewed_at']);
        $alert->update([
            ...$data,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'is_active' => ! in_array($data['status'], ['resolved', 'dismissed'], true),
        ]);
        $this->logAction($request, 'attendance_alert_reviewed', $alert, ['before' => $before, 'after' => $alert->only(array_keys($before))]);

        return response()->json(['success' => true, 'message' => 'Tindak lanjut berhasil disimpan.']);
    }

    private function accessibleClassIds($user, string $yearId): ?Collection
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'BK'])
            || $user->can('view-attendance-counseling')) {
            return null;
        }
        $ids = collect();
        if ($user->hasRole('Wali Kelas')) {
            $ids = $ids->merge(Kelas::where('tahun_pelajaran_id', $yearId)->where('wali_kelas_id', $user->id)->pluck('id'));
        }
        if ($user->gtk) {
            $ids = $ids->merge(JadwalPelajaran::where('tahun_pelajaran_id', $yearId)->where('gtk_id', $user->gtk->id)->pluck('kelas_id'));
        }

        return $ids->unique()->values();
    }

    private function accessibleStudent($user, Siswa $siswa): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'BK'])
            || $user->can('view-attendance-counseling')) {
            return true;
        }
        $yearIds = TahunPelajaran::pluck('id');
        foreach ($yearIds as $yearId) {
            $classIds = $this->accessibleClassIds($user, $yearId);
            if ($classIds && $siswa->siswaKelasRecords()->where('tahun_pelajaran_id', $yearId)->whereIn('kelas_id', $classIds)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function logAction(Request $request, string $type, $model, array $properties): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'activity_type' => $type,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $type === 'attendance_alerts_generated' ? 'Menjalankan smart detection absensi siswa' : 'Memperbarui tindak lanjut smart suggestion',
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);
    }
}
