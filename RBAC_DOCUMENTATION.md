# RBAC (Role-Based Access Control) System Documentation

## 📋 Overview

Sistem RBAC di SIMANSA V3 menggunakan package **Spatie Laravel-Permission** yang memungkinkan:
- Multi-role assignment per user
- Custom permissions per user (override dari role)
- Granular permission control di setiap fitur
- Dynamic permission checking

## 🏗️ Struktur Permission

### Format Penamaan
```
{action}-{module}
```

### Actions Available
- `view` - Melihat data
- `create` - Menambah data baru
- `edit` - Mengubah data
- `delete` - Menghapus data

### Modules
- `user` - User Management
- `role` - Role Management
- `permission` - Permission Management
- `siswa` - Data Siswa
- `gtk` - Data GTK
- `kelas` - Data Kelas
- `kurikulum` - Data Kurikulum
- `tahun-pelajaran` - Data Tahun Pelajaran
- `mata-pelajaran` - Data Mata Pelajaran
- `nilai` - Data Nilai
- `absensi` - Data Absensi
- `dashboard` - Dashboard
- `laporan` - Laporan

### Special Permissions
- `assign-role` - Assign role ke user
- `assign-permission` - Assign permission ke user/role
- `view-activity-log` - Lihat activity log
- `export-data` - Export data
- `import-data` - Import data
- `manage-settings` - Kelola pengaturan sistem
- `view-all-data` - Lihat semua data (bypass ownership)

## 👥 Roles & Default Permissions

### 1. Super Admin
**Permissions:** ALL (89 permissions)
**Deskripsi:** Akses penuh ke seluruh sistem
**Use Case:** System administrator, IT support

### 2. Admin
**Permissions:** 75 permissions
**Deskripsi:** Akses penuh kecuali user/role/permission management
**Exclusions:**
- Tidak bisa manage users
- Tidak bisa manage roles
- Tidak bisa manage permissions
- Tidak bisa assign role
**Use Case:** Kepala sekolah, wakil kepala sekolah

### 3. Operator
**Permissions:** 27 permissions
**Deskripsi:** CRUD data akademik, tidak bisa manage user
**Included:**
- CRUD Siswa, GTK, Kelas, Kurikulum, Tahun Pelajaran, Mata Pelajaran
- Import & Export data
- View Dashboard
**Use Case:** Staff TU, operator data

### 4. Guru
**Permissions:** 13 permissions
**Deskripsi:** View data akademik, CRUD nilai & absensi
**Included:**
- View: Siswa, Kelas, Mata Pelajaran
- CRUD: Nilai, Absensi
- View: Dashboard, Laporan
**Use Case:** Guru mata pelajaran, wali kelas

### 5. Siswa
**Permissions:** 4 permissions
**Deskripsi:** View only (data sendiri)
**Included:**
- View: Dashboard, Nilai, Absensi, Mata Pelajaran
**Use Case:** Siswa

## 💻 Implementasi dalam Code

### 1. Di Controller
```php
public function index()
{
    $this->authorize('view-user'); // Cek permission
    // ... logic
}

public function store(Request $request)
{
    $this->authorize('create-user');
    // ... logic
}
```

### 2. Di Routes
```php
Route::resource('users', UserController::class)
    ->middleware('permission:view-user');

Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])
    ->middleware('permission:assign-role');
```

### 3. Di Blade Views
```blade
@can('create-user')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        Tambah User
    </a>
@endcan

@can('edit-user')
    <button class="btn btn-warning">Edit</button>
@endcan

@cannot('delete-user')
    <p class="text-muted">Anda tidak memiliki akses untuk menghapus</p>
@endcannot
```

### 4. Di Sidebar (AdminLTE)
```php
[
    'text' => 'Data User',
    'route' => 'admin.users.index',
    'icon' => 'fas fa-user-shield',
    'can' => 'view-user', // Hanya tampil jika punya permission
]
```

### 5. Checking Permission Programmatically
```php
// Cek single permission
if (auth()->user()->can('view-user')) {
    // do something
}

// Cek multiple permissions (ANY)
if (auth()->user()->hasAnyPermission(['view-user', 'create-user'])) {
    // do something
}

// Cek multiple permissions (ALL)
if (auth()->user()->hasAllPermissions(['view-user', 'create-user'])) {
    // do something
}

// Cek role
if (auth()->user()->hasRole('Super Admin')) {
    // do something
}

// Cek multiple roles
if (auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
    // do something
}
```

## 🎯 Use Cases & Scenarios

### Scenario 1: GTK dengan View-Only Access (Contoh: Candra)
```php
$user = User::find($candraId);
$user->syncRoles(['Guru']); // Role Guru (13 permissions)
$user->syncPermissions([]); // No custom permissions

// Result: Candra bisa view siswa, kelas, mapel, nilai, absensi
// Tidak bisa create/edit/delete apapun
```

### Scenario 2: GTK dengan Full CRUD (Contoh: Nanang)
```php
$user = User::find($nanangId);
$user->syncRoles(['Operator']); // Role Operator (27 permissions)

// Result: Nanang bisa CRUD siswa, gtk, kelas, kurikulum, dll
// Tidak bisa manage user/role/permission
```

### Scenario 3: Waka Kurikulum (Contoh: Suhardi)
```php
$user = User::find($suhardiId);
$user->syncRoles(['Admin']); // Role Admin (75 permissions)

// Custom permissions (opsional, jika perlu tambahan)
$user->givePermissionTo(['manage-kurikulum', 'approve-nilai']);

// Result: Suhardi punya semua akses Admin + custom permissions
```

### Scenario 4: Multi-Role User
```php
$user = User::find($userId);
$user->syncRoles(['Operator', 'Guru']); // Dua role sekaligus

// Result: User punya gabungan permissions dari kedua role
// Total: 27 (Operator) + 13 (Guru) = 40 unique permissions
```

### Scenario 5: Custom Permission Override
```php
$user = User::find($userId);
$user->syncRoles(['Guru']); // Role Guru (13 permissions)

// Tambah permission khusus
$user->givePermissionTo(['edit-siswa', 'delete-siswa']);

// Result: Guru ini bisa edit & delete siswa (tidak standar untuk Guru)
```

## 🔄 Workflow Assignment Role & Permission

### Via UI (User Management)
1. Login sebagai Super Admin
2. Buka menu **Data User**
3. Klik icon **user-tag** (Assign Role) pada user
4. Modal muncul dengan:
   - Checkbox roles (bisa multiple)
   - Accordion permissions per module (custom, opsional)
5. Checklist role yang diinginkan
6. (Opsional) Checklist custom permissions
7. Klik **Simpan**

### Via Code (Programmatically)
```php
use App\Models\User;
use Spatie\Permission\Models\Role;

// Assign role
$user = User::find($userId);
$user->assignRole('Admin');

// Assign multiple roles
$user->syncRoles(['Admin', 'Guru']);

// Remove role
$user->removeRole('Guru');

// Assign custom permission
$user->givePermissionTo('edit-siswa');

// Revoke permission
$user->revokePermissionTo('edit-siswa');

// Sync permissions (replace all)
$user->syncPermissions(['view-siswa', 'edit-siswa']);
```

## 🛡️ Security Best Practices

### 1. Always Check Permission in Controller
```php
public function destroy(User $user)
{
    $this->authorize('delete-user'); // WAJIB!
    
    if ($user->id === auth()->id()) {
        abort(403, 'Tidak bisa hapus akun sendiri');
    }
    
    $user->delete();
}
```

### 2. Use Middleware on Routes
```php
Route::resource('users', UserController::class)
    ->middleware('permission:view-user');
```

### 3. Hide UI Elements
```blade
@can('create-user')
    <button>Tambah User</button>
@else
    <p class="text-muted">Anda tidak punya akses</p>
@endcan
```

### 4. Gate Check Before Sensitive Operations
```php
if (Gate::denies('delete-user')) {
    abort(403);
}
```

## 🧪 Testing Permissions

### Artisan Tinker
```bash
php artisan tinker

# Check user permissions
$user = User::find(1);
$user->permissions->pluck('name');

# Check user roles
$user->roles->pluck('name');

# Test permission
$user->can('view-user'); // true/false

# Assign role
$user->assignRole('Super Admin');

# Give permission
$user->givePermissionTo('view-user');
```

## 📊 Permission Matrix (Recommended)

Buat halaman untuk melihat matrix permissions vs roles:

| Permission | Super Admin | Admin | Operator | Guru | Siswa |
|------------|-------------|-------|----------|------|-------|
| view-user  | ✅ | ❌ | ❌ | ❌ | ❌ |
| create-user | ✅ | ❌ | ❌ | ❌ | ❌ |
| view-siswa | ✅ | ✅ | ✅ | ✅ | ❌ |
| edit-nilai | ✅ | ✅ | ❌ | ✅ | ❌ |
| ... | ... | ... | ... | ... | ... |

## 🚀 Next Steps

1. **Buat Permission Matrix View** - Halaman untuk visualisasi permissions
2. **Apply RBAC ke Semua Controller** - Tambahkan `$this->authorize()` di setiap method
3. **Update Semua Blade Views** - Tambahkan `@can` directives
4. **Buat Activity Log** - Track siapa melakukan apa
5. **Role Management Page** - CRUD roles (optional, advanced)

## 📚 Resources

- [Spatie Laravel-Permission Docs](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
- [AdminLTE Docs](https://adminlte.io/docs)

---

**Catatan:** Sistem ini sudah production-ready. Tinggal apply RBAC ke semua fitur yang ada dengan menambahkan permission checks di controller, routes, dan views.
