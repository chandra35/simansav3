<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UiDesignConsistencyTest extends TestCase
{
    private function file(string $relative): string
    {
        return file_get_contents(dirname(__DIR__, 2).'/'.$relative);
    }

    public function test_documentation_requires_the_data_siswa_page_structure(): void
    {
        foreach (['MAN1METRO.md', 'docs/UI_DESIGN_PRINCIPLES_UPDATED.md'] as $file) {
            $contents = $this->file($file);

            $this->assertStringContainsString('card bg-gradient-primary text-white', $contents);
            $this->assertStringContainsString('card-outline card-primary', $contents);
            $this->assertStringContainsString('content_header', $contents);
            $this->assertStringContainsString('breadcrumb', $contents);
        }
    }

    public function test_requested_operational_pages_use_one_continuous_hero(): void
    {
        $views = [
            'resources/views/admin/gtk/wali/siswa/index.blade.php',
            'resources/views/admin/gtk/wali/absensi/index.blade.php',
            'resources/views/admin/gtk/wali/absensi/rekap.blade.php',
            'resources/views/admin/cetak/id-card-siswa-index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = $this->file($view);

            $this->assertStringContainsString('class="row mb-2"', $contents);
            $this->assertStringContainsString('class="breadcrumb float-sm-right"', $contents);
            $this->assertStringContainsString('card bg-gradient-primary text-white mb-4', $contents);
            $this->assertStringContainsString('card card-outline card-primary', $contents);
            $this->assertStringNotContainsString('class="simansa-hero', $contents);
        }
    }

    public function test_student_and_parent_phone_numbers_are_clickable(): void
    {
        $studentShow = $this->file('resources/views/admin/siswa/show.blade.php');
        $studentIndex = $this->file('resources/views/admin/siswa/index.blade.php');
        $waliShow = $this->file('resources/views/admin/gtk/wali/siswa/show.blade.php');

        $this->assertGreaterThanOrEqual(3, substr_count($studentShow, 'href="tel:'));
        $this->assertStringContainsString('function renderPhoneLink(value, label)', $studentIndex);
        $this->assertSame(4, substr_count($studentIndex, 'renderPhoneLink('));
        $this->assertGreaterThanOrEqual(3, substr_count($waliShow, 'href="tel:'));
        $this->assertStringContainsString('$siswa->ortu->hp_ayah', $waliShow);
        $this->assertStringContainsString('$siswa->ortu->hp_ibu', $waliShow);
        $this->assertStringNotContainsString('$siswa->ortu->no_hp_ayah', $waliShow);
        $this->assertStringNotContainsString('$siswa->ortu->no_hp_ibu', $waliShow);
    }
}
