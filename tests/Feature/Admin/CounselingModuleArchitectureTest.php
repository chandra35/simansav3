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
        $this->assertSame('/admin/catatan-konseling/report/siswa', route('admin.catatan-konseling.report-siswa', absolute: false));
    }
}
