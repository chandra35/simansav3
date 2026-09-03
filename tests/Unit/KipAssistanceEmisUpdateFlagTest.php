<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class KipAssistanceEmisUpdateFlagTest extends TestCase
{
    public function test_kip_assistance_emis_flag_is_separate_from_general_student_emis_status(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaPipController.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/Siswa.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/pip/index.blade.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_09_03_000000_add_bantuan_emis_update_status_to_siswa_table.php');

        $this->assertStringContainsString('bantuan_emis_updated', $controller);
        $this->assertStringContainsString('toggleAssistanceEmisUpdate', $controller);
        $this->assertStringNotContainsString("route('admin.siswa.toggle-emis-registered'", $controller);
        $this->assertStringContainsString('bantuan_emis_updated_at', $model);
        $this->assertStringContainsString('toggle-assistance-emis-update', $routes);
        $this->assertStringContainsString('Update KIP/PKH EMIS', $view);
        $this->assertStringContainsString('bantuan_emis_updated_by', $migration);
    }
}
