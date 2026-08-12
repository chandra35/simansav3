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
        $this->assertContains('teacher_notice', $model->getFillable());
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
        $this->assertStringContainsString("->whereHas('kelasTahunAktif')", $controller);
        $this->assertStringContainsString("request->filled('tingkat')", $controller);
        $this->assertStringContainsString("request->filled('kelas_id')", $controller);
        $this->assertStringContainsString('filter-tingkat', $view);
        $this->assertStringContainsString('filter-kelas', $view);
        $this->assertStringContainsString('status_pendampingan', $view);
        $this->assertStringContainsString('Catat', $view);
        $this->assertStringContainsString('//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', $view);
        $this->assertStringNotContainsString("asset('vendor/datatables", $view);
    }

    public function test_student_history_exposes_a_compact_bk_profile_summary(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CatatanKonselingController.php'));
        $view = file_get_contents(resource_path('views/admin/catatan-konseling/report-siswa.blade.php'));

        $this->assertStringContainsString("'ortu'", $controller);
        $this->assertStringContainsString("'kelasTahunAktif.waliKelas'", $controller);
        $this->assertStringContainsString('student-photo', $view);
        $this->assertStringContainsString('Identitas Pribadi', $view);
        $this->assertStringContainsString('Informasi Akademik', $view);
        $this->assertStringContainsString('Orang Tua / Keluarga', $view);
        $this->assertStringContainsString('ALAMAT DOMISILI', $view);
        $this->assertStringContainsString('Riwayat Layanan Konseling', $view);
    }

    public function test_teacher_notice_is_separate_and_scoped_to_related_teachers(): void
    {
        $counselingForm = file_get_contents(resource_path('views/admin/catatan-konseling/_form.blade.php'));
        $dashboardController = file_get_contents(app_path('Http/Controllers/Admin/GtkDashboardController.php'));
        $studentRoutes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString('share_with_teachers', $counselingForm);
        $this->assertStringContainsString('teacher_notice', $counselingForm);
        $this->assertStringContainsString("where('gtk_id', \$gtk->id)", $dashboardController);
        $this->assertStringContainsString("whereIn('kelas.id', \$relatedClassIds)", $dashboardController);
        $this->assertStringNotContainsString('siswa/catatan-konseling', $studentRoutes);
    }

    public function test_bk_assignment_form_uses_schedule_caseload_and_own_counselor(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CatatanKonselingController.php'));
        $form = file_get_contents(resource_path('views/admin/catatan-konseling/_form.blade.php'));
        $assets = file_get_contents(resource_path('views/admin/catatan-konseling/_form-assets.blade.php'));

        $this->assertStringContainsString('private function availableStudentsQuery()', $controller);
        $this->assertStringContainsString("->where('gtk_id', auth()->user()->gtk?->id)", $controller);
        $this->assertStringContainsString("->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))", $controller);
        $this->assertStringContainsString("whereIn('kelas.id', \$classIds)", $controller);
        $this->assertStringContainsString("hasAnyRole(['Super Admin', 'Admin'])", $controller);
        $this->assertStringContainsString("orWhereHas('user.roles'", $controller);
        $this->assertStringContainsString("\$request->user()->hasRole('BK')", $controller);
        $this->assertStringContainsString("\$data['konselor_id'] = \$gtk->id", $controller);
        $this->assertStringContainsString('Konselor otomatis mengikuti akun BK', $form);
        $this->assertStringContainsString('minimumInputLength:0', $assets);
    }

    public function test_compact_counseling_form_omits_time_and_service_type_inputs(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CatatanKonselingController.php'));
        $form = file_get_contents(resource_path('views/admin/catatan-konseling/_form.blade.php'));

        $this->assertStringNotContainsString('name="waktu_mulai"', $form);
        $this->assertStringNotContainsString('name="waktu_selesai"', $form);
        $this->assertStringNotContainsString('name="jenis_konseling"', $form);
        $this->assertStringNotContainsString("'waktu_mulai' => [", $controller);
        $this->assertStringNotContainsString("'jenis_konseling' => ['required'", $controller);
        $this->assertStringContainsString("\$data['jenis_konseling'] = 'individual'", $controller);
    }
}
