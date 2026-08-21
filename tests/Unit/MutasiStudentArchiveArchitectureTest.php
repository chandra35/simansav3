<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MutasiStudentArchiveArchitectureTest extends TestCase
{
    public function test_mutation_detail_loads_archived_student_relations_and_renders_the_history_modal(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/MutasiSiswaController.php');
        $view = file_get_contents($root.'/resources/views/admin/mutasi-siswa/show.blade.php');
        $modal = file_get_contents($root.'/resources/views/admin/mutasi-siswa/partials/student-history-modal.blade.php');

        $this->assertStringContainsString("'siswa.ortu.provinsi'", $controller);
        $this->assertStringContainsString("'siswa.siswaKelasRecords.tahunPelajaran'", $controller);
        $this->assertStringContainsString("'siswa.mutasiHistory.verifikator'", $controller);
        $this->assertStringContainsString("'siswa.dokumen.approvedBy'", $controller);
        $this->assertStringContainsString('data-target="#modalArsipSiswa"', $view);
        $this->assertStringContainsString('Riwayat Rombel', $modal);
        $this->assertStringContainsString('Riwayat Mutasi', $modal);
        $this->assertStringContainsString('Keluarga & Alamat', $modal);
        $this->assertStringContainsString('Dokumen Tersimpan', $modal);
        $this->assertStringContainsString("route('siswa.dokumen.preview'", $modal);
    }
}
