<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\TahunPelajaran;
use App\Services\OsisElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OsisElectionController extends Controller
{
    public function __construct(private readonly OsisElectionService $service) {}

    public function index(Request $request): View
    {
        $siswa = $request->user()->siswa; abort_unless($siswa, 403);
        $year = TahunPelajaran::query()->where('is_active', true)->first();
        $election = $year ? OsisElection::query()->where('tahun_pelajaran_id', $year->id)
            ->whereIn('status', ['published', 'closed'])->latest('starts_at')->first() : null;
        $voter = $election?->voters()->where('siswa_id', $siswa->id)->first();
        if ($election) $election->load(['packages.chairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni']);
        $ownPackageIds = $election && $voter?->is_candidate ? $election->packages->filter(fn ($p) => in_array($siswa->id, $p->candidateIds(), true))->pluck('id') : collect();
        $results = $election?->results_visible
            ? $election->packages->map(fn ($p) => ['package' => $p, 'votes' => $p->ballots()->count()])->sortByDesc('votes')->values()
            : collect();
        return view('siswa.osis-election.index', compact('siswa', 'election', 'voter', 'ownPackageIds', 'results'));
    }

    public function vote(Request $request, OsisElection $election): RedirectResponse
    {
        $data = $request->validate(['package_id' => ['required', 'exists:osis_packages,id'], 'password' => ['required', 'string', 'max:255']]);
        $siswa = $request->user()->siswa; abort_unless($siswa, 403);
        $package = OsisPackage::findOrFail($data['package_id']);
        try {
            $receipt = $this->service->vote($election, $siswa, $package, $data['password']);
            return back()->with('vote_success', $receipt);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
