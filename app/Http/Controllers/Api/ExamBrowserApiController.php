<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Public API for the ExaManmet exam browser app.
 *
 * The app normally reads the STATIC config snapshot directly
 * (/storage/exam-browser/config.json) which the web server serves as a
 * plain file — no PHP/DB. This controller only provides a dynamic
 * fallback for that snapshot.
 *
 * Passwords are delivered as bcrypt hashes only; verification happens
 * locally on the device. Session/heartbeat/violation/notification
 * polling endpoints were removed — they overloaded the server.
 */
class ExamBrowserApiController extends Controller
{
    /**
     * Fallback config endpoint.
     * Returns the static snapshot file, rebuilding it if missing.
     */
    public function config(): JsonResponse
    {
        $disk = Storage::disk('public');

        if ($disk->exists(ExamBrowserSetting::STATIC_CONFIG_PATH)) {
            $json = json_decode($disk->get(ExamBrowserSetting::STATIC_CONFIG_PATH), true);
            if (is_array($json)) {
                return response()->json($json);
            }
        }

        // Snapshot missing/corrupt — rebuild it from the active setting.
        $setting = ExamBrowserSetting::getActive();

        if (!$setting) {
            return response()->json([
                'is_active' => false,
                'message' => 'Tidak ada konfigurasi exam browser yang aktif.',
            ], 404);
        }

        $setting->generateStaticConfigFile();

        return response()->json($setting->toStaticConfig());
    }

    /** Verify the app-entry or app-exit password without returning its hash. */
    public function verifyPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'in:app,exit'],
            'device_id' => ['nullable', 'string', 'max:200'],
        ]);

        $setting = ExamBrowserSetting::getActive();
        if (!$setting || !$setting->is_active) {
            return response()->json(['success' => false, 'message' => 'CBTman sedang tidak aktif.'], 403);
        }

        $column = $validated['purpose'] === 'exit' ? 'cbtman_exit_password' : 'cbtman_app_password';
        $legacyColumn = $validated['purpose'] === 'exit' ? 'exit_password' : 'app_password';
        $stored = (string) ($setting->{$column} ?? '');
        // Keep existing ExaManmet installations working until CBTman values
        // are configured, while always preferring the dedicated CBTman field.
        if ($stored === '') {
            $column = $legacyColumn;
            $stored = (string) ($setting->{$column} ?? '');
        }
        $password = $validated['password'];
        $valid = false;

        if ($stored !== '') {
            if (str_starts_with($stored, '$2y$')) {
                $valid = Hash::check($password, $stored);
            } else {
                // Verify legacy plaintext settings without mutating the
                // admin field. Password fields must remain stable in the UI.
                $valid = hash_equals($stored, $password);
            }
        }

        if (!$valid) {
            return response()->json(['success' => false, 'message' => 'Password salah.'], 401);
        }

        return response()->json(['success' => true, 'purpose' => $validated['purpose']]);
    }
}
