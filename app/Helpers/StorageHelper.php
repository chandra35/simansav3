<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get writable disk for dokumen siswa
     * 
     * @return string Disk name ('dokumen' or 'dokumen_fallback')
     */
    public static function getDokumenDisk(): string
    {
        $primaryDisk = 'dokumen';
        $fallbackDisk = 'dokumen_fallback';
        
        // Check if writable check is disabled in config
        if (!config('simansa.dokumen_storage.check_writable', true)) {
            return $primaryDisk;
        }
        
        // Check if primary disk is writable
        try {
            $testFile = '.writable_test_' . time();
            $testContent = 'test';
            
            Storage::disk($primaryDisk)->put($testFile, $testContent);
            
            // Verify file was written
            if (Storage::disk($primaryDisk)->exists($testFile)) {
                Storage::disk($primaryDisk)->delete($testFile);
                return $primaryDisk;
            }
            
            throw new \Exception('File write verification failed');
            
        } catch (\Exception $e) {
            // Log warning if configured
            if (config('simansa.dokumen_storage.log_fallback', true)) {
                Log::warning('Primary dokumen storage not writable, using fallback', [
                    'error' => $e->getMessage(),
                    'primary_path' => config('filesystems.disks.dokumen.root'),
                    'fallback_path' => config('filesystems.disks.dokumen_fallback.root'),
                ]);
            }
            
            return $fallbackDisk;
        }
    }
    
    /**
     * Ensure storage folder exists and is writable
     * 
     * @param string|null $disk Disk name, default to auto-detect
     * @return bool Success status
     */
    public static function ensureStorageExists(?string $disk = null): bool
    {
        $disk = $disk ?? self::getDokumenDisk();
        
        try {
            // Create directory if not exists
            if (!Storage::disk($disk)->exists('/')) {
                Storage::disk($disk)->makeDirectory('/', 0755, true);
            }
            
            // Verify writable
            $testFile = '.storage_check_' . time();
            Storage::disk($disk)->put($testFile, 'test');
            
            if (Storage::disk($disk)->exists($testFile)) {
                Storage::disk($disk)->delete($testFile);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to ensure dokumen storage exists', [
                'disk' => $disk,
                'error' => $e->getMessage(),
                'path' => config("filesystems.disks.{$disk}.root"),
            ]);
            
            return false;
        }
    }
    
    /**
     * Get storage info for monitoring/debugging
     * 
     * @return array Storage status info
     */
    public static function getStorageInfo(): array
    {
        $primaryDisk = 'dokumen';
        $fallbackDisk = 'dokumen_fallback';
        $activeDisk = self::getDokumenDisk();
        
        $info = [
            'active_disk' => $activeDisk,
            'primary' => [
                'disk' => $primaryDisk,
                'path' => config('filesystems.disks.dokumen.root'),
                'writable' => false,
                'exists' => false,
            ],
            'fallback' => [
                'disk' => $fallbackDisk,
                'path' => config('filesystems.disks.dokumen_fallback.root'),
                'writable' => false,
                'exists' => false,
            ],
        ];
        
        // Check primary
        try {
            $primaryPath = config('filesystems.disks.dokumen.root');
            $info['primary']['exists'] = file_exists($primaryPath);
            $info['primary']['writable'] = is_writable($primaryPath);
        } catch (\Exception $e) {
            // Ignore
        }
        
        // Check fallback
        try {
            $fallbackPath = config('filesystems.disks.dokumen_fallback.root');
            $info['fallback']['exists'] = file_exists($fallbackPath);
            $info['fallback']['writable'] = is_writable($fallbackPath);
        } catch (\Exception $e) {
            // Ignore
        }
        
        return $info;
    }
    
    /**
     * Get disk name from old file path for migration
     * 
     * @param string $filePath Old file path
     * @return string Disk name
     */
    public static function getDiskFromPath(string $filePath): string
    {
        // Check if path contains 'dokumen-siswa' (new structure)
        if (strpos($filePath, 'dokumen-siswa') !== false) {
            return 'dokumen';
        }
        
        // Old files in storage/app/private
        if (strpos($filePath, 'private') !== false) {
            return 'private';
        }
        
        // Old files in storage/app/public
        if (strpos($filePath, 'public') !== false) {
            return 'public';
        }
        
        // Default to dokumen
        return 'dokumen';
    }
}
