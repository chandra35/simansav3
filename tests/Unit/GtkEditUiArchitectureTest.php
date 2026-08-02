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
        $this->assertStringContainsString('card card-outline card-primary gtk-edit-shell', $view);
    }

    #[Test]
    public function tabs_photo_upload_and_form_actions_are_responsive_and_accessible(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/edit.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertSame(4, substr_count($view, 'aria-controls='));
        $this->assertStringContainsString('gtk-edit-tabs-wrap', $view);
        $this->assertStringContainsString('class="gtk-edit-photo-layout"', $view);
        $this->assertStringContainsString('class="gtk-edit-dropzone" role="button" tabindex="0"', $view);
        $this->assertStringContainsString("e.key === 'Enter' || e.key === ' '", $view);
        $this->assertSame(3, substr_count($view, 'gtk-edit-form-actions'));
        $this->assertSame(1, substr_count($view, 'id="email"'));
        $this->assertSame(1, substr_count($view, 'id="user_email"'));
        $this->assertStringContainsString("$('#gtkEditHeroName').text(response.data.nama_lengkap);", $view);

        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-tabs-wrap', $css);
        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-photo-layout', $css);
        $this->assertStringContainsString('.simansa-gtk-edit .gtk-edit-form-actions', $css);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $css);
    }
}
