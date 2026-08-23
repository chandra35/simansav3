<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentInternalVervalAccessTest extends TestCase
{
    public function test_internal_verval_is_only_rendered_and_mutable_by_admins(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/SiswaController.php');
        $view = file_get_contents($root.'/resources/views/admin/siswa/index.blade.php');

        $this->assertStringContainsString('canManageInternalVerval', $controller);
        $this->assertStringContainsString("hasAnyRole(['Super Admin', 'Admin'])", $controller);
        $this->assertStringContainsString('abort_unless($this->canManageInternalVerval(Auth::user()), 403);', $controller);
        $this->assertStringContainsString('@if($canManageInternalVerval)', $view);
        $this->assertStringContainsString("data: 'verval_ijazah'", $view);
    }
}
