<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GtkController;
use App\Http\Controllers\Api\V1\JadwalController;
use App\Http\Controllers\Api\V1\KeuanganController;
use App\Http\Controllers\Api\V1\LmsAuthenticationController;
use App\Http\Controllers\Api\V1\LmsAssessmentScoreController;
use App\Http\Controllers\Api\V1\LmsSyncController;
use App\Http\Controllers\Api\V1\OpenApiController;
use App\Http\Controllers\Api\V1\PresensiController;
use App\Http\Controllers\Api\V1\SiswaController;
use App\Http\Controllers\Api\V1\SuratController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIMANSA V3
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with "/api" and documented
| interactively at /docs/api via Dedoc Scramble.
|
*/

// OpenAPI JSON endpoint
Route::get('/v1/openapi.json', [OpenApiController::class, 'show'])->name('v1.openapi');

// Public Authentication
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
});

// Protected API Routes (Bearer Token via Laravel Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // User Profile & Session
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    });

    // Siswa (Students)
    Route::prefix('siswa')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('api.v1.siswa.index');
        Route::get('/stats/summary', [SiswaController::class, 'stats'])->name('api.v1.siswa.stats');
        Route::get('/{identifier}', [SiswaController::class, 'show'])->name('api.v1.siswa.show');
    });

    // GTK (Teachers & Staff)
    Route::prefix('gtk')->group(function () {
        Route::get('/', [GtkController::class, 'index'])->name('api.v1.gtk.index');
        Route::get('/stats/summary', [GtkController::class, 'stats'])->name('api.v1.gtk.stats');
        Route::get('/{identifier}', [GtkController::class, 'show'])->name('api.v1.gtk.show');
    });

    // Jadwal Pelajaran (Teaching & Class Schedules)
    Route::prefix('jadwal')->group(function () {
        Route::get('/hari-ini', [JadwalController::class, 'hariIni'])->name('api.v1.jadwal.hari-ini');
        Route::get('/kelas/{identifier}', [JadwalController::class, 'perKelas'])->name('api.v1.jadwal.kelas');
        Route::get('/guru/{identifier}', [JadwalController::class, 'perGuru'])->name('api.v1.jadwal.guru');
    });

    // Presensi & Sholat (Daily & Prayer Attendance)
    Route::prefix('presensi')->group(function () {
        Route::get('/harian', [PresensiController::class, 'dailyOverview'])->name('api.v1.presensi.harian');
        Route::get('/sholat/summary', [PresensiController::class, 'sholatSummary'])->name('api.v1.presensi.sholat.summary');
        Route::get('/sholat/status-kelas', [PresensiController::class, 'sholatStatusKelas'])->name('api.v1.presensi.sholat.status-kelas');
    });

    // Keuangan (Billings & Payments)
    Route::prefix('keuangan')->group(function () {
        Route::get('/tagihan/{identifier}', [KeuanganController::class, 'tagihanSiswa'])->name('api.v1.keuangan.tagihan');
        Route::get('/pembayaran/harian', [KeuanganController::class, 'dailyPayments'])->name('api.v1.keuangan.pembayaran.harian');
    });

    // Surat & Tata Usaha (Certificates & Incoming Letters)
    Route::prefix('surat')->group(function () {
        Route::get('/keterangan', [SuratController::class, 'suratKeteranganIndex'])->name('api.v1.surat.keterangan.index');
        Route::get('/keterangan/{identifier}', [SuratController::class, 'suratKeteranganShow'])->name('api.v1.surat.keterangan.show');
        Route::get('/masuk', [SuratController::class, 'suratMasukIndex'])->name('api.v1.surat.masuk.index');
    });

});

// LMS Sync API (Special Scopes / Existing Integration)
Route::prefix('v1/lms')->middleware(['auth:sanctum', 'abilities:lms:read', 'throttle:lms-sync-api'])->group(function () {
    Route::get('/students', [App\Http\Controllers\Api\V1\LmsSyncController::class, 'students']);
    Route::get('/teachers', [App\Http\Controllers\Api\V1\LmsSyncController::class, 'teachers']);
    Route::get('/roster', [App\Http\Controllers\Api\V1\LmsSyncController::class, 'roster']);
});

Route::post('/v1/lms/authenticate', [App\Http\Controllers\Api\V1\LmsAuthenticationController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:lms:auth', 'throttle:lms-auth-api']);

Route::post('/v1/lms/assessment-scores', [App\Http\Controllers\Api\V1\LmsAssessmentScoreController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:lms:write', 'throttle:lms-score-api']);

