<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassStudentTransferTest extends TestCase
{
    public function test_transfer_route_and_controller_are_permission_protected(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString("name('kelas.siswa.transfer')->middleware('permission:transfer-siswa-kelas')", $routes);
        $this->assertStringContainsString("authorize('transfer-siswa-kelas')", $controller);
        $this->assertStringContainsString('function transferSiswa(', $controller);
    }

    public function test_transfer_keeps_history_and_validates_academic_context_and_capacity(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString("'status' => 'keluar'", $controller);
        $this->assertStringContainsString("'tanggal_keluar' => \$transferDate", $controller);
        $this->assertStringContainsString("'status' => 'aktif'", $controller);
        $this->assertStringContainsString('tahun_pelajaran_id !== $sourceClass->tahun_pelajaran_id', $controller);
        $this->assertStringContainsString('(int) $targetClass->tingkat !== (int) $sourceClass->tingkat', $controller);
        $this->assertStringContainsString('$targetCount >= $targetClass->kapasitas', $controller);
        $this->assertStringContainsString("'kelas_saat_ini_id' => \$targetClass->id", $controller);
        $this->assertStringContainsString("'pelaksana_id' => Auth::id()", $controller);
    }

    public function test_transfer_permission_is_in_matrix_and_initially_assigned_only_to_admin_roles(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/PermissionSyncService.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_07_23_090000_register_transfer_siswa_kelas_permission.php');

        $this->assertStringContainsString("'transfer-siswa-kelas'", $service);
        $this->assertStringContainsString("whereIn('name', ['Super Admin', 'Admin'])", $migration);
        $this->assertStringNotContainsString("['Super Admin', 'Admin', 'Operator']", $migration);
    }

    public function test_class_detail_has_compact_transfer_action_and_modal(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/show.blade.php');

        $this->assertStringContainsString("@can('transfer-siswa-kelas')", $view);
        $this->assertStringContainsString('btn-transfer-siswa', $view);
        $this->assertStringContainsString('modalTransferSiswa', $view);
        $this->assertStringContainsString('Riwayat rombel asal tetap disimpan', $view);
        $this->assertStringContainsString('Memperbarui rombel aktif dan menyimpan riwayat perpindahan', $view);
    }
}
