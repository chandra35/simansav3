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
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\ProfileController as SiswaProfileController;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Public Verification Routes (No Auth Required - for QR Code scanning)
Route::get('/verifikasi/gtk/{id}', [App\Http\Controllers\VerifikasiController::class, 'verifikasiGtk'])->name('verifikasi.gtk');
Route::get('/verifikasi/siswa/{id}', [App\Http\Controllers\VerifikasiController::class, 'verifikasiSiswa'])->name('verifikasi.siswa');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Forgot Password Routes
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');

// Admin Routes (Super Admin, Admin, GTK, Operator)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Under Development Placeholder
    Route::get('/under-development', function () {
        return view('admin.under-development');
    })->name('under-development');
    
    // Profile Management
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'changePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    
    // Siswa Management
    Route::resource('siswa', AdminSiswaController::class);
    Route::get('/siswa-data', [AdminSiswaController::class, 'data'])->name('siswa.data');
    Route::get('/siswa-stats', [AdminSiswaController::class, 'stats'])->name('siswa.stats');
    Route::put('/siswa/{siswa}/reset-password', [AdminSiswaController::class, 'resetPassword'])->name('siswa.reset-password');
    Route::get('/siswa/{siswa}/dokumen', [AdminSiswaController::class, 'getDokumen'])->name('siswa.dokumen');
    Route::get('/siswa/{siswa}/quick-detail', [AdminSiswaController::class, 'quickDetail'])->name('siswa.quick-detail');
    Route::get('/siswa-kelas-by-tingkat', [AdminSiswaController::class, 'getKelasByTingkat'])->name('siswa.kelas-by-tingkat');
    
    // Sekolah Asal Management
    Route::middleware(['permission:view-siswa'])->group(function () {
        Route::get('/sekolah-asal', [App\Http\Controllers\Admin\SekolahAsalController::class, 'index'])->name('sekolah-asal.index');
        Route::get('/sekolah-asal/{npsn}', [App\Http\Controllers\Admin\SekolahAsalController::class, 'show'])->name('sekolah-asal.show');
        Route::get('/sekolah-asal/{npsn}/siswa-data', [App\Http\Controllers\Admin\SekolahAsalController::class, 'getSiswaData'])->name('sekolah-asal.siswa-data');
        Route::get('/lulusan', [App\Http\Controllers\Admin\LulusanController::class, 'index'])->name('lulusan.index');
        Route::get('/lulusan/data', [App\Http\Controllers\Admin\LulusanController::class, 'data'])->name('lulusan.data');
        Route::get('/lulusan/stats', [App\Http\Controllers\Admin\LulusanController::class, 'stats'])->name('lulusan.stats');
        Route::get('/lulusan/export/excel', [App\Http\Controllers\Admin\LulusanController::class, 'exportExcel'])->name('lulusan.export-excel');
        Route::get('/lulusan/export/pdf', [App\Http\Controllers\Admin\LulusanController::class, 'exportPdf'])->name('lulusan.export-pdf');
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
    Route::get('/nilai/siswa/{siswa}', [\App\Http\Controllers\Admin\NilaiController::class, 'siswa'])->name('nilai.siswa');
    Route::delete('/nilai/semester/{semester}', [\App\Http\Controllers\Admin\NilaiController::class, 'deleteSemester'])->name('nilai.delete-semester');
    Route::post('/nilai/semester/{semester}/export-preview', [\App\Http\Controllers\Admin\NilaiController::class, 'exportSemesterPreview'])->name('nilai.export-semester-preview');
    Route::get('/nilai/semester/{semester}/export-download', [\App\Http\Controllers\Admin\NilaiController::class, 'exportSemesterDownload'])->name('nilai.export-semester-download');
    
    // Kelas Management
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
    Route::post('/kelas/{id}/restore', [KelasController::class, 'restore'])->name('kelas.restore')->middleware('permission:create-kelas');
    Route::get('/kelas/{kelas}/assign-siswa', [KelasController::class, 'assignSiswa'])->name('kelas.assign-siswa')->middleware('permission:assign-siswa-kelas');
    Route::get('/kelas/{kelas}/siswa/available', [KelasController::class, 'getAvailableSiswa'])->name('kelas.siswa.available')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa', [KelasController::class, 'storeSiswa'])->name('kelas.siswa.store')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa/nisn', [KelasController::class, 'storeSiswaNISN'])->name('kelas.siswa.store-nisn')->middleware('permission:assign-siswa-kelas');
    Route::delete('/kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'removeSiswa'])->name('kelas.siswa.remove')->middleware('permission:remove-siswa-kelas');
    Route::post('/kelas/{kelas}/wali-kelas', [KelasController::class, 'assignWaliKelas'])->name('kelas.wali-kelas')->middleware('permission:assign-wali-kelas');
    Route::post('/kelas/{kelas}/kosongkan', [KelasController::class, 'kosongkanKelas'])->name('kelas.kosongkan')->middleware('permission:remove-siswa-kelas');
    Route::get('/kelas/{kelas}/cetak-absensi', [KelasController::class, 'cetakAbsensi'])->name('kelas.cetak-absensi');
    
    // GTK Personal (Dashboard & Profile for GTK users)
    Route::middleware(['permission:view-gtk-dashboard'])->group(function () {
        Route::get('/gtk/dashboard', [App\Http\Controllers\Admin\GtkDashboardController::class, 'index'])->name('gtk.dashboard');
    });
    
    Route::middleware(['permission:change-password-gtk'])->group(function () {
        Route::get('/gtk/profile/password', [App\Http\Controllers\Admin\GtkProfileController::class, 'password'])->name('gtk.profile.password');
        Route::put('/gtk/profile/password', [App\Http\Controllers\Admin\GtkProfileController::class, 'updatePassword'])->name('gtk.profile.password.update');
    });
    
    Route::middleware(['permission:edit-gtk-profile'])->group(function () {
        Route::get('/gtk/profile', [App\Http\Controllers\Admin\GtkProfileController::class, 'index'])->name('gtk.profile');
        Route::put('/gtk/profile/diri', [App\Http\Controllers\Admin\GtkProfileController::class, 'updateDiri'])->name('gtk.profile.diri.update');
        Route::put('/gtk/profile/kepeg', [App\Http\Controllers\Admin\GtkProfileController::class, 'updateKepeg'])->name('gtk.profile.kepeg.update');
        
        // AJAX routes for address dropdowns
        Route::get('/gtk/api/cities/{provinsi}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getCities'])->name('gtk.api.cities');
        Route::get('/gtk/api/districts/{kabupaten}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getDistricts'])->name('gtk.api.districts');
        Route::get('/gtk/api/villages/{kecamatan}', [App\Http\Controllers\Admin\GtkProfileController::class, 'getVillages'])->name('gtk.api.villages');
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
    
    // Cetak (Print Reports)
    Route::middleware(['permission:view-kelas'])->group(function () {
        Route::get('/cetak', [App\Http\Controllers\Admin\CetakController::class, 'index'])->name('cetak.index');
        Route::post('/cetak/absensi-batch', [App\Http\Controllers\Admin\CetakController::class, 'cetakAbsensiBatch'])->name('cetak.absensi-batch');
        Route::get('/cetak/kelas-by-filter', [App\Http\Controllers\Admin\CetakController::class, 'getKelasByFilter'])->name('cetak.kelas-by-filter');
    });

    // Cetak ID Card Siswa
    Route::get('/cetak/id-card-siswa', [App\Http\Controllers\Admin\CetakController::class, 'idCardSiswaIndex'])->name('cetak.id-card-siswa.index')->middleware('permission:view-siswa');
    Route::post('/cetak/id-card-siswa', [App\Http\Controllers\Admin\CetakController::class, 'cetakIdCardSiswa'])->name('cetak.id-card-siswa')->middleware('permission:view-siswa');

    // Cetak ID Card GTK
    Route::get('/cetak/id-card-gtk', [App\Http\Controllers\Admin\CetakController::class, 'idCardGtkIndex'])->name('cetak.id-card-gtk.index')->middleware('permission:view-gtk');
    Route::post('/cetak/id-card-gtk', [App\Http\Controllers\Admin\CetakController::class, 'cetakIdCardGtk'])->name('cetak.id-card-gtk')->middleware('permission:view-gtk');
    Route::get('/cetak/gtk-by-filter', [App\Http\Controllers\Admin\CetakController::class, 'getGtkByFilter'])->name('cetak.gtk-by-filter')->middleware('permission:view-gtk');
    
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
    Route::get('/jadwal-pelajaran', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'index'])->name('jadwal-pelajaran.index');
    Route::get('/jadwal-pelajaran/create', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'create'])->name('jadwal-pelajaran.create');
    Route::post('/jadwal-pelajaran', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'store'])->name('jadwal-pelajaran.store');
    Route::get('/jadwal-pelajaran/timetable', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'timetable'])->name('jadwal-pelajaran.timetable');
    Route::get('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'show'])->name('jadwal-pelajaran.show');
    Route::put('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'update'])->name('jadwal-pelajaran.update');
    Route::delete('/jadwal-pelajaran/{jadwalPelajaran}', [App\Http\Controllers\Admin\JadwalPelajaranController::class, 'destroy'])->name('jadwal-pelajaran.destroy');
    
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
    
    // ==================== FITUR BARU: EXAM BROWSER (ExamAnmet) ====================
    Route::get('/exam-browser', [App\Http\Controllers\Admin\ExamBrowserController::class, 'index'])->name('exam-browser.index');
    Route::put('/exam-browser', [App\Http\Controllers\Admin\ExamBrowserController::class, 'update'])->name('exam-browser.update');
    Route::delete('/exam-browser/logo', [App\Http\Controllers\Admin\ExamBrowserController::class, 'deleteLogo'])->name('exam-browser.delete-logo');
    Route::post('/exam-browser/generate-seb-key', [App\Http\Controllers\Admin\ExamBrowserController::class, 'generateSebKey'])->name('exam-browser.generate-seb-key');
    Route::get('/exam-browser/preview-config', [App\Http\Controllers\Admin\ExamBrowserController::class, 'previewConfig'])->name('exam-browser.preview-config');

    // ==================== FITUR BARU: NOTIFIKASI EXAM BROWSER ====================
    Route::get('/exam-notifications', [App\Http\Controllers\Admin\ExamNotificationController::class, 'index'])->name('exam-notifications.index');
    Route::post('/exam-notifications', [App\Http\Controllers\Admin\ExamNotificationController::class, 'store'])->name('exam-notifications.store');
    Route::delete('/exam-notifications/{examNotification}', [App\Http\Controllers\Admin\ExamNotificationController::class, 'destroy'])->name('exam-notifications.destroy');

    // ==================== FITUR BARU: MONITORING UJIAN (ExamAnmet) ====================
    Route::get('/exam-monitoring', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'index'])->name('exam-monitoring.index');
    Route::get('/exam-monitoring/api/sessions', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'apiSessions'])->name('exam-monitoring.api.sessions');
    Route::post('/exam-monitoring/{session}/lock', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'lock'])->name('exam-monitoring.lock');
    Route::post('/exam-monitoring/{session}/unlock', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'unlock'])->name('exam-monitoring.unlock');
    Route::post('/exam-monitoring/{session}/end', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'endSession'])->name('exam-monitoring.end');
    Route::get('/exam-monitoring/{session}/violations', [App\Http\Controllers\Admin\ExamMonitoringController::class, 'violations'])->name('exam-monitoring.violations');
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

    // Absensi Kiosk Mode (Fullscreen)
    Route::get('/absensi/kiosk', [App\Http\Controllers\Admin\AbsensiController::class, 'kiosk'])->name('absensi.kiosk');
    Route::post('/absensi/record-face', [App\Http\Controllers\Admin\AbsensiController::class, 'recordFace'])->name('absensi.record-face');

    // Absensi Input & Edit
    Route::middleware(['permission:create-absensi'])->group(function () {
        Route::post('/absensi/manual', [App\Http\Controllers\Admin\AbsensiController::class, 'manualInput'])->name('absensi.manual');
    });

    Route::middleware(['permission:edit-absensi'])->group(function () {
        Route::put('/absensi/{absensi}', [App\Http\Controllers\Admin\AbsensiController::class, 'update'])->name('absensi.update');
    });

    // Face Registration
    Route::get('/absensi/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('absensi.face-register');
    Route::post('/absensi/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('absensi.face-register.store');

    // Face Verification (Admin only)
    Route::middleware(['can:face-registration-admin'])->group(function () {
        Route::get('/absensi/face-verification', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'verificationList'])->name('absensi.face-verification');
        Route::post('/absensi/face-verify/{faceEncoding}', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'verify'])->name('absensi.face-verify');
        Route::delete('/absensi/face-encoding/{faceEncoding}', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'destroy'])->name('absensi.face-encoding.destroy');
        Route::post('/absensi/face-encoding/{faceEncoding}/reset', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'resetVerification'])->name('absensi.face-encoding.reset');
    });

    // Face Descriptors API (for kiosk matching)
    Route::get('/absensi/face-descriptors', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'getDescriptors'])->name('absensi.face-descriptors');

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
});

// Siswa Routes
Route::middleware(['auth'])->prefix('siswa')->name('siswa.')->group(function () {
    // Force setup (password + email) - no middleware restriction
    Route::get('/force-setup', [SiswaProfileController::class, 'forceSetup'])->name('force-setup');
    Route::post('/force-setup', [SiswaProfileController::class, 'updateForceSetup'])->name('force-setup.update');
    
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    
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
    
    // Tracking Lulusan untuk siswa kelas 12/alumni
    Route::get('/lulusan', [App\Http\Controllers\Siswa\LulusanController::class, 'index'])->name('lulusan.index');
    Route::post('/lulusan', [App\Http\Controllers\Siswa\LulusanController::class, 'store'])->name('lulusan.store');
    Route::get('/lulusan/referensi/search', [App\Http\Controllers\Siswa\LulusanController::class, 'searchReferences'])->name('lulusan.referensi.search');
    Route::get('/lulusan/prodi/search', [App\Http\Controllers\Siswa\LulusanController::class, 'searchStudyPrograms'])->name('lulusan.prodi.search');

    // Registrasi wajah mandiri siswa
    Route::get('/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('face-register');
    Route::post('/face-register', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('face-register.store');
    Route::get('/face-descriptors', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'getDescriptors'])->name('face-descriptors');

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
