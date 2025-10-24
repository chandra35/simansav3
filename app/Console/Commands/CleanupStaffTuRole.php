<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CleanupStaffTuRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:staff-tu-role {--force : Force delete without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old "Staff TU" role (replaced by GTK role system)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   Cleanup Old "Staff TU" Role');
        $this->info('========================================');
        $this->newLine();

        // Step 1: Check if Staff TU role exists
        $staffTuRole = Role::where('name', 'Staff TU')->first();
        
        if (!$staffTuRole) {
            $this->info('✓ Role "Staff TU" not found. Nothing to clean up.');
            return Command::SUCCESS;
        }

        $this->line("Found role: <fg=yellow>{$staffTuRole->name}</> (ID: {$staffTuRole->id})");
        $this->newLine();

        // Step 2: Check for users with Staff TU role
        $usersWithStaffTuRole = User::role('Staff TU')->get();
        $userCount = $usersWithStaffTuRole->count();

        $this->info("Checking users with 'Staff TU' role...");
        $this->line("Users found: <fg=cyan>{$userCount}</>");
        $this->newLine();

        if ($userCount > 0) {
            $this->warn("⚠ WARNING: There are still {$userCount} users with 'Staff TU' role!");
            $this->warn("Please migrate them to 'GTK' role first using: php artisan migrate:guru-to-gtk");
            $this->newLine();
            
            $this->table(
                ['ID', 'Name', 'Username', 'Email'],
                $usersWithStaffTuRole->map(function ($user) {
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

        $this->info('✓ No users found with "Staff TU" role. Safe to proceed.');
        $this->newLine();

        // Step 3: Check permissions assigned to Staff TU role
        $permissions = $staffTuRole->permissions;
        $permissionCount = $permissions->count();
        
        if ($permissionCount > 0) {
            $this->info("Permissions assigned to 'Staff TU' role: <fg=cyan>{$permissionCount}</>");
            foreach ($permissions as $permission) {
                $this->line("  - {$permission->name}");
            }
            $this->newLine();
        }

        // Step 4: Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to delete the "Staff TU" role?', true)) {
                $this->warn('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Step 5: Perform deletion
        DB::beginTransaction();
        try {
            $this->info('Deleting "Staff TU" role...');
            
            // Detach all permissions first
            if ($permissionCount > 0) {
                $staffTuRole->permissions()->detach();
                $this->line('  ✓ Detached all permissions');
            }
            
            // Delete the role
            $staffTuRole->delete();
            $this->line('  ✓ Role deleted successfully');
            
            DB::commit();
            
            $this->newLine();
            $this->info('========================================');
            $this->info('✓ Cleanup completed successfully!');
            $this->info('========================================');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete role: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
