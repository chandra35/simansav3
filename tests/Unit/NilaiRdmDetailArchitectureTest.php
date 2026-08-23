<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NilaiRdmDetailArchitectureTest extends TestCase
{
    public function test_homeroom_rdm_detail_stays_scoped_and_read_only(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root.'/routes/web.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/NilaiRdmController.php');
        $view = file_get_contents($root.'/resources/views/admin/nilai-rdm/show.blade.php');

        $this->assertStringContainsString("name('nilai-rdm.show')", $routes);
        $this->assertStringContainsString("permission:view-nilai-rdm", $routes);
        $this->assertStringContainsString('public function show(Request $request, Siswa $siswa)', $controller);
        $this->assertStringContainsString("where('sumber_data', 'rdm_sync')", $controller);
        $this->assertStringContainsString("whereIn('kelas_id', \$classIds)", $controller);
        $this->assertStringContainsString('<details class="rdm-semester"', $view);
        $this->assertStringContainsString('rdm-score-cards', $view);
        $this->assertStringContainsString('d-none d-md-block', $view);
    }
}
