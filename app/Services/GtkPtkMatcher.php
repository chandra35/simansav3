<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class GtkPtkMatcher
{
    public function match(array $source, Collection $gtks): array
    {
        $identityMatches = [];

        foreach (['nik', 'nip', 'nuptk'] as $field) {
            $value = $this->normalizeIdentifier($source[$field] ?? null);
            if (! $value) {
                continue;
            }

            foreach ($gtks as $gtk) {
                if ($value === $this->normalizeIdentifier($gtk->{$field})) {
                    $identityMatches[$gtk->id][] = strtoupper($field);
                }
            }
        }

        if (count($identityMatches) > 1) {
            return $this->result('ambiguous', null, 0, 'identity_conflict', 'Identitas sumber mengarah ke GTK yang berbeda.');
        }

        if (count($identityMatches) === 1) {
            $gtkId = array_key_first($identityMatches);
            $gtk = $gtks->firstWhere('id', $gtkId);
            $nameScore = $this->nameSimilarity($source['nama'] ?? '', $gtk->nama_lengkap);
            $birthMatches = $this->sameDate($source['tanggal_lahir'] ?? null, $gtk->tanggal_lahir);

            if ($nameScore < 45 && ! $birthMatches) {
                return $this->result('ambiguous', $gtk, $nameScore, 'identity_name_conflict', 'Identitas sama, tetapi nama dan tanggal lahir tidak mendukung.');
            }

            return $this->result('matched', $gtk, $nameScore, implode('+', $identityMatches[$gtkId]), 'Identitas resmi cocok.');
        }

        $ranked = $gtks->map(function ($gtk) use ($source) {
            $nameScore = $this->nameSimilarity($source['nama'] ?? '', $gtk->nama_lengkap);
            $birthMatches = $this->sameDate($source['tanggal_lahir'] ?? null, $gtk->tanggal_lahir);

            return [
                'gtk' => $gtk,
                'name_score' => $nameScore,
                'birth_matches' => $birthMatches,
                'score' => min(100, $nameScore + ($birthMatches ? 6 : 0)),
            ];
        })->sortByDesc('score')->values();

        $best = $ranked->first();
        $runnerUp = $ranked->get(1);
        if (! $best) {
            return $this->result('unmatched', null, 0, 'none', 'Database GTK kosong.');
        }

        $margin = $best['score'] - ($runnerUp['score'] ?? 0);
        $isConfident = ($best['name_score'] >= 92 && $margin >= 6)
            || ($best['name_score'] >= 84 && $best['birth_matches'] && $margin >= 4);

        if ($isConfident) {
            $method = $best['birth_matches'] ? 'smart_name+birth_date' : 'smart_name';

            return $this->result('matched', $best['gtk'], $best['name_score'], $method, 'Nama cocok unik dengan margin '.round($margin, 2).'.');
        }

        if ($best['name_score'] >= 75) {
            return $this->result('ambiguous', $best['gtk'], $best['name_score'], 'smart_name_low_confidence', 'Kandidat terdekat belum cukup unik; margin '.round($margin, 2).'.');
        }

        return $this->result('unmatched', $best['gtk'], $best['name_score'], 'none', 'Tidak ada kandidat dengan kemiripan memadai.');
    }

    public function nameSimilarity(?string $left, ?string $right): float
    {
        $left = $this->normalizeName($left);
        $right = $this->normalizeName($right);
        if ($left === '' || $right === '') {
            return 0;
        }
        if ($left === $right) {
            return 100;
        }

        similar_text($left, $right, $characterScore);
        $leftTokens = explode(' ', $left);
        $rightTokens = explode(' ', $right);
        $union = array_unique(array_merge($leftTokens, $rightTokens));
        $tokenScore = count($union) ? count(array_intersect($leftTokens, $rightTokens)) / count($union) * 100 : 0;
        $orderedScore = $this->orderedTokenScore($leftTokens, $rightTokens);

        return round(max($characterScore, $tokenScore, $orderedScore), 2);
    }

    public function normalizeName(?string $name): string
    {
        $name = mb_strtolower(trim((string) $name));
        $name = explode(',', $name, 2)[0];
        $name = preg_replace('/[^\pL\pN]+/u', ' ', $name) ?? '';
        $tokens = array_values(array_filter(preg_split('/\s+/', trim($name)) ?: []));
        $prefixes = ['dr', 'dra', 'drs', 'prof', 'ir', 'h', 'hj', 'ust', 'ustadz', 'ustadzah', 'kh'];

        while ($tokens && in_array($tokens[0], $prefixes, true)) {
            array_shift($tokens);
        }

        return implode(' ', $tokens);
    }

    private function orderedTokenScore(array $left, array $right): float
    {
        $length = max(count($left), count($right));
        if ($length === 0) {
            return 0;
        }

        $matches = 0;
        foreach (range(0, $length - 1) as $index) {
            $a = $left[$index] ?? '';
            $b = $right[$index] ?? '';
            if ($a !== '' && $b !== '' && ($a === $b || str_starts_with($a, $b) || str_starts_with($b, $a))) {
                $matches++;
            }
        }

        return $matches / $length * 100;
    }

    private function normalizeIdentifier(mixed $value): ?string
    {
        $value = preg_replace('/[^0-9A-Za-z]+/', '', trim((string) $value));

        return $value === '' ? null : strtoupper($value);
    }

    private function sameDate(mixed $left, mixed $right): bool
    {
        if (blank($left) || blank($right)) {
            return false;
        }

        try {
            return Carbon::parse($left)->toDateString() === Carbon::parse($right)->toDateString();
        } catch (\Throwable) {
            return false;
        }
    }

    private function result(string $status, mixed $gtk, float $score, string $method, string $note): array
    {
        return compact('status', 'gtk', 'score', 'method', 'note');
    }
}
