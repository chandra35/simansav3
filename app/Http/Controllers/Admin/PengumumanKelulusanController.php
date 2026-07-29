<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Kelas;
use App\Models\PengumumanKelulusan;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengumumanKelulusanController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->first();
        $setting = AppSetting::getInstance();

        if (!$tahunAktif) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.');
        }

        $kelasList = Kelas::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('tingkat', 12)
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();

        $selectedKelasId = $request->string('kelas_id')->toString();
        $selectedStatusFilter = $request->string('status_filter')->toString();
        $selectedOpenedFilter = $request->string('opened_filter')->toString();

        $students = $this->getClass12Students($tahunAktif->id, $selectedKelasId ?: null);
        $announcementMap = $this->getAnnouncementMap($tahunAktif->id, $students->pluck('siswa.id')->all());

        $stats = [
            'total' => $students->count(),
            'lulus' => $announcementMap->where('status', PengumumanKelulusan::STATUS_LULUS)->count(),
            'lulus_bersyarat' => $announcementMap->where('status', PengumumanKelulusan::STATUS_LULUS_BERSYARAT)->count(),
            'tidak_lulus' => $announcementMap->where('status', PengumumanKelulusan::STATUS_TIDAK_LULUS)->count(),
            'sudah_buka' => $announcementMap->filter(fn ($item) => filled($item->opened_at))->count(),
        ];
        $stats['belum_buka'] = max($stats['total'] - $stats['sudah_buka'], 0);

        // Apply status filter for display (stats remain unfiltered)
        $displayStudents = $students;
        if ($selectedStatusFilter === 'belum_ditentukan') {
            $displayStudents = $displayStudents->filter(function ($row) use ($announcementMap) {
                $item = $announcementMap->get($row->siswa->id);
                return !$item || blank($item->status);
            })->values();
        } elseif ($selectedStatusFilter) {
            $displayStudents = $displayStudents->filter(function ($row) use ($announcementMap, $selectedStatusFilter) {
                $item = $announcementMap->get($row->siswa->id);
                return optional($item)->status === $selectedStatusFilter;
            })->values();
        }

        // Apply opened filter for display
        if ($selectedOpenedFilter === 'sudah') {
            $displayStudents = $displayStudents->filter(function ($row) use ($announcementMap) {
                $item = $announcementMap->get($row->siswa->id);
                return $item && filled($item->opened_at);
            })->values();
        } elseif ($selectedOpenedFilter === 'belum') {
            $displayStudents = $displayStudents->filter(function ($row) use ($announcementMap) {
                $item = $announcementMap->get($row->siswa->id);
                return !$item || blank($item->opened_at);
            })->values();
        }

        return view('admin.kelulusan-pengumuman.index', [
            'tahunAktif' => $tahunAktif,
            'setting' => $setting,
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'selectedStatusFilter' => $selectedStatusFilter,
            'selectedOpenedFilter' => $selectedOpenedFilter,
            'students' => $displayStudents,
            'announcementMap' => $announcementMap,
            'stats' => $stats,
            'statusOptions' => PengumumanKelulusan::STATUSES,
        ]);
    }

    public function publish(Request $request)
    {
        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'graduation_announcement_enabled' => ['required', 'boolean'],
            'graduation_announcement_starts_at' => ['nullable', 'date'],
        ]);

        $setting = AppSetting::getInstance();
        $wasEnabled = (bool) $setting->graduation_announcement_enabled;
        $willBeEnabled = (bool) $validated['graduation_announcement_enabled'];
        $setting->update([
            'graduation_announcement_enabled' => $willBeEnabled,
            'graduation_announcement_starts_at' => $validated['graduation_announcement_starts_at'] ?? null,
            'graduation_announcement_tahun_pelajaran_id' => $tahunAktif->id,
        ]);

        $message = 'Jadwal pengumuman kelulusan berhasil diperbarui.';
        if ($wasEnabled !== $willBeEnabled) {
            $message = $willBeEnabled
                ? 'Pengumuman kelulusan untuk siswa kelas 12 sudah diaktifkan.'
                : 'Pengumuman kelulusan untuk siswa kelas 12 sudah disembunyikan.';
        }

        return redirect()->route('admin.kelulusan-pengumuman.index')
            ->with('success', $message);
    }

    public function save(Request $request)
    {
        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['nullable', Rule::in(array_keys(PengumumanKelulusan::STATUSES))],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:1500'],
        ]);

        $studentRows = $this->getClass12Students($tahunAktif->id);
        $allowedBySiswaId = $studentRows->keyBy('siswa.id');

        DB::transaction(function () use ($validated, $tahunAktif, $allowedBySiswaId) {
            $statuses = $validated['statuses'] ?? [];
            $notes = $validated['notes'] ?? [];

            $emptyStatuses = collect($statuses)
                ->filter(fn ($status) => blank($status))
                ->keys()
                ->filter(fn ($siswaId) => $allowedBySiswaId->has($siswaId))
                ->values();

            if ($emptyStatuses->isNotEmpty()) {
                PengumumanKelulusan::query()
                    ->where('tahun_pelajaran_id', $tahunAktif->id)
                    ->whereIn('siswa_id', $emptyStatuses->all())
                    ->delete();
            }

            foreach ($statuses as $siswaId => $status) {
                if (!$allowedBySiswaId->has($siswaId)) {
                    continue;
                }

                if (blank($status)) {
                    continue;
                }

                $note = trim((string) ($notes[$siswaId] ?? ''));
                if ($status === PengumumanKelulusan::STATUS_LULUS_BERSYARAT && $note === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "notes.$siswaId" => 'Catatan wajib diisi untuk status Lulus Bersyarat.',
                    ]);
                }

                $row = $allowedBySiswaId->get($siswaId);

                PengumumanKelulusan::updateOrCreate(
                    [
                        'tahun_pelajaran_id' => $tahunAktif->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'kelas_id' => $row->kelas_id,
                        'status' => $status,
                        'catatan' => $note !== '' ? $note : null,
                    ]
                );
            }
        });

        return redirect()->route('admin.kelulusan-pengumuman.index', array_filter([
            'kelas_id' => $request->input('kelas_filter'),
            'status_filter' => $request->input('status_filter_preserve'),
            'opened_filter' => $request->input('opened_filter_preserve'),
        ]))->with('success', 'Data pengumuman kelulusan berhasil disimpan.');
    }

    public function resetOpened(Request $request)
    {
        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->firstOrFail();
        $kelasId = $request->string('kelas_filter')->toString();
        $studentIds = $this->getClass12Students($tahunAktif->id, $kelasId ?: null)
            ->pluck('siswa.id')
            ->filter()
            ->values();

        if ($studentIds->isEmpty()) {
            return redirect()->route('admin.kelulusan-pengumuman.index', array_filter([
                'kelas_id' => $kelasId,
                'status_filter' => $request->input('status_filter_preserve'),
                'opened_filter' => $request->input('opened_filter_preserve'),
            ]))->with('warning', 'Tidak ada siswa kelas 12 yang bisa direset pada filter ini.');
        }

        $affected = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->whereIn('siswa_id', $studentIds->all())
            ->whereNotNull('opened_at')
            ->update([
                'opened_at' => null,
                'opened_ip' => null,
                'opened_user_agent' => null,
            ]);

        return redirect()->route('admin.kelulusan-pengumuman.index', array_filter([
            'kelas_id' => $kelasId,
            'status_filter' => $request->input('status_filter_preserve'),
            'opened_filter' => $request->input('opened_filter_preserve'),
        ]))->with('success', "Riwayat buka amplop berhasil direset untuk {$affected} siswa.");
    }

    public function resetOpenedForStudent(Request $request, string $siswa)
    {
        $tahunAktif = TahunPelajaran::query()->where('is_active', true)->firstOrFail();
        $kelasId = $request->string('kelas_filter')->toString();
        $allowed = $this->getClass12Students($tahunAktif->id)
            ->pluck('siswa.id')
            ->contains($siswa);

        if (!$allowed) {
            return redirect()->route('admin.kelulusan-pengumuman.index', array_filter([
                'kelas_id' => $kelasId,
                'status_filter' => $request->input('status_filter_preserve'),
                'opened_filter' => $request->input('opened_filter_preserve'),
            ]))->with('error', 'Siswa tidak termasuk kelas 12 pada tahun ajaran aktif.');
        }

        $affected = PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('siswa_id', $siswa)
            ->whereNotNull('opened_at')
            ->update([
                'opened_at' => null,
                'opened_ip' => null,
                'opened_user_agent' => null,
            ]);

        return redirect()->route('admin.kelulusan-pengumuman.index', array_filter([
            'kelas_id' => $kelasId,
            'status_filter' => $request->input('status_filter_preserve'),
            'opened_filter' => $request->input('opened_filter_preserve'),
        ]))->with($affected ? 'success' : 'info', $affected
            ? 'Riwayat buka amplop siswa berhasil direset.'
            : 'Siswa ini belum pernah membuka amplop, tidak ada yang perlu direset.');
    }

    private function getClass12Students(string $tahunPelajaranId, ?string $kelasId = null): Collection
    {
        return SiswaKelas::query()
            ->with(['siswa.user', 'kelas'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('status', 'aktif')
            ->whereNull('deleted_at')
            ->when($kelasId, fn ($query) => $query->where('kelas_id', $kelasId))
            ->whereHas('kelas', function ($query) {
                $query->where('tingkat', 12);
            })
            ->whereHas('siswa')
            ->get()
            ->sortBy([
                fn ($row) => $row->kelas->nama_kelas ?? '',
                fn ($row) => $row->siswa->nama_lengkap ?? '',
            ])
            ->values();
    }

    private function getAnnouncementMap(string $tahunPelajaranId, array $siswaIds): Collection
    {
        if (empty($siswaIds)) {
            return collect();
        }

        return PengumumanKelulusan::query()
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');
    }
}
