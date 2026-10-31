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
        $this->assertStringContainsString("students-attendance", $routes);
        $this->assertStringContainsString("TeacherJournal::create", $controller);
        $this->assertStringContainsString("where('user_id', Auth::id())", $controller);
        $this->assertStringContainsString("syncStudentAttendance", $controller);
        $this->assertStringContainsString("studentsAttendance", $controller);
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
        $this->assertStringContainsString('Presensi Kehadiran Siswa', $html);
    }

    public function test_journal_pdf_template_renders_properly(): void
    {
        $user = new \App\Models\User(['name' => 'Guru Pengampu']);
        $html = view('admin.gtk.journals.pdf', [
            'user' => $user,
            'gtk' => null,
            'year' => null,
            'dari' => now()->startOfMonth(),
            'sampai' => now(),
            'journals' => collect([]),
            'kepalaMadrasah' => null,
            'setting' => null,
            'stats' => [
                'total_entries' => 0,
                'total_hours' => 0,
                'teaching_count' => 0,
                'homeroom_count' => 0,
                'avg_attendance' => 100,
            ],
        ])->render();

        $this->assertStringContainsString('LAPORAN JURNAL KEGIATAN & PRESENSI PEMBELAJARAN GURU', $html);
        $this->assertStringContainsString('Kepala Madrasah', $html);
        $this->assertStringContainsString('Guru Pengampu / Wali Kelas', $html);
    }
}
