<?php

if (!function_exists('getDashboardRoute')) {
    /**
     * Get dashboard route based on user role
     *
     * @return string
     */
    function getDashboardRoute()
    {
        if (!auth()->check()) {
            return route('login');
        }

        $user = auth()->user();

        // Check role and return appropriate dashboard
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('GTK') || $user->hasAnyRole(['Wali Kelas', 'Kepala Sekolah', 'Wakil Kepala Sekolah'])) {
            return route('admin.gtk.dashboard');
        }

        if ($user->hasRole('Siswa')) {
            return route('siswa.dashboard');
        }

        // Default fallback
        return route('admin.dashboard');
    }
}
