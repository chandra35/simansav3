<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSetting;
use App\Models\ExamBrowserSession;
use App\Models\ExamBrowserViolation;
use App\Models\ExamNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

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
            'message' => 'ExaManmet API is running',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    }

    /**
     * Get notifications for the app.
     * Supports ?since=ISO8601 to get only newer notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = ExamNotification::getActiveForApi();

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->input('since'));
                $notifications = $notifications
                    ->filter(fn (ExamNotification $notification) => $notification->created_at?->gt($since))
                    ->values();
            } catch (\Exception $e) {
                // Ignore invalid date
            }
        }

        return response()->json([
            'success' => true,
            'data' => $notifications->map->toApiFormat(),
            'count' => $notifications->count(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    // ==================== SESSION & VIOLATION ENDPOINTS ====================

    /**
     * Start exam session.
     * Called when student opens exam in ExaManmet app.
     * Auto-matches moodle_username to siswa data via NISN.
     */
    public function sessionStart(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'moodle_username' => 'nullable|string|max:100',
            'moodle_fullname' => 'nullable|string|max:200',
            'app_version' => 'nullable|string|max:20',
            'os_version' => 'nullable|string|max:50',
        ]);

        // Close any previous active session from this device
        ExamBrowserSession::where('device_id', $request->device_id)
            ->active()
            ->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);

        // Auto-match siswa
        $siswaId = ExamBrowserSession::matchSiswa($request->moodle_username);

        // Create new session
        $session = ExamBrowserSession::create([
            'siswa_id' => $siswaId,
            'device_id' => $request->device_id,
            'device_model' => $request->device_model,
            'moodle_username' => $request->moodle_username,
            'moodle_fullname' => $request->moodle_fullname,
            'app_version' => $request->app_version,
            'os_version' => $request->os_version,
            'ip_address' => $request->ip(),
            'last_heartbeat' => now(),
            'started_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session ujian dimulai.',
            'data' => $session->toApiResponse(),
        ]);
    }

    /**
     * Heartbeat — app sends every 30 seconds.
     * Returns lock status so app knows if exam is locked.
     */
    public function sessionHeartbeat(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'moodle_username' => 'nullable|string|max:100',
            'moodle_fullname' => 'nullable|string|max:200',
        ]);

        $session = ExamBrowserSession::find($request->session_id);

        if (!$session || !$session->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Session tidak ditemukan atau sudah berakhir.',
                'data' => [
                    'is_locked' => false,
                    'session_valid' => false,
                ],
            ], 404);
        }

        // Update heartbeat
        $updateData = [
            'last_heartbeat' => now(),
            'ip_address' => $request->ip(),
        ];

        // Update moodle info if provided (may arrive after initial session start)
        if ($request->moodle_username && !$session->moodle_username) {
            $updateData['moodle_username'] = $request->moodle_username;
            $updateData['moodle_fullname'] = $request->moodle_fullname;
            // Try to match siswa if not yet matched
            if (!$session->siswa_id) {
                $updateData['siswa_id'] = ExamBrowserSession::matchSiswa($request->moodle_username);
            }
        }

        $session->update($updateData);

        $autoLockThreshold = 3; // default
        // Check if we should auto-lock
        if (!$session->is_locked && $session->violation_count >= $autoLockThreshold) {
            $session->lockSession(
                "Otomatis dikunci: {$session->violation_count} pelanggaran terdeteksi",
                null
            );
            $session->refresh();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'session_valid' => true,
                'is_locked' => $session->is_locked,
                'lock_reason' => $session->lock_reason,
                'violation_count' => $session->violation_count,
            ],
        ]);
    }

    /**
     * Report a violation from the app.
     * Server stores it and increments violation count.
     * May trigger auto-lock if threshold exceeded.
     * Rate-limited: same session + same type within 10 seconds is deduplicated.
     */
    public function sessionViolation(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'violation_type' => 'required|string|max:50',
            'violation_detail' => 'nullable|string|max:500',
        ]);

        $session = ExamBrowserSession::find($request->session_id);

        if (!$session || !$session->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Session tidak ditemukan atau sudah berakhir.',
            ], 404);
        }

        // Rate-limit: skip if same violation type was recorded within 10 seconds
        // This prevents rapid-fire violations from filling the database
        $recentDuplicate = ExamBrowserViolation::where('session_id', $session->id)
            ->where('violation_type', $request->violation_type)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if (!$recentDuplicate) {
            // Create violation record
            ExamBrowserViolation::create([
                'session_id' => $session->id,
                'siswa_id' => $session->siswa_id,
                'violation_type' => $request->violation_type,
                'violation_detail' => $request->violation_detail,
                'device_id' => $session->device_id,
                'ip_address' => $request->ip(),
            ]);

            // Increment violation count
            $session->increment('violation_count');
            $session->refresh();
        }

        // Check auto-lock (3 violations = auto-lock)
        $autoLockThreshold = 3;
        $autoLocked = false;
        if (!$session->is_locked && $session->violation_count >= $autoLockThreshold) {
            $session->lockSession(
                "Otomatis dikunci: {$session->violation_count} pelanggaran terdeteksi",
                null
            );
            $autoLocked = true;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran dicatat.',
            'data' => [
                'violation_count' => $session->violation_count,
                'is_locked' => $session->is_locked,
                'lock_reason' => $session->lock_reason,
                'auto_locked' => $autoLocked,
            ],
        ]);
    }

    /**
     * End exam session.
     */
    public function sessionEnd(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $session = ExamBrowserSession::find($request->session_id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session tidak ditemukan.',
            ], 404);
        }

        $session->endSession();

        return response()->json([
            'success' => true,
            'message' => 'Session ujian diakhiri.',
        ]);
    }
}
