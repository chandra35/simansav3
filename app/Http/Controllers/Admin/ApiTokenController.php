<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiTokenController extends Controller
{
    // Available token types
    private $tokenTypes = [
        'emis_api_token' => [
            'name' => 'Token EMIS (Cek NISN)',
            'description' => 'Token Bearer untuk API EMIS Kemenag (Cek NISN Siswa)',
            'api_url' => 'https://api-emis.kemenag.go.id/v1',
            'test_route' => 'admin.pengaturan.cek-nisn.index',
        ],
        'kemenag_nip_token' => [
            'name' => 'Token Kemenag (Cek NIP)',
            'description' => 'Token Bearer untuk API Kemenag BE-PINTAR (Cek NIP GTK)',
            'api_url' => 'https://be-pintar.kemenag.go.id/api/v1',
            'test_route' => 'admin.pengaturan.cek-nip.index',
        ],
    ];

    public function index()
    {
        // Cek permission Super Admin atau manage-settings
        $user = Auth::user();
        if (!$user->can('manage-settings') && !$user->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Get all tokens
        $tokens = DB::table('api_tokens')
            ->whereIn('name', array_keys($this->tokenTypes))
            ->get()
            ->keyBy('name');

        return view('admin.pengaturan.update-api-token', [
            'tokens' => $tokens,
            'tokenTypes' => $this->tokenTypes,
        ]);
    }

    public function update(Request $request)
    {
        // Cek permission Super Admin atau manage-settings
        $user = Auth::user();
        if (!$user->can('manage-settings') && !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'token_type' => 'required|in:' . implode(',', array_keys($this->tokenTypes)),
            'token' => 'required|string|min:100'
        ], [
            'token_type.required' => 'Tipe token wajib dipilih',
            'token_type.in' => 'Tipe token tidak valid',
            'token.required' => 'Token wajib diisi',
            'token.min' => 'Token minimal 100 karakter'
        ]);

        try {
            $tokenType = $request->token_type;
            $token = trim($request->token);
            $tokenInfo = $this->tokenTypes[$tokenType];

            // Validate JWT format (basic check) - optional for some tokens
            $isJwt = $this->validateJwtFormat($token);
            
            // Decode JWT to get expiry time (if JWT)
            $decoded = null;
            $expiresAt = null;
            if ($isJwt) {
                $decoded = $this->decodeJwt($token);
                if ($decoded && isset($decoded['exp'])) {
                    $expiresAt = date('Y-m-d H:i:s', $decoded['exp']);
                }
            }

            // Update or insert token
            DB::table('api_tokens')->updateOrInsert(
                ['name' => $tokenType],
                [
                    'token' => $token,
                    'description' => $tokenInfo['description'],
                    'expires_at' => $expiresAt,
                    'updated_at' => now()
                ]
            );

            // Log activity
            Log::info('API Token Updated', [
                'token_type' => $tokenType,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'expires_at' => $expiresAt,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => $tokenInfo['name'] . ' berhasil diupdate',
                'expires_at' => $expiresAt ? date('d F Y H:i:s', strtotime($expiresAt)) : null,
                'is_jwt' => $isJwt
            ]);

        } catch (\Exception $e) {
            Log::error('API Token Update Failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat update token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate JWT format (3 parts separated by dots)
     */
    private function validateJwtFormat($token)
    {
        $parts = explode('.', $token);
        return count($parts) === 3;
    }

    /**
     * Decode JWT payload (without verification)
     */
    private function decodeJwt($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            // Decode payload (second part)
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            return json_decode($payload, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
