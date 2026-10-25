<?php

namespace App\Console\Commands;

use App\Services\TkaImportService;
use Illuminate\Console\Command;

class ImportTkaPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tka:import 
                            {file? : Path to the TKA PDF file}
                            {--tahun= : Override academic year (e.g. 2026)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import TKA login cards from a PDF file';

    /**
     * Execute the console command.
     */
    public function handle(TkaImportService $importService): int
    {
        $filePath = $this->argument('file');

        if (!$filePath) {
            $defaultPath = 'c:\\Users\\chandra35\\Downloads\\Tes Kemampuan Akademik - Tahun 2026.pdf';
            if (file_exists($defaultPath)) {
                $filePath = $defaultPath;
            } else {
                $filePath = $this->ask('Masukkan path file PDF TKA:');
            }
        }

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $tahun = $this->option('tahun');

        $this->info("Memulai parsing dan import kartu TKA dari: {$filePath}");
        $this->info("Mohon tunggu sebentar...");

        try {
            $result = $importService->importPdf($filePath, $tahun);

            $this->newLine();
            $this->info("=========================================");
            $this->info("   HASIL IMPORT KARTU LOGIN TKA         ");
            $this->info("=========================================");
            $this->line(" Tahun           : <fg=yellow>{$result['tahun']}</>");
            $this->line(" Total Kartu     : <fg=cyan>{$result['total_cards']}</>");
            $this->line(" Data Baru       : <fg=green>{$result['new_imported']}</>");
            $this->line(" Diperbarui      : <fg=blue>{$result['updated']}</>");
            $this->line(" Siswa Terhubung : <fg=green>{$result['matched_siswa']}</>");
            $this->line(" Siswa Belum Ada : <fg=red>{$result['unmatched_siswa']}</>");
            $this->info("=========================================");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal import TKA: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
