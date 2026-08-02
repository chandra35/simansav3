<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GtkIndexUiArchitectureTest extends TestCase
{
    public function test_gtk_information_is_grouped_into_five_semantic_columns(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/GtkController.php');
        preg_match('/<table id="gtk-table".*?<\/table>/s', $view, $tableMatch);
        $table = $tableMatch[0] ?? '';

        $this->assertNotSame('', $table);
        $this->assertStringContainsString('>Profil</th>', $table);
        $this->assertStringContainsString('>Peran</th>', $table);
        $this->assertStringContainsString('>Status</th>', $table);
        $this->assertStringContainsString('>Aksi</th>', $table);
        $this->assertStringNotContainsString('<th>NIK</th>', $table);
        $this->assertStringNotContainsString('<th>ID PTK</th>', $table);
        $this->assertSame(5, preg_match_all('/<th(?:\s|>)/', $table));
        $this->assertStringContainsString("{ data: 'identity', name: 'nama_lengkap'", $view);
        $this->assertStringContainsString("{ data: 'role_summary', name: 'jenis_ptk'", $view);
        $this->assertStringContainsString("{ data: 'status_summary', name: 'status_summary'", $view);

        $this->assertStringContainsString("'identity' => '", $controller);
        $this->assertStringContainsString('simansa-gtk-profile__identifiers', $controller);
        $this->assertStringContainsString('<small>NIK</small>', $controller);
        $this->assertStringContainsString('<small>ID PTK</small>', $controller);
        $this->assertStringNotContainsString('<strong>Kode GTK</strong>', $controller);
        $this->assertStringNotContainsString('<strong>Username</strong>', $controller);
        $this->assertStringContainsString("'role_summary' => '", $controller);
        $this->assertStringContainsString("'status_summary' => '", $controller);
        $this->assertSame(4, substr_count($controller, 'simansa-gtk-status-badge'));
    }

    public function test_filters_reload_smoothly_and_styles_are_scoped_to_gtk_page(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('class="simansa-gtk-management"', $view);
        $this->assertStringContainsString('const reloadGtkTable = function(resetPaging = true)', $view);
        $this->assertStringContainsString('gtkTable.ajax.reload(null, resetPaging);', $view);
        $this->assertStringNotContainsString('scrollX:', $view);
        $this->assertStringNotContainsString('scrollCollapse:', $view);
        $this->assertStringNotContainsString('table-responsive simansa-gtk-table-wrap', $view);
        $this->assertStringContainsString("on('preXhr.dt'", $view);
        $this->assertStringContainsString("on('xhr.dt'", $view);
        $this->assertStringContainsString('Filter memuat data secara otomatis', $view);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-filter', $css);
        $this->assertStringContainsString('flex-direction: column;', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-profile__identifiers', $css);
        $this->assertStringContainsString('.simansa-gtk-management .dataTables_processing', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table-wrap .dataTables_filter', $css);
        $this->assertStringContainsString('justify-content: flex-end;', $css);
        $this->assertStringContainsString('overflow-x: auto;', $css);
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
        $this->assertStringContainsString('simansa-gtk-wali-list', $controller);
        $this->assertStringContainsString('public function kelasWali()', $model);
        $this->assertStringContainsString("hasMany(Kelas::class, 'wali_kelas_id', 'user_id')", $model);
        $this->assertStringContainsString('@keyframes simansa-gtk-avatar-breathe', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    public function test_table_columns_use_balanced_professional_proportions(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/index.blade.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/GtkController.php');
        $css = file_get_contents(dirname(__DIR__, 2).'/public/css/custom-compact.css');

        $this->assertStringContainsString('autoWidth: true', $view);
        $this->assertStringNotContainsString('columnDefs:', $view);
        $this->assertStringContainsString("className: 'gtk-col-profile align-middle'", $view);
        $this->assertStringContainsString("className: 'gtk-col-role align-middle'", $view);
        $this->assertStringContainsString("className: 'gtk-col-status align-middle'", $view);
        $this->assertStringContainsString("className: 'gtk-col-actions align-middle'", $view);
        $this->assertStringContainsString('table table-hover table-sm align-middle simansa-gtk-table', $view);

        $this->assertStringContainsString('table-layout: auto;', $css);
        $this->assertStringContainsString('min-width: 0;', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table .gtk-col-status', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-table .gtk-col-actions', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-action-menu .simansa-gtk-action-toggle', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-action-dropdown', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-status-grid', $css);
        $this->assertStringContainsString('.simansa-gtk-management .simansa-gtk-status-badge.is-success', $css);
        $this->assertStringContainsString('btn btn-sm btn-outline-primary dropdown-toggle simansa-gtk-action-toggle', $controller);
        $this->assertStringContainsString('dropdown-menu dropdown-menu-right simansa-gtk-action-dropdown', $controller);
        $this->assertStringContainsString('data-action="view"', $controller);
        $this->assertStringContainsString('data-action="edit"', $controller);
        $this->assertStringContainsString('data-action="reset-password"', $controller);
        $this->assertStringContainsString('data-action="login-as"', $controller);
        $this->assertStringContainsString('data-action="delete"', $controller);
        $this->assertStringContainsString('fas fa-eye text-info', $controller);
        $this->assertStringContainsString('fas fa-edit text-primary', $controller);
        $this->assertStringContainsString('fas fa-key text-warning', $controller);
        $this->assertStringContainsString('fas fa-user-shield text-success', $controller);
        $this->assertStringContainsString('fas fa-trash-alt', $controller);
        $this->assertStringContainsString('data-tooltip="true"', $controller);
        $this->assertStringNotContainsString('simansa-gtk-action-select', $controller);
        $this->assertStringContainsString('function handleGtkAction(item)', $view);
        $this->assertStringContainsString('refreshGtkTooltips', $view);
        $this->assertStringContainsString(".on('draw.dt'", $view);
    }
}
