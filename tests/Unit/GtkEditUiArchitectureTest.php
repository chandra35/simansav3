<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GtkEditUiArchitectureTest extends TestCase
{
    #[Test]
    public function edit_page_follows_the_standard_operational_page_structure(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/edit.blade.php');
        preg_match("/@section\('content_header'\)(.*?)@stop/s", $view, $headerMatch);
        $header = $headerMatch[1] ?? '';

        $this->assertStringContainsString('Edit Data GTK', $header);
        $this->assertStringContainsString('breadcrumb', $header);
        $this->assertStringNotContainsString('simansa-page-hero', $header);
        $this->assertStringContainsString('<div class="simansa-gtk-edit">', $view);
        $this->assertStringContainsString('card bg-gradient-primary text-white simansa-gtk-edit__hero', $view);
        $this->assertStringContainsString('card card-outline card-primary gtk-edit-sidebar', $view);
        $this->assertStringContainsString('card card-outline card-primary gtk-edit-main', $view);
    }

    #[Test]
    public function enterprise_layout_is_responsive_and_accessible(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/edit.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('class="col-md-3"', $view);
        $this->assertStringContainsString('class="col-md-9"', $view);
        $this->assertStringContainsString('nav nav-pills flex-column gtk-edit-tabs', $view);
        $this->assertSame(4, substr_count($view, 'aria-controls='));
        $this->assertStringContainsString('gtk-edit-identity-summary', $view);
        $this->assertStringContainsString('gtk-edit-dropzone--compact', $view);
        $this->assertStringContainsString('role="button" tabindex="0"', $view);
        $this->assertStringContainsString("e.key === 'Enter' || e.key === ' '", $view);
        $this->assertSame(3, substr_count($view, 'gtk-edit-form-actions'));
        $this->assertSame(3, substr_count($view, 'type="reset"'));
        $this->assertSame(3, substr_count($view, '<i class="fas fa-save"></i>'));
        $this->assertSame(3, substr_count($view, 'class="col-md-4"'));
        $this->assertGreaterThanOrEqual(4, substr_count($view, 'class="form-control select2"'));
        $this->assertSame(1, substr_count($view, 'id="email"'));
        $this->assertSame(1, substr_count($view, 'id="user_email"'));
        $this->assertStringContainsString("$('#gtkEditHeroName').text(response.data.nama_lengkap);", $view);

        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-sidebar', $css);
        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-main', $css);
        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-identity-summary', $css);
        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-form-actions', $css);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $css);
    }
}
