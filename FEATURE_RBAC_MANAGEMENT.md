# FEATURE: RBAC Management UI

## Overview
Fitur Role-Based Access Control (RBAC) Management UI untuk simansav3 menggunakan Spatie Laravel Permission.

## Implementasi

### 1. Controllers
- `app/Http/Controllers/Admin/RoleController.php` - Mengelola roles
- `app/Http/Controllers/Admin/PermissionController.php` - Mengelola permissions

### 2. Views

#### Role Management
- `resources/views/admin/roles/index.blade.php` - Daftar roles dengan card view modern
- `resources/views/admin/roles/create.blade.php` - Form tambah role dengan permission picker
- `resources/views/admin/roles/edit.blade.php` - Form edit role dengan permission picker
- `resources/views/admin/roles/show.blade.php` - Detail role dengan daftar users dan permissions

#### Permission Management
- `resources/views/admin/permissions/index.blade.php` - Daftar permissions grouped by category
- `resources/views/admin/permissions/create.blade.php` - Form tambah permission
- `resources/views/admin/permissions/show.blade.php` - Detail permission dengan daftar roles

### 3. Routes

```php
// Role Management
Route::middleware(['permission:assign-roles'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::post('/roles/{role}/assign-user', [RoleController::class, 'assignUser']);
    Route::delete('/roles/{role}/remove-user', [RoleController::class, 'removeUser']);
});

// Permission Management  
Route::middleware(['permission:assign-permissions'])->group(function () {
    Route::resource('permissions', PermissionController::class);
    Route::post('/permissions/bulk-create', [PermissionController::class, 'bulkCreate']);
});
```

### 4. Menu
Menu "Role & Permission" ditambahkan di submenu "Manajemen Data" pada sidebar.

## Fitur

### Role Management
1. **Daftar Roles** - Card view dengan statistik users dan permissions
2. **Tambah Role** - Form dengan permission picker (grouped by category)
3. **Edit Role** - Update nama dan permissions
4. **Hapus Role** - Dengan validasi (tidak bisa hapus jika masih ada users)
5. **Detail Role** - Lihat semua permissions dan users
6. **Assign/Remove User** - Tambah atau hapus user dari role

### Permission Management
1. **Daftar Permissions** - Grouped by category dengan statistik
2. **Tambah Permission** - Single permission
3. **Bulk Create** - Tambah banyak permission sekaligus (satu per baris)
4. **Hapus Permission** - Dengan validasi (tidak bisa hapus jika digunakan role)
5. **Detail Permission** - Lihat roles yang menggunakan permission ini

### System Roles (Protected)
Roles berikut tidak dapat dihapus:
- Super Admin
- Siswa
- GTK
- Admin
- Kepala Madrasah
- WAKA
- Operator
- BK
- Wali Kelas
- Bendahara

Roles berikut tidak dapat diedit:
- Super Admin
- Siswa

## Permission Format
Format nama permission yang disarankan: `kategori-aksi`

Contoh:
- `view-siswa`
- `create-kelas`
- `edit-gtk`
- `delete-users`
- `manage-settings`
- `export-laporan`

## Akses
- Akses ke menu Role & Permission membutuhkan permission `assign-roles`
- Manajemen permissions membutuhkan permission `assign-permissions`
- Super Admin secara default memiliki semua permissions

## Screenshot Features

### Roles Index
- Card grid dengan informasi:
  - Nama role
  - Badge System/Custom
  - Jumlah users
  - Jumlah permissions
  - Preview 5 permission pertama
  - Dropdown menu aksi

### Role Detail
- Hero section dengan gradient background
- Statistik users dan permissions
- Permissions grouped by category
- Daftar users dengan avatar
- Modal untuk assign user baru

### Permissions Index
- Grouped by category dengan warna berbeda
- Quick action buttons (view, delete)
- Modal untuk bulk create

## Activity Logging
Semua operasi RBAC dicatat di Activity Log:
- Create role
- Update role
- Delete role
- Create permission
- Update permission
- Delete permission
- Assign user to role
- Remove user from role

## Integration dengan Sistem Existing
- Menggunakan Spatie Permission yang sudah terinstal
- Terintegrasi dengan User Management
- Activity Log untuk audit trail
- AdminLTE menu system

## Catatan Pengembangan
- Permissions dikelompokkan berdasarkan prefix (bagian sebelum tanda hubung)
- System roles memiliki proteksi terhadap perubahan kritis
- Validasi mencegah penghapusan role/permission yang masih digunakan
