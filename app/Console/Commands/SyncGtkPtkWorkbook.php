<?php

namespace App\Console\Commands;

use App\Models\Gtk;
use App\Services\GtkPtkMatcher;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class SyncGtkPtkWorkbook extends Command
{
    protected $signature = 'gtk:sync-ptk {file : Path workbook Manajemen PTK} {--apply : Terapkan hasil match berkeyakinan tinggi} {--report= : Path laporan CSV}';

    protected $description = 'Smart matching dan sinkronisasi identitas profesional GTK dari workbook Manajemen PTK';

    public function handle(GtkPtkMatcher $matcher): int
    {
        $file = $this->resolveFile((string) $this->argument('file'));
        $sources = $this->readWorkbook($file);
        $gtks = Gtk::query()->select(['id', 'nama_lengkap', 'nik', 'nip', 'nuptk', 'tanggal_lahir'])->get();
        $results = $sources->map(function (array $source) use ($matcher, $gtks) {
            return ['source' => $source, ...$matcher->match($source, $gtks)];
        });

        $duplicateGtkIds = $results->where('status', 'matched')->pluck('gtk.id')->duplicates()->unique();
        if ($duplicateGtkIds->isNotEmpty()) {
            $results = $results->map(function (array $result) use ($duplicateGtkIds) {
                if ($result['status'] === 'matched' && $duplicateGtkIds->contains($result['gtk']->id)) {
                    $result['status'] = 'ambiguous';
                    $result['method'] = 'duplicate_source_match';
                    $result['note'] = 'Lebih dari satu baris workbook mengarah ke GTK yang sama.';
                }

                return $result;
            });
        }

        $report = $this->writeReport($results);
        $this->displaySummary($sources, $gtks, $results, $report);

        if (! $this->option('apply')) {
            $this->warn('DRY-RUN: database belum diubah. Gunakan --apply setelah laporan diverifikasi.');

            return self::SUCCESS;
        }

        $matched = $results->where('status', 'matched');
        $backup = $this->writeBackup($matched);
        DB::transaction(function () use ($matched) {
            foreach ($matched as $result) {
                $source = $result['source'];
                $updates = [
                    'peg_id' => $source['peg_id'],
                    'status_inpassing' => $source['status_inpassing'],
                    'status_sertifikasi' => $source['status_sertifikasi'],
                    'updated_at' => now(),
                ];
                foreach (['nuptk', 'nrg', 'npk'] as $field) {
                    if ($source[$field] !== null) {
                        $updates[$field] = $source[$field];
                    }
                }
                DB::table('gtks')->where('id', $result['gtk']->id)->update($updates);
            }
        });

        $this->newLine();
        $this->info('Berhasil memperbarui '.$matched->count().' GTK dalam satu transaksi.');
        $this->line('Backup sebelum perubahan: '.$backup);

        return self::SUCCESS;
    }

    private function resolveFile(string $path): string
    {
        $resolved = realpath($path) ?: realpath(base_path($path));
        if (! $resolved || ! is_file($resolved)) {
            throw new RuntimeException('Workbook tidak ditemukan: '.$path);
        }

        return $resolved;
    }

    private function readWorkbook(string $file): Collection
    {
        $sheet = IOFactory::load($file)->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);
        $headers = array_map(fn ($value) => mb_strtolower(trim((string) $value)), array_shift($rows));
        $required = ['peg id', 'nama', 'nik', 'nip', 'nuptk', 'nrg', 'npk', 'status inpassing', 'tanggal lahir', 'status sertifikasi'];
        foreach ($required as $header) {
            if (! in_array($header, $headers, true)) {
                throw new RuntimeException('Kolom wajib tidak ditemukan: '.$header);
            }
        }
        $index = array_flip($headers);

        return collect($rows)->filter(fn ($row) => trim((string) ($row[$index['nama']] ?? '')) !== '')
            ->map(fn ($row, $offset) => [
                'excel_row' => $offset + 2,
                'peg_id' => $this->cleanIdentifier($row[$index['peg id']] ?? null),
                'nama' => trim((string) ($row[$index['nama']] ?? '')),
                'nik' => $this->cleanIdentifier($row[$index['nik']] ?? null),
                'nip' => $this->cleanIdentifier($row[$index['nip']] ?? null),
                'nuptk' => $this->cleanIdentifier($row[$index['nuptk']] ?? null),
                'nrg' => $this->cleanIdentifier($row[$index['nrg']] ?? null),
                'npk' => $this->cleanIdentifier($row[$index['npk']] ?? null),
                'status_inpassing' => $this->cleanValue($row[$index['status inpassing']] ?? null),
                'tanggal_lahir' => $this->parseDate($row[$index['tanggal lahir']] ?? null),
                'status_sertifikasi' => $this->cleanValue($row[$index['status sertifikasi']] ?? null),
            ])->values();
    }

    private function cleanIdentifier(mixed $value): ?string
    {
        $value = preg_replace('/[^0-9A-Za-z]+/', '', trim((string) $value));

        return $value === '' ? null : $value;
    }

    private function cleanValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === '-' ? null : $value;
    }

    private function parseDate(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function writeReport(Collection $results): string
    {
        $path = $this->option('report') ?: storage_path('app/ptk-sync/report-'.now()->format('Ymd-His').'.csv');
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $handle = fopen($path, 'wb');
        fputcsv($handle, ['Baris', 'Nama Excel', 'Status', 'GTK SIMANSA', 'Metode', 'Skor Nama', 'PEG ID', 'NIK', 'NIP', 'Catatan']);
        foreach ($results as $result) {
            fputcsv($handle, [
                $result['source']['excel_row'], $result['source']['nama'], $result['status'],
                $result['gtk']?->nama_lengkap, $result['method'], $result['score'],
                $result['source']['peg_id'], $result['source']['nik'], $result['source']['nip'], $result['note'],
            ]);
        }
        fclose($handle);

        return $path;
    }

    private function writeBackup(Collection $matched): string
    {
        $directory = storage_path('app/ptk-sync');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.'/backup-before-'.now()->format('Ymd-His').'.json';
        $ids = $matched->pluck('gtk.id');
        $rows = DB::table('gtks')->whereIn('id', $ids)->get();
        file_put_contents($path, $rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    private function displaySummary(Collection $sources, Collection $gtks, Collection $results, string $report): void
    {
        $this->table(['Metrik', 'Jumlah'], [
            ['Baris workbook', $sources->count()],
            ['GTK SIMANSA', $gtks->count()],
            ['Cocok pasti', $results->where('status', 'matched')->count()],
            ['Ambigu', $results->where('status', 'ambiguous')->count()],
            ['Tidak ditemukan', $results->where('status', 'unmatched')->count()],
        ]);
        $this->line('Laporan detail: '.$report);
        foreach ($results->whereIn('status', ['ambiguous', 'unmatched']) as $result) {
            $this->warn("Baris {$result['source']['excel_row']}: {$result['source']['nama']} → ".($result['gtk']?->nama_lengkap ?: '-')." ({$result['note']})");
        }
    }
}
