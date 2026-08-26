<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\CatatanWaliKelas;
use App\Models\Kelas;
use Illuminate\Support\Collection;

class SiswaController extends BaseWaliKelasController
{
    /**
     * Daftar siswa rombel wali (read-only, tabel lengkap client-side).
     */
    public function index()
    {
        $kelas = $this->resolveKelas(request('kelas_id'));
        $siswa = $this->classStudents($kelas);

        return view('admin.gtk.wali.siswa.index', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'siswa' => $siswa,
            'stats' => [
                'total' => $siswa->count(),
                'laki_laki' => $siswa->where('jenis_kelamin', 'L')->count(),
                'perempuan' => $siswa->where('jenis_kelamin', 'P')->count(),
                'data_lengkap' => $siswa->filter(fn ($item) => $item->isDataComplete())->count(),
            ],
            'kategoriList' => CatatanWaliKelas::KATEGORI,
        ]);
    }

    /**
     * Detail siswa (read-only) — hanya siswa di rombel wali.
     */
    public function show(string $siswa)
    {
        $siswa = $this->resolveSiswa($siswa);
        $siswa->load([
            'user',
            'ortu.provinsi',
            'ortu.kabupaten',
            'ortu.kecamatan',
            'ortu.kelurahan',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa',
            'sekolahAsal',
            'kelasTahunAktif',
            'dokumen' => fn ($query) => $query->latest(),
        ]);

        $catatan = CatatanWaliKelas::query()
            ->where('siswa_id', $siswa->id)
            ->where('created_by', auth()->id())
            ->latest('tanggal')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $viewData = [
            'siswa' => $siswa,
            'catatan' => $catatan,
        ];

        if (request()->ajax()) {
            return response()->json([
                'title' => $siswa->nama_lengkap,
                'html' => view('admin.gtk.wali.siswa.partials.detail', $viewData)->render(),
            ]);
        }

        return view('admin.gtk.wali.siswa.show', $viewData);
    }

    /**
     * Ambil siswa aktif suatu rombel, terurut alfabetis berdasarkan nama.
     */
    protected function classStudents(Kelas $kelas): Collection
    {
        return $kelas->siswaAktif()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->orderBy('nama_lengkap')
            ->get();
    }
}
