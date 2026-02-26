<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamBrowserApiController extends Controller
{
    /**
     * Get active exam browser configuration.
     * This endpoint is consumed by the mobile ExamAnmet app.
     * No authentication required - config is public but password-protected in app.
     */
    public function config(): JsonResponse
    {
        $setting = ExamBrowserSetting::getActive();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada konfigurasi exam browser yang aktif.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi exam browser berhasil dimuat.',
            'data' => $setting->toApiConfig(),
        ]);
    }

    /**
     * Verify app password.
     */
    public function verifyPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'type' => 'required|in:app,exit',
        ]);

        $setting = ExamBrowserSetting::getActive();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi tidak ditemukan.',
            ], 404);
        }

        $passwordField = $request->type === 'exit' ? 'exit_password' : 'app_password';
        $storedPassword = $setting->$passwordField;

        // If no password set, always allow
        if (empty($storedPassword)) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada password yang dikonfigurasi.',
                'verified' => true,
            ]);
        }

        $verified = $request->password === $storedPassword;

        return response()->json([
            'success' => true,
            'verified' => $verified,
            'message' => $verified ? 'Password benar.' : 'Password salah.',
        ]);
    }

    /**
     * Ping endpoint for connection check
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'ExamAnmet API is running',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    }
}
