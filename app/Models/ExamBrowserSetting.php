<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExamBrowserSetting extends Model
{
    use HasUuids, SoftDeletes;

    public const ACTIVE_CACHE_KEY = 'exam_browser_settings_active';
    public const ACTIVE_CACHE_TTL = 300;

    /**
     * Path (relative to the "public" disk) of the static config snapshot.
     * Served directly by the web server as a plain file — no PHP/DB hit.
     * Public URL: /storage/exam-browser/config.json
     */
    public const STATIC_CONFIG_PATH = 'exam-browser/config.json';

    protected $table = 'exam_browser_settings';

    protected $fillable = [
        'app_name',
        'app_logo_path',
        'school_name',
        'moodle_url',
        'user_agent',
        'app_password',
        'exit_password',
        'supervisor_password',
        'seb_config_key',
        'seb_exam_key',
        'allow_screenshot',
        'allow_clipboard',
        'allow_navigation',
        'allow_reload',
        'show_toolbar',
        'testing_allow_developer_options',
        'testing_allow_usb_debugging',
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
        'testing_allow_developer_options' => 'boolean',
        'testing_allow_usb_debugging' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $refresh = static function (): void {
            static::clearActiveCache();
            // Always rebuild the static snapshot from the current active record,
            // so the file stays correct regardless of which row was saved.
            static::where('is_active', true)->latest()->first()?->generateStaticConfigFile();
        };

        static::saved($refresh);
        static::deleted($refresh);
        static::restored($refresh);
        static::forceDeleted($refresh);
    }

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
        return Cache::remember(self::ACTIVE_CACHE_KEY, self::ACTIVE_CACHE_TTL, function () {
            return static::where('is_active', true)->latest()->first();
        });
    }

    public static function clearActiveCache(): void
    {
        Cache::forget(self::ACTIVE_CACHE_KEY);
    }

    /**
     * Build the static config payload consumed by the ExaManmet app.
     *
     * Passwords are NEVER included in plaintext — only their bcrypt hashes,
     * so the app can verify offline without the secret ever leaving the server.
     */
    public function toStaticConfig(): array
    {
        return [
            'app_name' => $this->app_name,
            'school_name' => $this->school_name,
            'logo_url' => $this->logo_url,
            'moodle_url' => $this->moodle_url,
            'user_agent' => $this->user_agent,
            'app_password_hash' => $this->hashOrNull($this->app_password),
            'exit_password_hash' => $this->hashOrNull($this->exit_password),
            'supervisor_password_hash' => $this->hashOrNull($this->supervisor_password),
            'seb_config_key' => $this->seb_config_key,
            'seb_exam_key' => $this->seb_exam_key,
            'allow_screenshot' => (bool) $this->allow_screenshot,
            'allow_clipboard' => (bool) $this->allow_clipboard,
            'allow_navigation' => (bool) $this->allow_navigation,
            'allow_reload' => (bool) $this->allow_reload,
            'is_active' => (bool) $this->is_active,
            'show_toolbar' => (bool) $this->show_toolbar,
            'testing_allow_developer_options' => (bool) $this->testing_allow_developer_options,
            'testing_allow_usb_debugging' => (bool) $this->testing_allow_usb_debugging,
            'allowed_urls' => $this->allowed_urls_array,
            'blocked_apps' => $this->blocked_apps_array,
            'custom_css' => $this->custom_css,
            'custom_js' => $this->custom_js,
            'minimum_app_version' => $this->minimum_app_version,
            'announcement' => $this->announcement,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * bcrypt-hash a password, or return null when no password is set.
     */
    protected function hashOrNull(?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }
        // Force the bcrypt driver explicitly — the app verifies bcrypt hashes,
        // regardless of the app-wide default hashing driver.
        return Hash::driver('bcrypt')->make($plain);
    }

    /**
     * Write the static config snapshot to the public disk.
     * The web server then serves it as a plain static file (no PHP/DB).
     */
    public function generateStaticConfigFile(): bool
    {
        try {
            return Storage::disk('public')->put(
                self::STATIC_CONFIG_PATH,
                json_encode(
                    $this->toStaticConfig(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            ) !== false;
        } catch (\Throwable $e) {
            Log::error('[ExamBrowser] Gagal menulis file config statis: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Public URL of the static config snapshot.
     */
    public static function staticConfigUrl(): string
    {
        return Storage::disk('public')->url(self::STATIC_CONFIG_PATH);
    }

    /**
     * Last time the static snapshot file was written, or null if missing.
     */
    public static function staticConfigGeneratedAt(): ?\Illuminate\Support\Carbon
    {
        $disk = Storage::disk('public');
        if (!$disk->exists(self::STATIC_CONFIG_PATH)) {
            return null;
        }
        return \Illuminate\Support\Carbon::createFromTimestamp(
            $disk->lastModified(self::STATIC_CONFIG_PATH)
        );
    }
}
