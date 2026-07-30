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
        $this->assertStringContainsString("select('siswa.id', 'siswa.nisn'", $service);
        $this->assertStringContainsString("(int) \$filters['simansa_tingkat']", $service);
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

    public function test_apply_streams_staging_and_bulk_inserts_without_loading_all_models(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/RdmSyncController.php'));

        $this->assertStringContainsString('chunkById(500', $service);
        $this->assertStringContainsString("DB::table('nilai_siswa')->insertOrIgnore", $service);
        $this->assertStringContainsString('}, 3);', $service);
        $this->assertStringNotContainsString("\$rows = RdmSyncStaging::where('run_id'", $service);
        $this->assertStringContainsString("Log::error('Apply nilai RDM gagal'", $controller);
        $this->assertStringContainsString('tidak ada nilai yang disimpan sebagian', $controller);
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

    public function test_preview_ui_explains_scope_impact_and_problem_details(): void
    {
        $view = file_get_contents($this->projectPath('resources/views/admin/rdm-sync/index.blade.php'));
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/RdmSyncController.php'));

        $this->assertStringContainsString('Dampak jika Apply ditekan', $view);
        $this->assertStringContainsString('Semester Leger', $view);
        $this->assertStringContainsString('Sampel Nilai', $view);
        $this->assertStringContainsString('Siswa aktif belum ditemukan', $view);
        $this->assertStringContainsString('active_students_without_rdm_sample', $view);
        $this->assertStringContainsString("'sampleRows' => \$sampleRows", $controller);
    }

    public function test_preview_automatically_builds_cohort_semesters_without_rdm_source_inputs(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));
        $view = file_get_contents($this->projectPath('resources/views/admin/rdm-sync/index.blade.php'));
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/RdmSyncController.php'));

        $this->assertStringContainsString('previewCohortSync', $service);
        $this->assertStringContainsString('buildCohortPeriodPlan', $service);
        $this->assertStringContainsString('12 => 5', $service);
        $this->assertStringContainsString("'scope' => 'cohort_legger'", $service);
        $this->assertStringContainsString('Cakupan nilai otomatis', $view);
        $this->assertStringContainsString('Preview Semester 1-', $view);
        $this->assertStringNotContainsString('name="rdm_tingkat_id"', $view);
        $this->assertStringNotContainsString('name="rdm_tahunajaran_id"', $view);
        $this->assertStringContainsString('previewCohortSync($data', $controller);
        $this->assertStringNotContainsString("'rdm_tingkat_id' => ['nullable'", $controller);
    }

    public function test_curriculum_is_detected_per_rdm_record_and_merdeka_does_not_fill_k13_columns(): void
    {
        $service = file_get_contents($this->projectPath('app/Services/RdmSyncService.php'));

        $this->assertStringContainsString('detectCurriculum($rows)', $service);
        $this->assertStringContainsString("str_contains(\$name, 'MERDEKA')", $service);
        $this->assertStringContainsString("\$row->rdm_kurikulum_kode === 'MERDEKA'", $service);
        $this->assertStringContainsString("'nilai_pengetahuan' => \$isMerdeka ? null", $service);
        $this->assertStringContainsString('Preview lama tidak memiliki metadata kurikulum', $service);
    }

    public function test_active_legger_uses_expected_year_and_excludes_graduated_roster(): void
    {
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $index = file_get_contents($this->projectPath('resources/views/admin/nilai/index.blade.php'));
        $semester = file_get_contents($this->projectPath('resources/views/admin/nilai/semester.blade.php'));

        $this->assertStringContainsString('getActiveRosterStudentIds', $controller);
        $this->assertStringContainsString('$semesterConfig[$semester]', $controller);
        $this->assertStringContainsString("'tahun_pelajaran_id' => \$data['tahun_pelajaran_id']", $index);
        $this->assertStringContainsString('d.tingkat =', $semester);
    }

    public function test_score_recap_and_exports_use_actual_period_subjects(): void
    {
        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $semester = file_get_contents($this->projectPath('resources/views/admin/nilai/semester.blade.php'));

        $this->assertStringContainsString('getActualMapelList', $controller);
        $this->assertStringContainsString("->distinct()\n            ->pluck('mata_pelajaran_id')", $controller);
        $this->assertStringContainsString('$mapelsBySemester', $controller);
        $this->assertStringContainsString('$availableMapels = $this->getActualMapelList($tahunIds)', $controller);
        $this->assertStringContainsString('where(function ($query) use ($periods)', $controller);
        $this->assertStringContainsString('mapel terdeteksi', $semester);
        $this->assertStringContainsString('nilai yang benar-benar tersimpan', $semester);
    }

    public function test_large_excel_exports_use_dedicated_batched_cell_cache(): void
    {
        $provider = file_get_contents($this->projectPath('app/Providers/AppServiceProvider.php'));
        $cache = file_get_contents($this->projectPath('config/cache.php'));

        $this->assertStringContainsString("'excel.cache.driver' => 'batch'", $provider);
        $this->assertStringContainsString("'excel.cache.batch.memory_limit' => 1000", $provider);
        $this->assertStringContainsString("'excel.cache.illuminate.store' => 'excel'", $provider);
        $this->assertStringContainsString("'cache.stores.excel' => [", $provider);
        $this->assertStringContainsString("'excel' => [", $cache);
        $this->assertStringContainsString("storage_path('framework/cache/excel')", $cache);

        $controller = file_get_contents($this->projectPath('app/Http/Controllers/Admin/NilaiController.php'));
        $this->assertStringContainsString('getLeggerNilaiLookup', $controller);
        $this->assertStringContainsString('->lazyById(1000)', $controller);
        $this->assertStringNotContainsString("->groupBy(['siswa_id', 'semester', 'mata_pelajaran_id'])", $controller);
    }
}
