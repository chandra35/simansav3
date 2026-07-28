<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassDetailStudentMetadataTest extends TestCase
{
    public function test_academic_menu_starts_with_class_print_and_mutation_entries(): void
    {
        $menu = file_get_contents(dirname(__DIR__, 2).'/config/adminlte.php');

        $academicPosition = strpos($menu, "'text' => 'Akademik'");
        $classPosition = strpos($menu, "'text' => 'Manajemen Kelas'");
        $printPosition = strpos($menu, "'text' => 'Cetak Dokumen'");
        $mutationPosition = strpos($menu, "'text' => 'Mutasi Siswa'");
        $academicYearPosition = strpos($menu, "'text' => 'Tahun Pelajaran'");

        $this->assertNotFalse($academicPosition);
        $this->assertNotFalse($classPosition);
        $this->assertNotFalse($printPosition);
        $this->assertNotFalse($mutationPosition);
        $this->assertNotFalse($academicYearPosition);
        $this->assertLessThan($classPosition, $academicPosition);
        $this->assertLessThan($printPosition, $classPosition);
        $this->assertLessThan($mutationPosition, $printPosition);
        $this->assertLessThan($academicYearPosition, $mutationPosition);
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
