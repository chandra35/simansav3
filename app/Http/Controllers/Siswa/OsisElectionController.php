<?php

namespace App\Http\Controllers\Siswa;

use App\Exceptions\InvalidVotePasswordException;
use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\TahunPelajaran;
use App\Services\OsisElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use RuntimeException;

class OsisElectionController extends Controller
{
    private const PASSWORD_ATTEMPTS = 5;

    private const PASSWORD_DECAY_SECONDS = 60;

    public function __construct(private readonly OsisElectionService $service) {}

    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa;
        abort_unless($siswa, 403);
        $year = TahunPelajaran::query()->where('is_active', true)->first();
        $election = $year ? OsisElection::query()->where('tahun_pelajaran_id', $year->id)
            ->whereIn('status', ['published', 'paused', 'closed'])->latest('starts_at')->first() : null;
        $voter = $election?->voters()->where('user_id', $request->user()->id)->first();
        if ($election) {
            $election->load(['packages.election', 'packages.chairman.kelasSaatIni', 'packages.viceChairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni']);
        }
        $ownPackageIds = $election && $voter?->is_candidate ? $election->packages->filter(fn ($p) => in_array($siswa->id, $p->candidateIds(), true))->pluck('id') : collect();
        $results = $election?->results_visible
            ? $election->packages->map(fn ($p) => ['package' => $p, 'votes' => $p->ballots()->count()])->sortByDesc('votes')->values()
            : collect();

        return view('siswa.osis-election.index', [
            'participantName' => $siswa->nama_lengkap,
            'election' => $election,
            'voter' => $voter,
            'ownPackageIds' => $ownPackageIds,
            'results' => $results,
            'voteRoute' => $election ? route('siswa.osis-election.vote', $election) : null,
        ]);
    }

    public function vote(Request $request, OsisElection $election): RedirectResponse
    {
        $data = $request->validate(['package_id' => ['required', 'exists:osis_packages,id'], 'password' => ['required', 'string', 'max:255']]);
        $siswa = $request->user()->siswa;
        abort_unless($siswa, 403);
        $package = OsisPackage::findOrFail($data['package_id']);
        $rateLimitKey = "osis-vote-password:{$election->id}:{$request->user()->id}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::PASSWORD_ATTEMPTS)) {
            $seconds = max(1, RateLimiter::availableIn($rateLimitKey));

            return back()
                ->withInput($request->except('password'))
                ->with('error', "Terlalu banyak percobaan password. Tunggu {$seconds} detik lalu coba kembali.");
        }

        try {
            $receipt = $this->service->vote($election, $request->user(), $package, $data['password']);
            RateLimiter::clear($rateLimitKey);

            return back()->with('vote_success', $receipt);
        } catch (InvalidVotePasswordException $e) {
            RateLimiter::hit($rateLimitKey, self::PASSWORD_DECAY_SECONDS);

            return back()
                ->withInput($request->except('password'))
                ->with('error', $e->getMessage());
        } catch (RuntimeException $e) {
            // Password sudah lolos jika eksekusi mencapai validasi domain di service.
            RateLimiter::clear($rateLimitKey);

            return back()->with('error', $e->getMessage());
        }
    }
}
