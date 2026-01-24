<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixGtkRoleColumnCommand extends Command
{
    protected $signature = 'fix:gtk-role-column';
    protected $description = 'Update old role column for GTK users';

    public function handle()
    {
        $this->info("Updating old 'role' column for GTK users...");

        // Find users with GTK role (Spatie)
        $gtkUsers = User::role(['GTK', 'Wali Kelas'])->get();

        $fixed = 0;

        foreach ($gtkUsers as $user) {
            // Update old role column to 'gtk'
            if ($user->role !== 'gtk') {
                $oldRole = $user->role;
                $user->update(['role' => 'gtk']);
                $fixed++;
                $this->line("✓ {$user->name} ({$user->username}): '{$oldRole}' → 'gtk'");
            }
        }

        $this->line("");
        $this->info("=== SUMMARY ===");
        $this->line("Checked: {$gtkUsers->count()} GTK users");
        $this->line("Updated: {$fixed} users");
        
        if ($fixed > 0) {
            $this->info("✓ Done! Updated {$fixed} user(s)");
        } else {
            $this->info("✓ All GTK users already have correct role column!");
        }

        return 0;
    }
}
