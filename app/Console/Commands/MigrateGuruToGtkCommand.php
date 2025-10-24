<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Gtk;
use App\Models\TugasTambahan;
use Spatie\Permission\Models\Role;

class MigrateGuruToGtkCommand extends Command
{
    protected $signature = 'migrate:guru-to-gtk {--dry-run : Run in dry-run mode without making changes}';
    protected $description = 'Migrate existing Guru & Staff TU roles to GTK role with proper kategori_ptk and jenis_ptk';

    private $dryRun = false;
    private $stats = [
        'gtks_updated' => 0,
        'users_migrated' => 0,
        'tugas_tambahan_created' => 0,
        'errors' => [],
    ];

    public function handle()
    {
        $this->dryRun = $this->option('dry-run');
        
        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->line('');
        }

        $this->info('🚀 Starting Guru to GTK Migration...');
        $this->line('');

        // Step 1: Update GTK records with kategori_ptk and jenis_ptk
        $this->step1_UpdateGtkRecords();

        // Step 2: Migrate users from "Guru" role to "GTK" role
        $this->step2_MigrateGuruToGtk();

        // Step 3: Migrate users from "Staff TU" role to "GTK" role
        $this->step3_MigrateStaffTuToGtk();

        // Step 4: Handle "Wali Kelas" as tugas tambahan
        $this->step4_HandleWaliKelas();

        // Summary
        $this->line('');
        $this->info('📊 MIGRATION SUMMARY:');
        $this->info("   - GTK records updated: {$this->stats['gtks_updated']}");
        $this->info("   - Users migrated to GTK role: {$this->stats['users_migrated']}");
        $this->info("   - Tugas tambahan created: {$this->stats['tugas_tambahan_created']}");
        
        if (!empty($this->stats['errors'])) {
            $this->line('');
            $this->error('⚠️  ERRORS:');
            foreach ($this->stats['errors'] as $error) {
                $this->error("   - {$error}");
            }
        }

        $this->line('');
        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN COMPLETED - No actual changes made');
            $this->info('Run without --dry-run to apply changes');
        } else {
            $this->info('✅ MIGRATION COMPLETED SUCCESSFULLY!');
        }

        return 0;
    }

    private function step1_UpdateGtkRecords()
    {
        $this->info('📝 Step 1: Updating GTK records with kategori_ptk and jenis_ptk...');

        $gtks = Gtk::whereNull('kategori_ptk')->get();
        $this->info("   Found {$gtks->count()} GTK records to update");

        foreach ($gtks as $gtk) {
            $kategori = 'Pendidik'; // Default
            $jenis = 'Guru Mapel'; // Default

            // Determine kategori and jenis based on jabatan
            if ($gtk->jabatan) {
                $jabatan = strtolower($gtk->jabatan);

                // Check for Tenaga Kependidikan keywords
                if (str_contains($jabatan, 'tu') || 
                    str_contains($jabatan, 'tata usaha') ||
                    str_contains($jabatan, 'staff') ||
                    str_contains($jabatan, 'bendahara') ||
                    str_contains($jabatan, 'laboran') ||
                    str_contains($jabatan, 'pustakawan') ||
                    str_contains($jabatan, 'cleaning') ||
                    str_contains($jabatan, 'satpam')) {
                    
                    $kategori = 'Tenaga Kependidikan';

                    // Determine specific jenis
                    if (str_contains($jabatan, 'kepala tu')) {
                        $jenis = 'Kepala TU';
                    } elseif (str_contains($jabatan, 'bendahara')) {
                        $jenis = 'Bendahara';
                    } elseif (str_contains($jabatan, 'laboran')) {
                        $jenis = 'Laboran';
                    } elseif (str_contains($jabatan, 'pustakawan')) {
                        $jenis = 'Pustakawan';
                    } elseif (str_contains($jabatan, 'cleaning')) {
                        $jenis = 'Cleaning Service';
                    } elseif (str_contains($jabatan, 'satpam') || str_contains($jabatan, 'keamanan')) {
                        $jenis = 'Satpam';
                    } elseif (str_contains($jabatan, 'tu') || str_contains($jabatan, 'tata usaha')) {
                        $jenis = 'Staff TU';
                    } else {
                        $jenis = 'Lainnya';
                    }
                } else {
                    // Pendidik (Guru)
                    $kategori = 'Pendidik';

                    if (str_contains($jabatan, 'bk') || str_contains($jabatan, 'bimbingan')) {
                        $jenis = 'Guru BK';
                    } else {
                        $jenis = 'Guru Mapel';
                    }
                }
            }

            if (!$this->dryRun) {
                $gtk->update([
                    'kategori_ptk' => $kategori,
                    'jenis_ptk' => $jenis,
                ]);
            }

            $this->stats['gtks_updated']++;
            $this->line("   ✓ {$gtk->nama_lengkap}: {$kategori} - {$jenis}");
        }

        $this->info("   ✅ Updated {$this->stats['gtks_updated']} GTK records");
        $this->line('');
    }

    private function step2_MigrateGuruToGtk()
    {
        $this->info('👨‍🏫 Step 2: Migrating users from "Guru" role to "GTK" role...');

        $guruRole = Role::where('name', 'Guru')->first();
        $gtkRole = Role::where('name', 'GTK')->first();

        if (!$guruRole || !$gtkRole) {
            $this->error('   ❌ Guru or GTK role not found!');
            $this->stats['errors'][] = 'Guru or GTK role not found';
            return;
        }

        $usersWithGuru = User::role('Guru')->get();
        $this->info("   Found {$usersWithGuru->count()} users with 'Guru' role");

        foreach ($usersWithGuru as $user) {
            try {
                if (!$this->dryRun) {
                    // Remove "Guru" role
                    $user->removeRole('Guru');
                    
                    // Add "GTK" role (if not already has it)
                    if (!$user->hasRole('GTK')) {
                        $user->assignRole('GTK');
                    }
                }

                $this->stats['users_migrated']++;
                $this->line("   ✓ {$user->username} ({$user->name}): Guru → GTK");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to migrate {$user->username}: {$e->getMessage()}");
                $this->stats['errors'][] = "Failed to migrate {$user->username}: {$e->getMessage()}";
            }
        }

        $this->info("   ✅ Migrated {$this->stats['users_migrated']} users from Guru to GTK");
        $this->line('');
    }

    private function step3_MigrateStaffTuToGtk()
    {
        $this->info('👔 Step 3: Migrating users from "Staff TU" role to "GTK" role...');

        $staffTuRole = Role::where('name', 'Staff TU')->first();
        $gtkRole = Role::where('name', 'GTK')->first();

        if (!$staffTuRole || !$gtkRole) {
            $this->error('   ❌ Staff TU or GTK role not found!');
            $this->stats['errors'][] = 'Staff TU or GTK role not found';
            return;
        }

        $usersWithStaffTu = User::role('Staff TU')->get();
        $this->info("   Found {$usersWithStaffTu->count()} users with 'Staff TU' role");

        $migrated = 0;
        foreach ($usersWithStaffTu as $user) {
            try {
                if (!$this->dryRun) {
                    // Remove "Staff TU" role
                    $user->removeRole('Staff TU');
                    
                    // Add "GTK" role (if not already has it)
                    if (!$user->hasRole('GTK')) {
                        $user->assignRole('GTK');
                    }
                }

                $migrated++;
                $this->line("   ✓ {$user->username} ({$user->name}): Staff TU → GTK");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to migrate {$user->username}: {$e->getMessage()}");
                $this->stats['errors'][] = "Failed to migrate {$user->username}: {$e->getMessage()}";
            }
        }

        $this->stats['users_migrated'] += $migrated;
        $this->info("   ✅ Migrated {$migrated} users from Staff TU to GTK");
        $this->line('');
    }

    private function step4_HandleWaliKelas()
    {
        $this->info('📚 Step 4: Handling "Wali Kelas" as tugas tambahan...');

        $waliKelasRole = Role::where('name', 'Wali Kelas')->first();

        if (!$waliKelasRole) {
            $this->error('   ❌ Wali Kelas role not found!');
            $this->stats['errors'][] = 'Wali Kelas role not found';
            return;
        }

        $usersWithWaliKelas = User::role('Wali Kelas')->get();
        $this->info("   Found {$usersWithWaliKelas->count()} users with 'Wali Kelas' role");

        foreach ($usersWithWaliKelas as $user) {
            try {
                // Check if tugas tambahan already exists
                $exists = TugasTambahan::where('user_id', $user->id)
                    ->where('role_id', $waliKelasRole->id)
                    ->where('is_active', true)
                    ->exists();

                if ($exists) {
                    $this->line("   ⊙ {$user->username} already has active Wali Kelas tugas tambahan");
                    continue;
                }

                if (!$this->dryRun) {
                    // Create tugas tambahan record
                    TugasTambahan::create([
                        'user_id' => $user->id,
                        'role_id' => $waliKelasRole->id,
                        'is_active' => true,
                        'mulai_tugas' => now(),
                        'keterangan' => 'Migrated from existing Wali Kelas role',
                    ]);

                    // Keep the role (don't remove it yet, will be handled in Phase 4)
                }

                $this->stats['tugas_tambahan_created']++;
                $this->line("   ✓ {$user->username} ({$user->name}): Created Wali Kelas tugas tambahan");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create tugas tambahan for {$user->username}: {$e->getMessage()}");
                $this->stats['errors'][] = "Failed to create tugas tambahan for {$user->username}: {$e->getMessage()}";
            }
        }

        $this->info("   ✅ Created {$this->stats['tugas_tambahan_created']} tugas tambahan records");
        $this->line('');
    }
}
