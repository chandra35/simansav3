<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PollingResponse extends Model
{
    use HasUuid;

    protected $fillable = [
        'polling_id', 'user_id', 'respondent_type', 'respondent_id', 'respondent_name',
        'class_id', 'class_name', 'grade', 'submitted_at', 'locked_at',
        'unlock_requested_at', 'unlocked_at', 'unlocked_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'unlock_requested_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function polling() { return $this->belongsTo(Polling::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(PollingAnswer::class); }
    public function unlockedBy() { return $this->belongsTo(User::class, 'unlocked_by'); }

    public function isUnlocked(): bool
    {
        return $this->unlocked_at !== null;
    }

    public function isLocked(): bool
    {
        return ! $this->isUnlocked();
    }
}
