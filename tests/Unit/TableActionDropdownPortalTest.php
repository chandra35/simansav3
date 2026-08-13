<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TableActionDropdownPortalTest extends TestCase
{
    public function test_admin_layout_installs_a_global_portal_for_table_dropdowns(): void
    {
        $root = dirname(__DIR__, 2);
        $master = file_get_contents($root.'/resources/views/vendor/adminlte/master.blade.php');
        $portal = file_get_contents($root.'/resources/views/vendor/adminlte/partials/table-action-dropdown-portal.blade.php');

        $this->assertStringContainsString("@include('adminlte::partials.table-action-dropdown-portal')", $master);
        $this->assertStringContainsString("host.closest('table')", $portal);
        $this->assertStringContainsString("menu.appendTo(document.body)", $portal);
        $this->assertStringContainsString('position: fixed !important', $portal);
        $this->assertStringContainsString('shown.bs.dropdown.simansaTablePortal', $portal);
        $this->assertStringContainsString('preDraw.dt.simansaTablePortal', $portal);
        $this->assertStringContainsString('restoreMenu()', $portal);
    }
}
