<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaMapel;
use App\Models\AsramaPengampu;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengampuController extends Controller
{
    public function index(Request $request)
    {
        $activeYearId = TahunPelajaran::active()->value('id');
        $defaultYearId = $activeYearId ?: TahunPelajaran::orderByDesc('tahun_mulai')->value('id');
        $tahunId = $request->input('tahun_pelajaran_id', $defaultYearId);

        $kelasId = $request->input('asrama_kelas_id');
        $mapelId = $request->input('asrama_mapel_id');
        $asatidzId = $request->input('asrama_asatidz_id');
        $semester = $request->input('semester');
        $status = $request->input('status');

        $query = AsramaPengampu::with([
            'kelas.tahunPelajaran',
            'kelas.kelasReguler',
            'mapel',
            'asatidz.gtk',
        ])->withCount('nilai');

        if ($tahunId) {
            $query->whereHas('kelas', fn ($q) => $q->where('tahun_pelajaran_id', $tahunId));
        }

        if ($kelasId) {
            $query->where('asrama_kelas_id', $kelasId);
        }

        if ($mapelId) {
            $query->where('asrama_mapel_id', $mapelId);
        }

        if ($asatidzId) {
            $query->where('asrama_asatidz_id', $asatidzId);
        }

        if ($semester && in_array($semester, ['Ganjil', 'Genap'], true)) {
            $query->where('semester', $semester);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $records = $query->get()->sortBy([
            fn ($a, $b) => strcmp($a->kelas->nama_kelas ?? '', $b->kelas->nama_kelas ?? ''),
            fn ($a, $b) => ($a->mapel->urutan ?? 0) <=> ($b->mapel->urutan ?? 0),
            fn ($a, $b) => strcmp($a->semester, $b->semester),
        ])->values();

        // Statistics based on the selected academic year
        $baseStatQuery = AsramaPengampu::query();
        if ($tahunId) {
            $baseStatQuery->whereHas('kelas', fn ($q) => $q->where('tahun_pelajaran_id', $tahunId));
        }
        $totalAssignments = (clone $baseStatQuery)->count();
        $totalActive = (clone $baseStatQuery)->where('is_active', true)->count();
        $totalTeachers = (clone $baseStatQuery)->where('is_active', true)->distinct('asrama_asatidz_id')->count('asrama_asatidz_id');
        $totalClasses = (clone $baseStatQuery)->where('is_active', true)->distinct('asrama_kelas_id')->count('asrama_kelas_id');

        $years = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $kelasList = AsramaKelas::with('kelasReguler')
            ->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();
        $mapelList = AsramaMapel::where('is_active', true)->orderBy('urutan')->get();
        $asatidzList = AsramaAsatidz::with('gtk')
            ->where('is_active', true)
            ->where('dapat_mengampu_mapel', true)
            ->get()
            ->sortBy('gtk.nama_lengkap')
            ->values();

        return view('asrama.pengampu.index', [
            'records' => $records,
            'years' => $years,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'asatidzList' => $asatidzList,
            'selectedYear' => $tahunId,
            'selectedKelas' => $kelasId,
            'selectedMapel' => $mapelId,
            'selectedAsatidz' => $asatidzId,
            'selectedSemester' => $semester,
            'selectedStatus' => $status,
            'stats' => [
                'total_assignments' => $totalAssignments,
                'total_active' => $totalActive,
                'total_teachers' => $totalTeachers,
                'total_classes' => $totalClasses,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asrama_kelas_id' => ['required', 'exists:asrama_kelas,id'],
            'asrama_mapel_id' => ['required', 'exists:asrama_mapel,id'],
            'asrama_asatidz_id' => ['required', 'exists:asrama_asatidz,id'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $asatidz = AsramaAsatidz::where('is_active', true)
            ->where('dapat_mengampu_mapel', true)
            ->find($data['asrama_asatidz_id']);

        if (! $asatidz) {
            return back()->withInput()->with('error', 'GTK yang dipilih tidak aktif atau belum memiliki hak mengampu mapel di Asrama.');
        }

        $existing = AsramaPengampu::withTrashed()->where([
            'asrama_kelas_id' => $data['asrama_kelas_id'],
            'asrama_mapel_id' => $data['asrama_mapel_id'],
            'semester' => $data['semester'],
        ])->first();

        if ($existing) {
            if (! $existing->trashed()) {
                return back()->withInput()->with('error', 'Mata pelajaran tersebut sudah memiliki pengampu pada rombel dan semester yang dipilih.');
            }
            $existing->restore();
            $existing->update([
                'asrama_asatidz_id' => $data['asrama_asatidz_id'],
                'is_active' => $request->boolean('is_active', true),
                'updated_by' => $request->user()->id,
            ]);

            return back()->with('success', 'Penugasan guru mapel berhasil ditambahkan kembali.');
        }

        AsramaPengampu::create([
            'asrama_kelas_id' => $data['asrama_kelas_id'],
            'asrama_mapel_id' => $data['asrama_mapel_id'],
            'asrama_asatidz_id' => $data['asrama_asatidz_id'],
            'semester' => $data['semester'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Penugasan guru mapel asrama berhasil disimpan.');
    }

    public function update(Request $request, AsramaPengampu $pengampu)
    {
        $data = $request->validate([
            'asrama_kelas_id' => ['required', 'exists:asrama_kelas,id'],
            'asrama_mapel_id' => ['required', 'exists:asrama_mapel,id'],
            'asrama_asatidz_id' => ['required', 'exists:asrama_asatidz,id'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $asatidz = AsramaAsatidz::where('is_active', true)
            ->where('dapat_mengampu_mapel', true)
            ->find($data['asrama_asatidz_id']);

        if (! $asatidz) {
            return back()->withInput()->with('error', 'GTK yang dipilih tidak aktif atau belum memiliki hak mengampu mapel di Asrama.');
        }

        $conflict = AsramaPengampu::where('id', '!=', $pengampu->id)
            ->where([
                'asrama_kelas_id' => $data['asrama_kelas_id'],
                'asrama_mapel_id' => $data['asrama_mapel_id'],
                'semester' => $data['semester'],
            ])->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Kombinasi rombel, mapel, dan semester tersebut sudah digunakan oleh penugasan lain.');
        }

        $pengampu->update([
            'asrama_kelas_id' => $data['asrama_kelas_id'],
            'asrama_mapel_id' => $data['asrama_mapel_id'],
            'asrama_asatidz_id' => $data['asrama_asatidz_id'],
            'semester' => $data['semester'],
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Penugasan guru mapel asrama berhasil diperbarui.');
    }

    public function destroy(AsramaPengampu $pengampu)
    {
        if ($pengampu->nilai()->exists()) {
            return back()->with('error', 'Penugasan tidak dapat dihapus karena sudah memiliki data input nilai.');
        }

        $pengampu->delete();

        return back()->with('success', 'Penugasan guru mapel asrama berhasil dihapus.');
    }
}
