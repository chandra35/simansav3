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

    public function test_journal_views_compile_and_render_without_syntax_errors(): void
    {
        $journals = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]),
            0,
            15,
            1,
            ['path' => route('admin.gtk.journals.index')]
        );

        $html = view('admin.gtk.journals.index', [
            'summary' => [
                'total' => 0,
                'teaching' => 0,
                'homeroom' => 0,
                'minutes' => 0,
            ],
            'waliClasses' => collect([]),
            'schedules' => collect([]),
            'journals' => $journals,
            'jenis' => '',
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->toDateString(),
        ])->render();

        $this->assertStringContainsString('Pusat dokumentasi guru', $html);
        $this->assertStringContainsString('Belum ada jurnal pada periode ini', $html);
    }
}
