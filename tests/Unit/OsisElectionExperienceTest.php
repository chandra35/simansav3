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

    public function test_draft_can_be_previewed_and_only_unpublished_closed_simulations_can_be_deleted(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $panel = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');
        $voterView = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringContainsString("name('preview')", $routes);
        $this->assertStringContainsString('public function preview', $controller);
        $this->assertStringContainsString('$election->status === \'closed\' && ! $election->results_visible', $controller);
        $this->assertStringContainsString('Pratinjau Pemilih', $panel);
        $this->assertStringContainsString('Hapus Simulasi', $panel);
        $this->assertStringContainsString('Mode pratinjau', $voterView);
        $this->assertStringContainsString('@endphp', $voterView);
        $this->assertStringNotContainsString('@php(', $voterView);
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
        $this->assertStringContainsString("'voter_id' => \$voter->id", $service);
        $this->assertStringContainsString('function unlockVote', $service);
    }

    public function test_admin_can_monitor_and_unlock_a_voter_before_election_closes(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');
        $this->assertStringContainsString("name('voters.unlock')", $routes);
        $this->assertStringContainsString('function unlockVoter', $controller);
        $this->assertStringContainsString('Monitoring Pemilih', $view);
        $this->assertStringContainsString('Belum Memilih', $view);
        $this->assertStringContainsString('unlock-voter', $view);
    }

    public function test_admin_can_safely_add_newly_eligible_students_to_a_running_dpt(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $service = file_get_contents(dirname(__DIR__, 2).'/app/Services/OsisElectionService.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');
        $syncStart = strpos($service, 'public function syncStudentVoters');
        $syncSection = substr($service, $syncStart, strpos($service, 'public function vote', $syncStart) - $syncStart);

        $this->assertStringContainsString("name('voters.sync-students')", $routes);
        $this->assertStringContainsString('function syncStudentVoters', $controller);
        $this->assertStringContainsString('function syncStudentVoters', $service);
        $this->assertStringContainsString('insertOrIgnore', $syncSection);
        $this->assertStringNotContainsString('->delete()', $syncSection);
        $this->assertStringContainsString('Update Data Siswa', $view);
    }

    public function test_voter_page_uses_large_portrait_candidate_cards(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringContainsString('aspect-ratio:4/5', $view);
        $this->assertStringContainsString('width:min(100%,230px)', $view);
        $this->assertStringContainsString('<div class="col-12 mb-4">', $view);
    }

    public function test_packages_support_campaign_photo_and_live_gallery(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2).'/database/migrations/2026_08_07_150000_add_campaign_photos_to_osis_packages.php');
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/OsisPackage.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $panel = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/show.blade.php');
        $studentView = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');
        $liveView = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/osis-polling.blade.php');

        $this->assertStringContainsString("campaign_photo", $migration);
        $this->assertStringContainsString("live_photos", $migration);
        $this->assertStringContainsString('getCampaignPhotoUrlAttribute', $model);
        $this->assertStringContainsString('getLivePhotoUrlsAttribute', $model);
        $this->assertStringContainsString('persistPackagePhotos', $controller);
        $this->assertStringContainsString('campaign-photo', $studentView);
        $this->assertStringContainsString('aspect-ratio:16/7', $studentView);
        $this->assertStringContainsString('package-campaign-photo', $panel);
        $this->assertStringContainsString('campaign-gallery', $liveView);
        $this->assertStringContainsString('rotateGalleries', $liveView);
        $this->assertStringContainsString('grid-template-rows:auto minmax(0,1fr) auto', $liveView);
        $this->assertStringContainsString('object-fit:contain', $liveView);
        $this->assertStringContainsString('showing-gallery', $liveView);
        $this->assertStringContainsString('campaign-gallery__backdrop', $liveView);
        $this->assertStringContainsString("'vision' => \$package->vision", file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicOsisPollingController.php'));
        $this->assertStringContainsString('package-story', $liveView);
        $this->assertStringContainsString('typeStory', $liveView);
        $this->assertStringContainsString('showing-story', $liveView);
        $this->assertStringContainsString('stopStory', $liveView);
        $this->assertStringContainsString('scheduleShowcase', $liveView);
        $this->assertStringContainsString("name('packages.campaign-photo.destroy')", $routes);
        $this->assertStringContainsString("name('packages.live-photo.destroy')", $routes);
        $this->assertStringContainsString('deletePackageCampaignPhoto', $controller);
        $this->assertStringContainsString('deletePackageLivePhoto', $controller);
        $this->assertStringContainsString('array_merge($existingPhotos', $controller);
        $this->assertStringContainsString('packageGalleryModal', $panel);
        $this->assertStringContainsString('package-gallery-open', $panel);
        $this->assertStringContainsString('packageMediaManagerTemplate', $panel);
        $this->assertStringContainsString('package-media-dropzone', $panel);
        $this->assertStringContainsString('packageCropModal', $panel);
        $this->assertStringContainsString('new window.Cropper', $panel);
        $this->assertStringContainsString('aspectRatio: NaN', $panel);
        $this->assertStringContainsString('activeCrop = null;', $panel);
        $this->assertStringContainsString('rgba(0,92,59,.42)', $panel);
        $this->assertStringContainsString('rgba(0,92,59,.44)', $studentView);
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

    public function test_entire_candidate_card_can_be_used_to_choose_a_package(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringContainsString('$canChoosePackage', $view);
        $this->assertStringContainsString("'is-selectable'", $view);
        $this->assertStringContainsString('class="card-choice-surface"', $view);
        $this->assertStringContainsString('aria-pressed="false"', $view);
        $this->assertStringContainsString('SENTUH PASLON', $view);
        $this->assertStringContainsString("$('.card-choice-surface').on('click'", $view);
        $this->assertStringContainsString('.platform-tabs{cursor:default;position:relative;z-index:6}', $view);
        $this->assertStringContainsString('.vote-package.is-selected', $view);
        $this->assertStringContainsString('--people-columns:2', $view);
        $this->assertStringContainsString('max-width:760px', $view);
    }

    public function test_voter_page_prioritizes_candidates_with_compact_election_information(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/osis-election/index.blade.php');

        $this->assertStringContainsString('Suara Anda rahasia', $view);
        $this->assertStringContainsString('class="vote-receipt"', $view);
        $this->assertStringContainsString('Informasi dan panduan pemilihan', $view);
        $this->assertStringContainsString('.vote-hero{padding:.72rem 1rem', $view);
        $this->assertStringContainsString('.vote-success{gap:.7rem;padding:.65rem .8rem', $view);
        $this->assertGreaterThan(
            strpos($view, '<div class="row vote-packages">'),
            strpos($view, '<details class="election-details mb-4">')
        );
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
        $this->assertStringContainsString("simansa:osis-notice-dismissed", $overlay);
        $this->assertStringContainsString('has-student-election-pill', $overlay);
        $this->assertStringContainsString('env(safe-area-inset-bottom)', $overlay);
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

    public function test_live_polling_result_bars_are_clear_and_animated_when_votes_change(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/osis-polling.blade.php');

        $this->assertStringContainsString('Statistik perolehan', $view);
        $this->assertStringContainsString('height:clamp(24px,2.9vh,36px)', $view);
        $this->assertStringContainsString('class="candidate-vote" data-candidate-votes', $view);
        $this->assertStringContainsString('suara paket', $view);
        $this->assertStringContainsString("card.querySelectorAll('[data-candidate-votes]')", $view);
        $this->assertStringContainsString('.bar.has-value span{min-width:18px}', $view);
        $this->assertStringContainsString('@keyframes barFlow', $view);
        $this->assertStringContainsString('@keyframes barShimmer', $view);
        $this->assertStringContainsString('@keyframes barBeacon', $view);
        $this->assertStringContainsString('@keyframes voteCountPop', $view);
        $this->assertStringContainsString('@keyframes candidateVotePulse', $view);
        $this->assertStringContainsString("bar.classList.toggle('has-value',nextVotes>0)", $view);
        $this->assertStringContainsString("card.classList.add('vote-updated')", $view);
        $this->assertStringContainsString('@media(prefers-reduced-motion:reduce)', $view);
        $this->assertStringNotContainsString('.package-grid[data-count="5"] .result-value span', $view);
    }

    public function test_live_polling_uses_a_subtle_school_logo_depth_watermark(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/public/osis-polling.blade.php');

        $this->assertStringContainsString('style="--school-watermark:url(\'{{ $schoolLogo }}\')"', $view);
        $this->assertStringContainsString('class="package-watermark" aria-hidden="true"', $view);
        $this->assertStringContainsString('opacity:.072', $view);
        $this->assertStringContainsString('mix-blend-mode:screen', $view);
        $this->assertStringContainsString('perspective(950px)', $view);
        $this->assertStringContainsString('@keyframes watermarkFloat', $view);
        $this->assertStringContainsString('.package-watermark,.result-value', $view);
        $this->assertStringContainsString('<span class="package-watermark" aria-hidden="true"></span>', $view);
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
