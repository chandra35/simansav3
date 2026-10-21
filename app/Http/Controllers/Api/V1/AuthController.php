<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * APIs for authentication, token generation, and user profile
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login & Generate Bearer Token
     *
     * Authenticate user credentials and return a personal access token for SIMANSA API.
     *
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('username', $validated['username'])
            ->orWhere('email', $validated['username'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Kredensial yang diberikan tidak valid.', 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return $this->errorResponse('Akun pengguna ini sedang dinonaktifkan.', 403);
        }

        $deviceName = $validated['device_name'] ?? 'api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Autentikasi berhasil.');
    }

    /**
     * Get Current Authenticated User Profile
     *
     * Returns detailed information about the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'gtk' => $user->gtk ? [
                'id' => $user->gtk->id,
                'nama' => $user->gtk->nama,
                'nip' => $user->gtk->nip,
                'kode_gtk' => $user->gtk->kode_gtk,
            ] : null,
            'siswa' => $user->siswa ? [
                'id' => $user->siswa->id,
                'nama' => $user->siswa->nama,
                'nis' => $user->siswa->nis,
                'nisn' => $user->siswa->nisn,
                'kelas' => $user->siswa->rombonganBelajar?->nama ?? null,
            ] : null,
        ];

        return $this->successResponse($data, 'Profil pengguna berhasil dimuat.');
    }

    /**
     * Logout & Revoke Current Token
     *
     * Revokes the personal access token that was used to authenticate the request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse(null, 'Token berhasil dicabut.');
    }
}
