<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaMapel;
use App\Models\AsramaPengampu;
use App\Models\AsramaSantri;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\AsramaAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $tahunId = $request->input('tahun_pelajaran_id') ?: TahunPelajaran::active()->value('id');

        return view('asrama.kelas.index', [
            'records' => AsramaKelas::with(['asrama', 'tahunPelajaran', 'wali.gtk', 'ketua.santri.siswa'])
                ->withCount(['anggotaAktif'])
                ->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
                ->orderBy('nama_kelas')->get(),
            'units' => Asrama::where('is_active', true)->orderBy('nama')->get(),
            'years' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'selectedYear' => $tahunId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asrama_id' => ['required', 'exists:asrama_units,id'],
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'nama_kelas' => ['required', 'string', 'max:100'],
            'nama_arab' => ['nullable', 'string', 'max:255'],
            'tingkat' => ['nullable', 'integer', 'min:1', 'max:12'],
            'jenis' => ['required', Rule::in(['putra', 'putri', 'campuran'])],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:500'],
            'ruang' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $exists = AsramaKelas::where([
            'asrama_id' => $data['asrama_id'],
            'tahun_pelajaran_id' => $data['tahun_pelajaran_id'],
            'nama_kelas' => $data['nama_kelas'],
        ])->exists();
        abort_if($exists, 422, 'Nama kelas sudah digunakan pada asrama dan tahun tersebut.');
        AsramaKelas::create($data + [
            'is_active' => true, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Kelas asrama berhasil dibuat.');
    }

    public function show(AsramaKelas $kelas)
    {
        $kelas->load([
            'asrama', 'tahunPelajaran', 'wali.gtk', 'ketua.santri.siswa',
            'anggotaAktif.santri.siswa.kelasTahunAktif',
            'pengampu.mapel', 'pengampu.asatidz.gtk',
        ]);

        return view('asrama.kelas.show', [
            'kelas' => $kelas,
            'asatidz' => AsramaAsatidz::with('gtk')->where('asrama_id', $kelas->asrama_id)
                ->where('is_active', true)->get()->sortBy('gtk.nama_lengkap'),
            'mapels' => AsramaMapel::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('asrama_id')->orWhere('asrama_id', $kelas->asrama_id))
                ->orderBy('urutan')->get(),
            'regularClasses' => Kelas::where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                ->where('is_active', true)->orderBy('nama_kelas')->get(),
            'availableStudents' => Siswa::where('status_siswa', 'aktif')
                ->where(function ($query) use ($kelas) {
                    $query->whereDoesntHave('asramaSantriAktif')
                        ->orWhereHas('asramaSantriAktif', function ($santriQuery) use ($kelas) {
                            $santriQuery->where('asrama_id', $kelas->asrama_id)
                                ->whereDoesntHave('kelasAktif');
                        });
                })
                ->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nisn', 'nis_lokal']),
        ]);
    }

    public function update(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100'],
            'nama_arab' => ['nullable', 'string', 'max:255'],
            'tingkat' => ['nullable', 'integer', 'min:1', 'max:12'],
            'jenis' => ['required', Rule::in(['putra', 'putri', 'campuran'])],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:500'],
            'ruang' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;
        $kelas->update($data);

        return back()->with('success', 'Kelas asrama diperbarui.');
    }

    public function assignStudents(Request $request, AsramaKelas $kelas, AsramaAccessService $access)
    {
        $data = $request->validate([
            'sumber_kelas_id' => ['nullable', 'exists:kelas,id'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ]);
        $studentIds = collect($data['siswa_ids'] ?? []);
        if (! empty($data['sumber_kelas_id'])) {
            $source = Kelas::where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)->findOrFail($data['sumber_kelas_id']);
            $studentIds = $studentIds->merge($source->siswaAktif()->pluck('siswa.id'));
        }
        $studentIds = $studentIds->unique()->values();
        abort_if($studentIds->isEmpty(), 422, 'Pilih rombel sumber atau siswa.');

        $students = Siswa::with('user')->whereIn('id', $studentIds)->get();
        DB::transaction(function () use ($students, $kelas, $request, $access) {
            foreach ($students as $student) {
                $otherMemberships = AsramaSantri::where('siswa_id', $student->id)
                    ->where('asrama_id', '!=', $kelas->asrama_id)
                    ->where('status', 'aktif')
                    ->get();
                foreach ($otherMemberships as $other) {
                    $other->kelasRecords()->where('status', 'aktif')->update([
                        'status' => 'keluar',
                        'tanggal_keluar' => now()->toDateString(),
                        'is_ketua_kelas' => false,
                    ]);
                    $other->update([
                        'status' => 'nonaktif',
                        'tanggal_keluar' => now()->toDateString(),
                        'updated_by' => $request->user()->id,
                    ]);
                }
                $santri = AsramaSantri::withTrashed()->firstOrNew([
                    'asrama_id' => $kelas->asrama_id, 'siswa_id' => $student->id,
                ]);
                if ($santri->trashed()) {
                    $santri->restore();
                }
                if (! $santri->exists || ! $santri->nomor_induk_asrama) {
                    $santri->nomor_induk_asrama = $this->generateNumber($kelas, $student);
                }
                $santri->fill([
                    'status' => 'aktif', 'tanggal_masuk' => $santri->tanggal_masuk ?: now()->toDateString(),
                    'tanggal_keluar' => null, 'created_by' => $santri->created_by ?: $request->user()->id,
                    'updated_by' => $request->user()->id,
                ])->save();

                AsramaKelasSantri::where('asrama_santri_id', $santri->id)
                    ->where('status', 'aktif')->where('asrama_kelas_id', '!=', $kelas->id)
                    ->update(['status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false]);
                $membership = AsramaKelasSantri::withTrashed()->firstOrNew([
                    'asrama_kelas_id' => $kelas->id, 'asrama_santri_id' => $santri->id,
                ]);
                if ($membership->trashed()) {
                    $membership->restore();
                }
                $membership->fill([
                    'status' => 'aktif', 'tanggal_masuk' => now()->toDateString(), 'tanggal_keluar' => null,
                    'ditetapkan_by' => $request->user()->id,
                ])->save();
                $access->syncStudent($student->user);
            }
        });

        return back()->with('success', $students->count().' santri berhasil ditempatkan ke kelas asrama.');
    }

    public function removeStudent(Request $request, AsramaKelas $kelas, AsramaKelasSantri $anggota)
    {
        abort_unless($anggota->asrama_kelas_id === $kelas->id, 404);
        $anggota->update([
            'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(), 'is_ketua_kelas' => false,
        ]);

        return back()->with('success', 'Santri dikeluarkan dari kelas asrama.');
    }

    public function setChair(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate(['anggota_id' => ['nullable', 'exists:asrama_kelas_santri,id']]);
        DB::transaction(function () use ($kelas, $data) {
            $kelas->anggota()->update(['is_ketua_kelas' => false]);
            if (! empty($data['anggota_id'])) {
                $kelas->anggotaAktif()->whereKey($data['anggota_id'])->firstOrFail()
                    ->update(['is_ketua_kelas' => true]);
            }
        });

        return back()->with('success', 'Ketua kelas asrama diperbarui.');
    }

    public function setWali(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate(['wali_asatidz_id' => ['nullable', 'exists:asrama_asatidz,id']]);
        if (! empty($data['wali_asatidz_id'])) {
            AsramaAsatidz::where('asrama_id', $kelas->asrama_id)->findOrFail($data['wali_asatidz_id']);
        }
        $kelas->update(['wali_asatidz_id' => $data['wali_asatidz_id'], 'updated_by' => $request->user()->id]);

        return back()->with('success', 'Wali kelas asrama diperbarui.');
    }

    public function storePengampu(Request $request, AsramaKelas $kelas)
    {
        $data = $request->validate([
            'asrama_mapel_id' => ['required', 'exists:asrama_mapel,id'],
            'asrama_asatidz_id' => ['required', 'exists:asrama_asatidz,id'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
        ]);
        AsramaMapel::where('is_active', true)
            ->where(fn ($query) => $query->whereNull('asrama_id')->orWhere('asrama_id', $kelas->asrama_id))
            ->findOrFail($data['asrama_mapel_id']);
        AsramaAsatidz::where('asrama_id', $kelas->asrama_id)
            ->where('is_active', true)
            ->findOrFail($data['asrama_asatidz_id']);
        $exists = AsramaPengampu::where([
            'asrama_kelas_id' => $kelas->id,
            'asrama_mapel_id' => $data['asrama_mapel_id'],
            'semester' => $data['semester'],
        ])->exists();
        abort_if($exists, 422, 'Mapel tersebut sudah memiliki pengampu pada semester yang dipilih.');
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

    private function generateNumber(AsramaKelas $kelas, Siswa $student): string
    {
        foreach (array_filter([$student->nis_lokal, $student->nisn]) as $candidate) {
            if (! AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists()) {
                return $candidate;
            }
        }
        $prefix = strtoupper($kelas->asrama->kode).'-'.substr((string) $kelas->tahunPelajaran->tahun_mulai, -2).'-';
        $sequence = AsramaSantri::withTrashed()->where('nomor_induk_asrama', 'like', $prefix.'%')->count() + 1;
        do {
            $candidate = $prefix.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists());

        return $candidate;
    }
}
