<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentStatisticsRoleScopeTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_gtk_homeroom_statistics_use_active_class_scope_for_every_query(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SiswaStatisticsController.php');

        $this->assertStringContainsString('waliClassIds()', $controller);
        $this->assertStringContainsString('StudentAccessScope::class', $controller);
        $this->assertStringContainsString("whereHas('kelasTahunAktif'", $controller);
        $this->assertStringContainsString("->when(\$classIds !== null, fn (\$query) => \$query->whereIn('id', \$classIds))", $controller);
        $this->assertStringContainsString('abort_if($isWaliScope, 404', $controller);
        $this->assertStringContainsString('authorizeSchoolInScope($sekolah, $classIds)', $controller);
    }

    public function test_gtk_homeroom_statistics_are_read_only_and_use_scoped_links(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SiswaStatisticsController.php');
        $view = file_get_contents($this->root.'/resources/views/admin/siswa/statistics.blade.php');
        $routes = file_get_contents($this->root.'/routes/web.php');
        $menu = file_get_contents($this->root.'/config/adminlte.php');
        $gates = file_get_contents($this->root.'/app/Providers/AuthServiceProvider.php');

        $this->assertGreaterThanOrEqual(2, substr_count($controller, 'Akun GTK Wali Kelas hanya memiliki akses baca.'));
        $this->assertStringContainsString("route('admin.gtk.wali.siswa.show'", $controller);
        $this->assertStringContainsString('@if($isWaliScope)', $view);
        $this->assertStringContainsString('@if($canManage)', $view);
        $this->assertStringContainsString("Route::middleware('permission:view-statistik-siswa')", $routes);
        $this->assertSame(1, substr_count($menu, "'can' => 'sidebar-student-statistics-global'"));
        $this->assertStringContainsString("Gate::define('sidebar-student-statistics-global'", $gates);
    }
}
