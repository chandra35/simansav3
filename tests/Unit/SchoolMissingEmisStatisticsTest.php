<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SchoolMissingEmisStatisticsTest extends TestCase
{
    public function test_school_statistics_exposes_students_not_yet_marked_in_emis(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaStatisticsController.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString('missing_emis_count', $controller);
        $this->assertStringContainsString('studentsMissingEmis', $controller);
        $this->assertStringContainsString("whereNull('siswa.emis_registered')", $controller);
        $this->assertStringContainsString('school-missing-emis', $routes);
    }

    public function test_school_statistics_has_a_responsive_detail_modal(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/statistics.blade.php');

        $this->assertStringContainsString('Belum Ada di EMIS', $view);
        $this->assertStringContainsString('schoolMissingEmisModal', $view);
        $this->assertStringContainsString('simansa-emis-student-grid', $view);
        $this->assertStringContainsString('NPSN', $view);
        $this->assertStringContainsString('NSM', $view);
    }
}
