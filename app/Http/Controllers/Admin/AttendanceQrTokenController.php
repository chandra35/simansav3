<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaSession;
use App\Models\SholatSession;
use App\Services\StudentQrAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceQrTokenController extends Controller
{
    public function __construct(private readonly StudentQrAttendanceService $service)
    {
    }

    public function issueDaily(Request $request, AbsensiSiswaSession $session): JsonResponse
    {
        abort_unless($request->user()->can('view-student-attendance'), 403, 'Anda tidak memiliki izin membuat token QR absensi siswa.');

        $validated = $request->validate([
            'ttl_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
        ]);

        $issued = $this->service->issueDailyToken(
            $session,
            $request->user(),
            $validated['ttl_minutes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Token QR absensi harian berhasil dibuat.',
            'data' => [
                'context' => $issued['context'],
                'token' => $issued['token'],
                'expires_at' => $issued['expires_at']?->toIso8601String(),
                'session_id' => $issued['absensi_siswa_session_id'],
            ],
        ], 201);
    }

    public function issueSholat(Request $request, SholatSession $sholatSession): JsonResponse
    {
        abort_unless($request->user()->can('sholat-input'), 403, 'Anda tidak memiliki izin membuat token QR sholat.');

        $validated = $request->validate([
            'ttl_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
        ]);

        $issued = $this->service->issueSholatToken(
            $sholatSession,
            $request->user(),
            $validated['ttl_minutes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Token QR sholat berhasil dibuat.',
            'data' => [
                'context' => $issued['context'],
                'token' => $issued['token'],
                'expires_at' => $issued['expires_at']?->toIso8601String(),
                'session_id' => $issued['sholat_session_id'],
            ],
        ], 201);
    }
}
