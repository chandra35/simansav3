<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OsisElectionExperienceTest extends TestCase
{
    public function test_only_one_unfinished_election_can_be_managed(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $index = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/index.blade.php');

        $this->assertStringContainsString("whereIn('status', ['draft', 'published', 'paused'])", $controller);
        $this->assertStringContainsString("Cache::lock('osis-election-single-ongoing'", $controller);
        $this->assertStringContainsString('Hanya satu pemilihan yang boleh dikelola', $controller);
        $this->assertStringContainsString('$ongoingElection', $index);
        $this->assertStringContainsString('Buka Pemilihan Aktif', $index);
    }

    public function test_admin_has_live_polling_and_visual_candidate_picker(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');

        $this->assertStringContainsString("name('live-polling')", $routes);
        $this->assertStringContainsString("name('candidates')", $routes);
        $this->assertStringContainsString('function livePolling', $controller);
        $this->assertStringContainsString('function candidateOptions', $controller);
        $this->assertStringContainsString('package-live-result', $view);
        $this->assertStringContainsString('Live Polling Paket', $view);
        $this->assertStringContainsString('candidate-browser__grid', $view);
        $this->assertStringContainsString('setInterval(()=>{if(!document.hidden)refreshLivePolling()},3000)', $view);
        $this->assertStringContainsString("params.append('exclude_ids[]',id)", $view);
        $this->assertStringContainsString("->where('tingkat', 11)", $controller);
        $this->assertStringContainsString('KHUSUS KELAS XI', $view);
        $this->assertStringContainsString('perPage: 12', $controller);
        $this->assertStringContainsString("'has_more' => \$paginator->hasMorePages()", $controller);
        $this->assertStringContainsString("$('#candidateBrowserGrid').on('scroll'", $view);
        $this->assertStringContainsString('candidate-option__body', $view);
        $this->assertStringContainsString('width:58px;height:72px', $view);
        $this->assertStringContainsString('Live Poll Fullscreen', $view);
        $this->assertStringContainsString('target="_blank" rel="noopener"', $view);
    }

    public function test_voter_is_still_limited_to_one_irreversible_vote(): void
    {
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/OsisElectionService.php');

        $this->assertStringContainsString('->lockForUpdate()->first()', $service);
        $this->assertStringContainsString('if ($voter->has_voted)', $service);
        $this->assertStringContainsString("'has_voted' => true", $service);
    }

    public function test_voter_page_uses_large_portrait_candidate_cards(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringContainsString('aspect-ratio:4/5', $view);
        $this->assertStringContainsString('width:min(100%,230px)', $view);
        $this->assertStringContainsString('<div class="col-12 mb-4">', $view);
    }

    public function test_student_vote_has_friendly_password_limiter_and_casting_animation(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Siswa/OsisElectionController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringNotContainsString(
            "OsisElectionController::class, 'vote'])->middleware('throttle:5,1')",
            $routes
        );
        $this->assertStringContainsString('RateLimiter::tooManyAttempts', $controller);
        $this->assertStringContainsString('RateLimiter::hit', $controller);
        $this->assertStringContainsString('RateLimiter::clear', $controller);
        $this->assertStringContainsString('Terlalu banyak percobaan password.', $controller);
        $this->assertStringContainsString('vote-casting-overlay', $view);
        $this->assertStringContainsString('voteStamp', $view);
        $this->assertStringContainsString('voteSubmitting', $view);
        $this->assertStringContainsString('HTMLFormElement.prototype.submit.call(form)', $view);
    }

    public function test_all_student_pages_receive_election_overlay_and_countdown(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/AppServiceProvider.php');
        $layout = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/vendor/adminlte/partials/cwrapper/cwrapper-default.blade.php'
        );
        $overlay = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/partials/student-election-overlay.blade.php'
        );

        $this->assertStringContainsString("View::composer('partials.student-election-overlay'", $provider);
        $this->assertStringContainsString("whereIn('status', ['published', 'paused'])", $provider);
        $this->assertStringContainsString("->where('ends_at', '>', now())", $provider);
        $this->assertStringContainsString("@include('partials.student-election-overlay')", $layout);
        $this->assertStringContainsString('student-election-overlay', $overlay);
        $this->assertStringContainsString('sessionStorage', $overlay);
        $this->assertStringContainsString('PEMILIHAN SEGERA DIBUKA', $overlay);
        $this->assertStringContainsString('PEMILIHAN SEDANG BERLANGSUNG', $overlay);
        $this->assertStringContainsString('window.setInterval(update, 1000)', $overlay);
        $this->assertStringContainsString('student-election-pill', $overlay);
    }

    public function test_public_live_polling_is_anonymous_fullscreen_and_resource_aware(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicOsisPollingController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/osis-polling.blade.php');

        $this->assertStringContainsString("Route::get('/live-polling-osis'", $routes);
        $this->assertStringContainsString("middleware('throttle:30,1')", $routes);
        $this->assertStringContainsString('TahunPelajaran::active()', $controller);
        $this->assertStringContainsString("->where('status', 'paused')", $controller);
        $this->assertStringContainsString("\$published->where('status', 'published')", $controller);
        $this->assertStringNotContainsString("->where('starts_at', '<=', now())", $controller);
        $this->assertStringContainsString("'phase' => \$election->phase", $controller);
        $this->assertStringContainsString("'starts_at' => \$election->starts_at->toIso8601String()", $controller);
        $this->assertStringContainsString('Cache::remember(', $controller);
        $this->assertStringNotContainsString('participant_id', $controller);
        $this->assertStringNotContainsString('<form', $view);
        $this->assertStringContainsString('height:100dvh', $view);
        $this->assertStringContainsString('grid-template-rows:auto auto minmax(0,1fr) auto', $view);
        $this->assertStringContainsString('#emptyState[hidden]{display:none}', $view);
        $this->assertStringContainsString('@media(max-height:760px) and (min-width:901px)', $view);
        $this->assertStringContainsString('body{overflow-x:hidden;overflow-y:auto}', $view);
        $this->assertStringContainsString('Math.min(Math.max(packages.length,1),6)', $view);
        $this->assertStringContainsString('Identitas pemilih tidak pernah dipublikasikan', $view);
        $this->assertStringContainsString('function renderPackages(packages)', $view);
        $this->assertStringContainsString('Logo MAN 1 Metro', $view);
        $this->assertStringContainsString('AppSetting::first()?->logo_sekolah_url', $controller);
        $this->assertStringContainsString("phase==='scheduled'", $view);
        $this->assertStringContainsString('Pemungutan suara dimulai dalam', $view);
        $this->assertStringContainsString('requestFullscreen?.()', $view);
    }

    public function test_candidate_roles_pause_and_animated_ranking_are_configurable(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/OsisElection.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $form = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/form.blade.php');
        $show = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');
        $public = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/osis-polling.blade.php');

        foreach (['chairman', 'vice_chairman', 'secretary', 'treasurer'] as $role) {
            $this->assertStringContainsString("'{$role}' =>", $model);
        }
        $this->assertStringContainsString("'candidate_roles' => ['required', 'array', 'min:2', 'max:4']", $controller);
        $this->assertStringContainsString('candidate-role-options', $form);
        $this->assertStringContainsString('$roleDefinitions=$election->candidateRoleDefinitions()', $show);
        $this->assertStringContainsString("name('pause')", $routes);
        $this->assertStringContainsString("name('resume')", $routes);
        $this->assertStringContainsString('function reorderPackages(packages)', $public);
        $this->assertStringContainsString('card.animate?.([{transform:', $public);
        $this->assertStringContainsString("phase==='paused'?'DIJEDA'", $public);
        $this->assertStringContainsString('private function validatedPausedPackage', $controller);
        $this->assertStringContainsString('Edit Profil Paket', $show);
    }
}
