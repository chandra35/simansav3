<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GlobalStudentClassScopeArchitectureTest extends TestCase
{
    public function test_custom_permission_controls_global_student_and_class_scope(): void
    {
        $scope = file_get_contents(dirname(__DIR__, 2).'/app/Services/StudentAccessScope.php');
        $kelasController = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');
        $permissions = file_get_contents(dirname(__DIR__, 2).'/app/Services/PermissionSyncService.php');

        $this->assertStringContainsString("GLOBAL_SCOPE_PERMISSION = 'access-global-siswa-kelas'", $scope);
        $this->assertStringContainsString('$user->can(self::GLOBAL_SCOPE_PERMISSION)', $scope);
        $this->assertStringContainsString('ensureKelasInScope($kelas, $request->user())', $kelasController);
        $this->assertStringContainsString('applyKelasScope($query, $request->user())', $kelasController);
        $this->assertStringContainsString("'access-global-siswa-kelas'", $permissions);
    }
}
