<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\OsisVoter;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\AppSetting;
use App\Exports\OsisElectionReportExport;
use App\Exports\OsisPendingVotersExport;
use App\Services\OsisElectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class OsisElectionController extends Controller
{
    public function __construct(private readonly OsisElectionService $service) {}

    public function index(): View
    {
        $activeYearId = TahunPelajaran::query()->active()->value('id');
        $ongoingElection = $activeYearId
            ? OsisElection::query()->where('tahun_pelajaran_id', $activeYearId)->whereIn('status', ['draft', 'published', 'paused'])->latest('starts_at')->first()
            : null;
        $elections = OsisElection::query()->with('tahunPelajaran')
            ->withCount(['packages', 'voters', 'voters as voted_count' => fn ($q) => $q->where('has_voted', true)])
            ->latest('starts_at')->paginate(12);
        return view('admin.osis-election.index', compact('elections', 'ongoingElection'));
    }

    public function create(): View|RedirectResponse
    {
        $activeYearId = TahunPelajaran::query()->active()->value('id');
        if ($ongoing = $activeYearId ? OsisElection::query()->where('tahun_pelajaran_id', $activeYearId)->whereIn('status', ['draft', 'published', 'paused'])->latest('starts_at')->first() : null) {
            return redirect()->route('admin.osis-election.show', $ongoing)
                ->with('error', 'Selesaikan atau hapus pemilihan yang sedang dikelola sebelum membuat pemilihan baru.');
        }

        return view('admin.osis-election.form', ['election' => new OsisElection, 'years' => TahunPelajaran::latest('tahun_mulai')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedElection($request);
        $election = Cache::lock('osis-election-single-ongoing', 10)->block(5, function () use ($data, $request) {
            if (OsisElection::query()->where('tahun_pelajaran_id', $data['tahun_pelajaran_id'])->whereIn('status', ['draft', 'published', 'paused'])->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'title' => 'Hanya satu pemilihan yang boleh dikelola untuk tahun pelajaran yang sama. Selesaikan atau hapus pemilihan sebelumnya.',
                ]);
            }

            $payload = $data + [
                'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ];

            return OsisElection::create($payload);
        });
        Siswa::logCustomActivity('osis_election_created', "Membuat draft pemilihan: {$election->title}", $election);
        return redirect()->route('admin.osis-election.show', $election)->with('success', 'Pengaturan pemilihan berhasil dibuat. Tambahkan paket kandidat.');
    }

    public function show(OsisElection $election): View
    {
        $election->load(['tahunPelajaran', 'packages.election', 'packages.chairman.kelasSaatIni', 'packages.viceChairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni'])
            ->loadCount([
                'voters',
                'voters as student_voters_count' => fn ($q) => $q->where('participant_type', 'student'),
                'voters as gtk_voters_count' => fn ($q) => $q->where('participant_type', 'gtk'),
                'voters as voted_count' => fn ($q) => $q->where('has_voted', true),
                'ballots',
            ]);
        $results = $election->packages->map(fn ($package) => [
            'package' => $package,
            'votes' => $package->ballots()->count(),
        ]);
        $classes = Kelas::query()->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)->where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'tingkat', 'nama_kelas']);
        return view('admin.osis-election.show', compact('election', 'results', 'classes'));
    }

    public function report(OsisElection $election): View
    {
        return view('admin.osis-election.report', $this->reportData($election));
    }

    public function reportPdf(OsisElection $election)
    {
        return Pdf::loadView('admin.osis-election.report-pdf', $this->reportData($election))
            ->setPaper('a4', 'landscape')
            ->download('laporan-pemilihan-osis-'.$election->slug.'.pdf');
    }

    public function reportExcel(OsisElection $election)
    {
        $report = $this->reportData($election);

        return Excel::download(new OsisElectionReportExport($election, $report), 'laporan-pemilihan-osis-'.$election->slug.'.xlsx');
    }

    public function pendingReport(OsisElection $election): View
    {
        return view('admin.osis-election.pending-report', $this->reportData($election));
    }

    public function pendingReportPdf(OsisElection $election)
    {
        return Pdf::loadView('admin.osis-election.pending-report-pdf', $this->reportData($election))
            ->setPaper('a4', 'landscape')
            ->download('siswa-belum-memilih-'.$election->slug.'.pdf');
    }

    public function pendingReportExcel(OsisElection $election)
    {
        $report = $this->reportData($election);
        return Excel::download(new OsisPendingVotersExport($election, $report['pendingStudents']), 'siswa-belum-memilih-'.$election->slug.'.xlsx');
    }

    public function preview(OsisElection $election): View
    {
        $this->ensureDraft($election);
        $election->load(['packages.election', 'packages.chairman.kelasSaatIni', 'packages.viceChairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni']);

        return view('siswa.osis-election.index', [
            'participantName' => 'Pratinjau Admin',
            'election' => $election,
            'voter' => null,
            'ownPackageIds' => collect(),
            'results' => collect(),
            'voteRoute' => null,
            'previewMode' => true,
        ]);
    }

    public function candidateOptions(Request $request, OsisElection $election): JsonResponse
    {
        $this->ensureDraft($election);
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'current_package_id' => ['nullable', 'uuid'],
            'exclude_ids' => ['nullable', 'array', 'max:4'],
            'exclude_ids.*' => ['uuid'],
            'selected_ids' => ['nullable', 'array', 'max:4'],
            'selected_ids.*' => ['uuid'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $currentPackageId = $data['current_package_id'] ?? null;
        if ($currentPackageId && ! $election->packages()->whereKey($currentPackageId)->exists()) {
            abort(404);
        }

        $query = Siswa::query()
            ->with('kelasSaatIni:id,nama_kelas,tingkat')
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($class) => $class
                ->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
                ->where('tingkat', 11));

        if (! empty($data['selected_ids'])) {
            $students = $query->whereIn('id', $data['selected_ids'])
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nisn', 'kelas_saat_ini_id', 'foto_profile']);

            return response()->json([
                'students' => $students->map(fn (Siswa $student) => $this->candidatePayload($student))->values(),
                'pagination' => [
                    'page' => 1,
                    'has_more' => false,
                    'total' => $students->count(),
                ],
            ]);
        } else {
            $usedIds = $election->packages()
                ->when($currentPackageId, fn ($packages) => $packages->where('id', '<>', $currentPackageId))
                ->get(['chairman_id', 'vice_chairman_id', 'secretary_id', 'treasurer_id'])
                ->flatMap->candidateIds()
                ->filter()
                ->merge($data['exclude_ids'] ?? [])
                ->unique();

            $query->when($usedIds->isNotEmpty(), fn ($students) => $students->whereNotIn('id', $usedIds))
                ->when(filled($data['search'] ?? null), function ($students) use ($data) {
                    $term = trim($data['search']);
                    $students->where(function ($search) use ($term) {
                        $search->where('nama_lengkap', 'like', "%{$term}%")
                            ->orWhere('nisn', 'like', "%{$term}%")
                            ->orWhereHas('kelasSaatIni', fn ($class) => $class->where('nama_kelas', 'like', "%{$term}%"));
                    });
                });
        }

        $paginator = $query->orderBy('nama_lengkap')->paginate(
            perPage: 12,
            columns: ['id', 'nama_lengkap', 'nisn', 'kelas_saat_ini_id', 'foto_profile'],
            pageName: 'page',
            page: (int) ($data['page'] ?? 1),
        );

        return response()->json([
            'students' => $paginator->getCollection()
                ->map(fn (Siswa $student) => $this->candidatePayload($student))->values(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'has_more' => $paginator->hasMorePages(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function livePolling(OsisElection $election): JsonResponse
    {
        abort_if($election->status === 'draft', 404);

        $voters = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();
        $votes = $election->ballots()->select('package_id', DB::raw('COUNT(*) as total'))
            ->groupBy('package_id')->pluck('total', 'package_id');

        return response()->json([
            'phase' => $election->phase,
            'voters' => $voters,
            'voted' => $voted,
            'remaining' => max($voters - $voted, 0),
            'turnout' => $voters ? round(($voted / $voters) * 100, 1) : 0,
            'packages' => $election->packages()->get()->map(fn (OsisPackage $package) => [
                'id' => $package->id,
                'number' => $package->number,
                'name' => $package->name ?: 'Paket '.$package->number,
                'votes' => (int) ($votes[$package->id] ?? 0),
                'percentage' => $voted ? round(((int) ($votes[$package->id] ?? 0) / $voted) * 100, 1) : 0,
            ]),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    public function voters(Request $request, OsisElection $election): JsonResponse
    {
        $data = $request->validate(['type' => ['nullable', Rule::in(['student', 'gtk'])], 'status' => ['nullable', Rule::in(['voted', 'pending'])], 'tingkat' => ['nullable', Rule::in([10, 11, 12])], 'kelas_id' => ['nullable', 'uuid'], 'search' => ['nullable', 'string', 'max:100']]);
        $activeRoster = fn ($kelas) => $kelas
            ->where('kelas.tahun_pelajaran_id', $election->tahun_pelajaran_id)
            ->where('siswa_kelas.tahun_pelajaran_id', $election->tahun_pelajaran_id)
            ->where('siswa_kelas.status', 'aktif')
            ->whereNull('siswa_kelas.tanggal_keluar');
        $query = $election->voters()->with(['user.gtk:id,user_id,nama_lengkap', 'siswa.kelasSaatIni:id,nama_kelas,tingkat', 'siswa.kelas' => $activeRoster])
            ->when($data['type'] ?? null, fn ($q, $type) => $q->where('participant_type', $type))
            ->when($data['status'] ?? null, fn ($q, $status) => $q->where('has_voted', $status === 'voted'))
            ->when($data['tingkat'] ?? null, fn ($q, $tingkat) => $q->whereHas('siswa.kelas', fn ($kelas) => $activeRoster($kelas)->where('kelas.tingkat', $tingkat)))
            ->when($data['kelas_id'] ?? null, fn ($q, $kelas) => $q->whereHas('siswa.kelas', fn ($roster) => $activeRoster($roster)->where('kelas.id', $kelas)))
            ->when(filled($data['search'] ?? null), function ($q) use ($data) { $term = trim($data['search']); $q->where(function ($v) use ($term) { $v->whereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$term}%")->orWhere('nisn', 'like', "%{$term}%"))->orWhereHas('user.gtk', fn ($g) => $g->where('nama_lengkap', 'like', "%{$term}%")); }); });
        $page = $query->latest('voted_at')->paginate(15);
        return response()->json(['rows' => $page->getCollection()->map(function (OsisVoter $voter) {
            $student = $voter->siswa; $gtk = $voter->user?->gtk; $name = $student?->nama_lengkap ?: ($gtk?->nama_lengkap ?: $voter->user?->name);
            $scope = $student?->kelasSaatIni?->nama_kelas ?: $student?->kelas->first()?->nama_kelas ?: 'GTK';
            return ['id' => $voter->id, 'name' => $name, 'identity' => $student?->nisn ?: ($gtk?->nik ?: $voter->user?->username), 'type' => $voter->participant_type, 'scope' => $scope, 'voted' => $voter->has_voted, 'voted_at' => $voter->voted_at?->format('d/m/Y H:i'), 'can_unlock' => $voter->has_voted && in_array($election->status, ['published', 'paused'], true)]; }), 'pagination' => ['page' => $page->currentPage(), 'last' => $page->lastPage(), 'total' => $page->total()]]);
    }

    public function unlockVoter(OsisElection $election, OsisVoter $voter): JsonResponse
    {
        abort_unless($voter->election_id === $election->id, 404);
        try { $this->service->unlockVote($election, $voter, auth()->user()); Siswa::logCustomActivity('osis_vote_unlocked', "Membuka kembali hak pilih {$voter->user?->name} pada {$election->title}.", $election); return response()->json(['message' => 'Hak pilih dibuka kembali. Pemilih harus memilih ulang.']); }
        catch (RuntimeException $exception) { return response()->json(['message' => $exception->getMessage()], 422); }
    }

    /** Keeps ballot choices aggregate-only; individual voter choices never enter the report. */
    private function reportData(OsisElection $election): array
    {
        $school = AppSetting::first();
        $defaultLogo = public_path('vendor/adminlte/dist/img/logo-sekolah.png');
        $logoPath = $school?->logo_sekolah_path ? Storage::disk('public')->path($school->logo_sekolah_path) : $defaultLogo;
        $logoPath = is_file($logoPath) ? $logoPath : $defaultLogo;
        $election->load(['tahunPelajaran', 'packages.election', 'packages.chairman.kelasSaatIni', 'packages.viceChairman.kelasSaatIni', 'packages.secretary.kelasSaatIni', 'packages.treasurer.kelasSaatIni']);
        $voters = $election->voters()->with(['user.gtk:id,user_id,nama_lengkap', 'siswa.kelasSaatIni:id,nama_kelas,tingkat', 'siswa.sekolahAsal:npsn,nama', 'siswa.ortu', 'siswa.kelurahanSiswa', 'siswa.kecamatanSiswa', 'siswa.kabupatenSiswa', 'siswa.provinsiSiswa'])->orderByDesc('has_voted')->orderBy('voted_at')->get();
        $total = $voters->count();
        $voted = $voters->where('has_voted', true)->count();
        $votes = $election->ballots()->select('package_id', DB::raw('COUNT(*) as total'))->groupBy('package_id')->pluck('total', 'package_id');
        $packages = $election->packages->map(function (OsisPackage $package) use ($votes, $voted) {
            $totalVotes = (int) ($votes[$package->id] ?? 0);
            return ['package' => $package, 'votes' => $totalVotes, 'percentage' => $voted ? round($totalVotes / $voted * 100, 1) : 0];
        })->sortByDesc('votes')->values();
        $participation = $voters->filter(fn (OsisVoter $voter) => $voter->participant_type === 'student')
            ->groupBy(fn (OsisVoter $voter) => $voter->siswa?->kelasSaatIni?->nama_kelas ?: 'Belum terpetakan')
            ->map(function ($rows, $class) {
                $classVoted = $rows->where('has_voted', true)->count();
                return ['class' => $class, 'total' => $rows->count(), 'voted' => $classVoted, 'pending' => $rows->count() - $classVoted, 'percentage' => $rows->count() ? round($classVoted / $rows->count() * 100, 1) : 0];
            })->sortBy('class')->values();
        $voterRows = $voters->map(function (OsisVoter $voter) {
            $student = $voter->siswa;
            $gtk = $voter->user?->gtk;
            return ['name' => $student?->nama_lengkap ?: ($gtk?->nama_lengkap ?: $voter->user?->name), 'identity' => $student?->nisn ?: ($gtk?->nik ?: $voter->user?->username), 'type' => $voter->participant_type === 'student' ? 'Siswa' : 'GTK', 'scope' => $student?->kelasSaatIni?->nama_kelas ?: 'GTK', 'status' => $voter->has_voted ? 'Sudah memilih' : 'Belum memilih', 'voted_at' => $voter->voted_at?->format('d/m/Y H:i') ?: '-'];
        });
        $pendingStudents = $voters->filter(fn (OsisVoter $voter) => $voter->participant_type === 'student' && ! $voter->has_voted && $voter->siswa)
            ->values()->map(fn (OsisVoter $voter, int $index) => [
                'no' => $index + 1,
                'nisn' => $voter->siswa->nisn ?: '-',
                'name' => $voter->siswa->nama_lengkap,
                'class' => $voter->siswa->kelasSaatIni?->nama_kelas ?: 'Belum terpetakan',
                'school' => $voter->siswa->sekolahAsal?->nama ?: 'Belum tercatat',
                'address' => $voter->siswa->getAlamatLengkapSiswa() ?: 'Belum tercatat',
            ]);

        return compact('election', 'school', 'logoPath', 'total', 'voted', 'packages', 'participation', 'voterRows', 'pendingStudents') + ['pending' => $total - $voted, 'turnout' => $total ? round($voted / $total * 100, 1) : 0, 'studentTotal' => $voters->where('participant_type', 'student')->count(), 'gtkTotal' => $voters->where('participant_type', 'gtk')->count()];
    }

    public function edit(OsisElection $election): View
    {
        abort_unless(in_array($election->status, ['draft', 'paused'], true), 422, 'Jeda pemilihan terlebih dahulu untuk mengubah pengaturan.');
        return view('admin.osis-election.form', ['election' => $election, 'years' => TahunPelajaran::latest('tahun_mulai')->get()]);
    }

    public function update(Request $request, OsisElection $election): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'paused'], true), 422);
        $data = $this->validatedElection($request, $election);
        $election->update($data + ['updated_by' => $request->user()->id]);
        Siswa::logCustomActivity('osis_election_updated', "Memperbarui pengaturan pemilihan: {$election->title}", $election);
        return redirect()->route('admin.osis-election.show', $election)->with('success', 'Pengaturan pemilihan diperbarui.');
    }

    public function destroy(OsisElection $election): RedirectResponse
    {
        $canDelete = $election->status === 'draft' || ($election->status === 'closed' && ! $election->results_visible);
        abort_unless($canDelete, 422, 'Pemilihan yang masih berjalan atau hasilnya sudah diumumkan tidak dapat dihapus.');

        $label = $election->status === 'draft' ? 'Draft pemilihan' : 'Simulasi pemilihan tertutup';
        DB::transaction(fn () => $election->delete());
        Siswa::logCustomActivity('osis_election_deleted', "Menghapus {$label}: {$election->title}", $election);

        return redirect()->route('admin.osis-election.index')->with('success', "{$label} beserta paket, DPT, dan suara uji coba dihapus.");
    }

    public function storePackage(Request $request, OsisElection $election): RedirectResponse
    {
        $this->ensureDraft($election);
        $data = $this->persistPackagePhotos($request, $this->validatedPackage($request, $election), $election);
        $election->packages()->create($data);
        return back()->with('success', 'Paket kandidat berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, OsisElection $election, OsisPackage $package): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'paused'], true), 422);
        abort_unless($package->election_id === $election->id, 404);
        $data = $election->status === 'paused'
                ? $this->validatedPausedPackage($request, $election, $package)
                : $this->validatedPackage($request, $election, $package);
        $package->update($this->persistPackagePhotos($request, $data, $election, $package));
        return back()->with('success', 'Paket kandidat berhasil diperbarui.');
    }

    public function destroyPackage(OsisElection $election, OsisPackage $package): RedirectResponse
    {
        $this->ensureDraft($election); abort_unless($package->election_id === $election->id, 404);
        $package->delete(); return back()->with('success', 'Paket kandidat dihapus.');
    }

    public function deletePackageCampaignPhoto(OsisElection $election, OsisPackage $package): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'paused'], true), 422);
        abort_unless($package->election_id === $election->id && $package->campaign_photo, 404);
        Storage::disk('public')->delete($package->campaign_photo);
        $package->update(['campaign_photo' => null]);

        return back()->with('success', 'Foto bersama kandidat dihapus.');
    }

    public function deletePackageLivePhoto(Request $request, OsisElection $election, OsisPackage $package): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'paused'], true), 422);
        abort_unless($package->election_id === $election->id, 404);
        $photo = $request->validate(['photo' => ['required', 'string']])['photo'];
        $photos = $package->live_photos ?? [];
        abort_unless(in_array($photo, $photos, true), 404);
        Storage::disk('public')->delete($photo);
        $package->update(['live_photos' => array_values(array_diff($photos, [$photo]))]);

        return back()->with('success', 'Foto galeri dihapus.');
    }

    public function publish(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->publish($election), 'Pemilihan dipublikasikan dan daftar pemilih telah dibekukan.'); }
    public function syncStudentVoters(OsisElection $election): RedirectResponse
    {
        try {
            $added = $this->service->syncStudentVoters($election);
            Siswa::logCustomActivity('osis_student_voters_synced', "Memperbarui DPT siswa {$election->title}: {$added} siswa baru ditambahkan.", $election);
            return back()->with('success', $added ? "Data siswa diperbarui. {$added} siswa baru ditambahkan ke DPT." : 'Data siswa sudah terbaru. Tidak ada siswa baru yang perlu ditambahkan.');
        } catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
    }
    public function pause(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->pause($election), 'Pemilihan dijeda. Voting berhenti sementara dan pengaturan non-kandidat dapat diedit.'); }
    public function resume(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->resume($election), 'Pemilihan dilanjutkan. Voting kembali mengikuti jadwal.'); }
    public function close(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->close($election), 'Pemilihan telah ditutup.'); }
    public function publishResults(OsisElection $election): RedirectResponse { return $this->runAction(fn () => $this->service->publishResults($election), 'Hasil pemilihan telah diumumkan kepada siswa.'); }

    private function runAction(callable $action, string $message): RedirectResponse
    {
        try { $action(); return back()->with('success', $message); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
    }

    private function ensureDraft(OsisElection $election): void { abort_unless($election->status === 'draft', 422, 'Paket kandidat telah dikunci.'); }

    private function validatedElection(Request $request, ?OsisElection $election = null): array
    {
        $data = $request->validate([
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'], 'title' => ['required', 'string', 'max:150'],
            'theme' => ['nullable', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:3000'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'candidate_roles' => ['required', 'array', 'min:2', 'max:4'],
            'candidate_roles.*' => ['string', Rule::in(array_keys(OsisElection::CANDIDATE_ROLE_DEFINITIONS))],
            'eligible_levels' => ['nullable', 'array'],
            'eligible_levels.*' => ['integer', Rule::in([10, 11, 12])], 'include_gtk' => ['required', 'boolean'],
            'candidate_voting_policy' => ['required', Rule::in(['except_own', 'not_allowed'])],
            'starts_at' => ['required', 'date'], 'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        if (! in_array('chairman', $data['candidate_roles'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'candidate_roles' => 'Posisi Ketua wajib dipilih.',
            ]);
        }
        if (empty($data['eligible_levels']) && ! $request->boolean('include_gtk')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'eligible_levels' => 'Pilih minimal satu tingkat siswa atau sertakan GTK.',
            ]);
        }
        $data['candidate_roles'] = array_values(array_unique($data['candidate_roles']));
        $data['eligible_levels'] = array_values($data['eligible_levels'] ?? []);

        if ($election?->status === 'paused') {
            return collect($data)->only([
                'title', 'theme', 'description', 'instructions',
                'starts_at', 'ends_at',
            ])->all();
        }

        if ($election?->exists && $election->packages()->exists() && $election->candidateRoleKeys() !== $data['candidate_roles']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'candidate_roles' => 'Hapus paket kandidat terlebih dahulu sebelum mengubah susunan posisi.',
            ]);
        }

        return $data;
    }

    private function validatedPackage(Request $request, OsisElection $election, ?OsisPackage $current = null): array
    {
        $definitions = $election->candidateRoleDefinitions();
        $candidateFields = collect($definitions)->pluck('field')->values();
        $rules = [
            'number' => ['required', 'integer', 'min:1', 'max:99'], 'name' => ['nullable', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:180'], 'vision' => ['required', 'string', 'max:4000'],
            'mission' => ['required', 'string', 'max:6000'], 'programs' => ['nullable', 'string', 'max:6000'],
            'message' => ['nullable', 'string', 'max:3000'],
            'campaign_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'live_photos' => ['nullable', 'array', 'max:6'],
            'live_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
        foreach (OsisElection::CANDIDATE_ROLE_DEFINITIONS as $definition) {
            $field = $definition['field'];
            $fieldRules = in_array($field, $candidateFields->all(), true)
                ? ['required', 'exists:siswa,id']
                : ['nullable', 'exists:siswa,id'];
            foreach ($candidateFields->reject(fn (string $candidateField) => $candidateField === $field) as $otherField) {
                $fieldRules[] = 'different:'.$otherField;
            }
            $rules[$field] = $fieldRules;
        }
        $data = $request->validate($rules);
        foreach (OsisElection::CANDIDATE_ROLE_DEFINITIONS as $definition) {
            if (! in_array($definition['field'], $candidateFields->all(), true)) {
                $data[$definition['field']] = null;
            }
        }
        $duplicateNumber = $election->packages()->where('number', $data['number'])->when($current, fn ($q) => $q->where('id', '<>', $current->id))->exists();
        if ($duplicateNumber) throw \Illuminate\Validation\ValidationException::withMessages(['number' => 'Nomor paket sudah digunakan.']);
        $candidateIds = $candidateFields->map(fn (string $field) => $data[$field]);
        $eligibleCandidates = Siswa::query()->whereIn('id', $candidateIds)
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($q) => $q
                ->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
                ->where('tingkat', 11))
            ->count();
        if ($eligibleCandidates !== $candidateIds->count()) throw \Illuminate\Validation\ValidationException::withMessages(['chairman_id' => 'Semua kandidat harus merupakan siswa aktif kelas XI pada tahun pelajaran pemilihan.']);
        $used = $election->packages()->when($current, fn ($q) => $q->where('id', '<>', $current->id))->get()
            ->flatMap->candidateIds();
        if ($candidateIds->intersect($used)->isNotEmpty()) throw \Illuminate\Validation\ValidationException::withMessages(['chairman_id' => 'Satu siswa hanya boleh berada pada satu paket.']);
        return $data;
    }

    private function validatedPausedPackage(Request $request, OsisElection $election, OsisPackage $package): array
    {
        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:99'],
            'name' => ['nullable', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:180'],
            'vision' => ['required', 'string', 'max:4000'],
            'mission' => ['required', 'string', 'max:6000'],
            'programs' => ['nullable', 'string', 'max:6000'],
            'message' => ['nullable', 'string', 'max:3000'],
            'campaign_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'live_photos' => ['nullable', 'array', 'max:6'],
            'live_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $duplicateNumber = $election->packages()
            ->where('number', $data['number'])
            ->where('id', '<>', $package->id)
            ->exists();
        if ($duplicateNumber) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'number' => 'Nomor paket sudah digunakan.',
            ]);
        }

        return $data;
    }

    private function persistPackagePhotos(Request $request, array $data, OsisElection $election, ?OsisPackage $package = null): array
    {
        if ($request->hasFile('campaign_photo')) {
            if ($package?->campaign_photo) Storage::disk('public')->delete($package->campaign_photo);
            $data['campaign_photo'] = $request->file('campaign_photo')->store("osis-election/{$election->id}", 'public');
        }
        if ($request->hasFile('live_photos')) {
            $existingPhotos = $package?->live_photos ?? [];
            $newPhotos = $request->file('live_photos');
            if (count($existingPhotos) + count($newPhotos) > 6) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'live_photos' => 'Galeri maksimal berisi 6 foto. Hapus foto lama terlebih dahulu.',
                ]);
            }
            $data['live_photos'] = array_merge($existingPhotos, collect($newPhotos)
                ->map(fn ($photo) => $photo->store("osis-election/{$election->id}", 'public'))->all());
        }

        return $data;
    }

    private function candidatePayload(Siswa $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->nama_lengkap,
            'nisn' => $student->nisn,
            'class' => $student->kelasSaatIni?->nama_kelas ?: 'Tanpa rombel',
            'photo' => $student->foto_profile_url,
        ];
    }
}
