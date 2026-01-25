<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     * Force siswa to change password and set email if is_first_login is true
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }

        // Only apply to siswa role
        if (!$user->isSiswa()) {
            return $next($request);
        }

        // Check if user needs to change password (first login)
        if ($user->is_first_login) {
            // Allow access to password change routes and logout
            $allowedRoutes = [
                'siswa.profile.password',
                'siswa.profile.password.update',
                'siswa.force-setup',
                'siswa.force-setup.update',
                'logout',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('siswa.force-setup')
                    ->with('warning', 'Silakan lengkapi password dan email terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
