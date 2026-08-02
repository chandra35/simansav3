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
        $this->assertStringContainsString('>Nama / Identitas GTK</th>', $table);
        $this->assertStringNotContainsString('<th>NIK</th>', $table);
        $this->assertStringNotContainsString('<th>Kode Guru</th>', $table);
        $this->assertStringNotContainsString('<th>Kategori PTK</th>', $table);
        $this->assertStringNotContainsString('<th>Username</th>', $table);
        $this->assertStringNotContainsString('<th>Jenis PTK</th>', $table);
        $this->assertStringNotContainsString('<th>Status Kepeg</th>', $table);
        $this->assertStringNotContainsString('<th>Jabatan</th>', $table);
        $this->assertStringContainsString('>Data Kepeg</th>', $table);
        $this->assertStringContainsString('>ID PTK</th>', $table);
        $this->assertStringContainsString('>Status Inpassing</th>', $table);
        $this->assertStringContainsString('>Status Sertifikasi</th>', $table);
        $this->assertStringContainsString("{ data: 'identity', name: 'nama_lengkap'", $view);
        $this->assertStringContainsString("{ data: 'peg_id', name: 'peg_id'", $view);
        $this->assertStringContainsString("{ data: 'status_inpassing', name: 'status_inpassing'", $view);
        $this->assertStringContainsString("{ data: 'status_sertifikasi', name: 'status_sertifikasi'", $view);

        $this->assertStringContainsString("'identity' => '", $controller);
        $this->assertStringContainsString('simansa-gtk-identity__meta', $controller);
        $this->assertGreaterThanOrEqual(5, substr_count($controller, 'simansa-gtk-identity__meta-row'));
        $this->assertStringContainsString('<strong>NIK</strong>', $controller);
        $this->assertStringContainsString('<strong>Kode GTK</strong>', $controller);
        $this->assertStringContainsString('<strong>Username</strong>', $controller);
        $this->assertStringContainsString('<strong>Jenis PTK</strong>', $controller);
        $this->assertStringNotContainsString('<strong>Kategori PTK</strong>', $controller);
        $this->assertStringContainsString('simansa-gtk-meta-badge', $controller);
    }

    public function test_filters_reload_smoothly_and_styles_are_scoped_to_gtk_page(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('class="simansa-gtk-management"', $view);
        $this->assertStringContainsString('const reloadGtkTable = function(resetPaging = true)', $view);
        $this->assertStringContainsString('gtkTable.ajax.reload(null, resetPaging);', $view);
        $this->assertStringContainsString('scrollX: true', $view);
        $this->assertStringContainsString('scrollCollapse: true', $view);
        $this->assertStringNotContainsString('table-responsive simansa-gtk-table-wrap', $view);
        $this->assertStringContainsString("on('preXhr.dt'", $view);
        $this->assertStringContainsString("on('xhr.dt'", $view);
        $this->assertStringContainsString('Filter memuat data secara otomatis', $view);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-filter', $css);
        $this->assertStringContainsString('flex-direction: column;', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-identity__meta-row', $css);
        $this->assertStringContainsString('.simansa-gtk-management .dataTables_processing', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table-wrap .dataTables_filter', $css);
        $this->assertStringContainsString('justify-content: flex-end;', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table-wrap .dataTables_scrollBody', $css);
    }

    public function test_photo_gender_avatar_and_active_homeroom_metadata_are_available(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/GtkController.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/Gtk.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString("'foto_profile'", $controller);
        $this->assertStringContainsString('private function getGtkListAvatar(Gtk $gtk): string', $controller);
        $this->assertStringContainsString("jenis_kelamin === 'P'", $controller);
        $this->assertStringContainsString("? 'muslimah' : 'muslim'", $controller);
        $this->assertStringContainsString("'is-female' : 'is-male'", $controller);
        $this->assertStringContainsString('onerror="this.remove()"', $controller);
        $this->assertStringContainsString("'kelasWali' => function", $controller);
        $this->assertStringContainsString('tahun_pelajaran_id', $controller);
        $this->assertStringContainsString('<strong>Wali Kelas</strong>', $controller);
        $this->assertStringContainsString('public function kelasWali()', $model);
        $this->assertStringContainsString("hasMany(Kelas::class, 'wali_kelas_id', 'user_id')", $model);
        $this->assertStringContainsString('@keyframes simansa-gtk-avatar-breathe', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    public function test_table_columns_use_balanced_professional_proportions(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('autoWidth: false', $view);
        $this->assertStringContainsString("{ targets: 0, width: '4%' }", $view);
        $this->assertStringContainsString("{ targets: 1, width: '30%' }", $view);
        $this->assertStringContainsString("{ targets: 2, width: '12%' }", $view);
        $this->assertStringContainsString("{ targets: [3, 4], width: '11%' }", $view);
        $this->assertStringContainsString("{ targets: [5, 6], width: '10%' }", $view);
        $this->assertStringContainsString("{ targets: 7, width: '12%' }", $view);
        $this->assertStringContainsString("className: 'gtk-col-identity'", $view);
        $this->assertStringContainsString("className: 'gtk-col-professional-id'", $view);
        $this->assertStringContainsString("className: 'gtk-col-professional-status'", $view);
        $this->assertStringContainsString("className: 'gtk-col-status'", $view);
        $this->assertStringContainsString("className: 'gtk-col-actions'", $view);

        $this->assertStringContainsString('table-layout: fixed;', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table .gtk-col-status', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table .gtk-col-actions', $css);
        $this->assertStringContainsString('border-radius: 7px !important;', $css);
    }
}
