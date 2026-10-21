<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SholatPetugasKelas;
use App\Models\SholatSetting;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SholatSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sholat-manage-setting');
    }

    public function index(Request $request)
    {
        $settings = SholatSetting::query()->ordered()->get();
        $tahunAktif = TahunPelajaran::query()->active()->first();

        $kelasList = Kelas::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $selectedKelasId = (string) $request->get('kelas_id', $kelasList->first()?->id);
        $selectedKelas = $kelasList->firstWhere('id', $selectedKelasId);

        $petugasList = collect();
        if ($selectedKelas) {
            $petugasList = SholatPetugasKelas::query()
                ->with(['siswa', 'kelas'])
                ->where('kelas_id', $selectedKelas->id)
                ->where('is_active', true)
                ->get();
        }

        return view('admin.sholat.settings', compact('settings', 'kelasList', 'selectedKelas', 'petugasList', 'tahunAktif'));
    }

    public function update(Request $request, SholatSetting $sholatSetting)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'jam_mulai' => ['nullable', 'string', 'max:5'],
            'jam_selesai' => ['nullable', 'string', 'max:5'],
            'hari_aktif' => ['nullable', 'array'],
            'hari_aktif.*' => ['string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'target_gender' => ['required', 'in:all,L,P'],
            'urutan' => ['required', 'integer', 'min:1'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['hari_aktif'] = $request->input('hari_aktif', []);

        $sholatSetting->update($data);

        return redirect()->route('admin.sholat.settings.index')
            ->with('success', "Pengaturan {$sholatSetting->nama} berhasil diperbarui.");
    }

    public function toggle(SholatSetting $sholatSetting)
    {
        $sholatSetting->update([
            'is_active' => ! $sholatSetting->is_active,
        ]);

        $statusStr = $sholatSetting->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $sholatSetting->is_active,
                'message' => "Sholat {$sholatSetting->nama} berhasil {$statusStr}.",
            ]);
        }

        return back()->with('success', "Sholat {$sholatSetting->nama} berhasil {$statusStr}.");
    }

    public function assignPetugas(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
        ]);

        $tahunAktif = TahunPelajaran::query()->active()->first();

        $petugas = SholatPetugasKelas::updateOrCreate(
            [
                'kelas_id' => $data['kelas_id'],
                'siswa_id' => $data['siswa_id'],
            ],
            [
                'tahun_pelajaran_id' => $tahunAktif?->id,
                'semester' => (int) ($tahunAktif?->semester ?: 1),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->route('admin.sholat.settings.index', ['kelas_id' => $data['kelas_id']])
            ->with('success', 'Petugas Kerohanian kelas berhasil ditambahkan.');
    }

    public function removePetugas(SholatPetugasKelas $petugas)
    {
        $kelasId = $petugas->kelas_id;
        $petugas->delete();

        return redirect()->route('admin.sholat.settings.index', ['kelas_id' => $kelasId])
            ->with('success', 'Petugas Kerohanian berhasil dihapus.');
    }

    public function searchSiswa(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        $q = trim((string) $request->get('q'));

        if (! $kelasId) {
            return response()->json([]);
        }

        $kelas = Kelas::findOrFail($kelasId);

        $siswas = $kelas->siswas()
            ->where('siswa_kelas.status', 'aktif')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_lengkap', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->select('siswa.id', 'siswa.nama_lengkap', 'siswa.nisn', 'siswa.jenis_kelamin')
            ->orderBy('siswa.nama_lengkap')
            ->limit(30)
            ->get();

        return response()->json($siswas);
    }
}
