<?php

namespace Tests\Feature;

use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenStorageFallbackTest extends TestCase
{
    public function test_student_directory_is_checked_before_a_document_is_stored(): void
    {
        Storage::fake('dokumen');

        $this->assertTrue(StorageHelper::ensureDokumenDirectoryWritable('dokumen', '0123456789'));
        $this->assertSame([], Storage::disk('dokumen')->allFiles('0123456789'));
    }

    public function test_upload_controller_keeps_existing_document_until_new_file_is_stored(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Siswa/DokumenController.php'));

        $this->assertStringContainsString('getWritableDokumenDisk($nisn)', $controller);
        $this->assertStringContainsString('if ($existing) {', $controller);
        $this->assertStringContainsString('$existing->update($documentData);', $controller);
        $this->assertStringContainsString('if ($oldLocation) {', $controller);
    }
}
