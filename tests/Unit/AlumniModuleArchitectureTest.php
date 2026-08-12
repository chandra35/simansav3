<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AlumniModuleArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_alumni_routes_menu_and_views_are_registered(): void
    {
        $routes = file_get_contents($this->root.'/routes/web.php');
        $menu = file_get_contents($this->root.'/config/adminlte.php');
        $index = file_get_contents($this->root.'/resources/views/admin/alumni/index.blade.php');
        $show = file_get_contents($this->root.'/resources/views/admin/alumni/show.blade.php');

        $this->assertStringContainsString("->name('alumni.index')", $routes);
        $this->assertStringContainsString("->name('alumni.show')", $routes);
        $this->assertStringContainsString("'text' => 'Alumni'", $menu);
        $this->assertStringContainsString('Statistik Alumni dari Tahun ke Tahun', $index);
        $this->assertStringContainsString('Histori Kelas', $show);
        $this->assertStringContainsString('Riwayat Setelah Lulus', $show);
    }

    public function test_graduation_finalization_always_archives_students(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/KenaikanKelasController.php');
        $migration = file_get_contents($this->root.'/database/migrations/2026_08_12_120000_archive_graduated_students_as_alumni.php');

        $this->assertStringContainsString("'status_siswa'      => 'lulus'", $controller);
        $this->assertStringContainsString("'kelas_saat_ini_id' => null", $controller);
        $this->assertStringNotContainsString('tandai_siswa_lulus', $controller);
        $this->assertStringContainsString("->where('siswa_kelas.status', 'lulus')", $migration);
    }
}
