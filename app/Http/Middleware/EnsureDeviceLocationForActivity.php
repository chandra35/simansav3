<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceLocationForActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldEnforce($request)) {
            return $next($request);
        }

        if ($this->hasDeviceLocation($request)) {
            return $next($request);
        }

        $message = 'Lokasi perangkat wajib diaktifkan untuk menyimpan aktivitas. Izinkan akses lokasi lalu coba lagi.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'location_required' => true,
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $message);
    }

    private function shouldEnforce(Request $request): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($request->routeIs('device-location.sync')) {
            return false;
        }

        if (!Auth::check() && !$request->routeIs('login')) {
            return false;
        }

        $setting = AppSetting::query()->select('activity_log_require_location')->first();

        return (bool) ($setting?->activity_log_require_location ?? false);
    }

    private function hasDeviceLocation(Request $request): bool
    {
        if ($request->filled('latitude') && $request->filled('longitude')) {
            return true;
        }

        if ($request->headers->has('X-Device-Latitude') && $request->headers->has('X-Device-Longitude')) {
            return true;
        }

        $sessionLocation = $request->session()->get('device_location');

        return !empty($sessionLocation['latitude']) && !empty($sessionLocation['longitude']);
    }
}
