<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassDetailStudentMetadataTest extends TestCase
{
    public function test_academic_menu_groups_class_mutation_and_print_entries_in_that_order(): void
    {
        $menu = file_get_contents(dirname(__DIR__, 2).'/config/adminlte.php');

        $classPosition = strpos($menu, "'text' => 'Manajemen Kelas'");
        $mutationPosition = strpos($menu, "'text' => 'Mutasi Siswa'");
        $printPosition = strpos($menu, "'text' => 'Cetak Dokumen'");
        $matriculationPosition = strpos($menu, "'text' => 'Matrikulasi PPDB'");

        $this->assertNotFalse($classPosition);
        $this->assertNotFalse($mutationPosition);
        $this->assertNotFalse($printPosition);
        $this->assertLessThan($mutationPosition, $classPosition);
        $this->assertLessThan($printPosition, $mutationPosition);
        $this->assertLessThan($matriculationPosition, $printPosition);
    }

    public function test_class_detail_sorts_students_by_name_and_shows_previous_class_above_grade_ten(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/show.blade.php');

        $this->assertStringContainsString(
            "sortBy('nama_lengkap', SORT_NATURAL | SORT_FLAG_CASE)",
            $controller
        );
        $this->assertStringContainsString(
            "->where('tingkat', (int) \$kelas->tingkat - 1)",
            $controller
        );
        $this->assertStringContainsString(
            "->where('tahun_mulai', \$kelas->tahunPelajaran->tahun_mulai - 1)",
            $controller
        );
        $this->assertStringContainsString('@if((int) $kelas->tingkat > 10)', $view);
        $this->assertStringContainsString('Asal kelas:', $view);
        $this->assertStringContainsString("order: [[4, 'asc']]", $view);
    }
}
