<?php

namespace Tests\Feature\Admin;

use App\Models\User;
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
        $this->assertStringContainsString('assignment-feedback--success', $view);
        $this->assertStringContainsString('assignment-feedback--error', $view);
        $this->assertStringContainsString('rounded-2xl shadow-2xl confirm-assignment-form', $view);
        $this->assertStringContainsString('#assignmentModal .modal-footer', $view);
        $this->assertStringContainsString('.assignment-teacher-selection{display:flex;align-items:center', $view);
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

    public function test_workload_supports_search_and_expandable_schedule_details(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PenugasanGtkController.php'));
        $service = file_get_contents(app_path('Services/GtkWorkloadService.php'));
        $view = file_get_contents(resource_path('views/admin/penugasan-gtk/workload.blade.php'));

        $this->assertStringContainsString('$search = trim((string) $request->q)', $controller);
        $this->assertStringContainsString("->with(['mataPelajaran:id,nama_mapel', 'kelas:id,nama_kelas,tingkat'])", $service);
        $this->assertStringContainsString("'jadwal' =>", $service);
        $this->assertStringContainsString('name="q"', $view);
        $this->assertStringContainsString('workload-detail-toggle', $view);
        $this->assertStringContainsString('Tugas Tambahan & Ekuivalensi', $view);
    }

    public function test_global_select2_highlight_has_readable_foreground(): void
    {
        $styles = file_get_contents(public_path('css/custom-compact.css'));

        $this->assertStringContainsString('.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected]', $styles);
        $this->assertStringContainsString('.select2-results__option--highlighted[aria-selected] *', $styles);
        $this->assertStringContainsString('color: #fff !important;', $styles);
    }

    public function test_global_dropdowns_scroll_inside_the_viewport(): void
    {
        $styles = file_get_contents(public_path('css/custom-compact.css'));
        $layout = file_get_contents(resource_path('views/vendor/adminlte/master.blade.php'));

        $this->assertStringContainsString('.select2-results__options {', $styles);
        $this->assertStringContainsString('.dropdown-menu {', $styles);
        $this->assertStringContainsString('select.custom-select:not([multiple])', $styles);
        $this->assertStringContainsString('max-height: min(45vh, 360px) !important;', $styles);
        $this->assertStringContainsString('max-height: min(60vh, 420px);', $styles);
        $this->assertStringContainsString('overscroll-behavior: contain;', $styles);
        $this->assertStringContainsString('scrollbar-width: thin;', $styles);
        $this->assertStringContainsString('normalizeNativeBootstrapSelects(document);', $layout);
        $this->assertStringContainsString("select.classList.add('custom-select');", $layout);
        $this->assertStringContainsString("select.classList.contains('select2')", $layout);
        $this->assertStringContainsString('new MutationObserver', $layout);
    }

    public function test_assignment_page_has_a_dedicated_laptop_layout(): void
    {
        $view = file_get_contents(resource_path('views/admin/penugasan-gtk/index.blade.php'));

        $this->assertStringContainsString('assignment-hero-actions', $view);
        $this->assertStringContainsString('col-lg-5 mt-3 mt-lg-0 assignment-hero-actions', $view);
        $this->assertStringContainsString('@media(min-width:992px) and (max-width:1439.98px)', $view);
        $this->assertStringContainsString('min-width:820px', $view);
    }

    public function test_active_assignments_are_the_default_and_vacancies_are_visible(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PenugasanGtkController.php'));
        $view = file_get_contents(resource_path('views/admin/penugasan-gtk/index.blade.php'));

        $this->assertStringContainsString(": 'active';", $controller);
        $this->assertStringContainsString("\$statusFilter !== 'all'", $controller);
        $this->assertStringContainsString('Jabatan aktif belum terisi', $view);
        $this->assertStringContainsString('Lepas / selesai', $view);
    }

    public function test_each_waka_type_has_one_active_holder_and_assignment_is_locked_atomically(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PenugasanGtkController.php'));

        $this->assertStringContainsString("\$type->kelompok === 'waka' ? 1 : null", $controller);
        $this->assertStringContainsString('Jabatan {$type->nama} sudah dipegang GTK lain', $controller);
        $this->assertStringContainsString('JenisPenugasanGtk::query()->lockForUpdate()', $controller);
        $this->assertStringContainsString('$this->guardAssignmentRules($data, $type, $gtk);', $controller);
    }

    public function test_super_admin_can_open_the_active_assignment_workspace(): void
    {
        $admin = User::role('Super Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Super Admin tidak tersedia.');
        }

        $this->actingAs($admin)->get(route('admin.penugasan-gtk.index'))
            ->assertOk()
            ->assertSee('Penugasan GTK')
            ->assertSee('Lepas / selesai');
    }

    public function test_assignment_picker_excludes_the_latest_outgoing_mutation(): void
    {
        $gtk = file_get_contents(app_path('Models/Gtk.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PenugasanGtkController.php'));

        $this->assertStringContainsString('scopeEligibleForAssignment', $gtk);
        $this->assertStringContainsString("latest_gtk_mutation.status_baru', false", $gtk);
        $this->assertStringContainsString('ORDER BY checked_gtk_mutation.created_at DESC', $gtk);
        $this->assertStringContainsString('->eligibleForAssignment()', $controller);
        $this->assertStringContainsString('$latestMutation?->status_baru === false', $controller);
    }
}
