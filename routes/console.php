<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hotspot sync: setiap malam jam 02:00
Schedule::command('hotspot:sync --force')
    ->dailyAt('02:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] hotspot:sync gagal');
    });
