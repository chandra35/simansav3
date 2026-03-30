<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SiswaLulusan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class LulusanController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $selectedTahun = $request->filled('tahun_pelajaran_id')
            ? $tahunPelajaranList->firstWhere('id', $request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        if (!$selectedTahun) {
            $selectedTahun = $tahunPelajaranList->first();
        }

        $siswas = collect();
        $summary = [
            'total' => 0,
            'sudah_isi' => 0,
            'belum_isi' => 0,
            'per_jalur' => [],
        ];

        if ($selectedTahun) {
            $siswas = Siswa::with([
                    'user',
                    'kelas' => function ($query) use ($selectedTahun) {
                        $query->with('jurusan')
                            ->where('kelas.tingkat', 12)
                            ->where('siswa_kelas.tahun_pelajaran_id', $selectedTahun->id)
                            ->whereNull('siswa_kelas.deleted_at');
                    },
                    'dataLulusan' => function ($query) use ($selectedTahun) {
                        $query->where('tahun_pelajaran_id', $selectedTahun->id);
                    },
                ])
                ->whereHas('kelas', function ($query) use ($selectedTahun) {
                    $query->where('kelas.tingkat', 12)
                        ->where('siswa_kelas.tahun_pelajaran_id', $selectedTahun->id)
                        ->whereNull('siswa_kelas.deleted_at');
                })
                ->orderBy('nama_lengkap')
                ->get()
                ->map(function (Siswa $siswa) {
                    $kelas = $siswa->kelas->first();
                    $lulusan = $siswa->dataLulusan->first();

                    $siswa->setRelation('kelas_target', $kelas);
                    $siswa->setRelation('lulusan_target', $lulusan);

                    return $siswa;
                });

            if ($request->filled('status_pengisian')) {
                $siswas = $siswas->filter(function (Siswa $siswa) use ($request) {
                    $hasData = (bool) $siswa->getRelation('lulusan_target');

                    return $request->status_pengisian === 'sudah_isi' ? $hasData : !$hasData;
                })->values();
            }

            if ($request->filled('jalur_masuk')) {
                $siswas = $siswas->filter(function (Siswa $siswa) use ($request) {
                    return optional($siswa->getRelation('lulusan_target'))->jalur_masuk === $request->jalur_masuk;
                })->values();
            }

            if ($request->filled('q')) {
                $search = mb_strtolower($request->q);

                $siswas = $siswas->filter(function (Siswa $siswa) use ($search) {
                    $lulusan = $siswa->getRelation('lulusan_target');
                    $kelas = $siswa->getRelation('kelas_target');

                    return str_contains(mb_strtolower($siswa->nama_lengkap ?? ''), $search)
                        || str_contains(mb_strtolower($siswa->nisn ?? ''), $search)
                        || str_contains(mb_strtolower(optional($kelas)->nama_kelas ?? ''), $search)
                        || str_contains(mb_strtolower(optional($lulusan)->nama_universitas ?? ''), $search)
                        || str_contains(mb_strtolower(optional($lulusan)->program_studi ?? ''), $search);
                })->values();
            }

            $summary['total'] = $siswas->count();
            $summary['sudah_isi'] = $siswas->filter(fn (Siswa $siswa) => (bool) $siswa->getRelation('lulusan_target'))->count();
            $summary['belum_isi'] = $summary['total'] - $summary['sudah_isi'];
            $summary['per_jalur'] = collect(SiswaLulusan::JALUR_MASUK)
                ->mapWithKeys(function (string $jalur) use ($siswas) {
                    return [
                        $jalur => $siswas->filter(fn (Siswa $siswa) => optional($siswa->getRelation('lulusan_target'))->jalur_masuk === $jalur)->count(),
                    ];
                })
                ->all();
        }

        return view('admin.lulusan.index', [
            'tahunPelajaranList' => $tahunPelajaranList,
            'selectedTahun' => $selectedTahun,
            'siswas' => $siswas,
            'summary' => $summary,
            'jalurMasukOptions' => SiswaLulusan::JALUR_MASUK,
        ]);
    }
}
