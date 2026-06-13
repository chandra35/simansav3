<?php

namespace App\Services;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RdmMatchingService
 *
 * Membandingkan data siswa di RDM (Rapor Digital Madrasah) dengan SIMANSA
 * untuk analisis admin: siapa yang ada di RDM tapi belum ada di SIMANSA,
 * dan sebaliknya.
 *
 * Prinsip: READ-ONLY terhadap RDM — tidak ada perubahan apapun di VM RDM.
 */
class RdmMatchingService
{
    private const RDM_CONNECTION = 'mysql_rdm';

    /** Mapping tingkat_id RDM → tingkat SIMANSA */
    private const TINGKAT_MAP = [
        12 => 10, // Kelas X
        13 => 11, // Kelas XI
        14 => 12, // Kelas XII
    ];

    private const TINGKAT_LABELS = [
        12 => 'Kelas X',
        13 => 'Kelas XI',
        14 => 'Kelas XII',
    ];

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Ambil tahun ajaran aktif dari RDM.
     */
    public function getActiveTahunAjaran(): ?object
    {
        return DB::connection(self::RDM_CONNECTION)
            ->table('e_tahunajaran')
            ->where('tahunajaran_status', 1)
            ->first();
    }

    /**
     * Jalankan matching, kembalikan hasil lengkap.
     *
     * @param  int|null  $tingkatId  12=X, 13=XI, 14=XII; null = semua
     * @return array{
     *   stats: array,
     *   rdm_only: array,
     *   simansa_only: array,
     *   matched: array,
     *   tahun_rdm: string|null,
     *   tingkat_label: string,
     * }
     */
    public function runMatching(?int $tingkatId = null): array
    {
        $activeTahun = $this->getActiveTahunAjaran();

        // 1. Ambil siswa dari RDM (read-only)
        $rdmRows = $this->fetchRdmSiswa($activeTahun?->tahunajaran_id, $tingkatId);

        if ($rdmRows->isEmpty()) {
            return $this->emptyResult($activeTahun, $tingkatId);
        }

        // 2. Dekripsi nama & NISN via endpoint cipher di VM rapor (read-only HTTP call)
        $rdmRows = $this->decryptFields($rdmRows);

        // 3. Ambil siswa SIMANSA
        [$simansaByNisn, $simansaByNis, $simansaAll] = $this->buildSimansaMaps($tingkatId);

        // 4. Bandingkan
        return $this->compare($rdmRows, $simansaByNisn, $simansaByNis, $simansaAll, $activeTahun, $tingkatId);
    }

    // ─── RDM Data Fetching ────────────────────────────────────────────────────

    private function fetchRdmSiswa(?int $tahunAjaranId, ?int $tingkatId): Collection
    {
        $query = DB::connection(self::RDM_CONNECTION)
            ->table('e_siswa as s')
            ->join('e_kelas as k', 'k.kelas_id', '=', 's.kelas_id')
            ->select([
                's.siswa_id',
                's.siswa_nis',
                's.siswa_nisn',   // encrypted
                's.siswa_nama',   // encrypted
                's.tingkat_id',
                's.siswa_gender',
                's.siswa_tgllahir',
                's.sekolah_asal',
                'k.kelas_nama',
            ]);

        // Filter hanya siswa di kelas tahun ajaran aktif
        if ($tahunAjaranId) {
            $query->where('k.tahunajaran_id', $tahunAjaranId);
        }

        if ($tingkatId) {
            $query->where('s.tingkat_id', (string) $tingkatId);
        }

        return $query
            ->orderBy('s.tingkat_id')
            ->orderBy('k.kelas_nama')
            ->orderBy('s.siswa_nama')
            ->get();
    }

    // ─── Decrypt via Cipher Endpoint ──────────────────────────────────────────

    private function decryptFields(Collection $rows): Collection
    {
        $namaEnc = $rows->pluck('siswa_nama')->toArray();
        $nisnEnc = $rows->pluck('siswa_nisn')->toArray();

        // Kirim semua sekaligus dalam satu batch (nama dulu, lalu nisn)
        $allEnc = array_merge($namaEnc, $nisnEnc);
        $allDec = $this->batchDecrypt($allEnc);

        $count    = count($namaEnc);
        $namaPlain = array_slice($allDec, 0, $count);
        $nisnPlain = array_slice($allDec, $count);

        return $rows->values()->map(function ($row, int $i) use ($namaPlain, $nisnPlain) {
            $arr = (array) $row;
            $arr['siswa_nama_plain'] = trim((string) ($namaPlain[$i] ?? $arr['siswa_nama']));
            $arr['siswa_nisn_plain'] = trim((string) ($nisnPlain[$i] ?? $arr['siswa_nisn']));
            return (object) $arr;
        });
    }

    private function batchDecrypt(array $encValues): array
    {
        if (empty($encValues)) {
            return [];
        }

        $result = [];

        // Kirim dalam chunk 200 agar tidak melebihi batas ukuran body HTTP
        foreach (array_chunk($encValues, 200) as $chunk) {
            $decoded = $this->postToCipherEndpoint($chunk);
            $result  = array_merge($result, $decoded);
        }

        return $result;
    }

    private function postToCipherEndpoint(array $chunk): array
    {
        $url   = rtrim(env('RDM_CIPHER_URL', 'https://rapor.man1metro.sch.id/periksasiswa/dec.php'), '/');
        $token = env('RDM_CIPHER_TOKEN', 'mascan_code');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url . '?token=' . $token,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($chunk),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) {
            // Fallback: kembalikan nilai asli (tetap terenkripsi) agar tidak crash
            return $chunk;
        }

        $decoded = json_decode($response, true);

        return (is_array($decoded) && count($decoded) === count($chunk)) ? $decoded : $chunk;
    }

    // ─── SIMANSA Data ─────────────────────────────────────────────────────────

    /**
     * @return array{0: array<string, Siswa>, 1: array<string, Siswa>, 2: Collection}
     */
    private function buildSimansaMaps(?int $rdmTingkatId): array
    {
        $simanaTingkat = $rdmTingkatId ? (self::TINGKAT_MAP[$rdmTingkatId] ?? null) : null;

        $query = Siswa::query()
            ->select(['id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'data_diri_completed', 'data_ortu_completed'])
            ->with([
                'user:id,username',
                'kelasAktif:id,nama_kelas,tingkat',
            ]);

        if ($simanaTingkat) {
            $query->whereHas('kelasAktif', fn ($q) => $q->where('kelas.tingkat', $simanaTingkat));
        }

        $all = $query->get();

        $byNisn = [];
        $byNis  = [];

        foreach ($all as $s) {
            $nisn = trim((string) ($s->nisn ?? ''));
            if ($nisn !== '') {
                $byNisn[$nisn] = $s;
            }

            $nis = trim((string) ($s->user->username ?? ''));
            if ($nis !== '') {
                $byNis[$nis] = $s;
            }
        }

        return [$byNisn, $byNis, $all];
    }

    // ─── Comparison Logic ─────────────────────────────────────────────────────

    private function compare(
        Collection $rdmRows,
        array      $simansaByNisn,
        array      $simansaByNis,
        Collection $simansaAll,
        ?object    $activeTahun,
        ?int       $tingkatId,
    ): array {
        $matched      = [];
        $rdmOnly      = [];
        $matchedIds   = [];

        foreach ($rdmRows as $row) {
            $nisn = trim($row->siswa_nisn_plain ?? '');
            $nis  = trim($row->siswa_nis ?? '');

            $simansaSiswa = null;
            $matchBy      = null;

            // Priority 1: NISN
            if ($nisn !== '' && isset($simansaByNisn[$nisn])) {
                $simansaSiswa = $simansaByNisn[$nisn];
                $matchBy = 'nisn';
            }
            // Priority 2: NIS (username)
            elseif ($nis !== '' && isset($simansaByNis[$nis])) {
                $simansaSiswa = $simansaByNis[$nis];
                $matchBy = 'nis';
            }

            if ($simansaSiswa) {
                $matchedIds[] = $simansaSiswa->id;
                $kelasAktif   = $simansaSiswa->kelasAktif?->first();

                $matched[] = [
                    'rdm_nis'              => $nis,
                    'rdm_nisn'             => $nisn,
                    'rdm_nama'             => $row->siswa_nama_plain,
                    'rdm_tingkat'          => $this->tingkatLabel($row->tingkat_id),
                    'rdm_kelas'            => $row->kelas_nama,
                    'simansa_id'           => $simansaSiswa->id,
                    'simansa_nisn'         => $simansaSiswa->nisn,
                    'simansa_nama'         => $simansaSiswa->nama_lengkap,
                    'simansa_kelas'        => $kelasAktif?->nama_kelas,
                    'simansa_data_lengkap' => ($simansaSiswa->data_diri_completed && $simansaSiswa->data_ortu_completed),
                    'match_by'             => $matchBy,
                ];
            } else {
                $rdmOnly[] = [
                    'rdm_siswa_id' => $row->siswa_id,
                    'rdm_nis'      => $nis,
                    'rdm_nisn'     => $nisn,
                    'rdm_nama'     => $row->siswa_nama_plain,
                    'rdm_tingkat'  => $this->tingkatLabel($row->tingkat_id),
                    'rdm_kelas'    => $row->kelas_nama,
                    'rdm_gender'   => $row->siswa_gender,
                    'rdm_tgllahir' => $row->siswa_tgllahir,
                    'sekolah_asal' => $row->sekolah_asal,
                ];
            }
        }

        // SIMANSA-only: ada di SIMANSA, tidak ditemukan di RDM
        $simansaOnly = $simansaAll
            ->filter(fn ($s) => !in_array($s->id, $matchedIds, true))
            ->map(function ($s) {
                $kelas = $s->kelasAktif?->first();
                return [
                    'simansa_id'           => $s->id,
                    'simansa_nisn'         => $s->nisn,
                    'simansa_nama'         => $s->nama_lengkap,
                    'simansa_kelas'        => $kelas?->nama_kelas,
                    'simansa_tingkat'      => $kelas ? 'Kelas ' . $kelas->tingkat : '-',
                    'simansa_data_lengkap' => ($s->data_diri_completed && $s->data_ortu_completed),
                ];
            })
            ->values()
            ->toArray();

        return [
            'tahun_rdm'     => $activeTahun?->tahunajaran_nama,
            'tingkat_label' => $tingkatId ? (self::TINGKAT_LABELS[$tingkatId] ?? 'Semua Tingkat') : 'Semua Tingkat',
            'matched'       => $matched,
            'rdm_only'      => $rdmOnly,
            'simansa_only'  => $simansaOnly,
            'stats'         => [
                'total_rdm'       => count($rdmRows),
                'total_simansa'   => $simansaAll->count(),
                'total_matched'   => count($matched),
                'total_rdm_only'  => count($rdmOnly),
                'total_simansa_only' => count($simansaOnly),
            ],
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function tingkatLabel(mixed $tingkatId): string
    {
        return self::TINGKAT_LABELS[(int) $tingkatId] ?? ('Tingkat ' . $tingkatId);
    }

    private function emptyResult(?object $activeTahun, ?int $tingkatId): array
    {
        return [
            'tahun_rdm'     => $activeTahun?->tahunajaran_nama,
            'tingkat_label' => $tingkatId ? (self::TINGKAT_LABELS[$tingkatId] ?? 'Semua') : 'Semua Tingkat',
            'matched'       => [],
            'rdm_only'      => [],
            'simansa_only'  => [],
            'stats'         => [
                'total_rdm'          => 0,
                'total_simansa'      => 0,
                'total_matched'      => 0,
                'total_rdm_only'     => 0,
                'total_simansa_only' => 0,
            ],
        ];
    }
}
