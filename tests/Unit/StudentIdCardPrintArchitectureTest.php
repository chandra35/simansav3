<?php

namespace Tests\Unit;

use Tests\TestCase;

class StudentIdCardPrintArchitectureTest extends TestCase
{
    public function test_student_id_card_uses_dedicated_permission_and_ajax_search(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CetakController.php'));

        $this->assertStringContainsString("cetak/id-card-siswa/cari", $routes);
        $this->assertStringContainsString("cetak/id-card-siswa/kelas", $routes);
        $this->assertStringContainsString("middleware('permission:cetak-id-card-siswa')", $routes);
        $this->assertStringContainsString('StudentAccessScope', $controller);
        $this->assertStringContainsString("authorize('cetak-id-card-siswa')", $controller);
        $this->assertStringContainsString('searchSiswaIdCard', $controller);
        $this->assertStringContainsString('applyStudentAccessScope($query, $request->user())', $controller);
        $this->assertStringContainsString("where('siswa_kelas.status', 'aktif')", $controller);

        $view = file_get_contents(resource_path('views/admin/cetak/id-card-siswa-index.blade.php'));
        $this->assertStringContainsString(".prop('disabled', isSearchTab)", $view);

        $permissions = file_get_contents(app_path('Services/PermissionSyncService.php'));
        $this->assertStringContainsString("'cetak-id-card-siswa'", $permissions);
    }
}
