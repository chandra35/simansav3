<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WaliKelasAssignmentUiTest extends TestCase
{
    public function test_wali_kelas_candidates_are_active_teachers_with_current_class_metadata(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/KelasController.php');

        $this->assertStringContainsString("->where('is_active', true)", $controller);
        $this->assertStringContainsString("->where('kategori_ptk', 'Pendidik')", $controller);
        $this->assertStringContainsString(
            "->whereIn('jenis_ptk', ['Guru Mapel', 'Guru BK'])",
            $controller
        );
        $this->assertStringContainsString("'waliKelasRombelByUser'", $controller);
        $this->assertStringContainsString(
            'Wali kelas harus dipilih dari GTK aktif yang berstatus guru.',
            $controller
        );
    }

    public function test_wali_kelas_modal_has_searchable_friendly_dropdown_and_assignment_metadata(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/kelas/show.blade.php');

        $this->assertStringContainsString("@section('plugins.Select2', true)", $view);
        $this->assertStringContainsString("dropdownParent: \$('#modalWaliKelas')", $view);
        $this->assertStringContainsString('minimumResultsForSearch: 0', $view);
        $this->assertStringContainsString(
            '{{ $displayName }} | {{ $teacherType }} | {{ $assignmentText }}',
            $view
        );
        $this->assertStringContainsString('Belum menjadi wali kelas', $view);
        $this->assertStringNotContainsString('templateResult:', $view);
        $this->assertStringNotContainsString('templateSelection:', $view);
        $this->assertStringNotContainsString('.wali-kelas-modal__header', $view);
        $this->assertStringNotContainsString('.wali-option__assignment', $view);
        $this->assertStringNotContainsString('Debug modal Wali Kelas', $view);
    }
}
