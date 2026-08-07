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
        $gtk = ['id' => 'gtk-1', 'grade' => null, 'class_id' => null, 'gtk_type' => 'Guru Mapel', 'gtk_category' => 'Pendidik', 'roles' => ['GTK', 'Wali Kelas']];

        $this->assertTrue($service->matchesTarget($student, (object) ['scope_type' => 'tingkat', 'scope_value' => '12']));
        $this->assertTrue($service->matchesTarget($student, (object) ['scope_type' => 'kelas', 'scope_value' => 'kelas-xii-a']));
        $this->assertFalse($service->matchesTarget($student, (object) ['scope_type' => 'tingkat', 'scope_value' => '11']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'jenis_ptk', 'scope_value' => 'guru mapel']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'kategori_ptk', 'scope_value' => 'Pendidik']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'gtk', 'scope_value' => 'gtk-1']));
        $this->assertTrue($service->matchesTarget($gtk, (object) ['scope_type' => 'role', 'scope_value' => 'wali kelas']));
    }

    public function test_description_editor_content_is_sanitized_and_readable(): void
    {
        $polling = new Polling;
        $polling->description = '<p onclick="evil()"><strong>Tujuan</strong></p><script>alert(1)</script><ul class="x"><li>Baris kedua</li></ul>';

        $this->assertSame('<p><strong>Tujuan</strong></p><ul><li>Baris kedua</li></ul>', $polling->description);
        $this->assertStringNotContainsString('onclick', $polling->description_html);
        $this->assertStringNotContainsString('<script', $polling->description_html);
        $this->assertSame("Tujuan\nBaris kedua", $polling->description_plain);

        $polling->description = "Baris satu\nBaris dua";
        $this->assertStringContainsString('<br', $polling->description_html);
    }

    public function test_module_has_scope_guards_reporting_and_gentle_reminder(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = file_get_contents($root.'/database/migrations/2026_08_03_090000_create_polling_module_tables.php');
        $historyMigration = file_get_contents($root.'/database/migrations/2026_08_03_110000_add_history_metadata_to_pollings.php');
        $lockMigration = file_get_contents($root.'/database/migrations/2026_08_03_150000_add_lock_workflow_to_polling_responses.php');
        $routes = file_get_contents($root.'/routes/web.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/PollingResponseController.php');
        $adminController = file_get_contents($root.'/app/Http/Controllers/Admin/PollingController.php');
        $menu = file_get_contents($root.'/config/adminlte.php');
        $reminder = file_get_contents($root.'/resources/views/partials/polling-reminder.blade.php');

        $this->assertStringContainsString("\$table->unique(['polling_id', 'user_id'])", $migration);
        $this->assertStringContainsString('polling_answer_options', $migration);
        $this->assertStringContainsString('tahun_pelajaran_snapshot', $historyMigration);
        $this->assertStringContainsString('semester_snapshot', $historyMigration);
        $this->assertStringContainsString('source_polling_id', $historyMigration);
        $this->assertStringContainsString('unlock_requested_at', $lockMigration);
        $this->assertStringContainsString('unlocked_by', $lockMigration);
        $this->assertStringContainsString("permission:manage-polling", $routes);
        $this->assertStringContainsString("name('duplicate')", $routes);
        $this->assertStringContainsString("name('responses.unlock')", $routes);
        $this->assertStringContainsString("name('polling.unlock-request')", $routes);
        $this->assertStringContainsString('isEligible($polling, $request->user())', $controller);
        $this->assertStringContainsString("contains('id', \$optionId)", $controller);
        $this->assertStringContainsString('PollingReportExport', $adminController);
        $this->assertStringContainsString("Pdf::loadView('admin.polling.pdf'", $adminController);
        $this->assertStringContainsString("'can' => 'sidebar-active-polling'", $menu);
        $this->assertStringContainsString('Swal.fire', $reminder);
        $this->assertStringContainsString('snooze_url', $reminder);
        $this->assertStringContainsString('studentElectionOverlay', $reminder);
        $this->assertStringContainsString('simansa:osis-notice-dismissed', $reminder);
        $this->assertStringContainsString('window.setTimeout(showReminder, 500)', $reminder);
    }

    public function test_builder_and_responder_are_responsive_and_include_tka_preset(): void
    {
        $root = dirname(__DIR__, 2);
        $builder = file_get_contents($root.'/resources/views/admin/polling/form.blade.php');
        $respondent = file_get_contents($root.'/resources/views/polling/respondent/show.blade.php');
        $report = file_get_contents($root.'/resources/views/admin/polling/show.blade.php');
        $history = file_get_contents($root.'/resources/views/admin/polling/index.blade.php');
        $audienceService = file_get_contents($root.'/app/Services/PollingAudienceService.php');

        $this->assertStringContainsString('Preset TKA Kelas XII', $builder);
        $this->assertStringContainsString('Survei Kepuasan', $builder);
        $this->assertStringContainsString('Konfirmasi Kegiatan', $builder);
        $this->assertStringContainsString('source_polling_id', $builder);
        $this->assertStringContainsString('id="previewPolling"', $builder);
        $this->assertStringContainsString('id="pollingPreviewModal"', $builder);
        $this->assertStringContainsString('Deskripsi & Petunjuk', $builder);
        $this->assertStringContainsString('summernote@0.8.18', $builder);
        $this->assertStringContainsString('function safeRichText', $builder);
        $this->assertStringContainsString('function renderPreview()', $builder);
        $this->assertStringContainsString('overflow:visible;position:relative;z-index:20', $builder);
        $this->assertStringContainsString('min_selections:2,max_selections:2', $builder);
        $this->assertStringContainsString('Matematika Tingkat Lanjut', $builder);
        $this->assertStringContainsString('Bahasa Mandarin', $builder);
        $this->assertStringContainsString('student_grades[]', $builder);
        $this->assertStringContainsString('student_classes[]', $builder);
        $this->assertStringContainsString('id="allClasses"', $builder);
        $this->assertStringContainsString('name="gtk_categories[]"', $builder);
        $this->assertStringContainsString('name="gtks[]"', $builder);
        $this->assertStringContainsString('id="gtkTargetModal"', $builder);
        $this->assertStringContainsString('data-grade="{{ $class->tingkat }}"', $builder);
        $this->assertStringContainsString('function filterClasses()', $builder);
        $this->assertStringContainsString('function filterGtks()', $builder);
        $this->assertStringContainsString('@media(max-width:575.98px)', $builder);
        $this->assertStringContainsString('option-grid', $respondent);
        $this->assertStringContainsString('description_html', $respondent);
        $this->assertStringContainsString('Minta Buka Kunci', $respondent);
        $this->assertStringContainsString('@media(max-width:575.98px)', $respondent);
        $this->assertStringContainsString('Mapel Pilihan', $report);
        $this->assertStringContainsString('polling-stat-link', $report);
        $this->assertStringContainsString('dataTables.responsive.min.js', $report);
        $this->assertStringContainsString("window.matchMedia('(max-width: 767.98px)').matches", $report);
        $this->assertStringContainsString("responsive:useMobileResponsiveTable?{details:{type:'inline',target:0,renderer:mobileDetailsRenderer}}:false", $report);
        $this->assertStringContainsString('table-layout:auto!important', $report);
        $this->assertStringContainsString('polling-mobile-detail__item', $report);
        $this->assertStringContainsString('polling-col-respondent', $report);
        $this->assertStringContainsString('polling-mobile-toggle', $history);
        $this->assertStringContainsString('polling-mobile-detail-row', $history);
        $this->assertStringContainsString('polling-mobile-actions', $history);
        $this->assertStringNotContainsString('cdn.datatables.net/plug-ins', $report);
        $this->assertStringContainsString("relationLoaded('kelasTahunAktif')", $audienceService);
    }
}
