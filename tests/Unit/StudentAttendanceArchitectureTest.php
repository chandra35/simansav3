<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentAttendanceArchitectureTest extends TestCase
{
    public function test_joined_subject_schedule_query_qualifies_ambiguous_columns(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/AbsensiSiswaController.php');

        foreach (['kelas_id', 'hari', 'semester', 'is_active', 'jam_ke', 'gtk_id'] as $column) {
            $this->assertStringContainsString("jadwal_pelajaran.{$column}", $controller);
        }

        $this->assertStringNotContainsString("->where('semester', \$semester)", $controller);
        $this->assertStringNotContainsString("->orderBy('jam_ke')", $controller);
    }

    public function test_subject_attendance_is_manual_and_finalized_before_analytics(): void
    {
        $input = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $analytics = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php');

        $this->assertStringContainsString("'attendance_method' => 'manual'", $input);
        $this->assertStringContainsString("'source_reference' => \$validated['mode'] === 'mapel' ? 'teacher_marking'", $input);
        $this->assertStringContainsString("where('sessions.status', 'final')", $analytics);
    }

    public function test_daily_face_and_subject_records_are_analyzed_as_separate_sources(): void
    {
        $analytics = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php');
        $insights = file_get_contents(dirname(__DIR__, 2).'/app/Services/AttendanceInsightService.php');

        $this->assertStringContainsString("DB::table('absensis')", $analytics);
        $this->assertStringContainsString("\$row->source_type = 'daily_face'", $analytics);
        $this->assertStringContainsString("'daily_subject_conflict'", $insights);
    }

    public function test_schema_keeps_snapshots_audits_and_granular_permissions(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_07_19_150000_harden_student_attendance_system.php');

        foreach (['kelas_snapshot', 'mapel_snapshot', 'guru_snapshot', 'absensi_siswa_audits',
            'view-attendance-analytics', 'view-attendance-audit', 'edit-final-student-attendance'] as $required) {
            $this->assertStringContainsString($required, $migration);
        }
    }

    public function test_admin_monitoring_lists_the_complete_active_roster_for_today(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $menu = file_get_contents($root.'/config/adminlte.php');
        $permissionSync = file_get_contents($root.'/app/Services/PermissionSyncService.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/monitoring.blade.php');
        $permission = file_get_contents($root.'/database/migrations/2026_07_29_160500_add_student_attendance_monitoring_permission.php');

        $this->assertStringContainsString('public function monitoring(Request $request)', $controller);
        $this->assertStringContainsString("->where('sk.tahun_pelajaran_id', \$tahunPelajaran->id)", $controller);
        $this->assertStringContainsString("->where('attendance_sessions.mode', '=', 'harian')", $controller);
        $this->assertStringContainsString('attendance_records.id IS NOT NULL', $controller);
        $this->assertStringContainsString("'unrecorded' => max(0", $controller);
        $this->assertStringContainsString("name('absensi-siswa.monitoring')", $routes);
        $this->assertStringNotContainsString("'route' => 'admin.absensi-siswa.monitoring'", $menu);
        $this->assertStringContainsString("'monitor-all-student-attendance'", $permissionSync);
        $this->assertStringContainsString('Absensi Seluruh Siswa', $view);
        $this->assertStringContainsString('Belum Direkam', $view);
        $this->assertStringContainsString("'monitor-all-student-attendance'", $permission);
    }

    public function test_student_analytics_is_in_homeroom_menu_and_uses_notes_with_strict_scope(): void
    {
        $root = dirname(__DIR__, 2);
        $menu = file_get_contents($root.'/config/adminlte.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/analytics.blade.php');

        $this->assertStringContainsString("'route' => 'admin.absensi-siswa.analytics'", $menu);
        $this->assertSame(1, substr_count($menu, "'route' => 'admin.absensi-siswa.analytics'"));
        $this->assertStringNotContainsString("'route' => 'admin.absensi-siswa.index'", $menu);
        $this->assertStringContainsString("->where('wali_kelas_id', \$user->id)->where('is_active', true)", $controller);
        $this->assertStringNotContainsString('JadwalPelajaran', $controller);
        $this->assertStringContainsString('CatatanWaliKelas::query()', $controller);
        $this->assertStringContainsString('Catatan wali kelas', $view);
        $this->assertStringContainsString('admin.gtk.wali.catatan.index', $view);
        $this->assertStringContainsString('class="row mb-2"', $view);
        $this->assertStringContainsString('card bg-gradient-primary text-white attendance-hero', $view);
        $this->assertStringContainsString('card card-outline card-primary filter-card', $view);
        $this->assertStringContainsString('class="metric-grid mb-3"', $view);
        $this->assertStringNotContainsString('!important', $view);
    }

    public function test_presensi_admin_is_gtk_only_and_personally_scoped_for_regular_gtk(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiController.php');
        $faceController = file_get_contents($root.'/app/Http/Controllers/Admin/FaceRegistrationController.php');
        $settingController = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiSettingController.php');
        $kiosk = file_get_contents($root.'/resources/views/admin/absensi/kiosk.blade.php');
        $recap = file_get_contents($root.'/resources/views/admin/absensi/rekap.blade.php');
        $settings = file_get_contents($root.'/resources/views/admin/absensi/settings.blade.php');

        $this->assertStringContainsString('private function isPersonalGtkScope', $controller);
        $this->assertStringContainsString("->where('user_id', \$request->user()->id)", $controller);
        $this->assertStringContainsString("\$userType = 'gtk';", $controller);
        $this->assertStringContainsString('streamDownload', $controller);
        $this->assertStringContainsString("\$selectedType = 'gtk';", $faceController);
        $this->assertStringNotContainsString('Mode Siswa', $kiosk);
        $this->assertStringContainsString('Unduh CSV', $recap);
        $this->assertStringContainsString("->whereNotIn('key', ['jam_masuk_siswa', 'jam_pulang_siswa'])", $settingController);
        $this->assertStringContainsString('khusus untuk GTK', $settings);
    }

    public function test_teacher_and_homeroom_notes_use_a_per_student_modal(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiSiswaController.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/siswa.blade.php');

        $this->assertStringContainsString("'notes.*' => ['nullable', 'string', 'max:500']", $controller);
        $this->assertStringContainsString('id="studentNoteModal"', $view);
        $this->assertStringContainsString('class="student-note-trigger', $view);
        $this->assertStringContainsString('id="studentNoteEditor"', $view);
        $this->assertStringContainsString("$('#btnApplyStudentNote').on('click'", $view);
        $this->assertStringContainsString('Tersimpan bersama draft atau finalisasi absensi.', $view);
    }

    public function test_student_dashboard_uses_a_compact_neutral_heading(): void
    {
        $dashboard = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/dashboard.blade.php');

        $this->assertStringContainsString('class="student-dashboard-header"', $dashboard);
        $this->assertStringContainsString('Ringkasan profil, kelas, dan kelengkapan data Anda', $dashboard);
        $this->assertStringContainsString('background: #fff;', $dashboard);
        $this->assertStringNotContainsString('class="callout callout-info student-welcome-hero"', $dashboard);
        $this->assertStringNotContainsString('background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)', $dashboard);
        $this->assertStringNotContainsString('id="pageLoader"', $dashboard);
        $this->assertStringNotContainsString('lottie.loadAnimation', $dashboard);
    }

    public function test_attendance_settings_use_sweetalert_confirmations(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/absensi/settings.blade.php');

        $this->assertStringContainsString("@section('plugins.Sweetalert2', true)", $view);
        $this->assertSame(3, substr_count($view, 'class="d-inline js-confirm-form"'));
        $this->assertStringContainsString("document.querySelectorAll('.js-confirm-form')", $view);
        $this->assertStringContainsString('await Swal.fire({', $view);
        $this->assertStringContainsString('HTMLFormElement.prototype.submit.call(form)', $view);
        $this->assertStringNotContainsString('onclick="return confirm(', $view);
    }
}
