<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientRequest
{
    public static function ip(?Request $request = null): ?string
    {
        $request ??= request();

        $candidates = [
            $request->header('CF-Connecting-IP'),
            $request->header('True-Client-IP'),
            $request->header('X-Real-IP'),
            self::extractForwardedFor($request->header('X-Forwarded-For')),
            self::extractForwardedHeader($request->header('Forwarded')),
            $request->getClientIp(),
            $request->ip(),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return null;
    }

    public static function ipSource(?Request $request = null): string
    {
        $request ??= request();

        if (filter_var($request->header('CF-Connecting-IP'), FILTER_VALIDATE_IP)) {
            return 'cf-connecting-ip';
        }

        if (filter_var($request->header('True-Client-IP'), FILTER_VALIDATE_IP)) {
            return 'true-client-ip';
        }

        if (filter_var($request->header('X-Real-IP'), FILTER_VALIDATE_IP)) {
            return 'x-real-ip';
        }

        if (self::extractForwardedFor($request->header('X-Forwarded-For'))) {
            return 'x-forwarded-for';
        }

        if (self::extractForwardedHeader($request->header('Forwarded'))) {
            return 'forwarded';
        }

        return 'remote-addr';
    }

    public static function isPublicIp(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private static function extractForwardedFor(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        foreach (explode(',', $header) as $ip) {
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return null;
    }

    private static function extractForwardedHeader(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        if (preg_match('/for=(?:"?\[?)([a-f0-9\.:]+)(?:\]?"?)/i', $header, $matches)) {
            $ip = trim($matches[1], "\"' ");
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return null;
    }
}
