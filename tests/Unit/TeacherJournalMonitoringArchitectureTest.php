<?php

namespace Tests\Unit;

use App\Models\Gtk;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TeacherJournalMonitoringArchitectureTest extends TestCase
{
    public function test_monitoring_routes_and_controller_are_properly_configured(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/TeacherJournalMonitoringController.php'));

        $this->assertStringContainsString("prefix('monitoring-jurnal')", $routes);
        $this->assertStringContainsString("name('monitoring-jurnal.')", $routes);
        $this->assertStringContainsString("can:view-monitoring-teacher-journal", $routes);

        $this->assertStringContainsString('public function index', $controller);
        $this->assertStringContainsString('public function show', $controller);
        $this->assertStringContainsString('public function entries', $controller);
        $this->assertStringContainsString('public function exportTeacher', $controller);
        $this->assertStringContainsString('public function exportRekap', $controller);
        $this->assertStringContainsString("->setPaper('a4', 'landscape')", $controller);
    }

    public function test_user_and_gtk_models_have_teacher_journals_relationship(): void
    {
        $user = new User();
        $this->assertTrue(method_exists($user, 'teacherJournals'));

        $gtk = new Gtk();
        $this->assertTrue(method_exists($gtk, 'teacherJournals'));
    }

    public function test_permissions_and_gates_are_registered(): void
    {
        $permissionService = file_get_contents(app_path('Services/PermissionSyncService.php'));
        $authProvider = file_get_contents(app_path('Providers/AuthServiceProvider.php'));
        $adminlteConfig = file_get_contents(config_path('adminlte.php'));

        $this->assertStringContainsString("'monitoring-teacher-journal'", $permissionService);
        $this->assertStringContainsString("'view-monitoring-teacher-journal'", $authProvider);
        $this->assertStringContainsString("Monitoring Jurnal Guru", $adminlteConfig);
        $this->assertStringContainsString("admin.monitoring-jurnal.index", $adminlteConfig);
        $this->assertSame(1, substr_count($adminlteConfig, "'admin.monitoring-jurnal.index'"));
    }

    public function test_monitoring_index_view_compiles_and_renders(): void
    {
        $rows = collect([]);

        $html = view('admin.monitoring.journals.index', [
            'stats' => [
                'total_teachers' => 0,
                'active_teachers' => 0,
                'inactive_teachers' => 0,
                'participation_rate' => 0,
                'total_entries' => 0,
                'total_teaching' => 0,
                'total_homeroom' => 0,
                'total_hours' => 0,
                'overall_avg_attendance' => 0,
            ],
            'rows' => $rows,
            'teachers' => $rows,
            'year' => null,
            'years' => collect([]),
            'activeYear' => null,
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->toDateString(),
            'q' => '',
            'search' => '',
            'status' => 'all',
            'kategori' => 'pendidik',
            'sort' => 'most_active',
        ])->render();

        $this->assertStringContainsString('Monitoring Pengisian Jurnal Pembelajaran GTK', $html);
        $this->assertStringContainsString('Sudah Mengisi Jurnal', $html);
        $this->assertStringContainsString('Export Rekap PDF', $html);
    }

    public function test_monitoring_rekap_pdf_view_renders_cleanly(): void
    {
        $html = view('admin.monitoring.journals.rekap-pdf', [
            'setting' => (object) [
                'nama_madrasah' => 'MAN 1 KOTA BANDUNG',
                'kabupaten' => 'Bandung',
                'nama_kepala_madrasah' => 'Drs. H. Kepala Madrasah',
                'nip_kepala_madrasah' => '197001011995031001',
            ],
            'kepalaMadrasah' => null,
            'year' => (object) [
                'nama' => '2024/2025',
                'semester_aktif' => '1',
            ],
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->toDateString(),
            'stats' => [
                'total_teachers' => 1,
                'active_teachers' => 1,
                'inactive_teachers' => 0,
                'participation_rate' => 100,
                'total_entries' => 5,
                'total_teaching' => 4,
                'total_homeroom' => 1,
                'total_hours' => 10,
                'overall_avg_attendance' => 95,
            ],
            'rows' => collect([
                [
                    'nama' => 'Drs. Guru Teladan',
                    'nip' => '198001012005011001',
                    'jabatan' => 'Guru Madya',
                    'subjects' => collect(['Matematika']),
                    'teaching_count' => 4,
                    'homeroom_count' => 1,
                    'total_journals' => 5,
                    'total_hours' => 10,
                    'avg_attendance' => 95,
                    'last_journal_date' => now()->toDateString(),
                    'has_journals' => true,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Rekapitulasi Monitoring Jurnal Pembelajaran Guru', $html);
        $this->assertStringContainsString('Drs. Guru Teladan', $html);
        $this->assertStringContainsString('Sudah Mengisi', $html);
    }
}
