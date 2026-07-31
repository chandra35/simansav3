<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaPengampu;
use App\Models\AsramaRapor;
use App\Models\AsramaSantri;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tahunAktif = TahunPelajaran::query()->active()->first();
        $isManager = $user->can('view-asrama') || $user->can('manage-asrama');
        $asatidz = $user->gtk
            ? AsramaAsatidz::with('asrama')->where('gtk_id', $user->gtk->id)->where('is_active', true)->first()
            : null;
        $santri = $user->siswa
            ? AsramaSantri::with(['asrama', 'kelasAktif.kelas.tahunPelajaran'])
                ->where('siswa_id', $user->siswa->id)->where('status', 'aktif')->first()
            : null;

        abort_unless($isManager || $asatidz || $santri, 403);

        $stats = [
            'asrama' => $isManager ? Asrama::where('is_active', true)->count() : 1,
            'santri' => $isManager
                ? AsramaSantri::where('status', 'aktif')->count()
                : ($santri ? 1 : 0),
            'kelas' => $isManager
                ? AsramaKelas::where('is_active', true)
                    ->when($tahunAktif, fn ($query) => $query->where('tahun_pelajaran_id', $tahunAktif->id))
                    ->count()
                : ($asatidz
                    ? AsramaKelas::where('wali_asatidz_id', $asatidz->id)->where('is_active', true)->count()
                    : ($santri?->kelasAktif ? 1 : 0)),
            'pengampu' => $asatidz
                ? AsramaPengampu::where('asrama_asatidz_id', $asatidz->id)->where('is_active', true)->count()
                : 0,
        ];

        $assignments = $asatidz
            ? AsramaPengampu::with(['kelas.tahunPelajaran', 'mapel'])
                ->where('asrama_asatidz_id', $asatidz->id)
                ->where('is_active', true)
                ->latest()
                ->get()
            : collect();

        $studentReports = $santri
            ? AsramaRapor::with('kelasSantri.kelas.tahunPelajaran')
                ->whereHas('kelasSantri', fn ($query) => $query->where('asrama_santri_id', $santri->id))
                ->where('status', 'terbit')
                ->latest('published_at')
                ->get()
            : collect();

        return view('asrama.dashboard', compact(
            'tahunAktif', 'isManager', 'asatidz', 'santri', 'stats', 'assignments', 'studentReports'
        ));
    }
}
