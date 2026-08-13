<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GlobalDataTableUiTest extends TestCase
{
    public function test_admin_layout_installs_compact_adaptive_datatable_standards(): void
    {
        $root = dirname(__DIR__, 2);
        $master = file_get_contents($root.'/resources/views/vendor/adminlte/master.blade.php');
        $standards = file_get_contents($root.'/resources/views/vendor/adminlte/partials/table-ui-standards.blade.php');

        $this->assertStringContainsString("@include('adminlte::partials.table-ui-standards')", $master);
        $this->assertStringContainsString('.dataTables_wrapper table.dataTable thead th', $standards);
        $this->assertStringContainsString('.dataTables_wrapper table.dataTable tbody td', $standards);
        $this->assertStringContainsString('tables.columns.adjust()', $standards);
        $this->assertStringContainsString('ResizeObserver', $standards);
        $this->assertStringContainsString('shown.lte.pushmenu.simansaTableUi', $standards);
        $this->assertStringContainsString("lengthMenu: 'Tampilkan _MENU_ data'", $standards);
    }

    public function test_face_data_table_groups_related_metadata_into_compact_columns(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/absensi/face-verification.blade.php');

        $this->assertStringContainsString('<th>Identitas</th>', $view);
        $this->assertStringContainsString('<th>Data Capture</th>', $view);
        $this->assertStringContainsString('<th>Verifikasi</th>', $view);
        $this->assertStringContainsString('face-identity__copy', $view);
        $this->assertStringContainsString('face-capture-meta', $view);
        $this->assertStringContainsString('face-verification-meta', $view);
        $this->assertStringContainsString('Descriptor Biometrik', $view);
        $this->assertStringContainsString('Foto Tersimpan', $view);
        $this->assertStringContainsString('registrasi format lama', $view);
        $this->assertStringNotContainsString('<th>Angle</th>', $view);
    }

    public function test_face_approval_header_uses_compact_semantic_switcher(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/absensi/face-verification.blade.php');

        $this->assertStringContainsString('face-approval-hero__eyebrow', $view);
        $this->assertStringContainsString('Kontrol Identitas Biometrik', $view);
        $this->assertStringContainsString('face-approval-actions', $view);
        $this->assertStringContainsString('btn btn-sm', $view);
        $this->assertStringContainsString("\$typeKey === 'gtk' ? 'success' : 'primary'", $view);
        $this->assertStringContainsString("'btn-'.\$buttonVariant : 'btn-outline-'.\$buttonVariant", $view);
        $this->assertStringContainsString('aria-current="page"', $view);
        $this->assertStringNotContainsString('Face Approval', $view);
        $this->assertStringNotContainsString('face-type-switch__item', $view);
        $this->assertStringNotContainsString('btn-group btn-group-sm', $view);
    }
}
