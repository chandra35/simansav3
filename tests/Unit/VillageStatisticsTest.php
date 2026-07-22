<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VillageStatisticsTest extends TestCase
{
    public function test_student_statistics_exposes_village_distribution_and_drilldown(): void
    {
        $statisticsController = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaStatisticsController.php');
        $studentController = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/statistics.blade.php');

        $this->assertStringContainsString("addressSpreadByLevel(clone \$baseQuery, 'village')", $statisticsController);
        $this->assertStringContainsString('indonesia_villages as siswa_village', $statisticsController);
        $this->assertStringContainsString('indonesia_villages as ortu_village', $statisticsController);
        $this->assertStringContainsString('addressVillageChart', $view);
        $this->assertStringContainsString("address_scope: 'village'", $view);

        $this->assertStringContainsString("\$request->address_scope === 'village'", $studentController);
        $this->assertStringContainsString("'district_name'", $studentController);
        $this->assertStringContainsString("'city_name'", $studentController);
        $this->assertStringContainsString("'village' => 'Kelurahan / Desa'", $studentController);
    }
}
