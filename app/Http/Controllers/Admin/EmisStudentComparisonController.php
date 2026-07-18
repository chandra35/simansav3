<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmisStudentSnapshot;
use App\Models\EmisStudentSync;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\EmisInstitutionTokenService;
use App\Services\EmisStudentSyncService;
use App\Services\SmartStudentComparator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class EmisStudentComparisonController extends Controller
{
    public function __construct(
        private readonly EmisInstitutionTokenService $tokenService,
        private readonly EmisStudentSyncService $syncService,
        private readonly SmartStudentComparator $comparator,
    ) {}

    public function index(Request $request): View
    {
        $status = (string) $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
        $tingkat = (string) $request->get('tingkat', '');
        if (! in_array($tingkat, ['10', '11', '12'], true)) {
            $tingkat = '';
        }
        $kelasId = (string) $request->get('kelas_id', '');
        $activeYear = TahunPelajaran::query()->where('is_active', true)->first();
        $activeYearId = $activeYear?->id;
        $tokenStatus = $this->publicTokenStatus();
        $latestSync = EmisStudentSync::query()
            ->where('tahun_pelajaran_id', $activeYearId)
            ->latest('started_at')
            ->first();
        $hasCurrentSnapshot = EmisStudentSync::query()
            ->where('tahun_pelajaran_id', $activeYearId)
            ->where('status', 'completed')
            ->exists();
        $snapshotQuery = EmisStudentSnapshot::query()->where('tahun_pelajaran_id', $activeYearId);

        $activeStudents = Siswa::query()->where('status_siswa', 'aktif');
        $stats = [
            'simansa' => (clone $activeStudents)->count(),
            'emis' => (clone $snapshotQuery)->count(),
            'exact' => (clone $snapshotQuery)->whereIn('comparison_status', ['exact', 'normalized'])->count(),
            'similar' => (clone $snapshotQuery)->where('comparison_status', 'similar')->count(),
            'different' => (clone $snapshotQuery)->where('comparison_status', 'different')->count(),
            'only_simansa' => (clone $activeStudents)->whereDoesntHave('emisStudentSnapshots', fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId))->count(),
            'only_emis' => (clone $snapshotQuery)->whereNull('siswa_id')->count(),
        ];

        $archivePeriods = TahunPelajaran::query()
            ->whereHas('emisStudentSyncs', fn ($q) => $q->where('status', 'completed'))
            ->withCount(['emisStudentSnapshots as snapshot_count'])
            ->orderByDesc('tahun_mulai')
            ->get(['id', 'nama', 'semester_aktif', 'is_active']);
        $classes = Kelas::query()
            ->where('is_active', true)
            ->when($activeYear, fn ($query) => $query->where('tahun_pelajaran_id', $activeYear->id))
            ->when(! $activeYear, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        if ($kelasId !== '' && ! $classes->contains(
            fn (Kelas $kelas) => $kelas->id === $kelasId && (string) $kelas->tingkat === $tingkat
        )) {
            $kelasId = '';
        }

        if ($status === 'only_emis') {
            $query = EmisStudentSnapshot::query()->where('tahun_pelajaran_id', $activeYearId)->whereNull('siswa_id');
            if ($search !== '') {
                $query->where(fn ($q) => $q
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%"));
            }

            $items = $query->orderBy('full_name')->paginate(25)->withQueryString();
            $listMode = 'emis';
        } else {
            $query = Siswa::query()
                ->with(['kelasSaatIni:id,nama_kelas,tingkat', 'emisStudentSnapshots' => fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId)->latest('synced_at')])
                ->where('status_siswa', 'aktif');

            if ($search !== '') {
                $query->where(function ($q) use ($search, $activeYearId) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('emisStudentSnapshots', fn ($snapshot) => $snapshot
                            ->where('tahun_pelajaran_id', $activeYearId)
                            ->where(fn ($emis) => $emis
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('nisn', 'like', "%{$search}%")));
                });
            }

            if ($kelasId !== '') {
                $query->where('kelas_saat_ini_id', $kelasId);
            } elseif ($tingkat !== '') {
                $query->whereHas('kelasSaatIni', fn ($kelas) => $kelas
                    ->where('tingkat', (int) $tingkat)
                    ->when($activeYear, fn ($activeClass) => $activeClass->where('tahun_pelajaran_id', $activeYear->id))
                    ->when(! $activeYear, fn ($activeClass) => $activeClass->whereRaw('1 = 0')));
            }

            if ($status === 'only_simansa') {
                $query->whereDoesntHave('emisStudentSnapshots', fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId));
            } elseif ($status === 'exact') {
                $query->whereHas('emisStudentSnapshots', fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId)->whereIn('comparison_status', ['exact', 'normalized']));
            } elseif ($status === 'attention') {
                $query->whereHas('emisStudentSnapshots', fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId)->whereIn('comparison_status', ['similar', 'different']));
            } elseif (in_array($status, ['similar', 'different'], true)) {
                $query->whereHas('emisStudentSnapshots', fn ($q) => $q->where('tahun_pelajaran_id', $activeYearId)->where('comparison_status', $status));
            }

            $items = $query->orderBy('nama_lengkap')->paginate(25)->withQueryString();
            $listMode = 'simansa';
        }

        return view('admin.emis-comparison.index', compact(
            'items',
            'listMode',
            'stats',
            'status',
            'search',
            'tingkat',
            'kelasId',
            'classes',
            'activeYear',
            'tokenStatus',
            'latestSync',
            'hasCurrentSnapshot',
            'archivePeriods',
        ));
    }

    public function show(Siswa $siswa): View
    {
        abort_unless($siswa->status_siswa === 'aktif', 404);

        $activeYear = TahunPelajaran::query()->active()->firstOrFail();
        $siswa->load([
            'kelasSaatIni:id,nama_kelas,tingkat',
            'emisStudentSnapshots' => fn ($query) => $query->where('tahun_pelajaran_id', $activeYear->id)->latest('synced_at'),
            'dokumen' => fn ($query) => $query
                ->whereIn('jenis_dokumen', ['kk', 'ijazah_smp'])
                ->latest('created_at'),
        ]);
        $snapshot = $siswa->emisStudentSnapshots->first();
        $comparison = $snapshot
            ? $this->comparator->compare($this->simansaData($siswa), $this->emisData($snapshot))
            : null;

        return view('admin.emis-comparison.show', [
            'siswa' => $siswa,
            'snapshot' => $snapshot,
            'comparison' => $comparison,
            'tokenStatus' => $this->publicTokenStatus(),
            'activeYear' => $activeYear,
            'referenceDocuments' => $siswa->dokumen
                ->groupBy('jenis_dokumen')
                ->map(fn ($documents) => $documents->first()),
        ]);
    }

    public function showEmis(EmisStudentSnapshot $snapshot): View
    {
        abort_unless($snapshot->siswa_id === null, 404);
        $activeYear = TahunPelajaran::query()->active()->firstOrFail();
        abort_unless($snapshot->tahun_pelajaran_id === $activeYear->id, 404);

        return view('admin.emis-comparison.show', [
            'siswa' => null,
            'snapshot' => $snapshot,
            'comparison' => $this->comparator->compare([], $this->emisData($snapshot)),
            'tokenStatus' => $this->publicTokenStatus(),
            'activeYear' => $activeYear,
            'referenceDocuments' => collect(),
        ]);
    }

    public function syncStudent(Request $request, Siswa $siswa): JsonResponse
    {
        abort_unless($siswa->status_siswa === 'aktif', 404);

        try {
            $snapshot = $this->syncService->syncStudent($siswa, $request->user());
            Siswa::logCustomActivity(
                'emis_student_sync',
                "Menyinkronkan ulang snapshot EMIS siswa: {$siswa->nama_lengkap}",
                $siswa,
            );

            return response()->json([
                'success' => true,
                'message' => 'Data EMIS siswa berhasil diperbarui dan dibandingkan ulang.',
                'snapshot' => [
                    'status' => $snapshot->comparison_status,
                    'synced_at' => $snapshot->synced_at?->toIso8601String(),
                ],
                'redirect_url' => route('admin.emis-comparison.show', $siswa),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function sync(Request $request): JsonResponse
    {
        try {
            $sync = $this->syncService->start($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Permintaan sinkronisasi diterima.',
                'sync' => $this->syncPayload($sync),
                'process_url' => route('admin.emis-comparison.sync.process', $sync),
                'status_url' => route('admin.emis-comparison.sync.status', $sync),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function processSync(EmisStudentSync $sync): JsonResponse
    {
        try {
            $result = $this->syncService->process($sync);

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi EMIS selesai.',
                'sync' => $this->syncPayload($result),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'sync' => $this->syncPayload($sync->fresh()),
            ], 422);
        }
    }

    public function syncStatus(EmisStudentSync $sync): JsonResponse
    {
        return response()->json([
            'success' => true,
            'sync' => $this->syncPayload($sync->fresh()),
        ]);
    }

    private function publicTokenStatus(): array
    {
        $status = $this->tokenService->status();
        unset($status['token']);

        return $status;
    }

    private function syncPayload(EmisStudentSync $sync): array
    {
        return [
            'id' => $sync->id,
            'status' => $sync->status,
            'stage' => $sync->stage,
            'progress_percent' => (int) $sync->progress_percent,
            'progress_message' => $sync->progress_message,
            'processed_pages' => (int) $sync->processed_pages,
            'total_pages' => (int) $sync->total_pages,
            'total_students' => (int) $sync->total_students,
            'matched_students' => (int) $sync->matched_students,
            'different_students' => (int) $sync->different_students,
            'error_message' => $sync->status === 'failed' ? $sync->error_message : null,
            'started_at' => $sync->started_at?->toIso8601String(),
            'finished_at' => $sync->finished_at?->toIso8601String(),
            'tahun_pelajaran_id' => $sync->tahun_pelajaran_id,
        ];
    }

    private function simansaData(Siswa $siswa): array
    {
        return [
            'nama_lengkap' => $siswa->nama_lengkap,
            'nisn' => $siswa->nisn,
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir?->format('Y-m-d'),
            'kelas' => $siswa->kelasSaatIni?->nama_kelas,
        ];
    }

    private function emisData(EmisStudentSnapshot $snapshot): array
    {
        return [
            'nama_lengkap' => $snapshot->full_name,
            'nisn' => $snapshot->nisn,
            'tempat_lahir' => $snapshot->birth_place,
            'tanggal_lahir' => $snapshot->birth_date?->format('Y-m-d'),
            'kelas' => $snapshot->study_group_name,
        ];
    }
}
