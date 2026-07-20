<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ActiveAcademicYearUiTest extends TestCase
{
    public function test_student_class_filter_is_scoped_to_the_active_academic_year(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');
        $student = file_get_contents(dirname(__DIR__, 2).'/app/Models/Siswa.php');

        $this->assertStringContainsString("->where('tahun_pelajaran_id', \$activeYear->id)", $controller);
        $this->assertStringContainsString('kelasTahunAktif', $controller);
        $this->assertStringContainsString("->where('kelas.is_active', true)", $student);
        $this->assertStringContainsString("TahunPelajaran::query()->active()->select('id')", $student);
        $this->assertStringContainsString("whereColumn('siswa_kelas.tahun_pelajaran_id', 'kelas.tahun_pelajaran_id')", $student);
    }

    public function test_both_navbar_layouts_show_the_active_academic_year_before_the_clock(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/vendor/adminlte/partials/navbar/';

        foreach (['navbar.blade.php', 'navbar-layout-topnav.blade.php'] as $view) {
            $navbar = file_get_contents($root.$view);
            $yearPosition = strpos($navbar, 'simansa-navbar-academic-year');
            $clockPosition = strpos($navbar, 'simansa-navbar-live');

            $this->assertNotFalse($yearPosition);
            $this->assertNotFalse($clockPosition);
            $this->assertLessThan($clockPosition, $yearPosition);
            $this->assertStringContainsString('navbarActiveAcademicYear', $navbar);
        }
    }
}
