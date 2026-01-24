<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class CleanupGuruRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:guru-role {--force : Force delete without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old "Guru" role and replace with "GTK" role system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   Cleanup Old "Guru" Role');
        $this->info('========================================');
        $this->newLine();

        // Step 1: Check if Guru role exists
        $guruRole = Role::where('name', 'Guru')->first();
        
        if (!$guruRole) {
            $this->info('✓ Role "Guru" not found. Nothing to clean up.');
            return Command::SUCCESS;
        }

        $this->line("Found role: <fg=yellow>{$guruRole->name}</> (ID: {$guruRole->id})");
        $this->newLine();

        // Step 2: Check for users with Guru role
        $usersWithGuruRole = User::role('Guru')->get();
        $userCount = $usersWithGuruRole->count();

        $this->info("Checking users with 'Guru' role...");
        $this->line("Users found: <fg=cyan>{$userCount}</>");
        $this->newLine();

        if ($userCount > 0) {
            $this->warn("⚠ WARNING: There are still {$userCount} users with 'Guru' role!");
            $this->warn("Please migrate them to 'GTK' role first using: php artisan migrate:guru-to-gtk");
            $this->newLine();
            
            $this->table(
                ['ID', 'Name', 'Username', 'Email'],
                $usersWithGuruRole->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->username,
                        $user->email,
                    ];
                })
            );
            
            return Command::FAILURE;
        }

        $this->info('✓ No users found with "Guru" role. Safe to proceed.');
        $this->newLine();

        // Step 3: Check permissions assigned to Guru role
        $permissions = $guruRole->permissions;
        $permissionCount = $permissions->count();
        
        if ($permissionCount > 0) {
            $this->info("Permissions assigned to 'Guru' role: <fg=cyan>{$permissionCount}</>");
            foreach ($permissions as $permission) {
                $this->line("  - {$permission->name}");
            }
            $this->newLine();
        }

        // Step 4: Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to delete the "Guru" role?', true)) {
                $this->warn('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Step 5: Perform deletion
        DB::beginTransaction();
        try {
            $this->info('Deleting "Guru" role...');
            
            // Detach all permissions first
            if ($permissionCount > 0) {
                $guruRole->permissions()->detach();
                $this->line('  ✓ Detached all permissions');
            }
            
            // Delete the role
            $guruRole->delete();
            $this->line('  ✓ Role deleted successfully');
            
            DB::commit();
            
            $this->newLine();
            $this->info('========================================');
            $this->info('✓ Cleanup completed successfully!');
            $this->info('========================================');
            $this->newLine();
            
            $this->line('Next steps:');
            $this->line('1. Update seeders to remove "Guru" role creation');
            $this->line('2. Verify application still works correctly');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete role: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
