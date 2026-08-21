<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmisGtkNipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmisGtkNipCheckerController extends Controller
{
    public function __construct(private readonly EmisGtkNipService $service) {}

    public function index()
    {
        $this->authorizeAccess();

        return view('admin.pengaturan.cek-nip-emisgtk', [
            'credentialConfigured' => $this->service->isConfigured(),
        ]);
    }

    public function check(Request $request)
    {
        $this->authorizeAccess();
        $validated = $request->validate([
            'nip' => ['required', 'regex:/^\d{18}$/'],
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.regex' => 'NIP harus terdiri dari tepat 18 digit.',
        ]);

        $result = $this->service->check($validated['nip']);
        Log::info('Pemeriksaan NIP EMIS GTK', [
            'user_id' => auth()->id(),
            'nip' => EmisGtkNipService::maskNip($validated['nip']),
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
            'invalid_nip' => 422,
            'rate_limited' => 429,
            'credential_missing', 'session_expired' => 503,
            default => 502,
        };
    }
}
