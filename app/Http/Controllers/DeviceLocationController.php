<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceLocationController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $request->session()->put('device_location', [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'captured_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
