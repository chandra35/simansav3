<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScannerAccessLink;
use App\Models\Siswa;
use App\Models\User;
use App\Services\StudentQrAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceCardScannerController extends Controller
{
    private const ACCESS_SESSION_KEY = 'attendance_card_scanner_access_link';

    public function __construct(private readonly StudentQrAttendanceService $service)
    {
    }

    public function index(Request $request)
    {
        abort_unless($this->canScan($request->user()), 403, 'Akun ini belum memiliki akses pemindai presensi.');

        return view('admin.absensi.card-scanner', [
            'accessLinkId' => session(self::ACCESS_SESSION_KEY),
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        abort_unless($this->canScan($request->user()), 403, 'Akun ini belum memiliki akses pemindai presensi.');

        $validated = $request->validate([
            'card_qr' => ['required', 'string', 'max:2048'],
        ]);

        $studentId = $this->studentIdFromCardQr($validated['card_qr']);
        $student = Siswa::query()->findOrFail($studentId);
        $result = $this->service->checkInWithStudentCard($student, $request->user());

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result,
        ]);
    }

    public function createAccessLink(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view-student-attendance'), 403);

        $plainToken = Str::random(64);
        $link = AttendanceScannerAccessLink::query()->create([
            'token_hash' => hash('sha256', $plainToken),
            'issued_by' => $request->user()->id,
            'expires_at' => now()->addMinutes(30),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'url' => route('card-scanner.claim', ['token' => $plainToken]),
                'expires_at' => $link->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function claim(Request $request, string $token)
    {
        $link = DB::transaction(function () use ($token, $request) {
            $link = AttendanceScannerAccessLink::query()
                ->lockForUpdate()
                ->where('token_hash', hash('sha256', $token))
                ->first();

            if (! $link || $link->claimed_at || $link->isExpired()) {
                throw ValidationException::withMessages(['link' => 'Tautan pemindai tidak valid, sudah digunakan, atau kedaluwarsa.']);
            }

            $link->update([
                'claimed_by' => $request->user()->id,
                'claimed_at' => now(),
            ]);

            return $link;
        });

        session([self::ACCESS_SESSION_KEY => $link->id]);

        return redirect()->route('admin.absensi.card-scanner.index')
            ->with('success', 'Akses pemindai aktif untuk akun Anda.');
    }

    private function canScan(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->can('view-student-attendance')) {
            return true;
        }

        $linkId = session(self::ACCESS_SESSION_KEY);

        return $linkId && AttendanceScannerAccessLink::query()
            ->whereKey($linkId)
            ->where('claimed_by', $user->id)
            ->whereNotNull('claimed_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function studentIdFromCardQr(string $cardQr): string
    {
        $path = parse_url(trim($cardQr), PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('#/verifikasi/siswa/([a-f0-9-]{36})/?$#i', $path, $matches)) {
            throw ValidationException::withMessages([
                'card_qr' => 'QR bukan kartu siswa SIMANSA yang valid.',
            ]);
        }

        return $matches[1];
    }
}
