<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\TahunPelajaran;
use App\Services\OsisElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class GtkOsisElectionController extends Controller
{
    public function __construct(private readonly OsisElectionService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->gtk, 403);
        $year = TahunPelajaran::query()->active()->first();
        $election = $year ? OsisElection::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->whereIn('status', ['published', 'paused', 'closed'])
            ->where('include_gtk', true)
            ->latest('starts_at')->first() : null;
        $voter = $election?->voters()->where('user_id', $user->id)->first();
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
        ]);
    }

    public function vote(Request $request, OsisElection $election): RedirectResponse
    {
        abort_unless($request->user()->gtk && $election->include_gtk, 403);
        $data = $request->validate([
            'package_id' => ['required', 'exists:osis_packages,id'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $package = OsisPackage::findOrFail($data['package_id']);
        try {
            $receipt = $this->service->vote($election, $request->user(), $package, $data['password']);
            return back()->with('vote_success', $receipt);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
