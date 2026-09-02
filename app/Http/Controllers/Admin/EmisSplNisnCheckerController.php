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
            'nisn' => ['required', 'regex:/^\d{10}$/'],
        ], [
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.regex' => 'NISN harus terdiri dari tepat 10 digit angka.',
        ]);

        $result = $this->service->cekNisnSpl($validated['nisn']);

        Log::info('Pemeriksaan NISN SPL EMIS', [
            'user_id' => auth()->id(),
            'nisn' => EmisNisnService::maskNisn($validated['nisn']),
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
            'invalid_nisn' => 422,
            'not_found' => 404,
            'rate_limited' => 429,
            'credential_missing', 'token_expired' => 503,
            default => 502,
        };
    }
}
