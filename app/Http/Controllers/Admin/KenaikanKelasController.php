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

        // Jumlah siswa aktif per tingkat
        $siswaPerTingkat = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->where('status', 'aktif')
            ->whereHas('kelas', fn($q) => $q->where('is_active', true))
            ->with('kelas:id,tingkat,nama_kelas')
            ->get()
            ->groupBy(fn($sk) => $sk->kelas->tingkat ?? 0);

        // Status kelulusan kelas 12
        $kelas12Ids = ($kelasByTingkat[12] ?? collect())->pluck('id');
        $siswa12Total = SiswaKelas::where('tahun_pelajaran_id', $tahunId)
            ->whereIn('kelas_id', $kelas12Ids)
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
            ->orderBy('nomor_urut_absen')
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
            ->whereIn('kelas_id', $kelas12Ids)
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
            ->whereIn('kelas_id', $kelas12Ids)
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
            'tandai_siswa_lulus' => 'boolean',
        ]);

        $tahun       = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);
        $tandaiLulus = (bool) $request->input('tandai_siswa_lulus', true);

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
            ->whereIn('kelas_id', $kelas12Ids)
            ->where('status', 'aktif')
            ->get();

        $pengumumanMap = PengumumanKelulusan::where('tahun_pelajaran_id', $tahun->id)
            ->whereIn('siswa_id', $siswaKelasRows->pluck('siswa_id'))
            ->pluck('status', 'siswa_id');

        $diproses_lulus   = 0;
        $diproses_tinggal = 0;
        $belum_diproses   = 0;

        DB::transaction(function () use ($tahun, $siswaKelasRows, $pengumumanMap, $tandaiLulus, &$diproses_lulus, &$diproses_tinggal, &$belum_diproses) {
            foreach ($siswaKelasRows as $sk) {
                $statusPengumuman = $pengumumanMap->get($sk->siswa_id);

                if (!$statusPengumuman) {
                    $belum_diproses++;
                    continue;
                }

                if (in_array($statusPengumuman, ['lulus', 'lulus_bersyarat'])) {
                    $sk->update([
                        'status'              => 'lulus',
                        'tanggal_keluar'      => now()->toDateString(),
                        'catatan_perpindahan' => 'Finalisasi kelulusan tahun ' . $tahun->nama,
                    ]);
                    if ($tandaiLulus && $sk->siswa) {
                        $sk->siswa->update(['status_siswa' => 'lulus']);
                    }
                    $diproses_lulus++;
                } elseif ($statusPengumuman === 'tidak_lulus') {
                    $sk->update([
                        'status'              => 'tinggal_kelas',
                        'catatan_perpindahan' => 'Tidak lulus, tinggal kelas — tahun ' . $tahun->nama,
                    ]);
                    $diproses_tinggal++;
                }
            }
        });

        $total = $diproses_lulus + $diproses_tinggal + $belum_diproses;
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
     * Memindahkan siswa dari kelas lama (tahun lama) ke kelas baru (tahun baru)
     * berdasarkan mapping yang dikirim.
     *
     * Payload: {
     *   tahun_asal_id: uuid,
     *   tahun_tujuan_id: uuid,
     *   mapping: [ { kelas_asal_id: uuid, kelas_tujuan_id: uuid }, ... ],
     *   tanggal_masuk: 'YYYY-MM-DD',
     * }
     */
    public function prosesNaikKelas(Request $request)
    {
        $request->validate([
            'tahun_asal_id'            => 'required|exists:tahun_pelajaran,id',
            'tahun_tujuan_id'          => 'required|exists:tahun_pelajaran,id|different:tahun_asal_id',
            'mapping'                  => 'required|array|min:1',
            'mapping.*.kelas_asal_id'  => 'required|exists:kelas,id',
            'mapping.*.kelas_tujuan_id'=> 'required|exists:kelas,id',
            'tanggal_masuk'            => 'required|date',
        ]);

        $tahunAsal   = TahunPelajaran::findOrFail($request->tahun_asal_id);
        $tahunTujuan = TahunPelajaran::findOrFail($request->tahun_tujuan_id);
        $tanggalMasuk = $request->tanggal_masuk;

        $diproses = 0;
        $dilewati = 0;
        $errors   = [];

        DB::transaction(function () use ($tahunAsal, $tahunTujuan, $request, $tanggalMasuk, &$diproses, &$dilewati, &$errors) {
            foreach ($request->mapping as $map) {
                $kelasAsal   = Kelas::findOrFail($map['kelas_asal_id']);
                $kelasTujuan = Kelas::findOrFail($map['kelas_tujuan_id']);

                // Validasi: tingkat tujuan harus lebih tinggi
                if ($kelasTujuan->tingkat <= $kelasAsal->tingkat) {
                    $errors[] = "Kelas {$kelasTujuan->nama_kelas} (tingkat {$kelasTujuan->tingkat}) harus lebih tinggi dari {$kelasAsal->nama_kelas} (tingkat {$kelasAsal->tingkat}).";
                    continue;
                }

                // Ambil semua siswa aktif di kelas asal
                $siswaKelasRows = SiswaKelas::with('siswa')
                    ->where('kelas_id', $kelasAsal->id)
                    ->where('tahun_pelajaran_id', $tahunAsal->id)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($siswaKelasRows as $sk) {
                    // Cek apakah siswa sudah ada di kelas tujuan
                    $exists = SiswaKelas::where('siswa_id', $sk->siswa_id)
                        ->where('tahun_pelajaran_id', $tahunTujuan->id)
                        ->where('status', 'aktif')
                        ->exists();

                    if ($exists) {
                        $dilewati++;
                        continue;
                    }

                    // Nomor urut absen di kelas tujuan
                    $nomorBaru = SiswaKelas::where('kelas_id', $kelasTujuan->id)
                        ->where('tahun_pelajaran_id', $tahunTujuan->id)
                        ->max('nomor_urut_absen') + 1;

                    // Buat record baru di kelas tujuan
                    SiswaKelas::create([
                        'siswa_id'            => $sk->siswa_id,
                        'kelas_id'            => $kelasTujuan->id,
                        'tahun_pelajaran_id'  => $tahunTujuan->id,
                        'tanggal_masuk'       => $tanggalMasuk,
                        'status'              => 'aktif',
                        'nomor_urut_absen'    => $nomorBaru,
                        'catatan_perpindahan' => "Naik kelas dari {$kelasAsal->nama_kelas} ({$tahunAsal->nama})",
                    ]);

                    // Update record lama → status naik_kelas
                    $sk->update([
                        'status'              => 'naik_kelas',
                        'tanggal_keluar'      => $tanggalMasuk,
                        'catatan_perpindahan' => "Naik ke {$kelasTujuan->nama_kelas} ({$tahunTujuan->nama})",
                    ]);

                    // Update kelas_saat_ini_id di siswa
                    if ($sk->siswa) {
                        $sk->siswa->update(['kelas_saat_ini_id' => $kelasTujuan->id]);
                    }

                    $diproses++;
                }
            }
        });

        return response()->json([
            'success'  => count($errors) === 0,
            'diproses' => $diproses,
            'dilewati' => $dilewati,
            'errors'   => $errors,
            'message'  => "Berhasil memindahkan {$diproses} siswa. {$dilewati} siswa dilewati.",
        ]);
    }

    /**
     * AJAX: ambil daftar kelas di tahun pelajaran tertentu (untuk dropdown mapping).
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
