<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Basis Portal Wali Kelas ("Kelas Saya").
 *
 * Semua controller portal ini WAJIB extend kelas ini agar setiap request
 * dipastikan: (1) user adalah wali kelas aktif, (2) query di-scope ketat ke
 * rombel miliknya. Scope dilakukan di level query (bukan sekadar tombol
 * disembunyikan) demi keamanan.
 */
abstract class BaseWaliKelasController extends Controller
{
    private ?Collection $cachedClasses = null;

    private ?TahunPelajaran $cachedYear = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check(), 403);
            abort_unless($this->waliClasses()->isNotEmpty(), 403, 'Anda bukan wali kelas aktif.');

            return $next($request);
        });
    }

    protected function activeYear(): ?TahunPelajaran
    {
        return $this->cachedYear ??= TahunPelajaran::query()->active()->first();
    }

    /**
     * Rombel yang wali kelasnya adalah user aktif (tahun aktif, is_active).
     */
    protected function waliClasses(): Collection
    {
        return $this->cachedClasses ??= Kelas::query()
            ->with(['jurusan', 'tahunPelajaran'])
            ->where('wali_kelas_id', Auth::id())
            ->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
    }

    protected function waliClassIds(): Collection
    {
        return $this->waliClasses()->pluck('id');
    }

    /**
     * Ambil kelas milik wali (dari id opsional). Abort 403 jika bukan miliknya.
     * Default ke rombel pertama bila id kosong.
     */
    protected function resolveKelas(?string $kelasId): Kelas
    {
        if (blank($kelasId)) {
            return $this->waliClasses()->first();
        }

        $kelas = $this->waliClasses()->firstWhere('id', $kelasId);
        abort_if($kelas === null, 403, 'Rombel bukan milik Anda.');

        return $kelas;
    }

    /**
     * Pastikan siswa tergabung di salah satu rombel wali (tahun aktif). Abort 404 jika tidak.
     */
    protected function resolveSiswa(string $siswaId): Siswa
    {
        $kelasIds = $this->waliClassIds();

        $siswa = Siswa::query()
            ->whereKey($siswaId)
            ->whereHas('kelasTahunAktif', fn ($q) => $q->whereIn('kelas.id', $kelasIds))
            ->firstOrFail();

        return $siswa;
    }
}
