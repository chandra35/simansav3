<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ErrorPageUiTest extends TestCase
{
    public function test_error_status_pages_share_the_compact_safe_layout(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/errors';
        foreach ([403, 404, 500, 503] as $status) {
            $page = file_get_contents("{$root}/{$status}.blade.php");
            $this->assertStringContainsString("'status' => {$status}", $page);
            $this->assertStringContainsString("@include('errors.layout'", $page);
        }
        $layout = file_get_contents("{$root}/layout.blade.php");
        $this->assertStringContainsString('Ke dashboard', $layout);
        $this->assertStringNotContainsString('$exception->getMessage()', $layout);
    }
}
