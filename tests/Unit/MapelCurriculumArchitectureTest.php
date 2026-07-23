<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MapelCurriculumArchitectureTest extends TestCase
{
    public function test_man_template_uses_phase_structure_instead_of_a_b_c_grouping(): void
    {
        $templates = require dirname(__DIR__, 2) . '/config/mapel_man.php';
        $mapels = collect($templates)->flatMap(fn (array $group) => $group['mapel']);

        $this->assertNotEmpty($mapels);
        $this->assertTrue($mapels->every(
            fn (array $mapel) => array_key_exists('struktur_fase_e', $mapel)
                && array_key_exists('struktur_fase_f', $mapel)
                && isset($mapel['alokasi_jp'], $mapel['rumpun'])
        ));

        $this->assertContains('wajib_umum', $mapels->pluck('struktur_fase_e')->filter()->all());
        $this->assertContains('pilihan', $mapels->pluck('struktur_fase_f')->filter()->all());
        $this->assertNotContains('peminatan', $mapels->pluck('struktur_fase_f')->filter()->all());
    }

    public function test_rdm_mapping_remains_bound_to_stable_mapel_id(): void
    {
        $model = file_get_contents(dirname(__DIR__, 2) . '/app/Models/RdmMapelMapping.php');
        $migration = file_get_contents(
            dirname(__DIR__, 2) . '/database/migrations/2026_07_23_210000_align_mata_pelajaran_with_kma_1503.php'
        );

        $this->assertStringContainsString("'mata_pelajaran_id'", $model);
        $this->assertStringNotContainsString("update(['mata_pelajaran_id'", $migration);
        $this->assertStringNotContainsString("delete()", $migration);
    }

    public function test_schedule_options_are_scoped_by_class_curriculum_and_phase(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 2) . '/app/Http/Controllers/Admin/JadwalPelajaranController.php'
        );

        $this->assertStringContainsString("where('kurikulum_id', \$kelas->kurikulum_id)", $controller);
        $this->assertStringContainsString("where('is_schedulable', true)", $controller);
        $this->assertStringContainsString("whereNotNull(\$faseColumn)", $controller);
    }
}
