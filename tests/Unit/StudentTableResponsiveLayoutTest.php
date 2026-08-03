<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentTableResponsiveLayoutTest extends TestCase
{
    public function test_student_table_uses_balanced_fixed_column_proportions(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/index.blade.php');

        $this->assertStringContainsString('table-layout: fixed;', $view);
        $this->assertStringContainsString('.simansa-siswa-table .siswa-col-nama', $view);
        $this->assertStringContainsString('width: 22% !important;', $view);
        $this->assertStringContainsString('.simansa-siswa-table .siswa-col-jk', $view);
        $this->assertStringContainsString('.simansa-siswa-table .siswa-col-kelas', $view);
        $this->assertStringContainsString("{ targets: 0, width: '6%'", $view);
        $this->assertStringContainsString("{ targets: 1, width: '22%' }", $view);
        $this->assertStringContainsString("{ targets: 2, width: '4%'  }", $view);
        $this->assertStringContainsString("{ targets: 3, width: '14%' }", $view);
        $this->assertStringContainsString('simansa-siswa-class-stack', $view);
    }

    public function test_student_table_fits_desktop_and_scrolls_safely_on_narrow_screens(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/index.blade.php');

        $this->assertStringContainsString('width: 100% !important;', $view);
        $this->assertStringContainsString('overflow-x: auto;', $view);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $view);
        $this->assertStringContainsString('overscroll-behavior-inline: contain;', $view);
        $this->assertStringContainsString('@media (max-width: 1199.98px)', $view);
        $this->assertStringContainsString('siswa-col-aksi', $view);
        $this->assertStringContainsString('flex-wrap: nowrap;', $view);
    }

    public function test_student_actions_use_a_right_aligned_bootstrap_dropdown(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');

        $this->assertStringContainsString('btn btn-sm btn-outline-primary dropdown-toggle', $controller);
        $this->assertStringContainsString('dropdown-menu dropdown-menu-right simansa-siswa-action-dropdown', $controller);
        $this->assertStringContainsString('fas fa-eye fa-fw', $controller);
        $this->assertStringContainsString('fas fa-edit fa-fw', $controller);
        $this->assertStringContainsString('fas fa-key fa-fw', $controller);
        $this->assertStringContainsString('fas fa-user-secret fa-fw', $controller);
        $this->assertStringContainsString('fas fa-trash-alt fa-fw', $controller);
    }
}
