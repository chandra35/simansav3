<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Define gates for permissions
        Gate::define('admin-access', function ($user) {
            return in_array($user->role, ['super_admin', 'admin', 'gtk', 'operator']);
        });

        Gate::define('super-admin-access', function ($user) {
            return $user->role === 'super_admin';
        });

        Gate::define('siswa-access', function ($user) {
            return $user->role === 'siswa';
        });
    }
}
