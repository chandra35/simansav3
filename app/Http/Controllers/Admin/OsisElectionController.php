<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\OsisElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class OsisElectionController extends Controller
{
    public function __construct(private readonly OsisElectionService $service) {}

    public function index(): View
    {
        $elections = OsisElection::query()->with('tahunPelajaran')
            ->withCount(['packages', 'voters', 'voters as voted_count' => fn ($q) => $q->where('has_voted', true)])
            ->latest('starts_at')->paginate(12);
        return view('admin.osis-election.index', compact('elections'));
    }

    public function create(): View
    {
        return view('admin.osis-election.form', ['election' => new OsisElection, 'years' => TahunPelajaran::latest('tahun_mulai')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedElection($request);
        $data += ['slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id];
        $election = OsisElection::create($data);
        Siswa::logCustomActivity('osis_election_created', "Membuat draft pemilihan: {$election->title}", $election);
        return redirect()->route('admin.osis-election.show', $election)->with('success', 'Pengaturan pemilihan berhasil dibuat. Tambahkan paket kandidat.');
    }

    public function show(OsisElection $election): View
    {
        $election->load(['tahunPelajaran', 'packages.chairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni'])
            ->loadCount([
                'voters',
                'voters as student_voters_count' => fn ($q) => $q->where('participant_type', 'student'),
                'voters as gtk_voters_count' => fn ($q) => $q->where('participant_type', 'gtk'),
                'voters as voted_count' => fn ($q) => $q->where('has_voted', true),
                'ballots',
            ]);
        $students = Siswa::query()->with('kelasSaatIni:id,nama_kelas,tingkat')->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($q) => $q->where('tahun_pelajaran_id', $election->tahun_pelajaran_id))
            ->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nisn', 'kelas_saat_ini_id', 'foto_profile']);
        $results = $election->phase === 'closed'
            ? $election->packages->map(fn ($package) => ['package' => $package, 'votes' => $package->ballots()->count()])
            : collect();
        return view('admin.osis-election.show', compact('election', 'students', 'results'));
    }

    public function edit(OsisElection $election): View
    {
        abort_unless($election->status === 'draft', 422, 'Pemilihan yang sudah dipublikasikan tidak dapat diedit.');
        return view('admin.osis-election.form', ['election' => $election, 'years' => TahunPelajaran::latest('tahun_mulai')->get()]);
    }

    public function update(Request $request, OsisElection $election): RedirectResponse
    {
        abort_unless($election->status === 'draft', 422);
        $election->update($this->validatedElection($request) + ['updated_by' => $request->user()->id]);
        Siswa::logCustomActivity('osis_election_updated', "Memperbarui pengaturan pemilihan: {$election->title}", $election);
        return redirect()->route('admin.osis-election.show', $election)->with('success', 'Pengaturan pemilihan diperbarui.');
    }

    public function destroy(OsisElection $election): RedirectResponse
    {
        abort_unless($election->status === 'draft', 422);
        $election->delete();
        return redirect()->route('admin.osis-election.index')->with('success', 'Draft pemilihan dihapus.');
    }

    public function storePackage(Request $request, OsisElection $election): RedirectResponse
    {
        $this->ensureDraft($election);
        $data = $this->validatedPackage($request, $election);
        $election->packages()->create($data);
        return back()->with('success', 'Paket kandidat berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, OsisElection $election, OsisPackage $package): RedirectResponse
    {
        $this->ensureDraft($election); abort_unless($package->election_id === $election->id, 404);
        $package->update($this->validatedPackage($request, $election, $package));
        return back()->with('success', 'Paket kandidat berhasil diperbarui.');
    }

    public function destroyPackage(OsisElection $election, OsisPackage $package): RedirectResponse
    {
        $this->ensureDraft($election); abort_unless($package->election_id === $election->id, 404);
        $package->delete(); return back()->with('success', 'Paket kandidat dihapus.');
    }

    public function publish(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->publish($election), 'Pemilihan dipublikasikan dan daftar pemilih telah dibekukan.'); }
    public function close(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->close($election), 'Pemilihan telah ditutup.'); }
    public function publishResults(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->publishResults($election), 'Hasil pemilihan telah diumumkan kepada siswa.'); }

    private function runAction(callable $action, string $message): RedirectResponse
    {
        try { $action(); return back()->with('success', $message); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
    }

    private function ensureDraft(OsisElection $election): void { abort_unless($election->status === 'draft', 422, 'Paket kandidat telah dikunci.'); }

    private function validatedElection(Request $request): array
    {
        $data = $request->validate([
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'], 'title' => ['required', 'string', 'max:150'],
            'theme' => ['nullable', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:3000'],
            'instructions' => ['nullable', 'string', 'max:3000'], 'eligible_levels' => ['nullable', 'array'],
            'eligible_levels.*' => ['integer', Rule::in([10, 11, 12])], 'include_gtk' => ['required', 'boolean'],
            'candidate_voting_policy' => ['required', Rule::in(['except_own', 'not_allowed'])],
            'starts_at' => ['required', 'date'], 'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        if (empty($data['eligible_levels']) && ! $request->boolean('include_gtk')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'eligible_levels' => 'Pilih minimal satu tingkat siswa atau sertakan GTK.',
            ]);
        }
        $data['eligible_levels'] = array_values($data['eligible_levels'] ?? []);

        return $data;
    }

    private function validatedPackage(Request $request, OsisElection $election, ?OsisPackage $current = null): array
    {
        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:99'], 'name' => ['nullable', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:180'], 'vision' => ['required', 'string', 'max:4000'],
            'mission' => ['required', 'string', 'max:6000'], 'programs' => ['nullable', 'string', 'max:6000'],
            'message' => ['nullable', 'string', 'max:3000'],
            'chairman_id' => ['required', 'exists:siswa,id'], 'secretary_id' => ['required', 'different:chairman_id', 'exists:siswa,id'],
            'treasurer_id' => ['required', 'different:chairman_id', 'different:secretary_id', 'exists:siswa,id'],
        ]);
        $duplicateNumber = $election->packages()->where('number', $data['number'])->when($current, fn ($q) => $q->where('id', '<>', $current->id))->exists();
        if ($duplicateNumber) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Nomor paket sudah digunakan.']);
        $candidateIds = collect([$data['chairman_id'], $data['secretary_id'], $data['treasurer_id']]);
        $eligibleCandidates = Siswa::query()->whereIn('id', $candidateIds)
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($q) => $q->where('tahun_pelajaran_id', $election->tahun_pelajaran_id))
            ->count();
        if ($eligibleCandidates !== 3) throw \Illuminate\Validation\ValidationException::withMessages(['chairman_id' => 'Semua kandidat harus merupakan siswa aktif pada tahun pelajaran pemilihan.']);
        $used = $election->packages()->when($current, fn ($q) => $q->where('id', '<>', $current->id))->get()
            ->flatMap->candidateIds();
        if ($candidateIds->intersect($used)->isNotEmpty()) throw \Illuminate\Validation\ValidationException::withMessages(['chairman_id' => 'Satu siswa hanya boleh berada pada satu paket.']);
        return $data;
    }
}
