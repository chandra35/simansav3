<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PengumumanKelulusan;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KenaikanKelasController extends Controller
{
    /**
     * Halaman utama wizard proses akhir tahun.
     */
    public function index()
    {
        $tahunAktif = TahunPelajaran::with('kurikulum')->where('is_active', true)->first();
        $semuaTahun = TahunPelajaran::orderByDesc('tahun_mulai')->get();

        return view('admin.kenaikan-kelas.index', compact('tahunAktif', 'semuaTahun'));
    }

    /**
     * AJAX: ringkasan data tahun aktif untuk ditampilkan di wizard.
     */
    public function getData(Request $request)
    {
        $tahunId = $request->string('tahun_pelajaran_id')->toString()
            ?: optional(TahunPelajaran::where('is_active', true)->first())->id;

        if (!$tahunId) {
            return response()->json(['error' => 'Tidak ada tahun pelajaran aktif.'], 422);
        }

        // Kelas per tingkat
        $kelasByTingkat = Kelas::where('tahun_pelajaran_id', $tahunId)
            ->where('is_active', true)
            ->get()
            ->groupBy('tingkat');

        // Jumlah siswa aktif per tingkat, termasuk yang aktif tanpa rombel.
        $siswaPerTingkat = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->where('status', 'aktif')
            ->with('kelas:id,tingkat,nama_kelas')
            ->get()
            ->groupBy(fn($sk) => $sk->tingkat ?? $sk->kelas->tingkat ?? 0);

        // Status kelulusan kelas 12
        $kelas12Ids = ($kelasByTingkat[12] ?? collect())->pluck('id');
        $siswa12Total = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->where(function ($query) use ($kelas12Ids) {
                $query->where('tingkat', 12)
                    ->orWhereIn('kelas_id', $kelas12Ids);
            })
            ->where('status', 'aktif')
            ->count();
        $siswa12Lulus = PengumumanKelulusan::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('kelas_id', $kelas12Ids)
            ->count();

        return response()->json([
            'tahun_id'         => $tahunId,
            'kelas_10'         => ($kelasByTingkat[10] ?? collect())->values(),
            'kelas_11'         => ($kelasByTingkat[11] ?? collect())->values(),
            'kelas_12'         => ($kelasByTingkat[12] ?? collect())->values(),
            'siswa_10'         => ($siswaPerTingkat[10] ?? collect())->count(),
            'siswa_11'         => ($siswaPerTingkat[11] ?? collect())->count(),
            'siswa_12'         => ($siswaPerTingkat[12] ?? collect())->count(),
            'siswa_12_lulus'   => $siswa12Lulus,
            'siswa_12_belum'   => max($siswa12Total - $siswa12Lulus, 0),
        ]);
    }

    /**
     * AJAX: preview daftar siswa kelas tertentu sebelum diproses.
     */
    public function previewSiswaKelas(Request $request)
    {
        $request->validate([
            'kelas_id'           => 'required|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        $rows = SiswaKelas::with(['siswa:id,nisn,nama_lengkap,status_siswa'])
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('status', 'aktif')
            ->orderBy(Siswa::query()
                ->select('nama_lengkap')
                ->whereColumn('siswa.id', 'siswa_kelas.siswa_id'))
            ->get()
            ->map(fn($sk) => [
                'siswa_kelas_id' => $sk->id,
                'siswa_id'       => $sk->siswa_id,
                'nisn'           => optional($sk->siswa)->nisn,
                'nama'           => optional($sk->siswa)->nama_lengkap,
                'nomor_absen'    => $sk->nomor_urut_absen,
            ]);

        return response()->json($rows);
    }

    /**
     * AJAX GET: cek status pengumuman kelulusan kelas XII di tahun tertentu.
     * Digunakan wizard untuk menampilkan progress sebelum finalisasi.
     */
    public function statusKelulusan(Request $request)
    {
        $tahunId = $request->string('tahun_pelajaran_id')->toString()
            ?: optional(TahunPelajaran::where('is_active', true)->first())->id;

        if (!$tahunId) {
            return response()->json(['error' => 'Tidak ada tahun pelajaran aktif.'], 422);
        }

        $kelas12Ids = Kelas::where('tahun_pelajaran_id', $tahunId)
            ->where('tingkat', 12)
            ->where('is_active', true)
            ->pluck('id');

        $siswaKelasRows = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->where(function ($query) use ($kelas12Ids) {
                $query->where('tingkat', 12)
                    ->orWhereIn('kelas_id', $kelas12Ids);
            })
            ->where('status', 'aktif')
            ->pluck('siswa_id');

        $total = $siswaKelasRows->count();

        $pengumumanMap = PengumumanKelulusan::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('siswa_id', $siswaKelasRows)
            ->pluck('status', 'siswa_id');

        $sudah_lulus          = $pengumumanMap->filter(fn($s) => in_array($s, ['lulus', 'lulus_bersyarat']))->count();
        $sudah_tidak_lulus    = $pengumumanMap->filter(fn($s) => $s === 'tidak_lulus')->count();
        $belum_ada_pengumuman = $total - $pengumumanMap->count();

        // Cek berapa yang siswa_kelas.status sudah di-finalisasi
        $sudah_finalisasi = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->where(function ($query) use ($kelas12Ids) {
                $query->where('tingkat', 12)
                    ->orWhereIn('kelas_id', $kelas12Ids);
            })
            ->whereIn('status', ['lulus', 'tinggal_kelas'])
            ->count();

        return response()->json([
            'total'                => $total,
            'sudah_lulus'          => $sudah_lulus,
            'sudah_tidak_lulus'    => $sudah_tidak_lulus,
            'belum_ada_pengumuman' => $belum_ada_pengumuman,
            'sudah_finalisasi'     => $sudah_finalisasi,
        ]);
    }

    /**
     * POST: finalisasi kelulusan batch — baca PengumumanKelulusan yang sudah ada,
     * update siswa_kelas.status berdasarkan hasil tersebut.
     * - lulus / lulus_bersyarat → siswa_kelas.status = 'lulus'
     * - tidak_lulus             → siswa_kelas.status = 'tinggal_kelas'
     * Siswa tanpa record pengumuman kelulusan DILEWATI (harus di-set dulu via halaman Pengumuman Kelulusan).
     */
    public function prosesKelulusan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        $tahun       = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);

        $kelas12Ids = Kelas::where('tahun_pelajaran_id', $tahun->id)
            ->where('tingkat', 12)
            ->where('is_active', true)
            ->pluck('id');

        if ($kelas12Ids->isEmpty()) {
            return response()->json(['error' => 'Tidak ada kelas XII aktif di tahun ini.'], 422);
        }

        // Ambil semua siswa aktif kelas XII + PengumumanKelulusan mereka
        $siswaKelasRows = SiswaKelas::with('siswa')
            ->where('tahun_pelajaran_id', $tahun->id)
            ->where(function ($query) use ($kelas12Ids) {
                $query->where('tingkat', 12)
                    ->orWhereIn('kelas_id', $kelas12Ids);
            })
            ->where('status', 'aktif')
            ->get();

        $pengumumanMap = PengumumanKelulusan::where('tahun_pelajaran_id', $tahun->id)
            ->whereIn('siswa_id', $siswaKelasRows->pluck('siswa_id'))
            ->pluck('status', 'siswa_id');

        $lulusRows = $siswaKelasRows->filter(function ($sk) use ($pengumumanMap) {
            return in_array($pengumumanMap->get($sk->siswa_id), ['lulus', 'lulus_bersyarat'], true);
        });
        $tinggalRows = $siswaKelasRows->filter(function ($sk) use ($pengumumanMap) {
            return $pengumumanMap->get($sk->siswa_id) === 'tidak_lulus';
        });

        $diproses_lulus   = $lulusRows->count();
        $diproses_tinggal = $tinggalRows->count();
        $belum_diproses   = $siswaKelasRows->count() - $pengumumanMap->count();
        $tanggalKeluar    = now()->toDateString();

        // Batch update menjaga transaksi singkat dan menghindari lock wait timeout saat data kelas XII banyak.
        DB::transaction(function () use ($tahun, $lulusRows, $tinggalRows, $tanggalKeluar) {
            if ($lulusRows->isNotEmpty()) {
                SiswaKelas::whereIn('id', $lulusRows->pluck('id'))->update([
                    'status'              => 'lulus',
                    'tanggal_keluar'      => $tanggalKeluar,
                    'catatan_perpindahan' => 'Finalisasi kelulusan tahun ' . $tahun->nama,
                    'updated_at'          => now(),
                ]);

                Siswa::whereIn('id', $lulusRows->pluck('siswa_id'))->update([
                    'status_siswa'      => 'lulus',
                    'kelas_saat_ini_id' => null,
                    'updated_at'        => now(),
                ]);
            }

            if ($tinggalRows->isNotEmpty()) {
                SiswaKelas::whereIn('id', $tinggalRows->pluck('id'))->update([
                    'status'              => 'tinggal_kelas',
                    'catatan_perpindahan' => 'Tidak lulus, tinggal kelas - tahun ' . $tahun->nama,
                    'updated_at'          => now(),
                ]);
            }
        }, 3);

        $parts = [];
        if ($diproses_lulus > 0)   $parts[] = "{$diproses_lulus} siswa lulus";
        if ($diproses_tinggal > 0) $parts[] = "{$diproses_tinggal} siswa tinggal kelas";
        if ($belum_diproses > 0)   $parts[] = "{$belum_diproses} siswa belum ada pengumuman kelulusan (dilewati)";

        return response()->json([
            'success'          => true,
            'diproses_lulus'   => $diproses_lulus,
            'diproses_tinggal' => $diproses_tinggal,
            'belum_diproses'   => $belum_diproses,
            'message'          => 'Finalisasi selesai: ' . implode(', ', $parts) . '.',
        ]);

    }

    /**
     * POST: proses naik kelas batch.
     * Menandai siswa kelas X dan XI sebagai naik tingkat.
     * Penempatan rombel tahun tujuan dilakukan terpisah oleh admin.
     *
     * Payload: {
     *   tahun_asal_id: uuid,
     *   tahun_tujuan_id: uuid,
     *   tanggal_masuk: 'YYYY-MM-DD',
     * }
     */
    public function prosesNaikKelas(Request $request)
    {
        $request->validate([
            'tahun_asal_id'   => 'required|exists:tahun_pelajaran,id',
            'tahun_tujuan_id' => 'required|exists:tahun_pelajaran,id|different:tahun_asal_id',
            'tanggal_masuk'   => 'required|date',
        ]);

        $tahunAsal   = TahunPelajaran::findOrFail($request->tahun_asal_id);
        $tahunTujuan = TahunPelajaran::findOrFail($request->tahun_tujuan_id);
        $tanggalMasuk = $request->tanggal_masuk;

        $diproses = 0;
        $kelasDiproses = 0;
        $sudahDitempatkan = 0;

        DB::transaction(function () use ($tahunAsal, $tahunTujuan, $tanggalMasuk, &$diproses, &$kelasDiproses, &$sudahDitempatkan) {
            $kelasAsalIds = Kelas::where('tahun_pelajaran_id', $tahunAsal->id)
                ->whereIn('tingkat', [10, 11])
                ->where('is_active', true)
                ->pluck('id');

            $kelasDiproses = $kelasAsalIds->count();

            $rows = SiswaKelas::with('kelas:id,tingkat,nama_kelas')
                ->where('tahun_pelajaran_id', $tahunAsal->id)
                ->where('status', 'aktif')
                ->where(function ($query) use ($kelasAsalIds) {
                    $query->whereIn('tingkat', [10, 11])
                        ->orWhereIn('kelas_id', $kelasAsalIds);
                })
                ->get(['id', 'siswa_id', 'kelas_id', 'tingkat']);

            if ($rows->isEmpty()) {
                return;
            }

            $rowIds = $rows->pluck('id');
            $siswaIds = $rows->pluck('siswa_id')->unique()->values();
            $siswaAktifTahunTujuan = SiswaKelas::whereIn('siswa_id', $siswaIds)
                ->where('tahun_pelajaran_id', $tahunTujuan->id)
                ->where('status', 'aktif')
                ->pluck('siswa_id')
                ->unique()
                ->values();

            $sudahDitempatkan = $siswaAktifTahunTujuan->count();
            $belumDitempatkanRows = $rows
                ->whereNotIn('siswa_id', $siswaAktifTahunTujuan)
                ->values();

            foreach ($rowIds->chunk(500) as $chunk) {
                SiswaKelas::whereIn('id', $chunk)->update([
                    'status'              => 'naik_kelas',
                    'tanggal_keluar'      => $tanggalMasuk,
                    'catatan_perpindahan' => "Naik tingkat dari {$tahunAsal->nama} ke {$tahunTujuan->nama}. Histori kelas lama ditutup.",
                    'updated_at'          => now(),
                ]);
            }

            foreach ($belumDitempatkanRows as $row) {
                $tingkatAsal = $row->tingkat ?? $row->kelas?->tingkat;
                if (!in_array((int) $tingkatAsal, [10, 11], true)) {
                    continue;
                }

                SiswaKelas::create([
                    'siswa_id'            => $row->siswa_id,
                    'kelas_id'            => null,
                    'tahun_pelajaran_id'  => $tahunTujuan->id,
                    'tingkat'             => (int) $tingkatAsal + 1,
                    'tanggal_masuk'       => $tanggalMasuk,
                    'status'              => 'aktif',
                    'nomor_urut_absen'    => null,
                    'catatan_perpindahan' => "Naik tingkat dari {$tahunAsal->nama}. Menunggu penempatan rombel baru.",
                ]);
            }

            foreach ($belumDitempatkanRows->pluck('siswa_id')->chunk(500) as $chunk) {
                Siswa::whereIn('id', $chunk)->update([
                    'kelas_saat_ini_id' => null,
                    'updated_at'        => now(),
                ]);
            }

            $diproses = $rows->count();

        }, 3);

        return response()->json([
            'success'           => true,
            'diproses'          => $diproses,
            'kelas_diproses'    => $kelasDiproses,
            'sudah_ditempatkan' => $sudahDitempatkan,
            'message'           => "Naik kelas selesai: {$diproses} histori lama ditutup. {$sudahDitempatkan} siswa sudah punya rombel aktif di tahun tujuan, sisanya aktif tanpa rombel.",
        ]);
    }

    /**
     * AJAX: ambil daftar kelas di tahun pelajaran tertentu.
     */
    public function getKelasByTahun(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'tingkat'            => 'nullable|integer|in:10,11,12',
        ]);

        $query = Kelas::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas');

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        $kelas = $query->get(['id', 'nama_kelas', 'tingkat', 'kode_kelas']);

        return response()->json($kelas);
    }
}
