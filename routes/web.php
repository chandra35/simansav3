<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\SiswaImportController;
use App\Http\Controllers\Admin\TahunPelajaranController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\RdmSyncController;
use App\Http\Controllers\Admin\RdmMapelMappingController;
use App\Http\Controllers\Admin\RdmMatchingController;
use App\Http\Controllers\Admin\KenaikanKelasController;
use App\Http\Controllers\Admin\MutasiSiswaController;
use App\Http\Controllers\Admin\MatrikulasiPpdbController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\ProfileController as SiswaProfileController;
use App\Http\Controllers\PublicOsisPollingController;

// Redirect root: if logged in go to appropriate dashboard, else go to login
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }

    $user = auth()->user();

    if ($user->matrikulasiPeserta || $user->hasRole('Matrikulasi') || $user->role === 'matrikulasi') {
        return redirect('/matrikulasi/dashboard');
    }

    if ($user->hasRole('Siswa') || $user->role === 'siswa') {
        return redirect('/siswa/dashboard');
    }

    if ($user->hasRole('GTK') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])) {
        return redirect('/admin/gtk/dashboard');
    }

    return redirect('/admin/dashboard');
});

// Public Verification Routes (No Auth Required - for QR Code scanning)
Route::get('/verifikasi/gtk/{id}', [App\Http\Controllers\VerifikasiController::class, 'verifikasiGtk'])->name('verifikasi.gtk');
Route::get('/verifikasi/siswa/{id}', [App\Http\Controllers\VerifikasiController::class, 'verifikasiSiswa'])->name('verifikasi.siswa');

// Public Download Center
Route::get('/downloads', [App\Http\Controllers\PublicDownloadController::class, 'index'])->name('downloads.index');
Route::get('/downloads/{download:slug}/file/{filename?}', [App\Http\Controllers\PublicDownloadController::class, 'download'])->name('downloads.download');

// Public live polling (aggregate results only; no voter identity or voting action)
Route::get('/live-polling-osis', [PublicOsisPollingController::class, 'index'])->name('public.osis-polling.index');
Route::get('/live-polling-osis/data', [PublicOsisPollingController::class, 'data'])
    ->middleware('throttle:30,1')
    ->name('public.osis-polling.data');

// Layar TV guru piket: hanya menampilkan jadwal aktif hari ini, tanpa akses admin.
Route::get('/monitor-jadwal', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'publicMonitor'])
    ->middleware('throttle:60,1')
    ->name('public.jadwal-monitor');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->prefix('matrikulasi')->name('matrikulasi.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Matrikulasi\DashboardController::class, 'index'])->name('dashboard');
});

// Modul Asrama berdiri sendiri dan memakai master siswa/GTK SIMANSA.
Route::middleware(['auth'])->prefix('asrama')->name('asrama.')->group(function () {
    Route::get('/', [App\Http\Controllers\Asrama\DashboardController::class, 'index'])
        ->middleware('permission:view-asrama|view-asrama-portal')
        ->name('dashboard');

    Route::middleware('permission:manage-asrama-operator')->group(function () {
        Route::get('/operator', [App\Http\Controllers\Asrama\OperatorController::class, 'index'])->name('operator.index');
        Route::post('/operator', [App\Http\Controllers\Asrama\OperatorController::class, 'store'])->name('operator.store');
        Route::delete('/operator/{user}', [App\Http\Controllers\Asrama\OperatorController::class, 'destroy'])->name('operator.destroy');
    });

    Route::middleware('permission:manage-asrama-santri')->group(function () {
        Route::get('/santri', [App\Http\Controllers\Asrama\MasterController::class, 'santri'])->name('santri.index');
        Route::post('/santri', [App\Http\Controllers\Asrama\MasterController::class, 'storeSantri'])->name('santri.store');
        Route::get('/santri/{santri}/detail', [App\Http\Controllers\Asrama\MasterController::class, 'showSantri'])->name('santri.show');
        Route::delete('/santri/{santri}', [App\Http\Controllers\Asrama\MasterController::class, 'destroySantri'])->name('santri.destroy');
        Route::get('/santri/nomor-induk', [App\Http\Controllers\Asrama\MasterController::class, 'nomorInduk'])->name('santri.induk.index');
        Route::get('/santri/nomor-induk/template', [App\Http\Controllers\Asrama\MasterController::class, 'templateNomorInduk'])->name('santri.induk.template');
        Route::post('/santri/nomor-induk/import', [App\Http\Controllers\Asrama\MasterController::class, 'importNomorInduk'])->name('santri.induk.import');
        Route::put('/santri/nomor-induk/{santri}', [App\Http\Controllers\Asrama\MasterController::class, 'updateNomorInduk'])->name('santri.induk.update');
    });

    Route::middleware('permission:manage-asrama-asatidz')->group(function () {
        Route::get('/asatidz', [App\Http\Controllers\Asrama\MasterController::class, 'asatidz'])->name('asatidz.index');
        Route::post('/asatidz', [App\Http\Controllers\Asrama\MasterController::class, 'storeAsatidz'])->name('asatidz.store');
        Route::put('/asatidz/{asatidz}', [App\Http\Controllers\Asrama\MasterController::class, 'updateAsatidz'])->name('asatidz.update');
        Route::delete('/asatidz/{asatidz}', [App\Http\Controllers\Asrama\MasterController::class, 'destroyAsatidz'])->name('asatidz.destroy');
    });

    Route::middleware('permission:manage-asrama-mapel')->group(function () {
        Route::get('/mapel', [App\Http\Controllers\Asrama\MasterController::class, 'mapel'])->name('mapel.index');
        Route::post('/mapel', [App\Http\Controllers\Asrama\MasterController::class, 'storeMapel'])->name('mapel.store');
        Route::put('/mapel/{mapel}', [App\Http\Controllers\Asrama\MasterController::class, 'updateMapel'])->name('mapel.update');
        Route::delete('/mapel/{mapel}', [App\Http\Controllers\Asrama\MasterController::class, 'destroyMapel'])->name('mapel.destroy');
    });

    Route::middleware('permission:manage-asrama-kelas')->group(function () {
        Route::get('/kelas', [App\Http\Controllers\Asrama\KelasController::class, 'index'])->name('kelas.index');
        Route::get('/kelas/{kelas}', [App\Http\Controllers\Asrama\KelasController::class, 'show'])->name('kelas.show');
        Route::put('/kelas/{kelas}', [App\Http\Controllers\Asrama\KelasController::class, 'update'])->name('kelas.update');
        Route::post('/kelas/{kelas}/santri', [App\Http\Controllers\Asrama\KelasController::class, 'assignStudents'])->name('kelas.santri.store');
        Route::delete('/kelas/{kelas}/santri/{anggota}', [App\Http\Controllers\Asrama\KelasController::class, 'removeStudent'])->name('kelas.santri.destroy');
        Route::post('/kelas/{kelas}/ketua', [App\Http\Controllers\Asrama\KelasController::class, 'setChair'])->name('kelas.ketua');
        Route::post('/kelas/{kelas}/pengasuh', [App\Http\Controllers\Asrama\KelasController::class, 'storeCaregiver'])->name('kelas.pengasuh.store');
        Route::delete('/kelas/{kelas}/pengasuh/{pengasuh}', [App\Http\Controllers\Asrama\KelasController::class, 'destroyCaregiver'])->name('kelas.pengasuh.destroy');
        Route::post('/kelas/{kelas}/pengasuh/{pengasuh}/santri', [App\Http\Controllers\Asrama\KelasController::class, 'assignCaregiverStudents'])->name('kelas.pengasuh.santri');
        Route::post('/kelas/{kelas}/pengampu', [App\Http\Controllers\Asrama\KelasController::class, 'storePengampu'])->name('kelas.pengampu.store');
        Route::delete('/kelas/{kelas}/pengampu/{pengampu}', [App\Http\Controllers\Asrama\KelasController::class, 'destroyPengampu'])->name('kelas.pengampu.destroy');
    });

    Route::middleware('permission:manage-asrama-kamar')->group(function () {
        Route::get('/kamar', [App\Http\Controllers\Asrama\KamarController::class, 'index'])->name('kamar.index');
        Route::post('/kamar', [App\Http\Controllers\Asrama\KamarController::class, 'store'])->name('kamar.store');
        Route::put('/kamar/{kamar}', [App\Http\Controllers\Asrama\KamarController::class, 'update'])->name('kamar.update');
        Route::post('/kamar/{kamar}/santri', [App\Http\Controllers\Asrama\KamarController::class, 'assign'])->name('kamar.santri.store');
        Route::delete('/kamar/{kamar}/santri/{penghuni}', [App\Http\Controllers\Asrama\KamarController::class, 'remove'])->name('kamar.santri.destroy');
    });

    Route::middleware('permission:input-nilai-asrama|manage-asrama-pengampu')->group(function () {
        Route::get('/nilai', [App\Http\Controllers\Asrama\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/{pengampu}', [App\Http\Controllers\Asrama\NilaiController::class, 'edit'])->name('nilai.edit');
        Route::put('/nilai/{pengampu}', [App\Http\Controllers\Asrama\NilaiController::class, 'update'])->name('nilai.update');
    });

    Route::middleware('can:asrama-rapor-access')->group(function () {
        Route::get('/rapor', [App\Http\Controllers\Asrama\RaporController::class, 'index'])->name('rapor.index');
        Route::get('/rapor/santri/{anggota}', [App\Http\Controllers\Asrama\RaporController::class, 'edit'])->name('rapor.edit');
        Route::put('/rapor/santri/{anggota}', [App\Http\Controllers\Asrama\RaporController::class, 'update'])->name('rapor.update');
    });
    Route::post('/rapor/{rapor}/terbitkan', [App\Http\Controllers\Asrama\RaporController::class, 'publish'])
        ->middleware('permission:publish-rapor-asrama')->name('rapor.publish');
    Route::post('/rapor/{rapor}/batalkan', [App\Http\Controllers\Asrama\RaporController::class, 'unpublish'])
        ->middleware('permission:publish-rapor-asrama')->name('rapor.unpublish');
    Route::get('/rapor/{rapor}/cetak', [App\Http\Controllers\Asrama\RaporController::class, 'print'])
        ->middleware('permission:print-rapor-asrama|view-asrama-portal')->name('rapor.print');
});

// Forgot Password Routes
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:10,1')
    ->name('password.update');

// Admin Routes (Super Admin, Admin, GTK, Operator)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/online-users', [AdminDashboardController::class, 'onlineUsers'])->name('dashboard.online-users');
    
    // Under Development Placeholder
    Route::get('/under-development', function () {
        return view('admin.under-development');
    })->name('under-development');

    Route::middleware('permission:view-polling-results|manage-polling')->prefix('polling')->name('polling.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PollingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\PollingController::class, 'create'])->middleware('permission:manage-polling')->name('create');
        Route::post('/', [App\Http\Controllers\Admin\PollingController::class, 'store'])->middleware('permission:manage-polling')->name('store');
        Route::get('/{polling}/duplicate', [App\Http\Controllers\Admin\PollingController::class, 'duplicate'])->middleware('permission:manage-polling')->name('duplicate');
        Route::get('/{polling}/respondents', [App\Http\Controllers\Admin\PollingController::class, 'respondents'])->name('respondents');
        Route::get('/{polling}/questions/{question}/options/{option}/voters', [App\Http\Controllers\Admin\PollingController::class, 'voters'])->name('voters');
        Route::post('/{polling}/responses/{response}/unlock', [App\Http\Controllers\Admin\PollingController::class, 'unlock'])->middleware('permission:manage-polling')->name('responses.unlock');
        Route::get('/{polling}', [App\Http\Controllers\Admin\PollingController::class, 'show'])->name('show');
        Route::get('/{polling}/edit', [App\Http\Controllers\Admin\PollingController::class, 'edit'])->middleware('permission:manage-polling')->name('edit');
        Route::put('/{polling}', [App\Http\Controllers\Admin\PollingController::class, 'update'])->middleware('permission:manage-polling')->name('update');
        Route::post('/{polling}/publish', [App\Http\Controllers\Admin\PollingController::class, 'publish'])->middleware('permission:manage-polling')->name('publish');
        Route::post('/{polling}/close', [App\Http\Controllers\Admin\PollingController::class, 'close'])->middleware('permission:manage-polling')->name('close');
        Route::post('/{polling}/reopen', [App\Http\Controllers\Admin\PollingController::class, 'reopen'])->middleware('permission:manage-polling')->name('reopen');
        Route::delete('/{polling}', [App\Http\Controllers\Admin\PollingController::class, 'destroy'])->middleware('permission:manage-polling')->name('destroy');
        Route::get('/{polling}/export', [App\Http\Controllers\Admin\PollingController::class, 'export'])->name('export');
        Route::get('/{polling}/pdf', [App\Http\Controllers\Admin\PollingController::class, 'pdf'])->name('pdf');
    });
    
    // Profile Management
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'changePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    
    // Hotspot Management
    Route::prefix('hotspot')->name('hotspot.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\HotspotController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\Admin\HotspotController::class, 'data'])->name('data');
        Route::get('/online', [\App\Http\Controllers\Admin\HotspotController::class, 'onlinePage'])->name('online');
        Route::get('/online-users', [\App\Http\Controllers\Admin\HotspotController::class, 'onlineUsers'])->name('online-users');
        Route::get('/filter-options', [\App\Http\Controllers\Admin\HotspotController::class, 'filterOptions'])->name('filter-options');
        Route::post('/sync', [\App\Http\Controllers\Admin\HotspotController::class, 'sync'])->name('sync');
        Route::post('/sync/{hotspot}', [\App\Http\Controllers\Admin\HotspotController::class, 'syncSingle'])->name('sync-single');
        Route::post('/{hotspot}/toggle-active', [\App\Http\Controllers\Admin\HotspotController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/bulk-toggle', [\App\Http\Controllers\Admin\HotspotController::class, 'bulkToggle'])->name('bulk-toggle');
        Route::post('/assign-profile', [\App\Http\Controllers\Admin\HotspotController::class, 'assignProfile'])->name('assign-profile');
        Route::get('/radius-status', [\App\Http\Controllers\Admin\HotspotController::class, 'radiusStatus'])->name('radius-status');
        Route::get('/stats', [\App\Http\Controllers\Admin\HotspotController::class, 'stats'])->name('stats');
        Route::get('/profiles', [\App\Http\Controllers\Admin\HotspotController::class, 'profiles'])->name('profiles');
        Route::post('/profiles', [\App\Http\Controllers\Admin\HotspotController::class, 'storeProfile'])->name('profiles.store');
        Route::put('/profiles/{profile}', [\App\Http\Controllers\Admin\HotspotController::class, 'updateProfile'])->name('profiles.update');
        Route::post('/profiles/{profile}/sync', [\App\Http\Controllers\Admin\HotspotController::class, 'syncProfile'])->name('profiles.sync');
        Route::delete('/profiles/{profile}', [\App\Http\Controllers\Admin\HotspotController::class, 'destroyProfile'])->name('profiles.destroy');
        Route::post('/nas', [\App\Http\Controllers\Admin\HotspotController::class, 'storeNas'])->name('nas.store');
        Route::put('/nas/{nas}', [\App\Http\Controllers\Admin\HotspotController::class, 'updateNas'])->name('nas.update');
        Route::post('/nas/{nas}/sync', [\App\Http\Controllers\Admin\HotspotController::class, 'syncNas'])->name('nas.sync');
        Route::delete('/nas/{nas}', [\App\Http\Controllers\Admin\HotspotController::class, 'destroyNas'])->name('nas.destroy');
        // Tamu CRUD
        Route::post('/tamu', [\App\Http\Controllers\Admin\HotspotController::class, 'storeTamu'])->name('tamu.store');
        Route::put('/tamu/{hotspot}', [\App\Http\Controllers\Admin\HotspotController::class, 'updateTamu'])->name('tamu.update');
        Route::delete('/tamu/{hotspot}', [\App\Http\Controllers\Admin\HotspotController::class, 'destroyTamu'])->name('tamu.destroy');
    });

    // Siswa Management (edit method tidak ada — semua edit via modal AJAX)
    Route::get('/siswa/export', [AdminSiswaController::class, 'export'])->name('siswa.export');
    Route::resource('siswa', AdminSiswaController::class)->except(['edit']);
    Route::get('/siswa-data', [AdminSiswaController::class, 'data'])->name('siswa.data');
    Route::get('/siswa-stats', [AdminSiswaController::class, 'stats'])->name('siswa.stats');
    Route::middleware('permission:view-statistik-siswa')->group(function () {
        Route::get('/siswa-statistik', [App\Http\Controllers\Admin\SiswaStatisticsController::class, 'index'])->name('siswa.statistics');
        Route::get('/siswa-statistik/sekolah/{sekolah}/belum-emis', [App\Http\Controllers\Admin\SiswaStatisticsController::class, 'studentsMissingEmis'])->name('siswa.statistics.school-missing-emis');
        Route::post('/siswa-statistik/sekolah/{sekolah}/check-nsm', [App\Http\Controllers\Admin\SiswaStatisticsController::class, 'checkSchoolNsm'])->name('siswa.statistics.check-school-nsm');
        Route::post('/siswa-statistik/{siswa}/check-npsn-ppdb', [App\Http\Controllers\Admin\SiswaStatisticsController::class, 'checkNpsnFromPpdb'])->name('siswa.statistics.check-npsn-ppdb');
    });
    Route::put('/siswa/{siswa}/reset-password', [AdminSiswaController::class, 'resetPassword'])->name('siswa.reset-password');
    Route::get('/siswa/{siswa}/dokumen', [AdminSiswaController::class, 'getDokumen'])->name('siswa.dokumen');
    Route::get('/siswa/{siswaId}/dokumen/{dokumenId}/download-jpg', [AdminSiswaController::class, 'downloadDokumenAsJpg'])->name('siswa.dokumen.download-jpg');
    Route::post('/siswa/{siswa}/toggle-verval-ijazah', [AdminSiswaController::class, 'toggleVervalIjazah'])->name('siswa.toggle-verval-ijazah');
    Route::post('/siswa/{siswa}/toggle-emis-registered', [AdminSiswaController::class, 'toggleEmisRegistered'])
        ->middleware('can:super-admin-access')
        ->name('siswa.toggle-emis-registered');
    Route::get('/siswa/{siswa}/quick-detail', [AdminSiswaController::class, 'quickDetail'])->name('siswa.quick-detail');
    Route::get('/siswa/{siswa}/download-foto', [AdminSiswaController::class, 'downloadFoto'])->name('siswa.download-foto');
    Route::get('/siswa-kelas-by-tingkat', [AdminSiswaController::class, 'getKelasByTingkat'])->name('siswa.kelas-by-tingkat');
    Route::middleware('permission:impersonate-users')->prefix('impersonation')->name('impersonation.')->group(function () {
        Route::post('/siswa/{siswa}', [App\Http\Controllers\Admin\UserImpersonationController::class, 'startSiswa'])
            ->name('siswa.start');
        Route::post('/gtk/{gtk}', [App\Http\Controllers\Admin\UserImpersonationController::class, 'startGtk'])
            ->name('gtk.start');
    });

    Route::middleware('permission:manage-nis-lokal')->prefix('nis-lokal')->name('nis-lokal.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NisLokalController::class, 'index'])->name('index');
        Route::post('/generator/preview', [App\Http\Controllers\Admin\NisLokalController::class, 'generatorPreview'])->name('generator.preview');
        Route::post('/generator/confirm', [App\Http\Controllers\Admin\NisLokalController::class, 'confirmGenerator'])->name('generator.confirm');
        Route::get('/template', [App\Http\Controllers\Admin\NisLokalController::class, 'template'])->name('template');
        Route::post('/import/preview', [App\Http\Controllers\Admin\NisLokalController::class, 'importPreview'])->name('import.preview');
        Route::post('/import/confirm', [App\Http\Controllers\Admin\NisLokalController::class, 'confirmImport'])->name('import.confirm');
    });

    // Pembanding data siswa SIMANSA dengan snapshot EMIS Lembaga (admin only)
    Route::middleware('permission:view-emis-comparison')->prefix('cek-data-emis')->name('emis-comparison.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'index'])->name('index');
        Route::get('/siswa/{siswa}', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'show'])->name('show');
        Route::post('/siswa/{siswa}/sync', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'syncStudent'])
            ->middleware(['permission:sync-emis-comparison', 'throttle:emis-student-sync'])
            ->name('sync-student');
        Route::get('/emis/{snapshot}', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'showEmis'])->name('show-emis');
        Route::post('/sync', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'sync'])
            ->middleware('permission:sync-emis-comparison')
            ->name('sync');
        Route::post('/sync/{sync}/process', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'processSync'])
            ->middleware('permission:sync-emis-comparison')
            ->name('sync.process');
        Route::get('/sync/{sync}/status', [App\Http\Controllers\Admin\EmisStudentComparisonController::class, 'syncStatus'])
            ->name('sync.status');
    });

    Route::middleware('permission:view-osis-election')->prefix('pemilihan-osis')->name('osis-election.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\OsisElectionController::class, 'index'])->name('index');
        Route::get('/buat', [App\Http\Controllers\Admin\OsisElectionController::class, 'create'])->middleware('permission:manage-osis-election')->name('create');
        Route::post('/', [App\Http\Controllers\Admin\OsisElectionController::class, 'store'])->middleware('permission:manage-osis-election')->name('store');
        Route::get('/{election}/preview', [App\Http\Controllers\Admin\OsisElectionController::class, 'preview'])->middleware('permission:manage-osis-election')->name('preview');
        Route::get('/{election}/kandidat', [App\Http\Controllers\Admin\OsisElectionController::class, 'candidateOptions'])->middleware('permission:manage-osis-election')->name('candidates');
        Route::get('/{election}/pemilih', [App\Http\Controllers\Admin\OsisElectionController::class, 'voters'])->middleware('permission:manage-osis-election')->name('voters');
        Route::get('/{election}/laporan', [App\Http\Controllers\Admin\OsisElectionController::class, 'report'])->middleware('permission:manage-osis-election')->name('report');
        Route::get('/{election}/laporan/pdf', [App\Http\Controllers\Admin\OsisElectionController::class, 'reportPdf'])->middleware('permission:manage-osis-election')->name('report.pdf');
        Route::get('/{election}/laporan/excel', [App\Http\Controllers\Admin\OsisElectionController::class, 'reportExcel'])->middleware('permission:manage-osis-election')->name('report.excel');
        Route::get('/{election}/laporan/belum-memilih', [App\Http\Controllers\Admin\OsisElectionController::class, 'pendingReport'])->middleware('permission:manage-osis-election')->name('report.pending');
        Route::get('/{election}/laporan/belum-memilih/pdf', [App\Http\Controllers\Admin\OsisElectionController::class, 'pendingReportPdf'])->middleware('permission:manage-osis-election')->name('report.pending.pdf');
        Route::get('/{election}/laporan/belum-memilih/excel', [App\Http\Controllers\Admin\OsisElectionController::class, 'pendingReportExcel'])->middleware('permission:manage-osis-election')->name('report.pending.excel');
        Route::post('/{election}/pemilih/sinkron-siswa', [App\Http\Controllers\Admin\OsisElectionController::class, 'syncStudentVoters'])->middleware('permission:manage-osis-election')->name('voters.sync-students');
        Route::post('/{election}/pemilih/{voter}/unlock', [App\Http\Controllers\Admin\OsisElectionController::class, 'unlockVoter'])->middleware('permission:manage-osis-election')->name('voters.unlock');
        Route::get('/{election}/live-polling', [App\Http\Controllers\Admin\OsisElectionController::class, 'livePolling'])->name('live-polling');
        Route::get('/{election}', [App\Http\Controllers\Admin\OsisElectionController::class, 'show'])->name('show');
        Route::get('/{election}/edit', [App\Http\Controllers\Admin\OsisElectionController::class, 'edit'])->middleware('permission:manage-osis-election')->name('edit');
        Route::put('/{election}', [App\Http\Controllers\Admin\OsisElectionController::class, 'update'])->middleware('permission:manage-osis-election')->name('update');
        Route::delete('/{election}', [App\Http\Controllers\Admin\OsisElectionController::class, 'destroy'])->middleware('permission:manage-osis-election')->name('destroy');
        Route::post('/{election}/paket', [App\Http\Controllers\Admin\OsisElectionController::class, 'storePackage'])->middleware('permission:manage-osis-election')->name('packages.store');
        Route::put('/{election}/paket/{package}', [App\Http\Controllers\Admin\OsisElectionController::class, 'updatePackage'])->middleware('permission:manage-osis-election')->name('packages.update');
        Route::delete('/{election}/paket/{package}/foto-utama', [App\Http\Controllers\Admin\OsisElectionController::class, 'deletePackageCampaignPhoto'])->middleware('permission:manage-osis-election')->name('packages.campaign-photo.destroy');
        Route::delete('/{election}/paket/{package}/galeri', [App\Http\Controllers\Admin\OsisElectionController::class, 'deletePackageLivePhoto'])->middleware('permission:manage-osis-election')->name('packages.live-photo.destroy');
        Route::delete('/{election}/paket/{package}', [App\Http\Controllers\Admin\OsisElectionController::class, 'destroyPackage'])->middleware('permission:manage-osis-election')->name('packages.destroy');
        Route::post('/{election}/publish', [App\Http\Controllers\Admin\OsisElectionController::class, 'publish'])->middleware('permission:manage-osis-election')->name('publish');
        Route::post('/{election}/pause', [App\Http\Controllers\Admin\OsisElectionController::class, 'pause'])->middleware('permission:manage-osis-election')->name('pause');
        Route::post('/{election}/resume', [App\Http\Controllers\Admin\OsisElectionController::class, 'resume'])->middleware('permission:manage-osis-election')->name('resume');
        Route::post('/{election}/close', [App\Http\Controllers\Admin\OsisElectionController::class, 'close'])->middleware('permission:manage-osis-election')->name('close');
        Route::post('/{election}/publish-results', [App\Http\Controllers\Admin\OsisElectionController::class, 'publishResults'])->middleware('permission:manage-osis-election')->name('publish-results');
    });
    
    // Sekolah Asal Management
    Route::middleware(['permission:view-siswa'])->group(function () {
        Route::get('/sekolah-asal', [App\Http\Controllers\Admin\SekolahAsalController::class, 'index'])->name('sekolah-asal.index');
        Route::post('/sekolah-asal/{sekolah}/enrich', [App\Http\Controllers\Admin\SekolahAsalController::class, 'enrich'])->name('sekolah-asal.enrich');
        Route::get('/sekolah-asal/{npsn}', [App\Http\Controllers\Admin\SekolahAsalController::class, 'show'])->name('sekolah-asal.show');
        Route::get('/sekolah-asal/{npsn}/siswa-data', [App\Http\Controllers\Admin\SekolahAsalController::class, 'getSiswaData'])->name('sekolah-asal.siswa-data');
        Route::get('/alumni', [App\Http\Controllers\Admin\AlumniController::class, 'index'])->name('alumni.index');
        Route::get('/alumni/{siswa}', [App\Http\Controllers\Admin\AlumniController::class, 'show'])->name('alumni.show');
        Route::get('/lulusan', [App\Http\Controllers\Admin\LulusanController::class, 'index'])->name('lulusan.index');
        Route::get('/lulusan/data', [App\Http\Controllers\Admin\LulusanController::class, 'data'])->name('lulusan.data');
        Route::get('/lulusan/stats', [App\Http\Controllers\Admin\LulusanController::class, 'stats'])->name('lulusan.stats');
        Route::get('/lulusan/export/excel', [App\Http\Controllers\Admin\LulusanController::class, 'exportExcel'])->name('lulusan.export-excel');
        Route::get('/lulusan/export/pdf', [App\Http\Controllers\Admin\LulusanController::class, 'exportPdf'])->name('lulusan.export-pdf');
        Route::post('/lulusan/student-access', [App\Http\Controllers\Admin\LulusanController::class, 'updateStudentAccess'])->name('lulusan.student-access');
        Route::post('/lulusan/send-graduation-emails', [App\Http\Controllers\Admin\LulusanController::class, 'sendGraduationEmails'])->name('lulusan.send-graduation-emails');
            Route::get('/kelulusan-pengumuman', [App\Http\Controllers\Admin\PengumumanKelulusanController::class, 'index'])->name('kelulusan-pengumuman.index');
            Route::post('/kelulusan-pengumuman/publish', [App\Http\Controllers\Admin\PengumumanKelulusanController::class, 'publish'])->name('kelulusan-pengumuman.publish');
            Route::post('/kelulusan-pengumuman/save', [App\Http\Controllers\Admin\PengumumanKelulusanController::class, 'save'])->name('kelulusan-pengumuman.save');
            Route::post('/kelulusan-pengumuman/reset-opened', [App\Http\Controllers\Admin\PengumumanKelulusanController::class, 'resetOpened'])->name('kelulusan-pengumuman.reset-opened');
            Route::post('/kelulusan-pengumuman/{siswa}/reset-opened', [App\Http\Controllers\Admin\PengumumanKelulusanController::class, 'resetOpenedForStudent'])->name('kelulusan-pengumuman.reset-opened-student');
    });
    Route::middleware(['permission:manage-settings'])->group(function () {
        Route::get('/referensi-perguruan-tinggi', [App\Http\Controllers\Admin\ReferensiPerguruanTinggiController::class, 'index'])->name('referensi-perguruan-tinggi.index');
        Route::post('/referensi-perguruan-tinggi', [App\Http\Controllers\Admin\ReferensiPerguruanTinggiController::class, 'store'])->name('referensi-perguruan-tinggi.store');
        Route::put('/referensi-perguruan-tinggi/{referensiPerguruanTinggi}', [App\Http\Controllers\Admin\ReferensiPerguruanTinggiController::class, 'update'])->name('referensi-perguruan-tinggi.update');
        Route::delete('/referensi-perguruan-tinggi/{referensiPerguruanTinggi}', [App\Http\Controllers\Admin\ReferensiPerguruanTinggiController::class, 'destroy'])->name('referensi-perguruan-tinggi.destroy');
        Route::get('/referensi-program-studi', [App\Http\Controllers\Admin\ReferensiProgramStudiController::class, 'index'])->name('referensi-program-studi.index');
        Route::post('/referensi-program-studi', [App\Http\Controllers\Admin\ReferensiProgramStudiController::class, 'store'])->name('referensi-program-studi.store');
        Route::put('/referensi-program-studi/{referensiProgramStudi}', [App\Http\Controllers\Admin\ReferensiProgramStudiController::class, 'update'])->name('referensi-program-studi.update');
        Route::delete('/referensi-program-studi/{referensiProgramStudi}', [App\Http\Controllers\Admin\ReferensiProgramStudiController::class, 'destroy'])->name('referensi-program-studi.destroy');
    });
    
    // Siswa Import
    Route::get('/siswa/import/form', [SiswaImportController::class, 'index'])->name('siswa.import');
    Route::get('/siswa/import/template', [SiswaImportController::class, 'downloadTemplate'])->name('siswa.import.template');
    Route::post('/siswa/import/process', [SiswaImportController::class, 'import'])->name('siswa.import.process');
    
    // EMIS Import (fitur baru - beda dari import biasa)
    Route::get('/siswa/import-emis/form', [App\Http\Controllers\Admin\EmisImportController::class, 'form'])->name('emis-import.form');
    Route::post('/siswa/import-emis/parse', [App\Http\Controllers\Admin\EmisImportController::class, 'parse'])->name('emis-import.parse');
    Route::post('/siswa/import-emis/execute', [App\Http\Controllers\Admin\EmisImportController::class, 'execute'])->name('emis-import.execute');

    // NPSN Import
    Route::get('/siswa/import-npsn/form', [App\Http\Controllers\Admin\NpsnImportController::class, 'index'])->name('siswa.import-npsn');
    Route::get('/siswa/import-npsn/template', [App\Http\Controllers\Admin\NpsnImportController::class, 'downloadTemplate'])->name('siswa.import-npsn.template');
    Route::post('/siswa/import-npsn/process', [App\Http\Controllers\Admin\NpsnImportController::class, 'import'])->name('siswa.import-npsn.process');
    
    // Custom Menu Management
    Route::resource('custom-menu', App\Http\Controllers\Admin\CustomMenuController::class);
    Route::post('/custom-menu/{customMenu}/toggle-status', [App\Http\Controllers\Admin\CustomMenuController::class, 'toggleStatus'])->name('custom-menu.toggle-status');
    Route::get('/custom-menu/{customMenu}/assign', [App\Http\Controllers\Admin\CustomMenuController::class, 'assign'])->name('custom-menu.assign');
    Route::post('/custom-menu/{customMenu}/assign-siswa', [App\Http\Controllers\Admin\CustomMenuController::class, 'assignSiswa'])->name('custom-menu.assign-siswa');
    Route::post('/custom-menu/{customMenu}/assign-by-nisn', [App\Http\Controllers\Admin\CustomMenuController::class, 'assignByNisn'])->name('custom-menu.assign-by-nisn');
    Route::post('/custom-menu/{customMenu}/remove-siswa', [App\Http\Controllers\Admin\CustomMenuController::class, 'removeSiswa'])->name('custom-menu.remove-siswa');
    Route::post('/custom-menu/{customMenu}/upload-excel', [App\Http\Controllers\Admin\CustomMenuController::class, 'uploadExcel'])->name('custom-menu.upload-excel');
    Route::get('/custom-menu/{customMenu}/template', [App\Http\Controllers\Admin\CustomMenuController::class, 'downloadTemplate'])->name('custom-menu.template');
    Route::get('/custom-menu/{customMenu}/get-siswa', [App\Http\Controllers\Admin\CustomMenuController::class, 'getSiswaList'])->name('custom-menu.get-siswa');
    Route::get('/custom-menu/{customMenu}/get-siswa-by-kelas', [App\Http\Controllers\Admin\CustomMenuController::class, 'getSiswaByKelas'])->name('custom-menu.get-siswa-by-kelas');
    
    // User Monitoring
    Route::get('/monitoring/users', [App\Http\Controllers\Admin\UserMonitoringController::class, 'index'])->name('monitoring.users');
    Route::get('/monitoring/users/{user}', [App\Http\Controllers\Admin\UserMonitoringController::class, 'show'])->name('monitoring.users.show');
    Route::get('/monitoring/online-count', [App\Http\Controllers\Admin\UserMonitoringController::class, 'getOnlineCount'])->name('monitoring.online-count');
    Route::post('/monitoring/users/{user}/force-logout', [App\Http\Controllers\Admin\UserMonitoringController::class, 'forceLogout'])->name('monitoring.users.force-logout');
    
    // Pengaturan - Cek NIP (Super Admin Only)
    Route::get('/pengaturan/cek-nip', [App\Http\Controllers\Admin\NipCheckerController::class, 'index'])->name('pengaturan.cek-nip.index');
    Route::post('/pengaturan/cek-nip/check', [App\Http\Controllers\Admin\NipCheckerController::class, 'check'])->name('pengaturan.cek-nip.check');

    // Pengaturan - Cek NIK Dukcapil (Super Admin Only)
    Route::get('/pengaturan/cek-nik', [App\Http\Controllers\Admin\NikCheckerController::class, 'index'])->name('pengaturan.cek-nik.index');
    Route::post('/pengaturan/cek-nik/check', [App\Http\Controllers\Admin\NikCheckerController::class, 'check'])->name('pengaturan.cek-nik.check');
    
    // Pengaturan - Cek NISN (Super Admin Only)
    Route::get('/pengaturan/cek-nisn', [App\Http\Controllers\Admin\NisnCheckerController::class, 'index'])->name('pengaturan.cek-nisn.index');
    Route::post('/pengaturan/cek-nisn/check', [App\Http\Controllers\Admin\NisnCheckerController::class, 'check'])->name('pengaturan.cek-nisn.check');
    
    // Pengaturan - Update EMIS Token (Super Admin Only)
    Route::get('/pengaturan/update-api-token', [App\Http\Controllers\Admin\ApiTokenController::class, 'index'])->name('pengaturan.update-api-token.index');
    Route::post('/pengaturan/update-api-token', [App\Http\Controllers\Admin\ApiTokenController::class, 'update'])->name('pengaturan.update-api-token.update');
    
    // Pengaturan - Reset System (Super Admin Only)
    Route::get('/pengaturan/reset-system', [App\Http\Controllers\Admin\SystemResetController::class, 'index'])->name('reset-system.index');
    Route::post('/pengaturan/reset-system/verify-password', [App\Http\Controllers\Admin\SystemResetController::class, 'verifyPassword'])->name('reset-system.verify-password');
    Route::post('/pengaturan/reset-system/delete-all', [App\Http\Controllers\Admin\SystemResetController::class, 'deleteAll'])->name('reset-system.delete-all');
    Route::post('/pengaturan/reset-system/delete-siswa', [App\Http\Controllers\Admin\SystemResetController::class, 'deleteSiswa'])->name('reset-system.delete-siswa');
    Route::post('/pengaturan/reset-system/delete-gtk', [App\Http\Controllers\Admin\SystemResetController::class, 'deleteGtk'])->name('reset-system.delete-gtk');
    Route::post('/pengaturan/reset-system/delete-kelas', [App\Http\Controllers\Admin\SystemResetController::class, 'deleteKelas'])->name('reset-system.delete-kelas');
    Route::post('/pengaturan/reset-system/create-backup', [App\Http\Controllers\Admin\SystemResetController::class, 'createBackup'])->name('reset-system.create-backup');
    Route::get('/pengaturan/reset-system/download-backup/{filename}', [App\Http\Controllers\Admin\SystemResetController::class, 'downloadBackup'])->name('reset-system.download-backup');
    Route::delete('/pengaturan/reset-system/delete-backup/{filename}', [App\Http\Controllers\Admin\SystemResetController::class, 'deleteBackup'])->name('reset-system.delete-backup');
    Route::post('/pengaturan/reset-system/restore-backup', [App\Http\Controllers\Admin\SystemResetController::class, 'restoreBackup'])->name('reset-system.restore-backup');
    
    // Tahun Pelajaran Management
    Route::resource('tahun-pelajaran', TahunPelajaranController::class);
    Route::post('/tahun-pelajaran/{tahunPelajaran}/set-active', [TahunPelajaranController::class, 'setActive'])->name('tahun-pelajaran.set-active');
    Route::post('/tahun-pelajaran/{tahunPelajaran}/change-semester', [TahunPelajaranController::class, 'changeSemester'])->name('tahun-pelajaran.change-semester');
    
    // Kurikulum Management
    Route::resource('kurikulum', KurikulumController::class);
    Route::post('/kurikulum/{kurikulum}/activate', [KurikulumController::class, 'activate'])->name('kurikulum.activate');
    Route::post('/kurikulum/{kurikulum}/deactivate', [KurikulumController::class, 'deactivate'])->name('kurikulum.deactivate');
    
    // Jurusan Management (nested in Kurikulum)
    Route::post('/kurikulum/{kurikulum}/jurusan', [KurikulumController::class, 'storeJurusan'])->name('kurikulum.jurusan.store')->middleware('permission:manage-jurusan');
    Route::put('/kurikulum/{kurikulum}/jurusan/{jurusan}', [KurikulumController::class, 'updateJurusan'])->name('kurikulum.jurusan.update')->middleware('permission:manage-jurusan');
    Route::delete('/kurikulum/{kurikulum}/jurusan/{jurusan}', [KurikulumController::class, 'deleteJurusan'])->name('kurikulum.jurusan.delete')->middleware('permission:manage-jurusan');
    
    // Mata Pelajaran Management
    Route::resource('mapel', \App\Http\Controllers\Admin\MataPelajaranController::class);
    Route::get('/mapel-data', [\App\Http\Controllers\Admin\MataPelajaranController::class, 'data'])->name('mapel.data');
    Route::post('/mapel-bulk-store', [\App\Http\Controllers\Admin\MataPelajaranController::class, 'bulkStore'])->name('mapel.bulk-store');
    Route::post('/mapel/{mapel}/toggle-status', [\App\Http\Controllers\Admin\MataPelajaranController::class, 'toggleStatus'])->name('mapel.toggle-status');
    Route::post('/mapel/{mapel}/duplicate', [\App\Http\Controllers\Admin\MataPelajaranController::class, 'duplicate'])->name('mapel.duplicate');
    
    // Nilai Siswa Management (Legger untuk SPAN-PTKIN)
    Route::get('/nilai', [\App\Http\Controllers\Admin\NilaiController::class, 'index'])->name('nilai.index');
    Route::get('/nilai/semester/{semester}', [\App\Http\Controllers\Admin\NilaiController::class, 'semester'])->name('nilai.semester');
    Route::get('/nilai/upload', [\App\Http\Controllers\Admin\NilaiController::class, 'uploadForm'])->name('nilai.upload-form');
    Route::post('/nilai/upload', [\App\Http\Controllers\Admin\NilaiController::class, 'upload'])->name('nilai.upload');
    Route::get('/nilai/preview', [\App\Http\Controllers\Admin\NilaiController::class, 'preview'])->name('nilai.preview');
    Route::post('/nilai/confirm-upload', [\App\Http\Controllers\Admin\NilaiController::class, 'confirmUpload'])->name('nilai.confirm-upload');
    Route::get('/nilai/cancel-upload', [\App\Http\Controllers\Admin\NilaiController::class, 'cancelUpload'])->name('nilai.cancel-upload');
    Route::get('/nilai/template', [\App\Http\Controllers\Admin\NilaiController::class, 'downloadTemplate'])->name('nilai.template');
    Route::get('/nilai/export-legger', [\App\Http\Controllers\Admin\NilaiController::class, 'exportLeggerForm'])->name('nilai.export-legger-form');
    Route::get('/nilai/export-legger/download', [\App\Http\Controllers\Admin\NilaiController::class, 'exportLegger'])->name('nilai.export-legger');
    Route::get('/nilai/export-span', [\App\Http\Controllers\Admin\NilaiController::class, 'exportSpan'])->name('nilai.export-span');
    Route::get('/nilai/perangkingan', [\App\Http\Controllers\Admin\NilaiController::class, 'ranking'])->name('nilai.ranking');
    Route::get('/nilai/perangkingan/export', [\App\Http\Controllers\Admin\NilaiController::class, 'exportRanking'])->name('nilai.ranking-export');
    Route::get('/nilai/siswa/{siswa}', [\App\Http\Controllers\Admin\NilaiController::class, 'siswa'])->name('nilai.siswa');
    Route::delete('/nilai/semester/{semester}', [\App\Http\Controllers\Admin\NilaiController::class, 'deleteSemester'])->name('nilai.delete-semester');
    Route::post('/nilai/semester/{semester}/export-preview', [\App\Http\Controllers\Admin\NilaiController::class, 'exportSemesterPreview'])->name('nilai.export-semester-preview');
    Route::get('/nilai/semester/{semester}/export-download', [\App\Http\Controllers\Admin\NilaiController::class, 'exportSemesterDownload'])->name('nilai.export-semester-download');

    // Integrasi RDM
    Route::middleware(['permission:view-kurikulum'])->group(function () {
        Route::get('/rdm-sync', [RdmSyncController::class, 'index'])->name('rdm-sync.index');
        Route::post('/rdm-sync/preview', [RdmSyncController::class, 'preview'])->name('rdm-sync.preview');
        Route::post('/rdm-sync/{run}/apply', [RdmSyncController::class, 'apply'])->name('rdm-sync.apply');

        // Mapping Mapel RDM
        Route::get('/rdm-mapel-mapping', [RdmMapelMappingController::class, 'index'])->name('rdm-mapel-mapping.index');
        Route::post('/rdm-mapel-mapping', [RdmMapelMappingController::class, 'store'])->name('rdm-mapel-mapping.store');
        Route::post('/rdm-mapel-mapping/auto-map', [RdmMapelMappingController::class, 'autoMap'])->name('rdm-mapel-mapping.auto-map');
        Route::post('/rdm-mapel-mapping/bulk', [RdmMapelMappingController::class, 'bulkStore'])->name('rdm-mapel-mapping.bulk-store');
        Route::delete('/rdm-mapel-mapping/{mapping}', [RdmMapelMappingController::class, 'destroy'])->name('rdm-mapel-mapping.destroy');

        // Matching Siswa RDM vs SIMANSA
        Route::get('/rdm-matching', [RdmMatchingController::class, 'index'])->name('rdm-matching.index');
        Route::post('/rdm-matching/run', [RdmMatchingController::class, 'run'])->name('rdm-matching.run');
    });
    
    // Proses Akhir Tahun (Naik Kelas & Kelulusan)
    Route::middleware(['permission:manage-settings'])->group(function () {
        Route::get('/kenaikan-kelas', [KenaikanKelasController::class, 'index'])->name('kenaikan-kelas.index');
        Route::get('/kenaikan-kelas/data', [KenaikanKelasController::class, 'getData'])->name('kenaikan-kelas.data');
        Route::get('/kenaikan-kelas/preview', [KenaikanKelasController::class, 'previewSiswaKelas'])->name('kenaikan-kelas.preview');
        Route::get('/kenaikan-kelas/kelas-by-tahun', [KenaikanKelasController::class, 'getKelasByTahun'])->name('kenaikan-kelas.kelas-by-tahun');
        Route::get('/kenaikan-kelas/status-kelulusan', [KenaikanKelasController::class, 'statusKelulusan'])->name('kenaikan-kelas.status-kelulusan');
        Route::post('/kenaikan-kelas/proses-kelulusan', [KenaikanKelasController::class, 'prosesKelulusan'])->name('kenaikan-kelas.proses-kelulusan');
        Route::post('/kenaikan-kelas/proses-naik-kelas', [KenaikanKelasController::class, 'prosesNaikKelas'])->name('kenaikan-kelas.proses-naik-kelas');
    });

    Route::middleware(['permission:manage-kelas'])->prefix('matrikulasi-ppdb')->name('matrikulasi-ppdb.')->group(function () {
        Route::get('/', [MatrikulasiPpdbController::class, 'index'])->name('index');
        Route::post('/kelompok', [MatrikulasiPpdbController::class, 'storeKelompok'])->name('kelompok.store');
        Route::get('/candidates', [MatrikulasiPpdbController::class, 'candidates'])->name('candidates');
        Route::get('/browser-candidates', [MatrikulasiPpdbController::class, 'browserCandidates'])->name('browser-candidates');
        Route::get('/peserta', [MatrikulasiPpdbController::class, 'peserta'])->name('peserta');
        Route::get('/peserta-ids', [MatrikulasiPpdbController::class, 'pesertaIds'])->name('peserta-ids');
        Route::post('/assign-kelompok', [MatrikulasiPpdbController::class, 'assignKelompok'])->name('assign-kelompok');
        Route::post('/update-validation', [MatrikulasiPpdbController::class, 'updateValidation'])->name('update-validation');
        Route::post('/generate-accounts', [MatrikulasiPpdbController::class, 'generateAccounts'])->name('generate-accounts');
        Route::post('/promote-to-siswa', [MatrikulasiPpdbController::class, 'promoteToSiswa'])->name('promote-to-siswa');
        Route::post('/preview', [MatrikulasiPpdbController::class, 'preview'])->name('preview');
        Route::post('/preview-all', [MatrikulasiPpdbController::class, 'previewAll'])->name('preview-all');
        Route::post('/import', [MatrikulasiPpdbController::class, 'import'])->name('import');
    });

    // Mutasi Siswa
    Route::prefix('mutasi-siswa')->name('mutasi-siswa.')->group(function () {
        Route::get('/', [MutasiSiswaController::class, 'index'])->name('index');
        Route::get('/create', [MutasiSiswaController::class, 'create'])->name('create');
        Route::post('/', [MutasiSiswaController::class, 'store'])->name('store');
        Route::get('/search-siswa', [MutasiSiswaController::class, 'searchSiswa'])->name('search-siswa');
        Route::get('/lookup-npsn', [MutasiSiswaController::class, 'lookupNpsn'])->name('lookup-npsn');
        Route::get('/{mutasiSiswa}', [MutasiSiswaController::class, 'show'])->name('show');
        Route::get('/{mutasiSiswa}/edit', [MutasiSiswaController::class, 'edit'])->name('edit');
        Route::put('/{mutasiSiswa}', [MutasiSiswaController::class, 'update'])->name('update');
        Route::delete('/{mutasiSiswa}', [MutasiSiswaController::class, 'destroy'])->name('destroy');
        Route::post('/{mutasiSiswa}/approve', [MutasiSiswaController::class, 'approve'])->name('approve');
        Route::post('/{mutasiSiswa}/reject', [MutasiSiswaController::class, 'reject'])->name('reject');
        Route::post('/{mutasiSiswa}/upload-dokumen', [MutasiSiswaController::class, 'uploadDokumen'])->name('upload-dokumen');
    });

    // Kelas Management
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
    Route::post('/kelas/{id}/restore', [KelasController::class, 'restore'])->name('kelas.restore')->middleware('permission:create-kelas');
    Route::get('/kelas/{kelas}/assign-siswa', [KelasController::class, 'assignSiswa'])->name('kelas.assign-siswa')->middleware('permission:assign-siswa-kelas');
    Route::get('/kelas/{kelas}/siswa/available', [KelasController::class, 'getAvailableSiswa'])->name('kelas.siswa.available')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa', [KelasController::class, 'storeSiswa'])->name('kelas.siswa.store')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa/nisn', [KelasController::class, 'storeSiswaNISN'])->name('kelas.siswa.store-nisn')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa/verifikasi-keberadaan-semua', [KelasController::class, 'verifikasiKeberadaanSemua'])->name('kelas.siswa.verifikasi-keberadaan-semua')->middleware('can:super-admin-access');
    Route::delete('/kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'removeSiswa'])->name('kelas.siswa.remove')->middleware('permission:remove-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa/{siswa}/pindah-rombel', [KelasController::class, 'transferSiswa'])->name('kelas.siswa.transfer')->middleware('permission:transfer-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa/{siswa}/verifikasi-keberadaan', [KelasController::class, 'toggleKeberadaanSiswa'])->name('kelas.siswa.toggle-keberadaan')->middleware('can:super-admin-access');
    Route::post('/kelas/{kelas}/wali-kelas', [KelasController::class, 'assignWaliKelas'])->name('kelas.wali-kelas')->middleware('permission:assign-wali-kelas');
    Route::post('/kelas/{kelas}/ketua-kelas', [KelasController::class, 'assignKetuaKelas'])->name('kelas.ketua-kelas')->middleware('permission:edit-kelas');
    Route::post('/kelas/{kelas}/toggle-asrama', [KelasController::class, 'toggleAsrama'])->name('kelas.toggle-asrama')->middleware('permission:edit-kelas');
    Route::post('/kelas/{kelas}/kosongkan', [KelasController::class, 'kosongkanKelas'])->name('kelas.kosongkan')->middleware('permission:remove-siswa-kelas');
    Route::get('/kelas/{kelas}/cetak-absensi', [KelasController::class, 'cetakAbsensi'])->name('kelas.cetak-absensi');
    
    // GTK Personal (Dashboard & Profile for GTK users)
    Route::post('/gtk/impersonation/stop', [App\Http\Controllers\Admin\UserImpersonationController::class, 'stopGtk'])
        ->middleware('impersonation:gtk')
        ->name('gtk.impersonation.stop');

    Route::middleware(['impersonation:gtk', 'permission:view-gtk-dashboard'])->group(function () {
        Route::get('/gtk/dashboard', [App\Http\Controllers\Admin\GtkDashboardController::class, 'index'])->name('gtk.dashboard');
        Route::get('/gtk/pemilihan-osis', [App\Http\Controllers\Admin\GtkOsisElectionController::class, 'index'])->name('gtk.osis-election.index');
        Route::post('/gtk/pemilihan-osis/{election}/pilih', [App\Http\Controllers\Admin\GtkOsisElectionController::class, 'vote'])
            ->middleware('throttle:5,1')->name('gtk.osis-election.vote');
        Route::get('/gtk/polling', [App\Http\Controllers\PollingResponseController::class, 'index'])->name('gtk.polling.index');
        Route::get('/gtk/polling/{polling}', [App\Http\Controllers\PollingResponseController::class, 'show'])->name('gtk.polling.show');
        Route::post('/gtk/polling/{polling}', [App\Http\Controllers\PollingResponseController::class, 'store'])->name('gtk.polling.store');
        Route::post('/gtk/polling/{polling}/unlock-request', [App\Http\Controllers\PollingResponseController::class, 'requestUnlock'])->name('gtk.polling.unlock-request');
        Route::post('/gtk/polling/{polling}/snooze', [App\Http\Controllers\PollingResponseController::class, 'snooze'])->name('gtk.polling.snooze');
    });
    
    // ─── Portal Wali Kelas ("Kelas Saya") ──────────────────────────────────────
    // GTK murni yang menjadi wali kelas aktif. Semua di-scope ketat ke rombelnya.
    Route::middleware(['impersonation:gtk', 'can:sidebar-wali-kelas-menu'])
        ->prefix('gtk/wali')->name('gtk.wali.')->group(function () {
            Route::get('/siswa', [App\Http\Controllers\Admin\WaliKelas\SiswaController::class, 'index'])->name('siswa.index');
            Route::get('/siswa/{siswa}', [App\Http\Controllers\Admin\WaliKelas\SiswaController::class, 'show'])->name('siswa.show');

            Route::get('/absensi', [App\Http\Controllers\Admin\WaliKelas\AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('/absensi', [App\Http\Controllers\Admin\WaliKelas\AbsensiController::class, 'store'])->name('absensi.store');
            Route::get('/absensi/rekap', [App\Http\Controllers\Admin\WaliKelas\AbsensiController::class, 'rekap'])->name('absensi.rekap');

            Route::get('/catatan', [App\Http\Controllers\Admin\WaliKelas\CatatanController::class, 'index'])->name('catatan.index');
            Route::post('/catatan', [App\Http\Controllers\Admin\WaliKelas\CatatanController::class, 'store'])->name('catatan.store');
            Route::put('/catatan/{catatan}', [App\Http\Controllers\Admin\WaliKelas\CatatanController::class, 'update'])->name('catatan.update');
            Route::delete('/catatan/{catatan}', [App\Http\Controllers\Admin\WaliKelas\CatatanController::class, 'destroy'])->name('catatan.destroy');

            Route::get('/jadwal', [App\Http\Controllers\Admin\WaliKelas\JadwalController::class, 'index'])->name('jadwal.index');
        });
    
    Route::middleware(['impersonation:gtk', 'permission:change-password-gtk'])->group(function () {
        Route::get('/gtk/profile/password', [App\Http\Controllers\Admin\GtkProfileController::class, 'password'])->name('gtk.profile.password');
        Route::put('/gtk/profile/password', [App\Http\Controllers\Admin\GtkProfileController::class, 'updatePassword'])->name('gtk.profile.password.update');
    });
    
    Route::middleware(['impersonation:gtk', 'permission:edit-gtk-profile'])->group(function () {
        Route::get('/gtk/profile', [App\Http\Controllers\Admin\GtkProfileController::class, 'index'])->name('gtk.profile');
        Route::put('/gtk/profile/diri', [App\Http\Controllers\Admin\GtkProfileController::class, 'updateDiri'])->name('gtk.profile.diri.update');
        Route::put('/gtk/profile/kepeg', [App\Http\Controllers\Admin\GtkProfileController::class, 'updateKepeg'])->name('gtk.profile.kepeg.update');
        
        // AJAX routes for address dropdowns
        Route::get('/gtk/api/cities/{provinsi}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getCities'])->name('gtk.api.cities');
        Route::get('/gtk/api/districts/{kabupaten}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getDistricts'])->name('gtk.api.districts');
        Route::get('/gtk/api/villages/{kecamatan}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getVillages'])->name('gtk.api.villages');
    });
    
    // ─── Verifikasi Ijazah SMP/MTs ─────────────────────────────────────────────
    Route::middleware(['permission:verifikasi-ijazah'])->prefix('verifikasi-ijazah')->name('verifikasi-ijazah.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\VerifikasiIjazahController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Admin\VerifikasiIjazahController::class, 'data'])->name('data');
        Route::get('/{siswa}', [App\Http\Controllers\Admin\VerifikasiIjazahController::class, 'show'])->name('show');
        Route::post('/{siswa}', [App\Http\Controllers\Admin\VerifikasiIjazahController::class, 'store'])->name('store');
        Route::post('/{siswa}/refresh-emis', [App\Http\Controllers\Admin\VerifikasiIjazahController::class, 'refreshEmis'])->name('refresh-emis');
    });

    // Data Siswa KIP/SKTM
    Route::middleware(['permission:view-pip'])->prefix('kip-sktm')->name('kip-sktm.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SiswaPipController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Admin\SiswaPipController::class, 'data'])->name('data');
    });

    Route::middleware(['permission:view-pip'])->prefix('pip')->name('pip.')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.kip-sktm.index'))->name('index');
        Route::get('/data', [App\Http\Controllers\Admin\SiswaPipController::class, 'data'])->name('data');
    });

    // GTK Management (for Admin/Super Admin)
    Route::middleware(['permission:view-gtk'])->group(function () {
        Route::get('/gtk-data', [App\Http\Controllers\Admin\GtkController::class, 'data'])->name('gtk.data');
        Route::get('/gtk', [App\Http\Controllers\Admin\GtkController::class, 'index'])->name('gtk.index');
        Route::get('/gtk/{gtk}', [App\Http\Controllers\Admin\GtkController::class, 'show'])->name('gtk.show');
    });
    
    Route::middleware(['permission:create-gtk'])->group(function () {
        Route::post('/gtk', [App\Http\Controllers\Admin\GtkController::class, 'store'])->name('gtk.store');
    });
    
    Route::middleware(['permission:edit-gtk'])->group(function () {
        Route::get('/gtk/{gtk}/edit', [App\Http\Controllers\Admin\GtkController::class, 'edit'])->name('gtk.edit');
        Route::put('/gtk/{gtk}', [App\Http\Controllers\Admin\GtkController::class, 'update'])->name('gtk.update');
        Route::post('/gtk/{gtk}/upload-foto', [App\Http\Controllers\Admin\GtkController::class, 'uploadFoto'])->name('gtk.upload-foto');
        Route::delete('/gtk/{gtk}/delete-foto', [App\Http\Controllers\Admin\GtkController::class, 'deleteFoto'])->name('gtk.delete-foto');
        // API for cascade dropdown
        Route::get('/api/cities/{province}', [App\Http\Controllers\Admin\GtkController::class, 'getCities'])->name('admin.api.cities');
        Route::get('/api/districts/{city}', [App\Http\Controllers\Admin\GtkController::class, 'getDistricts'])->name('admin.api.districts');
        Route::get('/api/villages/{district}', [App\Http\Controllers\Admin\GtkController::class, 'getVillages'])->name('admin.api.villages');
    });
    
    Route::middleware(['permission:delete-gtk'])->group(function () {
        Route::delete('/gtk/{gtk}', [App\Http\Controllers\Admin\GtkController::class, 'destroy'])->name('gtk.destroy');
    });
    
    Route::middleware(['permission:reset-password-gtk'])->group(function () {
        Route::put('/gtk/{gtk}/reset-password', [App\Http\Controllers\Admin\GtkController::class, 'resetPassword'])->name('gtk.reset-password');
    });
    
    // GTK Kemenag Sync
    Route::middleware(['permission:edit-gtk'])->group(function () {
        Route::get('/gtk/sync-kemenag/candidates', [App\Http\Controllers\Admin\GtkController::class, 'syncKemenagCandidates'])->name('gtk.sync-kemenag-candidates');
        Route::post('/gtk/{gtk}/sync-kemenag', [App\Http\Controllers\Admin\GtkController::class, 'syncKemenag'])->name('gtk.sync-kemenag');
        Route::post('/gtk/{gtk}/apply-kemenag-data', [App\Http\Controllers\Admin\GtkController::class, 'applyKemenagData'])->name('gtk.apply-kemenag-data');
    });
    
    // GTK Import
    Route::middleware(['permission:create-gtk'])->group(function () {
        Route::get('/gtk/import/form', [App\Http\Controllers\Admin\GtkImportController::class, 'index'])->name('gtk.import');
        Route::get('/gtk/import/template', [App\Http\Controllers\Admin\GtkImportController::class, 'downloadTemplate'])->name('gtk.import.template');
        Route::post('/gtk/import/process', [App\Http\Controllers\Admin\GtkImportController::class, 'import'])->name('gtk.import.process');
    });
    
    // User Management
    Route::get('/users-data', [App\Http\Controllers\Admin\UserController::class, 'data'])->name('users.data');
    Route::put('/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('/users/{user}/assign-role-form', [App\Http\Controllers\Admin\UserController::class, 'assignRoleForm'])->name('users.assign-role-form');
    Route::post('/users/{user}/assign-role', [App\Http\Controllers\Admin\UserController::class, 'assignRole'])->name('users.assign-role');
    Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Permission Matrix (Enhanced RBAC UI)
    Route::get('/permission-matrix', [App\Http\Controllers\Admin\UserController::class, 'permissionMatrix'])->name('users.permission-matrix');
    Route::post('/permission-matrix/update', [App\Http\Controllers\Admin\UserController::class, 'updatePermissionMatrix'])->name('permission-matrix.update');
    Route::get('/permission-matrix/scan', [App\Http\Controllers\Admin\UserController::class, 'scanPermissions'])->name('permission-matrix.scan');
    Route::post('/permission-matrix/sync', [App\Http\Controllers\Admin\UserController::class, 'syncPermissions'])->name('permission-matrix.sync');
    Route::post('/permission-matrix/role/store', [App\Http\Controllers\Admin\UserController::class, 'storeRole'])->name('permission-matrix.role.store');
    Route::post('/permission-matrix/role/bulk', [App\Http\Controllers\Admin\UserController::class, 'bulkUpdateRolePermissions'])->name('permission-matrix.role.bulk');
    Route::delete('/permission-matrix/role/{role}', [App\Http\Controllers\Admin\UserController::class, 'destroyRole'])->name('permission-matrix.role.destroy');
    
    // Role & Permission Management (RBAC)
    Route::middleware(['permission:assign-roles'])->group(function () {
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
        Route::post('/roles/{role}/assign-user', [App\Http\Controllers\Admin\RoleController::class, 'assignUser'])->name('roles.assign-user');
        Route::delete('/roles/{role}/remove-user', [App\Http\Controllers\Admin\RoleController::class, 'removeUser'])->name('roles.remove-user');
    });
    
    Route::middleware(['permission:assign-permissions'])->group(function () {
        Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class);
        Route::post('/permissions/bulk-create', [App\Http\Controllers\Admin\PermissionController::class, 'bulkCreate'])->name('permissions.bulk-create');
    });
    
    // Tugas Tambahan Management
    Route::post('/users/{user}/tugas-tambahan', [App\Http\Controllers\Admin\UserController::class, 'assignTugasTambahan'])->name('users.tugas-tambahan.assign');
    Route::post('/tugas-tambahan/{tugasTambahan}/deactivate', [App\Http\Controllers\Admin\UserController::class, 'deactivateTugasTambahan'])->name('tugas-tambahan.deactivate');
    Route::post('/tugas-tambahan/{tugasTambahan}/activate', [App\Http\Controllers\Admin\UserController::class, 'activateTugasTambahan'])->name('tugas-tambahan.activate');
    Route::delete('/tugas-tambahan/{tugasTambahan}', [App\Http\Controllers\Admin\UserController::class, 'deleteTugasTambahan'])->name('tugas-tambahan.delete');
    
    // Activity Logs
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/data', [App\Http\Controllers\Admin\ActivityLogController::class, 'getData'])->name('activity-logs.data');
    Route::get('/activity-logs/{id}', [App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('/activity-logs/statistics/data', [App\Http\Controllers\Admin\ActivityLogController::class, 'statistics'])->name('activity-logs.statistics');
    Route::get('/activity-logs/export/csv', [App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('activity-logs.export');
    
    // App Settings
    Route::middleware(['permission:manage-settings'])->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\AppSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [App\Http\Controllers\Admin\AppSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/fetch-school-data', [App\Http\Controllers\Admin\AppSettingController::class, 'fetchSchoolData'])->name('settings.fetch-school-data');
        Route::get('/settings/academic-health', [App\Http\Controllers\Admin\AcademicHealthController::class, 'index'])->name('settings.academic-health');
        Route::get('/settings/server-info', [App\Http\Controllers\Admin\ServerInfoController::class, 'index'])->name('settings.server-info');
        Route::post('/settings/upload-logo-kemenag', [App\Http\Controllers\Admin\AppSettingController::class, 'uploadLogoKemenag'])->name('settings.upload-logo-kemenag');
        Route::post('/settings/upload-logo-sekolah', [App\Http\Controllers\Admin\AppSettingController::class, 'uploadLogoSekolah'])->name('settings.upload-logo-sekolah');
        Route::post('/settings/upload-kop-surat', [App\Http\Controllers\Admin\AppSettingController::class, 'uploadKopSurat'])->name('settings.upload-kop-surat');
        
        // SMTP Settings
        Route::get('/settings/smtp', [App\Http\Controllers\Admin\AppSettingController::class, 'smtpSettings'])->name('settings.smtp');
        Route::put('/settings/smtp', [App\Http\Controllers\Admin\AppSettingController::class, 'updateSmtp'])->name('settings.smtp.update');
        Route::post('/settings/smtp/test', [App\Http\Controllers\Admin\AppSettingController::class, 'testSmtp'])->name('settings.smtp.test');
        
        // Email Logs
        Route::get('/email-logs', [App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('email-logs.index');
        Route::get('/email-logs/{emailLog}', [App\Http\Controllers\Admin\EmailLogController::class, 'show'])->name('email-logs.show');
        Route::post('/email-logs/cleanup', [App\Http\Controllers\Admin\EmailLogController::class, 'cleanup'])->name('email-logs.cleanup');
        
        // Email Templates
        Route::resource('email-templates', App\Http\Controllers\Admin\EmailTemplateController::class);
        Route::get('/email-templates/{emailTemplate}/preview', [App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('email-templates.preview');
        Route::post('/email-templates/preview-form', [App\Http\Controllers\Admin\EmailTemplateController::class, 'previewForm'])->name('email-templates.preview-form');
        Route::post('/email-templates/{emailTemplate}/duplicate', [App\Http\Controllers\Admin\EmailTemplateController::class, 'duplicate'])->name('email-templates.duplicate');
        Route::post('/email-templates/{emailTemplate}/toggle-status', [App\Http\Controllers\Admin\EmailTemplateController::class, 'toggleStatus'])->name('email-templates.toggle-status');
        Route::post('/email-templates/seed-defaults', [App\Http\Controllers\Admin\EmailTemplateController::class, 'seedDefaults'])->name('email-templates.seed-defaults');
        Route::post('/email-templates/{emailTemplate}/reset-default', [App\Http\Controllers\Admin\EmailTemplateController::class, 'resetToDefault'])->name('email-templates.reset-default');
    });
    
    Route::middleware(['permission:view-student-attendance'])->group(function () {
        Route::get('/absensi-siswa', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'index'])->name('absensi-siswa.index');
        Route::post('/absensi-siswa', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'store'])->name('absensi-siswa.store');
    });
    Route::get('/absensi-siswa/pemantauan', [App\Http\Controllers\Admin\AbsensiSiswaController::class, 'monitoring'])
        ->middleware('permission:monitor-all-student-attendance')
        ->name('absensi-siswa.monitoring');
    Route::middleware(['permission:view-attendance-analytics'])->group(function () {
        Route::get('/absensi-siswa/analitik', [App\Http\Controllers\Admin\StudentAttendanceAnalyticsController::class, 'index'])->name('absensi-siswa.analytics');
        Route::get('/absensi-siswa/analitik/siswa/{siswa}', [App\Http\Controllers\Admin\StudentAttendanceAnalyticsController::class, 'student'])->name('absensi-siswa.analytics.student');
    });
    Route::post('/absensi-siswa/analitik/generate', [App\Http\Controllers\Admin\StudentAttendanceAnalyticsController::class, 'generate'])
        ->middleware('permission:manage-attendance-alerts')->name('absensi-siswa.analytics.generate');
    Route::put('/absensi-siswa/analitik/alert/{alert}', [App\Http\Controllers\Admin\StudentAttendanceAnalyticsController::class, 'updateAlert'])
        ->middleware('permission:manage-attendance-alerts')->name('absensi-siswa.analytics.alert.update');

    // Cetak (Print Reports)
    Route::middleware(['permission:view-kelas'])->group(function () {
        Route::get('/cetak', [App\Http\Controllers\Admin\CetakController::class, 'index'])->name('cetak.index');
        Route::post('/cetak/absensi-batch', [App\Http\Controllers\Admin\CetakController::class, 'cetakAbsensiBatch'])->name('cetak.absensi-batch');
        Route::post('/cetak/absensi-batch/export', [App\Http\Controllers\Admin\CetakController::class, 'exportAbsensiBatch'])->name('cetak.absensi-batch.export');
        Route::get('/cetak/kelas-by-filter', [App\Http\Controllers\Admin\CetakController::class, 'getKelasByFilter'])->name('cetak.kelas-by-filter');
    });

    // Cetak ID Card Siswa
    Route::get('/cetak/id-card-siswa', [App\Http\Controllers\Admin\CetakController::class, 'idCardSiswaIndex'])->name('cetak.id-card-siswa.index')->middleware('permission:view-siswa');
    Route::post('/cetak/id-card-siswa', [App\Http\Controllers\Admin\CetakController::class, 'cetakIdCardSiswa'])->name('cetak.id-card-siswa')->middleware('permission:view-siswa');

    // Cetak ID Card GTK
    Route::get('/cetak/id-card-gtk', [App\Http\Controllers\Admin\CetakController::class, 'idCardGtkIndex'])->name('cetak.id-card-gtk.index')->middleware('permission:view-gtk');
    Route::post('/cetak/id-card-gtk', [App\Http\Controllers\Admin\CetakController::class, 'cetakIdCardGtk'])->name('cetak.id-card-gtk')->middleware('permission:view-gtk');
    Route::get('/cetak/gtk-by-filter', [App\Http\Controllers\Admin\CetakController::class, 'getGtkByFilter'])->name('cetak.gtk-by-filter')->middleware('permission:view-gtk');

    // Download foto siswa asli per kelas (tahun pelajaran aktif)
    Route::middleware(['permission:download-foto-kelas'])->prefix('cetak/download-foto-siswa')->name('cetak.download-foto-siswa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CetakController::class, 'photoDownloadIndex'])->name('index');
        Route::get('/kelas', [App\Http\Controllers\Admin\CetakController::class, 'photoClasses'])->name('classes');
        Route::post('/preview', [App\Http\Controllers\Admin\CetakController::class, 'photoPreview'])->name('preview');
        Route::post('/arsip', [App\Http\Controllers\Admin\CetakController::class, 'photoArchiveStart'])->name('archive.start');
        Route::post('/arsip/{token}/proses', [App\Http\Controllers\Admin\CetakController::class, 'photoArchiveProcess'])->name('archive.process');
        Route::get('/arsip/{token}/download', [App\Http\Controllers\Admin\CetakController::class, 'photoArchiveDownload'])->name('archive.download');
    });

    // ==================== FITUR BARU: PENGUMUMAN ====================
    Route::resource('pengumuman', App\Http\Controllers\Admin\PengumumanController::class);
    
    // ==================== FITUR BARU: KALENDER AKADEMIK ====================
    Route::get('/kalender-akademik', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'index'])->name('kalender-akademik.index');
    Route::get('/kalender-akademik/events', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'getEvents'])->name('kalender-akademik.events');
    Route::post('/kalender-akademik', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'store'])->name('kalender-akademik.store');
    Route::get('/kalender-akademik/{kalenderAkademik}', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'show'])->name('kalender-akademik.show');
    Route::put('/kalender-akademik/{kalenderAkademik}', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'update'])->name('kalender-akademik.update');
    Route::patch('/kalender-akademik/{kalenderAkademik}/dates', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'updateDates'])->name('kalender-akademik.update-dates');
    Route::delete('/kalender-akademik/{kalenderAkademik}', [App\Http\Controllers\Admin\KalenderAkademikController::class, 'destroy'])->name('kalender-akademik.destroy');
    
    // ==================== FITUR BARU: PRESTASI SISWA ====================
    Route::resource('prestasi-siswa', App\Http\Controllers\Admin\PrestasiSiswaController::class);
    Route::post('/prestasi-siswa/{prestasiSiswa}/verify', [App\Http\Controllers\Admin\PrestasiSiswaController::class, 'verify'])->name('prestasi-siswa.verify');
    
    // ==================== FITUR BARU: EKSTRAKURIKULER ====================
    Route::resource('ekstrakurikuler', App\Http\Controllers\Admin\EkstrakurikulerController::class);
    Route::get('/ekstrakurikuler/{ekstrakurikuler}/anggota', [App\Http\Controllers\Admin\EkstrakurikulerController::class, 'anggota'])->name('ekstrakurikuler.anggota');
    Route::post('/ekstrakurikuler/{ekstrakurikuler}/anggota', [App\Http\Controllers\Admin\EkstrakurikulerController::class, 'storeAnggota'])->name('ekstrakurikuler.anggota.store');
    Route::put('/ekstrakurikuler/anggota/{anggota}', [App\Http\Controllers\Admin\EkstrakurikulerController::class, 'updateAnggota'])->name('ekstrakurikuler.anggota.update');
    Route::delete('/ekstrakurikuler/anggota/{anggota}', [App\Http\Controllers\Admin\EkstrakurikulerController::class, 'destroyAnggota'])->name('ekstrakurikuler.anggota.destroy');
    
// ==================== FITUR BARU: JADWAL PELAJARAN ====================
    Route::middleware(['permission:view-jadwal-pelajaran'])->group(function () {
        Route::get('/jadwal-pelajaran', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'index'])->name('jadwal-pelajaran.index');
        Route::get('/jadwal-pelajaran/timetable', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'timetable'])->name('jadwal-pelajaran.timetable');
        Route::get('/jadwal-pelajaran/monitor', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'monitor'])->name('jadwal-pelajaran.monitor');
        Route::get('/jadwal-pelajaran/timetable-data', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'timetableData'])->name('jadwal-pelajaran.timetable-data');
        Route::get('/jadwal-pelajaran/guru-options', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'guruOptions'])->name('jadwal-pelajaran.guru-options');
        Route::get('/jadwal-pelajaran/mapel-options', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'mapelOptions'])->name('jadwal-pelajaran.mapel-options');
        Route::get('/jadwal-pelajaran/guru-mapel-in-kelas', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'guruMapelInKelas'])->name('jadwal-pelajaran.guru-mapel-in-kelas');
        Route::get('/jadwal-pelajaran/guru-jtm-summary', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'guruJtmSummary'])->name('jadwal-pelajaran.guru-jtm-summary');
        Route::get('/jadwal-hari-jam', [App\Http\Controllers\Admin\JadwalHariJamController::class, 'index'])->name('jadwal-hari-jam.index');
    });
    Route::middleware(['permission:view-jadwal-mapping'])->group(function () {
        Route::get('/jadwal-mapping', [App\Http\Controllers\Admin\JadwalMappingController::class, 'index'])->name('jadwal-mapping.index');
    });
    Route::middleware(['permission:manage-jadwal-mapping'])->group(function () {
        Route::post('/jadwal-mapping/refresh', [App\Http\Controllers\Admin\JadwalMappingController::class, 'refresh'])->name('jadwal-mapping.refresh');
        Route::put('/jadwal-mapping/guru/{alias}', [App\Http\Controllers\Admin\JadwalMappingController::class, 'updateGuru'])->name('jadwal-mapping.guru.update');
        Route::put('/jadwal-mapping/mapel/{alias}', [App\Http\Controllers\Admin\JadwalMappingController::class, 'updateMapel'])->name('jadwal-mapping.mapel.update');
    });
    Route::middleware(['permission:manage-jadwal-pelajaran'])->group(function () {
        Route::get('/jadwal-pelajaran/import', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'importForm'])->name('jadwal-pelajaran.import');
        Route::post('/jadwal-pelajaran/import/preview', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'previewWakakurImport'])->name('jadwal-pelajaran.import.preview');
        Route::post('/jadwal-pelajaran/import/commit', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'importWakakur'])->name('jadwal-pelajaran.import.commit');
        Route::post('/jadwal-pelajaran', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'store'])->name('jadwal-pelajaran.store');
        Route::post('/jadwal-pelajaran/copy', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'copyJadwal'])->name('jadwal-pelajaran.copy');
        Route::post('/jadwal-pelajaran/clear-all', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'clearAll'])->name('jadwal-pelajaran.clear-all');
        Route::get('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'show'])->name('jadwal-pelajaran.show');
        Route::put('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'update'])->name('jadwal-pelajaran.update');
        Route::delete('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'destroy'])->name('jadwal-pelajaran.destroy');
        // Jadwal Hari Jam (slot jam per hari, gantikan jadwal-jam-config)
        Route::post('/jadwal-hari-jam', [App\Http\Controllers\Admin\JadwalHariJamController::class, 'store'])->name('jadwal-hari-jam.store');
        Route::post('/jadwal-hari-jam/generate-default', [App\Http\Controllers\Admin\JadwalHariJamController::class, 'generateDefault'])->name('jadwal-hari-jam.generate-default');
        Route::post('/jadwal-hari-jam/reorder', [App\Http\Controllers\Admin\JadwalHariJamController::class, 'reorder'])->name('jadwal-hari-jam.reorder');
        Route::delete('/jadwal-hari-jam/{hariJam}', [App\Http\Controllers\Admin\JadwalHariJamController::class, 'destroy'])->name('jadwal-hari-jam.destroy');
    });
    // Jadwal Jam Config (dipertahankan untuk backward compat)
    Route::middleware(['permission:manage-jadwal-pelajaran'])->group(function () {
        Route::get('/jadwal-jam-config', [App\Http\Controllers\Admin\JadwalJamConfigController::class, 'index'])->name('jadwal-jam-config.index');
        Route::post('/jadwal-jam-config/generate', [App\Http\Controllers\Admin\JadwalJamConfigController::class, 'generate'])->name('jadwal-jam-config.generate');
        Route::post('/jadwal-jam-config', [App\Http\Controllers\Admin\JadwalJamConfigController::class, 'store'])->name('jadwal-jam-config.store');
        Route::delete('/jadwal-jam-config/{jamConfig}', [App\Http\Controllers\Admin\JadwalJamConfigController::class, 'destroy'])->name('jadwal-jam-config.destroy');
    });
    
    // ==================== FITUR BARU: CATATAN KONSELING (BK) ====================
    Route::resource('catatan-konseling', App\Http\Controllers\Admin\CatatanKonselingController::class);
    Route::get('/catatan-konseling-report/siswa', [App\Http\Controllers\Admin\CatatanKonselingController::class, 'reportSiswa'])->name('catatan-konseling.report-siswa');
    
    // ==================== FITUR BARU: PEMBAYARAN (SPP) ====================
    // Jenis Pembayaran
    Route::get('/pembayaran/jenis', [App\Http\Controllers\Admin\PembayaranController::class, 'jenisPembayaran'])->name('pembayaran.jenis');
    Route::post('/pembayaran/jenis', [App\Http\Controllers\Admin\PembayaranController::class, 'storeJenisPembayaran'])->name('pembayaran.jenis.store');
    Route::get('/pembayaran/jenis/{jenisPembayaran}', [App\Http\Controllers\Admin\PembayaranController::class, 'showJenisPembayaran'])->name('pembayaran.jenis.show');
    Route::put('/pembayaran/jenis/{jenisPembayaran}', [App\Http\Controllers\Admin\PembayaranController::class, 'updateJenisPembayaran'])->name('pembayaran.jenis.update');
    Route::delete('/pembayaran/jenis/{jenisPembayaran}', [App\Http\Controllers\Admin\PembayaranController::class, 'destroyJenisPembayaran'])->name('pembayaran.jenis.destroy');
    
    // Tagihan
    Route::get('/pembayaran/tagihan', [App\Http\Controllers\Admin\PembayaranController::class, 'tagihan'])->name('pembayaran.tagihan');
    Route::post('/pembayaran/tagihan/generate', [App\Http\Controllers\Admin\PembayaranController::class, 'generateTagihan'])->name('pembayaran.tagihan.generate');
    Route::get('/pembayaran/tagihan/{tagihan}', [App\Http\Controllers\Admin\PembayaranController::class, 'showTagihan'])->name('pembayaran.tagihan.show');
    Route::delete('/pembayaran/tagihan/{tagihan}', [App\Http\Controllers\Admin\PembayaranController::class, 'destroyTagihan'])->name('pembayaran.tagihan.destroy');
    
    // Pembayaran
    Route::get('/pembayaran', [App\Http\Controllers\Admin\PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [App\Http\Controllers\Admin\PembayaranController::class, 'store'])->name('pembayaran.store');
    Route::get('/pembayaran/laporan', [App\Http\Controllers\Admin\PembayaranController::class, 'laporan'])->name('pembayaran.laporan');
    Route::get('/pembayaran/{pembayaran}', [App\Http\Controllers\Admin\PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{pembayaran}/verify', [App\Http\Controllers\Admin\PembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::post('/pembayaran/{pembayaran}/reject', [App\Http\Controllers\Admin\PembayaranController::class, 'reject'])->name('pembayaran.reject');
    
    // ==================== FITUR BARU: SURAT KETERANGAN ====================
    // Template Surat
    Route::get('/surat-keterangan/template', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'template'])->name('surat-keterangan.template');
    Route::get('/surat-keterangan/template/create', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'createTemplate'])->name('surat-keterangan.template.create');
    Route::post('/surat-keterangan/template', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'storeTemplate'])->name('surat-keterangan.template.store');
    Route::get('/surat-keterangan/template/{template}/edit', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'editTemplate'])->name('surat-keterangan.template.edit');
    Route::put('/surat-keterangan/template/{template}', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'updateTemplate'])->name('surat-keterangan.template.update');
    Route::delete('/surat-keterangan/template/{template}', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'destroyTemplate'])->name('surat-keterangan.template.destroy');
    
    // Surat Keterangan
    Route::resource('surat-keterangan', App\Http\Controllers\Admin\SuratKeteranganController::class);
    Route::post('/surat-keterangan/{suratKeterangan}/approve', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'approve'])->name('surat-keterangan.approve');
    Route::post('/surat-keterangan/{suratKeterangan}/reject', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'reject'])->name('surat-keterangan.reject');
    Route::get('/surat-keterangan/{suratKeterangan}/print', [App\Http\Controllers\Admin\SuratKeteranganController::class, 'print'])->name('surat-keterangan.print');
    
    // ==================== FITUR BARU: MENU SNBP (Eligibility Kelas 12) ====================
    Route::resource('snbp-menu', App\Http\Controllers\Admin\SnbpMenuController::class);
    Route::get('/snbp-menu/{snbpMenu}/assign-eligible', [App\Http\Controllers\Admin\SnbpMenuController::class, 'assignEligible'])->name('snbp-menu.assign-eligible');
    Route::post('/snbp-menu/{snbpMenu}/store-eligible', [App\Http\Controllers\Admin\SnbpMenuController::class, 'storeEligible'])->name('snbp-menu.store-eligible');
    Route::get('/snbp-menu/{snbpMenu}/assign-not-eligible', [App\Http\Controllers\Admin\SnbpMenuController::class, 'assignNotEligible'])->name('snbp-menu.assign-not-eligible');
    Route::post('/snbp-menu/{snbpMenu}/store-not-eligible', [App\Http\Controllers\Admin\SnbpMenuController::class, 'storeNotEligible'])->name('snbp-menu.store-not-eligible');
    Route::post('/snbp-menu/{snbpMenu}/registrations/{registration}/check-announcement', [App\Http\Controllers\Admin\SnbpMenuController::class, 'checkAnnouncement'])->name('snbp-menu.check-announcement');
    Route::delete('/snbp-menu/{snbpSiswa}/remove-assignment', [App\Http\Controllers\Admin\SnbpMenuController::class, 'removeAssignment'])->name('snbp-menu.remove-assignment');
    Route::resource('span-ptkin-menu', App\Http\Controllers\Admin\SpanPtkinMenuController::class);
    Route::post('/span-ptkin-menu/{spanPtkinMenu}/import-pdf', [App\Http\Controllers\Admin\SpanPtkinMenuController::class, 'importPdf'])->name('span-ptkin-menu.import-pdf');
    Route::post('/span-ptkin-menu/{spanPtkinMenu}/confirm-import', [App\Http\Controllers\Admin\SpanPtkinMenuController::class, 'confirmImport'])->name('span-ptkin-menu.confirm-import');
    Route::delete('/span-ptkin-menu/{spanPtkinMenu}/cancel-preview', [App\Http\Controllers\Admin\SpanPtkinMenuController::class, 'cancelPreview'])->name('span-ptkin-menu.cancel-preview');
    Route::post('/span-ptkin-menu/{spanPtkinMenu}/registrations/{registration}/check-announcement', [App\Http\Controllers\Admin\SpanPtkinMenuController::class, 'checkAnnouncement'])->name('span-ptkin-menu.check-announcement');
    
    // ==================== FITUR BARU: EXAM BROWSER (ExamAnmet) ====================
    Route::get('/exam-browser', [App\Http\Controllers\Admin\ExamBrowserController::class, 'index'])->name('exam-browser.index');
    Route::put('/exam-browser', [App\Http\Controllers\Admin\ExamBrowserController::class, 'update'])->name('exam-browser.update');
    Route::delete('/exam-browser/logo', [App\Http\Controllers\Admin\ExamBrowserController::class, 'deleteLogo'])->name('exam-browser.delete-logo');
    Route::post('/exam-browser/generate-seb-key', [App\Http\Controllers\Admin\ExamBrowserController::class, 'generateSebKey'])->name('exam-browser.generate-seb-key');
    Route::post('/exam-browser/regenerate-config', [App\Http\Controllers\Admin\ExamBrowserController::class, 'regenerateConfig'])->name('exam-browser.regenerate-config');
    Route::get('/exam-browser/preview-config', [App\Http\Controllers\Admin\ExamBrowserController::class, 'previewConfig'])->name('exam-browser.preview-config');

    // ==================== FITUR BARU: NOTIFIKASI EXAM BROWSER ====================
    Route::get('/exam-notifications', [App\Http\Controllers\Admin\ExamNotificationController::class, 'index'])->name('exam-notifications.index');
    Route::post('/exam-notifications', [App\Http\Controllers\Admin\ExamNotificationController::class, 'store'])->name('exam-notifications.store');
    Route::post('/exam-notifications/bulk-action', [App\Http\Controllers\Admin\ExamNotificationController::class, 'bulkAction'])->name('exam-notifications.bulk-action');
    Route::post('/exam-notifications/{examNotification}/resend', [App\Http\Controllers\Admin\ExamNotificationController::class, 'resend'])->name('exam-notifications.resend');
    Route::delete('/exam-notifications/{examNotification}', [App\Http\Controllers\Admin\ExamNotificationController::class, 'destroy'])->name('exam-notifications.destroy');
    Route::delete('/exam-notifications/{id}/force-delete', [App\Http\Controllers\Admin\ExamNotificationController::class, 'forceDelete'])->name('exam-notifications.force-delete');

    // ==================== FITUR BARU: MONITORING UJIAN (ExamAnmet) ====================
    // Monitoring admin dinonaktifkan sementara untuk mengurangi beban saat ujian.
    Route::get('/exam-monitoring', function () {
        return redirect()
            ->route('admin.exam-browser.index')
            ->with('warning', 'Monitoring ujian dinonaktifkan sementara untuk menjaga kestabilan server saat sesi ujian.');
    })->name('exam-monitoring.index');
    Route::get('/exam-monitoring/api/sessions', function () {
        return response()->json([
            'success' => false,
            'message' => 'Monitoring ujian dinonaktifkan sementara untuk menjaga kestabilan server.',
        ], 503);
    })->name('exam-monitoring.api.sessions');
    Route::post('/exam-monitoring/{session}/lock', function () {
        return response()->json([
            'success' => false,
            'message' => 'Aksi monitoring ujian dinonaktifkan sementara.',
        ], 503);
    })->name('exam-monitoring.lock');
    Route::post('/exam-monitoring/{session}/unlock', function () {
        return response()->json([
            'success' => false,
            'message' => 'Aksi monitoring ujian dinonaktifkan sementara.',
        ], 503);
    })->name('exam-monitoring.unlock');
    Route::post('/exam-monitoring/{session}/end', function () {
        return response()->json([
            'success' => false,
            'message' => 'Aksi monitoring ujian dinonaktifkan sementara.',
        ], 503);
    })->name('exam-monitoring.end');
    Route::get('/exam-monitoring/{session}/violations', function () {
        return response()->json([
            'success' => false,
            'message' => 'Monitoring ujian dinonaktifkan sementara.',
        ], 503);
    })->name('exam-monitoring.violations');
});

// Laravolt Indonesia API (untuk semua yang authenticated)
Route::middleware(['auth'])->prefix('laravolt/indonesia')->group(function () {
    Route::get('/cities', function(\Illuminate\Http\Request $request) {
        $provinceCode = $request->get('province_code');
        $cities = \Laravolt\Indonesia\Models\City::where('province_code', $provinceCode)->orderBy('name')->get();
        return response()->json($cities);
    });
    
    Route::get('/districts', function(\Illuminate\Http\Request $request) {
        $cityCode = $request->get('city_code');
        $districts = \Laravolt\Indonesia\Models\District::where('city_code', $cityCode)->orderBy('name')->get();
        return response()->json($districts);
    });
    
    Route::get('/villages', function(\Illuminate\Http\Request $request) {
        $districtCode = $request->get('district_code');
        $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $districtCode)->orderBy('name')->get();
        return response()->json($villages);
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Debug route (temporary)
    Route::get('/debug-users', function() {
        $user = auth()->user();
        $users = \App\Models\User::with('roles')->get();
        
        $html = '<h1>Debug User Data</h1>';
        $html .= '<h2>Current User:</h2>';
        $html .= '<p>Name: ' . $user->name . '</p>';
        $html .= '<p>Can view-user: ' . ($user->can('view-user') ? 'YES' : 'NO') . '</p>';
        $html .= '<p>Roles: ' . $user->roles->pluck('name')->implode(', ') . '</p>';
        $html .= '<p>Permissions Count: ' . $user->getAllPermissions()->count() . '</p>';
        
        $html .= '<h2>All Users (' . $users->count() . '):</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><th>ID</th><th>Name</th><th>Email</th><th>Roles</th></tr>';
        foreach($users as $u) {
            $html .= '<tr>';
            $html .= '<td>' . $u->id . '</td>';
            $html .= '<td>' . $u->name . '</td>';
            $html .= '<td>' . $u->email . '</td>';
            $html .= '<td>' . $u->roles->pluck('name')->implode(', ') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        
        return $html;
    })->name('debug.users');
    
    // Debug users-data route
    Route::get('/debug-users-data', function() {
        $request = request();
        $request->merge(['draw' => 1, 'start' => 0, 'length' => 10]);
        
        $controller = new \App\Http\Controllers\Admin\UserController();
        $response = $controller->data($request);
        
        return '<pre>' . json_encode($response->getData(), JSON_PRETTY_PRINT) . '</pre>';
    })->name('debug.users.data');

    // ============================================
    // ABSENSI WAJAH (Face Attendance System)
    // ============================================
    
    // Absensi Dashboard & Management (Admin/Operator)
    Route::middleware(['permission:view-absensi'])->group(function () {
        Route::get('/absensi', [App\Http\Controllers\Admin\AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/rekap', [App\Http\Controllers\Admin\AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('/absensi/export', [App\Http\Controllers\Admin\AbsensiController::class, 'export'])->name('absensi.export');
        Route::get('/absensi/today-data', [App\Http\Controllers\Admin\AbsensiController::class, 'todayData'])->name('absensi.today-data');
    });

    // Absensi Kiosk Mode (Fullscreen) hanya dijalankan oleh perangkat/admin tepercaya.
    Route::middleware(['can:face-registration-admin'])->group(function () {
        Route::get('/absensi/kiosk', [App\Http\Controllers\Admin\AbsensiController::class, 'kiosk'])->name('absensi.kiosk');
        Route::post('/absensi/record-face', [App\Http\Controllers\Admin\AbsensiController::class, 'recordFace'])->name('absensi.record-face');
        Route::get('/absensi/face-descriptors', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'getDescriptors'])->name('absensi.face-descriptors');
    });

    // Absensi Input & Edit
    Route::middleware(['permission:create-absensi'])->group(function () {
        Route::post('/absensi/manual', [App\Http\Controllers\Admin\AbsensiController::class, 'manualInput'])->name('absensi.manual');
    });

    Route::middleware(['permission:edit-absensi'])->group(function () {
        Route::put('/absensi/{absensi}', [App\Http\Controllers\Admin\AbsensiController::class, 'update'])->name('absensi.update');
    });

    // Face Registration terpusat: admin mengelola GTK/Siswa, GTK hanya akun sendiri.
    Route::middleware(['can:face-registration-access'])->group(function () {
        Route::get('/absensi/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('absensi.face-register');
        Route::post('/absensi/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('absensi.face-register.store');
    });

    // Face Verification (Admin only)
    Route::middleware(['can:face-registration-admin'])->group(function () {
        Route::get('/absensi/face-verification', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'verificationList'])->name('absensi.face-verification');
        Route::post('/absensi/face-verify/{faceEncoding}', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'verify'])->name('absensi.face-verify');
        Route::delete('/absensi/face-encoding/{faceEncoding}', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'destroy'])->name('absensi.face-encoding.destroy');
        Route::post('/absensi/face-encoding/{faceEncoding}/reset', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'resetVerification'])->name('absensi.face-encoding.reset');
        Route::post('/absensi/face-encoding/{faceEncoding}/self-access', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'updateSelfRegistrationAccess'])->name('absensi.face-encoding.self-access');
    });

    // Absensi Settings (Admin)
    Route::middleware(['permission:manage-settings'])->group(function () {
        Route::get('/absensi/settings', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'index'])->name('absensi.settings');
        Route::post('/absensi/settings', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'updateSettings'])->name('absensi.settings.update');
        Route::post('/absensi/location', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'storeLocation'])->name('absensi.location.store');
        Route::put('/absensi/location/{location}', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'updateLocation'])->name('absensi.location.update');
        Route::post('/absensi/location/{location}/toggle', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'toggleLocation'])->name('absensi.location.toggle');
        Route::delete('/absensi/location/{location}', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'destroyLocation'])->name('absensi.location.destroy');
        Route::post('/absensi/hari-libur', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'storeHariLibur'])->name('absensi.hari-libur.store');
        Route::delete('/absensi/hari-libur/{hariLibur}', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'destroyHariLibur'])->name('absensi.hari-libur.destroy');
        Route::post('/absensi/hari-libur/seed', [App\Http\Controllers\Admin\AbsensiSettingController::class, 'seedHariLibur'])->name('absensi.hari-libur.seed');
    });

    // ==================== SMART-Q KELAS UNGGULAN ====================
    Route::prefix('smartq')->name('smartq.')->middleware(['permission:view-smartq'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SmartqController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SmartqController::class, 'create'])->middleware('permission:create-smartq')->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SmartqController::class, 'store'])->middleware('permission:create-smartq')->name('store');
        Route::get('/{smartq}', [App\Http\Controllers\Admin\SmartqController::class, 'show'])->name('show');
        Route::get('/{smartq}/edit', [App\Http\Controllers\Admin\SmartqController::class, 'edit'])->middleware('permission:edit-smartq')->name('edit');
        Route::put('/{smartq}', [App\Http\Controllers\Admin\SmartqController::class, 'update'])->middleware('permission:edit-smartq')->name('update');
        Route::put('/{smartq}/komponen', [App\Http\Controllers\Admin\SmartqController::class, 'updateKomponen'])->middleware('permission:edit-smartq')->name('komponen.update');

        // Peserta
        Route::get('/{smartq}/peserta', [App\Http\Controllers\Admin\SmartqController::class, 'peserta'])->name('peserta');
        Route::post('/{smartq}/peserta', [App\Http\Controllers\Admin\SmartqController::class, 'tambahPeserta'])->middleware('permission:manage-peserta-smartq')->name('peserta.tambah');
        Route::delete('/{smartq}/peserta/{peserta}', [App\Http\Controllers\Admin\SmartqController::class, 'hapusPeserta'])->middleware('permission:manage-peserta-smartq')->name('peserta.hapus');

        // Kelulusan reset
        Route::delete('/{smartq}/kelulusan/{peserta}/reset', [App\Http\Controllers\Admin\SmartqController::class, 'resetKelulusanPeserta'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.reset.peserta');
        Route::delete('/{smartq}/kelulusan/reset-all', [App\Http\Controllers\Admin\SmartqController::class, 'resetKelulusanBulk'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.reset.bulk');
        Route::put('/{smartq}/kelulusan/{peserta}/status', [App\Http\Controllers\Admin\SmartqController::class, 'updateKelulusanPesertaStatus'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.status.update');

        // Nilai
        Route::get('/{smartq}/nilai', [App\Http\Controllers\Admin\SmartqController::class, 'inputNilai'])->middleware('permission:input-nilai-smartq')->name('nilai');
        Route::post('/{smartq}/nilai', [App\Http\Controllers\Admin\SmartqController::class, 'simpanNilai'])->middleware('permission:input-nilai-smartq')->name('nilai.simpan');

        // Moodle Integration
        Route::get('/{smartq}/moodle', [App\Http\Controllers\Admin\SmartqController::class, 'moodleConfig'])->middleware('permission:manage-moodle-smartq')->name('moodle.config');
        Route::get('/{smartq}/moodle/categories', [App\Http\Controllers\Admin\SmartqController::class, 'moodleCategories'])->middleware('permission:manage-moodle-smartq')->name('moodle.categories');
        Route::get('/{smartq}/moodle/courses', [App\Http\Controllers\Admin\SmartqController::class, 'moodleCourses'])->middleware('permission:manage-moodle-smartq')->name('moodle.courses');
        Route::get('/{smartq}/moodle/quizzes', [App\Http\Controllers\Admin\SmartqController::class, 'moodleQuizzes'])->middleware('permission:manage-moodle-smartq')->name('moodle.quizzes');
        Route::post('/{smartq}/moodle/save', [App\Http\Controllers\Admin\SmartqController::class, 'moodleSaveCourseQuiz'])->middleware('permission:manage-moodle-smartq')->name('moodle.save');
        Route::post('/{smartq}/moodle/sync', [App\Http\Controllers\Admin\SmartqController::class, 'syncMoodle'])->middleware('permission:manage-moodle-smartq')->name('moodle.sync');
        Route::get('/{smartq}/moodle/scan', [App\Http\Controllers\Admin\SmartqController::class, 'moodleScan'])->middleware('permission:manage-moodle-smartq')->name('moodle.scan');
        Route::post('/{smartq}/moodle/scan/confirm', [App\Http\Controllers\Admin\SmartqController::class, 'confirmMoodleScan'])->middleware('permission:manage-moodle-smartq')->name('moodle.scan.confirm');
        Route::post('/{smartq}/moodle/scan/add-to-simansa', [App\Http\Controllers\Admin\SmartqController::class, 'addUnmatchedToSimansa'])->middleware('permission:manage-moodle-smartq')->name('moodle.scan.addToSimansa');
        Route::get('/{smartq}/moodle/scan/export', [App\Http\Controllers\Admin\SmartqController::class, 'exportScanReport'])->middleware('permission:export-smartq')->name('moodle.scan.export');
        Route::get('/{smartq}/moodle/scan/view', [App\Http\Controllers\Admin\SmartqController::class, 'viewScanCache'])->middleware('permission:manage-moodle-smartq')->name('moodle.scan.view');
        Route::get('/{smartq}/nilai-cbt', [App\Http\Controllers\Admin\SmartqController::class, 'nilaiCbt'])->middleware('permission:manage-moodle-smartq')->name('nilai-cbt');

        // Kelulusan Import & Export
        Route::get('/{smartq}/ranking-data', [App\Http\Controllers\Admin\SmartqController::class, 'rankingData'])->name('ranking.data');
        Route::get('/{smartq}/kelulusan/import', [App\Http\Controllers\Admin\SmartqController::class, 'importKelulusanForm'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.import');
        Route::get('/{smartq}/kelulusan/template', [App\Http\Controllers\Admin\SmartqController::class, 'importKelulusanTemplate'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.template');
        Route::post('/{smartq}/kelulusan/import', [App\Http\Controllers\Admin\SmartqController::class, 'importKelulusanPreview'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.import.process');
        Route::post('/{smartq}/kelulusan/confirm', [App\Http\Controllers\Admin\SmartqController::class, 'importKelulusanConfirm'])->middleware('permission:manage-kelulusan-smartq')->name('kelulusan.import.confirm');
        Route::get('/{smartq}/export', [App\Http\Controllers\Admin\SmartqController::class, 'exportExcel'])->middleware('permission:export-smartq')->name('export');
    });

    // ==================== DOWNLOAD CENTER ====================
    Route::middleware(['permission:view-downloads'])->group(function () {
        Route::get('/downloads', [App\Http\Controllers\Admin\DownloadController::class, 'index'])->name('downloads.index');
    });
    Route::middleware(['permission:create-downloads'])->group(function () {
        Route::get('/downloads/create', [App\Http\Controllers\Admin\DownloadController::class, 'create'])->name('downloads.create');
        Route::post('/downloads', [App\Http\Controllers\Admin\DownloadController::class, 'store'])->name('downloads.store');
    });
    Route::middleware(['permission:edit-downloads'])->group(function () {
        Route::get('/downloads/{download}/edit', [App\Http\Controllers\Admin\DownloadController::class, 'edit'])->name('downloads.edit');
        Route::put('/downloads/{download}', [App\Http\Controllers\Admin\DownloadController::class, 'update'])->name('downloads.update');
    });
    Route::middleware(['permission:delete-downloads'])->group(function () {
        Route::delete('/downloads/{download}', [App\Http\Controllers\Admin\DownloadController::class, 'destroy'])->name('downloads.destroy');
    });
    Route::middleware(['permission:manage-download-settings'])->group(function () {
        Route::get('/download-categories', [App\Http\Controllers\Admin\DownloadCategoryController::class, 'index'])->name('download-categories.index');
        Route::post('/download-categories', [App\Http\Controllers\Admin\DownloadCategoryController::class, 'store'])->name('download-categories.store');
        Route::put('/download-categories/{downloadCategory}', [App\Http\Controllers\Admin\DownloadCategoryController::class, 'update'])->name('download-categories.update');
        Route::delete('/download-categories/{downloadCategory}', [App\Http\Controllers\Admin\DownloadCategoryController::class, 'destroy'])->name('download-categories.destroy');
        Route::get('/download-settings', [App\Http\Controllers\Admin\DownloadSettingController::class, 'edit'])->name('download-settings.edit');
        Route::put('/download-settings', [App\Http\Controllers\Admin\DownloadSettingController::class, 'update'])->name('download-settings.update');
        Route::post('/download-settings/test-connection', [App\Http\Controllers\Admin\DownloadSettingController::class, 'testConnection'])->name('download-settings.test-connection');
    });
});

// Siswa Routes
Route::middleware(['auth', 'impersonation:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::post('/impersonation/stop', [App\Http\Controllers\Admin\UserImpersonationController::class, 'stopSiswa'])
        ->name('impersonation.stop');

    // Force setup (password + email) - no middleware restriction
    Route::get('/force-setup', [SiswaProfileController::class, 'forceSetup'])->name('force-setup');
    Route::post('/force-setup', [SiswaProfileController::class, 'updateForceSetup'])->name('force-setup.update');

    // Email verification
    Route::get('/email/verify/{id}/{hash}', [SiswaProfileController::class, 'verifyEmail'])
        ->middleware('signed')->name('email.verify');
    Route::post('/email/resend-verification', [SiswaProfileController::class, 'resendVerification'])
        ->middleware('throttle:3,10')->name('email.resend');
    
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pemilihan-osis', [App\Http\Controllers\Siswa\OsisElectionController::class, 'index'])->name('osis-election.index');
    Route::post('/pemilihan-osis/{election}/pilih', [App\Http\Controllers\Siswa\OsisElectionController::class, 'vote'])->name('osis-election.vote');
    Route::get('/polling', [App\Http\Controllers\PollingResponseController::class, 'index'])->name('polling.index');
    Route::get('/polling/{polling}', [App\Http\Controllers\PollingResponseController::class, 'show'])->name('polling.show');
    Route::post('/polling/{polling}', [App\Http\Controllers\PollingResponseController::class, 'store'])->name('polling.store');
    Route::post('/polling/{polling}/unlock-request', [App\Http\Controllers\PollingResponseController::class, 'requestUnlock'])->name('polling.unlock-request');
    Route::post('/polling/{polling}/snooze', [App\Http\Controllers\PollingResponseController::class, 'snooze'])->name('polling.snooze');
    
    // Profile Management for Siswa
    Route::get('/profile/password', [SiswaProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile/password', [SiswaProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    // Change Password (for non-first login)
    Route::get('/profile/change-password', [SiswaProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::put('/profile/change-password', [SiswaProfileController::class, 'updateChangePassword'])->name('profile.change-password.update');
    
    Route::get('/profile/ortu', [App\Http\Controllers\Siswa\OrtuController::class, 'show'])->name('profile.ortu');
    Route::put('/profile/ortu', [App\Http\Controllers\Siswa\OrtuController::class, 'update'])->name('profile.ortu.update');
    
    Route::get('/profile/diri', [SiswaProfileController::class, 'diri'])->name('profile.diri');
    Route::put('/profile/diri', [SiswaProfileController::class, 'updateDiri'])->name('profile.diri.update');
    Route::post('/profile/foto', [SiswaProfileController::class, 'uploadFoto'])->name('profile.foto.upload');
    Route::get('/profile/alamat-ortu', [SiswaProfileController::class, 'loadAlamatOrtu'])->name('profile.alamat-ortu');
    
    // AJAX: Search Sekolah by NPSN
    Route::get('/profile/search-sekolah', [SiswaProfileController::class, 'searchSekolah'])->name('profile.search-sekolah');
    
    // Dokumen Management
    Route::get('/dokumen', [App\Http\Controllers\Siswa\DokumenController::class, 'index'])->name('dokumen');
    Route::put('/dokumen/pkh', [App\Http\Controllers\Siswa\DokumenController::class, 'updatePkh'])->name('dokumen.pkh.update');
    Route::post('/dokumen/upload', [App\Http\Controllers\Siswa\DokumenController::class, 'upload'])->name('dokumen.upload');
    Route::get('/dokumen/{id}/preview', [App\Http\Controllers\Siswa\DokumenController::class, 'preview'])->name('dokumen.preview');
    Route::get('/dokumen/{id}/download', [App\Http\Controllers\Siswa\DokumenController::class, 'download'])->name('dokumen.download');
    Route::delete('/dokumen/{id}', [App\Http\Controllers\Siswa\DokumenController::class, 'destroy'])->name('dokumen.destroy');
    
    // Custom Menu for Siswa
    Route::get('/menu', [App\Http\Controllers\Siswa\CustomMenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/{slug}', [App\Http\Controllers\Siswa\CustomMenuController::class, 'show'])->name('menu.show');
    Route::post('/menu/{id}/read', [App\Http\Controllers\Siswa\CustomMenuController::class, 'markAsRead'])->name('menu.read');
    
    // SNBP Menu for Siswa (Kelas 12 only)
    Route::get('/snbp', [App\Http\Controllers\Siswa\SnbpController::class, 'index'])->name('snbp.index');
    Route::post('/snbp', [App\Http\Controllers\Siswa\SnbpController::class, 'storeRegistration'])->name('snbp.store');
    Route::get('/span-ptkin', [App\Http\Controllers\Siswa\SpanPtkinController::class, 'index'])->name('span-ptkin.index');
    
    // Tracking Lulusan untuk siswa kelas 12/alumni
    Route::get('/lulusan', [App\Http\Controllers\Siswa\LulusanController::class, 'index'])->name('lulusan.index');
    Route::post('/lulusan', [App\Http\Controllers\Siswa\LulusanController::class, 'store'])->name('lulusan.store');
    Route::get('/lulusan/referensi/search', [App\Http\Controllers\Siswa\LulusanController::class, 'searchReferences'])->name('lulusan.referensi.search');
    Route::get('/lulusan/prodi/search', [App\Http\Controllers\Siswa\LulusanController::class, 'searchStudyPrograms'])->name('lulusan.prodi.search');
    Route::get('/kelulusan-pengumuman', [App\Http\Controllers\Siswa\PengumumanKelulusanController::class, 'index'])->name('kelulusan-pengumuman.index');
    Route::post('/kelulusan-pengumuman/open-envelope', [App\Http\Controllers\Siswa\PengumumanKelulusanController::class, 'openEnvelope'])->name('kelulusan-pengumuman.open-envelope');


    // Registrasi wajah mandiri siswa
    Route::get('/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('face-register');
    Route::post('/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('face-register.store');

    // SMART-Q Pengumuman Kelulusan
    Route::get('/smartq', [App\Http\Controllers\Siswa\SmartqController::class, 'index'])->name('smartq.index');
    Route::post('/smartq/open-envelope', [App\Http\Controllers\Siswa\SmartqController::class, 'openEnvelope'])->name('smartq.open-envelope');

    // API for address dropdowns
    Route::get('/api/cities/{province}', [App\Http\Controllers\Siswa\OrtuController::class, 'getCities'])->name('api.cities');
    Route::get('/api/districts/{city}', [App\Http\Controllers\Siswa\OrtuController::class, 'getDistricts'])->name('api.districts');
    Route::get('/api/villages/{district}', [App\Http\Controllers\Siswa\OrtuController::class, 'getVillages'])->name('api.villages');
});

// ==================== PUBLIC API: EXAM BROWSER (ExamAnmet App) ====================
// These endpoints are consumed by the mobile ExamAnmet app
// No authentication required - data is non-sensitive config
Route::prefix('api/exam-browser')->name('api.exam-browser.')->group(function () {
    Route::get('/ping', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'ping'])->name('ping');
    Route::get('/config', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'config'])
        ->middleware('throttle:exam-browser-config')
        ->name('config');
    Route::post('/verify-password', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'verifyPassword'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-password'])
        ->name('verify-password');
    Route::get('/notifications', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'notifications'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-notifications'])
        ->name('notifications');

    // Session & Violation reporting (from ExaManmet app)
    Route::post('/session/start', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'sessionStart'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-session-start'])
        ->name('session.start');
    Route::post('/session/heartbeat', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'sessionHeartbeat'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-heartbeat'])
        ->name('session.heartbeat');
    Route::post('/session/violation', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'sessionViolation'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-violation'])
        ->name('session.violation');
    Route::post('/session/end', [App\Http\Controllers\Api\ExamBrowserApiController::class, 'sessionEnd'])
        ->middleware(['exam.browser.client', 'throttle:exam-browser-session-end'])
        ->name('session.end');
});

// ─── Device Location & Client Runtime (global, no auth required) ──────────────
Route::post('/device-location/sync', [App\Http\Controllers\DeviceLocationController::class, 'sync'])
    ->middleware('throttle:60,1')
    ->name('device-location.sync');

Route::prefix('client-runtime')->name('client-runtime.')->group(function () {
    Route::post('/heartbeat', [App\Http\Controllers\ClientRuntimeController::class, 'heartbeat'])
        ->middleware(['auth', 'throttle:60,1'])
        ->name('heartbeat');
    Route::get('/server-time', [App\Http\Controllers\ClientRuntimeController::class, 'serverTime'])
        ->middleware('throttle:60,1')
        ->name('server-time');
});
