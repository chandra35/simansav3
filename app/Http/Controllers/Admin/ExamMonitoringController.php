<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSession;
use App\Models\ExamBrowserViolation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamMonitoringController extends Controller
{
    /**
     * Auto-end sessions that have been offline for more than 2 hours.
     * This prevents zombie sessions from accumulating over multi-day exams.
     */
    private function autoCleanupStaleSessions(): int
    {
        return ExamBrowserSession::where('is_active', true)
            ->where(function ($q) {
                $q->where('last_heartbeat', '<', now()->subHours(2))
                  ->orWhereNull('last_heartbeat');
            })
            ->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);
    }

    /**
     * Map session to JSON-safe array for frontend.
     */
    private function mapSessionToArray(ExamBrowserSession $s): array
    {
        return [
            'id' => $s->id,
            'siswa_nama' => $s->siswa?->nama_lengkap ?? $s->moodle_fullname ?? $s->moodle_username ?? '-',
            'siswa_nisn' => $s->siswa?->nisn ?? $s->moodle_username,
            'kelas' => $s->siswa?->kelasSaatIni?->nama_kelas ?? '-',
            'device_model' => $s->device_model ?? '-',
            'device_id' => $s->device_id,
            'status' => $s->status,
            'status_label' => $s->status_label,
            'status_color' => $s->status_color,
            'is_locked' => $s->is_locked,
            'lock_reason' => $s->lock_reason,
            'violation_count' => $s->violation_count,
            'last_heartbeat' => $s->last_heartbeat ? $s->last_heartbeat->diffForHumans(short: true) : null,
            'started_at' => $s->started_at?->format('H:i'),
            'started_date' => $s->started_at?->format('Y-m-d'),
            'ip_address' => $s->ip_address,
            'app_version' => $s->app_version,
            'foto' => $s->siswa?->foto_profile,
        ];
    }

    /**
     * Main monitoring page — shows all active exam sessions.
     * Supports ?date=YYYY-MM-DD filter for multi-day exams.
     */
    public function index(Request $request)
    {
        // Auto-cleanup stale offline sessions (>2 hours)
        $this->autoCleanupStaleSessions();

        $dateFilter = $request->get('date', now()->format('Y-m-d'));

        $query = ExamBrowserSession::with(['siswa', 'siswa.kelasSaatIni', 'siswa.user'])
            ->active()
            ->orderBy('last_heartbeat', 'desc');

        // Filter by date if not "all"
        if ($dateFilter !== 'all') {
            $query->whereDate('started_at', $dateFilter);
        }

        $activeSessions = $query->get();

        $stats = [
            'total_active' => $activeSessions->count(),
            'online' => $activeSessions->filter(fn($s) => $s->status === 'online')->count(),
            'idle' => $activeSessions->filter(fn($s) => $s->status === 'idle')->count(),
            'offline' => $activeSessions->filter(fn($s) => $s->status === 'offline')->count(),
            'locked' => $activeSessions->filter(fn($s) => $s->is_locked)->count(),
            'with_violations' => $activeSessions->filter(fn($s) => $s->violation_count > 0)->count(),
        ];

        // Pre-map sessions to plain array (avoids Blade @json parsing issues)
        $sessionsJson = $activeSessions->map(fn($s) => $this->mapSessionToArray($s))->values();

        // Get available dates for the date picker
        $availableDates = ExamBrowserSession::where('is_active', true)
            ->selectRaw('DATE(started_at) as exam_date')
            ->groupBy('exam_date')
            ->orderBy('exam_date', 'desc')
            ->pluck('exam_date')
            ->filter()
            ->values();

        return view('admin.exam-monitoring.index', compact(
            'activeSessions', 'stats', 'sessionsJson', 'dateFilter', 'availableDates'
        ));
    }

    /**
     * API endpoint for AJAX refresh (auto-refresh every 10 seconds).
     */
    public function apiSessions(Request $request): JsonResponse
    {
        $dateFilter = $request->get('date', now()->format('Y-m-d'));

        $query = ExamBrowserSession::with(['siswa', 'siswa.kelasSaatIni'])
            ->active()
            ->orderBy('last_heartbeat', 'desc');

        if ($dateFilter !== 'all') {
            $query->whereDate('started_at', $dateFilter);
        }

        $sessions = $query->get()->map(fn($s) => $this->mapSessionToArray($s));

        $stats = [
            'total_active' => $sessions->count(),
            'online' => $sessions->filter(fn($s) => $s['status'] === 'online')->count(),
            'idle' => $sessions->filter(fn($s) => $s['status'] === 'idle')->count(),
            'offline' => $sessions->filter(fn($s) => $s['status'] === 'offline')->count(),
            'locked' => $sessions->filter(fn($s) => $s['is_locked'])->count(),
            'with_violations' => $sessions->filter(fn($s) => $s['violation_count'] > 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'sessions' => $sessions->values(),
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
