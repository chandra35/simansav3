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
            return $user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists();
        });

        Gate::define('siswa-smartq-access', function ($user) {
            if (!$user->siswa) return false;
            return \App\Models\SmartqPeserta::where('siswa_id', $user->siswa->id)
                ->whereIn('status', ['lulus', 'cadangan'])
                ->exists();
        });

        Gate::define('siswa-graduation-announcement-access', function ($user) {
            if (!$user->siswa) {
                return false;
            }

            $setting = \App\Models\AppSetting::query()->first();
            if (!$setting || !$setting->graduation_announcement_enabled) {
                return false;
            }

            $tahunAktif = \App\Models\TahunPelajaran::query()->where('is_active', true)->first();
            if (!$tahunAktif) {
                return false;
            }

            return \App\Models\SiswaKelas::query()
                ->where('siswa_id', $user->siswa->id)
                ->where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('status', 'aktif')
                ->whereNull('deleted_at')
                ->whereHas('kelas', function ($query) {
                    $query->where('tingkat', 12);
                })
                ->exists();
        });

        Gate::define('siswa-menu-only', function ($user) {
            return ($user->hasRole('Siswa') || $user->role === 'siswa' || $user->siswa()->exists()) &&
                !$user->hasRole('GTK') &&
                !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA']);
        });

        // Gate for GTK-specific menus (Dashboard Saya, Profil Saya)
        // Only show to users with GTK role, excluding Super Admin and Admin
        Gate::define('gtk-menu-only', function ($user) {
            return $user->hasRole('GTK') && 
                   !$user->hasRole('Siswa') &&
                   !$user->hasRole('Super Admin') && 
                   !$user->hasRole('Admin') &&
                   !$user->siswa()->exists();
        });

        // Gate for Admin Dashboard
        // Show to Super Admin, Admin, Operator, Kepala Madrasah, WAKA but NOT to pure GTK users
        Gate::define('admin-dashboard-access', function ($user) {
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
                    $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'GTK', 'Wali Kelas', 'Kepala Madrasah', 'WAKA']) ||
                    in_array($user->role, ['super_admin', 'admin', 'operator', 'gtk'])
                );
        });

        Gate::define('kesiswaan-lulusan-access', function ($user) {
            if ($user->hasRole('Wali Kelas') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])) {
                return false;
            }

            return $user->can('view-siswa');
        });
    }
}
