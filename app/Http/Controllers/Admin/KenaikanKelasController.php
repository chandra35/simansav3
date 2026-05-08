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
     * POST: proses kelulusan batch — semua siswa aktif kelas 12
     * di tahun pelajaran aktif yang belum punya record PengumumanKelulusan.
     */
    public function prosesKelulusan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_ids'          => 'required|array|min:1',
            'kelas_ids.*'        => 'exists:kelas,id',
            'status_default'     => 'required|in:lulus,lulus_bersyarat,tidak_lulus',
            'catatan'            => 'nullable|string|max:500',
            'tandai_siswa_lulus' => 'boolean',
        ]);

        $tahun = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);
        $tandaiLulus = (bool) $request->input('tandai_siswa_lulus', true);
        $statusDefault = $request->status_default;
        $catatan = $request->catatan;

        // Validasi: kelas harus bertingkat 12
        $kelas = Kelas::whereIn('id', $request->kelas_ids)
            ->where('tingkat', 12)
            ->where('tahun_pelajaran_id', $tahun->id)
            ->get();

        if ($kelas->isEmpty()) {
            return response()->json(['error' => 'Tidak ada kelas 12 valid yang dipilih.'], 422);
        }

        $diproses = 0;
        $dilewati = 0;

        DB::transaction(function () use ($tahun, $kelas, $statusDefault, $catatan, $tandaiLulus, &$diproses, &$dilewati) {
            foreach ($kelas as $k) {
                $siswaKelasRows = SiswaKelas::with('siswa')
                    ->where('kelas_id', $k->id)
                    ->where('tahun_pelajaran_id', $tahun->id)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($siswaKelasRows as $sk) {
                    // Cek apakah sudah ada record pengumuman kelulusan
                    $existing = PengumumanKelulusan::where('tahun_pelajaran_id', $tahun->id)
                        ->where('siswa_id', $sk->siswa_id)
                        ->first();

                    if ($existing) {
                        $dilewati++;
                        continue;
                    }

                    // Buat record PengumumanKelulusan
                    PengumumanKelulusan::create([
                        'tahun_pelajaran_id' => $tahun->id,
                        'siswa_id'           => $sk->siswa_id,
                        'kelas_id'           => $k->id,
                        'status'             => $statusDefault,
                        'catatan'            => $catatan,
                    ]);

                    // Update status di siswa_kelas → lulus
                    $sk->update([
                        'status'              => 'lulus',
                        'tanggal_keluar'      => now()->toDateString(),
                        'catatan_perpindahan' => 'Proses kelulusan tahun ' . $tahun->nama,
                    ]);

                    // Update status_siswa di tabel siswa (opsional)
                    if ($tandaiLulus && $sk->siswa) {
                        $sk->siswa->update(['status_siswa' => 'lulus']);
                    }

                    $diproses++;
                }
            }
        });

        return response()->json([
            'success'  => true,
            'diproses' => $diproses,
            'dilewati' => $dilewati,
            'message'  => "Berhasil memproses {$diproses} siswa. {$dilewati} siswa dilewati (sudah ada data kelulusan).",
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
