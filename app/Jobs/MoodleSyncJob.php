<?php

namespace App\Jobs;

use App\Models\MoodleSyncRun;
use App\Services\MoodleSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MoodleSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 3600;

    public function __construct(public int $runId)
    {
        $this->onQueue('moodle-sync');
    }

    public function handle(MoodleSyncService $service): void
    {
        $run = MoodleSyncRun::find($this->runId);
        if (!$run || $run->status !== 'queued') return;
        $service->runExisting($run);
    }
}
