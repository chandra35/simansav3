<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaginationStyleTest extends TestCase
{
    public function test_admin_layout_uses_bootstrap_four_pagination(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/AppServiceProvider.php');

        $this->assertStringContainsString('Paginator::useBootstrapFour();', $provider);
    }
}
