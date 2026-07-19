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
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator']) ||
                in_array($user->role, ['super_admin', 'admin', 'operator']);
        });

        Gate::define('admin-menu-only', function ($user) {
            return (
                $user->hasAnyRole(['Super Admin', 'Admin', 'Operator']) ||
                in_array($user->role, ['super_admin', 'admin', 'operator'])
            ) && !$user->hasRole('Siswa') && !$user->siswa()->exists();
        });

        Gate::define('super-admin-access', function ($user) {
            return $user->hasRole('Super Admin') || $user->role === 'super_admin';
        });

        Gate::define('siswa-access', function ($user) {
            // Staff/admin roles jangan tampil meski punya relasi siswa
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            return $user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists();
        });

        Gate::define('siswa-smartq-access', function ($user) {
            // Admin/staff roles jangan tampil meski punya data siswa
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            if (!$user->siswa) return false;
            return \App\Models\SmartqPeserta::where('siswa_id', $user->siswa->id)
                ->whereIn('status', ['lulus', 'cadangan'])
                ->exists();
        });

        // Sidebar gates — pakai nama berbeda agar tidak dioverride Spatie Gate::before
        // (Spatie Gate::before return true jika user punya Spatie permission dengan nama sama)
        // JANGAN gunakan 'siswa-access' atau 'siswa-menu-only' sebagai gate di menu config
        // karena nama itu juga ada sebagai Spatie permission → Gate::before intercept duluan
        Gate::define('sidebar-siswa-access', function ($user) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            return $user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists();
        });

        Gate::define('sidebar-siswa-menu-only', function ($user) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            return $user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists();
        });

        Gate::define('sidebar-siswa-smartq', function ($user) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            if (!$user->siswa) return false;
            return \App\Models\SmartqPeserta::where('siswa_id', $user->siswa->id)
                ->whereIn('status', ['lulus', 'cadangan'])
                ->exists();
        });

        Gate::define('siswa-graduation-announcement-access', function ($user) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            return $user->hasRole('Siswa') || $user->role === 'siswa';
        });

        // Sidebar gate — nama berbeda agar tidak dioverride Spatie Gate::before
        Gate::define('sidebar-siswa-graduation', function ($user) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA', 'GTK'])) return false;
            return $user->hasRole('Siswa') || $user->role === 'siswa';
        });

        Gate::define('siswa-menu-only', function ($user) {
            return ($user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists()) &&
                !$user->hasRole('GTK') &&
                !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
        });

        // Gate for GTK-specific menus (Dashboard Saya, Profil Saya)
        // Only show to users with GTK role, excluding Super Admin and Admin
        // PAKAI sidebar- prefix agar tidak bentrok dengan Spatie permission 'gtk-menu-only'
        Gate::define('sidebar-gtk-menu-only', function ($user) {
            return $user->hasRole('GTK') && 
                   !$user->hasRole('Siswa') &&
                   !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']) &&
                   !$user->siswa()->exists();
        });

        // Gate for Admin Dashboard
        // Show to Super Admin, Admin, Operator, Kepala Madrasah, WAKA but NOT to pure GTK users
        Gate::define('admin-dashboard-access', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']) ||
                in_array($user->role, ['super_admin', 'admin', 'operator']);
        });

        // Gate for admin-only menu items (e.g. SMART-Q Unggulan di menu admin)
        // PAKAI sidebar- prefix agar tidak bentrok dengan Spatie permission 'admin-menu-only'
        Gate::define('sidebar-admin-menu-only', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']) ||
                in_array($user->role, ['super_admin', 'admin', 'operator']);
        });

        Gate::define('face-registration-admin', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin']) ||
                in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('face-registration-access', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'GTK']) ||
                in_array($user->role, ['super_admin', 'admin', 'gtk']);
        });

        Gate::define('staff-presensi-menu', function ($user) {
            return !$user->hasRole('Siswa') &&
                !$user->siswa()->exists() &&
                (
                    $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'GTK', 'BK', 'Wali Kelas', 'Kepala Madrasah', 'WAKA']) ||
                    in_array($user->role, ['super_admin', 'admin', 'operator', 'gtk', 'bk', 'wali_kelas'])
                );
        });
    }
}
