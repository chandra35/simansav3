<?php

namespace App\Http\Controllers;

use App\Models\HotspotDeviceReport;
use App\Models\HotspotUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class HotspotDeviceReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:64'],
            'mac' => ['required', 'string', 'max:30'],
            'ip' => ['nullable', 'ip'],
            'model' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:60'],
            'platform_version' => ['nullable', 'string', 'max:40'],
            'architecture' => ['nullable', 'string', 'max:30'],
            'brands' => ['nullable', 'string', 'max:500'],
        ]);

        $mac = $this->normalizeMac($data['mac']);
        abort_unless($mac && $this->hasMatchingActiveSession($data['username'], $mac, $data['ip'] ?? null), 422, 'Sesi Hotspot aktif tidak ditemukan.');

        $userAgent = Str::limit((string) $request->userAgent(), 2000, '');
        $metadata = $this->resolveMetadata($userAgent, $data);
        $hotspot = HotspotUser::query()->where('username', $data['username'])->first();
        $now = now();

        $report = HotspotDeviceReport::query()->updateOrCreate(
            ['username' => $data['username'], 'mac_address' => $mac],
            [
                'hotspot_user_id' => $hotspot?->id,
                'last_ip' => $data['ip'] ?? null,
                ...$metadata,
                'user_agent' => $userAgent,
                'client_hints' => [
                    'architecture' => $data['architecture'] ?? null,
                    'brands' => $data['brands'] ?? null,
                ],
                'first_seen_at' => HotspotDeviceReport::query()
                    ->where('username', $data['username'])->where('mac_address', $mac)
                    ->value('first_seen_at') ?: $now,
                'last_seen_at' => $now,
            ]
        );

        return response()->json(['success' => true, 'device' => $report->display_label]);
    }

    private function hasMatchingActiveSession(string $username, string $mac, ?string $ip): bool
    {
        return DB::connection('mysql_radius')->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->get(['callingstationid', 'framedipaddress'])
            ->contains(function ($session) use ($mac, $ip) {
                $sessionMac = $this->normalizeMac((string) $session->callingstationid);
                $ipMatches = ! $ip || ! $session->framedipaddress || $session->framedipaddress === $ip;

                return $sessionMac === $mac && $ipMatches;
            });
    }

    private function resolveMetadata(string $userAgent, array $data): array
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);
        $model = $this->clean($data['model'] ?? null) ?: $this->modelFromUserAgent($userAgent);
        $platform = $this->clean($data['platform'] ?? null) ?: ($agent->platform() ?: null);
        $deviceType = $agent->isTablet() ? 'tablet' : ($agent->isMobile() ? 'mobile' : 'desktop');
        $vendor = $this->vendorFor($model, $userAgent, $platform);

        return [
            'vendor' => $vendor,
            'model' => $model,
            'marketing_name' => $this->marketingName($model, $vendor),
            'device_type' => $deviceType,
            'platform' => $platform,
            'platform_version' => $this->clean($data['platform_version'] ?? null),
            'browser' => $agent->browser() ?: null,
        ];
    }

    private function modelFromUserAgent(string $userAgent): ?string
    {
        if (preg_match('/Android[^;)]*;\s*([^;)]+?)(?:\s+Build\/[^;)]*)?[;)]/i', $userAgent, $matches)) {
            return $this->clean($matches[1]);
        }
        if (stripos($userAgent, 'iPad') !== false) return 'iPad';
        if (stripos($userAgent, 'iPhone') !== false) return 'iPhone';

        return null;
    }

    private function vendorFor(?string $model, string $userAgent, ?string $platform): ?string
    {
        $haystack = strtolower(trim(($model ?? '').' '.$userAgent.' '.($platform ?? '')));

        return match (true) {
            str_contains($haystack, 'samsung'), (bool) preg_match('/^sm-/i', (string) $model) => 'Samsung',
            str_contains($haystack, 'oppo'), (bool) preg_match('/^cph/i', (string) $model) => 'OPPO',
            str_contains($haystack, 'realme'), (bool) preg_match('/^rmx/i', (string) $model) => 'realme',
            str_contains($haystack, 'vivo') => 'vivo',
            str_contains($haystack, 'xiaomi'), str_contains($haystack, 'redmi'), str_contains($haystack, 'poco') => 'Xiaomi',
            str_contains($haystack, 'iphone'), str_contains($haystack, 'ipad'), str_contains($haystack, 'ios') => 'Apple',
            default => null,
        };
    }

    private function marketingName(?string $model, ?string $vendor): ?string
    {
        $normalized = strtoupper((string) $model);

        return match (true) {
            $vendor === 'Samsung' && str_starts_with($normalized, 'SM-S921') => 'Samsung Galaxy S24',
            $vendor === 'Samsung' && str_starts_with($normalized, 'SM-S926') => 'Samsung Galaxy S24+',
            $vendor === 'Samsung' && str_starts_with($normalized, 'SM-S928') => 'Samsung Galaxy S24 Ultra',
            default => null,
        };
    }

    private function normalizeMac(string $mac): ?string
    {
        $hex = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $mac));
        if (strlen($hex) !== 12) return null;

        return implode(':', str_split($hex, 2));
    }

    private function clean(?string $value): ?string
    {
        $value = Str::squish(strip_tags((string) $value));
        return $value !== '' ? $value : null;
    }
}
