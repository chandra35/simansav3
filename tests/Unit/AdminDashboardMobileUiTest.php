<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminDashboardMobileUiTest extends TestCase
{
    public function test_dashboard_provides_mobile_navigation_and_accessible_controls(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/dashboard.blade.php');

        $this->assertStringContainsString('aria-label="Akses cepat dashboard"', $view);
        $this->assertStringContainsString('simansa-dashboard-quick-nav__link', $view);
        $this->assertStringContainsString('aria-label="Cari pengguna online"', $view);
        $this->assertStringContainsString('aria-label="Perbarui daftar pengguna online"', $view);
        $this->assertStringContainsString(':focus-visible', $view);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $view);
    }

    public function test_dashboard_tables_become_readable_cards_on_mobile(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/dashboard.blade.php');

        $this->assertStringContainsString('data-label="Waktu"', $view);
        $this->assertStringContainsString('data-label="Deskripsi"', $view);
        $this->assertStringContainsString('.simansa-activity-table thead {', $view);
        $this->assertStringContainsString('display: none;', $view);
        $this->assertStringContainsString('content: attr(data-label);', $view);
        $this->assertStringContainsString('min-height: 44px;', $view);
    }
}
