<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\AppSetting;
use App\Models\TahunPelajaran;
use App\Services\OsisElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use RuntimeException;

class GtkOsisElectionController extends Controller
{
    private const VOTE_ATTEMPTS = 5;
    private const VOTE_DECAY_SECONDS = 60;

    public function __construct(private readonly OsisElectionService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->gtk, 403);
        $year = TahunPelajaran::query()->active()->first();
        $election = $year ? OsisElection::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->where('status', 'published')
            ->where('include_gtk', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('starts_at')->first() : null;
        abort_unless($election, 404);
        $voter = $election->voters()->where('user_id', $user->id)->first();
        abort_unless($voter, 404);
        if ($election) {
            $election->load(['packages.election', 'packages.chairman.kelasSaatIni', 'packages.viceChairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni']);
        }
        $results = $election?->results_visible
            ? $election->packages->map(fn ($package) => ['package' => $package, 'votes' => $package->ballots()->count()])->sortByDesc('votes')->values()
            : collect();

        return view('siswa.osis-election.index', [
            'participantName' => $user->gtk->nama_lengkap ?: $user->name,
            'election' => $election,
            'voter' => $voter,
            'ownPackageIds' => collect(),
            'results' => $results,
            'voteRoute' => $election ? route('admin.gtk.osis-election.vote', $election) : null,
            'schoolLogo' => AppSetting::first()?->logo_sekolah_url
                ?? asset('vendor/adminlte/dist/img/logo-sekolah.png'),
        ]);
    }

    public function vote(Request $request, OsisElection $election): RedirectResponse
    {
        abort_unless($request->user()->gtk && $election->include_gtk, 403);
        $data = $request->validate([
            'package_id' => ['required', 'exists:osis_packages,id'],
            'confirmed' => ['accepted'],
        ]);
        $package = OsisPackage::findOrFail($data['package_id']);
        $rateLimitKey = "osis-vote-submit:{$election->id}:{$request->user()->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::VOTE_ATTEMPTS)) {
            $seconds = max(1, RateLimiter::availableIn($rateLimitKey));
            return back()->withInput($request->only('package_id'))->with('error', "Terlalu banyak permintaan pengiriman suara. Tunggu {$seconds} detik lalu coba kembali.");
        }
        try {
            RateLimiter::hit($rateLimitKey, self::VOTE_DECAY_SECONDS);
            $receipt = $this->service->vote($election, $request->user(), $package);
            RateLimiter::clear($rateLimitKey);
            return back()->with('vote_success', $receipt);
        } catch (RuntimeException $exception) {
            return back()->withInput($request->only('package_id'))->with('error', $exception->getMessage());
        }
    }
}
