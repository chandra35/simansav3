<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalJamConfig;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalJamConfigController extends Controller
{
    /**
     * Halaman konfigurasi jam pelajaran per tahun pelajaran.
     */
    public function index(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $tahunList  = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId    = $request->tahun_pelajaran_id ?? $tahunAktif?->id;
        $tahunDipilih = $tahunId ? TahunPelajaran::find($tahunId) : null;

        $jamList = $tahunId
            ? JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)
                ->orderBy('urutan')
                ->get()
            : collect();

        return view('admin.jadwal-jam-config.index', compact(
            'tahunList', 'tahunAktif', 'tahunDipilih', 'jamList'
        ));
    }

    /**
     * Generate otomatis baris jam config dari parameter dan simpan ke DB.
     * Body: tahun_pelajaran_id, jam_mulai, durasi_menit, istirahat (array)
     */
    public function generate(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'jam_mulai'          => 'required|date_format:H:i',
            'durasi_menit'       => 'required|integer|min:20|max:120',
            'jam_pulang'         => 'required|date_format:H:i',
            'istirahat'          => 'nullable|array',
            'istirahat.*.setelah_jam' => 'required_with:istirahat|integer|min:1',
            'istirahat.*.durasi'      => 'required_with:istirahat|integer|min:5|max:90',
            'istirahat.*.label'       => 'nullable|string|max:50',
        ]);

        $tahunId   = $request->tahun_pelajaran_id;
        $rows      = JadwalJamConfig::generateRows(
            $request->jam_mulai,
            (int) $request->durasi_menit,
            $request->istirahat ?? [],
            $request->jam_pulang
        );

        DB::transaction(function () use ($tahunId, $rows) {
            // Hapus config lama untuk tahun ini
            JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)->delete();

            foreach ($rows as $row) {
                JadwalJamConfig::create(array_merge($row, ['tahun_pelajaran_id' => $tahunId]));
            }
        });

        $saved = JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'success' => true,
            'message' => count($rows) . ' baris jam berhasil di-generate.',
            'data'    => $saved,
        ]);
    }

    /**
     * Simpan satu baris jam baru (tambah manual).
     */
    public function store(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'waktu_mulai'        => 'required|date_format:H:i',
            'waktu_selesai'      => 'required|date_format:H:i|after:waktu_mulai',
            'is_istirahat'       => 'boolean',
            'label'              => 'nullable|string|max:50',
        ]);

        $tahunId    = $request->tahun_pelajaran_id;
        $isIstirahat = $request->boolean('is_istirahat', false);

        // Hitung urutan berikutnya
        $maxUrutan = JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)->max('urutan') ?? 0;
        $maxJamKe  = JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)->whereNotNull('jam_ke')->max('jam_ke') ?? 0;

        $row = JadwalJamConfig::create([
            'tahun_pelajaran_id' => $tahunId,
            'urutan'             => $maxUrutan + 1,
            'jam_ke'             => $isIstirahat ? null : ($maxJamKe + 1),
            'waktu_mulai'        => $request->waktu_mulai,
            'waktu_selesai'      => $request->waktu_selesai,
            'is_istirahat'       => $isIstirahat,
            'label'              => $request->label,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Baris jam berhasil ditambahkan.',
            'data'    => $row,
        ]);
    }

    /**
     * Hapus satu baris jam config.
     */
    public function destroy(JadwalJamConfig $jamConfig)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $jamConfig->delete();

        return response()->json([
            'success' => true,
            'message' => 'Baris jam berhasil dihapus.',
        ]);
    }
}
