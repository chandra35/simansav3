<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassStudentPresenceVerificationTest extends TestCase
{
    public function test_presence_verification_is_scoped_to_active_class_enrollment(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString('function toggleKeberadaanSiswa(Kelas $kelas, Siswa $siswa)', $controller);
        $this->assertStringContainsString("authorize('edit-kelas')", $controller);
        $this->assertStringContainsString("->where('kelas_id', \$kelas->id)", $controller);
        $this->assertStringContainsString("->where('tahun_pelajaran_id', \$kelas->tahun_pelajaran_id)", $controller);
        $this->assertStringContainsString("->where('status', 'aktif')", $controller);
        $this->assertStringContainsString("'keberadaan_diverifikasi_at' => \$wasVerified ? null : now()", $controller);
        $this->assertStringContainsString("'keberadaan_diverifikasi_by' => \$wasVerified ? null : Auth::id()", $controller);
    }

    public function test_class_detail_exposes_presence_toggle_and_reload_after_transfer(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/show.blade.php');

        $this->assertStringContainsString("name('kelas.siswa.toggle-keberadaan')->middleware('permission:edit-kelas')", $routes);
        $this->assertStringContainsString('class-presence-toggle', $view);
        $this->assertStringContainsString('Belum dicek', $view);
        $this->assertStringNotContainsString('@php($keberadaanTerverifikasi', $view);
        $this->assertStringContainsString("confirmButtonText: 'Selesai'", $view);
        $this->assertStringContainsString('window.location.reload()', $view);
        $this->assertStringNotContainsString("confirmButtonText:'Lihat rombel tujuan'", $view);
    }

    public function test_presence_is_reset_when_student_enters_another_class(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertGreaterThanOrEqual(
            3,
            substr_count($controller, "'keberadaan_diverifikasi_at' => null")
        );
        $this->assertGreaterThanOrEqual(
            3,
            substr_count($controller, "'keberadaan_diverifikasi_by' => null")
        );
    }
}
