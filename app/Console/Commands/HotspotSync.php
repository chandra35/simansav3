<?php

namespace App\Console\Commands;

use App\Models\HotspotUser;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotspotSync extends Command
{
    protected $signature = 'hotspot:sync
        {--role= : Sync hanya role tertentu: guru, siswa, tamu}
        {--force : Paksa re-sync semua, termasuk yang sudah synced}
        {--dry-run : Tampilkan tanpa eksekusi}';

    protected $description = 'Sync akun hotspot Simansa ke database FreeRADIUS';

    private int $created = 0;
    private int $updated = 0;
    private int $deactivated = 0;
    private int $errors = 0;

    public function handle(): int
    {
        $role = $this->option('role');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║   Hotspot Sync → FreeRADIUS          ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada perubahan yang akan disimpan.');
        }

        // Test koneksi RADIUS
        try {
            DB::connection('mysql_radius')->getPdo();
            $this->info('✓ Koneksi ke RADIUS database OK');
        } catch (\Exception $e) {
            $this->error('✗ Gagal koneksi ke RADIUS database: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$role || $role === 'guru') {
            $this->syncGuru($force, $dryRun);
        }

        if (!$role || $role === 'siswa') {
            $this->syncSiswa($force, $dryRun);
        }

        // Deactivate user yang sudah tidak aktif
        $this->deactivateInactive($dryRun);
        $this->deactivateExpiredGuests($dryRun);

        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info("║  Created : {$this->created}");
        $this->info("║  Updated : {$this->updated}");
        $this->info("║  Deactivated: {$this->deactivated}");
        $this->info("║  Errors  : {$this->errors}");
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');

        Log::info('[HotspotSync] Sync selesai', [
            'created' => $this->created,
            'updated' => $this->updated,
            'deactivated' => $this->deactivated,
            'errors' => $this->errors,
        ]);

        return self::SUCCESS;
    }

    private function syncGuru(bool $force, bool $dryRun): void
    {
        $this->info('');
        $this->info('→ Sync Guru/GTK...');

        $gtks = Gtk::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereNotNull('nik')
            ->where('nik', '!=', '')
            ->get();

        $bar = $this->output->createProgressBar($gtks->count());
        $bar->start();

        foreach ($gtks as $gtk) {
            $this->processUser(
                user: $gtk->user,
                username: $gtk->nik,
                role: 'guru',
                displayName: $gtk->nama_lengkap,
                force: $force,
                dryRun: $dryRun,
            );
            $bar->advance();
        }

        $bar->finish();
        $this->info('');
        $this->info("  ✓ {$gtks->count()} GTK diproses.");
    }

    private function syncSiswa(bool $force, bool $dryRun): void
    {
        $this->info('');
        $this->info('→ Sync Siswa aktif...');

        $siswas = Siswa::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereNotNull('nisn')
            ->where('nisn', '!=', '')
            ->where('status_siswa', 'aktif')
            ->get();

        $bar = $this->output->createProgressBar($siswas->count());
        $bar->start();

        foreach ($siswas as $siswa) {
            $this->processUser(
                user: $siswa->user,
                username: $siswa->nisn,
                role: 'siswa',
                displayName: $siswa->nama_lengkap,
                force: $force,
                dryRun: $dryRun,
            );
            $bar->advance();
        }

        $bar->finish();
        $this->info('');
        $this->info("  ✓ {$siswas->count()} Siswa diproses.");
    }

    private function processUser(
        ?User $user,
        string $username,
        string $role,
        string $displayName,
        bool $force,
        bool $dryRun,
    ): void {
        if (!$user) {
            return;
        }

        $plainPassword = $this->getPlainPassword($user, $username);

        $existing = HotspotUser::where('username', $username)->first();
        $isNew = $existing === null;

        if ($dryRun) {
            $action = $plainPassword === null ? 'REJECT-INSECURE' : ($isNew ? 'CREATE' : ($force ? 'UPDATE' : 'SKIP'));
            $this->line("  [{$action}] {$username} ({$role}) - {$displayName}");
            return;
        }

        if ($plainPassword === null) {
            $hotspot = $existing ?: HotspotUser::create([
                'user_id' => $user->id,
                'username' => $username,
                'role' => $role,
                'display_name' => $displayName,
                'is_active' => false,
                'sync_status' => 'pending',
            ]);
            $hotspot->rejectFromRadius('Password hotspot tidak aman atau tidak tersedia. Reset password akun SIMANSA untuk mengaktifkan kembali.');
            $this->deactivated++;
            return;
        }

        if ($isNew) {
            try {
                $hotspot = HotspotUser::create([
                    'user_id' => $user->id,
                    'username' => $username,
                    'role' => $role,
                    'display_name' => $displayName,
                    'is_active' => true,
                    'sync_status' => 'pending',
                ]);
                $hotspot->syncToRadius($plainPassword);
                $this->created++;
            } catch (\Exception $e) {
                $this->errors++;
                Log::error("[HotspotSync] Error creating {$username}: " . $e->getMessage());
            }
        } elseif ($force || $existing->sync_status !== 'synced') {
            try {
                $existing->update([
                    'user_id' => $user->id,
                    'role' => $role,
                    'display_name' => $displayName,
                    'is_active' => true,
                    'sync_status' => 'pending',
                ]);
                $existing->syncToRadius($plainPassword);
                $this->updated++;
            } catch (\Exception $e) {
                $this->errors++;
                Log::error("[HotspotSync] Error updating {$username}: " . $e->getMessage());
            }
        }
    }

    private function deactivateInactive(bool $dryRun): void
    {
        // Nonaktifkan siswa yang tidak lagi memenuhi syarat akses hotspot.
        // Syarat siswa: user aktif, data siswa ada, status_siswa = aktif, dan NISN terisi.
        $inactiveSiswa = HotspotUser::with('user.siswa')
            ->where('role', 'siswa')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereDoesntHave('user', function ($user) {
                    $user->where('is_active', true)
                        ->whereHas('siswa', function ($siswa) {
                            $siswa->where('status_siswa', 'aktif')
                                ->whereNotNull('nisn')
                                ->where('nisn', '!=', '');
                        });
                });
            })
            ->get();

        foreach ($inactiveSiswa as $hotspot) {
            if ($dryRun) {
                $status = $hotspot->user?->siswa?->status_siswa ?: 'tanpa data siswa/user';
                $this->line("  [DEACTIVATE] {$hotspot->username} ({$status})");
                continue;
            }
            $status = $hotspot->user?->siswa?->status_siswa ?: 'tanpa data siswa/user';
            $hotspot->rejectFromRadius("Siswa tidak aktif untuk hotspot: {$status}");
            $this->deactivated++;
        }
    }

    private function deactivateExpiredGuests(bool $dryRun): void
    {
        $expiredGuests = HotspotUser::query()
            ->where('role', 'tamu')
            ->where('is_active', true)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($expiredGuests as $hotspot) {
            if ($dryRun) {
                $this->line("  [DEACTIVATE] {$hotspot->username} (tamu expired)");
                continue;
            }

            $hotspot->rejectFromRadius('Masa berlaku akun tamu telah berakhir.');
            $this->deactivated++;
        }
    }

    private function getPlainPassword(User $user, string $username): ?string
    {
        if (!empty($user->encrypted_password)) {
            try {
                $password = Crypt::decryptString($user->encrypted_password);

                return mb_strlen($password) >= 8 && !hash_equals($username, $password)
                    ? $password
                    : null;
            } catch (\Exception) {
                // Fallback jika decrypt gagal
            }
        }

        return null;
    }
}
