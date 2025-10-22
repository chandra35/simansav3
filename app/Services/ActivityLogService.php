<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Torann\GeoIP\Facades\GeoIP;

class ActivityLogService
{
    /**
     * Log activity with enhanced details
     */
    public static function log(array $data)
    {
        $agent = new Agent();
        
        // Get real IP address (even behind proxy/cloudflare)
        $ip = self::getRealIpAddress();
        
        // Check if device location provided (from GPS)
        $hasDeviceLocation = isset($data['latitude']) && isset($data['longitude']);
        
        // If device location provided, use reverse geocoding
        if ($hasDeviceLocation) {
            try {
                $location = self::reverseGeocode($data['latitude'], $data['longitude']);
            } catch (\Exception $e) {
                $location = null;
            }
        } else {
            // Fallback to IP-based location
            try {
                $geoip = GeoIP::getLocation($ip);
                $location = [
                    'country' => $geoip->country ?? null,
                    'country_code' => $geoip->iso_code ?? null,
                    'region' => $geoip->state_name ?? null,
                    'city' => $geoip->city ?? null,
                    'postal_code' => $geoip->postal_code ?? null,
                    'latitude' => $geoip->lat ?? null,
                    'longitude' => $geoip->lon ?? null,
                    'timezone' => $geoip->timezone ?? null,
                ];
            } catch (\Exception $e) {
                $location = null;
            }
        }
        
        $logData = array_merge([
            'user_id' => Auth::id(),
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            
            // Device & Browser Info
            'device_type' => self::getDeviceType($agent),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'platform' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
            
            // Geo Location (prioritize device location over IP location)
            'country' => $location['country'] ?? null,
            'country_code' => $location['country_code'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'postal_code' => $location['postal_code'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'timezone' => $location['timezone'] ?? null,
            
            // Request Info
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ], $data);
        
        return ActivityLog::create($logData);
    }
    
    /**
     * Log data changes with before/after values
     */
    public static function logChanges($activityType, $model, $oldData, $newData, $description = null)
    {
        $changes = self::detectChanges($oldData, $newData);
        
        if (empty($changes['changed'])) {
            return null; // No changes detected
        }
        
        return self::log([
            'activity_type' => $activityType,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? "Updated " . class_basename($model),
            'old_values' => $changes['old'],
            'new_values' => $changes['new'],
            'changed_fields' => $changes['changed'],
        ]);
    }
    
    /**
     * Detect changes between old and new data
     */
    private static function detectChanges($oldData, $newData)
    {
        $changed = [];
        $old = [];
        $new = [];
        
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData) || $oldData[$key] != $value) {
                $changed[] = $key;
                $old[$key] = $oldData[$key] ?? null;
                $new[$key] = $value;
            }
        }
        
        return [
            'changed' => $changed,
            'old' => $old,
            'new' => $new,
        ];
    }
    
    /**
     * Get device type
     */
    private static function getDeviceType($agent)
    {
        if ($agent->isDesktop()) {
            return 'desktop';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isMobile()) {
            return 'mobile';
        }
        return 'unknown';
    }
    
    /**
     * Get real IP address (even behind proxy/load balancer)
     */
    private static function getRealIpAddress()
    {
        // Check for IP from shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Check for IP passed from proxy (CloudFlare, etc)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // CloudFlare specific
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        
        // Standard remote address
        $ip = $_SERVER['REMOTE_ADDR'] ?? request()->ip();
        
        // For development/localhost, try to get public IP from external service
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            try {
                // Try to get public IP (with timeout)
                $context = stream_context_create(['http' => ['timeout' => 2]]);
                $publicIp = @file_get_contents('https://api.ipify.org', false, $context);
                
                if ($publicIp && filter_var($publicIp, FILTER_VALIDATE_IP)) {
                    return $publicIp;
                }
            } catch (\Exception $e) {
                // Fallback to localhost IP
            }
        }
        
        return $ip;
    }
    
    /**
     * Reverse geocode coordinates to location info
     * Using Nominatim (OpenStreetMap) - Free, no API key required
     */
    private static function reverseGeocode($latitude, $longitude)
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => 'SIMANSA Activity Logger/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['address'])) {
                    $address = $data['address'];
                    
                    return [
                        'country' => $address['country'] ?? null,
                        'country_code' => strtoupper($address['country_code'] ?? ''),
                        'region' => $address['state'] ?? $address['province'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null,
                        'postal_code' => $address['postcode'] ?? null,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'timezone' => self::getTimezoneFromCoordinates($latitude, $longitude),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Reverse geocoding failed', [
                'lat' => $latitude,
                'lon' => $longitude,
                'error' => $e->getMessage()
            ]);
        }
        
        // Fallback: return coordinates only
        return [
            'country' => null,
            'country_code' => null,
            'region' => null,
            'city' => null,
            'postal_code' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => self::getTimezoneFromCoordinates($latitude, $longitude),
        ];
    }
    
    /**
     * Get timezone from coordinates
     */
    private static function getTimezoneFromCoordinates($latitude, $longitude)
    {
        // Indonesia timezone mapping based on coordinates
        if ($longitude >= 95 && $longitude <= 141) { // Indonesia range
            if ($longitude >= 95 && $longitude <= 105) {
                return 'Asia/Jakarta'; // WIB
            } elseif ($longitude > 105 && $longitude <= 120) {
                return 'Asia/Makassar'; // WITA
            } else {
                return 'Asia/Jayapura'; // WIT
            }
        }
        
        return 'Asia/Jakarta'; // Default
    }
    
    /**
     * Log login activity
     */
    public static function logLogin($userId = null)
    {
        return self::log([
            'user_id' => $userId ?? Auth::id(),
            'activity_type' => 'login',
            'description' => 'User logged in',
        ]);
    }
    
    /**
     * Log logout activity
     */
    public static function logLogout()
    {
        return self::log([
            'activity_type' => 'logout',
            'description' => 'User logged out',
        ]);
    }
    
    /**
     * Log upload activity
     */
    public static function logUpload($fileType, $fileName, $model = null)
    {
        return self::log([
            'activity_type' => 'upload',
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => "Uploaded {$fileType}: {$fileName}",
        ]);
    }
    
    /**
     * Log delete activity
     */
    public static function logDelete($model, $description = null)
    {
        return self::log([
            'activity_type' => 'delete',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? "Deleted " . class_basename($model),
            'old_values' => $model->toArray(),
        ]);
    }
    
    /**
     * Get human readable device icon
     */
    public static function getDeviceIcon($deviceType)
    {
        return match($deviceType) {
            'mobile' => '<i class="fas fa-mobile-alt text-primary"></i>',
            'tablet' => '<i class="fas fa-tablet-alt text-info"></i>',
            'desktop' => '<i class="fas fa-desktop text-success"></i>',
            default => '<i class="fas fa-question-circle text-muted"></i>',
        };
    }
    
    /**
     * Get human readable browser icon
     */
    public static function getBrowserIcon($browser)
    {
        $browser = strtolower($browser);
        
        if (str_contains($browser, 'chrome')) {
            return '<i class="fab fa-chrome text-warning"></i>';
        } elseif (str_contains($browser, 'firefox')) {
            return '<i class="fab fa-firefox text-danger"></i>';
        } elseif (str_contains($browser, 'safari')) {
            return '<i class="fab fa-safari text-primary"></i>';
        } elseif (str_contains($browser, 'edge')) {
            return '<i class="fab fa-edge text-info"></i>';
        } elseif (str_contains($browser, 'opera')) {
            return '<i class="fab fa-opera text-danger"></i>';
        }
        
        return '<i class="fas fa-globe text-muted"></i>';
    }
}
