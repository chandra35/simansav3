<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OsisElectionExperienceTest extends TestCase
{
    public function test_only_one_unfinished_election_can_be_managed(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/OsisElectionController.php');
        $index = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/osis-election/index.blade.php');

        $this->assertStringContainsString("whereIn('status', ['draft', 'published'])", $controller);
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
        $this->assertStringContainsString('live-polling__packages', $view);
        $this->assertStringContainsString('candidate-browser__grid', $view);
        $this->assertStringContainsString("setInterval(()=>{if(!document.hidden)refreshLivePolling()},3000)", $view);
        $this->assertStringContainsString("params.append('exclude_ids[]',id)", $view);
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
}
