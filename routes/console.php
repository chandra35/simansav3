<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:analyze', function () {
    $year = \App\Models\TahunPelajaran::query()->active()->first();
    if (! $year) {
        $this->warn('Tahun pelajaran aktif tidak ditemukan.');

        return self::FAILURE;
    }

    $result = app(\App\Services\AttendanceInsightService::class)->generate($year);
    \App\Models\AttendanceAnalysisRun::create([
        'tahun_pelajaran_id' => $year->id,
        'source' => 'scheduler',
        'status' => 'completed',
        'result' => $result,
    ]);
    $this->info("Analisis selesai: {$result['students_scanned']} siswa, {$result['alerts_detected']} indikator.");

    return self::SUCCESS;
})->purpose('Generate smart suggestion kehadiran untuk tahun pelajaran aktif');

// Hotspot sync: setiap malam jam 02:00
Schedule::command('hotspot:sync --force')
    ->dailyAt('02:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] hotspot:sync gagal');
    });

Schedule::command('attendance:analyze')
    ->dailyAt('16:30')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] attendance:analyze gagal');
    });
