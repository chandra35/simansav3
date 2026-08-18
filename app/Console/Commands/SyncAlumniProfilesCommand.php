<?php

namespace App\Console\Commands;

use App\Services\AlumniDataService;
use Illuminate\Console\Command;

class SyncAlumniProfilesCommand extends Command
{
    protected $signature = 'alumni:sync-simansa';
    protected $description = 'Buat atau perbarui profil alumni dari riwayat kelulusan SIMANSA';

    public function handle(AlumniDataService $service): int
    {
        $result = $service->syncLegacyGraduates();
        $this->info("Sinkronisasi selesai: {$result['created']} baru, {$result['updated']} diperbarui.");
        return self::SUCCESS;
    }
}
