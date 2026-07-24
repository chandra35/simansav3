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
}
