<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestApiController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManager($request);

        return view('admin.pengaturan.rest-api', [
            'tokens' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['in:lms:read,lms:auth,lms:write'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $expiresAt = empty($data['expires_at']) ? null : Carbon::parse($data['expires_at']);
        $token = $request->user()->createToken($data['name'], $data['abilities'], $expiresAt);

        return redirect()->route('admin.pengaturan.rest-api.index')->with([
            'success' => 'Token integrasi berhasil dibuat.',
            'new_token' => $token->plainTextToken,
        ]);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $this->authorizeManager($request);

        $request->user()->tokens()->whereKey($tokenId)->firstOrFail()->delete();

        return redirect()->route('admin.pengaturan.rest-api.index')->with('success', 'Token integrasi telah dicabut.');
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->can('manage-settings'), 403);
    }
}
