<?php

namespace App\Console\Commands;

use App\Models\HotspotUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class HotspotSyncSimansaPasswords extends Command
{
    protected $signature = 'hotspot:sync-simansa-passwords
        {--username= : Sinkronkan satu username saja}
        {--dry-run : Hitung tanpa menulis ke FreeRADIUS}';

    protected $description = 'Samakan password Hotspot dengan password SIMANSA yang tersedia terenkripsi';

    public function handle(): int
    {
        $query = HotspotUser::query()
            ->with(['user.siswa'])
            ->whereIn('role', ['guru', 'siswa'])
            ->whereHas('user', fn ($user) => $user
                ->whereNotNull('encrypted_password')
                ->where('encrypted_password', '!=', ''));

        if ($username = $this->option('username')) {
            $query->where('username', $username);
        }

        if ($this->option('dry-run')) {
            $this->info($query->count().' akun memiliki password SIMANSA yang dapat disinkronkan.');

            return self::SUCCESS;
        }

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById(200, function ($hotspots) use (&$synced, &$skipped, &$failed) {
            foreach ($hotspots as $hotspot) {
                try {
                    $password = Crypt::decryptString($hotspot->user->encrypted_password);
                } catch (\Throwable) {
                    $failed++;
                    continue;
                }

                if (!$hotspot->isEligibleForRadius() || !$hotspot->isSecurePassword($password)) {
                    $skipped++;
                    continue;
                }

                $hotspot->forceFill([
                    'is_active' => true,
                    'sync_status' => 'pending',
                    'sync_error' => null,
                ])->save();

                $hotspot->syncToRadius($password) ? $synced++ : $failed++;
            }
        });

        $this->info("{$synced} password tersinkron, {$skipped} dilewati, {$failed} gagal.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
