<?php

namespace Tests\Unit;

use App\Services\NisLokalService;
use PHPUnit\Framework\TestCase;

class NisLokalArchitectureTest extends TestCase
{
    public function test_nis_format_resets_sequence_by_entry_year(): void
    {
        $service = new NisLokalService();

        $this->assertSame('131118720001240078', $service->formatNis('131118720001', 2024, 78));
        $this->assertSame('131118720001260001', $service->formatNis('131118720001', 2026, 1));
    }

    public function test_smart_name_matching_accepts_abbreviated_names(): void
    {
        $service = new NisLokalService();

        $this->assertGreaterThanOrEqual(85, $service->nameSimilarityScore(
            'M RIZKY',
            'MUHAMMAD RIZKY'
        ));
        $this->assertGreaterThanOrEqual(85, $service->nameSimilarityScore(
            'AHMAD F',
            'AHMAD FAUZAN'
        ));
    }

    public function test_class_order_uses_numeric_rombel_suffix(): void
    {
        $service = new NisLokalService();

        $this->assertSame(1, $service->classOrderNumber('X-1'));
        $this->assertSame(13, $service->classOrderNumber('X-13'));
        $this->assertLessThan(
            $service->classOrderNumber('X-10'),
            $service->classOrderNumber('X-2')
        );
    }

    public function test_feature_has_preview_confirmation_and_school_enrichment_guards(): void
    {
        $root = dirname(__DIR__, 2);
        $service = file_get_contents($root.'/app/Services/NisLokalService.php');
        $view = file_get_contents($root.'/resources/views/admin/nis-lokal/index.blade.php');
        $settings = file_get_contents($root.'/resources/views/admin/settings/edit.blade.php');
        $migration = file_get_contents($root.'/database/migrations/2026_07_28_030000_add_nis_lokal_support.php');

        $this->assertStringContainsString('lockForUpdate()', $service);
        $this->assertStringContainsString("whereIn('sk.tingkat', [11, 12])", $service);
        $this->assertStringContainsString('nomor_urut_absen', $service);
        $this->assertStringContainsString('Live Preview Update NIS Lokal', $view);
        $this->assertStringContainsString('xhr.upload.onprogress', $view);
        $this->assertStringContainsString('btnConfirmGenerator', $view);
        $this->assertStringContainsString('btnFetchSchoolData', $settings);
        $this->assertStringContainsString('siswa_nis_lokal_tahun_urutan_unique', $migration);
        $this->assertStringContainsString('manage-nis-lokal', $migration);
    }

    public function test_import_confirmation_uses_bulk_validation_and_one_update_statement(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/NisLokalService.php');
        preg_match(
            '/public function confirmImport\(string \$token\): array.*?public function formatNis/s',
            $service,
            $methodMatch
        );
        $confirmImport = $methodMatch[0] ?? '';

        $this->assertNotSame('', $confirmImport);
        $this->assertStringContainsString('private function bulkUpdateImportedStudents(Collection $rows): void', $service);
        $this->assertStringContainsString("'UPDATE `siswa` SET '", $service);
        $this->assertStringContainsString("->whereIn('siswa_id', \$targetIds)", $confirmImport);
        $this->assertStringContainsString("->orWhereIn('nis_lokal', \$inputNis)", $confirmImport);
        $this->assertStringContainsString('$this->bulkUpdateImportedStudents($changedRows);', $confirmImport);
        $this->assertStringNotContainsString("Siswa::query()->lockForUpdate()->findOrFail(\$row['student_id'])", $confirmImport);
        $this->assertStringNotContainsString("if (\$student->nis_lokal !== \$row['input_nis']) {\n                    \$student->forceFill", $confirmImport);
    }
}
