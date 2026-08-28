<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Services\StudentAttendanceAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbsensiSiswaController extends Controller
{
    public function __construct(private readonly StudentAttendanceAuditService $audit) {}

    public function index(Request $request)
    {
        $this->authorize('view-student-attendance');

        $user = $request->user();
        $isGlobalScope = $this->isUnrestrictedStaff($user);
        $tanggal = $this->normalizeDate($request->get('tanggal'));
        $tahunPelajaran = TahunPelajaran::query()->active()->first();
        $canManageHarian = $tahunPelajaran && $this->canManageHarian($user, $tahunPelajaran->id);
        $canManageMapel = $tahunPelajaran && $this->canManageMapel($user, $tahunPelajaran->id);
        $canBulkGenerate = $this->canBulkGenerate($user);

        $requestedMode = $request->get('mode');
        $mode = match (true) {
            $requestedMode === 'harian' && $canManageHarian => 'harian',
            $requestedMode === 'mapel' && $canManageMapel => 'mapel',
            $canManageHarian => 'harian',
            $canManageMapel => 'mapel',
            default => null,
        };

        $kelasOptions = $mode && $tahunPelajaran
            ? $this->getAccessibleClasses($user, $tanggal, $mode, $tahunPelajaran)
            : collect();
        $selectedKelasId = (string) ($request->get('kelas_id') ?: optional($kelasOptions->first())->id);
        $selectedKelas = $selectedKelasId !== '' ? $kelasOptions->firstWhere('id', $selectedKelasId) : null;

        $jadwalOptions = collect();
        $selectedJadwalId = null;
        if ($mode === 'mapel' && $selectedKelas && $tahunPelajaran) {
            $jadwalOptions = $this->getAccessibleSchedules($user, $tanggal, $selectedKelas->id, $tahunPelajaran);
            $selectedJadwalId = (string) ($request->get('jadwal_pelajaran_id') ?: optional($jadwalOptions->first())->id);
            if (! $jadwalOptions->contains('id', $selectedJadwalId)) {
                $selectedJadwalId = null;
            }
        }

        $session = null;
        $students = collect();
        $existingRecords = collect();
        $summary = collect(['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dispen', 'keluar_awal'])
            ->mapWithKeys(fn ($status) => [$status => 0])->all();
        $classAttendanceSummary = collect();

        if ($mode === 'harian' && $tahunPelajaran && $kelasOptions->isNotEmpty()) {
            $classAttendanceSummary = DB::table('kelas as k')
                ->leftJoin('siswa_kelas as sk', function ($join) use ($tahunPelajaran, $tanggal) {
                    $join->on('sk.kelas_id', '=', 'k.id')
                        ->where('sk.tahun_pelajaran_id', '=', $tahunPelajaran->id)
                        ->whereNull('sk.deleted_at')
                        ->whereDate('sk.tanggal_masuk', '<=', $tanggal)
                        ->where(function ($nested) use ($tanggal) {
                            $nested->whereNull('sk.tanggal_keluar')
                                ->orWhereDate('sk.tanggal_keluar', '>=', $tanggal);
                        });
                })
                ->leftJoin('siswa as s', function ($join) {
                    $join->on('s.id', '=', 'sk.siswa_id')->whereNull('s.deleted_at');
                })
                ->leftJoin('absensi_siswa_sessions as attendance_sessions', function ($join) use ($tahunPelajaran, $tanggal) {
                    $join->on('attendance_sessions.kelas_id', '=', 'k.id')
                        ->where('attendance_sessions.tahun_pelajaran_id', '=', $tahunPelajaran->id)
                        ->where('attendance_sessions.tanggal', '=', $tanggal)
                        ->where('attendance_sessions.mode', '=', 'harian')
                        ->whereNull('attendance_sessions.deleted_at');
                })
                ->leftJoin('absensi_siswa_records as attendance_records', function ($join) {
                    $join->on('attendance_records.session_id', '=', 'attendance_sessions.id')
                        ->on('attendance_records.siswa_id', '=', 's.id')
                        ->whereNull('attendance_records.deleted_at');
                })
                ->whereIn('k.id', $kelasOptions->pluck('id'))
                ->select('k.id')
                ->selectRaw("COUNT(DISTINCT CASE WHEN attendance_records.status IN ('hadir', 'terlambat', 'keluar_awal') THEN s.id END) as present")
                ->selectRaw("COUNT(DISTINCT CASE WHEN attendance_records.status IN ('izin', 'sakit', 'alpa', 'dispen') THEN s.id END) as absent")
                ->groupBy('k.id')
                ->get()
                ->keyBy('id');
        }

        if ($selectedKelas && ($mode === 'harian' || $selectedJadwalId)) {
            $session = $this->findExistingSession($tanggal, $selectedKelas->id, $mode, $selectedJadwalId);
            $students = $this->studentsForDate($selectedKelas, $tanggal);

            if ($session) {
                $existingRecords = $session->records()->get()->keyBy('siswa_id');
                foreach (array_keys($summary) as $status) {
                    $summary[$status] = $existingRecords->where('status', $status)->count();
                }
            }
        }

        return view('admin.absensi.siswa', compact(
            'tanggal', 'tahunPelajaran', 'mode', 'canManageHarian', 'canManageMapel', 'isGlobalScope',
            'kelasOptions', 'selectedKelas', 'jadwalOptions', 'selectedJadwalId', 'canBulkGenerate',
            'session', 'students', 'existingRecords', 'summary', 'classAttendanceSummary'
        ));
    }

    /** Search the daily roster across every class that the current account may manage. */
    public function searchStudents(Request $request)
    {
        $this->authorize('view-student-attendance');

        $user = $request->user();
        $tahunPelajaran = TahunPelajaran::query()->active()->first();
        abort_unless($tahunPelajaran && $this->canManageHarian($user, $tahunPelajaran->id), 403);

        $tanggal = $this->normalizeDate($request->query('tanggal'));
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['students' => []]);
        }

        $classIds = $this->getAccessibleClasses($user, $tanggal, 'harian', $tahunPelajaran)->pluck('id');
        if ($classIds->isEmpty()) {
            return response()->json(['students' => []]);
        }

        $students = DB::table('siswa')
            ->join('siswa_kelas as sk', 'sk.siswa_id', '=', 'siswa.id')
            ->join('kelas as k', 'k.id', '=', 'sk.kelas_id')
            ->whereIn('k.id', $classIds)
            ->where('sk.tahun_pelajaran_id', $tahunPelajaran->id)
            ->where('k.tahun_pelajaran_id', $tahunPelajaran->id)
            ->where('k.is_active', true)
            ->whereNull('sk.deleted_at')
            ->whereNull('siswa.deleted_at')
            ->whereDate('sk.tanggal_masuk', '<=', $tanggal)
            ->where(function ($nested) use ($tanggal) {
                $nested->whereNull('sk.tanggal_keluar')
                    ->orWhereDate('sk.tanggal_keluar', '>=', $tanggal);
            })
            ->where(function ($nested) use ($query) {
                $nested->where('siswa.nama_lengkap', 'like', "%{$query}%")
                    ->orWhere('siswa.nisn', 'like', "%{$query}%");
            })
            ->orderBy('k.tingkat')
            ->orderBy('k.nama_kelas')
            ->orderBy('siswa.nama_lengkap')
            ->limit(12)
            ->get([
                'siswa.id', 'siswa.nama_lengkap', 'siswa.nisn',
                'k.id as kelas_id', 'k.tingkat', 'k.nama_kelas',
            ]);

        return response()->json([
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->nama_lengkap,
                'nisn' => $student->nisn,
                'class_label' => "Tingkat {$student->tingkat} · {$student->nama_kelas}",
                'url' => route('admin.absensi-siswa.index', [
                    'tanggal' => $tanggal,
                    'mode' => 'harian',
                    'kelas_id' => $student->kelas_id,
                ])."#siswa-{$student->id}",
            ])->values(),
        ]);
    }

    /** Generate untouched daily attendance sessions as drafts for an admin. */
    public function generateBulkDraft(Request $request)
    {
        $this->authorize('generate-bulk-student-attendance');
        abort_unless($this->canBulkGenerate($request->user()), 403);
        set_time_limit(300);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'scope' => ['required', 'in:all,selected'],
            'kelas_ids' => ['required_if:scope,selected', 'array'],
            'kelas_ids.*' => ['uuid'],
        ]);
        $year = TahunPelajaran::query()->active()->firstOrFail();
        $this->ensureDateBelongsToActiveYear($validated['tanggal'], $year);

        $classes = Kelas::query()->where('tahun_pelajaran_id', $year->id)->where('is_active', true)
            ->when($validated['scope'] === 'selected', fn ($query) => $query->whereIn('id', $validated['kelas_ids'] ?? []))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();
        abort_if($classes->isEmpty(), 422, 'Tidak ada kelas aktif yang dapat dibuatkan draft.');

        $created = 0;
        $skipped = 0;
        foreach ($classes as $kelas) {
            $key = $this->sessionKey($validated['tanggal'], $kelas->id, 'harian', null);
            if (AbsensiSiswaSession::query()->where('session_key', $key)->exists()) {
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($kelas, $year, $validated, $key, $request) {
                $session = AbsensiSiswaSession::create([
                    'tahun_pelajaran_id' => $year->id, 'session_key' => $key, 'kelas_id' => $kelas->id,
                    'guru_user_id' => $request->user()->id, 'tanggal' => $validated['tanggal'],
                    'semester' => $year->semester_aktif, 'tingkat' => $kelas->tingkat,
                    'kelas_snapshot' => $kelas->nama_kelas, 'guru_snapshot' => $request->user()->name,
                    'mode' => 'harian', 'attendance_method' => 'manual', 'status' => 'draft',
                    'version' => 1, 'notes' => 'Draft dibuat secara massal oleh admin.',
                    'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                ]);
                foreach ($this->studentsForDate($kelas, $validated['tanggal']) as $siswa) {
                    $record = AbsensiSiswaRecord::create([
                        'session_id' => $session->id, 'siswa_id' => $siswa->id, 'status' => 'hadir',
                        'attendance_method' => 'manual', 'source_reference' => 'admin_bulk_draft',
                        'checked_at' => now(), 'checked_by' => $request->user()->id,
                    ]);
                    $this->audit->record($session, $record, $request->user(), 'record_created', [], $this->recordAuditValues($record), 'Draft massal admin', $request);
                }
                $this->audit->session($session, $request->user(), 'session_created', [], $this->sessionAuditValues($session), 'Draft massal admin', $request);
            });
            $created++;
        }

        return redirect()->route('admin.absensi-siswa.index', ['tanggal' => $validated['tanggal'], 'mode' => 'harian'])
            ->with('toastr_success', "{$created} draft kelas dibuat. {$skipped} kelas dilewati karena sudah memiliki sesi.");
    }

    public function monitoring(Request $request)
    {
        $this->authorize('monitor-all-student-attendance');

        $tanggal = $this->normalizeDate($request->get('tanggal'));
        $tahunPelajaran = TahunPelajaran::query()->active()->first();
        $kelasOptions = collect();
        $students = collect();
        $stats = [
            'total' => 0,
            'recorded' => 0,
            'present' => 0,
            'exceptions' => 0,
            'unrecorded' => 0,
        ];

        if ($tahunPelajaran) {
            $kelasOptions = Kelas::query()
                ->where('tahun_pelajaran_id', $tahunPelajaran->id)
                ->where('is_active', true)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get(['id', 'tingkat', 'nama_kelas']);

            $baseQuery = DB::table('siswa')
                ->join('siswa_kelas as sk', 'sk.siswa_id', '=', 'siswa.id')
                ->join('kelas as k', 'k.id', '=', 'sk.kelas_id')
                ->leftJoin('absensi_siswa_sessions as attendance_sessions', function ($join) use ($tanggal, $tahunPelajaran) {
                    $join->on('attendance_sessions.kelas_id', '=', 'k.id')
                        ->where('attendance_sessions.tahun_pelajaran_id', '=', $tahunPelajaran->id)
                        ->where('attendance_sessions.tanggal', '=', $tanggal)
                        ->where('attendance_sessions.mode', '=', 'harian')
                        ->whereNull('attendance_sessions.deleted_at');
                })
                ->leftJoin('absensi_siswa_records as attendance_records', function ($join) {
                    $join->on('attendance_records.session_id', '=', 'attendance_sessions.id')
                        ->on('attendance_records.siswa_id', '=', 'siswa.id')
                        ->whereNull('attendance_records.deleted_at');
                })
                ->leftJoin('users as checked_by_users', 'checked_by_users.id', '=', 'attendance_records.checked_by')
                ->where('sk.tahun_pelajaran_id', $tahunPelajaran->id)
                ->where('k.tahun_pelajaran_id', $tahunPelajaran->id)
                ->where('k.is_active', true)
                ->whereNull('sk.deleted_at')
                ->whereNull('siswa.deleted_at')
                ->whereDate('sk.tanggal_masuk', '<=', $tanggal)
                ->where(function ($query) use ($tanggal) {
                    $query->whereNull('sk.tanggal_keluar')
                        ->orWhereDate('sk.tanggal_keluar', '>=', $tanggal);
                });

            $aggregate = (clone $baseQuery)
                ->selectRaw('COUNT(DISTINCT siswa.id) as total')
                ->selectRaw('COUNT(DISTINCT CASE WHEN attendance_records.id IS NOT NULL THEN siswa.id END) as recorded')
                ->selectRaw("COUNT(DISTINCT CASE WHEN attendance_records.status IN ('hadir', 'terlambat', 'keluar_awal') THEN siswa.id END) as present")
                ->selectRaw("COUNT(DISTINCT CASE WHEN attendance_records.status IN ('izin', 'sakit', 'alpa', 'dispen') THEN siswa.id END) as exceptions")
                ->first();

            $stats = [
                'total' => (int) ($aggregate->total ?? 0),
                'recorded' => (int) ($aggregate->recorded ?? 0),
                'present' => (int) ($aggregate->present ?? 0),
                'exceptions' => (int) ($aggregate->exceptions ?? 0),
                'unrecorded' => max(0, (int) ($aggregate->total ?? 0) - (int) ($aggregate->recorded ?? 0)),
            ];

            $query = (clone $baseQuery)
                ->select([
                    'siswa.id',
                    'siswa.nama_lengkap',
                    'siswa.nisn',
                    'siswa.jenis_kelamin',
                    'siswa.foto_profile',
                    'k.id as kelas_id',
                    'k.tingkat',
                    'k.nama_kelas',
                    'attendance_sessions.id as session_id',
                    'attendance_sessions.status as session_status',
                    'attendance_sessions.updated_at as session_updated_at',
                    'attendance_records.status as attendance_status',
                    'attendance_records.notes as attendance_notes',
                    'attendance_records.checked_at',
                    'checked_by_users.name as checked_by_name',
                ]);

            if ($request->filled('kelas_id')) {
                $query->where('k.id', $request->string('kelas_id')->toString());
            }
            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                if ($status === 'belum_direkam') {
                    $query->whereNull('attendance_records.id');
                } elseif (in_array($status, ['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dispen', 'keluar_awal'], true)) {
                    $query->where('attendance_records.status', $status);
                }
            }
            if ($request->filled('q')) {
                $search = trim($request->string('q')->toString());
                $query->where(function ($nested) use ($search) {
                    $nested->where('siswa.nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('siswa.nisn', 'like', "%{$search}%");
                });
            }

            $students = $query
                ->orderBy('k.tingkat')
                ->orderBy('k.nama_kelas')
                ->orderBy('siswa.nama_lengkap')
                ->paginate(25)
                ->withQueryString();
        }

        return view('admin.absensi.monitoring', compact(
            'tanggal',
            'tahunPelajaran',
            'kelasOptions',
            'students',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('view-student-attendance');

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'mode' => ['required', 'in:harian,mapel'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'jadwal_pelajaran_id' => ['nullable', 'exists:jadwal_pelajaran,id'],
            'submit_action' => ['required', 'in:draft,final'],
            'session_notes' => ['nullable', 'string', 'max:1000'],
            'revision_reason' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'in:hadir,terlambat,izin,sakit,alpa,dispen,keluar_awal'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:500'],
            'late_minutes' => ['nullable', 'array'],
            'late_minutes.*' => ['nullable', 'integer', 'min:1', 'max:600'],
            'left_early_minutes' => ['nullable', 'array'],
            'left_early_minutes.*' => ['nullable', 'integer', 'min:1', 'max:600'],
        ]);

        $user = $request->user();
        $tahunPelajaran = TahunPelajaran::query()->active()->firstOrFail();
        $this->ensureDateBelongsToActiveYear($validated['tanggal'], $tahunPelajaran);
        $this->authorizeInputMode($user, $validated['mode'], $validated['submit_action']);

        $kelas = Kelas::query()
            ->where('tahun_pelajaran_id', $tahunPelajaran->id)
            ->where('is_active', true)
            ->findOrFail($validated['kelas_id']);

        abort_unless(
            $this->getAccessibleClasses($user, $validated['tanggal'], $validated['mode'], $tahunPelajaran)
                ->contains('id', $kelas->id),
            403,
            'Anda tidak memiliki akses untuk mengabsen kelas ini.'
        );

        $selectedJadwal = null;
        if ($validated['mode'] === 'mapel') {
            abort_unless($validated['jadwal_pelajaran_id'], 422, 'Jadwal pelajaran wajib dipilih untuk absensi per mapel.');
            $selectedJadwal = $this->getAccessibleSchedules($user, $validated['tanggal'], $kelas->id, $tahunPelajaran)
                ->firstWhere('id', $validated['jadwal_pelajaran_id']);
            abort_unless($selectedJadwal, 403, 'Anda tidak memiliki akses untuk jadwal pelajaran ini.');
        }

        $sessionKey = $this->sessionKey(
            $validated['tanggal'],
            $kelas->id,
            $validated['mode'],
            $selectedJadwal?->id
        );
        $existingSession = AbsensiSiswaSession::query()->where('session_key', $sessionKey)->first();

        if ($existingSession?->status === 'final') {
            if ($existingSession->locked_at?->isPast() && ! $user->can('edit-final-student-attendance')) {
                abort(403, 'Sesi sudah dikunci. Hubungi admin untuk melakukan koreksi.');
            }
            if (blank($validated['revision_reason'])) {
                throw ValidationException::withMessages([
                    'revision_reason' => 'Alasan perubahan wajib diisi karena sesi ini sudah pernah difinalkan.',
                ]);
            }
        }

        $students = $this->studentsForDate($kelas, $validated['tanggal'])->pluck('id');
        $recordChanges = 0;

        DB::transaction(function () use (
            $validated, $request, $kelas, $selectedJadwal, $students, $user,
            $tahunPelajaran, $sessionKey, &$recordChanges
        ) {
            $session = AbsensiSiswaSession::query()->lockForUpdate()->firstOrNew(['session_key' => $sessionKey]);
            $beforeSession = $session->exists ? $this->sessionAuditValues($session) : [];
            $wasFinal = $session->status === 'final';

            if (! $session->exists) {
                $session->created_by = $user->id;
                $session->tanggal = $validated['tanggal'];
                $session->kelas_id = $kelas->id;
                $session->mode = $validated['mode'];
                $session->jadwal_pelajaran_id = $selectedJadwal?->id;
            } else {
                $session->version = max(1, (int) $session->version) + 1;
            }

            $isFinal = $validated['submit_action'] === 'final';
            $guruUser = $selectedJadwal?->gtk?->user;
            $session->fill([
                'tahun_pelajaran_id' => $tahunPelajaran->id,
                'mapel_id' => $selectedJadwal?->mapel_id,
                'guru_user_id' => $guruUser?->id ?? $user->id,
                'semester' => $tahunPelajaran->semester_aktif,
                'tingkat' => $kelas->tingkat,
                'kelas_snapshot' => $kelas->nama_kelas,
                'mapel_snapshot' => $selectedJadwal?->mapel_nama,
                'guru_snapshot' => $guruUser?->name ?? $user->name,
                'scheduled_start' => $selectedJadwal?->jam_mulai,
                'scheduled_end' => $selectedJadwal?->jam_selesai,
                'attendance_method' => 'manual',
                'status' => $isFinal ? 'final' : 'draft',
                'notes' => $validated['session_notes'] ?? null,
                'revision_reason' => $validated['revision_reason'] ?? null,
                'updated_by' => $user->id,
                'finalized_at' => $isFinal ? now() : null,
                'finalized_by' => $isFinal ? $user->id : null,
                'locked_at' => $isFinal ? now()->addHours(24) : null,
            ]);
            $session->save();

            foreach ($validated['statuses'] as $siswaId => $status) {
                if (! $students->contains($siswaId)) {
                    continue;
                }

                $record = AbsensiSiswaRecord::withTrashed()->firstOrNew([
                    'session_id' => $session->id,
                    'siswa_id' => $siswaId,
                ]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $beforeRecord = $record->exists ? $this->recordAuditValues($record) : [];
                $record->fill([
                    'status' => $status,
                    'late_minutes' => $status === 'terlambat' ? data_get($validated, "late_minutes.$siswaId") : null,
                    'left_early_minutes' => $status === 'keluar_awal' ? data_get($validated, "left_early_minutes.$siswaId") : null,
                    'notes' => data_get($validated, "notes.$siswaId"),
                    'attendance_method' => 'manual',
                    'source_reference' => $validated['mode'] === 'mapel' ? 'teacher_marking' : 'homeroom_marking',
                    'checked_by' => $user->id,
                ]);
                $afterRecord = $this->recordAuditValues($record);
                if (! $record->exists || $beforeRecord !== $afterRecord) {
                    $record->checked_at = now();
                    $record->save();
                    $afterRecord = $this->recordAuditValues($record);
                    $recordChanges++;
                    $this->audit->record(
                        $session,
                        $record,
                        $user,
                        $beforeRecord ? 'record_updated' : 'record_created',
                        $beforeRecord,
                        $afterRecord,
                        $validated['revision_reason'] ?? null,
                        $request
                    );
                }
            }

            $this->audit->session(
                $session,
                $user,
                ! $beforeSession ? 'session_created' : ($isFinal && ! $wasFinal ? 'session_finalized' : 'session_updated'),
                $beforeSession,
                $this->sessionAuditValues($session),
                $validated['revision_reason'] ?? null,
                $request
            );
        });

        return redirect()->route('admin.absensi-siswa.index', array_filter([
            'tanggal' => $validated['tanggal'],
            'mode' => $validated['mode'],
            'kelas_id' => $kelas->id,
            'jadwal_pelajaran_id' => $validated['jadwal_pelajaran_id'] ?? null,
        ]))->with('toastr_success', ($validated['submit_action'] === 'final' ? 'Absensi berhasil difinalkan' : 'Draft absensi berhasil disimpan')." ({$recordChanges} perubahan tercatat).");
    }

    protected function getAccessibleClasses($user, string $tanggal, string $mode, TahunPelajaran $year): Collection
    {
        $query = Kelas::query()
            ->with(['jurusan', 'tahunPelajaran'])
            ->where('tahun_pelajaran_id', $year->id)
            ->where('is_active', true);

        if ($this->isUnrestrictedStaff($user)) {
            return $query->orderBy('tingkat')->orderBy('nama_kelas')->get();
        }

        $classIds = collect();
        if ($mode === 'harian' && $this->canManageHarian($user, $year->id)) {
            $classIds = $classIds->merge(Kelas::query()
                ->where('tahun_pelajaran_id', $year->id)
                ->where('wali_kelas_id', $user->id)
                ->pluck('id'));
        }
        if ($mode === 'mapel' && $this->canManageMapel($user, $year->id) && $user->gtk) {
            $classIds = $classIds->merge(JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $year->id)
                ->where('gtk_id', $user->gtk->id)
                ->where('hari', $this->resolveHari($tanggal))
                ->where('is_active', true)
                ->pluck('kelas_id'));
        }

        return $query->whereIn('id', $classIds->unique())->orderBy('tingkat')->orderBy('nama_kelas')->get();
    }

    protected function getAccessibleSchedules($user, string $tanggal, string $kelasId, TahunPelajaran $year): Collection
    {
        $semester = strcasecmp((string) $year->semester_aktif, 'Genap') === 0 ? 2 : 1;
        $query = JadwalPelajaran::query()
            ->with(['kelas', 'gtk.user'])
            ->where('jadwal_pelajaran.tahun_pelajaran_id', $year->id)
            ->where('jadwal_pelajaran.kelas_id', $kelasId)
            ->where('jadwal_pelajaran.hari', $this->resolveHari($tanggal))
            ->where('jadwal_pelajaran.semester', $semester)
            ->where('jadwal_pelajaran.is_active', true)
            ->leftJoin('mata_pelajaran', 'mata_pelajaran.id', '=', 'jadwal_pelajaran.mapel_id')
            ->select('jadwal_pelajaran.*', 'mata_pelajaran.nama_mapel as mapel_nama')
            ->orderBy('jadwal_pelajaran.jam_ke');

        if (! $this->isUnrestrictedStaff($user) && $user->gtk) {
            $query->where('jadwal_pelajaran.gtk_id', $user->gtk->id);
        }

        return $query->get();
    }

    protected function studentsForDate(Kelas $kelas, string $tanggal): Collection
    {
        return $kelas->siswas()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->wherePivot('tanggal_masuk', '<=', $tanggal)
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('siswa_kelas.tanggal_keluar')
                    ->orWhere('siswa_kelas.tanggal_keluar', '>=', $tanggal);
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    protected function findExistingSession(string $tanggal, string $kelasId, string $mode, ?string $jadwalId): ?AbsensiSiswaSession
    {
        return AbsensiSiswaSession::query()
            ->with(['records'])
            ->where('session_key', $this->sessionKey($tanggal, $kelasId, $mode, $jadwalId))
            ->first();
    }

    protected function canManageHarian($user, string $yearId): bool
    {
        if (! $user->can('input-daily-attendance')) {
            return false;
        }

        return $this->isUnrestrictedStaff($user)
            || ($user->hasRole('Wali Kelas') && Kelas::query()
                ->where('tahun_pelajaran_id', $yearId)
                ->where('wali_kelas_id', $user->id)->exists());
    }

    protected function canManageMapel($user, string $yearId): bool
    {
        if (! $user->can('input-subject-attendance')) {
            return false;
        }

        return $this->isUnrestrictedStaff($user)
            || ($user->gtk && JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $yearId)
                ->where('gtk_id', $user->gtk->id)->where('is_active', true)->exists());
    }

    protected function authorizeInputMode($user, string $mode, string $action): void
    {
        $permission = $mode === 'harian' ? 'input-daily-attendance' : 'input-subject-attendance';
        abort_unless($user->can($permission), 403, 'Anda tidak memiliki permission untuk mode absensi ini.');
        if ($action === 'final') {
            abort_unless($user->can('finalize-student-attendance'), 403, 'Anda tidak memiliki permission untuk memfinalkan absensi.');
        }
    }

    protected function isUnrestrictedStaff($user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
    }

    protected function canBulkGenerate($user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    protected function ensureDateBelongsToActiveYear(string $tanggal, TahunPelajaran $year): void
    {
        $date = Carbon::parse($tanggal)->startOfDay();
        if (($year->tanggal_mulai && $date->lt($year->tanggal_mulai)) || ($year->tanggal_selesai && $date->gt($year->tanggal_selesai))) {
            throw ValidationException::withMessages([
                'tanggal' => "Tanggal harus berada dalam tahun pelajaran aktif {$year->nama}.",
            ]);
        }
    }

    protected function normalizeDate(?string $date): string
    {
        try {
            $parsed = Carbon::parse($date ?: now());
        } catch (\Throwable) {
            $parsed = now();
        }

        return $parsed->isFuture() ? now()->format('Y-m-d') : $parsed->format('Y-m-d');
    }

    protected function sessionKey(string $date, string $classId, string $mode, ?string $scheduleId): string
    {
        return implode(':', [$date, $classId, $mode, $mode === 'mapel' ? $scheduleId : 'daily']);
    }

    protected function resolveHari(string $tanggal): string
    {
        return match (Carbon::parse($tanggal)->dayOfWeekIso) {
            1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis',
            5 => 'jumat', 6 => 'sabtu', default => 'minggu',
        };
    }

    private function sessionAuditValues(AbsensiSiswaSession $session): array
    {
        return collect($session->only([
            'tahun_pelajaran_id', 'kelas_id', 'jadwal_pelajaran_id', 'mapel_id', 'guru_user_id',
            'tanggal', 'semester', 'tingkat', 'kelas_snapshot', 'mapel_snapshot', 'guru_snapshot',
            'mode', 'attendance_method', 'status', 'notes', 'finalized_at', 'locked_at',
            'finalized_by', 'version', 'revision_reason',
        ]))->map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value)->all();
    }

    private function recordAuditValues(AbsensiSiswaRecord $record): array
    {
        return collect($record->only([
            'status', 'late_minutes', 'left_early_minutes', 'notes', 'attendance_method',
            'source_reference', 'checked_at', 'checked_by',
        ]))->map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value)->all();
    }
}
