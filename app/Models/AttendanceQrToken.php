<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceQrToken extends Model
{
    use HasUuids;

    public const CONTEXT_DAILY = 'daily';
    public const CONTEXT_SHOLAT = 'sholat';

    protected $table = 'attendance_qr_tokens';

    protected $fillable = [
        'context',
        'absensi_siswa_session_id',
        'sholat_session_id',
        'token_hash',
        'expires_at',
        'issued_by',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(AbsensiSiswaSession::class, 'absensi_siswa_session_id');
    }

    public function sholatSession(): BelongsTo
    {
        return $this->belongsTo(SholatSession::class, 'sholat_session_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at')->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }
}
