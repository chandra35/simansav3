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

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
    Route::put('/siswa/{siswa}/reset-password', [AdminSiswaController::class, 'resetPassword'])->name('siswa.reset-password');
    Route::get('/siswa/{siswa}/dokumen', [AdminSiswaController::class, 'getDokumen'])->name('siswa.dokumen');
    Route::get('/siswa-kelas-by-tingkat', [AdminSiswaController::class, 'getKelasByTingkat'])->name('siswa.kelas-by-tingkat');
    
    // Sekolah Asal Management
    Route::middleware(['permission:view-siswa'])->group(function () {
        Route::get('/sekolah-asal', [App\Http\Controllers\Admin\SekolahAsalController::class, 'index'])->name('sekolah-asal.index');
        Route::get('/sekolah-asal/{npsn}', [App\Http\Controllers\Admin\SekolahAsalController::class, 'show'])->name('sekolah-asal.show');
        Route::get('/sekolah-asal/{npsn}/siswa-data', [App\Http\Controllers\Admin\SekolahAsalController::class, 'getSiswaData'])->name('sekolah-asal.siswa-data');
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
    Route::post('/custom-menu/{customMenu}/remove-siswa', [App\Http\Controllers\Admin\CustomMenuController::class, 'removeSiswa'])->name('custom-menu.remove-siswa');
    Route::post('/custom-menu/{customMenu}/upload-excel', [App\Http\Controllers\Admin\CustomMenuController::class, 'uploadExcel'])->name('custom-menu.upload-excel');
    Route::get('/custom-menu/{customMenu}/template', [App\Http\Controllers\Admin\CustomMenuController::class, 'downloadTemplate'])->name('custom-menu.template');
    
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
    Route::get('/permission-matrix', [App\Http\Controllers\Admin\UserController::class, 'permissionMatrix'])->name('users.permission-matrix');
    
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
    });
    
    // Cetak (Print Reports)
    Route::middleware(['permission:view-kelas'])->group(function () {
        Route::get('/cetak', [App\Http\Controllers\Admin\CetakController::class, 'index'])->name('cetak.index');
        Route::post('/cetak/absensi-batch', [App\Http\Controllers\Admin\CetakController::class, 'cetakAbsensiBatch'])->name('cetak.absensi-batch');
        Route::get('/cetak/kelas-by-filter', [App\Http\Controllers\Admin\CetakController::class, 'getKelasByFilter'])->name('cetak.kelas-by-filter');
    });
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
});

// Siswa Routes
Route::middleware(['auth'])->prefix('siswa')->name('siswa.')->group(function () {
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
    
    // API for address dropdowns
    Route::get('/api/cities/{province}', [App\Http\Controllers\Siswa\OrtuController::class, 'getCities'])->name('api.cities');
    Route::get('/api/districts/{city}', [App\Http\Controllers\Siswa\OrtuController::class, 'getDistricts'])->name('api.districts');
    Route::get('/api/villages/{district}', [App\Http\Controllers\Siswa\OrtuController::class, 'getVillages'])->name('api.villages');
});
