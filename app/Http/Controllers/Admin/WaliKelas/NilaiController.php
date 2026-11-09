<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\MataPelajaran;
use App\Models\PenilaianSiswa;
use Illuminate\Http\Request;

class NilaiController extends BaseWaliKelasController
{
    public function index(Request $request)
    {
        $kelas = $this->resolveKelas($request->query('kelas_id'));
        $kelasList = $this->waliClasses();
        $activeYear = $this->activeYear();
        $semester = (int)$request->query('semester', $activeYear?->semester_aktif === 'Genap' ? 2 : 1);

        $selectedMapelId = $request->query('mata_pelajaran_id');
        $selectedKategori = $request->query('kategori');

        $students = $this->classStudents($kelas);
        $studentIds = $students->pluck('id');

        // Distinct subjects that have grades for this class and semester
        $availableMapelIds = PenilaianSiswa::where('kelas_id', $kelas->id)
            ->where('tahun_pelajaran_id', $activeYear?->id)
            ->where('semester', $semester)
            ->distinct()
            ->pluck('mata_pelajaran_id');

        $mapelList = MataPelajaran::whereIn('id', $availableMapelIds)
            ->orderBy('nama')
            ->get();

        if ($mapelList->isEmpty()) {
            $mapelList = MataPelajaran::orderBy('nama')->get();
        }

        // Query Penilaian Siswa
        $query = PenilaianSiswa::with(['siswa', 'mataPelajaran', 'gtk'])
            ->where('kelas_id', $kelas->id)
            ->where('tahun_pelajaran_id', $activeYear?->id)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $studentIds);

        if ($selectedMapelId) {
            $query->where('mata_pelajaran_id', $selectedMapelId);
        }

        if ($selectedKategori) {
            $query->where('kategori', $selectedKategori);
        }

        $scores = $query->orderBy('kategori')->orderBy('created_at')->get();

        // Matrix / Pivot view if mapel selected
        $assessments = $scores->unique(fn ($item) => $item->kategori . '-' . $item->nama_penilaian)->values();
        $scoresGrouped = $scores->groupBy('siswa_id');

        // Statistics
        $totalEvaluations = $scores->count();
        $averageScore = $totalEvaluations > 0 ? round($scores->avg('nilai_akhir'), 1) : 0;
        $highestScore = $totalEvaluations > 0 ? $scores->max('nilai_akhir') : 0;
        $lowestScore = $totalEvaluations > 0 ? $scores->min('nilai_akhir') : 0;

        return view('admin.gtk.wali.nilai.index', compact(
            'kelas',
            'kelasList',
            'activeYear',
            'semester',
            'students',
            'mapelList',
            'selectedMapelId',
            'selectedKategori',
            'scores',
            'assessments',
            'scoresGrouped',
            'totalEvaluations',
            'averageScore',
            'highestScore',
            'lowestScore'
        ));
    }
}
