<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HotspotAccountUiTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_account_directory_uses_responsive_bootstrap_table_layout(): void
    {
        $view = file_get_contents($this->root.'/resources/views/admin/hotspot/index.blade.php');
        $css = file_get_contents($this->root.'/public/css/admin/hotspot-accounts.css');

        $this->assertStringContainsString('class="col-xl-9"', $view);
        $this->assertStringContainsString('class="col-xl-3 hs-sidebar"', $view);
        $this->assertStringContainsString('class="table-responsive hs-table-wrap"', $view);
        $this->assertStringContainsString('table table-hover table-striped w-100 mb-0', $view);
        $this->assertStringContainsString("lengthMenu: [[10, 20, 50, 100]", $view);
        $this->assertStringContainsString("dom: \"<'row align-items-center mx-0'", $view);
        $this->assertStringContainsString('.hs-directory-filters { display: grid;', $css);
    }

    public function test_all_account_action_groups_have_stable_button_layouts(): void
    {
        $view = file_get_contents($this->root.'/resources/views/admin/hotspot/index.blade.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');
        $css = file_get_contents($this->root.'/public/css/admin/hotspot-accounts.css');

        $this->assertStringContainsString('class="hs-bulk-toolbar__actions"', $view);
        $this->assertStringContainsString('class="btn-group btn-group-sm" role="group"', $view);
        $this->assertStringContainsString('btn-group btn-group-sm hs-row-actions', $controller);
        $this->assertStringContainsString('.hs-row-actions { display: inline-flex; flex-wrap: nowrap;', $css);
        $this->assertStringContainsString('.hs-bulk-toolbar.is-visible { display: flex !important; }', $css);
    }
}
