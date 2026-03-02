<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamBrowserSession extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'exam_browser_sessions';

    protected $fillable = [
        'siswa_id',
        'device_id',
        'device_model',
        'moodle_username',
        'moodle_fullname',
        'app_version',
        'os_version',
        'is_locked',
        'locked_by',
        'lock_reason',
        'locked_at',
        'last_heartbeat',
        'ip_address',
        'violation_count',
        'is_active',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'locked_at' => 'datetime',
        'last_heartbeat' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'violation_count' => 'integer',
    ];

    // ==================== Relationships ====================

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(ExamBrowserViolation::class, 'session_id');
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline($query)
    {
        // Consider "online" if heartbeat within last 60 seconds
        return $query->where('last_heartbeat', '>=', now()->subSeconds(60));
    }

    public function scopeIdle($query)
    {
        // "Idle" = heartbeat 60s-120s ago
        return $query->where('last_heartbeat', '<', now()->subSeconds(60))
                     ->where('last_heartbeat', '>=', now()->subSeconds(120));
    }

    public function scopeOffline($query)
    {
        // "Offline" = no heartbeat for 120+ seconds
        return $query->where(function ($q) {
            $q->where('last_heartbeat', '<', now()->subSeconds(120))
              ->orWhereNull('last_heartbeat');
        });
    }

    // ==================== Helpers ====================

    /**
     * Get online status indicator
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) return 'ended';
        if (!$this->last_heartbeat) return 'offline';

        $seconds = now()->diffInSeconds($this->last_heartbeat);
        if ($seconds <= 60) return 'online';
        if ($seconds <= 120) return 'idle';
        return 'offline';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'online' => 'success',
            'idle' => 'warning',
            'offline' => 'danger',
            'ended' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status label for UI
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'online' => 'Online',
            'idle' => 'Idle',
            'offline' => 'Offline',
            'ended' => 'Selesai',
            default => 'Unknown',
        };
    }

    /**
     * Lock this session
     */
    public function lockSession(string $reason, ?string $lockedBy = null): void
    {
        $this->update([
            'is_locked' => true,
            'lock_reason' => $reason,
            'locked_by' => $lockedBy,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock this session
     */
    public function unlockSession(): void
    {
        $this->update([
            'is_locked' => false,
            'lock_reason' => null,
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * End this session
     */
    public function endSession(): void
    {
        $this->update([
            'is_active' => false,
            'ended_at' => now(),
        ]);
    }

    /**
     * Try to match siswa from moodle_username (NISN)
     */
    public static function matchSiswa(?string $moodleUsername): ?string
    {
        if (empty($moodleUsername)) return null;

        // Try matching by NISN first
        $siswa = Siswa::where('nisn', $moodleUsername)->first();
        if ($siswa) return $siswa->id;

        // Try matching by user username
        $siswa = Siswa::whereHas('user', function ($q) use ($moodleUsername) {
            $q->where('username', $moodleUsername);
        })->first();
        if ($siswa) return $siswa->id;

        return null;
    }

    /**
     * Format for API response
     */
    public function toApiResponse(): array
    {
        return [
            'session_id' => $this->id,
            'is_locked' => $this->is_locked,
            'lock_reason' => $this->lock_reason,
            'violation_count' => $this->violation_count,
        ];
    }
}
