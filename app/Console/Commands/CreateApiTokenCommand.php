<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simansa:token 
                            {name=openclaw-bot : Name of the token/client} 
                            {--username= : Username of the user to attach token (defaults to first super-admin/admin)} 
                            {--abilities=* : Sanctum abilities/scopes (default: *)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a personal access token for SIMANSA API clients (e.g., OpenClaw Bot)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $username = $this->option('username');
        $abilities = $this->option('abilities') ?: ['*'];

        $user = null;
        if ($username) {
            $user = User::where('username', $username)->orWhere('email', $username)->first();
        } else {
            // Pick an active admin/super-admin or first user
            $user = User::where('is_active', true)
                ->where(function ($query) {
                    $query->where('role', 'super_admin')
                          ->orWhere('role', 'admin')
                          ->orWhereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']));
                })
                ->first() ?? User::where('is_active', true)->first();
        }

        if (!$user) {
            $this->error('No valid user found to issue token.');
            return self::FAILURE;
        }

        $token = $user->createToken($name, $abilities);

        $this->info("==================================================");
        $this->info(" SIMANSA API Personal Access Token Generated");
        $this->info("==================================================");
        $this->line("User     : <comment>{$user->name} ({$user->username})</comment>");
        $this->line("Token Name: <comment>{$name}</comment>");
        $this->line("Abilities: <comment>" . implode(', ', $abilities) . "</comment>");
        $this->newLine();
        $this->line("Bearer Token (Copy this into OpenClaw config):");
        $this->info($token->plainTextToken);
        $this->newLine();
        $this->comment("Header: Authorization: Bearer {$token->plainTextToken}");
        $this->info("==================================================");

        return self::SUCCESS;
    }
}
