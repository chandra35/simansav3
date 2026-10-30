<?php

namespace Tests\Unit;

use Tests\TestCase;

class TeacherJournalArchitectureTest extends TestCase
{
    public function test_journal_module_has_scoped_teacher_routes_and_pdf_export(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/TeacherJournalController.php'));

        $this->assertStringContainsString("prefix('gtk/jurnals')", $routes);
        $this->assertStringContainsString("permission:view-teacher-journal", $routes);
        $this->assertStringContainsString("TeacherJournal::create", $controller);
        $this->assertStringContainsString("where('user_id', Auth::id())", $controller);
        $this->assertStringContainsString("PDF::loadView", $controller);
        $this->assertStringContainsString("wali_kelas_id", $controller);
    }

    public function test_journal_permissions_and_wali_id_card_menu_are_registered(): void
    {
        $permissions = file_get_contents(app_path('Services/PermissionSyncService.php'));
        $menu = file_get_contents(config_path('adminlte.php'));

        $this->assertStringContainsString("'jurnal-guru'", $permissions);
        $this->assertStringContainsString("'export-teacher-journal'", $permissions);
        $this->assertStringContainsString("'Cetak ID Card Siswa'", $menu);
        $this->assertStringContainsString("'admin.cetak.id-card-siswa.index'", $menu);
    }
}
