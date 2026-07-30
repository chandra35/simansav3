<?php

namespace App\Services;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        // 2. Ambil siswa SIMANSA (no HTTP calls)
        [$simansaByNisn, $simansaByNis, $simansaAll] = $this->buildSimansaMaps($tingkatId);

        // 3. Pre-match by NIS (plaintext — zero HTTP calls needed).
        //    Pisahkan: NIS-matched vs belum cocok (perlu decrypt penuh).
        $nisMatched   = [];   // rows yang sudah pasti cocok via NIS
        $needsDecrypt = [];   // rows yang belum cocok, perlu decrypt nama+nisn

        foreach ($rdmRows as $row) {
            $nis = trim((string) ($row->siswa_nis ?? ''));
            if ($nis !== '' && isset($simansaByNis[$nis])) {
                $nisMatched[] = $row;
            } else {
                $needsDecrypt[] = $row;
            }
        }

        // 4. Decrypt:
        //    - Untuk yg belum cocok: decrypt nama + nisn (untuk NISN match + fuzzy)
        //    - Untuk yg sudah NIS-matched: decrypt nama saja (untuk tampilan)
        //    Ini jauh mengurangi jumlah HTTP call vs decrypt semua di awal.
        $decryptedUnmatched = $this->decryptFields(collect($needsDecrypt));
        $decryptedMatched   = $this->decryptNamesOnly(collect($nisMatched));

        // Gabung: NIS-matched di depan agar compare() memprosesnya lebih dulu
        $rdmRows = $decryptedMatched->concat($decryptedUnmatched);

        // 5. Bandingkan
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

    /** Decrypt nama + nisn (untuk siswa yang belum cocok via NIS). */
    private function decryptFields(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $namaEnc = $rows->pluck('siswa_nama')->toArray();
        $nisnEnc = $rows->pluck('siswa_nisn')->toArray();

        $allEnc    = array_merge($namaEnc, $nisnEnc);
        $allDec    = $this->batchDecrypt($allEnc);
        $count     = count($namaEnc);
        $namaPlain = array_slice($allDec, 0, $count);
        $nisnPlain = array_slice($allDec, $count);

        return $rows->values()->map(function ($row, int $i) use ($namaPlain, $nisnPlain) {
            $arr = (array) $row;
            $arr['siswa_nama_plain'] = trim((string) ($namaPlain[$i] ?? $arr['siswa_nama']));
            $arr['siswa_nisn_plain'] = trim((string) ($nisnPlain[$i] ?? $arr['siswa_nisn']));
            return (object) $arr;
        });
    }

    /**
     * Decrypt hanya nama (untuk siswa yang sudah cocok via NIS — tidak perlu NISN).
     * Menghemat separuh HTTP call untuk siswa yang pasti sudah cocok.
     */
    private function decryptNamesOnly(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $namaEnc   = $rows->pluck('siswa_nama')->toArray();
        $namaPlain = $this->batchDecrypt($namaEnc);

        return $rows->values()->map(function ($row, int $i) use ($namaPlain) {
            $arr = (array) $row;
            $arr['siswa_nama_plain'] = trim((string) ($namaPlain[$i] ?? $arr['siswa_nama']));
            $arr['siswa_nisn_plain'] = '';   // tidak perlu — sudah cocok via NIS
            return (object) $arr;
        });
    }

    private function batchDecrypt(array $encValues): array
    {
        if (empty($encValues)) {
            return [];
        }

        // Step 1: Pisahkan nilai yang sudah ada di cache vs yang perlu dikirim
        $cached     = [];  // index => decrypted value
        $toDecrypt  = [];  // index => encrypted value (hanya yang belum di-cache)

        foreach ($encValues as $i => $val) {
            $hit = $this->fileCache($val);
            if ($hit !== null) {
                $cached[$i] = $hit;
            } else {
                $toDecrypt[$i] = $val;
            }
        }

        if (empty($toDecrypt)) {
            // Semua sudah di-cache, tidak perlu HTTP call
            ksort($cached);
            return array_values($cached);
        }

        // Step 2: Bagi menjadi chunk 25, dikirim 5 concurrent per batch
        $chunks      = [];   // chunkIdx => [origIndex => encValue]
        $chunkKeys   = [];   // chunkIdx => [origIndex, ...]

        foreach (array_chunk($toDecrypt, 25, true) as $chunk) {
            $chunks[]    = array_values($chunk);
            $chunkKeys[] = array_keys($chunk);
        }

        // Step 2b: Kirim dalam batch 5 concurrent (hindari overwhelming cipher endpoint)
        $allDecoded = [];
        foreach (array_chunk($chunks, 5, true) as $batchChunks) {
            $batchResult = $this->parallelPostToCipherEndpoint($batchChunks);
            foreach ($batchResult as $origChunkIdx => $res) {
                $allDecoded[$origChunkIdx] = $res;
            }
        }

        // Step 3: Gabungkan hasil decrypt ke indeks aslinya
        // Hanya cache jika decrypt berhasil (hasil berbeda dari input atau hasil valid string)
        foreach ($allDecoded as $chunkIdx => $results) {
            if ($results === null) {
                // Chunk gagal total — gunakan encrypted value tapi jangan cache
                foreach ($chunkKeys[$chunkIdx] as $j => $origIdx) {
                    $cached[$origIdx] = $chunks[$chunkIdx][$j];
                }
                continue;
            }
            foreach ($results as $j => $decVal) {
                $origIdx  = $chunkKeys[$chunkIdx][$j];
                $encVal   = (string) $chunks[$chunkIdx][$j];
                $decStr   = (string) $decVal;
                $cached[$origIdx] = $decStr;
                // Cache hanya jika decrypt mengubah nilai (hindari cache salah saat endpoint error)
                if ($decStr !== $encVal && $decStr !== '') {
                    $this->fileCachePut($encVal, $decStr);
                }
            }
        }

        ksort($cached);
        return array_values($cached);
    }

    /**
     * API bersama untuk modul integrasi lain. Nilai tetap dibaca dari cache
     * lokal dan didekripsi secara batch; koneksi RDM tidak pernah ditulis.
     */
    public function decryptValues(array $encryptedValues): array
    {
        return $this->batchDecrypt($encryptedValues);
    }

    /**
     * Kirim beberapa chunk ke cipher endpoint secara paralel menggunakan curl_multi.
     * Jauh lebih cepat daripada sequential — semua chunk selesai dalam waktu satu timeout.
     *
     * @param  array[]  $chunks  Array of arrays, each being one batch of encrypted values
     * @return array[]           Array of arrays (decoded results per chunk), indexed same as $chunks
     */
    private function parallelPostToCipherEndpoint(array $chunks): array
    {
        $url   = rtrim(env('RDM_CIPHER_URL', 'https://rapor.man1metro.sch.id/periksasiswa/dec.php'), '/');
        $token = env('RDM_CIPHER_TOKEN', 'mascan_code');
        $endpoint = $url . '?token=' . $token;

        $mh      = curl_multi_init();
        $handles = [];

        foreach ($chunks as $i => $chunk) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $endpoint,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($chunk),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 30,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }

        // Execute all handles in parallel
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running > 0 && $status === CURLM_OK);

        // Collect results
        $results = [];
        foreach ($handles as $i => $ch) {
            $response = curl_multi_getcontent($ch);
            $error    = curl_error($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            if ($error || !$response) {
                // Chunk gagal — kembalikan null agar tidak di-cache
                $results[$i] = null;
                continue;
            }

            $decoded = json_decode($response, true);
            $results[$i] = (is_array($decoded) && count($decoded) === count($chunks[$i]))
                ? $decoded
                : null;  // JSON tidak valid atau panjang mismatch — jangan cache
        }

        curl_multi_close($mh);

        return $results;
    }

    // ─── File-based Decrypt Cache ─────────────────────────────────────────────
    // Disimpan di storage/app/rdm_cache/ — TIDAK terhapus oleh artisan cache:clear
    // atau optimize:clear. Persistent across deploys.

    private function fileCachePath(string $encVal): string
    {
        $hash = md5($encVal);
        // Simpan dalam subfolder 2-char pertama hash untuk hindari too many files per dir
        return 'rdm_cache/' . substr($hash, 0, 2) . '/' . $hash;
    }

    private function fileCache(string $encVal): ?string
    {
        if (trim($encVal) === '') {
            return '';
        }
        $path = $this->fileCachePath($encVal);
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }
        return null;
    }

    private function fileCachePut(string $encVal, string $decVal): void
    {
        if (trim($encVal) === '') {
            return;
        }
        $path = $this->fileCachePath($encVal);
        Storage::disk('local')->put($path, $decVal);
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
            ->with(['user:id,username', 'kelasAktif']);

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
            $nis = trim((string) ($s->nis_lokal ?: ($s->user->username ?? '')));
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
        // ── Pre-build SIMANSA name indexes for efficient fuzzy lookup ──
        $simansaNorm       = [];   // id => normalized_name
        $simansaTokenIdx   = [];   // token => [id, ...]
        $simansaFirstChar  = [];   // firstChar => [id, ...]
        $simansaById       = $simansaAll->keyBy('id');

        foreach ($simansaAll as $s) {
            $norm = $this->normalizeName($s->nama_lengkap ?? '');
            $simansaNorm[$s->id] = $norm;

            foreach ($this->nameTokens($norm) as $token) {
                if (mb_strlen($token) >= 3) {
                    $simansaTokenIdx[$token][] = $s->id;
                }
            }

            $fc = mb_substr($norm, 0, 1, 'UTF-8');
            if ($fc !== '') {
                $simansaFirstChar[$fc][] = $s->id;
            }
        }

        $matched         = [];
        $rdmOnly         = [];
        $fuzzyCandidates = [];
        $matchedIds      = [];

        foreach ($rdmRows as $row) {
            $nisn = trim($row->siswa_nisn_plain ?? '');
            $nis  = trim($row->siswa_nis ?? '');

            $simansaSiswa = null;
            $matchBy      = null;

            // Priority 1: NISN exact
            if ($nisn !== '' && isset($simansaByNisn[$nisn])) {
                $simansaSiswa = $simansaByNisn[$nisn];
                $matchBy = 'nisn';
            }
            // Priority 2: NIS/username exact
            elseif ($nis !== '' && isset($simansaByNis[$nis])) {
                $simansaSiswa = $simansaByNis[$nis];
                $matchBy = 'nis';
            }

            if ($simansaSiswa) {
                $matchedIds[] = $simansaSiswa->id;
                $kelas = $simansaSiswa->kelasAktif->first();
                $matched[] = [
                    'rdm_nis'              => $nis,
                    'rdm_nisn'             => $nisn,
                    'rdm_nama'             => $row->siswa_nama_plain,
                    'rdm_tingkat'          => $this->tingkatLabel($row->tingkat_id),
                    'rdm_kelas'            => $row->kelas_nama,
                    'simansa_id'           => $simansaSiswa->id,
                    'simansa_nisn'         => $simansaSiswa->nisn,
                    'simansa_nama'         => $simansaSiswa->nama_lengkap,
                    'simansa_kelas'        => $kelas?->nama_kelas,
                    'simansa_data_lengkap' => ($simansaSiswa->data_diri_completed && $simansaSiswa->data_ortu_completed),
                    'match_by'             => $matchBy,
                ];
                continue;
            }

            // ── Priority 3: Smart Fuzzy Name Matching ──
            $rdmNorm   = $this->normalizeName($row->siswa_nama_plain ?? '');
            $rdmTokens = $this->nameTokens($rdmNorm);

            // Gather candidate SIMANSA IDs via token index (exact token overlap)
            $candidateIds = [];
            foreach ($rdmTokens as $token) {
                if (mb_strlen($token) >= 3 && isset($simansaTokenIdx[$token])) {
                    foreach ($simansaTokenIdx[$token] as $sid) {
                        $candidateIds[$sid] = true;
                    }
                }
            }

            // Also add first-char candidates to catch spelling variations
            // (e.g. "MUHAMAD" vs "MUHAMMAD" share no exact tokens)
            $fc = mb_substr($rdmNorm, 0, 1, 'UTF-8');
            foreach ($simansaFirstChar[$fc] ?? [] as $sid) {
                $candidateIds[$sid] = true;
            }

            // Cap at 200 for performance safety
            $candidateIds = array_slice(array_keys($candidateIds), 0, 200);

            // Score each candidate
            $candidates = [];
            foreach ($candidateIds as $sid) {
                $s = $simansaById[$sid] ?? null;
                if (!$s) {
                    continue;
                }
                $score = $this->nameSimilarity($rdmNorm, $simansaNorm[$sid] ?? '');
                if ($score >= 60.0) {
                    $kelas = $s->kelasAktif->first();
                    $candidates[] = [
                        'simansa_id'           => $s->id,
                        'simansa_nama'         => $s->nama_lengkap,
                        'simansa_nisn'         => $s->nisn,
                        'simansa_kelas'        => $kelas?->nama_kelas,
                        'simansa_tingkat'      => $kelas ? 'Kelas ' . $kelas->tingkat : '-',
                        'simansa_data_lengkap' => ($s->data_diri_completed && $s->data_ortu_completed),
                        'score'                => $score,
                        'score_label'          => $this->scoreLabel($score),
                        'score_color'          => $this->scoreColor($score),
                    ];
                }
            }

            // Sort by score desc, keep top 3
            usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);
            $candidates = array_slice($candidates, 0, 3);

            $rdmInfo = [
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

            if (!empty($candidates)) {
                $fuzzyCandidates[] = array_merge($rdmInfo, ['candidates' => $candidates]);
            } else {
                $rdmOnly[] = $rdmInfo;
            }
        }

        // SIMANSA-only: tidak ditemukan via exact match manapun
        $simansaOnly = $simansaAll
            ->filter(fn ($s) => !in_array($s->id, $matchedIds, true))
            ->map(function ($s) {
                $kelas = $s->kelasAktif->first();
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
            'tahun_rdm'        => $activeTahun?->tahunajaran_nama,
            'tingkat_label'    => $tingkatId ? (self::TINGKAT_LABELS[$tingkatId] ?? 'Semua Tingkat') : 'Semua Tingkat',
            'matched'          => $matched,
            'rdm_only'         => $rdmOnly,
            'fuzzy_candidates' => $fuzzyCandidates,
            'simansa_only'     => $simansaOnly,
            'stats'            => [
                'total_rdm'          => count($rdmRows),
                'total_simansa'      => $simansaAll->count(),
                'total_matched'      => count($matched),
                'total_fuzzy'        => count($fuzzyCandidates),
                'total_rdm_only'     => count($rdmOnly),
                'total_simansa_only' => count($simansaOnly),
            ],
        ];
    }

    // ─── Smart Name Similarity ────────────────────────────────────────────────

    /**
     * Normalize name for comparison:
     * uppercase, remove dots/commas, collapse whitespace.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtoupper(trim($name), 'UTF-8');
        $name = str_replace(['.', ',', "'", '\u2019', '`', '-', '_'], ' ', $name);
        $name = (string) preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    /** Split normalized name into word tokens. */
    private function nameTokens(string $normalized): array
    {
        return array_values(array_filter(
            explode(' ', $normalized),
            fn ($t) => mb_strlen($t) > 0
        ));
    }

    /**
     * Combined smart similarity score (0–100).
     *
     * Uses:
     * 1. similar_text() percentage
     * 2. Levenshtein distance (normalized)
     * 3. Jaccard token similarity
     * 4. Subset bonus: all words of shorter name in longer name (abbreviation)
     * 5. Initial-letter bonus: "M." matching "MUHAMMAD"
     */
    private function nameSimilarity(string $normA, string $normB): float
    {
        if ($normA === $normB) {
            return 100.0;
        }
        if ($normA === '' || $normB === '') {
            return 0.0;
        }

        // 1. similar_text
        similar_text($normA, $normB, $simPct);

        // 2. Levenshtein (cap at 255 chars)
        $a255   = substr($normA, 0, 255);
        $b255   = substr($normB, 0, 255);
        $maxLen = max(strlen($a255), strlen($b255));
        $lev    = levenshtein($a255, $b255);
        $levScore = max(0.0, (1 - $lev / $maxLen) * 100);

        // 3. Jaccard token similarity
        $tokA      = $this->nameTokens($normA);
        $tokB      = $this->nameTokens($normB);
        $intersect = count(array_intersect($tokA, $tokB));
        $union     = count(array_unique(array_merge($tokA, $tokB)));
        $jaccard   = $union > 0 ? ($intersect / $union) * 100 : 0.0;

        // 4. Subset bonus: all tokens of shorter name found in longer name
        //    → catches abbreviations like "SITI AISYAH" in "SITI AISYAH RAHMAWATI"
        $subsetBonus = 0.0;
        $shorter = count($tokA) <= count($tokB) ? $tokA : $tokB;
        $longer  = count($tokA) <= count($tokB) ? $tokB : $tokA;
        if (count($shorter) >= 2) {
            $matchedInLonger = count(array_intersect($shorter, $longer));
            if ($matchedInLonger === count($shorter)) {
                $subsetBonus = 20.0;
            } elseif ($matchedInLonger >= count($shorter) - 1) {
                $subsetBonus = 10.0;
            }
        }

        // 5. Initial-letter bonus: single letter token matching start of longer token
        //    → catches "M." or "M" matching "MUHAMMAD"
        $initialBonus = 0.0;
        if (!empty($tokA) && !empty($tokB)) {
            $fA = $tokA[0];
            $fB = $tokB[0];
            if (mb_strlen($fA) === 1 && mb_strpos($fB, $fA) === 0) {
                $initialBonus = 8.0;
            } elseif (mb_strlen($fB) === 1 && mb_strpos($fA, $fB) === 0) {
                $initialBonus = 8.0;
            }
        }

        $base = ($simPct * 0.35) + ($levScore * 0.25) + ($jaccard * 0.40);

        return min(100.0, round($base + $subsetBonus + $initialBonus, 1));
    }

    private function scoreLabel(float $score): string
    {
        if ($score >= 88) {
            return 'Sangat Mirip';
        }
        if ($score >= 72) {
            return 'Mirip';
        }
        return 'Potensi Mirip';
    }

    private function scoreColor(float $score): string
    {
        if ($score >= 88) {
            return 'success';
        }
        if ($score >= 72) {
            return 'warning';
        }
        return 'secondary';
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function tingkatLabel(mixed $tingkatId): string
    {
        return self::TINGKAT_LABELS[(int) $tingkatId] ?? ('Tingkat ' . $tingkatId);
    }

    private function emptyResult(?object $activeTahun, ?int $tingkatId): array
    {
        return [
            'tahun_rdm'        => $activeTahun?->tahunajaran_nama,
            'tingkat_label'    => $tingkatId ? (self::TINGKAT_LABELS[$tingkatId] ?? 'Semua') : 'Semua Tingkat',
            'matched'          => [],
            'rdm_only'         => [],
            'fuzzy_candidates' => [],
            'simansa_only'     => [],
            'stats'            => [
                'total_rdm'          => 0,
                'total_simansa'      => 0,
                'total_matched'      => 0,
                'total_fuzzy'        => 0,
                'total_rdm_only'     => 0,
                'total_simansa_only' => 0,
            ],
        ];
    }
}
