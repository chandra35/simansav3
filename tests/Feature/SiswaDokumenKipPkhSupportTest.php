<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiswaDokumenKipPkhSupportTest extends TestCase
{
    public function test_student_document_portal_supports_kip_and_kks_pkh_numbers_and_uploads(): void
    {
        $view = file_get_contents(resource_path('views/siswa/dokumen/index.blade.php'));
        $dashboard = file_get_contents(resource_path('views/siswa/dashboard.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Siswa/DokumenController.php'));
        $siswa = file_get_contents(app_path('Models/Siswa.php'));
        $pip = file_get_contents(app_path('Http/Controllers/Admin/SiswaPipController.php'));
        $migration = file_get_contents(database_path('migrations/2026_08_24_014500_add_kip_and_kks_pkh_document_support.php'));

        $this->assertStringContainsString('id="nomor_kip"', $view);
        $this->assertStringContainsString('id="nomor_pkh"', $view);
        $this->assertStringContainsString("showUploadModal('pkh', 'Kartu KKS / PKH')", $view);
        $this->assertStringContainsString('directUploadLabels', $view);
        $this->assertStringContainsString("['upload' => 'pkh']", $view);
        $this->assertStringContainsString('$dokumenQuickLink', $dashboard);
        $this->assertStringContainsString("['upload' => 'ijazah_smp']", $dashboard);
        $this->assertStringContainsString('Isi & Upload KKS/PKH', $dashboard);
        $this->assertStringContainsString("'jenis_dokumen' => 'required|in:kk,ijazah_smp,kip,pkh,sktm,lainnya'", $controller);
        $this->assertStringContainsString("'kip' => 'nomor_kip'", $controller);
        $this->assertStringContainsString("'pkh' => 'nomor_pkh'", $controller);
        $this->assertStringContainsString("'nomor_kip'", $siswa);
        $this->assertStringContainsString('KEYWORDS_PKH', $pip);
        $this->assertStringContainsString("ENUM('kk', 'ijazah_smp', 'kip', 'pkh', 'sktm', 'lainnya')", $migration);
    }
}
