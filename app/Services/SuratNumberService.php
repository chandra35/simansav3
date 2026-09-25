<?php

namespace App\Services;

use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuratNumberService
{
    public function year(?Carbon $date = null): int
    {
        return (int) (TahunPelajaran::active()->value('tahun_mulai') ?: ($date ?: now())->year);
    }

    public function preview(string $kode = 'PP', ?Carbon $date = null): string
    {
        $config = SuratNomorConfig::where('kode', strtoupper($kode))->where('is_active', true)->firstOrFail();
        $date ??= now();
        $year = $this->year($date);
        $counter = SuratNomorCounter::where('tahun', $year)->value('nomor_terakhir') ?? 0;

        return $this->format($config, $counter + 1, $date, $year);
    }

    public function generate(string $kode = 'PP', ?Carbon $date = null): string
    {
        return DB::transaction(function () use ($kode, $date) {
            $config = SuratNomorConfig::where('kode', strtoupper($kode))->where('is_active', true)->firstOrFail();
            $date ??= now();
            $year = $this->year($date);
            $counter = SuratNomorCounter::where('tahun', $year)->lockForUpdate()->first();

            if (!$counter) {
                $counter = SuratNomorCounter::create(['tahun' => $year, 'nomor_terakhir' => 0]);
            }

            $next = $counter->nomor_terakhir + 1;
            $counter->update(['nomor_terakhir' => $next]);

            return $this->format($config, $next, $date, $year);
        });
    }

    private function format(SuratNomorConfig $config, int $number, Carbon $date, int $year): string
    {
        $formattedNumber = str_pad((string) $number, (int) $config->panjang_nomor, '0', STR_PAD_LEFT);
        $tokens = [
            '{prefix}' => $config->prefix,
            '{nomor}' => $formattedNumber,
            '{kode_satuan_kerja}' => $config->kode_satuan_kerja,
            '{kode_klasifikasi}' => $config->kode_klasifikasi,
            '{bulan}' => $date->format('m'),
            '{bulan_romawi}' => ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) $date->format('n')],
            '{tahun}' => (string) $year,
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $config->format_nomor);
    }
}
