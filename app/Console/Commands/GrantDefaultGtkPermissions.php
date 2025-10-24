<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class GrantDefaultGtkPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gtk:grant-default-permissions 
                            {--force : Force grant permissions without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant default permissions to existing GTK users (view-siswa, view-kelas, view-detail-kelas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Finding GTK users...');

        // Get all users with GTK role
        $gtkUsers = User::role('GTK')->get();

        if ($gtkUsers->isEmpty()) {
            $this->warn('⚠️  No GTK users found.');
            return 0;
        }

        $this->info("Found {$gtkUsers->count()} GTK users.");

        // Define default permissions for GTK
        $defaultPermissions = [
            'view-siswa',
            'view-kelas',
            'view-detail-kelas',
        ];

        // Check if permissions exist
        $this->info('🔍 Checking permissions...');
        foreach ($defaultPermissions as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if (!$perm) {
                $this->error("❌ Permission '{$permName}' not found in database!");
                return 1;
            }
        }

        // Show what will be done
        $this->newLine();
        $this->info('📋 The following permissions will be granted to GTK users:');
        foreach ($defaultPermissions as $permName) {
            $this->line("   - {$permName}");
        }
        $this->newLine();

        // Confirm unless --force
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed?', true)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Grant permissions
        $this->info('🚀 Granting permissions...');
        $progressBar = $this->output->createProgressBar($gtkUsers->count());
        $progressBar->start();

        $granted = 0;
        $skipped = 0;

        foreach ($gtkUsers as $user) {
            $newPermissions = [];
            
            foreach ($defaultPermissions as $permName) {
                // Check if user already has this permission (via role or direct)
                if (!$user->hasPermissionTo($permName)) {
                    $newPermissions[] = $permName;
                }
            }

            if (!empty($newPermissions)) {
                // Give permissions directly to user
                $user->givePermissionTo($newPermissions);
                $granted++;
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Operation completed!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Users processed', $gtkUsers->count()],
                ['Permissions granted', $granted],
                ['Skipped (already had permissions)', $skipped],
            ]
        );

        $this->newLine();
        $this->info('💡 TIP: Super Admin can now manage individual GTK permissions via User Management.');
        $this->info('   Navigate to: Admin > Users > Assign Role & Permission');

        return 0;
    }
}
