<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;

class ServerInfoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-settings');

        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST) ?: $request->getHost();
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: $request->getScheme();
        $resolvedIp = $this->resolveHostIp($appHost);
        $publicIp = $this->detectPublicIp($request, $resolvedIp);

        $server = [
            'app_url' => $appUrl,
            'app_host' => $appHost,
            'app_scheme' => strtoupper($appScheme),
            'app_env' => config('app.env'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $request->server('SERVER_SOFTWARE') ?: php_sapi_name(),
            'hostname' => gethostname() ?: php_uname('n'),
            'server_name' => $request->server('SERVER_NAME') ?: $appHost,
            'server_addr' => $request->server('SERVER_ADDR') ?: null,
            'resolved_ip' => $resolvedIp,
            'public_ip' => $publicIp,
            'timezone' => config('app.timezone'),
            'server_time' => now()->format('d-m-Y H:i:s'),
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'architecture' => php_uname('m'),
            'base_path' => base_path(),
            'storage_path' => storage_path(),
        ];

        $resources = [
            'disk_total' => $this->formatBytes(@disk_total_space(base_path())),
            'disk_free' => $this->formatBytes(@disk_free_space(base_path())),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . ' detik',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'load_average' => $this->getLoadAverage(),
        ];

        $database = [
            'driver' => config('database.default'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
            'port' => config('database.connections.' . config('database.default') . '.port'),
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'username' => config('database.connections.' . config('database.default') . '.username'),
            'status' => $this->checkDatabaseStatus(),
            'table_count' => $this->getTableCount(),
        ];

        $application = [
            'app_name' => config('app.name'),
            'app_debug' => config('app.debug') ? 'Aktif' : 'Nonaktif',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mailer' => config('mail.default'),
            'broadcast_driver' => config('broadcasting.default'),
        ];

        $dnsRecords = $this->getDnsRecords($appHost);
        $sslInfo = $appScheme === 'https' ? $this->getSslInfo($appHost) : null;
        $ipLookup = $publicIp ? $this->lookupIp($publicIp) : null;

        return view('admin.settings.server-info', compact(
            'server',
            'resources',
            'database',
            'application',
            'dnsRecords',
            'sslInfo',
            'ipLookup'
        ));
    }

    private function resolveHostIp(string $host): ?string
    {
        $resolved = @gethostbyname($host);

        return $resolved && $resolved !== $host ? $resolved : null;
    }

    private function detectPublicIp(Request $request, ?string $resolvedIp): ?string
    {
        $candidates = [
            $request->server('SERVER_ADDR'),
            $resolvedIp,
            gethostbyname(gethostname() ?: ''),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $candidate;
            }
        }

        return $resolvedIp;
    }

    private function checkDatabaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'Terhubung';
        } catch (\Throwable $e) {
            return 'Gagal: ' . $e->getMessage();
        }
    }

    private function getTableCount(): ?int
    {
        try {
            return count(Schema::getTableListing());
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getLoadAverage(): ?string
    {
        if (!function_exists('sys_getloadavg')) {
            return null;
        }

        $load = sys_getloadavg();

        if (!is_array($load)) {
            return null;
        }

        return implode(' / ', array_map(static fn ($value) => Number::format($value, 2), $load));
    }

    private function formatBytes($bytes): ?string
    {
        if (!is_numeric($bytes) || $bytes <= 0) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = (int) floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $value = $bytes / (1024 ** $pow);

        return Number::format($value, 2) . ' ' . $units[$pow];
    }

    private function getDnsRecords(string $host): array
    {
        try {
            $records = @dns_get_record($host, DNS_A + DNS_AAAA + DNS_CNAME + DNS_MX + DNS_TXT);

            return is_array($records) ? $records : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getSslInfo(string $host): ?array
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $client = @stream_socket_client(
                "ssl://{$host}:443",
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$client) {
                return [
                    'status' => 'Tidak dapat membaca sertifikat',
                    'message' => trim("{$errno} {$errstr}"),
                ];
            }

            $params = stream_context_get_params($client);
            $certificate = $params['options']['ssl']['peer_certificate'] ?? null;
            fclose($client);

            if (!$certificate) {
                return null;
            }

            $parsed = @openssl_x509_parse($certificate);

            if (!is_array($parsed)) {
                return null;
            }

            return [
                'status' => 'Aktif',
                'issuer' => $parsed['issuer']['O'] ?? $parsed['issuer']['CN'] ?? null,
                'valid_from' => isset($parsed['validFrom_time_t']) ? date('d-m-Y H:i:s', $parsed['validFrom_time_t']) : null,
                'valid_to' => isset($parsed['validTo_time_t']) ? date('d-m-Y H:i:s', $parsed['validTo_time_t']) : null,
                'subject' => $parsed['subject']['CN'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'Gagal membaca sertifikat',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function lookupIp(string $ip): ?array
    {
        return Cache::remember("server-info-rdap-{$ip}", now()->addMinutes(30), function () use ($ip) {
            $rdap = $this->lookupRdap($ip);
            $whois = $this->lookupWhois($ip);

            if (!$rdap && !$whois) {
                return null;
            }

            return [
                'rdap' => $rdap,
                'whois' => $whois,
            ];
        });
    }

    private function lookupRdap(string $ip): ?array
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get("https://rdap.org/ip/{$ip}");

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (!is_array($data)) {
                return null;
            }

            return [
                'name' => $data['name'] ?? null,
                'handle' => $data['handle'] ?? null,
                'country' => $data['country'] ?? null,
                'type' => $data['type'] ?? null,
                'start_address' => $data['startAddress'] ?? null,
                'end_address' => $data['endAddress'] ?? null,
                'port43' => $data['port43'] ?? null,
                'entities' => collect($data['entities'] ?? [])
                    ->map(function ($entity) {
                        return [
                            'handle' => $entity['handle'] ?? null,
                            'roles' => implode(', ', $entity['roles'] ?? []),
                        ];
                    })
                    ->filter(fn ($entity) => $entity['handle'] || $entity['roles'])
                    ->values()
                    ->all(),
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    private function lookupWhois(string $ip): ?array
    {
        try {
            if (!function_exists('shell_exec')) {
                return null;
            }

            $binary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where whois' : 'command -v whois';
            $exists = @shell_exec($binary);

            if (!$exists) {
                return null;
            }

            $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                ? 'whois ' . escapeshellarg($ip)
                : 'whois ' . escapeshellarg($ip) . ' 2>&1';

            $output = @shell_exec($command);

            if (!$output) {
                return null;
            }

            $clean = trim($output);

            return [
                'summary' => collect(preg_split("/\r\n|\n|\r/", $clean))
                    ->map(fn ($line) => trim($line))
                    ->filter()
                    ->take(25)
                    ->values()
                    ->all(),
                'raw' => $clean,
            ];
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
