<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaAudit;
use App\Models\ActivityLog;
use App\Models\AttendanceAlert;
use App\Models\AttendanceAnalysisRun;
use App\Models\CatatanWaliKelas;
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
        $isWaliScope = $this->isWaliScopedUser($user);
        $years = TahunPelajaran::query()
            ->when($isWaliScope, fn ($query) => $query->active()
                ->whereHas('kelas', fn ($classes) => $classes
                    ->where('wali_kelas_id', $user->id)->where('is_active', true)))
            ->orderByDesc('tahun_mulai')->get();
        $activeYear = $years->firstWhere('is_active', true);
        $year = $years->firstWhere('id', (string) $request->get('tahun_pelajaran_id')) ?: $activeYear ?: $years->first();
        abort_unless($year, 404, 'Tahun pelajaran belum tersedia.');

        $accessibleClassIds = $this->accessibleClassIds($user, $year->id);
        $classes = Kelas::query()->where('tahun_pelajaran_id', $year->id)
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereIn('id', $accessibleClassIds))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();
        // Wali kelas selalu melihat keseluruhan rombel perwaliannya.  Jangan
        // biarkan query string lama menyisakan filter tingkat/rombel tersembunyi.
        $tingkat = $isWaliScope ? null : (in_array((int) $request->get('tingkat'), [10, 11, 12], true) ? (int) $request->get('tingkat') : null);
        $classId = $isWaliScope ? '' : (string) $request->get('kelas_id', '');
        if ($classId !== '' && ! $classes->contains('id', $classId)) {
            abort_if($accessibleClassIds !== null, 404, 'Rombel tidak ditemukan dalam cakupan wali kelas Anda.');
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
                'siswa.id', 'siswa.nama_lengkap', 'siswa.nisn', DB::raw('MAX(scoped_sessions.kelas_id) as kelas_id'),
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

        $notesScope = CatatanWaliKelas::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereIn('kelas_id', $accessibleClassIds))
            ->when($classId !== '', fn ($query) => $query->where('kelas_id', $classId))
            ->when($tingkat, fn ($query) => $query->whereHas('kelas', fn ($class) => $class->where('tingkat', $tingkat)))
            ->when($isWaliScope, fn ($query) => $query->where('created_by', $user->id));
        $notesByStudent = (clone $notesScope)
            ->selectRaw('siswa_id, COUNT(*) as total, SUM(is_penting = 1) as important, MAX(tanggal) as latest_date')
            ->groupBy('siswa_id')->get()->keyBy('siswa_id');
        $studentRows->each(function ($row) use ($notesByStudent) {
            $note = $notesByStudent->get($row->id);
            $row->note_count = (int) ($note->total ?? 0);
            $row->important_note_count = (int) ($note->important ?? 0);
            $row->latest_note_date = $note->latest_date ?? null;
        });

        $alertQuery = AttendanceAlert::query()->with(['siswa.kelasSaatIni', 'assignee'])
            ->where('tahun_pelajaran_id', $year->id)->where('is_active', true)
            ->when($accessibleClassIds !== null, fn ($query) => $query->whereHas('siswa.siswaKelasRecords', fn ($membership) => $membership
                ->where('tahun_pelajaran_id', $year->id)->whereIn('kelas_id', $accessibleClassIds)))
            ->when($tingkat, fn ($query) => $query->whereHas('siswa.siswaKelasRecords.kelas', fn ($class) => $class
                ->where('kelas.tahun_pelajaran_id', $year->id)->where('kelas.tingkat', $tingkat)))
            ->when($classId !== '', fn ($query) => $query->whereHas('siswa.siswaKelasRecords', fn ($membership) => $membership->where('kelas_id', $classId)));
        $alerts = $alertQuery->orderByRaw("FIELD(severity, 'high', 'medium', 'low')")->orderByDesc('score')->limit(100)->get();
        $lastAnalysis = AttendanceAnalysisRun::with('actor')->where('tahun_pelajaran_id', $year->id)
            ->when($isWaliScope, fn ($query) => $query->where('actor_user_id', $user->id))
            ->latest()->first();

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
            'student_notes' => (clone $notesScope)->count(),
            'important_notes' => (clone $notesScope)->where('is_penting', true)->count(),
            'students_with_notes' => (clone $notesScope)->distinct()->count('siswa_id'),
        ];

        return view('admin.absensi.analytics', compact(
            'years', 'year', 'activeYear', 'classes', 'tingkat', 'classId', 'start', 'end',
            'statusCounts', 'dailyStatusCounts', 'studentRows', 'alerts', 'lastAnalysis', 'kpi', 'isWaliScope'
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
        $isWaliScope = $this->isWaliScopedUser($request->user());
        $noteClassIds = $this->accessibleClassIdsAcrossYears($request->user());
        $notes = CatatanWaliKelas::query()->with(['kelas', 'penulis'])
            ->where('siswa_id', $siswa->id)
            ->when($noteClassIds !== null, fn ($query) => $query
                ->whereIn('kelas_id', $noteClassIds)->where('created_by', $request->user()->id))
            ->latest('tanggal')->limit(50)->get();

        return view('admin.absensi.student-analytics', compact('siswa', 'records', 'history', 'alerts', 'audits', 'notes', 'isWaliScope'));
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
        abort_unless($alert->siswa && $this->accessibleStudent($request->user(), $alert->siswa), 403);
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
        $ids = Kelas::query()->where('tahun_pelajaran_id', $yearId)
            ->where('wali_kelas_id', $user->id)->where('is_active', true)->pluck('id');
        abort_if($ids->isEmpty(), 403, 'Anda bukan wali kelas aktif pada tahun pelajaran ini.');

        return $ids->unique()->values();
    }

    private function accessibleClassIdsAcrossYears($user): ?Collection
    {
        if (! $this->isWaliScopedUser($user)) {
            return null;
        }

        return Kelas::query()->where('wali_kelas_id', $user->id)->pluck('id');
    }

    private function isWaliScopedUser($user): bool
    {
        $isManager = $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'BK'])
            || $user->can('view-attendance-counseling');

        return ! $isManager && $user->hasAnyRole(['GTK', 'Wali Kelas']);
    }

    private function accessibleStudent($user, Siswa $siswa): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'BK'])
            || $user->can('view-attendance-counseling')) {
            return true;
        }
        $classIds = Kelas::query()
            ->where('wali_kelas_id', $user->id)
            ->where('is_active', true)
            ->whereHas('tahunPelajaran', fn ($year) => $year->where('is_active', true))
            ->pluck('id');

        return $classIds->isNotEmpty() && $siswa->siswaKelasRecords()
            ->where('status', 'aktif')
            ->whereIn('kelas_id', $classIds)
            ->exists();
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
