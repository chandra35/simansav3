<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StudentClassLinkAccessTest extends TestCase
{
    public function test_student_table_links_class_only_with_detail_class_permission(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaController.php');

        $this->assertStringContainsString(
            "\$request->user()->can('view-detail-kelas')",
            $controller
        );
        $this->assertStringContainsString(
            "if (\$kelasAktif && \$canViewDetailKelas)",
            $controller
        );
        $this->assertStringContainsString(
            "route('admin.kelas.show', \$kelasAktif)",
            $controller
        );
        $this->assertStringContainsString(
            'Anda tidak memiliki akses detail rombel',
            $controller
        );
        $this->assertStringContainsString(
            'aria-disabled="true"',
            $controller
        );
    }
}
