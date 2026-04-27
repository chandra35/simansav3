<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\VerifikasiIjazah;
use App\Models\VerifikasiIjazahLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        'nama_ayah'       => 'Nama Ayah',
        'nama_ibu'        => 'Nama Ibu',
    ];

    public function __construct(private readonly EmisNisnService $emisService) {}

    /**
     * Ambil snapshot data Simansa untuk seorang siswa
     */
    public function getDataSimansa(Siswa $siswa): array
    {
        $siswa->load('ortu', 'sekolahAsal');

        return [
            'nama_lengkap'    => $siswa->nama_lengkap,
            'nik'             => $siswa->nik,
            'nisn'            => $siswa->nisn,
            'tempat_lahir'    => $siswa->tempat_lahir,
            'tanggal_lahir'   => $siswa->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin'   => $siswa->jenis_kelamin,
            'nama_sekolah_asal' => $siswa->sekolahAsal?->nama,
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

        // cekNisn() returns data nested under $result['data']
        $emisData = $result['data'] ?? $result;

        // Parsing data Kemdikbud (Pusdatin)
        if (!empty($emisData['kemdikbud'])) {
            $k = $emisData['kemdikbud'];
            $kemdikbud = [
                'nama_lengkap'    => $k['nama'] ?? $k['nm_siswa'] ?? null,
                'nisn'            => $k['nisn'] ?? null,
                'tempat_lahir'    => $k['tempat_lahir'] ?? $k['tmpt_lahir'] ?? null,
                'tanggal_lahir'   => $k['tanggal_lahir'] ?? $k['tgl_lahir'] ?? null,
                'jenis_kelamin'   => $k['jenis_kelamin'] ?? $k['jk'] ?? null,
                'nama_sekolah_asal' => $k['nama_sekolah_asal'] ?? $k['sekolah_asal'] ?? null,
                'raw'             => $k,
            ];
        }

        // Parsing data Kemenag (PPDB Search)
        if (!empty($emisData['kemenag'])) {
            $m = $emisData['kemenag'];
            $kemenag = [
                'nama_lengkap'    => $m['nama_siswa'] ?? $m['name'] ?? null,
                'nisn'            => $m['nisn'] ?? null,
                'tempat_lahir'    => $m['tempat_lahir'] ?? null,
                'tanggal_lahir'   => $m['tanggal_lahir'] ?? null,
                'jenis_kelamin'   => $m['jenis_kelamin'] ?? null,
                'nama_sekolah_asal' => $m['nama_sekolah_asal'] ?? $m['sekolah_asal'] ?? null,
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
     * Ambil token EMIS lembaga dari DB (emis_institusi_token)
     */
    protected function getEmisInstitusiToken(): ?object
    {
        return DB::table('api_tokens')->where('name', 'emis_institusi_token')->first();
    }

    /**
     * Fetch data siswa dari EMIS endpoint lembaga berdasarkan NISN.
     * Membutuhkan emis_institusi_token (token akun operator lembaga).
     * Returns: array data siswa atau null jika tidak ditemukan / token tidak ada.
     */
    public function fetchDataEmisLembaga(string $nisn): ?array
    {
        $tokenData = $this->getEmisInstitusiToken();

        if (!$tokenData || empty($tokenData->token)) {
            return null; // Token belum di-set — fitur ini opsional
        }

        // Decode JWT untuk ambil institution_id
        $parts = explode('.', $tokenData->token);
        if (count($parts) !== 3) {
            Log::warning('VerifikasiIjazah: emis_institusi_token bukan JWT valid');
            return null;
        }

        $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
        $institutionId = $payload['institution_id'] ?? $payload['institutionId'] ?? $payload['lembaga_id'] ?? null;

        if (!$institutionId) {
            Log::warning('VerifikasiIjazah: institution_id tidak ditemukan di JWT emis_institusi_token', ['payload_keys' => array_keys($payload ?? [])]);
            return null;
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenData->token,
                ])
                ->get('https://api-emis.kemenag.go.id/v1/students/institution/' . $institutionId . '/student/search', [
                    'q' => $nisn,
                    'student_status_id' => 1, // aktif
                    'page' => 1,
                    'per_page' => 5,
                ]);

            if (!$response->successful()) {
                Log::warning('VerifikasiIjazah: EMIS lembaga API gagal', ['status' => $response->status()]);
                return null;
            }

            $body = $response->json();
            $results = $body['data']['results'] ?? $body['results'] ?? [];

            if (empty($results)) {
                return null;
            }

            // Cari yang NISN-nya cocok
            foreach ($results as $s) {
                $nisnEmis = $s['nisn'] ?? $s['no_nisn'] ?? null;
                if ($nisnEmis && trim($nisnEmis) === trim($nisn)) {
                    return $this->normalizeEmisLembagaData($s);
                }
            }

            // Jika tidak ada yang persis cocok, return first result
            return $this->normalizeEmisLembagaData($results[0]);

        } catch (\Exception $e) {
            Log::error('VerifikasiIjazah: fetchDataEmisLembaga error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Normalisasi data siswa dari endpoint EMIS lembaga ke format standar
     */
    protected function normalizeEmisLembagaData(array $s): array
    {
        return [
            'nama_lengkap'  => $s['nama_siswa'] ?? $s['name'] ?? $s['nama'] ?? null,
            'nisn'          => $s['nisn'] ?? $s['no_nisn'] ?? null,
            'nik'           => $s['nik'] ?? $s['no_ktp'] ?? null,
            'tempat_lahir'  => $s['tempat_lahir'] ?? null,
            'tanggal_lahir' => $s['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $s['jenis_kelamin'] ?? $s['gender'] ?? null,
            'nama_ayah'     => $s['nama_ayah'] ?? null,
            'nama_ibu'      => $s['nama_ibu'] ?? null,
            'raw'           => $s,
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
        array $dataEmis,
        ?array $dataEmisLembaga = null
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
                    'data_emis_lembaga'   => $dataEmisLembaga,
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
            $updateData = [
                'data_emis_kemdikbud' => $dataEmis['kemdikbud'],
                'data_emis_kemenag'   => $dataEmis['kemenag'],
            ];

            // Refresh lembaga juga jika token tersedia
            $lembagaData = $this->fetchDataEmisLembaga($siswa->nisn);
            if ($lembagaData !== null) {
                $updateData['data_emis_lembaga'] = $lembagaData;
            }

            $verifikasi->update($updateData);

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
