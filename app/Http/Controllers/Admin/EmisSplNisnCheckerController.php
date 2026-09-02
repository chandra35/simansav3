<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmisNisnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmisSplNisnCheckerController extends Controller
{
    public function __construct(private readonly EmisNisnService $service) {}

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

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('manage-tools') || $user->hasRole('Super Admin')), 403);
    }

    private function statusFor(string $code): int
    {
        return match ($code) {
            'invalid_type', 'invalid_number' => 422,
            'not_found' => 404,
            'rate_limited' => 429,
            'credential_missing', 'token_expired' => 503,
            default => 502,
        };
    }
}
