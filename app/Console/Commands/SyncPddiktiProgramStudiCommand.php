<?php

namespace App\Console\Commands;

use App\Models\ReferensiPerguruanTinggi;
use App\Models\ReferensiProgramStudi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SyncPddiktiProgramStudiCommand extends Command
{
    protected $signature = 'referensi:sync-prodi-pddikti
        {--kampus_id=* : UUID referensi kampus yang ingin disinkronkan}
        {--limit= : Batasi jumlah kampus yang diproses}
        {--only-active : Hanya proses kampus yang aktif}
        {--with-detail : Ambil detail tambahan per prodi (lebih lambat)}';

    protected $description = 'Sinkronkan master program studi dari API resmi PDDIKTI ke referensi lokal';

    public function handle(): int
    {
        $campuses = ReferensiPerguruanTinggi::query()
            ->when($this->option('kampus_id'), fn ($query, $ids) => $query->whereIn('id', (array) $ids))
            ->when($this->option('only-active'), fn ($query) => $query->where('is_active', true))
            ->orderBy('nama')
            ->when($this->option('limit'), fn ($query, $limit) => $query->limit((int) $limit))
            ->get();

        if ($campuses->isEmpty()) {
            $this->warn('Tidak ada referensi kampus yang diproses.');
            return self::SUCCESS;
        }

        $headers = [
            'X-User-IP' => '127.0.0.1',
            'Referer' => 'https://pddikti.kemdiktisaintek.go.id/',
            'Origin' => 'https://pddikti.kemdiktisaintek.go.id',
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'application/json, text/plain, */*',
        ];

        $imported = 0;
        $updated = 0;

        foreach ($campuses as $campus) {
            $this->info("Sinkron kampus: {$campus->nama}");

            try {
                $searchResponse = Http::withHeaders($headers)
                    ->withOptions(['verify' => false])
                    ->timeout(30)
                    ->get('https://api-pddikti.kemdiktisaintek.go.id/pencarian/prodi/' . rawurlencode($campus->nama));
            } catch (Throwable $throwable) {
                $this->warn("  Gagal mengambil daftar prodi untuk {$campus->nama}: {$throwable->getMessage()}");
                continue;
            }

            if (!$searchResponse->successful()) {
                $this->warn("  Gagal mengambil daftar prodi untuk {$campus->nama} ({$searchResponse->status()}).");
                continue;
            }

            $items = $searchResponse->json();
            if (isset($items['value']) && is_array($items['value'])) {
                $items = $items['value'];
            }

            if (!is_array($items)) {
                $this->warn("  Format respons tidak dikenali untuk {$campus->nama}.");
                continue;
            }

            $matches = collect($items)
                ->filter(function (array $item) use ($campus) {
                    return $this->normalizedMatch($campus->nama, $item['pt'] ?? '');
                })
                ->unique(fn (array $item) => ($item['nama'] ?? '') . '|' . ($item['jenjang'] ?? ''))
                ->values();

            if ($matches->isEmpty()) {
                $this->warn("  Tidak ada prodi resmi yang cocok dengan kampus {$campus->nama}.");
                continue;
            }

            foreach ($matches as $item) {
                $detail = [];

                if ($this->option('with-detail')) {
                    try {
                        $detail = Http::withHeaders($headers)
                            ->withOptions(['verify' => false])
                            ->timeout(30)
                            ->get('https://api-pddikti.kemdiktisaintek.go.id/prodi/detail/' . rawurlencode($item['id']))
                            ->json();
                    } catch (Throwable $throwable) {
                        $this->warn("    Gagal mengambil detail prodi {$item['nama']}: {$throwable->getMessage()}");
                    }
                }

                $record = ReferensiProgramStudi::query()->firstOrNew([
                    'referensi_perguruan_tinggi_id' => $campus->id,
                    'nama' => Str::upper(trim($item['nama'] ?? '')),
                    'jenjang' => trim($item['jenjang'] ?? '') ?: null,
                ]);

                $isExisting = $record->exists;

                $record->fill([
                    'fakultas' => $detail['kel_bidang'] ?? null,
                    'sumber_referensi' => 'PDDIKTI',
                    'is_active' => true,
                ]);
                $record->save();

                if ($isExisting) {
                    $updated++;
                } else {
                    $imported++;
                }
            }

            $this->line("  Prodi cocok: {$matches->count()}");
        }

        $this->newLine();
        $this->info("Sinkronisasi selesai. Baru: {$imported}, diperbarui: {$updated}");

        return self::SUCCESS;
    }

    private function normalizedMatch(string $referenceName, string $officialName): bool
    {
        $reference = $this->normalizeCampusName($referenceName);
        $official = $this->normalizeCampusName($officialName);

        return $reference === $official
            || str_contains($reference, $official)
            || str_contains($official, $reference);
    }

    private function normalizeCampusName(string $value): string
    {
        $value = Str::upper($value);

        $replacements = [
            'UIN ' => 'UNIVERSITAS ISLAM NEGERI ',
            'IAIN ' => 'INSTITUT AGAMA ISLAM NEGERI ',
            'STAIN ' => 'SEKOLAH TINGGI AGAMA ISLAM NEGERI ',
            'POLTEKKES ' => 'POLITEKNIK KESEHATAN ',
            ' KEMENKES ' => ' KEMENKES ',
            '\'' => '',
            '"' => '',
            '.' => ' ',
            ',' => ' ',
            '-' => ' ',
            '&' => ' DAN ',
        ];

        $value = str_replace(array_keys($replacements), array_values($replacements), $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
