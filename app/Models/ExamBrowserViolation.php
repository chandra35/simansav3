<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamBrowserViolation extends Model
{
    use HasUuids;

    protected $table = 'exam_browser_violations';

    protected $fillable = [
        'session_id',
        'siswa_id',
        'violation_type',
        'violation_detail',
        'device_id',
        'ip_address',
    ];

    // Valid violation types
    public const TYPES = [
        'app_switch',
        'bluetooth',
        'developer_mode',
        'usb_debugging',
        'root_detected',
        'split_screen',
        'pip',
        'headset',
        'adware_keyboard',
        'floating_app',
        'accessibility_abuse',
        'screen_capture',
        'other',
    ];

    // ==================== Relationships ====================

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamBrowserSession::class, 'session_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    // ==================== Helpers ====================

    /**
     * Get human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->violation_type) {
            'app_switch' => 'Keluar Aplikasi',
            'bluetooth' => 'Bluetooth Aktif',
            'developer_mode' => 'Developer Mode',
            'usb_debugging' => 'USB Debugging',
            'root_detected' => 'Root Terdeteksi',
            'split_screen' => 'Split Screen',
            'pip' => 'Picture-in-Picture',
            'headset' => 'Headset Terpasang',
            'adware_keyboard' => 'Keyboard Adware',
            'floating_app' => 'Aplikasi Mengambang',
            'accessibility_abuse' => 'Accessibility Abuse',
            'screen_capture' => 'Screen Capture',
            default => ucfirst(str_replace('_', ' ', $this->violation_type)),
        };
    }

    /**
     * Get severity badge color
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->violation_type) {
            'app_switch', 'screen_capture' => 'danger',
            'developer_mode', 'usb_debugging', 'root_detected' => 'danger',
            'bluetooth', 'headset' => 'warning',
            'adware_keyboard', 'floating_app', 'accessibility_abuse' => 'warning',
            'split_screen', 'pip' => 'info',
            default => 'secondary',
        };
    }
}
