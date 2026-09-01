<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LmsAuthenticationController extends Controller
{
    /**
     * Validates LMS login credentials against SIMANSA without returning a
     * password, password hash, or any reusable user token.
     */
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()
            ->with(['siswa:id,user_id,nisn,nama_lengkap', 'gtk:id,user_id,nip,nuptk,nama_lengkap'])
            ->where('username', $credentials['username'])
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Kredensial SIMANSA tidak valid.'], 401);
        }

        $type = $user->siswa ? 'student' : 'gtk';
        $profile = $user->siswa ?: $user->gtk;

        return response()->json([
            'data' => [
                'id' => (string) $user->id,
                'username' => $user->username,
                'name' => $profile?->nama_lengkap ?: $user->name,
                'email' => $user->email,
                'account_type' => $type,
                'profile_id' => $profile?->id,
                'identifier' => $type === 'student' ? $profile?->nisn : ($profile?->nip ?: $profile?->nuptk),
                'is_active' => (bool) $user->is_active,
            ],
        ]);
    }
}
