<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NilaiRankingArchitectureTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_upload_template_and_parser_use_actual_header_subjects(): void
    {
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $upload = file_get_contents($this->projectPath('resources/views/admin/nilai/upload.blade.php'));

        $this->assertStringContainsString('getTemplateMapelList', $controller);
        $this->assertStringContainsString('array_slice($header, $kolomNilaiMulai)', $controller);
        $this->assertStringContainsString('Header mapel tidak ditemukan mulai kolom F', $controller);
        $this->assertStringContainsString('Download Template Sesuai Pilihan', $upload);
        $this->assertStringContainsString('tahun_pelajaran_id=${tahunFound.id}', $upload);
        $this->assertStringNotContainsString('18 Mapel', $upload);
        $this->assertStringNotContainsString('20 Mapel', $upload);
    }

    public function test_ranking_supports_semester_cumulative_class_grade_and_export(): void
    {
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $routes = file_get_contents($this->projectPath('routes/web.php'));
        $view = file_get_contents($this->projectPath('resources/views/admin/nilai/ranking.blade.php'));

        $this->assertStringContainsString('buildRankingData', $controller);
        $this->assertStringContainsString('competitionRanks', $controller);
        $this->assertStringContainsString('AVG(nilai) as rata_rata', $controller);
        $this->assertStringContainsString('$valuesForRanking->avg()', $controller);
        $this->assertStringContainsString('expectedMapelCounts', $controller);
        $this->assertStringContainsString('public function exportRanking', $controller);
        $this->assertStringContainsString("name('nilai.ranking')", $routes);
        $this->assertStringContainsString("name('nilai.ranking-export')", $routes);
        $this->assertStringContainsString('Rank Rombel', $view);
        $this->assertStringContainsString('Rank Tingkat', $view);
        $this->assertStringContainsString('Akumulasi seluruh semester', $view);
        $this->assertStringContainsString('Export Excel', $view);
    }

    public function test_rdm_sync_and_database_keep_score_natural_key_unique(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));
        $migration = file_get_contents($this->projectPath('database/migrations/2026_01_30_100000_create_nilai_siswa_table.php'));

        $this->assertStringContainsString("'conflict_existing'", $service);
        $this->assertStringContainsString("where('apply_action', 'insert')", $service);
        $this->assertStringContainsString("insertOrIgnore", $service);
        $this->assertStringContainsString(
            "\$table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_pelajaran_id', 'semester'], 'nilai_unique')",
            $migration
        );
    }
}
