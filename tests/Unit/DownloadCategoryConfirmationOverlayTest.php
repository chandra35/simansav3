<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DownloadCategoryConfirmationOverlayTest extends TestCase
{
    public function test_category_forms_skip_the_navigation_overlay_until_confirmation_is_resolved(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/download_categories/index.blade.php');

        $this->assertSame(3, substr_count($view, 'data-no-overlay'));
        $this->assertStringContainsString("$(document).on('submit', '.js-confirm-submit'", $view);
    }
}
