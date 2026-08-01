<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Services\StudentAttendanceAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Absensi harian rombel wali kelas.
 *
 * ponytail: penulisan absensi di-duplikasi ringan dari
 * Admin\AbsensiSiswaController::store (harian-only, single owned class) agar
 * portal wali kelas tetap self-contained tanpa perlu permission admin.
 * Menulis ke tabel & session_key yang IDENTIK ("$tgl:$kelasId:harian:daily")
 * + memakai StudentAttendanceAuditService, sehingga monitoring pusat tetap
 * membaca sesi yang sama. Upgrade path: bila logika finalisasi makin kompleks,
 * ekstrak core store ke sebuah service bersama yang dipakai kedua controller.
 */
class AbsensiController extends BaseWaliKelasController
{
    private const STATUSES = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'dispen', 'keluar_awal'];

    public function __construct(private StudentAttendanceAuditService $audit)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));
        $tanggal = $this->normalizeDate($request->input('tanggal'));
        $tahun = $this->activeYear();

        $students = $this->studentsForDate($kelas, $tanggal);
        $session = AbsensiSiswaSession::query()
            ->with('records')
            ->where('session_key', $this->sessionKey($tanggal, $kelas->id))
            ->first();
        $existing = $session
            ? $session->records->keyBy('siswa_id')
            : collect();

        return view('admin.gtk.wali.absensi.index', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'tanggal' => $tanggal,
            'students' => $students,
            'existing' => $existing,
            'session' => $session,
            'statuses' => self::STATUSES,
            'tahun' => $tahun,
        ]);
    }

    public function store(Request $request)
    {
        $tahun = $this->activeYear();
        abort_if($tahun === null, 422, 'Tahun pelajaran aktif tidak ditemukan.');

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kelas_id' => ['required'],
            'submit_action' => ['required', 'in:draft,final'],
            'session_notes' => ['nullable', 'string', 'max:1000'],
            'revision_reason' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'in:' . implode(',', self::STATUSES)],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:500'],
            'late_minutes' => ['nullable', 'array'],
            'late_minutes.*' => ['nullable', 'integer', 'min:1', 'max:600'],
            'left_early_minutes' => ['nullable', 'array'],
            'left_early_minutes.*' => ['nullable', 'integer', 'min:1', 'max:600'],
        ]);

        $kelas = $this->resolveKelas($validated['kelas_id']);
        $this->ensureDateBelongsToActiveYear($validated['tanggal'], $tahun);

        $sessionKey = $this->sessionKey($validated['tanggal'], $kelas->id);
        $existingSession = AbsensiSiswaSession::query()->where('session_key', $sessionKey)->first();

        // Sesi final yang sudah dikunci hanya boleh diubah admin — wali kelas tidak.
        if ($existingSession?->status === 'final' && $existingSession->locked_at?->isPast()) {
            abort(403, 'Sesi sudah dikunci. Hubungi admin untuk melakukan koreksi.');
        }
        if ($existingSession?->status === 'final' && blank($validated['revision_reason'])) {
            throw ValidationException::withMessages([
                'revision_reason' => 'Alasan perubahan wajib diisi karena sesi ini sudah difinalkan.',
            ]);
        }

        $user = $request->user();
        $students = $this->studentsForDate($kelas, $validated['tanggal'])->pluck('id');
        $changes = 0;

        DB::transaction(function () use ($validated, $request, $kelas, $students, $user, $tahun, $sessionKey, &$changes) {
            $session = AbsensiSiswaSession::query()->lockForUpdate()->firstOrNew(['session_key' => $sessionKey]);
            $before = $session->exists ? $this->sessionValues($session) : [];

            if (! $session->exists) {
                $session->created_by = $user->id;
                $session->tanggal = $validated['tanggal'];
                $session->kelas_id = $kelas->id;
                $session->mode = 'harian';
            } else {
                $session->version = max(1, (int) $session->version) + 1;
            }

            $isFinal = $validated['submit_action'] === 'final';
            $session->fill([
                'tahun_pelajaran_id' => $tahun->id,
                'guru_user_id' => $user->id,
                'semester' => $tahun->semester_aktif,
                'tingkat' => $kelas->tingkat,
                'kelas_snapshot' => $kelas->nama_kelas,
                'guru_snapshot' => $user->name,
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
                $beforeRecord = $record->exists ? $this->recordValues($record) : [];
                $record->fill([
                    'status' => $status,
                    'late_minutes' => $status === 'terlambat' ? data_get($validated, "late_minutes.$siswaId") : null,
                    'left_early_minutes' => $status === 'keluar_awal' ? data_get($validated, "left_early_minutes.$siswaId") : null,
                    'notes' => data_get($validated, "notes.$siswaId"),
                    'attendance_method' => 'manual',
                    'source_reference' => 'homeroom_marking',
                    'checked_by' => $user->id,
                ]);
                $afterRecord = $this->recordValues($record);
                if (! $record->exists || $beforeRecord !== $afterRecord) {
                    $record->checked_at = now();
                    $record->save();
                    $changes++;
                    $this->audit->record(
                        $session,
                        $record,
                        $user,
                        $beforeRecord ? 'record_updated' : 'record_created',
                        $beforeRecord,
                        $this->recordValues($record),
                        $validated['revision_reason'] ?? null,
                        $request
                    );
                }
            }

            $wasFinal = ($before['status'] ?? null) === 'final';
            $this->audit->session(
                $session,
                $user,
                ! $before ? 'session_created' : ($isFinal && ! $wasFinal ? 'session_finalized' : 'session_updated'),
                $before,
                $this->sessionValues($session),
                $validated['revision_reason'] ?? null,
                $request
            );
        });

        $msg = ($validated['submit_action'] === 'final' ? 'Absensi difinalkan' : 'Draft absensi tersimpan')
            . " ({$changes} perubahan).";

        return redirect()->route('admin.gtk.wali.absensi.index', [
            'kelas_id' => $kelas->id,
            'tanggal' => $validated['tanggal'],
        ])->with('success', $msg);
    }

    /**
     * Rekap absensi read-only per periode (harian/mingguan/bulanan).
     */
    public function rekap(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));
        $periode = in_array($request->input('periode'), ['hari', 'minggu', 'bulan'], true)
            ? $request->input('periode') : 'minggu';
        $anchor = $this->normalizeDate($request->input('tanggal'));
        $ref = Carbon::parse($anchor);

        [$start, $end, $label] = match ($periode) {
            'hari' => [$ref->copy()->startOfDay(), $ref->copy()->endOfDay(), $ref->translatedFormat('l, d F Y')],
            'bulan' => [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth(), $ref->translatedFormat('F Y')],
            default => [$ref->copy()->startOfWeek(Carbon::MONDAY), $ref->copy()->endOfWeek(Carbon::SUNDAY),
                $ref->copy()->startOfWeek(Carbon::MONDAY)->translatedFormat('d M') . ' – ' . $ref->copy()->endOfWeek(Carbon::SUNDAY)->translatedFormat('d M Y')],
        };

        $rows = AbsensiSiswaRecord::query()
            ->join('absensi_siswa_sessions as s', 's.id', '=', 'absensi_siswa_records.session_id')
            ->where('s.kelas_id', $kelas->id)
            ->where('s.mode', 'harian')
            ->whereNull('s.deleted_at')
            ->whereBetween('s.tanggal', [$start->toDateString(), $end->toDateString()])
            ->select('absensi_siswa_records.siswa_id', 'absensi_siswa_records.status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('absensi_siswa_records.siswa_id', 'absensi_siswa_records.status')
            ->get();

        $totals = collect(self::STATUSES)->mapWithKeys(fn ($s) => [$s => (int) $rows->where('status', $s)->sum('jumlah')]);
        $perSiswa = $rows->groupBy('siswa_id')->map(function ($group) {
            $summary = collect(self::STATUSES)->mapWithKeys(fn ($s) => [$s => (int) $group->where('status', $s)->sum('jumlah')]);
            $summary['total'] = $summary->sum();

            return $summary;
        });

        $students = $this->studentsForDate($kelas, $end->toDateString());
        $hariAktif = AbsensiSiswaSession::query()
            ->where('kelas_id', $kelas->id)
            ->where('mode', 'harian')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->distinct('tanggal')
            ->count('tanggal');

        return view('admin.gtk.wali.absensi.rekap', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'periode' => $periode,
            'tanggal' => $anchor,
            'label' => $label,
            'statuses' => self::STATUSES,
            'totals' => $totals,
            'perSiswa' => $perSiswa,
            'students' => $students,
            'hariAktif' => $hariAktif,
        ]);
    }

    protected function studentsForDate(Kelas $kelas, string $tanggal): Collection
    {
        return $kelas->siswas()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->wherePivot('tanggal_masuk', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('siswa_kelas.tanggal_keluar')
                    ->orWhere('siswa_kelas.tanggal_keluar', '>=', $tanggal);
            })
            ->orderByRaw('COALESCE(siswa_kelas.nomor_urut_absen, 9999)')
            ->orderBy('nama_lengkap')
            ->get();
    }

    protected function sessionKey(string $date, string $kelasId): string
    {
        return implode(':', [$date, $kelasId, 'harian', 'daily']);
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

    protected function ensureDateBelongsToActiveYear(string $tanggal, TahunPelajaran $year): void
    {
        $date = Carbon::parse($tanggal)->startOfDay();
        if (($year->tanggal_mulai && $date->lt($year->tanggal_mulai)) || ($year->tanggal_selesai && $date->gt($year->tanggal_selesai))) {
            throw ValidationException::withMessages([
                'tanggal' => "Tanggal harus berada dalam tahun pelajaran aktif {$year->nama}.",
            ]);
        }
    }

    private function sessionValues(AbsensiSiswaSession $session): array
    {
        return collect($session->only([
            'tahun_pelajaran_id', 'kelas_id', 'tanggal', 'semester', 'tingkat',
            'mode', 'status', 'notes', 'finalized_at', 'locked_at', 'finalized_by', 'version', 'revision_reason',
        ]))->map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : $v)->all();
    }

    private function recordValues(AbsensiSiswaRecord $record): array
    {
        return collect($record->only([
            'status', 'late_minutes', 'left_early_minutes', 'notes', 'attendance_method', 'source_reference', 'checked_at', 'checked_by',
        ]))->map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : $v)->all();
    }
}
