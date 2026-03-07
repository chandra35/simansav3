<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AbsensiLog extends Model
{
    protected $table = 'absensi_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'absensi_id', 'user_id', 'action',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'reason',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a log entry for an absensi action
     */
    public static function record(
        string $absensiId,
        string $userId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null
    ): self {
        return static::create([
            'absensi_id' => $absensiId,
            'user_id' => $userId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => $reason,
        ]);
    }
}
