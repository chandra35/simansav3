<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaMapel;
use App\Models\AsramaPengampu;
use App\Models\AsramaPengasuhSantri;
use App\Models\AsramaRombelPengasuh;
use App\Models\AsramaSantri;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\AsramaAccessService;
use App\Services\AsramaRombelSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request, AsramaRombelSyncService $sync)
    {
        $sync->sync($request->user()->id);
        $tahunId = $request->input('tahun_pelajaran_id') ?: TahunPelajaran::active()->value('id');

        return view('asrama.kelas.index', [
            'records' => AsramaKelas::with([
                'kelasReguler', 'tahunPelajaran', 'ketua.santri.siswa', 'pengasuhRombel.pengasuh.gtk',
            ])->withCount('anggotaAktif')->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->orderBy('nama_kelas')->get(),
            'years' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'selectedYear' => $tahunId,
        ]);
    }

    public function show(AsramaKelas $kelas)
    {
        $kelas->load([
            'kelasReguler', 'tahunPelajaran', 'ketua.santri.siswa',
            'anggotaAktif.santri.siswa', 'anggotaAktif.pengasuhAssignment.rombelPengasuh.pengasuh.gtk',
            'pengasuhRombel.pengasuh.gtk', 'pengasuhRombel.santriAssignments',
            'pengampu.mapel', 'pengampu.asatidz.gtk',
        ]);
        $availableIds = $this->allowedSiswaIds($kelas);

        return view('asrama.kelas.show', [
            'kelas' => $kelas,
            'caregivers' => AsramaAsatidz::with('gtk')->where('is_active', true)
                ->where('dapat_mengasuh_rombel', true)->get()->sortBy('gtk.nama_lengkap'),
            'teachers' => AsramaAsatidz::with('gtk')->where('is_active', true)
                ->where('dapat_mengampu_mapel', true)->get()->sortBy('gtk.nama_lengkap'),
            'mapels' => AsramaMapel::where('is_active', true)->orderBy('urutan')->get(),
            'availableStudents' => Siswa::whereIn('id', $availableIds)->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nisn', 'nis_lokal']),
        ]);
    }

    /**
     * Siswa rombel SIMANSA yang sama + santri aktif yang belum punya rombel asrama (kasus titipan).
     */
    private function allowedSiswaIds(AsramaKelas $kelas)
    {
        $classStudentIds = $kelas->kelasReguler?->siswaAktif()->pluck('siswa.id') ?? collect();
        $unassignedSantri = AsramaSantri::where('status', 'aktif')
            ->whereDoesntHave('kelasAktif')->pluck('siswa_id');

        return $classStudentIds->merge($unassignedSantri)->unique()->values();
    }

    public function update(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate([
            'nama_arab' => ['nullable', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(['putra', 'putri', 'campuran'])],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $kelas->update($data + [
            'is_active' => $request->boolean('is_active'), 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Konfigurasi rombel Asrama diperbarui.');
    }

    public function assignStudents(Request $request, AsramaKelas $kelas, AsramaAccessService $access)
    {
        $data = $request->validate([
            'ambil_semua_rombel' => ['nullable', 'boolean'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ]);
        $allowedIds = $this->allowedSiswaIds($kelas);
        $ids = $request->boolean('ambil_semua_rombel')
            ? ($kelas->kelasReguler?->siswaAktif()->pluck('siswa.id') ?? collect())
            : collect($data['siswa_ids'] ?? []);
        $ids = $ids->intersect($allowedIds)->unique()->values();
        abort_if($ids->isEmpty(), 422, 'Pilih minimal satu siswa untuk rombel '.$kelas->nama_kelas.'.');
        $students = Siswa::with('user')->whereIn('id', $ids)->get();
        $this->placeStudents($students, $kelas, $request, $access);

        return back()->with('success', $students->count().' santri disinkronkan ke rombel Asrama.');
    }

    public function removeStudent(AsramaKelas $kelas, AsramaKelasSantri $anggota)
    {
        abort_unless($anggota->asrama_kelas_id === $kelas->id, 404);
        $anggota->pengasuhAssignment()->update([
            'is_active' => false, 'tanggal_selesai' => now()->toDateString(),
        ]);
        $anggota->update([
            'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
        ]);

        return back()->with('success', 'Santri dilepas dari rombel Asrama.');
    }

    public function setChair(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate(['anggota_id' => ['nullable', 'exists:asrama_kelas_santri,id']]);
        DB::transaction(function () use ($kelas, $data): void {
            $kelas->anggota()->update(['is_ketua_kelas' => false]);
            if (! empty($data['anggota_id'])) {
                $kelas->anggotaAktif()->findOrFail($data['anggota_id'])->update(['is_ketua_kelas' => true]);
            }
        });

        return back()->with('success', 'Ketua rombel Asrama diperbarui.');
    }

    public function storeCaregiver(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate([
            'asrama_asatidz_id' => ['required', 'exists:asrama_asatidz,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
        $caregiver = AsramaAsatidz::where('is_active', true)->where('dapat_mengasuh_rombel', true)
            ->findOrFail($data['asrama_asatidz_id']);
        $assignment = AsramaRombelPengasuh::withTrashed()->firstOrNew([
            'asrama_kelas_id' => $kelas->id, 'asrama_asatidz_id' => $caregiver->id,
        ]);
        if ($assignment->trashed()) {
            $assignment->restore();
        }
        if ($request->boolean('is_primary')) {
            $kelas->pengasuhRombel()->update(['is_primary' => false]);
        }
        $assignment->fill([
            'is_primary' => $request->boolean('is_primary'),
            'tanggal_mulai' => $assignment->tanggal_mulai ?: now()->toDateString(),
            'tanggal_selesai' => null, 'is_active' => true, 'created_by' => $request->user()->id,
        ])->save();
        if (! $kelas->wali_asatidz_id || $assignment->is_primary) {
            $kelas->update(['wali_asatidz_id' => $caregiver->id]);
        }

        return back()->with('success', $caregiver->gtk->nama_lengkap.' ditambahkan sebagai pengasuh rombel.');
    }

    public function destroyCaregiver(AsramaKelas $kelas, AsramaRombelPengasuh $pengasuh)
    {
        abort_unless($pengasuh->asrama_kelas_id === $kelas->id, 404);
        $pengasuh->santriAssignments()->update([
            'is_active' => false, 'tanggal_selesai' => now()->toDateString(),
        ]);
        $pengasuh->update(['is_active' => false, 'tanggal_selesai' => now()->toDateString()]);
        if ($kelas->wali_asatidz_id === $pengasuh->asrama_asatidz_id) {
            $kelas->update(['wali_asatidz_id' => $kelas->pengasuhRombel()->first()?->asrama_asatidz_id]);
        }

        return back()->with('success', 'Pengasuh dilepas dari rombel.');
    }

    public function assignCaregiverStudents(Request $request, AsramaKelas $kelas, AsramaRombelPengasuh $pengasuh)
    {
        abort_unless($pengasuh->asrama_kelas_id === $kelas->id && $pengasuh->is_active, 404);
        $data = $request->validate([
            'semua_santri' => ['nullable', 'boolean'],
            'anggota_ids' => ['nullable', 'array'],
            'anggota_ids.*' => ['exists:asrama_kelas_santri,id'],
        ]);
        $validIds = $kelas->anggotaAktif()->pluck('id');
        $ids = $request->boolean('semua_santri') ? $validIds : collect($data['anggota_ids'] ?? [])->intersect($validIds);
        abort_if($ids->isEmpty(), 422, 'Pilih minimal satu santri.');

        DB::transaction(function () use ($ids, $pengasuh, $request): void {
            foreach ($ids as $memberId) {
                $existing = AsramaPengasuhSantri::withTrashed()->where('asrama_kelas_santri_id', $memberId)->first();
                if ($existing?->trashed()) {
                    $existing->restore();
                }
                ($existing ?: new AsramaPengasuhSantri)->fill([
                    'asrama_rombel_pengasuh_id' => $pengasuh->id,
                    'asrama_kelas_santri_id' => $memberId,
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_selesai' => null,
                    'is_active' => true,
                    'created_by' => $request->user()->id,
                ])->save();
            }
        });

        return back()->with('success', $ids->count().' santri ditetapkan kepada pengasuh.');
    }

    public function storePengampu(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate([
            'asrama_mapel_id' => ['required', 'exists:asrama_mapel,id'],
            'asrama_asatidz_id' => ['required', 'exists:asrama_asatidz,id'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
        ]);
        AsramaAsatidz::where('is_active', true)->where('dapat_mengampu_mapel', true)
            ->findOrFail($data['asrama_asatidz_id']);
        abort_if(AsramaPengampu::where([
            'asrama_kelas_id' => $kelas->id,
            'asrama_mapel_id' => $data['asrama_mapel_id'],
            'semester' => $data['semester'],
        ])->exists(), 422, 'Mapel sudah memiliki pengampu pada semester tersebut.');
        AsramaPengampu::create($data + [
            'asrama_kelas_id' => $kelas->id, 'is_active' => true,
            'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Pengampu mata pelajaran ditambahkan.');
    }

    public function destroyPengampu(AsramaKelas $kelas, AsramaPengampu $pengampu)
    {
        abort_unless($pengampu->asrama_kelas_id === $kelas->id, 404);
        abort_if($pengampu->nilai()->exists(), 422, 'Pengampu sudah memiliki nilai.');
        $pengampu->delete();

        return back()->with('success', 'Penugasan pengampu dihapus.');
    }

    private function placeStudents($students, AsramaKelas $kelas, Request $request, AsramaAccessService $access): void
    {
        DB::transaction(function () use ($students, $kelas, $request, $access): void {
            foreach ($students as $student) {
                $santri = AsramaSantri::withTrashed()->where('siswa_id', $student->id)->first()
                    ?: new AsramaSantri(['asrama_id' => $kelas->asrama_id, 'siswa_id' => $student->id]);
                if ($santri->trashed()) {
                    $santri->restore();
                }
                $santri->fill([
                    'asrama_id' => $kelas->asrama_id,
                    'nomor_induk_asrama' => $santri->nomor_induk_asrama ?: $this->generateNumber($student),
                    'status' => 'aktif', 'tanggal_masuk' => $santri->tanggal_masuk ?: now()->toDateString(),
                    'tanggal_keluar' => null, 'created_by' => $santri->created_by ?: $request->user()->id,
                    'updated_by' => $request->user()->id,
                ])->save();
                AsramaKelasSantri::where('asrama_santri_id', $santri->id)->where('status', 'aktif')
                    ->where('asrama_kelas_id', '!=', $kelas->id)->update([
                        'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
                    ]);
                $member = AsramaKelasSantri::withTrashed()->firstOrNew([
                    'asrama_kelas_id' => $kelas->id, 'asrama_santri_id' => $santri->id,
                ]);
                if ($member->trashed()) {
                    $member->restore();
                }
                $member->fill([
                    'status' => 'aktif', 'tanggal_masuk' => $member->tanggal_masuk ?: now()->toDateString(),
                    'tanggal_keluar' => null, 'ditetapkan_by' => $request->user()->id,
                ])->save();
                $access->syncStudent($student->user);
            }
        });
    }

    private function generateNumber(Siswa $student): string
    {
        foreach (array_filter([$student->nis_lokal, $student->nisn]) as $candidate) {
            if (! AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists()) {
                return $candidate;
            }
        }
        $sequence = AsramaSantri::withTrashed()->count() + 1;
        do {
            $candidate = 'AST-'.now()->format('y').'-'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists());

        return $candidate;
    }
}
