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
            return in_array($user->role, ['super_admin', 'admin', 'operator']);
        });

        Gate::define('super-admin-access', function ($user) {
            return $user->role === 'super_admin';
        });

        Gate::define('siswa-access', function ($user) {
            return $user->role === 'siswa';
        });

        // Gate for GTK-specific menus (Dashboard Saya, Profil Saya)
        // Only show to users with GTK role, excluding Super Admin and Admin
        Gate::define('gtk-menu-only', function ($user) {
            return $user->hasRole('GTK') && 
                   !$user->hasRole('Super Admin') && 
                   !$user->hasRole('Admin');
        });

        // Gate for Admin Dashboard
        // Show to Super Admin, Admin, but NOT to pure GTK users
        Gate::define('admin-dashboard-access', function ($user) {
            return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        });
    }
}
