<?php

namespace Tests\Unit;

use App\Services\JadwalAliasMappingService;
use PHPUnit\Framework\TestCase;

class JadwalAliasMappingArchitectureTest extends TestCase
{
    public function test_reference_contains_all_excel_teacher_and_subject_codes(): void
    {
        $reference = require dirname(__DIR__, 2).'/config/jadwal_reference_2026.php';

        $this->assertCount(90, $reference['guru']);
        $this->assertCount(26, $reference['mapel']);
        $this->assertCount(90, array_unique(array_column($reference['guru'], 'code')));
        $this->assertSame(range('A', 'Z'), array_column($reference['mapel'], 'code'));
        $this->assertSame('M-QH', collect($reference['mapel'])->firstWhere('code', 'A')['canonical_code']);
        $this->assertSame('M-BK', collect($reference['mapel'])->firstWhere('code', 'Z')['canonical_code']);
    }

    public function test_gunawan_susanto_and_santoso_have_explicit_distinct_hints(): void
    {
        $reference = require dirname(__DIR__, 2).'/config/jadwal_reference_2026.php';
        $byCode = collect($reference['guru'])->keyBy('code');

        $this->assertSame('Gunawan Susanto', $byCode['1']['canonical_hint']);
        $this->assertSame('kepala_madrasah', $byCode['1']['context']);
        $this->assertSame('Gunawan Santoso', $byCode['21']['canonical_hint']);
        $this->assertSame('guru_fikih', $byCode['21']['context']);
    }

    public function test_name_normalizer_removes_titles_without_collapsing_distinct_people(): void
    {
        $service = new JadwalAliasMappingService();

        $this->assertSame('gunawan santoso', $service->normalizePersonName('GUNAWAN SANTOSO S.Ag. M.Pd.I'));
        $this->assertSame('gunawan susanto', $service->normalizePersonName('Hi. Gunawan Susanto, S.Pd, M.Pd'));
        $this->assertSame('m januar', $service->normalizePersonName('M. Januar, S.Pd'));
    }

    public function test_schedule_allows_multiple_subjects_per_day_but_keeps_same_time_conflict(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');

        $this->assertStringNotContainsString('Satu guru hanya boleh mengajar satu mapel per hari', $controller);
        $this->assertStringContainsString("->where('jam_ke', \$validated['jam_ke'])", $controller);
        $this->assertStringContainsString('Guru sudah mengajar di {$kelasLain} pada jam ini.', $controller);
    }

    public function test_mapping_changes_are_written_to_the_activity_log(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalMappingController.php');

        $this->assertStringContainsString("activity('jadwal-mapping')", $controller);
    }

    public function test_wakakur_code_is_kept_separate_from_internal_subject_code(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/JadwalAliasMappingService.php');
        $schedule = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/JadwalPelajaranController.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_08_07_030000_add_wakakur_schedule_code_to_mata_pelajaran.php');

        $this->assertStringContainsString("->where('kode_mapel', \$row['canonical_code'])", $service);
        $this->assertStringContainsString("->where('kode_jadwal', \$row['code'])", $service);
        $this->assertStringContainsString("'kode_jadwal' => \$row['code']", $service);
        $this->assertStringContainsString('kode_tampil_jadwal', $schedule);
        $this->assertStringContainsString("string('kode_jadwal', 20)", $migration);
    }
}
