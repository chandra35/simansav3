<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientRuntimeController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        if (Auth::check()) {
            UserSession::updateOrCreateSession(Auth::user(), $request);
        }

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'user_online' => Auth::check(),
        ]);
    }

    public function serverTime(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'formatted_time' => now()->format('d M Y H:i:s'),
            'timezone' => config('app.timezone'),
        ]);
    }
}
