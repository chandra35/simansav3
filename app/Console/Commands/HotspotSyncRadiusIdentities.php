<?php

namespace App\Console\Commands;

use App\Models\HotspotUser;
use Illuminate\Console\Command;

class HotspotSyncRadiusIdentities extends Command
{
    protected $signature = 'hotspot:sync-radius-identities
        {--username= : Sinkronkan satu username saja}
        {--dry-run : Hitung data tanpa menulis ke FreeRADIUS}';

    protected $description = 'Sinkronkan nama akun Hotspot SIMANSA ke FreeRADIUS tanpa mengubah password';

    public function handle(): int
    {
        $query = HotspotUser::query()
            ->whereNotNull('username')
            ->where('username', '!=', '');

        if ($username = $this->option('username')) {
            $query->where('username', $username);
        }

        if ($this->option('dry-run')) {
            $this->info($query->count().' identitas siap disinkronkan.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById(250, function ($users) use (&$synced, &$failed) {
            foreach ($users as $user) {
                $user->syncIdentityToRadius() ? $synced++ : $failed++;
            }
        });

        $this->info("{$synced} identitas tersinkron, {$failed} gagal.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
