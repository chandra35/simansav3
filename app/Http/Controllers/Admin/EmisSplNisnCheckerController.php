<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmisNisnService;
use App\Services\KemendikdasmenSchoolProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmisSplNisnCheckerController extends Controller
{
    public function __construct(
        private readonly EmisNisnService $service,
        private readonly KemendikdasmenSchoolProfileService $schoolProfileService,
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        return view('admin.pengaturan.cek-nisn-spl', [
            'credentialConfigured' => $this->service->isConfigured(),
        ]);
    }

    public function check(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'type' => ['required', 'in:nisn,nik'],
            'number' => ['required', 'regex:/^\d+$/'],
        ], [
            'type.required' => 'Pilih jenis pemeriksaan terlebih dahulu.',
            'type.in' => 'Jenis pemeriksaan tidak valid.',
            'number.required' => 'Nomor identitas wajib diisi.',
            'number.regex' => 'Nomor identitas hanya boleh berisi angka.',
        ]);

        $result = $this->service->cekSpl($validated['type'], $validated['number']);

        Log::info('Pemeriksaan identitas SPL EMIS', [
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'number' => EmisNisnService::maskIdentityNumber($validated['number']),
            'success' => $result['success'],
            'code' => $result['code'],
        ]);

        return response()->json($result, $result['success'] ? 200 : $this->statusFor($result['code']));
    }

    public function schoolProfile(Request $request)
    {
        $this->authorizeAccess();
        $validated = $request->validate([
            'school_id' => ['required', 'regex:/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/i'],
        ], ['school_id.required' => 'ID sekolah wajib tersedia.', 'school_id.regex' => 'ID sekolah tidak valid.']);

        $result = $this->schoolProfileService->getProfile($validated['school_id']);

        Log::info('Pemeriksaan profil sekolah referensi SPL', [
            'user_id' => auth()->id(),
            'school_id' => strtoupper($validated['school_id']),
            'success' => $result['success'],
            'code' => $result['code'],
        ]);

        return response()->json($result, $result['success'] ? 200 : $this->statusFor($result['code']));
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('manage-tools') || $user->hasRole('Super Admin')), 403);
    }

    private function statusFor(string $code): int
    {
        return match ($code) {
            'invalid_type', 'invalid_number', 'invalid_school_id' => 422,
            'not_found' => 404,
            'rate_limited' => 429,
            'credential_missing', 'token_expired' => 503,
            default => 502,
        };
    }
}
