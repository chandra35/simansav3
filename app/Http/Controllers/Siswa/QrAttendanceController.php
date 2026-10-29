<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\StudentQrAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrAttendanceController extends Controller
{
    public function __construct(private readonly StudentQrAttendanceService $service)
    {
    }

    public function index()
    {
        return view('siswa.qr-attendance.index');
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:16', 'max:255'],
        ]);

        $user = $request->user();
        $siswa = $user?->siswa;
        abort_if(! $siswa, 403, 'Akun ini tidak terhubung dengan profil siswa.');

        $result = $this->service->checkInWithToken($validated['token'], $siswa, $user);

        return response()->json([
            'success' => true,
            'message' => $result['already_marked']
                ? 'Presensi sudah tercatat sebelumnya untuk sesi ini.'
                : 'Presensi QR berhasil disimpan.',
            'data' => $result,
        ], $result['already_marked'] ? 200 : 201);
    }
}
