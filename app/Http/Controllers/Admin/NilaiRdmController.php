<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\NilaiSiswa;
use App\Models\SiswaKelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\StudentAccessScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiRdmController extends Controller
{
    public function __construct(private readonly StudentAccessScope $studentAccess)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('view-nilai-rdm');

        $year = TahunPelajaran::query()->active()->firstOrFail();
        $classIds = $this->studentAccess->classIds($request->user());
        abort_if($classIds !== null && $classIds->isEmpty(), 403, 'Belum ada rombel aktif dalam penugasan Anda.');

        $classes = Kelas::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->where('is_active', true)
            ->when($classIds !== null, fn ($query) => $query->whereIn('id', $classIds))
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get();
        abort_if($classes->isEmpty(), 403, 'Rombel aktif tidak ditemukan dalam penugasan Anda.');

        $roster = SiswaKelas::query()
            ->with('siswa:id,nama_lengkap,nisn')
            ->where('tahun_pelajaran_id', $year->id)
            ->where('status', 'aktif')
            ->whereIn('kelas_id', $classes->pluck('id'))
            ->whereNull('deleted_at')
            ->orderBy(Siswa::query()
                ->select('nama_lengkap')
                ->whereColumn('siswa.id', 'siswa_kelas.siswa_id'))
            ->get();
        $studentIds = $roster->pluck('siswa_id')->unique()->values();

        $scores = NilaiSiswa::query()
            ->with('mataPelajaran:id,nama_mapel,kode_mapel')
            ->where('tahun_pelajaran_id', $year->id)
            ->where('sumber_data', 'rdm_sync')
            ->whereIn('siswa_id', $studentIds)
            ->orderBy('semester')->orderBy('mata_pelajaran_id')
            ->get()
            ->groupBy('siswa_id');

        $rows = $roster->map(function (SiswaKelas $membership) use ($scores) {
            $studentScores = $scores->get($membership->siswa_id, collect());
            $latestImport = $studentScores->max('imported_at');

            return (object) [
                'siswa' => $membership->siswa,
                'kelas_id' => $membership->kelas_id,
                'nilai_count' => $studentScores->count(),
                'mapel_count' => $studentScores->pluck('mata_pelajaran_id')->unique()->count(),
                'semester_count' => $studentScores->pluck('semester')->unique()->count(),
                'average' => $studentScores->pluck('nilai')->filter(fn ($value) => $value !== null)->avg(),
                'latest_import' => $latestImport,
            ];
        });

        $mapelSummary = NilaiSiswa::query()
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'nilai_siswa.mata_pelajaran_id')
            ->where('nilai_siswa.tahun_pelajaran_id', $year->id)
            ->where('nilai_siswa.sumber_data', 'rdm_sync')
            ->whereIn('nilai_siswa.siswa_id', $studentIds)
            ->select([
                'mata_pelajaran.nama_mapel', 'mata_pelajaran.kode_mapel',
                DB::raw('COUNT(*) as nilai_count'),
                DB::raw('COUNT(DISTINCT nilai_siswa.siswa_id) as student_count'),
                DB::raw('AVG(nilai_siswa.nilai) as average'),
            ])
            ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kode_mapel')
            ->orderBy('mata_pelajaran.nama_mapel')->get();

        $scoreCount = $scores->flatten(1)->count();

        return view('admin.nilai-rdm.index', compact('year', 'classes', 'rows', 'mapelSummary', 'scoreCount'));
    }

    /** Detail nilai RDM hanya untuk siswa dalam rombel aktif akun yang sedang login. */
    public function show(Request $request, Siswa $siswa)
    {
        $this->authorize('view-nilai-rdm');

        $year = TahunPelajaran::query()->active()->firstOrFail();
        $classIds = $this->studentAccess->classIds($request->user());
        abort_if($classIds !== null && $classIds->isEmpty(), 403, 'Belum ada rombel aktif dalam penugasan Anda.');

        $membership = SiswaKelas::query()
            ->with('kelas:id,nama_kelas')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $year->id)
            ->where('status', 'aktif')
            ->when($classIds !== null, fn ($query) => $query->whereIn('kelas_id', $classIds))
            ->whereNull('deleted_at')
            ->first();

        abort_if($membership === null, 404);

        $scores = NilaiSiswa::query()
            ->with('mataPelajaran:id,nama_mapel,kode_mapel')
            ->where('tahun_pelajaran_id', $year->id)
            ->where('sumber_data', 'rdm_sync')
            ->where('siswa_id', $siswa->id)
            ->orderBy('semester')
            ->orderBy('mata_pelajaran_id')
            ->get()
            ->groupBy('semester');

        $scoreCount = $scores->flatten(1)->count();
        $average = $scores->flatten(1)->pluck('nilai')->filter(fn ($value) => $value !== null)->avg();

        return view('admin.nilai-rdm.show', compact('year', 'siswa', 'membership', 'scores', 'scoreCount', 'average'));
    }
}
