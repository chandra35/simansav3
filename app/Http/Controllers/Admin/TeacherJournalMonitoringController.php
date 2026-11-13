<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Gtk;
use App\Models\PenugasanGtk;
use App\Models\TahunPelajaran;
use App\Models\TeacherJournal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TeacherJournalMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-monitoring-teacher-journal');

        [$dari, $sampai] = $this->period($request);
        $search = trim((string) $request->input('q', ''));
        $kategori = $request->input('kategori', 'pendidik');
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'most_active');

        $years = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get();
        $activeYear = $years->firstWhere('is_active', true) ?? $years->first();

        $data = $this->buildMonitoringData($dari, $sampai, $search ?: null, $kategori, $status, $sort);

        return view('admin.monitoring.journals.index', [
            'rows' => $data['rows'],
            'teachers' => $data['rows'],
            'stats' => $data['stats'],
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'search' => $search,
            'q' => $search,
            'kategori' => $kategori,
            'status' => $status,
            'sort' => $sort,
            'years' => $years,
            'activeYear' => $activeYear,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view-monitoring-teacher-journal');

        [$dari, $sampai] = $this->period($request);
        $jenis = (string) $request->input('jenis', '');

        $gtk = $user->gtk ?: Gtk::where('user_id', $user->id)->first();

        $journalsQuery = TeacherJournal::query()
            ->with(['kelas', 'jadwalPelajaran.mataPelajaran', 'absensiSession'])
            ->where(function ($q) use ($user, $gtk) {
                $q->where('user_id', $user->id);
                if ($gtk) {
                    $q->orWhere('gtk_id', $gtk->id);
                }
            })
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        $journals = $journalsQuery->paginate(15)->withQueryString();

        $allTeacherJournals = TeacherJournal::query()
            ->where(function ($q) use ($user, $gtk) {
                $q->where('user_id', $user->id);
                if ($gtk) {
                    $q->orWhere('gtk_id', $gtk->id);
                }
            })
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->get();

        $totalMinutes = (int) $allTeacherJournals->sum('durasi_menit');
        $attSummaries = $allTeacherJournals->pluck('attendance_summary')->filter();
        $avgAttendance = $attSummaries->isNotEmpty()
            ? round($attSummaries->avg('persentase'), 1)
            : null;

        $teacherStats = [
            'total' => $allTeacherJournals->count(),
            'teaching' => $allTeacherJournals->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count(),
            'homeroom' => $allTeacherJournals->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count(),
            'minutes' => $totalMinutes,
            'hours' => round($totalMinutes / 60, 1),
            'avg_attendance' => $avgAttendance,
        ];

        return view('admin.monitoring.journals.show', [
            'user' => $user,
            'gtk' => $gtk,
            'journals' => $journals,
            'teacherStats' => $teacherStats,
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'jenis' => $jenis,
        ]);
    }

    public function entries(Request $request, User $user)
    {
        $this->authorize('view-monitoring-teacher-journal');

        [$dari, $sampai] = $this->period($request);
        $gtk = $user->gtk ?: Gtk::where('user_id', $user->id)->first();

        $journals = TeacherJournal::query()
            ->with(['kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where(function ($q) use ($user, $gtk) {
                $q->where('user_id', $user->id);
                if ($gtk) {
                    $q->orWhere('gtk_id', $gtk->id);
                }
            })
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $totalMinutes = (int) $journals->sum('durasi_menit');
        $attSummaries = $journals->pluck('attendance_summary')->filter();
        $avgAttendance = $attSummaries->isNotEmpty()
            ? round($attSummaries->avg('persentase'), 1)
            : null;

        return response()->json([
            'success' => true,
            'teacher' => [
                'name' => $gtk?->nama_lengkap ?? $user->name,
                'nip' => $gtk?->nip ?: '-',
                'nuptk' => $gtk?->nuptk ?: '-',
                'role' => $gtk?->jenis_ptk ?? 'Guru',
                'foto' => $gtk?->foto_profile_url ?? null,
            ],
            'stats' => [
                'total' => $journals->count(),
                'teaching' => $journals->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count(),
                'homeroom' => $journals->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count(),
                'hours' => round($totalMinutes / 60, 1),
                'avg_attendance' => $avgAttendance,
            ],
            'entries' => $journals->map(fn ($j) => [
                'id' => $j->id,
                'tanggal' => $j->tanggal ? Carbon::parse($j->tanggal)->translatedFormat('d F Y') : '-',
                'hari' => $j->tanggal ? Carbon::parse($j->tanggal)->translatedFormat('l') : '-',
                'jenis' => $j->jenis,
                'jenis_label' => $j->jenis === 'mengajar' ? 'Mengajar' : 'Wali Kelas',
                'kelas' => $j->kelas_snapshot ?: ($j->kelas?->nama_kelas ?? '-'),
                'mapel' => $j->mapel_snapshot ?: ($j->jadwalPelajaran?->mataPelajaran?->nama_mapel ?? '-'),
                'topik' => $j->topik,
                'kegiatan' => $j->kegiatan,
                'durasi' => $j->durasi_menit ? $j->durasi_menit . ' Menit' : '-',
                'attendance' => $j->attendance_summary,
                'asesmen' => $j->asesmen_tindak_lanjut,
                'catatan' => $j->catatan,
            ]),
        ]);
    }

    public function exportTeacher(Request $request, User $user)
    {
        $this->authorize('view-monitoring-teacher-journal');
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 300);
        set_time_limit(300);

        [$dari, $sampai] = $this->period($request);
        $jenis = (string) $request->input('jenis', '');
        $gtk = $user->gtk ?: Gtk::where('user_id', $user->id)->first();

        $journals = TeacherJournal::query()
            ->with(['kelas', 'absensiSession.records'])
            ->where(function ($q) use ($user, $gtk) {
                $q->where('user_id', $user->id);
                if ($gtk) {
                    $q->orWhere('gtk_id', $gtk->id);
                }
            })
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        $activeYear = TahunPelajaran::query()->active()->first()
            ?? TahunPelajaran::query()->orderByDesc('tahun_mulai')->first();

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

        $teacherName = $gtk?->nama_lengkap ?? $user->name;
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $teacherName);

        return Pdf::loadView('admin.gtk.journals.pdf', [
            'journals' => $journals,
            'year' => $activeYear,
            'user' => $user,
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
        ])->setPaper('a4', 'landscape')->download('jurnal-guru-'.$cleanName.'-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.pdf');
    }

    public function exportRekap(Request $request)
    {
        $this->authorize('view-monitoring-teacher-journal');
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 300);
        set_time_limit(300);

        [$dari, $sampai] = $this->period($request);
        $search = trim((string) $request->input('q', ''));
        $kategori = $request->input('kategori', 'pendidik');
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'most_active');

        $activeYear = TahunPelajaran::query()->active()->first()
            ?? TahunPelajaran::query()->orderByDesc('tahun_mulai')->first();

        $kepalaMadrasah = PenugasanGtk::query()->with('gtk')
            ->where('status', 'active')
            ->whereHas('jenis', fn ($q) => $q->where('kelompok', 'kepala_madrasah'))
            ->first()?->gtk;

        $setting = AppSetting::first();

        $data = $this->buildMonitoringData($dari, $sampai, $search ?: null, $kategori, $status, $sort);

        return Pdf::loadView('admin.monitoring.journals.rekap-pdf', [
            'rows' => $data['rows'],
            'stats' => $data['stats'],
            'dari' => $dari,
            'sampai' => $sampai,
            'year' => $activeYear,
            'kepalaMadrasah' => $kepalaMadrasah,
            'setting' => $setting,
        ])->setPaper('a4', 'landscape')->download('rekapitulasi-jurnal-guru-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.pdf');
    }

    private function buildMonitoringData(
        Carbon $dari,
        Carbon $sampai,
        ?string $search = null,
        string $kategori = 'pendidik',
        string $status = 'all',
        string $sort = 'most_active'
    ): array {
        $gtkQuery = Gtk::query()
            ->active()
            ->with(['user', 'jadwalPelajaran.mataPelajaran'])
            ->when($kategori === 'pendidik', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('kategori_ptk', 'Pendidik')
                        ->orWhere('jenis_ptk', 'like', '%Guru%')
                        ->orWhere('jabatan', 'like', '%Guru%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->whereHas('roles', fn ($rq) => $rq->where('name', 'like', '%Guru%'));
                        })
                        ->orWhereHas('teacherJournals');
                });
            })
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama_lengkap', 'like', "%{$term}%")
                        ->orWhere('nip', 'like', "%{$term}%")
                        ->orWhere('nuptk', 'like', "%{$term}%")
                        ->orWhere('npk', 'like', "%{$term}%");
                });
            })
            ->orderBy('nama_lengkap');

        $gtks = $gtkQuery->get();

        $periodJournals = TeacherJournal::query()
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->get();

        $journalsByUser = $periodJournals->groupBy('user_id');
        $journalsByGtk = $periodJournals->groupBy('gtk_id');

        $rows = $gtks->map(function (Gtk $gtk) use ($journalsByUser, $journalsByGtk) {
            $teacherJournals = collect();
            if ($gtk->user_id && isset($journalsByUser[$gtk->user_id])) {
                $teacherJournals = $teacherJournals->concat($journalsByUser[$gtk->user_id]);
            }
            if (isset($journalsByGtk[$gtk->id])) {
                $teacherJournals = $teacherJournals->concat($journalsByGtk[$gtk->id]);
            }
            $teacherJournals = $teacherJournals->unique('id')->sortByDesc('tanggal');

            $totalCount = $teacherJournals->count();
            $teachingCount = $teacherJournals->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count();
            $homeroomCount = $teacherJournals->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count();
            $totalMinutes = (int) $teacherJournals->sum('durasi_menit');
            $attSummaries = $teacherJournals->pluck('attendance_summary')->filter();
            $avgAttendance = $attSummaries->isNotEmpty()
                ? round($attSummaries->avg('persentase'), 1)
                : null;
            $lastDate = $teacherJournals->max('tanggal');

            $subjects = $gtk->jadwalPelajaran
                ->pluck('mataPelajaran.nama_mapel')
                ->filter()
                ->unique()
                ->values();

            return [
                'gtk' => $gtk,
                'user' => $gtk->user,
                'user_id' => $gtk->user_id,
                'nama' => $gtk->nama_lengkap,
                'nip' => $gtk->nip ?: ($gtk->nuptk ?: '-'),
                'jabatan' => $gtk->jenis_ptk ?: ($gtk->jabatan ?: 'Guru'),
                'total_journals' => $totalCount,
                'teaching_count' => $teachingCount,
                'homeroom_count' => $homeroomCount,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 1),
                'avg_attendance' => $avgAttendance,
                'last_journal_date' => $lastDate,
                'has_journals' => $totalCount > 0,
                'subjects' => $subjects,
            ];
        });

        // Filter by journal status
        if ($status === 'has_journals') {
            $rows = $rows->where('has_journals', true);
        } elseif ($status === 'no_journals') {
            $rows = $rows->where('has_journals', false);
        }

        // Sort rows
        $rows = match ($sort) {
            'name_asc' => $rows->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'latest_activity' => $rows->sortByDesc('last_journal_date')->values(),
            'avg_attendance' => $rows->sortByDesc('avg_attendance')->values(),
            default => $rows->sortByDesc('total_journals')->values(),
        };

        // Calculate summary stats
        $totalTeachers = $gtks->count();
        $activeTeachers = $gtks->filter(function ($g) use ($journalsByUser, $journalsByGtk) {
            $hasUser = $g->user_id && isset($journalsByUser[$g->user_id]) && $journalsByUser[$g->user_id]->isNotEmpty();
            $hasGtk = isset($journalsByGtk[$g->id]) && $journalsByGtk[$g->id]->isNotEmpty();
            return $hasUser || $hasGtk;
        })->count();

        $inactiveTeachers = max(0, $totalTeachers - $activeTeachers);
        $totalEntries = $periodJournals->count();
        $totalTeachingEntries = $periodJournals->where('jenis', TeacherJournal::JENIS_MENGAJAR)->count();
        $totalHomeroomEntries = $periodJournals->where('jenis', TeacherJournal::JENIS_WALI_KELAS)->count();
        $totalDurationMinutes = (int) $periodJournals->sum('durasi_menit');
        $allAttSummaries = $periodJournals->pluck('attendance_summary')->filter();
        $overallAvgAtt = $allAttSummaries->isNotEmpty()
            ? round($allAttSummaries->avg('persentase'), 1)
            : null;

        $stats = [
            'total_teachers' => $totalTeachers,
            'active_teachers' => $activeTeachers,
            'inactive_teachers' => $inactiveTeachers,
            'participation_rate' => $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100, 1) : 0,
            'total_entries' => $totalEntries,
            'total_teaching' => $totalTeachingEntries,
            'total_homeroom' => $totalHomeroomEntries,
            'total_minutes' => $totalDurationMinutes,
            'total_hours' => round($totalDurationMinutes / 60, 1),
            'overall_avg_attendance' => $overallAvgAtt,
        ];

        return [
            'rows' => $rows,
            'stats' => $stats,
        ];
    }

    private function period(Request $request): array
    {
        $dari = $request->filled('dari')
            ? Carbon::parse($request->dari)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->sampai)->endOfDay()
            : now()->endOfDay();

        return [$dari, $sampai];
    }
}
