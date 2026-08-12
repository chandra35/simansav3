<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-siswa');

        $tahunPelajaranList = TahunPelajaran::query()
            ->whereHas('siswaKelas', fn (Builder $query) => $query->where('status', 'lulus'))
            ->orderByDesc('tahun_mulai')
            ->get();

        $alumni = $this->graduationQuery($request)
            ->with([
                'siswa:id,nisn,nis_lokal,nama_lengkap,jenis_kelamin,foto_profile,nomor_hp,status_siswa',
                'kelas:id,nama_kelas,tingkat,jurusan_id',
                'kelas.jurusan:id,nama_jurusan',
                'tahunPelajaran:id,nama,tahun_mulai,tahun_selesai',
            ])
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = $this->statistics();

        return view('admin.alumni.index', compact('alumni', 'tahunPelajaranList', 'stats'));
    }

    public function show(Siswa $siswa)
    {
        $this->authorize('view-siswa');

        $graduation = $siswa->siswaKelasRecords()
            ->where('status', 'lulus')
            ->with(['kelas.jurusan', 'tahunPelajaran'])
            ->latest('tanggal_keluar')
            ->firstOrFail();

        $siswa->load([
            'user:id,email',
            'ortu',
            'sekolahAsal',
            'siswaKelasRecords' => fn ($query) => $query
                ->with(['kelas.jurusan', 'tahunPelajaran'])
                ->orderBy('tanggal_masuk')
                ->orderBy('created_at'),
            'dataLulusan' => fn ($query) => $query
                ->with('tahunPelajaran')
                ->latest('updated_at'),
        ]);

        return view('admin.alumni.show', compact('siswa', 'graduation'));
    }

    private function graduationQuery(Request $request): Builder
    {
        return SiswaKelas::query()
            ->where('status', 'lulus')
            ->whereHas('siswa', function (Builder $query) use ($request) {
                $query->whereIn('status_siswa', ['lulus', 'alumni']);

                if ($request->filled('q')) {
                    $term = trim((string) $request->q);
                    $query->where(function (Builder $search) use ($term) {
                        $search->where('nama_lengkap', 'like', "%{$term}%")
                            ->orWhere('nisn', 'like', "%{$term}%")
                            ->orWhere('nis_lokal', 'like', "%{$term}%");
                    });
                }

                if (in_array($request->jenis_kelamin, ['L', 'P'], true)) {
                    $query->where('jenis_kelamin', $request->jenis_kelamin);
                }
            })
            ->when($request->filled('tahun_pelajaran_id'), fn (Builder $query) =>
                $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            );
    }

    private function statistics(): array
    {
        $base = SiswaKelas::query()
            ->where('siswa_kelas.status', 'lulus')
            ->join('siswa', 'siswa.id', '=', 'siswa_kelas.siswa_id')
            ->whereNull('siswa.deleted_at')
            ->whereIn('siswa.status_siswa', ['lulus', 'alumni']);

        $byYear = (clone $base)
            ->join('tahun_pelajaran', 'tahun_pelajaran.id', '=', 'siswa_kelas.tahun_pelajaran_id')
            ->selectRaw('tahun_pelajaran.id, tahun_pelajaran.nama, tahun_pelajaran.tahun_mulai, COUNT(DISTINCT siswa_kelas.siswa_id) as total')
            ->groupBy('tahun_pelajaran.id', 'tahun_pelajaran.nama', 'tahun_pelajaran.tahun_mulai')
            ->orderBy('tahun_pelajaran.tahun_mulai')
            ->get();

        $latest = $byYear->last();

        return [
            'total' => (clone $base)->distinct()->count('siswa_kelas.siswa_id'),
            'laki_laki' => (clone $base)->where('siswa.jenis_kelamin', 'L')->distinct()->count('siswa_kelas.siswa_id'),
            'perempuan' => (clone $base)->where('siswa.jenis_kelamin', 'P')->distinct()->count('siswa_kelas.siswa_id'),
            'angkatan' => $byYear->count(),
            'terbaru_label' => $latest?->nama,
            'terbaru_total' => (int) ($latest?->total ?? 0),
            'labels' => $byYear->pluck('nama')->values(),
            'values' => $byYear->pluck('total')->map(fn ($total) => (int) $total)->values(),
        ];
    }
}
