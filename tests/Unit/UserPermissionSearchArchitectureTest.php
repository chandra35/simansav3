<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserPermissionSearchArchitectureTest extends TestCase
{
    public function test_direct_user_permission_modal_has_a_search_filter(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/users/index.blade.php');

        $this->assertStringContainsString('id="permissionSearch"', $view);
        $this->assertStringContainsString('filterPermissionAccordion', $view);
        $this->assertStringContainsString('permission-module', $view);
        $this->assertStringContainsString('id="clearPermissionSearch"', $view);
    }
}
