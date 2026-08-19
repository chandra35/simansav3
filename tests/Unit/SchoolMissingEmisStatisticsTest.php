<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SchoolMissingEmisStatisticsTest extends TestCase
{
    public function test_school_statistics_exposes_students_not_yet_marked_in_emis(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/SiswaStatisticsController.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString('missing_emis_count', $controller);
        $this->assertStringContainsString('studentsMissingEmis', $controller);
        $this->assertStringContainsString("whereNull('siswa.emis_registered')", $controller);
        $this->assertStringContainsString("'siswa.nik'", $controller);
        $this->assertStringContainsString("'nik' => \$siswa->nik", $controller);
        $this->assertStringContainsString("'tanggal_lahir' => \$siswa->tanggal_lahir?->format('Y-m-d')", $controller);
        $this->assertStringContainsString('school-missing-emis', $routes);
    }

    public function test_school_statistics_has_a_responsive_detail_modal(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/siswa/statistics.blade.php');

        $this->assertStringContainsString('Belum Ada di EMIS', $view);
        $this->assertStringContainsString('schoolMissingEmisModal', $view);
        $this->assertStringContainsString('simansa-emis-student-grid', $view);
        $this->assertStringContainsString("NIK: \${escapeHtml(student.nik || '-')}", $view);
        $this->assertTrue(
            strpos($view, "NIK: \${escapeHtml(student.nik || '-')}")
                < strpos($view, "NISN: \${escapeHtml(student.nisn || '-')}")
        );
        $this->assertStringContainsString('NPSN', $view);
        $this->assertStringContainsString('NSM', $view);
        $this->assertStringContainsString('btn-modal-toggle-emis', $view);
        $this->assertStringContainsString('schoolMissingEmisIdentity', $view);
        $this->assertStringContainsString('simansa-emis-modal__school-meta', $view);
        $this->assertStringContainsString('#schoolMissingEmisModal .simansa-emis-modal__header', $view);
        $this->assertStringContainsString('background: #fff !important', $view);
        $this->assertStringContainsString('color: #0f172a !important', $view);
        $this->assertStringContainsString("content: '|'", $view);
        $this->assertStringContainsString('background: transparent !important', $view);
        $this->assertStringContainsString("<span>NPSN: \${escapeHtml(school.npsn || '-')}</span>", $view);
        $this->assertStringContainsString("<span>NSM: \${escapeHtml(school.nsm || '-')}</span>", $view);
        $this->assertStringNotContainsString("['Sekolah Asal', school.name || '-']", $view);
        $this->assertStringContainsString('simansa-school-metadata', $view);
        $this->assertStringContainsString('simansa-school-col-name', $view);
        $this->assertStringContainsString('table-layout: fixed', $view);
        $this->assertStringNotContainsString('<th>Status / Bentuk</th>', $view);
        $this->assertStringNotContainsString('<th>Akreditasi</th>', $view);
    }
}
