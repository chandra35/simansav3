<?php

namespace Tests\Unit;

use App\Services\AsramaRaporService;
use PHPUnit\Framework\TestCase;

class AsramaModuleTest extends TestCase
{
    public function test_score_is_rendered_as_arabic_report_text(): void
    {
        $service = new AsramaRaporService;

        $this->assertSame('ثماني', $service->scoreInArabic(8));
        $this->assertSame('ثماني ونصف', $service->scoreInArabic(8.5));
        $this->assertSame('سبع وربع', $service->scoreInArabic(7.25));
    }

    public function test_report_descriptor_uses_asrama_scale(): void
    {
        $service = new AsramaRaporService;

        $this->assertSame('ممتاز', $service->descriptor(9));
        $this->assertSame('جيد جدا', $service->descriptor(8.5));
        $this->assertSame('ناقص', $service->descriptor(5));
    }

    public function test_module_files_keep_asrama_data_separate_from_regular_academics(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = file_get_contents($root.'/database/migrations/2026_07_31_100000_create_asrama_module_tables.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $menu = file_get_contents($root.'/config/adminlte.php');

        $this->assertStringContainsString("Schema::create('asrama_nilai'", $migration);
        $this->assertStringContainsString("Schema::create('asrama_rapor'", $migration);
        $this->assertStringContainsString("prefix('asrama')", $routes);
        $this->assertStringContainsString("'text' => 'ASRAMA'", $menu);
    }
}
