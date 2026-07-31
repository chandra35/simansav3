<?php

namespace Tests\Unit;

use App\Exceptions\InvalidVotePasswordException;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\User;
use App\Services\OsisElectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
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

    public function test_draft_paused_and_closed_elections_cannot_accept_votes(): void
    {
        Carbon::setTestNow('2026-08-17 09:00:00');
        foreach (['draft', 'paused', 'closed'] as $status) {
            $election = new OsisElection([
                'status' => $status,
                'starts_at' => '2026-08-17 08:00:00',
                'ends_at' => '2026-08-17 12:00:00',
            ]);

            $this->assertFalse($election->is_open);
            if ($status === 'paused') {
                $this->assertSame('paused', $election->phase);
            }
        }
    }

    public function test_new_and_existing_candidate_role_defaults_remain_compatible(): void
    {
        $newElection = new OsisElection;
        $existingElection = new OsisElection([
            'candidate_roles' => ['chairman', 'secretary', 'treasurer'],
        ]);

        $this->assertSame(['chairman', 'vice_chairman'], $newElection->candidateRoleKeys());
        $this->assertSame(
            ['chairman', 'secretary', 'treasurer'],
            $existingElection->candidateRoleKeys()
        );
    }

    public function test_results_are_hidden_until_admin_publishes_them(): void
    {
        $election = new OsisElection(['result_published_at' => null]);
        $this->assertFalse($election->results_visible);

        $election->result_published_at = now();
        $this->assertTrue($election->results_visible);
    }

    public function test_wrong_vote_password_uses_specific_exception_before_ballot_is_processed(): void
    {
        Carbon::setTestNow('2026-08-17 09:00:00');

        $election = new OsisElection([
            'status' => 'published',
            'starts_at' => '2026-08-17 08:00:00',
            'ends_at' => '2026-08-17 12:00:00',
        ]);
        $election->id = '00000000-0000-0000-0000-000000000001';

        $package = new OsisPackage;
        $package->id = '00000000-0000-0000-0000-000000000002';
        $package->election_id = $election->id;

        $user = new User([
            'name' => 'Pemilih',
            'username' => 'pemilih-test',
            'email' => 'pemilih@example.test',
            'password' => Hash::make('password-benar'),
            'role' => 'siswa',
        ]);

        $this->expectException(InvalidVotePasswordException::class);
        $this->expectExceptionMessage('Password akun tidak sesuai.');

        app(OsisElectionService::class)->vote($election, $user, $package, 'password-salah');
    }
}
