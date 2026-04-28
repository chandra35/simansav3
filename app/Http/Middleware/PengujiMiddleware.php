<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PengujiMiddleware
{
    /**
     * Handle an incoming request.
     * Allow access for: penguji, admin, super_admin
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow access for penguji, admin, super_admin (Spatie) or legacy role column
        $allowedRoles = ['penguji', 'admin', 'super_admin'];
        
        if (!in_array($user->role, $allowedRoles) && !$user->hasAnyRole(['Super Admin', 'Admin', 'Penguji'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
