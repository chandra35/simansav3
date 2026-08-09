<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentPopulationScopeTest extends TestCase
{
    public function test_student_management_defaults_to_the_active_year_roster(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');

        $this->assertStringContainsString("input('population', 'active_year')", $controller);
        $this->assertStringContainsString("->where('siswa.status_siswa', 'aktif')", $controller);
        $this->assertStringContainsString("->whereHas('kelasTahunAktif')", $controller);
        $this->assertGreaterThanOrEqual(4, substr_count($controller, 'applyPopulationScope('));
    }

    public function test_archived_and_unassigned_students_remain_accessible(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/index.blade.php');

        $this->assertStringContainsString('value="unassigned"', $view);
        $this->assertStringContainsString('value="graduated"', $view);
        $this->assertStringContainsString('value="transferred_out"', $view);
        $this->assertStringContainsString('value="all"', $view);
    }

    public function test_all_student_exports_use_a_single_lightweight_sheet(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/index.blade.php');

        $this->assertStringContainsString('$isLoginDrilldown', $controller);
        $this->assertStringContainsString('belum-pernah-login', $controller);
        $this->assertStringContainsString('Excel::download(new SiswaExport($rows)', $controller);
        $this->assertStringNotContainsString('SiswaPerRombelExport', $controller);
        $this->assertStringContainsString("route('admin.siswa.export', \$contextQuery ?? [])", $view);
    }
}
