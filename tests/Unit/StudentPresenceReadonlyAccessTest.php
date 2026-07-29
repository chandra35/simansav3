<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentPresenceReadonlyAccessTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_student_table_includes_presence_for_every_viewer(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SiswaController.php');
        $view = file_get_contents($this->root.'/resources/views/admin/siswa/index.blade.php');
        $model = file_get_contents($this->root.'/app/Models/Siswa.php');

        $this->assertStringContainsString("'keberadaan' => \$this->getKeberadaanBadge(\$kelasAktif)", $controller);
        $this->assertStringContainsString('<th class="text-center">Keberadaan</th>', $view);
        $this->assertStringContainsString("{ data: 'keberadaan'", $view);
        $this->assertStringContainsString("'keberadaan_diverifikasi_at'", $model);
        $this->assertStringContainsString("'keberadaan_diverifikasi_by'", $model);
    }

    public function test_emis_and_presence_controls_are_buttons_only_for_super_admin(): void
    {
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/SiswaController.php');
        $routes = file_get_contents($this->root.'/routes/web.php');

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($controller, "if (! Auth::user()?->hasRole('Super Admin'))")
        );
        $this->assertStringContainsString('class="btn btn-xs btn-toggle-keberadaan', $controller);
        $this->assertStringContainsString('class="btn btn-success btn-xs btn-toggle-emis', $controller);
        $this->assertStringContainsString("toggle-emis-registered', [AdminSiswaController::class, 'toggleEmisRegistered']", $routes);
        $this->assertStringContainsString("->middleware('can:super-admin-access')", $routes);
    }

    public function test_class_detail_keeps_both_statuses_visible_but_mutations_super_admin_only(): void
    {
        $view = file_get_contents($this->root.'/resources/views/admin/kelas/show.blade.php');
        $controller = file_get_contents($this->root.'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString('<th class="text-center">Keberadaan</th>', $view);
        $this->assertStringContainsString('<th class="text-center">EMIS</th>', $view);
        $this->assertStringContainsString("auth()->user()->hasRole('Super Admin')", $view);
        $this->assertStringContainsString('Belum diverifikasi', $view);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($controller, "abort_unless(Auth::user()?->hasRole('Super Admin'), 403)")
        );
    }
}
