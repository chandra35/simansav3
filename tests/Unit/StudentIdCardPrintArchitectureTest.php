<?php

namespace Tests\Unit;

use Tests\TestCase;

class StudentIdCardPrintArchitectureTest extends TestCase
{
    public function test_student_id_card_routes_use_student_permission_and_ajax_search(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/CetakController.php'));

        $this->assertStringContainsString("cetak/id-card-siswa/cari", $routes);
        $this->assertStringContainsString("cetak/id-card-siswa/kelas", $routes);
        $this->assertStringContainsString("middleware('permission:view-siswa')", $routes);
        $this->assertStringContainsString('StudentAccessScope', $controller);
        $this->assertStringContainsString('searchSiswaIdCard', $controller);
        $this->assertStringContainsString('applyStudentAccessScope($query, $request->user())', $controller);
    }
}
