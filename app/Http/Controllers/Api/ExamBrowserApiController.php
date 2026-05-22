<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSetting;
use Illuminate\Http\JsonResponse;
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
}
