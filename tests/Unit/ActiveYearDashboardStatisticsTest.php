<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ActiveYearDashboardStatisticsTest extends TestCase
{
    public function test_dashboard_counts_students_from_active_academic_year_and_shows_gtk(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/DashboardController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/dashboard.blade.php');

        $this->assertStringContainsString("whereHas('kelasTahunAktif')", $controller);
        $this->assertStringContainsString("'total_siswa' => (clone \$siswaTahunAktif)->count()", $controller);
        $this->assertStringContainsString("'siswa_aktif' => (clone \$siswaTahunAktif)", $controller);
        $this->assertStringContainsString("'total_gtk' => Gtk::count()", $controller);
        $this->assertStringNotContainsString("'total_admin'", $controller);

        $this->assertStringContainsString('Jumlah GTK', $view);
        $this->assertStringContainsString('Sudah Aktivasi', $view);
        $this->assertStringContainsString("route('admin.gtk.index')", $view);
        $this->assertStringContainsString('Siswa pada tahun aktif', $view);
        $this->assertStringNotContainsString('total_admin', $view);
    }

    public function test_student_statistics_always_starts_from_active_year_roster(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaStatisticsController.php');

        $this->assertStringContainsString("if (! \$activeYear)", $controller);
        $this->assertStringContainsString("whereHas('kelasTahunAktif'", $controller);
        $this->assertStringContainsString("->where('kelas.tingkat', \$tingkat)", $controller);
        $this->assertStringContainsString("->where('kelas.id', \$kelasId)", $controller);
    }
}
