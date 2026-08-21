<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NilaiRdmAccessArchitectureTest extends TestCase
{
    public function test_legger_and_homeroom_rdm_recap_use_separate_permissions(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root.'/routes/web.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/NilaiRdmController.php');
        $migration = file_get_contents($root.'/database/migrations/2026_08_22_090000_separate_legger_and_rdm_recap_permissions.php');

        $this->assertStringContainsString("middleware('permission:view-nilai-legger')", $routes);
        $this->assertStringContainsString("middleware('permission:view-nilai-rdm')", $routes);
        $this->assertStringContainsString('StudentAccessScope', $controller);
        $this->assertStringContainsString("where('sumber_data', 'rdm_sync')", $controller);
        $this->assertStringContainsString("revokePermissionTo(['view-nilai', 'input-nilai'])", $migration);
    }
}
