<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SchoolOriginRoleScopeTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_pure_gtk_school_data_is_scoped_to_active_homeroom_classes(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SekolahAsalController.php');

        $this->assertStringContainsString('waliClassIds()', $controller);
        $this->assertStringContainsString('StudentAccessScope::class', $controller);
        $this->assertStringContainsString("whereHas('kelasTahunAktif'", $controller);
        $this->assertStringContainsString("whereHas('siswa'", $controller);
        $this->assertStringContainsString("whereKey(\$npsn)->firstOrFail()", $controller);
        $this->assertStringNotContainsString("\$user->gtk->load('kelas.siswaAktif')", $controller);
    }

    public function test_wali_scope_is_read_only_and_admin_scope_keeps_management_actions(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SekolahAsalController.php');
        $index = file_get_contents($this->root.'/resources/views/admin/sekolah-asal/index.blade.php');
        $show = file_get_contents($this->root.'/resources/views/admin/sekolah-asal/show.blade.php');

        $this->assertStringContainsString('Akun GTK Wali Kelas hanya memiliki akses baca.', $controller);
        $this->assertStringContainsString("!\$isWaliScope && auth()->user()->can('edit-siswa')", $controller);
        $this->assertStringContainsString('@if($canEnrich)', $index);
        $this->assertStringContainsString('@if($canEnrich)', $show);
        $this->assertStringContainsString("route('admin.gtk.wali.siswa.show'", $controller);
    }

    public function test_sidebar_places_school_origin_in_the_correct_role_section(): void
    {
        $menu = file_get_contents($this->root.'/config/adminlte.php');
        $gates = file_get_contents($this->root.'/app/Providers/AuthServiceProvider.php');

        $this->assertSame(1, substr_count($menu, "'can' => 'sidebar-school-origin-global'"));
        $this->assertStringContainsString("'can' => 'sidebar-wali-kelas-menu'", $menu);
        $this->assertStringContainsString("Gate::define('sidebar-school-origin-global'", $gates);
    }
}
