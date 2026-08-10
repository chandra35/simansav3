<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class JadwalOpeningActivityArchitectureTest extends TestCase
{
    public function test_schedule_generator_supports_day_specific_opening_activities(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalJamConfigController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/jadwal-jam-config/index.blade.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_08_10_090000_add_opening_activities_to_tahun_pelajaran_table.php');

        $this->assertStringContainsString("'upacara_senin_aktif'", $controller);
        $this->assertStringContainsString("'religi_harian_aktif'", $controller);
        $this->assertStringContainsString("'upacara', 'Upacara Bendera'", $controller);
        $this->assertStringContainsString("'khusus', 'Religi'", $controller);
        $this->assertStringContainsString('jadwal_jam_pulang', $controller);

        $this->assertStringContainsString('id="upacaraActive"', $view);
        $this->assertStringContainsString('id="religiActive"', $view);
        $this->assertStringContainsString("renderDay('Senin'", $view);
        $this->assertStringContainsString("renderDay('Selain Senin'", $view);

        $this->assertStringContainsString("boolean('upacara_senin_aktif')->default(true)", $migration);
        $this->assertStringContainsString("unsignedSmallInteger('durasi_upacara_senin')->default(30)", $migration);
        $this->assertStringContainsString("unsignedSmallInteger('durasi_religi_harian')->default(15)", $migration);
    }
}
