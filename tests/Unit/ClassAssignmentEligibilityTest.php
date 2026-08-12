<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ClassAssignmentEligibilityTest extends TestCase
{
    public function test_assignment_only_uses_active_students_without_an_active_class(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString('private function availableStudentsQuery(Kelas $kelas): Builder', $controller);
        $this->assertGreaterThanOrEqual(2, substr_count($controller, '$this->availableStudentsQuery($kelas)'));
        $this->assertStringContainsString("->where('status_siswa', 'aktif')", $controller);
        $this->assertStringContainsString("->whereDoesntHave('siswaKelasRecords'", $controller);
        $this->assertStringContainsString("->where('siswa_kelas.status', 'aktif')", $controller);
        $this->assertStringContainsString("->whereNull('siswa_kelas.kelas_id')", $controller);
        $this->assertStringContainsString("Siswa tidak aktif (termasuk lulus atau mutasi keluar)", $controller);
    }

    public function test_historical_enrollment_is_reused_and_database_errors_are_not_exposed(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString('private function placeStudentInClass(', $controller);
        $this->assertStringContainsString('SiswaKelas::withTrashed()', $controller);
        $this->assertStringContainsString("'tanggal_keluar' => null", $controller);
        $this->assertStringContainsString("'status' => 'aktif'", $controller);
        $this->assertStringContainsString('$enrollment->restore();', $controller);
        $this->assertStringContainsString('$historicalTarget->id !== $activeRecord->id', $controller);
        $this->assertStringContainsString('Placeholder ditutup saat riwayat rombel diaktifkan kembali.', $controller);
        $this->assertStringContainsString('Data mungkin berubah di proses lain', $controller);
        $this->assertStringNotContainsString("'Gagal menambahkan siswa: ' . \$e->getMessage()", $controller);
        $this->assertStringNotContainsString("'Gagal memproses bulk import: ' . \$e->getMessage()", $controller);
    }
}
