<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\AppSetting;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\PenugasanGtk;
use App\Models\TahunPelajaran;
use App\Models\TeacherJournal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class TeacherJournalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-teacher-journal');
        [$year, $gtk, $waliClasses] = $this->context();
        [$dari, $sampai] = $this->period($request);
        $jenis = $request->string('jenis')->toString();
        abort_unless(in_array($jenis, ['', TeacherJournal::JENIS_MENGAJAR, TeacherJournal::JENIS_WALI_KELAS], true), 422);

        $journalQuery = $this->journalQuery($request, $year, $dari, $sampai, $jenis);
        $journals = (clone $journalQuery)->paginate(15);
        $summary = [
            'total' => (clone $journalQuery)->count(),
            'teaching' => (clone $journalQuery)->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count(),
            'homeroom' => (clone $journalQuery)->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count(),
            'minutes' => (int) ((clone $journalQuery)->sum('durasi_menit') ?? 0),
        ];
        $schedules = $gtk && $year ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
            ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->orderBy('hari')->orderBy('jam_ke')->get() : collect();

        return view('admin.gtk.journals.index', compact('journals', 'summary', 'schedules', 'waliClasses', 'jenis', 'dari', 'sampai'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-teacher-journal');
        [$year, $gtk, $waliClasses] = $this->context();
        abort_unless($year && $gtk, 422, 'Tahun pelajaran aktif atau profil GTK belum tersedia.');
        $data = $this->validatedData($request);
        abort_unless(Carbon::parse($data['tanggal'])->betweenIncluded($year->tanggal_mulai, $year->tanggal_selesai), 422, 'Tanggal jurnal harus berada dalam tahun pelajaran aktif.');
        [$schedule, $kelas] = $this->resolveJournalContext($data, $year, $gtk, $waliClasses);

        $journal = TeacherJournal::create(array_merge($data, [
            'tahun_pelajaran_id' => $year->id,
            'user_id' => Auth::id(),
            'gtk_id' => $gtk->id,
            'kelas_id' => $kelas?->id,
            'jadwal_pelajaran_id' => $schedule?->id,
            'mapel_snapshot' => $schedule?->mataPelajaran?->nama_mapel,
            'kelas_snapshot' => $kelas?->nama_lengkap ?? $kelas?->nama_kelas,
        ]));

        if ($kelas && $request->has('student_attendance') && !empty($request->input('student_attendance'))) {
            $this->syncStudentAttendance($journal, $kelas, $schedule, $data, $request, $year);
        }

        return redirect()->route('admin.gtk.journals.index')->with('success', 'Jurnal berhasil disimpan.');
    }

    public function studentsAttendance(Request $request)
    {
        $this->authorize('view-teacher-journal');
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jadwal_pelajaran_id' => ['nullable', 'uuid'],
            'kelas_id' => ['nullable', 'uuid'],
            'journal_id' => ['nullable', 'uuid'],
        ]);

        [$year, $gtk, $waliClasses] = $this->context();
        abort_unless($year, 422, 'Tahun pelajaran aktif tidak ditemukan.');

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $schedule = null;
        $kelas = null;

        $jadwalId = $validated['jadwal_pelajaran_id'] ?? null;
        $kelasId = $validated['kelas_id'] ?? null;
        $journalId = $validated['journal_id'] ?? null;

        if (filled($jadwalId)) {
            $schedule = JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
                ->where('tahun_pelajaran_id', $year->id)
                ->when($gtk, fn ($q) => $q->where('gtk_id', $gtk->id))
                ->find($jadwalId)
                ?? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()->find($jadwalId);
            $kelas = $schedule?->kelas;
        } elseif (filled($kelasId)) {
            $kelas = $waliClasses->firstWhere('id', $kelasId)
                ?? Kelas::query()->where('tahun_pelajaran_id', $year->id)->find($kelasId);
        }

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan.',
                'students' => [],
            ]);
        }

        $students = $this->studentsForDate($kelas, $tanggal);
        $journal = filled($journalId)
            ? TeacherJournal::query()->with('absensiSession.records')->where('user_id', Auth::id())->find($journalId)
            : null;

        $mode = $schedule ? 'mapel' : 'harian';
        $sessionKey = implode(':', [$tanggal, $kelas->id, $mode, $mode === 'mapel' ? $schedule->id : 'daily']);
        $existingSession = $journal?->absensiSession
            ?: AbsensiSiswaSession::query()->with('records')->where('session_key', $sessionKey)->first();
        $existingRecords = $existingSession ? $existingSession->records->keyBy('siswa_id') : collect();

        // Morning daily attendance records for smart pre-filling
        $harianKey = implode(':', [$tanggal, $kelas->id, 'harian', 'daily']);
        $harianSession = AbsensiSiswaSession::query()->with('records')->where('session_key', $harianKey)->first();
        $harianRecords = $harianSession ? $harianSession->records->keyBy('siswa_id') : collect();

        $prefilledCount = 0;
        $studentList = $students->map(function ($s) use ($existingRecords, $harianRecords, &$prefilledCount) {
            $existing = $existingRecords->get($s->id);
            $harian = $harianRecords->get($s->id);

            $status = 'hadir';
            $notes = '';
            $source = 'default';

            if ($existing) {
                $status = $existing->status;
                $notes = $existing->notes ?? '';
                $source = 'saved';
            } elseif ($harian && in_array($harian->status, ['sakit', 'izin', 'alpa', 'dispen'], true)) {
                $status = $harian->status;
                $notes = $harian->notes ?? '';
                $source = 'harian';
                $prefilledCount++;
            }

            return [
                'id' => $s->id,
                'nama' => $s->nama_lengkap,
                'nisn' => $s->nisn,
                'jenis_kelamin' => $s->jenis_kelamin,
                'status' => $status,
                'notes' => $notes,
                'source' => $source,
                'harian_status' => $harian?->status,
                'harian_notes' => $harian?->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'kelas_name' => $kelas->nama_lengkap ?? $kelas->nama_kelas,
            'mapel_name' => $schedule?->mataPelajaran?->nama_mapel,
            'mode' => $mode,
            'total' => $studentList->count(),
            'prefilled_count' => $prefilledCount,
            'has_saved_session' => (bool) $existingSession,
            'students' => $studentList,
        ]);
    }

    public function edit(TeacherJournal $journal)
    {
        $this->authorize('edit-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        [$year, $gtk, $waliClasses] = $this->context();
        $schedules = $gtk && $year ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
            ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->orderBy('hari')->orderBy('jam_ke')->get() : collect();
        return view('admin.gtk.journals.edit', compact('journal', 'schedules', 'waliClasses'));
    }

    public function update(Request $request, TeacherJournal $journal)
    {
        $this->authorize('edit-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        [$year, $gtk, $waliClasses] = $this->context();
        abort_unless($year && $gtk, 422, 'Tahun pelajaran aktif atau profil GTK belum tersedia.');
        $data = $this->validatedData($request);
        abort_unless(Carbon::parse($data['tanggal'])->betweenIncluded($year->tanggal_mulai, $year->tanggal_selesai), 422, 'Tanggal jurnal harus berada dalam tahun pelajaran aktif.');
        [$schedule, $kelas] = $this->resolveJournalContext($data, $year, $gtk, $waliClasses);

        $journal->update(array_merge($data, [
            'kelas_id' => $kelas?->id,
            'jadwal_pelajaran_id' => $schedule?->id,
            'mapel_snapshot' => $schedule?->mataPelajaran?->nama_mapel,
            'kelas_snapshot' => $kelas?->nama_lengkap ?? $kelas?->nama_kelas,
        ]));

        if ($kelas && $request->has('student_attendance') && !empty($request->input('student_attendance'))) {
            $this->syncStudentAttendance($journal, $kelas, $schedule, $data, $request, $year);
        }

        return redirect()->route('admin.gtk.journals.index')->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(TeacherJournal $journal)
    {
        $this->authorize('delete-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        $journal->delete();
        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $this->authorize('export-teacher-journal');
        [$year, $gtk] = $this->context();
        [$dari, $sampai] = $this->period($request);
        $jenis = $request->string('jenis')->toString();
        $journals = $this->journalQuery($request, $year, $dari, $sampai, $jenis)
            ->with(['kelas', 'absensiSession.records'])
            ->get();

        $kepalaMadrasah = PenugasanGtk::query()->with('gtk')
            ->where('status', 'active')
            ->whereHas('jenis', fn ($q) => $q->where('kelompok', 'kepala_madrasah'))
            ->first()?->gtk;

        $setting = AppSetting::first();

        $totalMinutes = (int) $journals->sum('durasi_menit');
        $attSummaries = $journals->pluck('attendance_summary')->filter();
        $avgAttendance = $attSummaries->isNotEmpty()
            ? round($attSummaries->avg('persentase'), 1)
            : null;

        return PDF::loadView('admin.gtk.journals.pdf', [
            'journals' => $journals,
            'year' => $year,
            'user' => Auth::user(),
            'gtk' => $gtk,
            'dari' => $dari,
            'sampai' => $sampai,
            'kepalaMadrasah' => $kepalaMadrasah,
            'setting' => $setting,
            'stats' => [
                'total_entries' => $journals->count(),
                'total_hours' => round($totalMinutes / 60, 1),
                'teaching_count' => $journals->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count(),
                'homeroom_count' => $journals->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count(),
                'avg_attendance' => $avgAttendance,
            ],
        ])->setPaper('a4', 'portrait')->download('jurnal-guru-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'jenis' => ['required', 'in:mengajar,wali_kelas'],
            'tanggal' => ['required', 'date'],
            'jadwal_pelajaran_id' => ['nullable', 'uuid'],
            'kelas_id' => ['nullable', 'uuid'],
            'topik' => ['required', 'string', 'max:255'],
            'kegiatan' => ['required', 'string', 'max:5000'],
            'asesmen_tindak_lanjut' => ['nullable', 'string', 'max:5000'],
            'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:720'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'student_attendance' => ['nullable', 'array'],
            'student_attendance.*.status' => ['nullable', 'string', 'in:hadir,sakit,izin,alpa,terlambat,dispen,keluar_awal'],
            'student_attendance.*.notes' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function studentsForDate(Kelas $kelas, string $tanggal): Collection
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

    private function syncStudentAttendance(
        TeacherJournal $journal,
        Kelas $kelas,
        ?JadwalPelajaran $schedule,
        array $data,
        Request $request,
        TahunPelajaran $year
    ): void {
        $attendanceData = $request->input('student_attendance');
        if (!is_array($attendanceData) || empty($attendanceData)) {
            return;
        }

        $mode = $data['jenis'] === TeacherJournal::JENIS_MENGAJAR ? 'mapel' : 'harian';
        $scheduleId = $schedule?->id;
        $sessionKey = implode(':', [$data['tanggal'], $kelas->id, $mode, $mode === 'mapel' ? $scheduleId : 'daily']);

        DB::transaction(function () use (
            $journal, $kelas, $schedule, $data, $attendanceData, $year, $mode, $sessionKey
        ) {
            $session = AbsensiSiswaSession::query()->firstOrNew(['session_key' => $sessionKey]);
            $isNew = !$session->exists;

            if ($isNew) {
                $session->created_by = Auth::id();
                $session->tanggal = $data['tanggal'];
                $session->kelas_id = $kelas->id;
                $session->mode = $mode;
                $session->jadwal_pelajaran_id = $schedule?->id;
                $session->version = 1;
            } else {
                $session->version = max(1, (int) $session->version) + 1;
            }

            $guruUser = $schedule?->gtk?->user ?? Auth::user();
            $session->fill([
                'tahun_pelajaran_id' => $year->id,
                'mapel_id' => $schedule?->mapel_id,
                'guru_user_id' => $guruUser->id,
                'semester' => $year->semester_aktif,
                'tingkat' => $kelas->tingkat,
                'kelas_snapshot' => $kelas->nama_lengkap ?? $kelas->nama_kelas,
                'mapel_snapshot' => $schedule?->mataPelajaran?->nama_mapel,
                'guru_snapshot' => $guruUser->name,
                'scheduled_start' => $schedule?->jam_mulai,
                'scheduled_end' => $schedule?->jam_selesai,
                'attendance_method' => 'manual',
                'status' => 'final',
                'finalized_at' => now(),
                'finalized_by' => Auth::id(),
                'notes' => 'Tercatat melalui Jurnal Guru: ' . ($journal->topik ?? ''),
                'updated_by' => Auth::id(),
            ]);
            $session->save();

            $students = $this->studentsForDate($kelas, $data['tanggal'])->keyBy('id');
            $counts = [
                'total' => 0, 'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0,
                'terlambat' => 0, 'dispen' => 0, 'keluar_awal' => 0,
            ];
            $absentStudents = [];

            foreach ($attendanceData as $siswaId => $item) {
                $student = $students->get($siswaId);
                if (!$student) {
                    continue;
                }

                $rawStatus = is_array($item) ? ($item['status'] ?? 'hadir') : $item;
                $status = in_array($rawStatus, ['hadir', 'sakit', 'izin', 'alpa', 'terlambat', 'dispen', 'keluar_awal'], true)
                    ? $rawStatus
                    : 'hadir';
                $note = is_array($item) && isset($item['notes']) && filled($item['notes'])
                    ? trim((string) $item['notes'])
                    : null;

                $record = AbsensiSiswaRecord::withTrashed()->firstOrNew([
                    'session_id' => $session->id,
                    'siswa_id' => $siswaId,
                ]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->fill([
                    'status' => $status,
                    'notes' => $note,
                    'attendance_method' => 'manual',
                    'source_reference' => 'teacher_journal',
                    'checked_by' => Auth::id(),
                    'checked_at' => now(),
                ]);
                $record->save();

                $counts['total']++;
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
                if (in_array($status, ['sakit', 'izin', 'alpa', 'dispen'], true)) {
                    $absentStudents[] = [
                        'siswa_id' => $siswaId,
                        'nama' => $student->nama_lengkap,
                        'status' => $status,
                        'catatan' => $note,
                    ];
                }
            }

            $presentCount = $counts['hadir'] + $counts['terlambat'] + $counts['keluar_awal'];
            $percentage = $counts['total'] > 0 ? round(($presentCount / $counts['total']) * 100, 1) : 100.0;

            $summary = [
                'total' => $counts['total'],
                'hadir' => $counts['hadir'],
                'sakit' => $counts['sakit'],
                'izin' => $counts['izin'],
                'alpa' => $counts['alpa'],
                'terlambat' => $counts['terlambat'],
                'dispen' => $counts['dispen'],
                'keluar_awal' => $counts['keluar_awal'],
                'persentase' => $percentage,
                'absent_students' => $absentStudents,
            ];

            $journal->update([
                'absensi_siswa_session_id' => $session->id,
                'attendance_summary' => $summary,
            ]);
        });
    }

    private function resolveJournalContext(array $data, TahunPelajaran $year, Gtk $gtk, $waliClasses): array
    {
        if ($data['jenis'] === TeacherJournal::JENIS_MENGAJAR) {
            $schedule = filled($data['jadwal_pelajaran_id']) ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
                ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->findOrFail($data['jadwal_pelajaran_id']) : null;
            return [$schedule, $schedule?->kelas];
        }
        abort_unless($waliClasses->isNotEmpty(), 403, 'Jurnal wali kelas hanya tersedia untuk wali kelas aktif.');
        $kelas = $waliClasses->firstWhere('id', $data['kelas_id']);
        abort_unless($kelas, 403, 'Kelas bukan rombel wali Anda.');
        return [null, $kelas];
    }

    private function context(): array
    {
        $year = TahunPelajaran::query()->active()->first();
        $gtk = Auth::user()->gtk;
        $waliClasses = $year ? Kelas::query()->where('tahun_pelajaran_id', $year->id)->where('wali_kelas_id', Auth::id())->where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get() : collect();
        return [$year, $gtk, $waliClasses];
    }

    private function period(Request $request): array
    {
        $dari = Carbon::parse($request->input('dari', now()->startOfMonth()->toDateString()))->startOfDay();
        $sampai = Carbon::parse($request->input('sampai', now()->toDateString()))->endOfDay();
        abort_if($dari->gt($sampai) || $dari->diffInDays($sampai) > 366, 422, 'Periode jurnal tidak valid.');
        return [$dari, $sampai];
    }

    private function journalQuery(Request $request, ?TahunPelajaran $year, Carbon $dari, Carbon $sampai, string $jenis)
    {
        $query = TeacherJournal::query()->where('user_id', Auth::id())->with('kelas')->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->latest('tanggal')->latest('created_at');
        if ($year) $query->where('tahun_pelajaran_id', $year->id);
        if ($jenis !== '') $query->where('jenis', $jenis);
        return $query;
    }
}
