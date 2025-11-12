<?php

/**
 * Script untuk migrate file dokumen siswa dari storage lama ke folder dokumen-siswa baru
 * 
 * Cara pakai:
 * php migrate_dokumen_storage.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Helpers\StorageHelper;

echo "==============================================\n";
echo "  MIGRATE DOKUMEN SISWA STORAGE\n";
echo "==============================================\n\n";

// Get target disk
$targetDisk = StorageHelper::getDokumenDisk();
echo "Target disk: {$targetDisk}\n";
echo "Target path: " . config("filesystems.disks.{$targetDisk}.root") . "\n\n";

// Ensure target storage exists
if (!StorageHelper::ensureStorageExists($targetDisk)) {
    echo "ERROR: Cannot create or write to target storage!\n";
    exit(1);
}

echo "Fetching dokumen records...\n";

// Get all dokumen that need migration (not in target disk yet)
$documents = DB::table('dokumen_siswa')
    ->where(function($q) use ($targetDisk) {
        $q->where('storage_disk', '!=', $targetDisk)
          ->orWhereNull('storage_disk');
    })
    ->get();

echo "Found " . $documents->count() . " documents to migrate\n\n";

if ($documents->isEmpty()) {
    echo "No documents to migrate. All done!\n";
    exit(0);
}

$success = 0;
$failed = 0;
$skipped = 0;

foreach ($documents as $doc) {
    echo "[{$doc->id}] Processing: {$doc->original_name}...";
    
    try {
        // Determine source disk
        $sourceDisk = $doc->storage_disk ?? StorageHelper::getDiskFromPath($doc->file_path);
        
        // Check if source file exists
        if (!Storage::disk($sourceDisk)->exists($doc->file_path)) {
            echo " SKIP (source not found)\n";
            $skipped++;
            continue;
        }
        
        // Get file content
        $fileContent = Storage::disk($sourceDisk)->get($doc->file_path);
        
        // New path structure: {NISN}/{UUID}.ext
        // Extract UUID and extension from file_path
        $pathParts = explode('/', $doc->file_path);
        $fileName = end($pathParts);
        
        // Get siswa NISN
        $siswa = DB::table('siswa')->where('id', $doc->siswa_id)->first();
        if (!$siswa) {
            echo " SKIP (siswa not found)\n";
            $skipped++;
            continue;
        }
        
        // New path: {NISN}/{filename}
        $newPath = "{$siswa->nisn}/{$fileName}";
        
        // Check if already exists in target
        if (Storage::disk($targetDisk)->exists($newPath)) {
            echo " SKIP (already exists in target)\n";
            
            // Just update database
            DB::table('dokumen_siswa')
                ->where('id', $doc->id)
                ->update([
                    'file_path' => $newPath,
                    'storage_disk' => $targetDisk,
                    'updated_at' => now(),
                ]);
            
            $skipped++;
            continue;
        }
        
        // Copy to new storage
        Storage::disk($targetDisk)->put($newPath, $fileContent);
        
        // Verify file was copied
        if (!Storage::disk($targetDisk)->exists($newPath)) {
            echo " FAILED (copy verification failed)\n";
            $failed++;
            continue;
        }
        
        // Update database
        DB::table('dokumen_siswa')
            ->where('id', $doc->id)
            ->update([
                'file_path' => $newPath,
                'storage_disk' => $targetDisk,
                'updated_at' => now(),
            ]);
        
        // Optional: Delete from old storage (commented for safety)
        // Storage::disk($sourceDisk)->delete($doc->file_path);
        
        echo " SUCCESS\n";
        $success++;
        
    } catch (\Exception $e) {
        echo " ERROR: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n==============================================\n";
echo "  MIGRATION COMPLETE\n";
echo "==============================================\n";
echo "Success: {$success}\n";
echo "Skipped: {$skipped}\n";
echo "Failed: {$failed}\n";
echo "Total: " . $documents->count() . "\n\n";

if ($success > 0) {
    echo "NOTE: Old files are NOT deleted for safety.\n";
    echo "      Please verify the migration, then manually delete old files if needed.\n";
    echo "      Old files location: storage/app/private/dokumen-siswa/\n\n";
}

echo "Done!\n";
