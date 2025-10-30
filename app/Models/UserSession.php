<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'last_activity',
        'is_online'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_online' => 'boolean'
    ];

    /**
     * Relation to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk filter online users
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true)
                    ->where('last_activity', '>=', Carbon::now()->subMinutes(5));
    }

    /**
     * Scope untuk filter user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Parse user agent dan update device info
     */
    public static function parseUserAgent($userAgent)
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $browser = $agent->browser();
        $platform = $agent->platform();

        return [
            'device_type' => $agent->isDesktop() ? 'desktop' : ($agent->isMobile() ? 'mobile' : 'tablet'),
            'browser' => $browser ?: 'Unknown',
            'platform' => $platform ?: 'Unknown'
        ];
    }

    /**
     * Update atau create session
     */
    public static function updateOrCreateSession($user, $request)
    {
        try {
            $sessionId = session()->getId();
            
            // Jika session ID kosong, generate baru
            if (empty($sessionId)) {
                session()->regenerate();
                $sessionId = session()->getId();
            }
            
            $userAgent = $request->userAgent();
            $deviceInfo = self::parseUserAgent($userAgent);

            return self::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ],
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,
                    'device_type' => $deviceInfo['device_type'],
                    'browser' => $deviceInfo['browser'],
                    'platform' => $deviceInfo['platform'],
                    'last_activity' => Carbon::now(),
                    'is_online' => true
                ]
            );
        } catch (\Exception $e) {
            // Log error tapi jangan break aplikasi
            Log::error('Failed to track user session: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tandai session sebagai offline
     */
    public function markOffline()
    {
        $this->update(['is_online' => false]);
    }

    /**
     * Check apakah user masih online (aktif dalam 5 menit terakhir)
     */
    public function isStillOnline()
    {
        return $this->is_online && 
               $this->last_activity >= Carbon::now()->subMinutes(5);
    }

    /**
     * Get formatted last activity
     */
    public function getLastActivityHumanAttribute()
    {
        return $this->last_activity?->diffForHumans();
    }

    /**
     * Get device icon
     */
    public function getDeviceIconAttribute()
    {
        return match($this->device_type) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            default => 'fas fa-desktop'
        };
    }

    /**
     * Get browser icon
     */
    public function getBrowserIconAttribute()
    {
        $browser = strtolower($this->browser ?? '');
        
        if (str_contains($browser, 'chrome')) return 'fab fa-chrome';
        if (str_contains($browser, 'firefox')) return 'fab fa-firefox';
        if (str_contains($browser, 'safari')) return 'fab fa-safari';
        if (str_contains($browser, 'edge')) return 'fab fa-edge';
        if (str_contains($browser, 'opera')) return 'fab fa-opera';
        
        return 'fas fa-globe';
    }

    /**
     * Get platform icon
     */
    public function getPlatformIconAttribute()
    {
        $platform = strtolower($this->platform ?? '');
        
        if (str_contains($platform, 'windows')) return 'fab fa-windows';
        if (str_contains($platform, 'mac') || str_contains($platform, 'ios')) return 'fab fa-apple';
        if (str_contains($platform, 'android')) return 'fab fa-android';
        if (str_contains($platform, 'linux')) return 'fab fa-linux';
        
        return 'fas fa-question-circle';
    }
}
