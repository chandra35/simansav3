<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NisLocalTemplateDownloadOverlayTest extends TestCase
{
    public function test_template_download_is_marked_as_non_navigation(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/nis-lokal/index.blade.php');

        $this->assertStringContainsString("route('admin.nis-lokal.template')", $view);
        $this->assertStringContainsString('data-no-overlay', $view);
        $this->assertStringContainsString('download="template-update-nis-lokal.xlsx"', $view);
    }

    public function test_global_overlay_suppresses_beforeunload_for_downloads(): void
    {
        $layout = file_get_contents(dirname(__DIR__, 2).'/resources/views/vendor/adminlte/master.blade.php');

        $this->assertStringContainsString('let suppressUnloadOverlay = false;', $layout);
        $this->assertStringContainsString('suppressOverlayForNonNavigation();', $layout);
        $this->assertStringContainsString("link.hasAttribute('download')", $layout);
        $this->assertStringContainsString('if (suppressUnloadOverlay)', $layout);
        $this->assertStringContainsString('appHideGlobalOverlay();', $layout);
    }
}
