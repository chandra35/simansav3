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

        $this->assertStringContainsString('class="col-12"', $view);
        $this->assertStringNotContainsString('class="col-xl-3 hs-sidebar"', $view);
        $this->assertStringContainsString('class="card card-outline card-info hs-control-card mb-3"', $view);
        $this->assertLessThan(strpos($view, 'hs-account-card'), strpos($view, 'hs-control-card'));
        $this->assertStringContainsString('class="table-responsive hs-table-wrap"', $view);
        $this->assertStringContainsString('table table-bordered table-striped table-hover w-100 mb-0', $view);
        $this->assertStringContainsString("@section('plugins.Datatables', true)", $view);
        $this->assertStringNotContainsString('cdn.datatables.net', $view);
        $this->assertStringContainsString("lengthMenu: [[10, 20, 50, 100]", $view);
        $this->assertStringNotContainsString('dom:', $view);
        $this->assertStringNotContainsString('.dataTables_wrapper', $css);
    }

    public function test_all_account_action_groups_have_stable_button_layouts(): void
    {
        $view = file_get_contents($this->root.'/resources/views/admin/hotspot/index.blade.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/HotspotController.php');
        $css = file_get_contents($this->root.'/public/css/admin/hotspot-accounts.css');

        $this->assertStringContainsString('class="hs-bulk-toolbar__actions"', $view);
        $this->assertStringContainsString('id="filterAccountState"', $view);
        $this->assertStringContainsString('<option value="active" selected>Aktif</option>', $view);
        $this->assertMatchesRegularExpression('/<option value="alumni">Alumni \/ Lulus \([^<]+\)<\/option>/', $view);
        $this->assertMatchesRegularExpression('/<option value="credentials_missing">Password Belum Tersedia \([^<]+\)<\/option>/', $view);
        $this->assertStringContainsString('class="dropdown hs-row-actions"', $controller);
        $this->assertStringContainsString('dropdown-menu dropdown-menu-right', $controller);
        $this->assertStringContainsString('data-boundary="viewport"', $controller);
        $this->assertStringContainsString("data-username=\"'.e(\$h->username).'\"", $controller);
        $this->assertStringContainsString('id="accountActionFeedback"', $view);
        $this->assertStringContainsString("showAccountActionFeedback('info', 'Sinkronisasi sedang berjalan'", $view);
        $this->assertStringContainsString('timeout: 60000', $view);
        $this->assertStringContainsString('table.ajax.reload(null, false)', $view);
        $this->assertStringContainsString('.hs-bulk-toolbar.is-visible { display: flex !important; }', $css);
        $this->assertStringNotContainsString('.hs-account-card .pagination', $css);
        $this->assertStringNotContainsString('.page-item.active', $css);
    }
}
