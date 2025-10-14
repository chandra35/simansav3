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
    
    // Siswa Import
    Route::get('/siswa/import/form', [SiswaImportController::class, 'index'])->name('siswa.import');
    Route::get('/siswa/import/template', [SiswaImportController::class, 'downloadTemplate'])->name('siswa.import.template');
    Route::post('/siswa/import/process', [SiswaImportController::class, 'import'])->name('siswa.import.process');
    
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
    Route::get('/kelas/{kelas}/assign-siswa', [KelasController::class, 'assignSiswa'])->name('kelas.assign-siswa')->middleware('permission:assign-siswa-kelas');
    Route::post('/kelas/{kelas}/siswa', [KelasController::class, 'storeSiswa'])->name('kelas.siswa.store')->middleware('permission:assign-siswa-kelas');
    Route::delete('/kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'removeSiswa'])->name('kelas.siswa.remove')->middleware('permission:remove-siswa-kelas');
    Route::post('/kelas/{kelas}/wali-kelas', [KelasController::class, 'assignWaliKelas'])->name('kelas.wali-kelas')->middleware('permission:assign-wali-kelas');
    
    // User Management
    Route::get('/users-data', [App\Http\Controllers\Admin\UserController::class, 'data'])->name('users.data');
    Route::post('/users/bulk-delete', [App\Http\Controllers\Admin\UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('/users/{user}/assign-role-form', [App\Http\Controllers\Admin\UserController::class, 'assignRoleForm'])->name('users.assign-role-form');
    Route::post('/users/{user}/assign-role', [App\Http\Controllers\Admin\UserController::class, 'assignRole'])->name('users.assign-role');
    Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/permission-matrix', [App\Http\Controllers\Admin\UserController::class, 'permissionMatrix'])->name('users.permission-matrix');
    
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
    
    Route::get('/profile/ortu', [App\Http\Controllers\Siswa\OrtuController::class, 'show'])->name('profile.ortu');
    Route::put('/profile/ortu', [App\Http\Controllers\Siswa\OrtuController::class, 'update'])->name('profile.ortu.update');
    
    Route::get('/profile/diri', [SiswaProfileController::class, 'diri'])->name('profile.diri');
    Route::put('/profile/diri', [SiswaProfileController::class, 'updateDiri'])->name('profile.diri.update');
    Route::get('/profile/alamat-ortu', [SiswaProfileController::class, 'loadAlamatOrtu'])->name('profile.alamat-ortu');
    
    // AJAX: Search Sekolah by NPSN
    Route::get('/profile/search-sekolah', [SiswaProfileController::class, 'searchSekolah'])->name('profile.search-sekolah');
    
    // Dokumen Management
    Route::get('/dokumen', [App\Http\Controllers\Siswa\DokumenController::class, 'index'])->name('dokumen');
    Route::post('/dokumen/upload', [App\Http\Controllers\Siswa\DokumenController::class, 'upload'])->name('dokumen.upload');
    Route::delete('/dokumen/{id}', [App\Http\Controllers\Siswa\DokumenController::class, 'destroy'])->name('dokumen.destroy');
    
    // API for address dropdowns
    Route::get('/api/cities/{province}', [App\Http\Controllers\Siswa\OrtuController::class, 'getCities'])->name('api.cities');
    Route::get('/api/districts/{city}', [App\Http\Controllers\Siswa\OrtuController::class, 'getDistricts'])->name('api.districts');
    Route::get('/api/villages/{district}', [App\Http\Controllers\Siswa\OrtuController::class, 'getVillages'])->name('api.villages');
});
