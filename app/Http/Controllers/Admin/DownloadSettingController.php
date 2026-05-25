<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadSetting;
use App\Services\DownloadStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadSettingController extends Controller
{
    public function __construct(private DownloadStorageService $storageService)
    {
    }

    public function edit()
    {
        $setting = DownloadSetting::getInstance();

        return view('admin.download_settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = DownloadSetting::getInstance();

        $validated = $request->validate([
            'default_storage' => 'required|in:local,gdrive',
            'gdrive_auth_mode' => 'required|in:service_account,oauth',
            'gdrive_root_folder_id' => 'nullable|string|max:255',
            'gdrive_make_public' => 'nullable|boolean',
            'gdrive_credentials_file' => 'nullable|file|mimes:json,txt|max:1024',
            'gdrive_oauth_client_id' => 'nullable|string|max:255',
            'gdrive_oauth_client_secret' => 'nullable|string|max:2000',
            'gdrive_oauth_refresh_token' => 'nullable|string|max:2000',
            'gdrive_oauth_email' => 'nullable|email|max:255',
        ]);

        $payload = [
            'default_storage' => $validated['default_storage'],
            'gdrive_auth_mode' => $validated['gdrive_auth_mode'],
            'gdrive_root_folder_id' => $validated['gdrive_root_folder_id'] ?? null,
            'gdrive_make_public' => $request->boolean('gdrive_make_public', true),
            'gdrive_oauth_client_id' => $validated['gdrive_oauth_client_id'] ?? null,
            'gdrive_oauth_email' => $validated['gdrive_oauth_email'] ?? null,
        ];

        if ($request->filled('gdrive_oauth_client_secret')) {
            $payload['gdrive_oauth_client_secret'] = $request->input('gdrive_oauth_client_secret');
        }

        if ($request->filled('gdrive_oauth_refresh_token')) {
            $payload['gdrive_oauth_refresh_token'] = $request->input('gdrive_oauth_refresh_token');
        }

        if ($request->hasFile('gdrive_credentials_file')) {
            if ($setting->gdrive_credentials_path && Storage::disk('local')->exists($setting->gdrive_credentials_path)) {
                Storage::disk('local')->delete($setting->gdrive_credentials_path);
            }

            $file = $request->file('gdrive_credentials_file');
            $path = $file->storeAs('download-settings/gdrive', 'service-account-' . now()->format('YmdHis') . '.json', 'local');
            $payload['gdrive_credentials_path'] = $path;
        }

        $setting->update($payload);

        return back()->with('success', 'Pengaturan Download Center berhasil disimpan.');
    }

    public function testConnection()
    {
        $setting = DownloadSetting::getInstance();

        try {
            $result = $this->storageService->testGoogleDriveConnection($setting);

            return back()->with('success', 'Koneksi Google Drive berhasil. Folder: ' . ($result['name'] ?? '-'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Koneksi Google Drive gagal: ' . $e->getMessage());
        }
    }
}
