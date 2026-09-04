<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class KipAssistanceEmisUpdateFlagTest extends TestCase
{
    public function test_assistance_follow_up_flag_covers_all_assistance_data_separately(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaPipController.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/Siswa.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/pip/index.blade.php');
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_09_03_000000_add_bantuan_emis_update_status_to_siswa_table.php');

        $this->assertStringContainsString('bantuan_emis_updated', $controller);
        $this->assertStringContainsString('toggleAssistanceFollowUp', $controller);
        $this->assertStringNotContainsString("route('admin.siswa.toggle-emis-registered'", $controller);
        $this->assertStringContainsString('bantuan_emis_updated_at', $model);
        $this->assertStringContainsString('toggle-assistance-follow-up', $routes);
        $this->assertStringContainsString('Tindak Lanjut', $view);
        $this->assertStringContainsString('pip-document-group__items', $view);
        $this->assertStringContainsString('pip-document-entry__meta', $controller);
        $this->assertStringContainsString("method: 'POST'", $view);
        $this->assertStringContainsString('pip-assistance-follow-up-meta', $view);
        $this->assertStringNotContainsString('table.ajax.reload(null, false);', $view);
        $this->assertStringContainsString("'marked_at'", $controller);
        $this->assertStringContainsString('bantuan_emis_updated_by', $migration);
    }
}
