<?php

namespace App\Services;

use Carbon\Carbon;

class SmartStudentComparator
{
    public const LABELS = [
        'nama_lengkap' => 'Nama Lengkap',
        'nisn' => 'NISN',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'jenis_kelamin' => 'Jenis Kelamin',
        'kelas' => 'Kelas / Rombel',
    ];

    public function compare(array $simansa, array $emis): array
    {
        $details = [
            'nama_lengkap' => $this->compareName($simansa['nama_lengkap'] ?? null, $emis['nama_lengkap'] ?? null),
            'nisn' => $this->compareText($simansa['nisn'] ?? null, $emis['nisn'] ?? null),
            'tempat_lahir' => $this->compareText($simansa['tempat_lahir'] ?? null, $emis['tempat_lahir'] ?? null),
            'tanggal_lahir' => $this->compareDate($simansa['tanggal_lahir'] ?? null, $emis['tanggal_lahir'] ?? null),
            'jenis_kelamin' => $this->compareGender($simansa['jenis_kelamin'] ?? null, $emis['jenis_kelamin'] ?? null),
            'kelas' => $this->compareClass($simansa['kelas'] ?? null, $emis['kelas'] ?? null),
        ];

        foreach ($details as $field => &$detail) {
            $detail['label'] = self::LABELS[$field];
        }

        $comparable = collect($details)->whereNotIn('status', ['both_empty', 'emis_empty']);
        $overall = 'exact';

        if ($comparable->contains(fn (array $detail) => in_array($detail['status'], ['different', 'simansa_empty'], true))) {
            $overall = 'different';
        } elseif ($comparable->contains('status', 'similar')) {
            $overall = 'similar';
        } elseif ($comparable->contains('status', 'equivalent')) {
            $overall = 'normalized';
        }

        return [
            'status' => $overall,
            'name_similarity' => $details['nama_lengkap']['score'] ?? null,
            'details' => $details,
            'different_fields' => collect($details)
                ->filter(fn (array $detail) => in_array($detail['status'], ['different', 'similar', 'simansa_empty'], true))
                ->keys()
                ->values()
                ->all(),
        ];
    }

    private function compareName(mixed $left, mixed $right): array
    {
        $base = $this->baseResult($left, $right);
        if ($base) {
            return $base;
        }

        $leftRaw = trim((string) $left);
        $rightRaw = trim((string) $right);
        if ($leftRaw === $rightRaw) {
            return $this->result($left, $right, 'exact', 100);
        }

        $leftNormalized = $this->normalizeText($leftRaw);
        $rightNormalized = $this->normalizeText($rightRaw);
        if ($leftNormalized === $rightNormalized) {
            return $this->result($left, $right, 'equivalent', 100);
        }

        similar_text($leftNormalized, $rightNormalized, $characterScore);
        $leftTokens = array_values(array_filter(explode(' ', $leftNormalized)));
        $rightTokens = array_values(array_filter(explode(' ', $rightNormalized)));
        $union = array_unique(array_merge($leftTokens, $rightTokens));
        $tokenScore = count($union) > 0
            ? count(array_intersect($leftTokens, $rightTokens)) / count($union) * 100
            : 0;
        // Ambil sinyal terkuat: character score bagus untuk typo kecil,
        // token score bagus ketika urutan kata nama berbeda.
        $score = round(max($characterScore, $tokenScore), 2);

        return $this->result($left, $right, $score >= 85 ? 'similar' : 'different', $score);
    }

    private function compareText(mixed $left, mixed $right): array
    {
        $base = $this->baseResult($left, $right);
        if ($base) {
            return $base;
        }

        if (trim((string) $left) === trim((string) $right)) {
            return $this->result($left, $right, 'exact');
        }

        return $this->result(
            $left,
            $right,
            $this->normalizeText($left) === $this->normalizeText($right) ? 'equivalent' : 'different'
        );
    }

    private function compareDate(mixed $left, mixed $right): array
    {
        $base = $this->baseResult($left, $right);
        if ($base) {
            return $base;
        }

        try {
            $leftDate = Carbon::parse($left)->format('Y-m-d');
            $rightDate = Carbon::parse($right)->format('Y-m-d');
        } catch (\Throwable) {
            return $this->compareText($left, $right);
        }

        return $this->result($left, $right, $leftDate === $rightDate ? 'equivalent' : 'different');
    }

    private function compareGender(mixed $left, mixed $right): array
    {
        $base = $this->baseResult($left, $right);
        if ($base) {
            return $base;
        }

        $normalize = function ($value): string {
            $value = $this->normalizeText($value);

            return match ($value) {
                'l', 'laki laki', 'male', 'pria' => 'L',
                'p', 'perempuan', 'female', 'wanita' => 'P',
                default => strtoupper($value),
            };
        };

        return $this->result($left, $right, $normalize($left) === $normalize($right) ? 'equivalent' : 'different');
    }

    private function compareClass(mixed $left, mixed $right): array
    {
        $base = $this->baseResult($left, $right);
        if ($base) {
            return $base;
        }

        $normalize = function ($value): string {
            $value = strtoupper($this->normalizeText($value));
            $value = preg_replace('/\bXII\b/', '12', $value);
            $value = preg_replace('/\bXI\b/', '11', $value);
            $value = preg_replace('/\bX\b/', '10', $value);

            return preg_replace('/\s+/', '', $value);
        };

        return $this->result($left, $right, $normalize($left) === $normalize($right) ? 'equivalent' : 'different');
    }

    private function baseResult(mixed $left, mixed $right): ?array
    {
        $leftEmpty = blank($left);
        $rightEmpty = blank($right);

        if ($leftEmpty && $rightEmpty) {
            return $this->result($left, $right, 'both_empty');
        }

        if ($leftEmpty) {
            return $this->result($left, $right, 'simansa_empty');
        }

        if ($rightEmpty) {
            return $this->result($left, $right, 'emis_empty');
        }

        return null;
    }

    private function result(mixed $left, mixed $right, string $status, ?float $score = null): array
    {
        return [
            'simansa' => $left,
            'emis' => $right,
            'status' => $status,
            'score' => $score,
        ];
    }

    private function normalizeText(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
