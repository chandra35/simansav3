<?php

namespace App\Console\Commands;

use App\Models\ExamBrowserSession;
use App\Models\ExamBrowserViolation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamCleanupCommand extends Command
{
    protected $signature = 'exam:cleanup
        {--days=7 : Delete violations older than N days}
        {--end-stale : End sessions inactive for >2 hours}
        {--purge-logs : Truncate Laravel log file}
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Cleanup exam browser data: old violations, stale sessions, and log files to free disk space';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $endStale = $this->option('end-stale');
        $purgeLogs = $this->option('purge-logs');
        $dryRun = $this->option('dry-run');

        $this->info('=== ExaManmet Cleanup ===');
        if ($dryRun) {
            $this->warn('DRY RUN — no data will be deleted.');
        }

        // 1. Delete old violations (biggest disk consumer)
        $this->cleanupViolations($days, $dryRun);

        // 2. End stale sessions 
        if ($endStale) {
            $this->cleanupStaleSessions($dryRun);
        }

        // 3. Delete ended sessions older than N days
        $this->cleanupOldSessions($days, $dryRun);

        // 4. Purge log file
        if ($purgeLogs) {
            $this->purgeLogFiles($dryRun);
        }

        // 5. Show DB table sizes
        $this->showTableSizes();

        $this->info('=== Cleanup Complete ===');
        return self::SUCCESS;
    }

    private function cleanupViolations(int $days, bool $dryRun): void
    {
        $cutoff = now()->subDays($days);
        $count = ExamBrowserViolation::where('created_at', '<', $cutoff)->count();

        $this->info("Violations older than {$days} days: {$count} records");

        if ($count > 0 && !$dryRun) {
            // Delete in chunks to avoid memory issues
            $deleted = 0;
            while (true) {
                $batch = ExamBrowserViolation::where('created_at', '<', $cutoff)
                    ->limit(1000)
                    ->delete();
                if ($batch === 0) break;
                $deleted += $batch;
                $this->output->write("\rDeleted: {$deleted}/{$count}");
            }
            $this->newLine();
            $this->info("✓ Deleted {$deleted} old violations.");
        }
    }

    private function cleanupStaleSessions(bool $dryRun): void
    {
        $count = ExamBrowserSession::where('is_active', true)
            ->where(function ($q) {
                $q->where('last_heartbeat', '<', now()->subHours(2))
                  ->orWhereNull('last_heartbeat');
            })
            ->count();

        $this->info("Stale active sessions (>2h offline): {$count}");

        if ($count > 0 && !$dryRun) {
            $updated = ExamBrowserSession::where('is_active', true)
                ->where(function ($q) {
                    $q->where('last_heartbeat', '<', now()->subHours(2))
                      ->orWhereNull('last_heartbeat');
                })
                ->update([
                    'is_active' => false,
                    'ended_at' => now(),
                ]);
            $this->info("✓ Ended {$updated} stale sessions.");
        }
    }

    private function cleanupOldSessions(int $days, bool $dryRun): void
    {
        $cutoff = now()->subDays($days);
        $count = ExamBrowserSession::where('is_active', false)
            ->where('ended_at', '<', $cutoff)
            ->count();

        $this->info("Ended sessions older than {$days} days: {$count}");

        if ($count > 0 && !$dryRun) {
            // First delete their violations
            $violationCount = ExamBrowserViolation::whereIn(
                'session_id',
                ExamBrowserSession::where('is_active', false)
                    ->where('ended_at', '<', $cutoff)
                    ->pluck('id')
            )->delete();

            // Then soft-delete sessions
            $deleted = ExamBrowserSession::where('is_active', false)
                ->where('ended_at', '<', $cutoff)
                ->delete(); // SoftDeletes

            $this->info("✓ Removed {$deleted} old sessions + {$violationCount} related violations.");
        }
    }

    private function purgeLogFiles(bool $dryRun): void
    {
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $sizeMb = round(filesize($logPath) / 1024 / 1024, 2);
            $this->info("Log file: {$sizeMb} MB");

            if (!$dryRun) {
                file_put_contents($logPath, '');
                $this->info("✓ Log file truncated.");
            }
        } else {
            $this->info("No log file found.");
        }

        // Also clean old daily logs
        $logDir = storage_path('logs');
        $oldLogs = glob($logDir . '/laravel-*.log');
        if (!empty($oldLogs)) {
            $count = count($oldLogs);
            $totalSize = array_sum(array_map('filesize', $oldLogs));
            $totalMb = round($totalSize / 1024 / 1024, 2);
            $this->info("Daily log files: {$count} files, {$totalMb} MB total");

            if (!$dryRun) {
                foreach ($oldLogs as $log) {
                    // Keep last 3 days of daily logs
                    if (filemtime($log) < strtotime('-3 days')) {
                        unlink($log);
                    }
                }
                $this->info("✓ Old daily logs cleaned (kept last 3 days).");
            }
        }
    }

    private function showTableSizes(): void
    {
        $this->newLine();
        $this->info('--- Current Record Counts ---');

        $tables = [
            'exam_browser_sessions' => ExamBrowserSession::count(),
            'exam_browser_sessions (active)' => ExamBrowserSession::where('is_active', true)->count(),
            'exam_browser_violations' => ExamBrowserViolation::count(),
        ];

        foreach ($tables as $name => $count) {
            $this->line("  {$name}: {$count}");
        }
    }
}
