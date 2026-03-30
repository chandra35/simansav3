<?php

namespace App\Http\Middleware;

use App\Models\ExamBrowserSetting;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupportedExamBrowserClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $minimumVersion = $this->normalizeVersion(
            ExamBrowserSetting::getActive()?->minimum_app_version
        );

        if ($minimumVersion === null) {
            return $next($request);
        }

        $clientVersion = $this->resolveClientVersion($request);

        if ($clientVersion === null) {
            return $next($request);
        }

        if (version_compare($clientVersion, $minimumVersion, '<')) {
            return $this->unsupportedResponse($minimumVersion, $clientVersion);
        }

        return $next($request);
    }

    private function resolveClientVersion(Request $request): ?string
    {
        $candidates = [
            $request->input('app_version'),
            $request->query('app_version'),
            $request->header('X-ExamAnmet-Version'),
            $request->header('X-App-Version'),
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeVersion($candidate);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private function normalizeVersion(?string $version): ?string
    {
        if ($version === null) {
            return null;
        }

        $version = trim($version);
        if ($version === '') {
            return null;
        }

        $version = explode('+', $version, 2)[0];

        if (!preg_match('/^\d+(?:\.\d+){0,3}$/', $version)) {
            return null;
        }

        return $version;
    }

    private function unsupportedResponse(string $minimumVersion, string $clientVersion): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Versi aplikasi tidak didukung. Silakan update aplikasi terlebih dahulu.',
            'error_code' => 'UNSUPPORTED_APP_VERSION',
            'minimum_app_version' => $minimumVersion,
            'client_app_version' => $clientVersion,
        ], 426);
    }
}
