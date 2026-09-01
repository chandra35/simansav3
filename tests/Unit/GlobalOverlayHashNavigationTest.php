<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GlobalOverlayHashNavigationTest extends TestCase
{
    public function test_global_overlay_is_not_shown_for_same_page_hash_navigation(): void
    {
        $layout = file_get_contents(dirname(__DIR__, 2).'/resources/views/vendor/adminlte/master.blade.php');

        $this->assertStringContainsString('destination.pathname === window.location.pathname', $layout);
        $this->assertStringContainsString('destination.search === window.location.search', $layout);
        $this->assertStringContainsString('destination.hash)', $layout);
        $this->assertStringContainsString('suppressOverlayForNonNavigation();', $layout);
    }
}
