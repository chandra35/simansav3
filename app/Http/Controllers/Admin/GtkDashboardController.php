<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanKonseling;
use App\Models\AppSetting;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Services\GtkScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GtkDashboardController extends Controller
{
    /**
     * Display GTK dashboard
     */
    public function index(Request $request, GtkScheduleService $scheduleService)
    {
        $this->authorize('view-gtk-dashboard');

        $user = Auth::user();
        $gtk = $user->gtk;

        // If GTK record doesn't exist, create one
        if (! $gtk) {
            $gtk = Gtk::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'nik' => $user->username,
                'jenis_kelamin' => 'L', // Default, will be updated in profile
                'created_by' => $user->id,
            ]);
        }

        // Check if user needs to change password (first login)
        if ($user->is_first_login && ! $request->attributes->has('impersonation')) {
            return redirect()->route('admin.gtk.profile.password')
                ->with('info', 'Silakan ganti password Anda terlebih dahulu untuk keamanan akun.');
        }

        // Check if profile is incomplete
        $needsCompletion = ! $gtk->data_diri_completed || ! $gtk->data_kepeg_completed;

        // Get statistics for GTK
        $stats = [
            'data_diri_completed' => $gtk->data_diri_completed,
            'data_kepeg_completed' => $gtk->data_kepeg_completed,
            'completion_percentage' => $this->calculateCompletionPercentage($gtk),
        ];

        $tahunAktif = TahunPelajaran::query()->active()->first();
        $waliKelasRombels = $tahunAktif
            ? Kelas::query()
                ->where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('wali_kelas_id', $user->id)
                ->where('is_active', true)
                ->with([
                    'jurusan',
                    'waliKelas.gtk',
                    'ketuaKelasRecord.siswa',
                ])
                ->withCount('siswaAktif')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();
        $isWaliKelas = $user->hasRole('Wali Kelas') || $waliKelasRombels->isNotEmpty();
        $relatedClassIds = $waliKelasRombels->pluck('id');
        if ($tahunAktif) {
            $relatedClassIds = $relatedClassIds->merge(JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $tahunAktif->id)->where('gtk_id', $gtk->id)
                ->where('is_active', true)->pluck('kelas_id'))->unique()->values();
        }
        $teacherNotices = $relatedClassIds->isEmpty() ? collect() : CatatanKonseling::query()
            ->with(['siswa.kelasTahunAktif'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('share_with_teachers', true)->whereNotNull('teacher_notice')
            ->whereHas('siswa.kelasTahunAktif', fn ($query) => $query->whereIn('kelas.id', $relatedClassIds))
            ->latest('tanggal_konseling')->limit(12)->get();
        $scheduleSettings = AppSetting::getInstance();
        $gtk->load(['asramaAssignments' => fn ($query) => $query->where('is_active', true)->with('asrama')]);
        $now = now();
        $todaySchedules = $scheduleService->decorateSchedules(
            $scheduleService->schedulesForDay($gtk, $tahunAktif, $now),
            $now
        );
        $weeklyTeachingSchedules = $scheduleService->schedulesForWeek($gtk, $tahunAktif);
        $assignmentRoles = $gtk->penugasan()
            ->active()
            ->with('jenis:id,nama')
            ->latest('mulai_tugas')
            ->get()
            ->map(fn ($assignment) => [
                'name' => $assignment->jenis?->nama ?: 'Penugasan GTK',
                'detail' => $assignment->unit_nama,
            ]);
        if ($isWaliKelas) {
            $assignmentRoles->prepend([
                'name' => 'Wali Kelas',
                'detail' => $waliKelasRombels->pluck('nama_lengkap')->filter()->implode(', '),
            ]);
        }
        $teachingSummary = [
            'today_slots' => $todaySchedules->count(),
            'weekly_slots' => $weeklyTeachingSchedules->count(),
            'teaching_days' => $weeklyTeachingSchedules->pluck('hari')->unique()->count(),
        ];
        $scheduleReminder = $scheduleService->reminder($todaySchedules, $gtk, $scheduleSettings, now());
        $scheduleReminderConfig = [
            'enabled' => (bool) $scheduleSettings->gtk_schedule_reminder_enabled,
            'minutes' => max(1, (int) $scheduleSettings->gtk_schedule_reminder_minutes),
            'greeting' => $scheduleService->greeting($gtk, $scheduleSettings),
            'server_now' => now()->toIso8601String(),
        ];

        return view('admin.gtk.dashboard', compact(
            'gtk',
            'stats',
            'needsCompletion',
            'tahunAktif',
            'waliKelasRombels',
            'isWaliKelas', 'teacherNotices', 'todaySchedules', 'scheduleReminder', 'scheduleReminderConfig',
            'assignmentRoles', 'teachingSummary'
        ));
    }

    public function mySchedule(GtkScheduleService $scheduleService)
    {
        $this->authorize('view-gtk-dashboard');

        $gtk = Auth::user()->gtk;
        abort_unless($gtk, 404);

        $tahunAktif = TahunPelajaran::query()->active()->first();
        $schedules = $scheduleService->decorateSchedules($scheduleService->schedulesForWeek($gtk, $tahunAktif));

        return view('admin.gtk.my-schedule', [
            'gtk' => $gtk,
            'tahunAktif' => $tahunAktif,
            'schedulesByDay' => $schedules->groupBy('hari'),
            'dayLabels' => JadwalPelajaran::HARI,
        ]);
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateCompletionPercentage($gtk)
    {
        $total = 0;
        $completed = 0;

        // Data Diri fields (wajib)
        $dataDiriFields = ['nama_lengkap', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'alamat'];
        foreach ($dataDiriFields as $field) {
            $total++;
            if (! empty($gtk->$field)) {
                $completed++;
            }
        }

        // Data Kepegawaian fields (wajib: status_kepegawaian, jabatan saja. NUPTK & TMT tidak wajib)
        $dataKepegFields = ['status_kepegawaian', 'jabatan'];
        foreach ($dataKepegFields as $field) {
            $total++;
            if (! empty($gtk->$field)) {
                $completed++;
            }
        }

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
}
