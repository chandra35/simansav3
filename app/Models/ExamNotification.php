<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamNotification extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
