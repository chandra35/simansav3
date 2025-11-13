<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DatabaseBackupService
{
    protected $backupPath;
    protected $maxBackups = 10; // Keep only last 10 backups

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups/database');
        
        // Create backup directory if not exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create full database backup
     */
    public function createBackup($reason = 'manual')
    {
        try {
            $timestamp = now()->format('Y-m-d_His');
            $filename = "backup_{$reason}_{$timestamp}.sql";
            $filepath = $this->backupPath . '/' . $filename;

            // Get database credentials
            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            // Execute backup
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Backup failed with return code: ' . $returnVar);
            }

            // Compress backup
            $zipFilename = $filename . '.zip';
            $zipPath = $this->backupPath . '/' . $zipFilename;
            $this->compressFile($filepath, $zipPath);

            // Delete uncompressed SQL file
            File::delete($filepath);

            // Get file info
            $fileSize = File::size($zipPath);

            // Cleanup old backups
            $this->cleanupOldBackups();

            // Log backup
            Log::info('Database backup created', [
                'filename' => $zipFilename,
                'size' => $this->formatBytes($fileSize),
                'reason' => $reason,
                'user_id' => auth()->id() ?? null,
            ]);

            return [
                'success' => true,
                'filename' => $zipFilename,
                'filepath' => $zipPath,
                'size' => $fileSize,
                'size_formatted' => $this->formatBytes($fileSize),
                'created_at' => now(),
            ];

        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($filename)
    {
        try {
            $zipPath = $this->backupPath . '/' . $filename;

            if (!File::exists($zipPath)) {
                throw new \Exception('Backup file not found: ' . $filename);
            }

            // Extract ZIP
            $sqlFilename = str_replace('.zip', '', $filename);
            $sqlPath = $this->backupPath . '/' . $sqlFilename;
            $this->extractFile($zipPath, $sqlPath);

            // Get database credentials
            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Build mysql restore command
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($sqlPath)
            );

            // Execute restore
            exec($command, $output, $returnVar);

            // Delete extracted SQL file
            File::delete($sqlPath);

            if ($returnVar !== 0) {
                throw new \Exception('Restore failed with return code: ' . $returnVar);
            }

            Log::info('Database restored from backup', [
                'filename' => $filename,
                'user_id' => auth()->id() ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Database berhasil direstore dari backup',
            ];

        } catch (\Exception $e) {
            Log::error('Database restore failed', [
                'error' => $e->getMessage(),
                'filename' => $filename,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of all backups
     */
    public function listBackups()
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'filepath' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatBytes($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'age' => $this->getFileAge($file->getMTime()),
                ];
            }
        }

        // Sort by date (newest first)
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        try {
            $filepath = $this->backupPath . '/' . $filename;

            if (!File::exists($filepath)) {
                throw new \Exception('Backup file not found');
            }

            File::delete($filepath);

            Log::info('Backup deleted', [
                'filename' => $filename,
                'user_id' => auth()->id() ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Backup berhasil dihapus',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Compress SQL file to ZIP
     */
    private function compressFile($source, $destination)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            throw new \Exception('Cannot create ZIP file');
        }

        $zip->addFile($source, basename($source));
        $zip->close();
    }

    /**
     * Extract ZIP file
     */
    private function extractFile($zipPath, $destination)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Cannot open ZIP file');
        }

        $zip->extractTo(dirname($destination));
        $zip->close();
    }

    /**
     * Cleanup old backups (keep only last N)
     */
    private function cleanupOldBackups()
    {
        $backups = $this->listBackups();

        if (count($backups) > $this->maxBackups) {
            $toDelete = array_slice($backups, $this->maxBackups);
            
            foreach ($toDelete as $backup) {
                File::delete($backup['filepath']);
                Log::info('Old backup deleted', ['filename' => $backup['filename']]);
            }
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get file age in human readable format
     */
    private function getFileAge($timestamp)
    {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return $diff . ' detik yang lalu';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } else {
            return floor($diff / 86400) . ' hari yang lalu';
        }
    }

    /**
     * Get total backup size
     */
    public function getTotalBackupSize()
    {
        $backups = $this->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'total' => $totalSize,
            'formatted' => $this->formatBytes($totalSize),
            'count' => count($backups),
        ];
    }
}
