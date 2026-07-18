<?php

namespace Tests\Unit;

use App\Models\OsisElection;
use Carbon\Carbon;
use Tests\TestCase;

class OsisElectionStateTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_published_election_follows_its_schedule(): void
    {
        Carbon::setTestNow('2026-08-17 09:00:00');
        $election = new OsisElection([
            'status' => 'published',
            'starts_at' => '2026-08-17 08:00:00',
            'ends_at' => '2026-08-17 12:00:00',
        ]);

        $this->assertSame('open', $election->phase);
        $this->assertTrue($election->is_open);
    }

    public function test_draft_and_closed_elections_cannot_accept_votes(): void
    {
        Carbon::setTestNow('2026-08-17 09:00:00');
        foreach (['draft', 'closed'] as $status) {
            $election = new OsisElection([
                'status' => $status,
                'starts_at' => '2026-08-17 08:00:00',
                'ends_at' => '2026-08-17 12:00:00',
            ]);

            $this->assertFalse($election->is_open);
        }
    }

    public function test_results_are_hidden_until_admin_publishes_them(): void
    {
        $election = new OsisElection(['result_published_at' => null]);
        $this->assertFalse($election->results_visible);

        $election->result_published_at = now();
        $this->assertTrue($election->results_visible);
    }
}
