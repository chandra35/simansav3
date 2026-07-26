<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MaintenancePageArchitectureTest extends TestCase
{
    public function test_maintenance_page_is_responsive_and_self_contained(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/errors/503.blade.php');

        $this->assertStringContainsString('viewport-fit=cover', $view);
        $this->assertStringContainsString('@media (max-width: 900px)', $view);
        $this->assertStringContainsString('@media (max-width: 600px)', $view);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $view);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr)', $view);
        $this->assertStringContainsString('overflow-wrap: anywhere', $view);
        $this->assertStringContainsString('height: 100dvh', $view);
        $this->assertStringContainsString('max-height: calc(100dvh', $view);
        $this->assertStringContainsString('@media (max-height: 560px)', $view);
        $this->assertStringContainsString('Single viewport contract', $view);
    }

    public function test_maintenance_page_has_clear_recovery_controls(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/errors/503.blade.php');

        $this->assertStringContainsString('id="refreshCountdown"', $view);
        $this->assertStringContainsString('id="retryButton"', $view);
        $this->assertStringContainsString('window.location.reload()', $view);
        $this->assertStringContainsString('src="/storage/settings/logo/', $view);
        $this->assertStringContainsString('aria-live="polite"', $view);
    }
}
