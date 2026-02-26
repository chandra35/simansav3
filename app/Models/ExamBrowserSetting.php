<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ExamBrowserSetting extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'exam_browser_settings';

    protected $fillable = [
        'app_name',
        'app_logo_path',
        'school_name',
        'moodle_url',
        'user_agent',
        'app_password',
        'exit_password',
        'seb_config_key',
        'seb_exam_key',
        'allow_screenshot',
        'allow_clipboard',
        'allow_navigation',
        'allow_reload',
        'show_toolbar',
        'is_active',
        'allowed_urls',
        'blocked_apps',
        'custom_css',
        'custom_js',
        'minimum_app_version',
        'announcement',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allow_screenshot' => 'boolean',
        'allow_clipboard' => 'boolean',
        'allow_navigation' => 'boolean',
        'allow_reload' => 'boolean',
        'show_toolbar' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->app_logo_path) {
            return Storage::disk('public')->url($this->app_logo_path);
        }
        return null;
    }

    /**
     * Get allowed URLs as array
     */
    public function getAllowedUrlsArrayAttribute(): array
    {
        if ($this->allowed_urls) {
            return json_decode($this->allowed_urls, true) ?? [];
        }
        return [];
    }

    /**
     * Get blocked apps as array
     */
    public function getBlockedAppsArrayAttribute(): array
    {
        if ($this->blocked_apps) {
            return json_decode($this->blocked_apps, true) ?? [];
        }
        return [];
    }

    /**
     * Get active settings (singleton pattern - only one active config)
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }

    /**
     * Format config for API response (to be consumed by mobile app)
     */
    public function toApiConfig(): array
    {
        return [
            'app_name' => $this->app_name,
            'school_name' => $this->school_name,
            'logo_url' => $this->logo_url,
            'moodle_url' => $this->moodle_url,
            'user_agent' => $this->user_agent,
            'app_password' => $this->app_password,
            'exit_password' => $this->exit_password,
            'seb_config_key' => $this->seb_config_key,
            'seb_exam_key' => $this->seb_exam_key,
            'allow_screenshot' => $this->allow_screenshot,
            'allow_clipboard' => $this->allow_clipboard,
            'allow_navigation' => $this->allow_navigation,
            'allow_reload' => $this->allow_reload,
            'show_toolbar' => $this->show_toolbar,
            'allowed_urls' => $this->allowed_urls_array,
            'blocked_apps' => $this->blocked_apps_array,
            'custom_css' => $this->custom_css,
            'custom_js' => $this->custom_js,
            'minimum_app_version' => $this->minimum_app_version,
            'announcement' => $this->announcement,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
