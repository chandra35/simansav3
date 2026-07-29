<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GtkIndexUiArchitectureTest extends TestCase
{
    public function test_technical_identity_columns_are_consolidated_below_gtk_name(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/GtkController.php');
        preg_match('/<table id="gtk-table".*?<\/table>/s', $view, $tableMatch);
        $table = $tableMatch[0] ?? '';

        $this->assertNotSame('', $table);
        $this->assertStringContainsString('<th>Nama / Identitas GTK</th>', $table);
        $this->assertStringNotContainsString('<th>NIK</th>', $table);
        $this->assertStringNotContainsString('<th>Kode Guru</th>', $table);
        $this->assertStringNotContainsString('<th>Kategori PTK</th>', $table);
        $this->assertStringNotContainsString('<th>Username</th>', $table);
        $this->assertStringContainsString("{ data: 'identity', name: 'nama_lengkap' }", $view);

        $this->assertStringContainsString("'identity' => '", $controller);
        $this->assertStringContainsString('simansa-gtk-identity__meta', $controller);
        $this->assertStringContainsString('<strong>NIK</strong>', $controller);
        $this->assertStringContainsString('<strong>Kode</strong>', $controller);
        $this->assertStringContainsString('<strong>Username</strong>', $controller);
        $this->assertStringContainsString('simansa-gtk-meta-badge', $controller);
    }

    public function test_filters_reload_smoothly_and_styles_are_scoped_to_gtk_page(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('class="simansa-gtk-management"', $view);
        $this->assertStringContainsString('const reloadGtkTable = function(resetPaging = true)', $view);
        $this->assertStringContainsString('gtkTable.ajax.reload(null, resetPaging);', $view);
        $this->assertStringContainsString("on('preXhr.dt'", $view);
        $this->assertStringContainsString("on('xhr.dt'", $view);
        $this->assertStringContainsString('Filter memuat data secara otomatis', $view);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-filter', $css);
        $this->assertStringContainsString('.simansa-gtk-management .dataTables_processing', $css);
    }
}
