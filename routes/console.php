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

Artisan::command('attendance:generate-daily-draft {--date= : Tanggal presensi (default: hari ini)} {--force : Abaikan pengecekan jadwal operasional}', function () {
    $date = $this->option('date') ?: now()->toDateString();
    $force = (bool) $this->option('force');

    $year = \App\Models\TahunPelajaran::query()->active()->first();
    if (! $year) {
        $this->warn('Tahun pelajaran aktif tidak ditemukan.');

        return self::FAILURE;
    }

    $carbonDate = \Carbon\Carbon::parse($date);
    $dayOfWeek = $carbonDate->dayOfWeek;

    if (! $force) {
        $schedule = \App\Models\AbsensiOperationalSchedule::query()
            ->where('user_type', 'siswa')
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if ($schedule && ! $schedule->is_active) {
            $this->info("Tanggal {$date} ({$schedule->day_label}) bukan hari operasional siswa. Gunakan --force untuk tetap membuat.");

            return self::SUCCESS;
        }
    }

    $adminUser = \App\Models\User::role('Super Admin')->first()
        ?? \App\Models\User::where('email', 'like', '%admin%')->first()
        ?? \App\Models\User::first();

    $classes = \App\Models\Kelas::query()
        ->where('tahun_pelajaran_id', $year->id)
        ->where('is_active', true)
        ->orderBy('tingkat')
        ->orderBy('nama_kelas')
        ->get();

    $created = 0;
    $skipped = 0;

    foreach ($classes as $kelas) {
        $key = implode(':', [$date, $kelas->id, 'harian', 'daily']);
        if (\App\Models\AbsensiSiswaSession::query()->where('session_key', $key)->exists()) {
            $skipped++;
            continue;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($kelas, $year, $date, $key, $adminUser) {
            $session = \App\Models\AbsensiSiswaSession::create([
                'tahun_pelajaran_id' => $year->id,
                'session_key' => $key,
                'kelas_id' => $kelas->id,
                'guru_user_id' => $adminUser->id,
                'tanggal' => $date,
                'semester' => $year->semester_aktif,
                'tingkat' => $kelas->tingkat,
                'kelas_snapshot' => $kelas->nama_kelas,
                'guru_snapshot' => $adminUser->name,
                'mode' => 'harian',
                'attendance_method' => 'manual',
                'status' => 'draft',
                'version' => 1,
                'notes' => 'Draft harian dibuat otomatis oleh sistem.',
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

            $students = $kelas->siswas()
                ->wherePivot('tahun_pelajaran_id', $year->id)
                ->wherePivot('tanggal_masuk', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('siswa_kelas.tanggal_keluar')
                        ->orWhere('siswa_kelas.tanggal_keluar', '>=', $date);
                })
                ->get();

            foreach ($students as $siswa) {
                \App\Models\AbsensiSiswaRecord::create([
                    'session_id' => $session->id,
                    'siswa_id' => $siswa->id,
                    'status' => 'hadir',
                    'attendance_method' => 'manual',
                    'source_reference' => 'system_daily_draft',
                    'checked_at' => now(),
                    'checked_by' => $adminUser->id,
                ]);
            }
        });
        $created++;
    }

    $this->info("Draft harian {$date}: {$created} kelas dibuat, {$skipped} kelas dilewati.");

    return self::SUCCESS;
})->purpose('Generate daily draft attendance sessions for all active classes');

// Hotspot sync: setiap malam jam 02:00
Schedule::command('hotspot:sync --force')
    ->dailyAt('02:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] hotspot:sync gagal');
    });

Schedule::command('attendance:generate-daily-draft')
    ->dailyAt('06:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] attendance:generate-daily-draft gagal');
    });

Schedule::command('attendance:analyze')
    ->dailyAt('16:30')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('[Scheduler] attendance:analyze gagal');
    });
