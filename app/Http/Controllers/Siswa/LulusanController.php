<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ReferensiPerguruanTinggi;
use App\Models\SiswaKelas;
use App\Models\SiswaLulusan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LulusanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $targetSiswaKelas = $this->resolveTargetSiswaKelas($siswa->id);

        if (!$targetSiswaKelas || !$targetSiswaKelas->tahunPelajaran) {
            return view('siswa.lulusan.not-applicable', [
                'siswa' => $siswa,
            ]);
        }

        $dataLulusan = SiswaLulusan::firstOrNew([
            'siswa_id' => $siswa->id,
            'tahun_pelajaran_id' => $targetSiswaKelas->tahun_pelajaran_id,
        ]);

        if ($dataLulusan->referensi_perguruan_tinggi_id && !$dataLulusan->nama_universitas_manual) {
            $dataLulusan->loadMissing('referensiPerguruanTinggi');
        }

        return view('siswa.lulusan.index', [
            'siswa' => $siswa,
            'targetSiswaKelas' => $targetSiswaKelas,
            'targetTahunPelajaran' => $targetSiswaKelas->tahunPelajaran,
            'dataLulusan' => $dataLulusan,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
        ]);
    }

    public function searchReferences(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $results = ReferensiPerguruanTinggi::query()
            ->where('is_active', true)
            ->where('nama', 'like', '%' . $query . '%')
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nama', 'jenis']);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $targetSiswaKelas = $this->resolveTargetSiswaKelas($siswa->id);

        if (!$targetSiswaKelas || !$targetSiswaKelas->tahunPelajaran) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Menu data lulusan hanya tersedia untuk siswa kelas 12 atau alumni yang sudah memiliki riwayat kelas 12.');
        }

        $validated = $request->validate([
            'jalur_masuk' => ['required', 'string', Rule::in(SiswaLulusan::JALUR_MASUK)],
            'nama_universitas' => 'required|string|max:255',
            'referensi_perguruan_tinggi_id' => 'nullable|exists:referensi_perguruan_tinggi,id',
            'jurusan_fakultas' => 'nullable|string|max:255',
            'program_studi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'jalur_masuk.required' => 'Jalur diterima wajib dipilih.',
            'nama_universitas.required' => 'Nama universitas wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
        ]);

        $referensi = null;
        if (!empty($validated['referensi_perguruan_tinggi_id'])) {
            $referensi = ReferensiPerguruanTinggi::find($validated['referensi_perguruan_tinggi_id']);
        }

        $payload = [
            'referensi_perguruan_tinggi_id' => $referensi?->id,
            'jalur_masuk' => $validated['jalur_masuk'],
            'nama_universitas' => $referensi?->nama ?? $validated['nama_universitas'],
            'nama_universitas_manual' => $referensi ? null : $validated['nama_universitas'],
            'jurusan_fakultas' => $validated['jurusan_fakultas'] ?? null,
            'program_studi' => $validated['program_studi'],
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        SiswaLulusan::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tahun_pelajaran_id' => $targetSiswaKelas->tahun_pelajaran_id,
            ],
            $payload
        );

        return redirect()->route('siswa.lulusan.index')
            ->with('success', 'Data lulusan berhasil disimpan.');
    }

    private function resolveTargetSiswaKelas(string $siswaId): ?SiswaKelas
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        if ($tahunAktif) {
            $aktif = SiswaKelas::with(['kelas.jurusan', 'tahunPelajaran'])
                ->where('siswa_id', $siswaId)
                ->where('tahun_pelajaran_id', $tahunAktif->id)
                ->whereNull('deleted_at')
                ->whereHas('kelas', function ($query) {
                    $query->where('tingkat', 12);
                })
                ->latest('created_at')
                ->first();

            if ($aktif) {
                return $aktif;
            }
        }

        return SiswaKelas::with(['kelas.jurusan', 'tahunPelajaran'])
            ->where('siswa_id', $siswaId)
            ->whereNull('deleted_at')
            ->whereHas('kelas', function ($query) {
                $query->where('tingkat', 12);
            })
            ->get()
            ->sortByDesc(function (SiswaKelas $siswaKelas) {
                return $siswaKelas->tahunPelajaran->tahun_mulai ?? 0;
            })
            ->first();
    }
}
