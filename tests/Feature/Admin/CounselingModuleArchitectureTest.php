<?php

namespace Tests\Feature\Admin;

use App\Models\CatatanKonseling;
use App\Services\PermissionSyncService;
use Tests\TestCase;

class CounselingModuleArchitectureTest extends TestCase
{
    public function test_counseling_permissions_are_available_in_role_matrix(): void
    {
        $modules = (new PermissionSyncService)->getModuleDefinitions();

        $this->assertSame([
            'view-catatan-konseling',
            'create-catatan-konseling',
            'edit-catatan-konseling',
            'delete-catatan-konseling',
            'view-confidential-catatan-konseling',
            'report-catatan-konseling',
        ], $modules['catatan-konseling']['permissions']);
    }

    public function test_model_fields_and_workflow_match_the_existing_table_contract(): void
    {
        $model = new CatatanKonseling;

        $this->assertContains('permasalahan', $model->getFillable());
        $this->assertContains('tanggal_tindak_lanjut', $model->getFillable());
        $this->assertContains('is_confidential', $model->getFillable());
        $this->assertSame(['baru', 'dalam_proses', 'selesai', 'perlu_rujukan'], array_keys(CatatanKonseling::STATUS));
    }

    public function test_specific_counseling_routes_are_registered_before_resource_binding(): void
    {
        $this->assertSame('/admin/catatan-konseling/siswa/search', route('admin.catatan-konseling.students.search', absolute: false));
        $this->assertSame('/admin/catatan-konseling/catatan', route('admin.catatan-konseling.records', absolute: false));
        $this->assertSame('/admin/catatan-konseling/report/siswa', route('admin.catatan-konseling.report-siswa', absolute: false));
    }

    public function test_every_counseling_page_uses_the_installed_adminlte_layout(): void
    {
        foreach (['index', 'records', 'create', 'edit', 'show', 'report-siswa'] as $page) {
            $view = file_get_contents(resource_path("views/admin/catatan-konseling/{$page}.blade.php"));

            $this->assertStringContainsString("@extends('adminlte::page')", $view, "Layout halaman {$page} tidak valid.");
            $this->assertStringNotContainsString("@extends('layouts.admin')", $view);
        }
    }

    public function test_bk_home_is_a_filterable_active_student_directory(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CatatanKonselingController.php'));
        $view = file_get_contents(resource_path('views/admin/catatan-konseling/index.blade.php'));

        $this->assertStringContainsString("where('status_siswa', 'aktif')", $controller);
        $this->assertStringContainsString("request->filled('tingkat')", $controller);
        $this->assertStringContainsString("request->filled('kelas_id')", $controller);
        $this->assertStringContainsString('filter-tingkat', $view);
        $this->assertStringContainsString('filter-kelas', $view);
        $this->assertStringContainsString('status_pendampingan', $view);
        $this->assertStringContainsString('Catat', $view);
    }
}
