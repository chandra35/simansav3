<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-kelas');

        $user = $request->user();
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();

        $canManageHarian = $this->canManageHarian($user);
        $canManageMapel = $this->canManageMapel($user);

        $mode = $request->get('mode', $canManageHarian ? 'harian' : 'mapel');
        if ($mode === 'harian' && !$canManageHarian) {
            $mode = 'mapel';
        }
        if ($mode === 'mapel' && !$canManageMapel) {
            $mode = 'harian';
        }

        $kelasOptions = $this->getAccessibleClasses($user, $tanggal, $mode);
        $selectedKelasId = $request->get('kelas_id') ?: optional($kelasOptions->first())->id;
        $selectedKelas = $selectedKelasId
            ? $kelasOptions->firstWhere('id', $selectedKelasId)
            : null;

        $jadwalOptions = collect();
        $selectedJadwalId = null;
        if ($mode === 'mapel' && $selectedKelas) {
            $jadwalOptions = $this->getAccessibleSchedules($user, $tanggal, $selectedKelas->id);
            $selectedJadwalId = $request->get('jadwal_pelajaran_id') ?: optional($jadwalOptions->first())->id;
        }

        $session = null;
        $students = collect();
        $existingRecords = collect();
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'dispen' => 0,
        ];

        if ($selectedKelas) {
            $session = $this->findExistingSession($tanggal, $selectedKelas->id, $mode, $selectedJadwalId);
            $students = $selectedKelas->siswas()
                ->wherePivot('status', 'aktif')
                ->wherePivot('tahun_pelajaran_id', $selectedKelas->tahun_pelajaran_id)
                ->orderBy('nama_lengkap')
                ->get();

            if ($session) {
                $existingRecords = $session->records()
                    ->get()
                    ->keyBy('siswa_id');

                $summary = [
                    'hadir' => $existingRecords->where('status', 'hadir')->count(),
                    'izin' => $existingRecords->where('status', 'izin')->count(),
                    'sakit' => $existingRecords->where('status', 'sakit')->count(),
                    'alpa' => $existingRecords->where('status', 'alpa')->count(),
                    'dispen' => $existingRecords->where('status', 'dispen')->count(),
                ];
            }
        }

        return view('admin.absensi.siswa', compact(
            'tanggal',
            'tahunPelajaran',
            'mode',
            'canManageHarian',
            'canManageMapel',
            'kelasOptions',
            'selectedKelas',
            'jadwalOptions',
            'selectedJadwalId',
            'session',
            'students',
            'existingRecords',
            'summary'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('view-kelas');

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'mode' => 'required|in:harian,mapel',
            'kelas_id' => 'required|exists:kelas,id',
            'jadwal_pelajaran_id' => 'nullable|exists:jadwal_pelajaran,id',
            'session_notes' => 'nullable|string|max:1000',
            'statuses' => 'required|array|min:1',
            'statuses.*' => 'required|in:hadir,izin,sakit,alpa,dispen',
            'notes' => 'nullable|array',
        ]);

        $user = $request->user();
        $kelas = Kelas::findOrFail($validated['kelas_id']);

        abort_unless(
            $this->getAccessibleClasses($user, $validated['tanggal'], $validated['mode'])->pluck('id')->contains($kelas->id),
            403,
            'Anda tidak memiliki akses untuk mengabsen kelas ini.'
        );

        $selectedJadwal = null;
        if ($validated['mode'] === 'mapel') {
            abort_unless($validated['jadwal_pelajaran_id'], 422, 'Jadwal pelajaran wajib dipilih untuk absensi per mapel.');

            $selectedJadwal = $this->getAccessibleSchedules($user, $validated['tanggal'], $kelas->id)
                ->firstWhere('id', $validated['jadwal_pelajaran_id']);

            abort_unless($selectedJadwal, 403, 'Anda tidak memiliki akses untuk jadwal pelajaran ini.');
        }

        $students = $kelas->siswas()
            ->wherePivot('status', 'aktif')
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->pluck('id');

        DB::transaction(function () use ($validated, $request, $kelas, $selectedJadwal, $students, $user) {
            $session = AbsensiSiswaSession::firstOrNew([
                'tanggal' => $validated['tanggal'],
                'kelas_id' => $kelas->id,
                'mode' => $validated['mode'],
                'jadwal_pelajaran_id' => $validated['mode'] === 'mapel' ? $validated['jadwal_pelajaran_id'] : null,
            ]);

            if (!$session->exists) {
                $session->created_by = $user->id;
            }

            $session->fill([
                'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                'mapel_id' => $selectedJadwal?->mapel_id,
                'guru_user_id' => $selectedJadwal?->gtk?->user_id ?? $user->id,
                'attendance_method' => 'manual',
                'status' => 'final',
                'notes' => $validated['session_notes'] ?? null,
                'updated_by' => $user->id,
            ]);
            $session->save();

            foreach ($validated['statuses'] as $siswaId => $status) {
                if (!$students->contains($siswaId)) {
                    continue;
                }

                AbsensiSiswaRecord::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'status' => $status,
                        'notes' => data_get($validated, "notes.$siswaId"),
                        'attendance_method' => 'manual',
                        'checked_at' => now(),
                        'checked_by' => $user->id,
                    ]
                );
            }
        });

        return redirect()->route('admin.absensi-siswa.index', [
            'tanggal' => $validated['tanggal'],
            'mode' => $validated['mode'],
            'kelas_id' => $kelas->id,
            'jadwal_pelajaran_id' => $validated['jadwal_pelajaran_id'] ?? null,
        ])->with('success', 'Absensi siswa berhasil disimpan.');
    }

    protected function getAccessibleClasses($user, string $tanggal, string $mode)
    {
        if ($this->isUnrestrictedStaff($user)) {
            return Kelas::query()
                ->with(['jurusan', 'tahunPelajaran'])
                ->where('is_active', true)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        }

        $classIds = collect();

        if ($mode === 'harian' && $this->canManageHarian($user)) {
            $classIds = $classIds->merge(
                Kelas::query()
                    ->where('wali_kelas_id', $user->id)
                    ->pluck('id')
            );
        }

        if ($mode === 'mapel' && $this->canManageMapel($user) && $user->gtk) {
            $classIds = $classIds->merge(
                JadwalPelajaran::query()
                    ->where('gtk_id', $user->gtk->id)
                    ->where('hari', $this->resolveHari($tanggal))
                    ->where('is_aktif', true)
                    ->pluck('kelas_id')
            );
        }

        return Kelas::query()
            ->with(['jurusan', 'tahunPelajaran'])
            ->whereIn('id', $classIds->unique()->values())
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
    }

    protected function getAccessibleSchedules($user, string $tanggal, string $kelasId)
    {
        $query = JadwalPelajaran::query()
            ->with(['kelas', 'gtk.user'])
            ->where('kelas_id', $kelasId)
            ->where('hari', $this->resolveHari($tanggal))
            ->where('is_aktif', true)
            ->leftJoin('mata_pelajaran', 'mata_pelajaran.id', '=', 'jadwal_pelajaran.mapel_id')
            ->select('jadwal_pelajaran.*', 'mata_pelajaran.nama_mapel as mapel_nama')
            ->orderBy('jam_ke');

        if (!$this->isUnrestrictedStaff($user) && $user->gtk) {
            $query->where('gtk_id', $user->gtk->id);
        }

        return $query->get();
    }

    protected function findExistingSession(string $tanggal, string $kelasId, string $mode, ?string $jadwalId)
    {
        return AbsensiSiswaSession::query()
            ->with(['records'])
            ->where('tanggal', $tanggal)
            ->where('kelas_id', $kelasId)
            ->where('mode', $mode)
            ->when($mode === 'mapel', fn ($query) => $query->where('jadwal_pelajaran_id', $jadwalId))
            ->when($mode === 'harian', fn ($query) => $query->whereNull('jadwal_pelajaran_id'))
            ->latest('updated_at')
            ->first();
    }

    protected function canManageHarian($user): bool
    {
        return $this->isUnrestrictedStaff($user)
            || ($user->hasRole('Wali Kelas') && Kelas::query()->where('wali_kelas_id', $user->id)->exists());
    }

    protected function canManageMapel($user): bool
    {
        return $this->isUnrestrictedStaff($user)
            || ($user->gtk && JadwalPelajaran::query()->where('gtk_id', $user->gtk->id)->exists());
    }

    protected function isUnrestrictedStaff($user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
    }

    protected function resolveHari(string $tanggal): string
    {
        return match (Carbon::parse($tanggal)->dayOfWeekIso) {
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            default => 'minggu',
        };
    }
}
