<?php

namespace Tests\Unit;

use App\Models\Polling;
use App\Services\PollingAudienceService;
use Carbon\Carbon;
use Tests\TestCase;

class PollingModuleArchitectureTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_published_polling_follows_its_schedule(): void
    {
        Carbon::setTestNow('2026-08-03 10:00:00');
        $polling = new Polling([
            'status' => 'published',
            'starts_at' => '2026-08-03 09:00:00',
            'ends_at' => '2026-08-03 12:00:00',
        ]);

        $this->assertTrue($polling->isOpen());
        $this->assertSame('open', $polling->phase);

        $polling->status = 'draft';
        $this->assertFalse($polling->isOpen());
        $this->assertSame('draft', $polling->phase);
    }

    public function test_target_matcher_supports_student_and_gtk_scopes(): void
    {
        $service = new PollingAudienceService;
        $student = ['grade' => 12, 'class_id' => 'kelas-xii-a', 'gtk_type' => null, 'roles' => ['Siswa']];
        $gtk = ['grade' => null, 'class_id' => null, 'gtk_type' => 'Guru Mapel', 'roles' => ['GTK', 'Wali Kelas']];

        $this->assertTrue($service->matchesTarget($student, (object) ['scope_type' => 'tingkat', 'scope_value' => '12']));
        $this->assertTrue($service->matchesTarget($student, (object) ['scope_type' => 'kelas', 'scope_value' => 'kelas-xii-a']));
        $this->assertFalse($service->matchesTarget($student, (object) ['scope_type' => 'tingkat', 'scope_value' => '11']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'jenis_ptk', 'scope_value' => 'guru mapel']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'role', 'scope_value' => 'wali kelas']));
    }

    public function test_module_has_scope_guards_reporting_and_gentle_reminder(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = file_get_contents($root.'/database/migrations/2026_08_03_090000_create_polling_module_tables.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/PollingResponseController.php');
        $adminController = file_get_contents($root.'/app/Http/Controllers/Admin/PollingController.php');
        $menu = file_get_contents($root.'/config/adminlte.php');
        $reminder = file_get_contents($root.'/resources/views/partials/polling-reminder.blade.php');

        $this->assertStringContainsString("\$table->unique(['polling_id', 'user_id'])", $migration);
        $this->assertStringContainsString('polling_answer_options', $migration);
        $this->assertStringContainsString("permission:manage-polling", $routes);
        $this->assertStringContainsString('isEligible($polling, $request->user())', $controller);
        $this->assertStringContainsString("contains('id', \$optionId)", $controller);
        $this->assertStringContainsString('PollingReportExport', $adminController);
        $this->assertStringContainsString("Pdf::loadView('admin.polling.pdf'", $adminController);
        $this->assertStringContainsString("'can' => 'sidebar-active-polling'", $menu);
        $this->assertStringContainsString('Swal.fire', $reminder);
        $this->assertStringContainsString('snooze_url', $reminder);
    }

    public function test_builder_and_responder_are_responsive_and_include_tka_preset(): void
    {
        $root = dirname(__DIR__, 2);
        $builder = file_get_contents($root.'/resources/views/admin/polling/form.blade.php');
        $respondent = file_get_contents($root.'/resources/views/polling/respondent/show.blade.php');

        $this->assertStringContainsString('Preset TKA Kelas XII', $builder);
        $this->assertStringContainsString('min_selections:2,max_selections:2', $builder);
        $this->assertStringContainsString('Matematika Tingkat Lanjut', $builder);
        $this->assertStringContainsString('Bahasa Mandarin', $builder);
        $this->assertStringContainsString('student_grades[]', $builder);
        $this->assertStringContainsString('student_classes[]', $builder);
        $this->assertStringContainsString('@media(max-width:575.98px)', $builder);
        $this->assertStringContainsString('option-grid', $respondent);
        $this->assertStringContainsString('@media(max-width:575.98px)', $respondent);
    }
}
