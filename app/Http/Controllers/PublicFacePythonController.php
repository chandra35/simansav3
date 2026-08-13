<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSetting;
use App\Services\FaceDescriptorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicFacePythonController extends Controller
{
    public function bootstrap(Request $request, FaceDescriptorService $service): JsonResponse
    {
        $token = $this->validToken($request);
        $people = $service->forPython();

        return response()->json([
            'success' => true,
            'mode' => 'simulation',
            'server_time' => now()->toIso8601String(),
            'revision' => hash('sha256', $people->pluck('revision')->implode('|')),
            'settings' => [
                'match_threshold' => 0.42,
                'confirm_frames' => 3,
                'cooldown_seconds' => 20,
                'heartbeat_seconds' => 10,
            ],
            'people' => $people,
        ])->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $token = $this->validToken($request);
        $data = $request->validate([
            'device_name' => ['required', 'string', 'max:100'],
            'agent_version' => ['required', 'string', 'max:30'],
            'state' => ['required', 'in:starting,syncing,running,stopped,error'],
            'fps' => ['nullable', 'numeric', 'min:0', 'max:240'],
            'faces_in_frame' => ['nullable', 'integer', 'min:0', 'max:100'],
            'profiles' => ['nullable', 'integer', 'min:0'],
            'recognized_name' => ['nullable', 'string', 'max:150'],
            'confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'message' => ['nullable', 'string', 'max:250'],
        ]);

        $data['last_seen'] = now()->toIso8601String();
        $data['ip'] = $request->ip();
        Cache::put('face-python:device:'.hash('sha256', $token), $data, now()->addMinutes(5));

        return response()->json(['success' => true, 'server_time' => now()->toIso8601String()])
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }

    private function validToken(Request $request): string
    {
        $provided = (string) $request->bearerToken();
        $expected = (string) AbsensiSetting::getValue('face_python_device_token', '');
        abort_if(strlen($expected) < 32 || strlen($provided) < 32 || ! hash_equals($expected, $provided), 404);

        return $provided;
    }
}
