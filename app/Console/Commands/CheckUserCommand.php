<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserCommand extends Command
{
    protected $signature = 'check:user {username}';
    protected $description = 'Check user roles and relations';

    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User dengan username '{$username}' tidak ditemukan!");
            return 1;
        }

        $this->info("=== USER DATA ===");
        $this->line("Name: {$user->name}");
        $this->line("Username: {$user->username}");
        $this->line("Email: {$user->email}");
        $this->line("");

        $this->info("=== ROLES (Spatie) ===");
        if ($user->roles->count() > 0) {
            foreach ($user->roles as $role) {
                $this->line("- {$role->name}");
            }
        } else {
            $this->warn("Tidak ada role!");
        }
        $this->line("");

        $this->info("=== RELATIONS ===");
        $this->line("Has Siswa: " . ($user->siswa ? 'YES (ID: ' . $user->siswa->id . ')' : 'NO'));
        $this->line("Has GTK: " . ($user->gtk ? 'YES (ID: ' . $user->gtk->id . ')' : 'NO'));

        return 0;
    }
}
