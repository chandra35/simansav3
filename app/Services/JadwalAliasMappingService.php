<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\JadwalGuruAlias;
use App\Models\JadwalMapelAlias;
use App\Models\Kurikulum;
use App\Models\MataPelajaran;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalAliasMappingService
{
    public function referenceYear(): ?TahunPelajaran
    {
        return TahunPelajaran::query()
            ->where('tahun_mulai', 2026)
            ->where('tahun_selesai', 2027)
            ->first();
    }

    public function synchronize(?TahunPelajaran $year = null, ?User $actor = null): array
    {
        $year ??= $this->referenceYear();
        if (!$year) {
            return ['guru' => 0, 'mapel' => 0, 'verified_guru' => 0, 'verified_mapel' => 0];
        }

        // Dibaca langsung agar migrasi tetap aman ketika production masih
        // menggunakan config cache dari commit sebelumnya.
        $reference = require base_path('config/jadwal_reference_2026.php');
        if (
            (int) $year->tahun_mulai !== (int) ($reference['tahun_mulai'] ?? 0)
            || (int) $year->tahun_selesai !== (int) ($reference['tahun_selesai'] ?? 0)
        ) {
            throw new \DomainException('Referensi Excel ini khusus untuk tahun pelajaran 2026/2027.');
        }
        $source = $reference['source'] ?? 'jadwal_excel';
        $gtks = Gtk::query()->orderBy('nama_lengkap')->get();

        return DB::transaction(function () use ($year, $actor, $reference, $source, $gtks) {
            $this->ensureOperationalMapels($year);

            foreach ($reference['guru'] ?? [] as $row) {
                $alias = JadwalGuruAlias::firstOrNew([
                    'tahun_pelajaran_id' => $year->id,
                    'source' => $source,
                    'external_code' => (string) $row['code'],
                ]);

                $alias->external_name = $row['name'];
                $alias->normalized_name = $this->normalizePersonName($row['name']);
                $alias->context = $row['context'] ?? null;

                // Keputusan admin tidak ditimpa ketika referensi disinkronkan ulang.
                if (!$alias->exists || !in_array($alias->status, ['verified', 'rejected'], true)) {
                    $match = $this->bestGtkMatch($row, $gtks);
                    $alias->gtk_id = $match['gtk']?->id;
                    $alias->confidence = $match['score'];
                    $alias->match_method = $match['method'];
                    $alias->status = $match['status'];

                    if ($match['status'] === 'verified') {
                        $alias->verified_by = $actor?->id;
                        $alias->verified_at = now();
                    }
                }

                $alias->save();
                $this->applyVerifiedGtkCode($alias);
            }

            $merdekaId = Kurikulum::query()->whereRaw('UPPER(kode) = ?', ['MERDEKA'])->value('id');
            foreach ($reference['mapel'] ?? [] as $row) {
                $mapel = MataPelajaran::query()
                    ->when($merdekaId, fn ($query) => $query->where('kurikulum_id', $merdekaId))
                    ->where('kode_mapel', $row['canonical_code'])
                    ->first();

                $alias = JadwalMapelAlias::firstOrNew([
                    'tahun_pelajaran_id' => $year->id,
                    'source' => $source,
                    'external_code' => $row['code'],
                ]);
                $alias->external_name = $row['name'];
                $alias->normalized_name = $this->normalizeText($row['name']);

                if (!$alias->exists || !in_array($alias->status, ['verified', 'rejected'], true)) {
                    $alias->mata_pelajaran_id = $mapel?->id;
                    $alias->confidence = $mapel ? 100 : 0;
                    $alias->match_method = $mapel ? 'canonical_code' : 'not_found';
                    $alias->status = $mapel ? 'verified' : 'pending';
                    if ($mapel) {
                        $alias->verified_by = $actor?->id;
                        $alias->verified_at = now();
                    }
                }
                $alias->save();
            }

            return [
                'guru' => JadwalGuruAlias::where('tahun_pelajaran_id', $year->id)->where('source', $source)->count(),
                'mapel' => JadwalMapelAlias::where('tahun_pelajaran_id', $year->id)->where('source', $source)->count(),
                'verified_guru' => JadwalGuruAlias::where('tahun_pelajaran_id', $year->id)->where('source', $source)->where('status', 'verified')->count(),
                'verified_mapel' => JadwalMapelAlias::where('tahun_pelajaran_id', $year->id)->where('source', $source)->where('status', 'verified')->count(),
            ];
        });
    }

    public function applyVerifiedGtkCode(JadwalGuruAlias $alias): void
    {
        if ($alias->status !== 'verified' || !$alias->gtk_id) {
            return;
        }

        Gtk::query()
            ->where('kode_gtk', $alias->external_code)
            ->where('id', '!=', $alias->gtk_id)
            ->update(['kode_gtk' => null]);

        Gtk::query()->whereKey($alias->gtk_id)->update(['kode_gtk' => $alias->external_code]);
    }

    public function normalizePersonName(?string $value): string
    {
        $normalized = $this->normalizeText($value);
        $normalized = preg_replace(
            '/\b[sm] (?:pd|ag|si|tp|e|h|sos|kom|kes|psi)(?: i| sy)?\b/',
            ' ',
            $normalized
        );
        $normalized = preg_replace('/\bm (?:p (?:fis|kim)|a)\b/', ' ', $normalized);
        $tokens = preg_split('/\s+/', trim(preg_replace('/\s+/', ' ', $normalized))) ?: [];
        $titles = [
            'h', 'hi', 'hj', 'dra', 'drs', 'dr', 'prof',
            'spd', 'spdi', 'mpd', 'mpdi', 'sag', 'msi', 'ssi', 'se', 'sesy',
            'st', 'sh', 'ssos', 'ma', 'mkom', 'mkes', 'mtp', 'stp', 'spsi',
        ];

        return implode(' ', array_values(array_filter(
            $tokens,
            fn (string $token) => $token !== '' && !in_array($token, $titles, true)
        )));
    }

    public function normalizeText(?string $value): string
    {
        $value = Str::lower(Str::ascii((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function bestGtkMatch(array $row, Collection $gtks): array
    {
        $external = $this->normalizePersonName($row['name'] ?? '');
        $hint = $this->normalizePersonName($row['canonical_hint'] ?? '');
        $target = $hint ?: $external;
        $ranked = $gtks->map(function (Gtk $gtk) use ($target) {
            $candidate = $this->normalizePersonName($gtk->nama_lengkap);
            return [
                'gtk' => $gtk,
                'candidate' => $candidate,
                'score' => $this->nameScore($target, $candidate),
            ];
        })->sortByDesc('score')->values();

        $best = $ranked->first();
        $second = $ranked->get(1);
        if (!$best || $best['score'] < 72) {
            return ['gtk' => null, 'score' => $best['score'] ?? 0, 'method' => 'not_found', 'status' => 'pending'];
        }

        $isExact = $target !== '' && $target === $best['candidate'];
        $margin = $best['score'] - ($second['score'] ?? 0);

        // Hint kanonik untuk nama rawan hanya boleh auto-verifikasi jika benar-benar sama.
        if ($hint !== '' && !$isExact) {
            return [
                'gtk' => null,
                'score' => $best['score'],
                'method' => 'canonical_hint_review',
                'status' => 'suggested',
            ];
        }

        if ($isExact || ($best['score'] >= 98 && $margin >= 8)) {
            return [
                'gtk' => $best['gtk'],
                'score' => $best['score'],
                'method' => $hint ? 'canonical_hint_exact' : 'normalized_exact',
                'status' => 'verified',
            ];
        }

        return [
            'gtk' => $margin >= 4 ? $best['gtk'] : null,
            'score' => $best['score'],
            'method' => 'smart_name',
            'status' => 'suggested',
        ];
    }

    private function nameScore(string $left, string $right): float
    {
        if ($left === '' || $right === '') {
            return 0;
        }
        if ($left === $right) {
            return 100;
        }

        similar_text($left, $right, $similar);
        $maxLength = max(strlen($left), strlen($right));
        $levenshtein = $maxLength
            ? max(0, 100 * (1 - levenshtein($left, $right) / $maxLength))
            : 0;

        $leftTokens = explode(' ', $left);
        $rightTokens = explode(' ', $right);
        $tokenScores = [];
        foreach ($leftTokens as $token) {
            $best = 0;
            foreach ($rightTokens as $candidate) {
                if (strlen($token) === 1 && str_starts_with($candidate, $token)) {
                    $score = 100;
                } elseif (strlen($candidate) === 1 && str_starts_with($token, $candidate)) {
                    $score = 100;
                } else {
                    similar_text($token, $candidate, $score);
                }
                $best = max($best, $score);
            }
            $tokenScores[] = $best;
        }
        $tokenAverage = $tokenScores ? array_sum($tokenScores) / count($tokenScores) : 0;

        return round(($similar * 0.35) + ($levenshtein * 0.25) + ($tokenAverage * 0.40), 2);
    }

    private function ensureOperationalMapels(TahunPelajaran $year): void
    {
        $kurikulumId = $year->kurikulum_id
            ?: Kurikulum::query()->whereRaw('UPPER(kode) = ?', ['MERDEKA'])->value('id');
        if (!$kurikulumId) {
            return;
        }

        $defaults = [
            [
                'kode_mapel' => 'M-SJRL',
                'nama_mapel' => 'Sejarah Tingkat Lanjut',
                'kelompok' => 'B',
                'kategori' => 'Pilihan',
                'struktur_fase_e' => null,
                'struktur_fase_f' => 'pilihan',
                'rumpun' => 'ips',
                'alokasi_jp' => ['11' => 5, '12' => 5],
                'jam_pelajaran' => 5,
                'tingkat' => [11, 12],
                'is_mapel_pilihan' => true,
            ],
            [
                'kode_mapel' => 'M-BK',
                'nama_mapel' => 'Bimbingan Konseling',
                'kelompok' => 'Lainnya',
                'kategori' => 'Layanan Siswa',
                'struktur_fase_e' => 'penguatan_program',
                'struktur_fase_f' => 'penguatan_program',
                'rumpun' => 'layanan_siswa',
                'alokasi_jp' => ['10' => 1, '11' => 1, '12' => 1],
                'jam_pelajaran' => 1,
                'tingkat' => [10, 11, 12],
                'is_mapel_pilihan' => false,
            ],
        ];

        foreach ($defaults as $data) {
            MataPelajaran::firstOrCreate(
                ['kurikulum_id' => $kurikulumId, 'kode_mapel' => $data['kode_mapel']],
                array_merge($data, [
                    'tahun_pelajaran_id' => $year->id,
                    'semester' => [1, 2],
                    'kkm' => 75,
                    'regulasi' => 'KMA 1503 Tahun 2025',
                    'is_schedulable' => true,
                    'is_active' => true,
                ])
            );
        }
    }
}
