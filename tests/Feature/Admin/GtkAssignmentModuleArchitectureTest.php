<?php

namespace Tests\Feature\Admin;

use App\Services\PermissionSyncService;
use Tests\TestCase;

class GtkAssignmentModuleArchitectureTest extends TestCase
{
    public function test_assignment_module_has_granular_permissions(): void
    {
        $definitions = app(PermissionSyncService::class)->getModuleDefinitions();

        $this->assertArrayHasKey('penugasan-gtk', $definitions);
        $this->assertSame([
            'view-penugasan-gtk',
            'create-penugasan-gtk',
            'edit-penugasan-gtk',
            'end-penugasan-gtk',
            'delete-penugasan-gtk',
            'manage-jenis-penugasan-gtk',
            'view-beban-kerja-gtk',
        ], $definitions['penugasan-gtk']['permissions']);
    }

    public function test_workload_no_longer_guesses_assignments_from_free_text_position(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/JadwalPelajaranController.php'));

        $this->assertStringContainsString('GtkWorkloadService', $controller);
        $this->assertStringNotContainsString("str_contains(\$jabatan, 'waka')", $controller);
        $this->assertStringNotContainsString("str_contains(\$jabatan, 'kepala lab')", $controller);
    }

    public function test_principal_management_is_not_loaded_by_settings_controller(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/AppSettingController.php'));

        $this->assertStringNotContainsString("compact('setting', 'provinsiList', 'kepalaSekolah'", $controller);
        $this->assertStringContainsString("compact('setting', 'provinsiList')", $controller);
    }

    public function test_sidebar_groups_gtk_assignment_and_workload(): void
    {
        $menu = file_get_contents(config_path('adminlte.php'));

        $this->assertStringContainsString("'text' => 'GTK & Penugasan'", $menu);
        $this->assertStringContainsString("'route' => 'admin.penugasan-gtk.index'", $menu);
        $this->assertStringContainsString("'route' => 'admin.penugasan-gtk.workload'", $menu);
    }
}
