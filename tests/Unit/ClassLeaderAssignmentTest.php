<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassLeaderAssignmentTest extends TestCase
{
    public function test_class_leader_is_selected_from_active_class_enrollments(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString(
            'function assignKetuaKelas(Request $request, Kelas $kelas)',
            $controller
        );
        $this->assertStringContainsString("'ketua_kelas_id' => 'nullable|uuid|exists:siswa,id'", $controller);
        $this->assertStringContainsString("->where('kelas_id', \$kelas->id)", $controller);
        $this->assertStringContainsString("->where('tahun_pelajaran_id', \$kelas->tahun_pelajaran_id)", $controller);
        $this->assertStringContainsString("->where('status', 'aktif')", $controller);
        $this->assertStringContainsString(
            "name('kelas.ketua-kelas')->middleware('permission:edit-kelas')",
            $routes
        );
    }

    public function test_class_leader_metadata_and_student_history_are_exposed_in_ui(): void
    {
        $classView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/show.blade.php');
        $studentView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/show.blade.php');

        $this->assertStringContainsString('id="modalKetuaKelas"', $classView);
        $this->assertStringContainsString('id="formKetuaKelas"', $classView);
        $this->assertStringContainsString('Ketua Kelas', $classView);
        $this->assertStringContainsString('Rekam Didik & Jabatan Kelas', $studentView);
        $this->assertStringContainsString('Riwayat jabatan', $studentView);
        $this->assertStringContainsString('ketua_kelas_mulai_at', $studentView);
        $this->assertStringContainsString('ketua_kelas_selesai_at', $studentView);
    }

    public function test_leadership_is_closed_when_student_leaves_the_class(): void
    {
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/SiswaKelas.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString("&& \$record->status !== 'aktif'", $model);
        $this->assertStringContainsString('$record->ketua_kelas_selesai_at = now();', $model);
        $this->assertGreaterThanOrEqual(
            3,
            substr_count($controller, "'ketua_kelas_selesai_at' =>")
        );
    }

    public function test_assignment_is_written_to_student_activity_history(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString("'activity_type' => 'penetapan_ketua_kelas'", $controller);
        $this->assertStringContainsString("'activity_type' => 'selesai_jabatan_ketua_kelas'", $controller);
        $this->assertStringContainsString("'model_type' => Siswa::class", $controller);
        $this->assertStringContainsString("'model_id' => \$selected->siswa_id", $controller);
    }
}
