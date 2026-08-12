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

    public function test_assignment_form_is_period_based_without_sk_inputs(): void
    {
        $view = file_get_contents(resource_path('views/admin/penugasan-gtk/index.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PenugasanGtkController.php'));

        $this->assertStringContainsString('id="assignmentTeacher"', $view);
        $this->assertStringContainsString('templateResult:teacherOption', $view);
        $this->assertStringNotContainsString('name="nomor_sk"', $view);
        $this->assertStringNotContainsString('name="tanggal_sk"', $view);
        $this->assertStringNotContainsString('name="file_sk"', $view);
        $this->assertStringNotContainsString("'nomor_sk' => [", $controller);
        $this->assertStringNotContainsString("'file_sk' => [", $controller);
    }

    public function test_student_and_gtk_action_dropdowns_escape_scroll_wrappers(): void
    {
        $studentView = file_get_contents(resource_path('views/admin/siswa/index.blade.php'));
        $gtkView = file_get_contents(resource_path('views/admin/gtk/index.blade.php'));
        $styles = file_get_contents(public_path('css/custom-compact.css'));

        $this->assertStringContainsString('simansa-action-dropdown-open', $studentView);
        $this->assertStringContainsString('simansa-action-dropdown-open', $gtkView);
        $this->assertStringContainsString('.simansa-action-dropdown-open', $styles);
    }
}
