<?php

namespace App\Console\Commands;

use App\Helpers\StorageHelper;
use App\Models\DokumenSiswa;
use App\Models\Gtk;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class NormalizeStudentMediaCommand extends Command
{
    protected $signature = 'media:normalize-siswa {--dry-run : Periksa tanpa menyalin file}';

    protected $description = 'Salin foto dan dokumen staging/legacy ke struktur storage siswa SIMANSA tanpa menghapus sumber lama';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $photos = $this->normalizePhotos($dryRun);
        $gtkPhotos = $this->normalizeGtkPhotos($dryRun);
        $documents = $this->normalizeMatrikulasiDocuments($dryRun);

        $this->table(['Jenis', 'Dinormalisasi', 'Dilewati', 'Gagal'], [
            ['Foto siswa', $photos['migrated'], $photos['skipped'], $photos['failed']],
            ['Foto GTK', $gtkPhotos['migrated'], $gtkPhotos['skipped'], $gtkPhotos['failed']],
            ['Dokumen matrikulasi', $documents['migrated'], $documents['skipped'], $documents['failed']],
        ]);

        if ($dryRun) {
            $this->warn('Dry run selesai. Tidak ada file maupun path database yang diubah.');
        } else {
            $this->info('Normalisasi selesai. File sumber lama tetap dipertahankan sebagai cadangan.');
        }

        return self::SUCCESS;
    }

    private function normalizePhotos(bool $dryRun): array
    {
        $result = ['migrated' => 0, 'skipped' => 0, 'failed' => 0];

        Siswa::query()->whereNotNull('foto_profile')->cursor()->each(function (Siswa $siswa) use (&$result, $dryRun) {
            $source = StorageHelper::normalizePublicPath($siswa->foto_profile);
            if (!$source || filter_var($source, FILTER_VALIDATE_URL) || !Storage::disk('public')->exists($source)) {
                $result['failed']++;
                return;
            }

            if (str_starts_with($source, 'foto_profile/siswa/' . $siswa->id . '/')) {
                $result['skipped']++;
                return;
            }

            $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'jpg';
            $target = 'foto_profile/siswa/' . $siswa->id . '/profil-migrasi.' . $extension;

            try {
                if (!$dryRun) {
                    Storage::disk('public')->makeDirectory(dirname($target));
                    if (!Storage::disk('public')->exists($target)) {
                        Storage::disk('public')->put($target, Storage::disk('public')->get($source));
                    }
                    $siswa->forceFill(['foto_profile' => $target])->save();
                }
                $result['migrated']++;
            } catch (\Throwable $e) {
                $this->warn("Foto {$siswa->id} gagal: {$e->getMessage()}");
                $result['failed']++;
            }
        });

        return $result;
    }

    private function normalizeGtkPhotos(bool $dryRun): array
    {
        $result = ['migrated' => 0, 'skipped' => 0, 'failed' => 0];

        Gtk::query()->whereNotNull('foto_profile')->cursor()->each(function (Gtk $gtk) use (&$result, $dryRun) {
            $source = StorageHelper::normalizePublicPath($gtk->foto_profile);
            if (!$source || filter_var($source, FILTER_VALIDATE_URL) || !Storage::disk('public')->exists($source)) {
                $result['failed']++;
                return;
            }

            if (str_starts_with($source, 'foto_profile/gtk/' . $gtk->id . '/')) {
                $result['skipped']++;
                return;
            }

            $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'jpg';
            $target = 'foto_profile/gtk/' . $gtk->id . '/profil-migrasi.' . $extension;

            try {
                if (!$dryRun) {
                    Storage::disk('public')->makeDirectory(dirname($target));
                    if (!Storage::disk('public')->exists($target)) {
                        Storage::disk('public')->put($target, Storage::disk('public')->get($source));
                    }
                    $gtk->forceFill(['foto_profile' => $target])->save();
                }
                $result['migrated']++;
            } catch (\Throwable $e) {
                $this->warn("Foto GTK {$gtk->id} gagal: {$e->getMessage()}");
                $result['failed']++;
            }
        });

        return $result;
    }

    private function normalizeMatrikulasiDocuments(bool $dryRun): array
    {
        $result = ['migrated' => 0, 'skipped' => 0, 'failed' => 0];
        $targetDisk = StorageHelper::getDokumenDisk();

        DokumenSiswa::query()->with('siswa')
            ->where('file_path', 'like', 'matrikulasi-ppdb/%')
            ->cursor()
            ->each(function (DokumenSiswa $document) use (&$result, $dryRun, $targetDisk) {
                $source = StorageHelper::resolveExistingDokumenFile($document->storage_disk, $document->file_path);
                if (!$document->siswa || !$source) {
                    $result['failed']++;
                    return;
                }

                $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)) ?: 'bin';
                $folder = $document->siswa->nisn ?: $document->siswa->id;
                $target = $folder . '/ppdb/' . $document->id . '.' . $extension;

                try {
                    if (!$dryRun) {
                        Storage::disk($targetDisk)->makeDirectory(dirname($target));
                        if (!Storage::disk($targetDisk)->exists($target)) {
                            Storage::disk($targetDisk)->put($target, Storage::disk($source['disk'])->get($source['path']));
                        }
                        $document->update([
                            'file_path' => $target,
                            'file_size' => Storage::disk($targetDisk)->size($target),
                            'storage_disk' => $targetDisk,
                        ]);
                    }
                    $result['migrated']++;
                } catch (\Throwable $e) {
                    $this->warn("Dokumen {$document->id} gagal: {$e->getMessage()}");
                    $result['failed']++;
                }
            });

        return $result;
    }
}
