<?php

namespace App\Services;

use App\Models\BukuNomorSurat;
use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuratNumberService
{
    public function year(?Carbon $date = null): int
    {
        return (int) (TahunPelajaran::active()->value('tahun_mulai') ?: ($date ?: now())->year);
    }

    public function findConfig(string $kode): SuratNomorConfig
    {
        $normalized = strtoupper(trim($kode));
        return SuratNomorConfig::where(function ($q) use ($normalized) {
            $q->where('kode', $normalized)
              ->orWhere('kode_klasifikasi', $normalized);
        })->where('is_active', true)->firstOrFail();
    }

    public function preview(string $kode = 'PP', $date = null): string
    {
        $config = $this->findConfig($kode);
        $date = $date ? Carbon::parse($date) : now();
        $year = $this->year($date);
        $counter = SuratNomorCounter::where('tahun', $year)->value('nomor_terakhir') ?? 0;

        return $this->format($config, $counter + 1, $date, $year);
    }

    /**
     * Generate sequential letter number and record into BukuNomorSurat registry.
     * Returns the formatted letter number string for backwards compatibility.
     */
    public function generate(string $kode = 'PP', $date = null, array $meta = []): string
    {
        if (is_array($date)) {
            $meta = $date;
            $date = null;
        }

        $buku = $this->issue($kode, $date, $meta);
        return $buku->nomor_surat;
    }

    /**
     * Issue and persist a new letter number record in BukuNomorSurat.
     */
    public function issue(string $kode = 'PP', $date = null, array $meta = []): BukuNomorSurat
    {
        if (is_array($date)) {
            $meta = $date;
            $date = null;
        }

        return DB::transaction(function () use ($kode, $date, $meta) {
            $config = $this->findConfig($kode);
            $date = $date ? Carbon::parse($date) : now();
            $year = $this->year($date);
            $counter = SuratNomorCounter::where('tahun', $year)->lockForUpdate()->first();

            if (!$counter) {
                $counter = SuratNomorCounter::create(['tahun' => $year, 'nomor_terakhir' => 0]);
            }

            $next = $counter->nomor_terakhir + 1;
            $counter->update(['nomor_terakhir' => $next]);

            $nomorSurat = $this->format($config, $next, $date, $year);

            $defaultPerihal = $meta['perihal'] ?? ($config->nama . ' No. ' . $next);

            return BukuNomorSurat::create([
                'nomor_surat' => $nomorSurat,
                'nomor_urut' => $next,
                'tahun' => $year,
                'tanggal_surat' => $date->toDateString(),
                'surat_nomor_config_id' => $config->id,
                'kode_klasifikasi' => $config->kode_klasifikasi,
                'perihal' => $defaultPerihal,
                'tujuan' => $meta['tujuan'] ?? null,
                'modul_asal' => $meta['modul_asal'] ?? 'manual_tu',
                'suratable_type' => $meta['suratable_type'] ?? null,
                'suratable_id' => $meta['suratable_id'] ?? null,
                'penandatangan_id' => $meta['penandatangan_id'] ?? null,
                'nama_penandatangan' => $meta['nama_penandatangan'] ?? null,
                'jabatan_penandatangan' => $meta['jabatan_penandatangan'] ?? null,
                'created_by' => $meta['created_by'] ?? Auth::id(),
                'status' => 'aktif',
                'keterangan' => $meta['keterangan'] ?? null,
            ]);
        });
    }

    /**
     * Cancel an issued letter number with reason and audit trail.
     * Supports passing BukuNomorSurat instance, UUID id, or formatted nomor_surat string.
     */
    public function cancel(BukuNomorSurat|string $buku, string $alasan, ?string $userId = null): bool
    {
        if (is_string($buku)) {
            $record = BukuNomorSurat::where('id', $buku)
                ->orWhere('nomor_surat', $buku)
                ->firstOrFail();
        } else {
            $record = $buku;
        }

        return $record->update([
            'status' => 'dibatalkan',
            'alasan_dibatalkan' => $alasan,
            'dibatalkan_oleh' => $userId ?: Auth::id(),
            'dibatalkan_at' => now(),
        ]);
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
