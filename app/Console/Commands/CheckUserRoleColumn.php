<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRoleColumn extends Command
{
    protected $signature = 'check:user-role {username}';
    protected $description = 'Check user role column value';

    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User tidak ditemukan!");
            return 1;
        }

        $this->info("=== USER ROLE DATA ===");
        $this->line("Name: {$user->name}");
        $this->line("Username: {$user->username}");
        $this->line("Old 'role' column: " . ($user->role ?? 'NULL'));
        $this->line("");
        
        $this->info("=== Spatie Roles ===");
        foreach ($user->roles as $role) {
            $this->line("- {$role->name}");
        }
        $this->line("");
        
        $this->info("=== Method Checks ===");
        $this->line("isSiswa(): " . ($user->isSiswa() ? 'TRUE' : 'FALSE'));
        $this->line("isAdmin(): " . ($user->isAdmin() ? 'TRUE' : 'FALSE'));
        $this->line("hasRole('Siswa'): " . ($user->hasRole('Siswa') ? 'TRUE' : 'FALSE'));
        $this->line("hasRole('GTK'): " . ($user->hasRole('GTK') ? 'TRUE' : 'FALSE'));

        return 0;
    }
}
