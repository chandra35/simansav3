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
        ]);
    }

    /**
     * Detail siswa (read-only) — hanya siswa di rombel wali.
     */
    public function show(string $siswa)
    {
        $siswa = $this->resolveSiswa($siswa);
        $siswa->load(['user', 'ortu', 'sekolahAsal', 'kelasAktif']);

        $catatan = CatatanWaliKelas::query()
            ->where('siswa_id', $siswa->id)
            ->where('created_by', auth()->id())
            ->latest('tanggal')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.gtk.wali.siswa.show', [
            'siswa' => $siswa,
            'catatan' => $catatan,
        ]);
    }

    /**
     * Ambil siswa aktif suatu rombel, terurut nomor absen lalu nama.
     */
    protected function classStudents(Kelas $kelas): Collection
    {
        return $kelas->siswaAktif()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->orderByRaw('COALESCE(siswa_kelas.nomor_urut_absen, 9999)')
            ->orderBy('nama_lengkap')
            ->get();
    }
}
