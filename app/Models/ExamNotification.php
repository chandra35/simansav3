<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ExamNotification extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const ACTIVE_API_CACHE_KEY = 'exam_notifications_active_api';
    public const ACTIVE_API_CACHE_TTL = 60;

    protected $fillable = [
        'title',
        'message',
        'type',
        'target',
        'sent_by',
        'scheduled_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $clearCache = static function (): void {
            static::clearActiveApiCache();
        };

        static::saved($clearCache);
        static::deleted($clearCache);
        static::restored($clearCache);
        static::forceDeleted($clearCache);
    }

    /**
     * Get the user who sent this notification
     */
    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_by');
    }

    /**
     * Scope: only active and not expired notifications
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    /**
     * Scope: notifications newer than a given timestamp
     */
    public function scopeNewerThan($query, $timestamp)
    {
        return $query->where('created_at', '>', $timestamp);
    }

    public static function getActiveForApi(): Collection
    {
        return Cache::remember(self::ACTIVE_API_CACHE_KEY, self::ACTIVE_API_CACHE_TTL, function () {
            return static::active()
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        });
    }

    public static function clearActiveApiCache(): void
    {
        Cache::forget(self::ACTIVE_API_CACHE_KEY);
    }

    /**
     * Convert to API format
     */
    public function toApiFormat(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'target' => $this->target,
            'created_at' => $this->created_at->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
