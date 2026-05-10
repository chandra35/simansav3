<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalHariJam;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class JadwalHariJamController extends Controller
{
    /**
     * GET /admin/jadwal-hari-jam
     * Kembalikan daftar slot jam untuk hari+semester+tahun tertentu.
     */
    public function index(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
            'hari'               => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
        ]);

        $slots = JadwalHariJam::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)
            ->where('hari', $request->hari)
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slots->map(fn($s) => $this->formatSlot($s)),
        ]);
    }

    /**
     * POST /admin/jadwal-hari-jam
     * Tambah slot jam ke hari tertentu.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
            'hari'               => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'tipe'               => 'required|in:pelajaran,istirahat,upacara,khusus',
            'waktu_mulai'        => 'nullable|date_format:H:i',
            'waktu_selesai'      => 'nullable|date_format:H:i',
            'label'              => 'nullable|string|max:60',
        ]);

        // Cari urutan berikutnya
        $maxUrutan = JadwalHariJam::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('semester', $validated['semester'])
            ->where('hari', $validated['hari'])
            ->max('urutan') ?? 0;

        // Tentukan jam_ke (hanya untuk tipe pelajaran)
        $jamKe = null;
        if ($validated['tipe'] === 'pelajaran') {
            $maxJamKe = JadwalHariJam::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
                ->where('semester', $validated['semester'])
                ->where('hari', $validated['hari'])
                ->whereNotNull('jam_ke')
                ->max('jam_ke') ?? 0;
            $jamKe = $maxJamKe + 1;
        }

        $slot = JadwalHariJam::create([
            'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
            'semester'           => $validated['semester'],
            'hari'               => $validated['hari'],
            'urutan'             => $maxUrutan + 1,
            'jam_ke'             => $jamKe,
            'waktu_mulai'        => $validated['waktu_mulai'] ?? null,
            'waktu_selesai'      => $validated['waktu_selesai'] ?? null,
            'tipe'               => $validated['tipe'],
            'label'              => $validated['label'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slot jam berhasil ditambahkan.',
            'data'    => $this->formatSlot($slot),
        ]);
    }

    /**
     * DELETE /admin/jadwal-hari-jam/{hariJam}
     * Hapus slot jam dari hari tertentu.
     * Juga hapus jadwal pelajaran yang ada di slot ini.
     */
    public function destroy(JadwalHariJam $hariJam)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $tahunId  = $hariJam->tahun_pelajaran_id;
        $semester = $hariJam->semester;
        $hari     = $hariJam->hari;
        $oldJamKe = $hariJam->jam_ke;

        // Hapus jadwal pelajaran yang terkait dengan slot ini (jika pelajaran)
        if ($hariJam->tipe === 'pelajaran' && $oldJamKe) {
            \App\Models\JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('hari', $hari)
                ->where('jam_ke', $oldJamKe)
                ->delete();
        }

        $hariJam->delete();

        // Renumber: geser semua jam_ke yang lebih besar ke bawah 1
        if ($oldJamKe) {
            // Update jadwal_hari_jam
            JadwalHariJam::where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('hari', $hari)
                ->where('tipe', 'pelajaran')
                ->where('jam_ke', '>', $oldJamKe)
                ->orderBy('jam_ke')
                ->get()
                ->each(function ($slot) {
                    $slot->decrement('jam_ke');
                });

            // Sync jadwal_pelajaran yang ada
            \App\Models\JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('hari', $hari)
                ->where('jam_ke', '>', $oldJamKe)
                ->orderBy('jam_ke')
                ->get()
                ->each(function ($j) {
                    $j->decrement('jam_ke');
                });
        }

        return response()->json([
            'success' => true,
            'message' => 'Slot jam berhasil dihapus.',
        ]);
    }

    /**
     * POST /admin/jadwal-hari-jam/generate-default
     * Generate slot jam default untuk semua hari dalam satu semester.
     * Senin: Upacara + 8 jam pelajaran
     * Selasa–Jumat: 8 jam pelajaran
     * Sabtu: 6 jam pelajaran
     * Hari yang sudah punya slot akan dilewati (tidak dioverwrite).
     */
    public function generateDefault(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
        ]);

        $jamPerHari = [
            'senin'  => 8,
            'selasa' => 8,
            'rabu'   => 8,
            'kamis'  => 8,
            'jumat'  => 8,
            'sabtu'  => 6,
        ];

        $created = 0;
        $skipped = [];

        foreach ($jamPerHari as $hari => $jumlahJam) {
            $existing = JadwalHariJam::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
                ->where('semester', $validated['semester'])
                ->where('hari', $hari)
                ->count();

            if ($existing > 0) {
                $skipped[] = ucfirst($hari);
                continue;
            }

            $urutan = 1;
            $jamKe  = 1;

            // Senin selalu mulai dengan Upacara Bendera
            if ($hari === 'senin') {
                JadwalHariJam::create([
                    'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                    'semester'           => $validated['semester'],
                    'hari'               => $hari,
                    'urutan'             => $urutan++,
                    'jam_ke'             => null,
                    'waktu_mulai'        => null,
                    'waktu_selesai'      => null,
                    'tipe'               => 'upacara',
                    'label'              => 'Upacara Bendera',
                ]);
                $created++;
            }

            for ($i = 0; $i < $jumlahJam; $i++) {
                JadwalHariJam::create([
                    'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                    'semester'           => $validated['semester'],
                    'hari'               => $hari,
                    'urutan'             => $urutan++,
                    'jam_ke'             => $jamKe++,
                    'waktu_mulai'        => null,
                    'waktu_selesai'      => null,
                    'tipe'               => 'pelajaran',
                    'label'              => null,
                ]);
                $created++;
            }
        }

        $msg = "Berhasil generate {$created} slot jam.";
        if (!empty($skipped)) {
            $msg .= ' Dilewati (sudah ada): ' . implode(', ', $skipped) . '.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * POST /admin/jadwal-hari-jam/reorder
     * Simpan urutan baru slot jam setelah drag-and-drop.
     * Juga renumber jam_ke sesuai urutan pelajaran terbaru.
     */
    public function reorder(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
            'hari'               => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'order'              => 'required|array',
            'order.*'            => 'required|exists:jadwal_hari_jam,id',
        ]);

        $tahunId  = $validated['tahun_pelajaran_id'];
        $semester = $validated['semester'];
        $hari     = $validated['hari'];
        $order    = $validated['order']; // array of slot IDs in new order

        // Ambil semua slot hari ini
        $slots = JadwalHariJam::where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('hari', $hari)
            ->get()
            ->keyBy('id');

        $jamKe = 1;
        foreach ($order as $urutan => $slotId) {
            $slot = $slots[$slotId] ?? null;
            if (!$slot) continue;

            $newJamKe = $slot->tipe === 'pelajaran' ? $jamKe++ : null;

            // Jika jam_ke berubah, sync jadwal_pelajaran
            if ($slot->tipe === 'pelajaran' && $slot->jam_ke !== null && $slot->jam_ke !== $newJamKe) {
                \App\Models\JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('hari', $hari)
                    ->where('jam_ke', $slot->jam_ke)
                    ->update(['jam_ke' => $newJamKe]);
            }

            $slot->update([
                'urutan' => $urutan + 1,
                'jam_ke' => $newJamKe,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan berhasil disimpan.']);
    }

    private function formatSlot(JadwalHariJam $s): array
    {
        return [
            'id'           => $s->id,
            'hari'         => $s->hari,
            'semester'     => $s->semester,
            'urutan'       => $s->urutan,
            'jam_ke'       => $s->jam_ke,
            'waktu_mulai'  => $s->waktu_mulai,
            'waktu_selesai'=> $s->waktu_selesai,
            'tipe'         => $s->tipe,
            'label'        => $s->label,
            'display_label'=> $s->displayLabel(),
            'is_pelajaran' => $s->isPelajaran(),
        ];
    }
}
