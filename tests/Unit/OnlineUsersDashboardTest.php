<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OnlineUsersDashboardTest extends TestCase
{
    public function test_online_users_endpoint_is_unique_paginated_and_filterable(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/DashboardController.php');

        $this->assertStringContainsString('public function onlineUsers(Request $request)', $controller);
        $this->assertStringContainsString('min(max($request->integer(\'per_page\', 8), 1), 50)', $controller);
        $this->assertStringContainsString('whereNotExists(function ($query)', $controller);
        $this->assertStringContainsString("'summary' => \$summary", $controller);
        $this->assertStringContainsString("'pagination' => [", $controller);
        $this->assertStringContainsString("in_array(\$role, ['siswa', 'gtk', 'staff'], true)", $controller);
        $this->assertStringContainsString("if (\$search !== '')", $controller);
    }

    public function test_online_users_panel_uses_compact_table_and_full_list_modal(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/dashboard.blade.php');

        $this->assertStringContainsString('id="online-users-table-body"', $view);
        $this->assertStringContainsString('id="onlineUsersModal"', $view);
        $this->assertStringContainsString('id="online-search"', $view);
        $this->assertStringContainsString('id="online-role-filter"', $view);
        $this->assertStringContainsString('id="online-page-next"', $view);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $view);
        $this->assertStringContainsString('onlineRequest({ per_page: 8, page: 1 })', $view);
        $this->assertStringNotContainsString('id="online-users-grid"', $view);
    }
}
