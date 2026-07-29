<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentGraduationModuleAccessTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_both_student_modules_use_one_access_service_for_menu_page_and_dashboard(): void
    {
        $service = file_get_contents($this->root.'/app/Services/StudentGraduationAccessService.php');
        $graduationController = file_get_contents($this->root.'/app/Http/Controllers/Siswa/PengumumanKelulusanController.php');
        $lulusanController = file_get_contents($this->root.'/app/Http/Controllers/Siswa/LulusanController.php');
        $dashboardController = file_get_contents($this->root.'/app/Http/Controllers/Siswa/DashboardController.php');
        $adminLte = file_get_contents($this->root.'/config/adminlte.php');

        $this->assertStringContainsString('resolveAnnouncementEnrollment', $service);
        $this->assertStringContainsString('graduation_announcement_tahun_pelajaran_id', $service);
        $this->assertStringContainsString('PengumumanKelulusan::query()', $service);
        $this->assertStringContainsString('resolveLulusanEnrollment', $service);
        $this->assertStringContainsString('lulusan_data_starts_at', $service);
        $this->assertStringContainsString('lulusan_data_ends_at', $service);
        $this->assertStringContainsString('resolveAnnouncementEnrollment($siswa)', $graduationController);
        $this->assertStringContainsString('resolveLulusanEnrollment($siswa)', $lulusanController);
        $this->assertStringContainsString('resolveAnnouncementEnrollment($siswa)', $dashboardController);
        $this->assertStringContainsString('GraduationAnnouncementMenuFilter::class', $adminLte);
        $this->assertStringContainsString('LulusanMenuFilter::class', $adminLte);
    }

    public function test_admin_can_configure_cohort_and_period_for_both_modules(): void
    {
        $graduationController = file_get_contents($this->root.'/app/Http/Controllers/Admin/PengumumanKelulusanController.php');
        $lulusanController = file_get_contents($this->root.'/app/Http/Controllers/Admin/LulusanController.php');
        $lulusanView = file_get_contents($this->root.'/resources/views/admin/lulusan/index.blade.php');
        $routes = file_get_contents($this->root.'/routes/web.php');

        $this->assertStringContainsString("'graduation_announcement_tahun_pelajaran_id' => \$tahunAktif->id", $graduationController);
        $this->assertStringContainsString('function updateStudentAccess(Request $request)', $lulusanController);
        $this->assertStringContainsString("'lulusan_data_tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id']", $lulusanController);
        $this->assertStringContainsString('id="lulusan_data_starts_at"', $lulusanView);
        $this->assertStringContainsString('id="lulusan_data_ends_at"', $lulusanView);
        $this->assertStringContainsString("name('lulusan.student-access')", $routes);
    }

    public function test_database_settings_cover_cohort_and_timing(): void
    {
        $migration = file_get_contents($this->root.'/database/migrations/2026_07_29_130000_add_student_graduation_access_periods_to_app_settings.php');
        $model = file_get_contents($this->root.'/app/Models/AppSetting.php');

        foreach ([
            'graduation_announcement_tahun_pelajaran_id',
            'lulusan_data_enabled',
            'lulusan_data_starts_at',
            'lulusan_data_ends_at',
            'lulusan_data_tahun_pelajaran_id',
        ] as $column) {
            $this->assertStringContainsString($column, $migration);
            $this->assertStringContainsString("'{$column}'", $model);
        }
    }

    public function test_alumni_keep_access_to_their_class_twelve_history(): void
    {
        $service = file_get_contents($this->root.'/app/Services/StudentGraduationAccessService.php');

        $this->assertStringContainsString("in_array(\$siswa->status_siswa, ['lulus', 'alumni'], true)", $service);
        $this->assertStringContainsString('return $this->latestClass12History($siswa->id);', $service);
    }
}
