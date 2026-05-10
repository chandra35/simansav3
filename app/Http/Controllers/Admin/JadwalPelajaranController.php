<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\JadwalJamConfig;
use App\Models\JadwalHariJam;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Gtk;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $tahunList   = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $tahunAktif  = TahunPelajaran::where('is_active', true)->first();
        $tahunId     = $request->tahun_pelajaran_id ?? $tahunAktif?->id;

        $kelasList = $tahunId
            ? Kelas::where('tahun_pelajaran_id', $tahunId)
                ->with(['jurusan', 'waliKelas'])
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        // Stats for UI
        $stats = ['kelas_ids_with_jadwal' => [], 'total_slots' => 0, 'jam_count' => 0];
        if ($tahunId && $kelasList->isNotEmpty()) {
            $jadwalStats = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)
                ->select('kelas_id', DB::raw('count(*) as slot_count'))
                ->groupBy('kelas_id')
                ->get();
            $stats['kelas_ids_with_jadwal'] = $jadwalStats->pluck('kelas_id')->toArray();
            $stats['total_slots'] = $jadwalStats->sum('slot_count');
        }
        $stats['kelas_with_jadwal'] = count($stats['kelas_ids_with_jadwal']);

        return view('admin.jadwal-pelajaran.index', compact(
            'tahunList', 'tahunAktif', 'tahunId', 'kelasList', 'stats'
        ));
    }

    public function timetable(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $tahunList   = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $tahunAktif  = TahunPelajaran::where('is_active', true)->first();
        $tahunId     = $request->tahun_pelajaran_id ?? $tahunAktif?->id;
        $kelasId     = $request->kelas_id;
        $semester    = (int) ($request->semester ?? 1);

        $kelasList = $tahunId
            ? Kelas::where('tahun_pelajaran_id', $tahunId)
                ->with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        $kelasObj = $kelasId ? Kelas::with(['jurusan', 'tahunPelajaran'])->find($kelasId) : null;

        // Ambil slot jam per hari dari jadwal_hari_jam
        $hariJamMap = [];
        if ($tahunId) {
            $allSlots = JadwalHariJam::where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->orderBy('hari')
                ->orderBy('urutan')
                ->get();
            foreach ($allSlots as $slot) {
                $hariJamMap[$slot->hari][] = $slot;
            }
        }

        $jadwalRaw = ($kelasId && $tahunId)
            ? JadwalPelajaran::with(['mataPelajaran', 'gtk'])
                ->where('kelas_id', $kelasId)
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('is_active', true)
                ->get()
            : collect();

        $jadwalMap = [];
        foreach ($jadwalRaw as $j) {
            $jadwalMap[$j->hari][$j->jam_ke] = $j;
        }

        $hariList = array_keys(JadwalPelajaran::HARI);

        return view('admin.jadwal-pelajaran.timetable', compact(
            'tahunList', 'tahunAktif', 'tahunId', 'kelasList', 'kelasObj',
            'kelasId', 'semester', 'hariJamMap',
            'jadwalMap', 'hariList'
        ));
    }

    public function timetableData(Request $request)
    {
        $request->validate([
            'kelas_id'            => 'required|exists:kelas,id',
            'tahun_pelajaran_id'  => 'required|exists:tahun_pelajaran,id',
            'semester'            => 'required|integer|in:1,2',
        ]);

        $jadwal = JadwalPelajaran::with(['mataPelajaran', 'gtk'])
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)
            ->where('is_active', true)
            ->get()
            ->map(function ($j) {
                return [
                    'id'          => $j->id,
                    'hari'        => $j->hari,
                    'jam_ke'      => $j->jam_ke,
                    'mapel_id'    => $j->mapel_id,
                    'mapel_nama'  => $j->mataPelajaran?->nama_mapel ?? '-',
                    'mapel_kode'  => $j->mataPelajaran?->kode_mapel ?? '',
                    'gtk_id'      => $j->gtk_id,
                    'gtk_nama'    => $j->gtk?->nama_lengkap ?? '-',
                    'gtk_kode'    => $j->gtk?->kode_gtk ?? '',
                    'ruangan'     => $j->ruangan ?? '',
                    'catatan'     => $j->catatan ?? '',
                ];
            });

        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    public function guruOptions(Request $request)
    {
        $tahunId  = $request->tahun_pelajaran_id;
        $hari     = $request->hari;
        $jamKe    = $request->jam_ke;
        $semester = $request->semester ?? 1;
        $excludeId = $request->exclude_id;

        $konflikQuery = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe)
            ->where('semester', $semester)
            ->where('is_active', true);
        if ($excludeId) {
            $konflikQuery->where('id', '!=', $excludeId);
        }
        $konflikGtkIds = $konflikQuery->pluck('gtk_id')->toArray();

        // Hitung JTM per guru untuk semester ini (lintas semua kelas)
        $jtmCounts = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->select('gtk_id', DB::raw('count(*) as jtm'))
            ->groupBy('gtk_id')
            ->pluck('jtm', 'gtk_id')
            ->toArray();

        $gtks = Gtk::orderBy('nama_lengkap')
            ->where('jenis_ptk', 'like', '%Guru%')
            ->get(['id', 'nama_lengkap', 'nip', 'kode_gtk'])
            ->map(function ($g) use ($konflikGtkIds, $jtmCounts) {
                $jtm = $jtmCounts[$g->id] ?? 0;
                return [
                    'id'      => $g->id,
                    'nama'    => $g->nama_lengkap,
                    'nip'     => $g->nip ?? '',
                    'kode'    => $g->kode_gtk ?? '',
                    'konflik' => in_array($g->id, $konflikGtkIds),
                    'jtm'     => $jtm,
                    'jtm_status' => $jtm < 24 ? 'kurang' : ($jtm > 40 ? 'lebih' : 'normal'),
                ];
            });

        return response()->json(['success' => true, 'data' => $gtks]);
    }

    public function mapelOptions(Request $request)
    {
        $tahunId = $request->tahun_pelajaran_id;
        $kelasId = $request->kelas_id;

        $tingkat = null;
        if ($kelasId) {
            $tingkat = Kelas::find($kelasId)?->tingkat;
        }

        $query = MataPelajaran::where('is_active', true)
            ->orderBy('nama_mapel');

        if ($tahunId) {
            $query->where('tahun_pelajaran_id', $tahunId);
        }

        if ($tingkat) {
            $query->whereJsonContains('tingkat', (int) $tingkat);
        }

        $mapels = $query->get(['id', 'kode_mapel', 'nama_mapel', 'kelompok'])
            ->map(fn($m) => [
                'id'       => $m->id,
                'nama'     => $m->nama_mapel,
                'kode'     => $m->kode_mapel ?? '',
                'kelompok' => $m->kelompok ?? '',
            ]);

        return response()->json(['success' => true, 'data' => $mapels]);
    }

    /**
     * GET /admin/jadwal-pelajaran/guru-mapel-in-kelas
     * Kembalikan mapel yang diajarkan guru tertentu di kelas tertentu
     * (berdasarkan jadwal yang sudah ada). Untuk auto-fill mapel saat pilih guru.
     */
    public function guruMapelInKelas(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $request->validate([
            'gtk_id'             => 'required|exists:gtks,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'nullable|integer|in:1,2',
        ]);

        $jadwal = JadwalPelajaran::with('mataPelajaran')
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('gtk_id', $request->gtk_id)
            ->where('is_active', true)
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->first();

        if (!$jadwal || !$jadwal->mataPelajaran) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mapel_id'   => $jadwal->mapel_id,
                'mapel_nama' => $jadwal->mataPelajaran->nama_mapel,
                'mapel_kode' => $jadwal->mataPelajaran->kode_mapel ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'mapel_id'           => 'required|exists:mata_pelajaran,id',
            'gtk_id'             => 'required|exists:gtks,id',
            'hari'               => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_ke'             => 'required|integer|min:1|max:15',
            'ruangan'            => 'nullable|string|max:50',
            'semester'           => 'required|integer|in:1,2',
            'catatan'            => 'nullable|string|max:255',
        ]);

        $conflictKelas = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->where('is_active', true)
            ->exists();

        if ($conflictKelas) {
            return response()->json(['success' => false, 'message' => 'Kelas sudah memiliki jadwal di jam ini.'], 422);
        }

        $conflictGuru = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->where('is_active', true)
            ->first();

        if ($conflictGuru) {
            $kelasLain = $conflictGuru->kelas?->nama_kelas ?? 'kelas lain';
            return response()->json([
                'success'       => false,
                'message'       => "Guru sudah mengajar di {$kelasLain} pada jam ini.",
                'conflict_type' => 'guru',
            ], 422);
        }

        // Cegah 1 guru mengajar mapel berbeda di hari yang sama
        $conflictMapelGuru = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $validated['hari'])
            ->where('semester', $validated['semester'])
            ->where('mapel_id', '!=', $validated['mapel_id'])
            ->where('is_active', true)
            ->with('mataPelajaran')
            ->first();

        if ($conflictMapelGuru) {
            $mapelLain = $conflictMapelGuru->mataPelajaran?->nama_mapel ?? 'mapel lain';
            $hariLabel = ucfirst($validated['hari']);
            return response()->json([
                'success' => false,
                'message' => "Guru sudah mengajar {$mapelLain} di hari {$hariLabel} ini. Satu guru hanya boleh mengajar satu mapel per hari.",
            ], 422);
        }

        // Ambil waktu dari jadwal_hari_jam jika ada
        $hariJam = JadwalHariJam::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('semester', $validated['semester'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->first();

        // Cari termasuk soft-deleted — updateOrCreate tidak cukup karena SoftDeletes
        $existing = JadwalPelajaran::withTrashed()
            ->where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->first();

        $fillData = array_merge($validated, [
            'jam_mulai'   => $hariJam?->waktu_mulai,
            'jam_selesai' => $hariJam?->waktu_selesai,
            'is_active'   => true,
            'created_by'  => auth()->id(),
        ]);

        if ($existing) {
            // Jika soft-deleted, hanya restore jika mapel sama (restore assignment lama yang dihapus)
            if ($existing->trashed()) {
                if ($existing->mapel_id !== $validated['mapel_id']) {
                    // Mapel berbeda → don't restore, create new instead
                    $jadwal = JadwalPelajaran::create($fillData);
                } else {
                    $existing->restore();
                    $existing->fill($fillData)->save();
                    $jadwal = $existing;
                }
            } else {
                // Sudah aktif → update
                $existing->fill($fillData)->save();
                $jadwal = $existing;
            }
        } else {
            $jadwal = JadwalPelajaran::create($fillData);
        }

        $jadwal->load(['mataPelajaran', 'gtk']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data' => [
                'id'         => $jadwal->id,
                'mapel_nama' => $jadwal->mataPelajaran?->nama_mapel ?? '-',
                'mapel_kode' => $jadwal->mataPelajaran?->kode_mapel ?? '',
                'gtk_nama'   => $jadwal->gtk?->nama_lengkap ?? '-',
                'gtk_kode'   => $jadwal->gtk?->kode_gtk ?? '',
                'ruangan'    => $jadwal->ruangan ?? '',
            ],
        ]);
    }

    public function show(JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('view-jadwal-pelajaran');

        $jadwalPelajaran->load(['mataPelajaran', 'gtk', 'kelas', 'tahunPelajaran']);

        return response()->json([
            'success' => true,
            'data' => [
                'id'                  => $jadwalPelajaran->id,
                'tahun_pelajaran_id'  => $jadwalPelajaran->tahun_pelajaran_id,
                'kelas_id'            => $jadwalPelajaran->kelas_id,
                'mapel_id'            => $jadwalPelajaran->mapel_id,
                'mapel_nama'          => $jadwalPelajaran->mataPelajaran?->nama_mapel,
                'gtk_id'              => $jadwalPelajaran->gtk_id,
                'gtk_nama'            => $jadwalPelajaran->gtk?->nama_lengkap,
                'hari'                => $jadwalPelajaran->hari,
                'jam_ke'              => $jadwalPelajaran->jam_ke,
                'ruangan'             => $jadwalPelajaran->ruangan,
                'semester'            => $jadwalPelajaran->semester,
                'catatan'             => $jadwalPelajaran->catatan,
            ],
        ]);
    }

    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'mapel_id'  => 'required|exists:mata_pelajaran,id',
            'gtk_id'    => 'required|exists:gtks,id',
            'ruangan'   => 'nullable|string|max:50',
            'catatan'   => 'nullable|string|max:255',
        ]);

        $conflictGuru = JadwalPelajaran::where('tahun_pelajaran_id', $jadwalPelajaran->tahun_pelajaran_id)
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $jadwalPelajaran->hari)
            ->where('jam_ke', $jadwalPelajaran->jam_ke)
            ->where('semester', $jadwalPelajaran->semester)
            ->where('is_active', true)
            ->where('id', '!=', $jadwalPelajaran->id)
            ->first();

        if ($conflictGuru) {
            $kelasLain = $conflictGuru->kelas?->nama_kelas ?? 'kelas lain';
            return response()->json([
                'success' => false,
                'message' => "Guru sudah mengajar di {$kelasLain} pada jam ini.",
            ], 422);
        }

        // Cegah 1 guru mengajar mapel berbeda di hari yang sama
        $conflictMapelGuru = JadwalPelajaran::where('tahun_pelajaran_id', $jadwalPelajaran->tahun_pelajaran_id)
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $jadwalPelajaran->hari)
            ->where('semester', $jadwalPelajaran->semester)
            ->where('mapel_id', '!=', $validated['mapel_id'])
            ->where('is_active', true)
            ->where('id', '!=', $jadwalPelajaran->id)
            ->with('mataPelajaran')
            ->first();

        if ($conflictMapelGuru) {
            $mapelLain = $conflictMapelGuru->mataPelajaran?->nama_mapel ?? 'mapel lain';
            $hariLabel = ucfirst($jadwalPelajaran->hari);
            return response()->json([
                'success' => false,
                'message' => "Guru sudah mengajar {$mapelLain} di hari {$hariLabel} ini. Satu guru hanya boleh mengajar satu mapel per hari.",
            ], 422);
        }

        $jadwalPelajaran->update($validated);
        $jadwalPelajaran->load(['mataPelajaran', 'gtk']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => [
                'id'         => $jadwalPelajaran->id,
                'mapel_nama' => $jadwalPelajaran->mataPelajaran?->nama_mapel ?? '-',
                'mapel_kode' => $jadwalPelajaran->mataPelajaran?->kode_mapel ?? '',
                'gtk_nama'   => $jadwalPelajaran->gtk?->nama_lengkap ?? '-',
                'gtk_kode'   => $jadwalPelajaran->gtk?->kode_gtk ?? '',
                'ruangan'    => $jadwalPelajaran->ruangan ?? '',
            ],
        ]);
    }

    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('manage-jadwal-pelajaran');
        $jadwalPelajaran->delete();
        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus.']);
    }

    /**
     * GET /admin/jadwal-pelajaran/guru-jtm-summary
     * Rekap JTM per guru (Jam Tatap Muka) untuk tahun pelajaran + semester.
     * JTM untuk MA/MAN: 1 slot jadwal = 1 JTM (45 menit).
     * Min sertifikasi: 24 JTM/minggu, Maks: 40 JTM/minggu.
     * Tugas tambahan dihitung ekuivalensi: Wali Kelas +6, Wakasek +12, Ka.Lab/Perpus +12.
     */
    public function guruJtmSummary(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
        ]);

        $tahunId  = $request->tahun_pelajaran_id;
        $semester = (int) $request->semester;

        // JTM mengajar per guru (lintas semua kelas)
        $jtmRows = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->select('gtk_id', DB::raw('count(*) as jtm_mengajar'))
            ->groupBy('gtk_id')
            ->get()
            ->keyBy('gtk_id');

        if ($jtmRows->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $gtkIds = $jtmRows->keys()->toArray();
        $gtks   = Gtk::whereIn('id', $gtkIds)
            ->orderBy('nama_lengkap')
            ->get(['id', 'user_id', 'nama_lengkap', 'kode_gtk', 'jabatan']);

        // Guru yang menjadi wali kelas di tahun pelajaran ini
        $waliKelasUserIds = Kelas::where('tahun_pelajaran_id', $tahunId)
            ->whereNotNull('wali_kelas_id')
            ->pluck('wali_kelas_id')
            ->toArray();

        $result = $gtks->map(function ($gtk) use ($jtmRows, $waliKelasUserIds, $tahunId) {
            $jtmMengajar    = (int) ($jtmRows[$gtk->id]->jtm_mengajar ?? 0);
            $jtmEkuivalensi = 0;
            $tugasTambahan  = [];

            // Ekuivalensi Wali Kelas: +6 JTM
            if (in_array($gtk->user_id, $waliKelasUserIds)) {
                $jtmEkuivalensi += 6;
                $tugasTambahan[] = ['label' => 'Wali Kelas', 'jtm' => 6];
            }

            // Ekuivalensi dari jabatan struktural
            $jabatan = strtolower($gtk->jabatan ?? '');
            if (str_contains($jabatan, 'wakil kepala') || str_contains($jabatan, 'waka') || str_contains($jabatan, 'wakasek')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Wakasek', 'jtm' => 12];
            } elseif (str_contains($jabatan, 'kepala lab')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Ka. Laboratorium', 'jtm' => 12];
            } elseif (str_contains($jabatan, 'kepala perpus')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Ka. Perpustakaan', 'jtm' => 12];
            }

            $jtmTotal = $jtmMengajar + $jtmEkuivalensi;

            return [
                'gtk_id'          => $gtk->id,
                'nama'            => $gtk->nama_lengkap,
                'kode'            => $gtk->kode_gtk ?? '',
                'jabatan'         => $gtk->jabatan ?? '',
                'jtm_mengajar'    => $jtmMengajar,
                'jtm_ekuivalensi' => $jtmEkuivalensi,
                'jtm_total'       => $jtmTotal,
                'tugas_tambahan'  => $tugasTambahan,
                // Status: kurang (<24), normal (24-40), lebih (>40)
                'status'          => $jtmTotal < 24 ? 'kurang' : ($jtmTotal > 40 ? 'lebih' : 'normal'),
            ];
        })->sortByDesc('jtm_total')->values();

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * POST /admin/jadwal-pelajaran/clear-all
     * Hapus semua slot jadwal kelas tertentu dalam semester tertentu.
     */
    public function clearAll(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'kelas_id'           => 'required|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
        ]);

        $deleted = JadwalPelajaran::where('kelas_id', $request->kelas_id)
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} slot jadwal berhasil dihapus.",
        ]);
    }

    public function copyJadwal(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'tahun_asal_id'   => 'required|exists:tahun_pelajaran,id',
            'tahun_tujuan_id' => 'required|exists:tahun_pelajaran,id|different:tahun_asal_id',
        ]);

        $tahunTujuan = TahunPelajaran::findOrFail($request->tahun_tujuan_id);

        $kelasTujuanMap = Kelas::where('tahun_pelajaran_id', $tahunTujuan->id)
            ->get()
            ->keyBy(fn($k) => strtolower(trim($k->nama_kelas)));

        $jadwalAsal = JadwalPelajaran::where('tahun_pelajaran_id', $request->tahun_asal_id)->get();

        $disalin = $dilewati = $no_target = 0;

        DB::transaction(function () use ($jadwalAsal, $kelasTujuanMap, $tahunTujuan, &$disalin, &$dilewati, &$no_target) {
            foreach ($jadwalAsal as $j) {
                $kelasAsal = Kelas::find($j->kelas_id);
                if (!$kelasAsal) { $no_target++; continue; }

                $kelasTujuan = $kelasTujuanMap->get(strtolower(trim($kelasAsal->nama_kelas)));
                if (!$kelasTujuan) { $no_target++; continue; }

                $exists = JadwalPelajaran::where('tahun_pelajaran_id', $tahunTujuan->id)
                    ->where('kelas_id', $kelasTujuan->id)
                    ->where('hari', $j->hari)
                    ->where('jam_ke', $j->jam_ke)
                    ->where('semester', $j->semester)
                    ->exists();

                if ($exists) { $dilewati++; continue; }

                JadwalPelajaran::create([
                    'tahun_pelajaran_id' => $tahunTujuan->id,
                    'kelas_id'           => $kelasTujuan->id,
                    'mapel_id'           => $j->mapel_id,
                    'gtk_id'             => $j->gtk_id,
                    'hari'               => $j->hari,
                    'jam_ke'             => $j->jam_ke,
                    'jam_mulai'          => $j->jam_mulai,
                    'jam_selesai'        => $j->jam_selesai,
                    'ruangan'            => $j->ruangan,
                    'semester'           => $j->semester,
                    'catatan'            => $j->catatan,
                    'is_active'          => true,
                ]);
                $disalin++;
            }
        });

        $parts = [];
        if ($disalin)   $parts[] = "{$disalin} jadwal disalin";
        if ($dilewati)  $parts[] = "{$dilewati} dilewati (sudah ada)";
        if ($no_target) $parts[] = "{$no_target} tidak ada kelas tujuan";

        return response()->json([
            'success'   => true,
            'disalin'   => $disalin,
            'dilewati'  => $dilewati,
            'no_target' => $no_target,
            'message'   => implode(', ', $parts) . '.',
        ]);
    }
}