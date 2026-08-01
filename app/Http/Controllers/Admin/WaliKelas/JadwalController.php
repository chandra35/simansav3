<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\JadwalPelajaran;
use Illuminate\Http\Request;

/**
 * Jadwal (read-only) untuk wali kelas:
 *  - Jadwal mengajar pribadi (bila user punya data GTK), dan
 *  - Jadwal pelajaran rombel yang diampu sebagai wali kelas.
 */
class JadwalController extends BaseWaliKelasController
{
    public function index(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));
        $tahun = $this->activeYear();
        $gtk = $request->user()->gtk;

        $baseKelas = JadwalPelajaran::query()
            ->with(['mataPelajaran', 'gtk.user'])
            ->where('kelas_id', $kelas->id)
            ->where('is_active', true);
        if ($tahun) {
            $baseKelas->where('tahun_pelajaran_id', $tahun->id);
        }
        $jadwalKelas = $baseKelas->get()->groupBy('hari');

        $jadwalMengajar = collect();
        if ($gtk) {
            $q = JadwalPelajaran::query()
                ->with(['mataPelajaran', 'kelas'])
                ->where('gtk_id', $gtk->id)
                ->where('is_active', true);
            if ($tahun) {
                $q->where('tahun_pelajaran_id', $tahun->id);
            }
            $jadwalMengajar = $q->get()->groupBy('hari');
        }

        return view('admin.gtk.wali.jadwal.index', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'hariList' => JadwalPelajaran::HARI,
            'jadwalKelas' => $jadwalKelas,
            'jadwalMengajar' => $jadwalMengajar,
            'hasGtk' => (bool) $gtk,
        ]);
    }
}
