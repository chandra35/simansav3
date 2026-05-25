<?php

namespace App\Services;

use App\Models\DownloadSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DownloadStorageService
{
    public function __construct(private GoogleDriveService $googleDriveService)
    {
    }

    public function storeUploadedFile(UploadedFile $file, string $source, ?DownloadSetting $setting = null): array
    {
        $setting = $setting ?? DownloadSetting::getInstance();

        if ($source === 'gdrive') {
            try {
                return $this->storeToGoogleDrive($file, $setting);
            } catch (\Throwable $e) {
                // Fallback lokal agar upload tetap berhasil ketika Google Drive bermasalah.
                return $this->storeToLocal($file);
            }
        }

        return $this->storeToLocal($file);
    }

    public function replaceUploadedFile(array $oldMeta, UploadedFile $newFile, string $source, ?DownloadSetting $setting = null): array
    {
        $this->deleteByMeta($oldMeta, $setting);

        return $this->storeUploadedFile($newFile, $source, $setting);
    }

    public function deleteByMeta(array $meta, ?DownloadSetting $setting = null): void
    {
        $source = $meta['source'] ?? 'local';

        if ($source === 'gdrive' && !empty($meta['gdrive_file_id'])) {
            $setting = $setting ?? DownloadSetting::getInstance();
            $credentials = $this->loadGoogleDriveCredentials($setting);

            if ($credentials) {
                $this->googleDriveService->deleteFile($credentials, $meta['gdrive_file_id']);
            }

            return;
        }

        if (!empty($meta['local_disk']) && !empty($meta['local_path'])) {
            Storage::disk($meta['local_disk'])->delete($meta['local_path']);
        }
    }

    public function testGoogleDriveConnection(?DownloadSetting $setting = null): array
    {
        $setting = $setting ?? DownloadSetting::getInstance();
        $credentials = $this->loadGoogleDriveCredentials($setting);

        if (!$credentials || empty($setting->gdrive_root_folder_id)) {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap.');
        }

        return $this->googleDriveService->testConnection($credentials, $setting->gdrive_root_folder_id);
    }

    private function storeToLocal(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($baseName ?: 'file-download');
        $fileName = $safeName . '-' . time() . '-' . Str::lower(Str::random(6)) . '.' . $extension;

        $path = $file->storeAs('downloads/' . now()->format('Y/m'), $fileName, 'public');

        return [
            'source' => 'local',
            'local_disk' => 'public',
            'local_path' => $path,
            'gdrive_file_id' => null,
            'gdrive_file_url' => null,
            'file_name_original' => $file->getClientOriginalName(),
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize() ?: 0,
        ];
    }

    private function storeToGoogleDrive(UploadedFile $file, DownloadSetting $setting): array
    {
        $credentials = $this->loadGoogleDriveCredentials($setting);
        if (!$credentials) {
            throw new RuntimeException('Credential Google Drive belum dikonfigurasi.');
        }

        if (empty($setting->gdrive_root_folder_id)) {
            throw new RuntimeException('Google Drive Root Folder ID wajib diisi.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($baseName ?: 'file-download');
        $fileName = $safeName . '-' . time() . '.' . $extension;

        $upload = $this->googleDriveService->uploadFile(
            credentials: $credentials,
            rootFolderId: $setting->gdrive_root_folder_id,
            folderSegments: ['Simansa-Downloads', now()->format('Y-m')],
            fileName: $fileName,
            mimeType: $file->getMimeType() ?: 'application/octet-stream',
            content: $file->get(),
            makePublic: (bool) $setting->gdrive_make_public
        );

        return [
            'source' => 'gdrive',
            'local_disk' => 'public',
            'local_path' => null,
            'gdrive_file_id' => $upload['remote_file_id'] ?? null,
            'gdrive_file_url' => $upload['remote_file_url'] ?? null,
            'file_name_original' => $file->getClientOriginalName(),
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize() ?: 0,
        ];
    }

    private function loadGoogleDriveCredentials(DownloadSetting $setting): ?array
    {
        if ($setting->gdrive_auth_mode === 'oauth') {
            if (
                empty($setting->gdrive_oauth_client_id)
                || empty($setting->gdrive_oauth_client_secret)
                || empty($setting->gdrive_oauth_refresh_token)
            ) {
                return null;
            }

            return [
                'auth_type' => 'oauth',
                'gdrive_oauth_client_id' => $setting->gdrive_oauth_client_id,
                'gdrive_oauth_client_secret' => $setting->gdrive_oauth_client_secret,
                'gdrive_oauth_refresh_token' => $setting->gdrive_oauth_refresh_token,
                'oauth_email' => $setting->gdrive_oauth_email,
            ];
        }

        if (empty($setting->gdrive_credentials_path)) {
            return null;
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($setting->gdrive_credentials_path)) {
            return null;
        }

        $decoded = json_decode($disk->get($setting->gdrive_credentials_path), true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['private_key'])) {
            $decoded['private_key'] = str_replace("\\n", "\n", $decoded['private_key']);
        }

        return $decoded;
    }
}
