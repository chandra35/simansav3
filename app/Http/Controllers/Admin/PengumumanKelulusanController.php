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
        $students = $this->getClass12Students($tahunAktif->id, $selectedKelasId ?: null);
        $announcementMap = $this->getAnnouncementMap($tahunAktif->id, $students->pluck('siswa.id')->all());

        $stats = [
            'total' => $students->count(),
            'lulus' => $announcementMap->where('status', PengumumanKelulusan::STATUS_LULUS)->count(),
            'lulus_bersyarat' => $announcementMap->where('status', PengumumanKelulusan::STATUS_LULUS_BERSYARAT)->count(),
            'tidak_lulus' => $announcementMap->where('status', PengumumanKelulusan::STATUS_TIDAK_LULUS)->count(),
        ];

        return view('admin.kelulusan-pengumuman.index', [
            'tahunAktif' => $tahunAktif,
            'setting' => $setting,
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'students' => $students,
            'announcementMap' => $announcementMap,
            'stats' => $stats,
            'statusOptions' => PengumumanKelulusan::STATUSES,
        ]);
    }

    public function publish(Request $request)
    {
        $validated = $request->validate([
            'graduation_announcement_enabled' => ['required', 'boolean'],
        ]);

        $setting = AppSetting::getInstance();
        $setting->update([
            'graduation_announcement_enabled' => (bool) $validated['graduation_announcement_enabled'],
        ]);

        return redirect()->route('admin.kelulusan-pengumuman.index')
            ->with('success', $setting->graduation_announcement_enabled
                ? 'Pengumuman kelulusan untuk siswa kelas 12 sudah diaktifkan.'
                : 'Pengumuman kelulusan untuk siswa kelas 12 sudah disembunyikan.');
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

            foreach ($statuses as $siswaId => $status) {
                if (!$allowedBySiswaId->has($siswaId)) {
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

            $emptyStatuses = collect($statuses)
                ->filter(fn ($status) => blank($status))
                ->keys();

            if ($emptyStatuses->isNotEmpty()) {
                PengumumanKelulusan::query()
                    ->where('tahun_pelajaran_id', $tahunAktif->id)
                    ->whereIn('siswa_id', $emptyStatuses->all())
                    ->delete();
            }
        });

        return redirect()->route('admin.kelulusan-pengumuman.index', [
            'kelas_id' => $request->input('kelas_filter'),
        ])->with('success', 'Data pengumuman kelulusan berhasil disimpan.');
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
