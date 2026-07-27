<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentPhotoArchiveArchitectureTest extends TestCase
{
    public function test_archive_is_scoped_to_active_year_classes_and_original_files(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/StudentPhotoArchiveService.php');

        $this->assertStringContainsString('->active()', $service);
        $this->assertStringContainsString("->where('tahun_pelajaran_id', \$activeYear->id)", $service);
        $this->assertStringContainsString("->wherePivot('status', 'aktif')", $service);
        $this->assertStringContainsString("->wherePivot('tahun_pelajaran_id', \$activeYearId)", $service);
        $this->assertStringContainsString("\$zip->addFile(\$entry['source'], \$entry['archive_name'])", $service);
        $this->assertStringContainsString('ZipArchive::CM_STORE', $service);
    }

    public function test_archive_has_preview_chunked_progress_and_private_download(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/StudentPhotoArchiveService.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/CetakController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/cetak/download-foto-siswa.blade.php');

        $this->assertStringContainsString('public const BATCH_SIZE = 12', $service);
        $this->assertStringContainsString("storage_path('app/private/photo-exports/'", $service);
        $this->assertStringContainsString('photoPreview', $controller);
        $this->assertStringContainsString('photoArchiveProcess', $controller);
        $this->assertStringContainsString('Preview Data Foto', $view);
        $this->assertStringContainsString('photo-progress-bar', $view);
        $this->assertStringContainsString('processArchive(token)', $view);
    }

    public function test_feature_is_registered_in_menu_permission_matrix_and_activity_log(): void
    {
        $menu = file_get_contents(dirname(__DIR__, 2).'/config/adminlte.php');
        $permissions = file_get_contents(dirname(__DIR__, 2).'/app/Services/PermissionSyncService.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/CetakController.php');

        $this->assertStringContainsString('Download Foto Siswa', $menu);
        $this->assertStringContainsString('download-foto-kelas', $menu);
        $this->assertStringContainsString('download-foto-kelas', $permissions);
        $this->assertStringContainsString('prepare_student_photo_archive', $controller);
        $this->assertStringContainsString('download_student_photo_archive', $controller);
    }
}
