<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmailLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'to_email',
        'to_name',
        'from_email',
        'from_name',
        'subject',
        'body',
        'type',
        'status',
        'error_message',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who sent the email
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'password_reset' => 'Reset Password',
            'notification' => 'Notifikasi',
            'test' => 'Test Email',
            'general' => 'Umum',
            default => ucfirst($this->type),
        };
    }

    /**
     * Log a sent email
     */
    public static function logEmail($to, $subject, $body = null, $type = 'general', $status = 'sent', $error = null)
    {
        $settings = AppSetting::getInstance();
        
        return self::create([
            'to_email' => is_array($to) ? $to['email'] : $to,
            'to_name' => is_array($to) ? ($to['name'] ?? null) : null,
            'from_email' => $settings->smtp_from_address,
            'from_name' => $settings->smtp_from_name,
            'subject' => $subject,
            'body' => $body,
            'type' => $type,
            'status' => $status,
            'error_message' => $error,
            'sent_by' => auth()->id(),
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }
}
