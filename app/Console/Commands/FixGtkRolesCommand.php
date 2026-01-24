<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Gtk;

class FixGtkRolesCommand extends Command
{
    protected $signature = 'fix:gtk-roles';
    protected $description = 'Fix GTK users who have both Guru and Siswa roles';

    public function handle()
    {
        $this->info("Mencari user GTK yang punya role Siswa...");

        // Find all GTK users
        $gtkUsers = User::whereHas('gtk')->with('roles', 'siswa', 'gtk')->get();

        $fixed = 0;
        $checked = 0;

        foreach ($gtkUsers as $user) {
            $checked++;
            
            // Check if has both GTK and Siswa role
            $hasGtk = $user->hasRole('GTK');
            $hasSiswa = $user->hasRole('Siswa');
            
            if ($hasGtk && $hasSiswa) {
                $this->warn("FOUND: {$user->name} ({$user->username}) - Has both GTK & Siswa role!");
                
                // Remove Siswa role
                $user->removeRole('Siswa');
                $fixed++;
                
                $this->line("  ✓ Removed 'Siswa' role from {$user->name}");
                
                // Delete siswa record if exists
                if ($user->siswa) {
                    $siswaId = $user->siswa->id;
                    $user->siswa->delete();
                    $this->line("  ✓ Deleted siswa record (ID: {$siswaId})");
                }
            }
        }

        $this->line("");
        $this->info("=== SUMMARY ===");
        $this->line("Checked: {$checked} GTK users");
        $this->line("Fixed: {$fixed} users");
        
        if ($fixed > 0) {
            $this->info("✓ Done! Fixed {$fixed} user(s)");
        } else {
            $this->info("✓ No issues found!");
        }

        return 0;
    }
}
