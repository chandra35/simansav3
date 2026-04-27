<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\VerifikasiIjazah;
use App\Models\VerifikasiIjazahLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiIjazahService
{
    /**
     * Field yang dibandingkan antara Simansa vs EMIS
     * Format: 'key' => label
     */
    public static array $verifikasiFields = [
        'nama_lengkap'    => 'Nama Lengkap',
        'nik'             => 'NIK',
        'nisn'            => 'NISN',
        'tempat_lahir'    => 'Tempat Lahir',
        'tanggal_lahir'   => 'Tanggal Lahir',
        'jenis_kelamin'   => 'Jenis Kelamin',
        'nama_sekolah_asal' => 'Nama Sekolah Asal (SMP/MTs)',
        'npsn_asal'       => 'NPSN Sekolah Asal',
        'nama_ayah'       => 'Nama Ayah',
        'nama_ibu'        => 'Nama Ibu',
    ];

    public function __construct(private readonly EmisNisnService $emisService) {}

    /**
     * Ambil snapshot data Simansa untuk seorang siswa
     */
    public function getDataSimansa(Siswa $siswa): array
    {
        $siswa->load('ortu');

        return [
            'nama_lengkap'    => $siswa->nama_lengkap,
            'nik'             => $siswa->nik,
            'nisn'            => $siswa->nisn,
            'tempat_lahir'    => $siswa->tempat_lahir,
            'tanggal_lahir'   => $siswa->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin'   => $siswa->jenis_kelamin,
            'nama_sekolah_asal' => $siswa->asal_siswa,
            'npsn_asal'       => $siswa->npsn_asal_sekolah,
            'nama_ayah'       => $siswa->ortu?->nama_ayah,
            'nama_ibu'        => $siswa->ortu?->nama_ibu,
        ];
    }

    /**
     * Fetch data EMIS (Kemdikbud + Kemenag) berdasarkan NISN
     * Returns: ['kemdikbud' => [...], 'kemenag' => [...], 'error' => ...]
     */
    public function fetchDataEmis(string $nisn): array
    {
        if (empty($nisn)) {
            return [
                'kemdikbud' => null,
                'kemenag'   => null,
                'error'     => 'NISN kosong, tidak bisa fetch data EMIS.',
            ];
        }

        $result = $this->emisService->cekNisn($nisn);

        if (!$result['success']) {
            return [
                'kemdikbud' => null,
                'kemenag'   => null,
                'error'     => $result['message'] ?? 'Gagal mengambil data EMIS.',
            ];
        }

        $kemdikbud = null;
        $kemenag   = null;

        // Parsing data Kemdikbud (Pusdatin)
        if (!empty($result['kemdikbud'])) {
            $k = $result['kemdikbud'];
            $kemdikbud = [
                'nama_lengkap'    => $k['nama'] ?? $k['nm_siswa'] ?? null,
                'nisn'            => $k['nisn'] ?? null,
                'tempat_lahir'    => $k['tempat_lahir'] ?? $k['tmpt_lahir'] ?? null,
                'tanggal_lahir'   => $k['tanggal_lahir'] ?? $k['tgl_lahir'] ?? null,
                'jenis_kelamin'   => $k['jenis_kelamin'] ?? $k['jk'] ?? null,
                'nama_sekolah_asal' => $k['sekolah'] ?? $k['nama_sekolah'] ?? null,
                'npsn_asal'       => $k['npsn'] ?? null,
                'raw'             => $k,
            ];
        }

        // Parsing data Kemenag (PPDB Search)
        if (!empty($result['kemenag'])) {
            $m = $result['kemenag'];
            $kemenag = [
                'nama_lengkap'    => $m['nama_siswa'] ?? $m['name'] ?? null,
                'nisn'            => $m['nisn'] ?? null,
                'tempat_lahir'    => $m['tempat_lahir'] ?? null,
                'tanggal_lahir'   => $m['tanggal_lahir'] ?? null,
                'jenis_kelamin'   => $m['jenis_kelamin'] ?? null,
                'nama_sekolah_asal' => $m['nama_sekolah_asal'] ?? $m['sekolah_asal'] ?? null,
                'npsn_asal'       => $m['npsn_asal'] ?? null,
                'nama_ayah'       => $m['nama_ayah'] ?? null,
                'nama_ibu'        => $m['nama_ibu'] ?? null,
                'raw'             => $m,
            ];
        }

        return [
            'kemdikbud' => $kemdikbud,
            'kemenag'   => $kemenag,
            'error'     => null,
        ];
    }

    /**
     * Bandingkan data Simansa vs EMIS, return list field yang berbeda
     * Mengutamakan data Kemenag, fallback ke Kemdikbud
     */
    public function compareData(array $simansa, array $kemdikbud = null, array $kemenag = null): array
    {
        $tidakSesuai = [];

        foreach (array_keys(self::$verifikasiFields) as $field) {
            $nilaiSimansa = strtolower(trim($simansa[$field] ?? ''));

            // Pilih sumber EMIS: utamakan Kemenag, fallback Kemdikbud
            $nilaiEmis = null;
            if ($kemenag && isset($kemenag[$field]) && !empty($kemenag[$field])) {
                $nilaiEmis = strtolower(trim($kemenag[$field]));
            } elseif ($kemdikbud && isset($kemdikbud[$field]) && !empty($kemdikbud[$field])) {
                $nilaiEmis = strtolower(trim($kemdikbud[$field]));
            }

            // Jika EMIS tidak punya data untuk field ini, skip
            if ($nilaiEmis === null || $nilaiEmis === '') {
                continue;
            }

            // Normalisasi tanggal lahir sebelum compare
            if ($field === 'tanggal_lahir') {
                $nilaiSimansa = $this->normalizeTanggal($nilaiSimansa);
                $nilaiEmis    = $this->normalizeTanggal($nilaiEmis);
            }

            if ($nilaiSimansa !== $nilaiEmis && !($nilaiSimansa === '' && $nilaiEmis === '')) {
                $tidakSesuai[] = $field;
            }
        }

        return $tidakSesuai;
    }

    /**
     * Buat atau update record verifikasi ijazah
     */
    public function simpanVerifikasi(
        Siswa $siswa,
        User $verifikator,
        string $status,
        array $fieldTidakSesuai,
        array $saranPerbaikan,
        string $catatan,
        array $dataEmis
    ): VerifikasiIjazah {
        $dataSimansa = $this->getDataSimansa($siswa);

        DB::beginTransaction();
        try {
            $existing = VerifikasiIjazah::where('siswa_id', $siswa->id)->first();
            $statusLama = $existing?->status ?? null;

            $verifikasi = VerifikasiIjazah::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'verifikator_id'      => $verifikator->id,
                    'verifikator_nama'    => $verifikator->name,
                    'status'              => $status,
                    'data_simansa'        => $dataSimansa,
                    'data_emis_kemdikbud' => $dataEmis['kemdikbud'],
                    'data_emis_kemenag'   => $dataEmis['kemenag'],
                    'field_tidak_sesuai'  => $fieldTidakSesuai,
                    'saran_perbaikan'     => $saranPerbaikan,
                    'catatan'             => $catatan,
                    'verified_at'         => now(),
                ]
            );

            // Catat log
            $aksi = $existing ? 'status_changed' : 'created';
            if ($existing && $statusLama === $status) {
                $aksi = 'catatan_updated';
            }

            $keterangan = $catatan;
            if ($aksi === 'status_changed') {
                $keterangan = 'Status diubah dari "' . $statusLama . '" menjadi "' . $status . '"';
                if ($catatan) {
                    $keterangan .= '. Catatan: ' . $catatan;
                }
            }

            VerifikasiIjazahLog::create([
                'verifikasi_id' => $verifikasi->id,
                'user_id'       => $verifikator->id,
                'user_nama'     => $verifikator->name,
                'aksi'          => $aksi,
                'status_lama'   => $statusLama,
                'status_baru'   => $status,
                'keterangan'    => $keterangan,
            ]);

            DB::commit();
            return $verifikasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VerifikasiIjazah: gagal simpan', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Refresh data EMIS (tanpa mengubah status/catatan yang sudah ada)
     */
    public function refreshDataEmis(VerifikasiIjazah $verifikasi, User $user): array
    {
        $siswa = $verifikasi->siswa;
        $dataEmis = $this->fetchDataEmis($siswa->nisn);

        if ($dataEmis['error']) {
            return ['success' => false, 'message' => $dataEmis['error']];
        }

        DB::beginTransaction();
        try {
            $verifikasi->update([
                'data_emis_kemdikbud' => $dataEmis['kemdikbud'],
                'data_emis_kemenag'   => $dataEmis['kemenag'],
            ]);

            VerifikasiIjazahLog::create([
                'verifikasi_id' => $verifikasi->id,
                'user_id'       => $user->id,
                'user_nama'     => $user->name,
                'aksi'          => 'data_refreshed',
                'status_lama'   => null,
                'status_baru'   => null,
                'keterangan'    => 'Data EMIS diperbarui dari API.',
            ]);

            DB::commit();
            return ['success' => true, 'data_emis' => $dataEmis];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Normalisasi format tanggal ke Y-m-d
     */
    private function normalizeTanggal(string $tgl): string
    {
        if (empty($tgl)) return '';
        // Coba parse berbagai format
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $tgl);
            if ($d) {
                return $d->format('Y-m-d');
            }
        }
        return $tgl;
    }
}
