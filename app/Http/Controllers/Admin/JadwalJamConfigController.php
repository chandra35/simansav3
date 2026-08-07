<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalJamConfig;
use App\Models\JadwalHariJam;
use App\Models\JadwalPelajaran;
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
        $presetGenerator = $this->presetGenerator($jamList);

        return view('admin.jadwal-jam-config.index', compact(
            'tahunList', 'tahunAktif', 'tahunDipilih', 'jamList', 'presetGenerator'
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
        $tahun = TahunPelajaran::findOrFail($tahunId);
        $rows      = JadwalJamConfig::generateRows(
            $request->jam_mulai,
            (int) $request->durasi_menit,
            $request->istirahat ?? [],
            $request->jam_pulang
        );

        $sync = DB::transaction(function () use ($tahunId, $tahun, $rows) {
            // Hapus config lama untuk tahun ini
            JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)->delete();

            foreach ($rows as $row) {
                JadwalJamConfig::create(array_merge($row, ['tahun_pelajaran_id' => $tahunId]));
            }

            return $this->sinkronkanSlotDanJadwal($tahun);
        });

        $saved = JadwalJamConfig::where('tahun_pelajaran_id', $tahunId)
            ->orderBy('urutan')
            ->get();

        $response = [
            'success' => true,
            'message' => count($rows) . ' baris jam berhasil di-generate dan '.$sync['jadwal'].' jadwal mapel disinkronkan ke slot '.implode(', ', array_map('ucfirst', $tahun->hariKerja())).'.'
                . ($sync['tanpaSlot'] ? ' '.$sync['tanpaSlot'].' jadwal lama belum memiliki slot jam dan perlu disesuaikan.' : ''),
            'data'    => $saved,
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()->route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId])
            ->with('success', $response['message']);
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

        [$row, $sync] = DB::transaction(function () use ($tahunId, $maxUrutan, $maxJamKe, $isIstirahat, $request) {
            $row = JadwalJamConfig::create([
                'tahun_pelajaran_id' => $tahunId,
                'urutan'             => $maxUrutan + 1,
                'jam_ke'             => $isIstirahat ? null : ($maxJamKe + 1),
                'waktu_mulai'        => $request->waktu_mulai,
                'waktu_selesai'      => $request->waktu_selesai,
                'is_istirahat'       => $isIstirahat,
                'label'              => $request->label,
            ]);

            return [$row, $this->sinkronkanSlotDanJadwal(TahunPelajaran::findOrFail($tahunId))];
        });

        return response()->json([
            'success' => true,
            'message' => 'Baris jam berhasil ditambahkan dan '.$sync['jadwal'].' jadwal mapel disinkronkan.',
            'data'    => $row,
        ]);
    }

    /**
     * Hapus satu baris jam config.
     */
    public function destroy(JadwalJamConfig $jamConfig)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $sync = DB::transaction(function () use ($jamConfig) {
            $tahun = TahunPelajaran::findOrFail($jamConfig->tahun_pelajaran_id);
            $jamConfig->delete();

            return $this->sinkronkanSlotDanJadwal($tahun);
        });

        return response()->json([
            'success' => true,
            'message' => 'Baris jam berhasil dihapus dan jadwal mapel aktif telah disinkronkan.'
                . ($sync['tanpaSlot'] ? ' '.$sync['tanpaSlot'].' jadwal lama belum memiliki slot jam.' : ''),
        ]);
    }

    /** Sinkronkan slot harian dan waktu jadwal mapel dari konfigurasi jam terakhir. */
    private function sinkronkanSlotDanJadwal(TahunPelajaran $tahun): array
    {
        $configs = JadwalJamConfig::where('tahun_pelajaran_id', $tahun->id)
            ->orderBy('urutan')
            ->get();
        $hariSekolah = $tahun->hariKerja();
        $jadwalTersinkron = 0;

        foreach ([1, 2] as $semester) {
            // Hapus juga hari yang tidak lagi aktif, misalnya Sabtu setelah beralih ke 5 hari kerja.
            JadwalHariJam::where('tahun_pelajaran_id', $tahun->id)
                ->where('semester', $semester)
                ->whereIn('hari', JadwalHariJam::HARI)
                ->delete();

            foreach ($hariSekolah as $hari) {
                foreach ($configs as $config) {
                    JadwalHariJam::create([
                        'tahun_pelajaran_id' => $tahun->id,
                        'semester' => $semester,
                        'hari' => $hari,
                        'urutan' => $config->urutan,
                        'jam_ke' => $config->jam_ke,
                        'waktu_mulai' => $config->waktu_mulai,
                        'waktu_selesai' => $config->waktu_selesai,
                        'tipe' => $config->is_istirahat ? 'istirahat' : 'pelajaran',
                        'label' => $config->label,
                    ]);

                    if (! $config->is_istirahat) {
                        $jadwalTersinkron += JadwalPelajaran::where('tahun_pelajaran_id', $tahun->id)
                            ->where('semester', $semester)
                            ->where('hari', $hari)
                            ->where('jam_ke', $config->jam_ke)
                            ->update([
                                'jam_mulai' => $config->waktu_mulai,
                                'jam_selesai' => $config->waktu_selesai,
                            ]);
                    }
                }
            }
        }

        $tanpaSlot = JadwalPelajaran::query()
            ->where('tahun_pelajaran_id', $tahun->id)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('jadwal_hari_jam')
                    ->whereColumn('jadwal_hari_jam.tahun_pelajaran_id', 'jadwal_pelajaran.tahun_pelajaran_id')
                    ->whereColumn('jadwal_hari_jam.semester', 'jadwal_pelajaran.semester')
                    ->whereColumn('jadwal_hari_jam.hari', 'jadwal_pelajaran.hari')
                    ->whereColumn('jadwal_hari_jam.jam_ke', 'jadwal_pelajaran.jam_ke')
                    ->where('jadwal_hari_jam.tipe', 'pelajaran');
            })
            ->count();

        return ['jadwal' => $jadwalTersinkron, 'tanpaSlot' => $tanpaSlot];
    }

    /** Bentuk kembali parameter generator dari konfigurasi terakhir yang tersimpan. */
    private function presetGenerator($configs): array
    {
        $default = [
            'jam_mulai' => '07:00', 'jam_pulang' => '14:30', 'durasi_menit' => 45,
            'istirahat' => [],
        ];

        if ($configs->isEmpty()) {
            return $default;
        }

        $pelajaran = $configs->where('is_istirahat', false);
        $durasi = $pelajaran->map(function ($item) {
            [$jam, $menit] = array_map('intval', explode(':', substr($item->waktu_mulai, 0, 5)));
            [$akhirJam, $akhirMenit] = array_map('intval', explode(':', substr($item->waktu_selesai, 0, 5)));

            return (($akhirJam * 60) + $akhirMenit) - (($jam * 60) + $menit);
        })->countBy()->sortDesc()->keys()->first() ?? 45;

        $istirahat = $configs->where('is_istirahat', true)->take(2)->values()->map(function ($item) use ($configs) {
            $sebelumnya = $configs->where('urutan', '<', $item->urutan)
                ->where('is_istirahat', false)
                ->max('jam_ke');
            [$jam, $menit] = array_map('intval', explode(':', substr($item->waktu_mulai, 0, 5)));
            [$akhirJam, $akhirMenit] = array_map('intval', explode(':', substr($item->waktu_selesai, 0, 5)));

            return [
                'setelah_jam' => $sebelumnya,
                'durasi' => (($akhirJam * 60) + $akhirMenit) - (($jam * 60) + $menit),
                'label' => $item->label ?: 'Istirahat',
            ];
        })->all();

        return [
            'jam_mulai' => substr($pelajaran->first()?->waktu_mulai ?? $default['jam_mulai'], 0, 5),
            'jam_pulang' => substr($configs->last()->waktu_selesai, 0, 5),
            'durasi_menit' => (int) $durasi,
            'istirahat' => $istirahat,
        ];
    }
}
