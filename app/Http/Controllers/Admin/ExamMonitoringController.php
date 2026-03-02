<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSession;
use App\Models\ExamBrowserViolation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamMonitoringController extends Controller
{
    /**
     * Main monitoring page — shows all active exam sessions.
     */
    public function index()
    {
        $activeSessions = ExamBrowserSession::with(['siswa', 'siswa.kelasSaatIni', 'siswa.user'])
            ->active()
            ->orderBy('last_heartbeat', 'desc')
            ->get();

        $stats = [
            'total_active' => $activeSessions->count(),
            'online' => $activeSessions->filter(fn($s) => $s->status === 'online')->count(),
            'idle' => $activeSessions->filter(fn($s) => $s->status === 'idle')->count(),
            'offline' => $activeSessions->filter(fn($s) => $s->status === 'offline')->count(),
            'locked' => $activeSessions->filter(fn($s) => $s->is_locked)->count(),
            'with_violations' => $activeSessions->filter(fn($s) => $s->violation_count > 0)->count(),
        ];

        return view('admin.exam-monitoring.index', compact('activeSessions', 'stats'));
    }

    /**
     * API endpoint for AJAX refresh (auto-refresh every 10 seconds).
     */
    public function apiSessions(): JsonResponse
    {
        $sessions = ExamBrowserSession::with(['siswa', 'siswa.kelasSaatIni'])
            ->active()
            ->orderBy('last_heartbeat', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'siswa_nama' => $session->siswa?->nama_lengkap ?? $session->moodle_fullname ?? $session->moodle_username ?? 'Unknown',
                    'siswa_nisn' => $session->siswa?->nisn ?? $session->moodle_username,
                    'kelas' => $session->siswa?->kelasSaatIni?->nama_kelas ?? '-',
                    'device_model' => $session->device_model ?? '-',
                    'device_id' => $session->device_id,
                    'status' => $session->status,
                    'status_color' => $session->status_color,
                    'status_label' => $session->status_label,
                    'is_locked' => $session->is_locked,
                    'lock_reason' => $session->lock_reason,
                    'violation_count' => $session->violation_count,
                    'last_heartbeat' => $session->last_heartbeat?->diffForHumans(),
                    'started_at' => $session->started_at?->format('H:i:s'),
                    'ip_address' => $session->ip_address,
                    'app_version' => $session->app_version,
                    'foto' => $session->siswa?->foto_profile,
                ];
            });

        $stats = [
            'total_active' => $sessions->count(),
            'online' => $sessions->filter(fn($s) => $s['status'] === 'online')->count(),
            'locked' => $sessions->filter(fn($s) => $s['is_locked'])->count(),
            'with_violations' => $sessions->filter(fn($s) => $s['violation_count'] > 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
            'stats' => $stats,
        ]);
    }

    /**
     * Lock a student's exam session.
     */
    public function lock(Request $request, ExamBrowserSession $session): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:300',
        ]);

        $session->lockSession($request->reason, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Ujian siswa berhasil dikunci.',
        ]);
    }

    /**
     * Unlock a student's exam session.
     */
    public function unlock(ExamBrowserSession $session): JsonResponse
    {
        $session->unlockSession();

        return response()->json([
            'success' => true,
            'message' => 'Ujian siswa berhasil dibuka kembali.',
        ]);
    }

    /**
     * End a student's exam session.
     */
    public function endSession(ExamBrowserSession $session): JsonResponse
    {
        $session->endSession();

        return response()->json([
            'success' => true,
            'message' => 'Session ujian diakhiri.',
        ]);
    }

    /**
     * Get violation details for a session.
     */
    public function violations(ExamBrowserSession $session): JsonResponse
    {
        $violations = $session->violations()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'type' => $v->violation_type,
                    'type_label' => $v->type_label,
                    'severity_color' => $v->severity_color,
                    'detail' => $v->violation_detail,
                    'time' => $v->created_at->format('H:i:s'),
                    'time_ago' => $v->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'violations' => $violations,
            'session' => [
                'id' => $session->id,
                'siswa_nama' => $session->siswa?->nama_lengkap ?? $session->moodle_fullname ?? 'Unknown',
                'violation_count' => $session->violation_count,
                'is_locked' => $session->is_locked,
            ],
        ]);
    }
}
