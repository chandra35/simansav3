<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RdmLeggerSyncArchitectureTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_sync_is_scoped_to_active_simansa_roster_and_exact_decrypted_nisn(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));

        $this->assertStringContainsString("whereHas('kelasAktif'", $service);
        $this->assertStringContainsString('decryptValues(', $service);
        $this->assertStringContainsString('targetByNisn', $service);
        $this->assertStringNotContainsString("'NIS:'", $service);
    }

    public function test_k13_values_are_paired_and_existing_conflicts_are_not_applied(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));

        $this->assertStringContainsString("firstWhere('rdm_jenisnilai_id', 1)", $service);
        $this->assertStringContainsString("firstWhere('rdm_jenisnilai_id', 2)", $service);
        $this->assertStringContainsString("'conflict_existing'", $service);
        $this->assertStringContainsString("where('apply_action', 'insert')", $service);
    }

    public function test_legger_supports_optional_semester_six(): void
    {
        $model = file_get_contents($this->projectPath('app/Models/NilaiSiswa.php'));
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $view = file_get_contents($this->projectPath('resources/views/admin/nilai/export-legger.blade.php'));

        $this->assertStringContainsString("6 => 'Semester 6 - Kelas XII'", $model);
        $this->assertStringContainsString("'Sem 6 (XII-2)'", $controller);
        $this->assertStringContainsString('include_semester_6', $view);
    }
}
