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
        $this->assertStringContainsString("'route' => 'admin.absensi-siswa.monitoring'", $menu);
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
        $this->assertSame(2, substr_count($menu, "'route' => 'admin.absensi-siswa.analytics'"));
        $this->assertStringContainsString("'route' => 'admin.absensi-siswa.index'", $menu);
        $this->assertStringContainsString("'can' => 'sidebar-student-attendance-global'", $menu);
        $this->assertStringContainsString("Gate::define('sidebar-student-attendance-global'", file_get_contents($root.'/app/Providers/AuthServiceProvider.php'));
        $gtkMenuPosition = strpos($menu, "'text' => 'Presensi Gerbang'");
        $faceMenuPosition = strpos($menu, "'text' => 'Data Wajah'");
        $gtkTodayPosition = strpos($menu, "'text' => 'Dashboard GTK'", $gtkMenuPosition);
        $studentMenuPosition = strpos($menu, "'text' => 'Kehadiran Kelas'", $gtkMenuPosition);
        $this->assertNotFalse($faceMenuPosition);
        $this->assertNotFalse($gtkMenuPosition);
        $this->assertLessThan($gtkMenuPosition, $faceMenuPosition);
        $this->assertLessThan($studentMenuPosition, $gtkTodayPosition);
        $this->assertLessThan($studentMenuPosition, strpos($menu, "'text' => 'Pengaturan Presensi'", $gtkMenuPosition));
        $this->assertGreaterThan(strpos($menu, '// PRESENSI'), strpos($menu, "'text' => 'Kehadiran Kelas'"));
        $this->assertLessThan(strpos($menu, '// HOTSPOT MANAGER'), strpos($menu, "'text' => 'Kehadiran Kelas'"));
        $this->assertStringContainsString("'active' => ['admin/absensi', 'admin/absensi/*', 'admin/absensi-siswa*']", $menu);
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

    public function test_presensi_dashboard_is_personally_scoped_while_kiosk_supports_both_types(): void
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
        $this->assertStringContainsString("\$userType = \$this->normalizeUserType(\$request->query('type'));", $controller);
        $this->assertStringContainsString('streamDownload', $controller);
        $this->assertStringContainsString("\$selectedType = \$this->normalizeUserType(\$request->query('type'));", $faceController);
        $this->assertStringContainsString("'typeOptions' => \$this->typeOptions()", $faceController);
        $this->assertStringContainsString('abort_unless($this->canManageAllRegistrations($request->user()), 403);', $faceController);
        $this->assertStringContainsString("'type' => 'siswa'", $kiosk);
        $this->assertStringContainsString('Unduh CSV', $recap);
        $this->assertStringContainsString("->where('group', '!=', 'waktu')", $settingController);
        $this->assertStringContainsString('Jadwal Operasional Kiosk', $settings);
    }

    public function test_gtk_attendance_dashboard_uses_active_population_and_smart_filters(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiController.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/index.blade.php');

        $this->assertStringContainsString('Gtk::active()', $controller);
        $this->assertStringContainsString("'belum' => max(\$totalGtk - \$recorded, 0)", $controller);
        $this->assertStringContainsString("'persentase' => \$totalGtk > 0", $controller);
        $this->assertStringContainsString("->when(\$request->filled('status')", $controller);
        $this->assertStringContainsString("->when(\$request->filled('metode')", $controller);
        $this->assertStringContainsString("->when(\$request->filled('q')", $controller);
        $this->assertStringContainsString('Pusat Presensi GTK', $view);
        $this->assertStringContainsString('Kelengkapan presensi', $view);
        $this->assertStringContainsString('Belum presensi', $view);
        $this->assertStringContainsString("@can('face-registration-admin')", $view);
        $this->assertStringContainsString("@can('create-absensi')", $view);
        $this->assertStringContainsString("@can('edit-absensi')", $view);
        $this->assertStringContainsString('foto_profile_url', $view);
        $this->assertStringContainsString("$('#editStatus').val(record.status)", $view);
    }

    public function test_face_kiosk_uses_server_controlled_operational_windows_for_gtk_and_students(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiController.php');
        $kiosk = file_get_contents($root.'/resources/views/admin/absensi/kiosk.blade.php');
        $settings = file_get_contents($root.'/resources/views/admin/absensi/settings.blade.php');
        $routes = file_get_contents($root.'/routes/web.php');

        $this->assertStringContainsString('AttendanceWindowService $windowService', $controller);
        $this->assertStringContainsString("if (! \$operationalState['is_open'])", $controller);
        $this->assertStringContainsString("\$operationalState['mode'] === 'masuk'", $controller);
        $this->assertStringNotContainsString("\$request->type === 'masuk'", $controller);
        $this->assertStringContainsString("'user_type' => 'required|in:gtk,siswa'", $controller);
        $this->assertStringContainsString("'type' => 'siswa'", $kiosk);
        $this->assertStringContainsString('Mode Otomatis', $kiosk);
        $this->assertStringContainsString('refreshOperationalState', $kiosk);
        $this->assertStringContainsString('operationalCountdown', $kiosk);
        $this->assertStringNotContainsString('type: currentTab', $kiosk);
        $this->assertStringContainsString('Jadwal Operasional Kiosk', $settings);
        $this->assertStringContainsString('settings.operational-schedules.update', $settings);
        $this->assertStringContainsString("name('absensi.kiosk-state')", $routes);
    }

    public function test_experimental_door_face_detect_is_read_only_and_admin_protected(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/AbsensiController.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $menu = file_get_contents($root.'/config/adminlte.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/door-face-detect.blade.php');

        $this->assertStringContainsString('public function doorFaceDetect()', $controller);
        $this->assertStringContainsString("name('absensi.face-detect')", $routes);
        $this->assertStringContainsString("Route::middleware(['can:face-registration-admin'])", $routes);
        $this->assertStringContainsString("'text' => 'Face Detect (Uji)'", $menu);
        $this->assertStringContainsString("['type' => 'gtk', 'verified_only' => 1]", $controller);
        $this->assertStringContainsString("['type' => 'siswa', 'verified_only' => 1]", $controller);
        $this->assertStringContainsString('function normalizeNameForSpeech(name)', $view);
        $this->assertStringContainsString("A: 'a', B: 'be', C: 'ce', D: 'de'", $view);
        $this->assertStringContainsString('speakText(buildGreeting(person.name))', $view);
        $this->assertStringContainsString('confirmations: 3', $view);
        $this->assertStringContainsString('Tidak ada presensi yang dicatat', $view);
        $this->assertStringContainsString('id="voiceSettingsModal"', $view);
        $this->assertStringContainsString("VOICE_SETTINGS_KEY = 'simansa.face-detect.voice.v1'", $view);
        $this->assertStringContainsString("greetingMode: 'time'", $view);
        $this->assertStringContainsString("if (hour >= 4 && hour < 11) return 'pagi'", $view);
        $this->assertStringContainsString("replaceAll('{nama}', spokenName).replaceAll('{waktu}', period)", $view);
        $this->assertStringContainsString('utterance.pitch = settings.pitch', $view);
        $this->assertStringContainsString('utterance.rate = settings.rate', $view);
        $this->assertStringContainsString('utterance.volume = settings.volume', $view);
        $this->assertStringContainsString('id="voiceIntonation"', $view);
        $this->assertStringContainsString('INTONATION_PROFILES', $view);
        $this->assertStringContainsString("enthusiastic: { rate: 1.05, pitchOffset: .14 }", $view);
        $this->assertStringContainsString('function applyIntonationPreset()', $view);
        $this->assertStringContainsString('id="publicAccessModal"', $view);
        $this->assertStringContainsString('$descriptorEndpoints', $view);
        $this->assertStringNotContainsString('absensi.record-face', $view);
        $this->assertStringNotContainsString('alert(error.message)', $view);

        $publicController = file_get_contents($root.'/app/Http/Controllers/PublicFaceDetectController.php');
        $descriptorService = file_get_contents($root.'/app/Services/FaceDescriptorService.php');
        $this->assertStringContainsString("hash_equals(\$expected, \$token)", $publicController);
        $this->assertStringContainsString("'X-Robots-Tag', 'noindex, nofollow, noarchive'", $publicController);
        $this->assertStringContainsString("'Cache-Control', 'no-store, private, max-age=0'", $publicController);
        $this->assertStringContainsString('public function forType(string $userType, bool $verifiedOnly = true)', $descriptorService);
        $this->assertStringContainsString("name('public.face-detect.show')", $routes);
        $this->assertStringContainsString("name('absensi.face-detect.rotate-token')", $routes);
    }

    public function test_python_face_agent_is_isolated_as_a_tokenized_simulation_module(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root.'/routes/web.php');
        $menu = file_get_contents($root.'/config/adminlte.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/PublicFacePythonController.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/face-python.blade.php');
        $agent = file_get_contents($root.'/tools/face-python-agent/agent.py');
        $menuBytes = file_get_contents($root.'/config/adminlte.php', false, null, 0, 3);

        $this->assertStringContainsString("'text' => 'Face Python (Uji)'", $menu);
        $this->assertStringContainsString("name('absensi.face-python')", $routes);
        $this->assertStringContainsString("name('public.face-python.bootstrap')", $routes);
        $this->assertStringContainsString('bearerToken()', $controller);
        $this->assertStringContainsString("'mode' => 'simulation'", $controller);
        $this->assertStringNotContainsString('Absensi::', $controller);
        $this->assertStringContainsString('belum mencatat presensi', $view);
        $this->assertStringContainsString('FaceAnalysis(name="buffalo_l"', $agent);
        $this->assertStringContainsString('class Tracker:', $agent);
        $this->assertStringContainsString('attendance is deliberately not recorded', $agent);
        $this->assertSame('<?p', $menuBytes, 'Konfigurasi menu tidak boleh memiliki BOM yang merusak respons biner/JSON.');
    }

    public function test_face_registration_has_voice_guidance_and_avoids_single_frame_duplicate_blocks(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/FaceRegistrationController.php');
        $view = file_get_contents($root.'/resources/views/admin/absensi/face-register.blade.php');

        $this->assertStringContainsString('DUPLICATE_REQUIRED_CAPTURES = 3', $controller);
        $this->assertStringContainsString("->where('is_verified', true)", $controller);
        $this->assertStringContainsString('matched_captures', $controller);
        $this->assertStringContainsString('function speakGuidance(text)', $view);
        $this->assertStringContainsString("utterance.lang = 'id-ID'", $view);
        $this->assertStringContainsString('async function welcomeRegistrant()', $view);
        $this->assertStringContainsString('Selamat datang, ${selectedUserName}. Silakan melakukan registrasi wajah.', $view);
        $this->assertStringContainsString('class="face-register-page d-none" id="modalRegister"', $view);
        $this->assertStringContainsString("window.location.assign(registrationListUrl);", $view);
        $this->assertStringNotContainsString("$('#modalRegister').modal('show')", $view);
        $this->assertStringContainsString('blinkOpenEarBaseline', $view);
        $this->assertStringContainsString('const closeThreshold = Math.max(0.11, Math.min(0.23, openEar * 0.72));', $view);
        $this->assertStringContainsString('Kedipkan mata secara normal: tutup lalu buka kembali.', $view);
        $this->assertStringContainsString('await video.play().catch(() => null);', $view);
        $this->assertStringContainsString('Menyiapkan kamera dan deteksi wajah...', $view);
        $this->assertStringContainsString('window.setTimeout(detect, 180);', $view);
        $this->assertStringContainsString("FACE_DETECTOR_INPUT_SIZE = window.matchMedia('(max-width: 767.98px)').matches ? 160 : 320", $view);
        $this->assertStringContainsString('async function detectFaceWithWatchdog(video, options)', $view);
        $this->assertStringContainsString('Kamera aktif. Mencari wajah...', $view);
        $this->assertStringContainsString('function playStepCompleteTone()', $view);
        $this->assertStringContainsString('playStepCompleteTone();', $view);
        $this->assertStringContainsString('height: 100dvh', $view);
        $this->assertStringContainsString('Hijab boleh tetap dipakai', $view);
        $this->assertStringContainsString('gigi tidak perlu terlihat', $view);
        $this->assertStringContainsString("smileRatio > 0.34 && smileDelta >= 0.015", $view);
        $this->assertStringNotContainsString('findLiveDuplicateMatch', $view);
        $this->assertStringNotContainsString('loadDuplicateFaceDatabase', $view);
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
