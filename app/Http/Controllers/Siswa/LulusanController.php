<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ReferensiPerguruanTinggi;
use App\Models\ReferensiProgramStudi;
use App\Models\SnbpRegistration;
use App\Models\SiswaKelas;
use App\Models\SiswaLulusan;
use App\Models\SpanPtkinRegistration;
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
            $dataLulusan->loadMissing(['referensiPerguruanTinggi', 'referensiProgramStudi', 'snbpRegistration']);
        }

        $snbpRegistration = SnbpRegistration::query()
            ->where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $targetSiswaKelas->tahun_pelajaran_id)
            ->first();

        $spanPtkinRegistration = SpanPtkinRegistration::query()
            ->where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $targetSiswaKelas->tahun_pelajaran_id)
            ->first();

        return view('siswa.lulusan.index', [
            'siswa' => $siswa,
            'targetSiswaKelas' => $targetSiswaKelas,
            'targetTahunPelajaran' => $targetSiswaKelas->tahunPelajaran,
            'dataLulusan' => $dataLulusan,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
            'snbpRegistration' => $snbpRegistration,
            'spanPtkinRegistration' => $spanPtkinRegistration,
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

    public function searchStudyPrograms(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $campusId = $request->get('referensi_perguruan_tinggi_id');

        if (mb_strlen($query) < 2 || empty($campusId)) {
            return response()->json([]);
        }

        $results = ReferensiProgramStudi::query()
            ->where('is_active', true)
            ->where('referensi_perguruan_tinggi_id', $campusId)
            ->where('nama', 'like', '%' . $query . '%')
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nama', 'jenjang', 'fakultas']);

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
            'referensi_program_studi_id' => 'nullable|exists:referensi_program_studi,id',
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

        $referensiProgramStudi = null;
        if (!empty($validated['referensi_program_studi_id'])) {
            $referensiProgramStudi = ReferensiProgramStudi::with('perguruanTinggi')
                ->find($validated['referensi_program_studi_id']);
        }

        if ($referensiProgramStudi && $referensi && $referensiProgramStudi->referensi_perguruan_tinggi_id !== $referensi->id) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'program_studi' => 'Program studi yang dipilih tidak sesuai dengan kampus yang dipilih.',
                ]);
        }

        if (!$referensi && $referensiProgramStudi) {
            $referensi = $referensiProgramStudi->perguruanTinggi;
        }

        $payload = [
            'snbp_registration_id' => null,
            'span_ptkin_registration_id' => null,
            'referensi_perguruan_tinggi_id' => $referensi?->id,
            'referensi_program_studi_id' => $referensiProgramStudi?->id,
            'jalur_masuk' => $validated['jalur_masuk'],
            'nama_universitas' => $referensi?->nama ?? $validated['nama_universitas'],
            'nama_universitas_manual' => $referensi ? null : $validated['nama_universitas'],
            'jurusan_fakultas' => $validated['jurusan_fakultas'] ?: $referensiProgramStudi?->fakultas,
            'program_studi' => $referensiProgramStudi
                ? trim($referensiProgramStudi->jenjang . ' ' . $referensiProgramStudi->nama)
                : $validated['program_studi'],
            'program_studi_manual' => $referensiProgramStudi ? null : $validated['program_studi'],
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if ($validated['jalur_masuk'] === 'SNBP') {
            $snbpRegistration = SnbpRegistration::query()
                ->where('siswa_id', $siswa->id)
                ->where('tahun_pelajaran_id', $targetSiswaKelas->tahun_pelajaran_id)
                ->first();

            if (!$snbpRegistration) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'jalur_masuk' => 'Isi nomor pendaftaran SNBP terlebih dahulu di menu SNBP sebelum menyimpan data lulusan jalur SNBP.',
                    ]);
            }

            $payload['snbp_registration_id'] = $snbpRegistration->id;
        }

        if ($validated['jalur_masuk'] === 'SPAN-PTKIN') {
            $spanPtkinRegistration = SpanPtkinRegistration::query()
                ->where('siswa_id', $siswa->id)
                ->where('tahun_pelajaran_id', $targetSiswaKelas->tahun_pelajaran_id)
                ->first();

            if ($spanPtkinRegistration) {
                $payload['span_ptkin_registration_id'] = $spanPtkinRegistration->id;
            }
        }

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
