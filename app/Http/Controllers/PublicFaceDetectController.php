<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSetting;
use App\Services\FaceDescriptorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicFaceDetectController extends Controller
{
    public function show(string $token): Response
    {
        $this->assertValidToken($token);

        return response()->view('admin.absensi.door-face-detect', [
            'faceThreshold' => (float) AbsensiSetting::getValue('face_match_threshold', 0.45),
            'descriptorEndpoints' => [
                route('public.face-detect.descriptors', ['token' => $token, 'type' => 'gtk']),
                route('public.face-detect.descriptors', ['token' => $token, 'type' => 'siswa']),
            ],
            'isPublicMode' => true,
            'publicFaceDetectUrl' => null,
        ])->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function descriptors(Request $request, string $token, FaceDescriptorService $service): JsonResponse
    {
        $this->assertValidToken($token);
        $validated = $request->validate(['type' => ['required', 'in:gtk,siswa']]);

        return response()->json([
            'success' => true,
            'data' => $service->forType($validated['type'], true),
        ])->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Referrer-Policy', 'no-referrer');
    }

    private function assertValidToken(string $token): void
    {
        $expected = (string) AbsensiSetting::getValue('face_detect_public_token', '');
        abort_if(strlen($expected) < 32 || ! hash_equals($expected, $token), 404);
    }
}
