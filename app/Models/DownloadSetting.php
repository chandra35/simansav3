<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DownloadSetting extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'download_settings';

    protected $fillable = [
        'default_storage',
        'gdrive_auth_mode',
        'gdrive_root_folder_id',
        'gdrive_credentials_path',
        'gdrive_make_public',
        'gdrive_oauth_client_id',
        'gdrive_oauth_client_secret',
        'gdrive_oauth_refresh_token',
        'gdrive_oauth_email',
    ];

    protected $casts = [
        'gdrive_make_public' => 'boolean',
    ];

    protected $hidden = [
        'gdrive_oauth_client_secret',
        'gdrive_oauth_refresh_token',
    ];

    public static function getInstance(): self
    {
        $instance = self::first();

        if (!$instance) {
            $instance = self::create([
                'default_storage' => 'local',
                'gdrive_auth_mode' => 'service_account',
                'gdrive_make_public' => true,
            ]);
        }

        return $instance;
    }

    public function setGdriveOauthClientSecretAttribute(?string $value): void
    {
        if (empty($value)) {
            return;
        }

        $this->attributes['gdrive_oauth_client_secret'] = Crypt::encryptString($value);
    }

    public function getGdriveOauthClientSecretAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setGdriveOauthRefreshTokenAttribute(?string $value): void
    {
        if (empty($value)) {
            return;
        }

        $this->attributes['gdrive_oauth_refresh_token'] = Crypt::encryptString($value);
    }

    public function getGdriveOauthRefreshTokenAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function isGoogleDriveConfigured(): bool
    {
        if (empty($this->gdrive_root_folder_id)) {
            return false;
        }

        if ($this->gdrive_auth_mode === 'oauth') {
            return !empty($this->gdrive_oauth_client_id)
                && !empty($this->gdrive_oauth_client_secret)
                && !empty($this->gdrive_oauth_refresh_token);
        }

        return !empty($this->gdrive_credentials_path);
    }
}
