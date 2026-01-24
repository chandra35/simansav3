<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmisTokenController extends Controller
{
    public function index()
    {
        // Get current token
        $tokenData = DB::table('api_tokens')->where('name', 'emis_api_token')->first();

        return view('admin.pengaturan.update-emis-token', compact('tokenData'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:100'
        ], [
            'token.required' => 'Token wajib diisi',
            'token.min' => 'Token minimal 100 karakter (JWT format)'
        ]);

        try {
            $token = trim($request->token);

            // Validate JWT format (basic check)
            if (!$this->validateJwtFormat($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format token tidak valid. Token harus berupa JWT (3 bagian dipisahkan dengan titik)'
                ]);
            }

            // Decode JWT to get expiry time
            $decoded = $this->decodeJwt($token);
            $expiresAt = null;
            if ($decoded && isset($decoded['exp'])) {
                $expiresAt = date('Y-m-d H:i:s', $decoded['exp']);
            }

            // Update or insert token
            DB::table('api_tokens')->updateOrInsert(
                ['name' => 'emis_api_token'],
                [
                    'token' => $token,
                    'description' => 'Token Bearer untuk API EMIS Kemenag (Cek NISN)',
                    'expires_at' => $expiresAt,
                    'updated_at' => now()
                ]
            );

            // Log activity
            Log::info('EMIS Token Updated', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'Unknown',
                'expires_at' => $expiresAt,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token EMIS berhasil diupdate',
                'expires_at' => $expiresAt ? date('d F Y H:i:s', strtotime($expiresAt)) : null
            ]);

        } catch (\Exception $e) {
            Log::error('EMIS Token Update Failed', [
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
