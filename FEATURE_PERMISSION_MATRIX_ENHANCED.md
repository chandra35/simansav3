# FEATURE: Enhanced Permission Matrix (RBAC Management)

## Tanggal: 2024
## Status: ✅ Implemented

---

## 📋 Deskripsi Fitur

Permission Matrix adalah fitur manajemen Role-Based Access Control (RBAC) yang enhanced dengan kemampuan:

1. **Editable Checkboxes** - Langsung edit permission per role dengan checkbox
2. **Module-Based Grouping** - Permission dikelompokkan per modul/fitur
3. **Auto-Scan** - Scan otomatis permission yang belum terdaftar
4. **Super Admin Protection** - Proteksi khusus untuk akun Super Admin
5. **Bulk Actions** - Grant/Revoke semua permission sekaligus
6. **AJAX Save** - Simpan perubahan tanpa reload halaman

---

## 🏗️ Arsitektur

### File yang Terlibat

```
app/
├── Services/
│   └── PermissionSyncService.php       # Service utama untuk permission management
├── Http/Controllers/Admin/
│   └── UserController.php              # Controller dengan method-method baru
│
resources/views/admin/users/
└── permission-matrix.blade.php         # View dengan UI enhanced
│
routes/
└── web.php                             # Routes untuk AJAX endpoints
│
config/
└── adminlte.php                        # Menu configuration
```

---

## 🔧 Service: PermissionSyncService

### Lokasi: `app/Services/PermissionSyncService.php`

### Method-method Utama:

#### 1. `getModuleDefinitions()`
Mendefinisikan semua modul dan permission-nya:

```php
return [
    'siswa' => [
        'label' => 'Data Siswa',
        'icon' => 'user-graduate',
        'color' => 'primary',
        'permissions' => [
            'view-siswa',
            'create-siswa',
            'edit-siswa',
            'delete-siswa',
            // ...
        ],
    ],
    // ... modul lainnya
];
```

**Modul yang tersedia:**
- `siswa` - Data Siswa (6 permissions)
- `gtk` - Data GTK (5 permissions)
- `gtk-personal` - GTK Personal (3 permissions)
- `users` - Manajemen User (10 permissions)
- `tahun-pelajaran` - Tahun Pelajaran (6 permissions)
- `kurikulum` - Kurikulum (6 permissions)
- `kelas` - Manajemen Kelas (9 permissions)
- `mutasi` - Mutasi Siswa (7 permissions)
- `nilai` - Nilai & Rapor (5 permissions)
- `absensi` - Absensi (4 permissions)
- `laporan` - Laporan (3 permissions)
- `settings` - Pengaturan (3 permissions)
- `dashboard` - Dashboard (1 permission)

#### 2. `getRolePermissionMatrix()`
Menghasilkan matrix role vs permission:

```php
[
    1 => [ // role_id
        'view-siswa' => true,
        'create-siswa' => false,
        // ...
    ],
    // ...
]
```

#### 3. `getUnregisteredPermissions()`
Mencari permission yang ada di module definitions tapi belum terdaftar di database.

#### 4. `syncPermissionsFromModules()`
Mendaftarkan semua permission dari module definitions ke database.

#### 5. `scanRoutesForPermissions()` & `scanMenuForPermissions()`
Scan routes dan menu config untuk menemukan permission yang digunakan.

---

## 🎮 Controller Methods

### Lokasi: `app/Http/Controllers/Admin/UserController.php`

### Endpoints:

| Method | Route | Name | Fungsi |
|--------|-------|------|--------|
| GET | `/admin/permission-matrix` | `users.permission-matrix` | Tampilkan matrix |
| POST | `/admin/permission-matrix/update` | `admin.permission-matrix.update` | Save perubahan checkbox |
| GET | `/admin/permission-matrix/scan` | `admin.permission-matrix.scan` | Scan unregistered |
| POST | `/admin/permission-matrix/sync` | `admin.permission-matrix.sync` | Sync semua permission |
| POST | `/admin/permission-matrix/role/store` | `admin.permission-matrix.role.store` | Buat role baru |
| POST | `/admin/permission-matrix/role/bulk` | `admin.permission-matrix.role.bulk` | Bulk grant/revoke |
| DELETE | `/admin/permission-matrix/role/{role}` | `admin.permission-matrix.role.destroy` | Hapus role |

### Super Admin Protection

```php
// Di method destroy()
if ($user->hasRole('Super Admin')) {
    $superAdminCount = User::role('Super Admin')->count();
    if ($superAdminCount <= 1) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat menghapus Super Admin terakhir'
        ], 403);
    }
    
    if (!auth()->user()->hasRole('Super Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Hanya Super Admin yang dapat menghapus Super Admin lain'
        ], 403);
    }
}
```

---

## 🎨 UI/UX Features

### Layout

```
┌─────────────────────────────────────────────────────────────┐
│ Permission Matrix                          [Scan] [+Role]   │
├───────────────────┬─────────────────────────────────────────┤
│                   │                                         │
│   ROLES           │   PERMISSION PER MODULE                 │
│   ─────           │   ─────────────────────                 │
│                   │                                         │
│   Super Admin 👑  │   ▼ Data Siswa (6/6)                   │
│   [✓✓] [✗]       │   ┌─────────────────────────────────┐   │
│                   │   │ Permission   │ SA │ Admin │ GTK │   │
│   Admin           │   │ view-siswa   │ ✓  │  [✓]  │ [ ] │   │
│   [✓✓] [✗]       │   │ create-siswa │ ✓  │  [✓]  │ [ ] │   │
│                   │   └─────────────────────────────────┘   │
│   GTK             │                                         │
│   [✓✓] [✗]       │   ▶ Data GTK (5/5)                      │
│                   │   ▶ Manajemen User (8/10)               │
│   Stats           │   ▶ ...                                 │
│   ─────           │                                         │
│   Roles: 5        │                                         │
│   Perms: 68       │                                         │
│   Users: 150      │                                         │
│                   │                                         │
└───────────────────┴─────────────────────────────────────────┘
                                    ┌────────────────────────┐
                                    │ 💾 Simpan Perubahan (3)│
                                    └────────────────────────┘
                                           Floating Button
```

### Fitur UI

1. **Sidebar Roles** - Menampilkan daftar role dengan quick actions
2. **Module Accordion** - Permission dikelompokkan per modul, bisa expand/collapse
3. **Checkbox Inline** - Edit langsung dengan checkbox
4. **Floating Save Button** - Muncul ketika ada perubahan
5. **Change Counter** - Menunjukkan jumlah perubahan pending
6. **Scan Modal** - Modal untuk scan dan sync permission
7. **Add Role Modal** - Modal untuk menambah role baru
8. **Keyboard Shortcut** - Ctrl+S untuk save

---

## 📝 Cara Penggunaan

### 1. Mengakses Permission Matrix

```
Menu: User & Role → Permission Matrix
URL: /admin/permission-matrix
```

### 2. Mengedit Permission

1. Buka accordion module yang diinginkan
2. Centang/uncentang checkbox pada kolom role
3. Floating button akan muncul menunjukkan jumlah perubahan
4. Klik "Simpan Perubahan" atau tekan Ctrl+S

### 3. Bulk Actions per Role

- Klik ✓✓ (grant all) untuk memberikan semua permission
- Klik ✗ (revoke all) untuk mencabut semua permission

### 4. Scan & Sync Permission

1. Klik tombol "Scan" di header
2. Modal akan menampilkan permission yang belum terdaftar
3. Klik "Sync All Permissions" untuk mendaftarkan semua

### 5. Menambah Role Baru

1. Klik tombol "+ Tambah Role"
2. Isi nama role
3. Klik Simpan

---

## 🔒 Security

### Protected Roles

Role berikut tidak dapat dihapus:
- Super Admin
- Admin
- GTK
- Siswa
- Kepala Madrasah
- Wali Kelas

### Super Admin Behavior

- Super Admin selalu memiliki semua permission (tidak perlu assign)
- Super Admin tidak bisa dimodifikasi permission-nya
- Super Admin terakhir tidak bisa dihapus
- Hanya Super Admin yang bisa menghapus Super Admin lain

---

## 🧪 Testing

### Test Cases

1. **Edit Permission**
   - Centang checkbox → Save → Refresh → Masih tercentang
   
2. **Bulk Grant/Revoke**
   - Klik Grant All → Semua checkbox tercentang
   
3. **Super Admin Protection**
   - Coba hapus Super Admin terakhir → Harus gagal
   
4. **Scan Permission**
   - Tambah permission baru di module definitions
   - Scan → Harus muncul di list
   - Sync → Permission terdaftar

---

## 📊 Database

### Tables yang Digunakan (Spatie Permission)

```sql
-- roles
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- permissions
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- role_has_permissions
CREATE TABLE role_has_permissions (
    permission_id BIGINT,
    role_id BIGINT,
    PRIMARY KEY (permission_id, role_id)
);
```

---

## 🔄 Changelog

### v1.0.0 (Initial)
- ✅ Editable checkbox matrix
- ✅ Module-based grouping (13 modules)
- ✅ AJAX save dengan change tracking
- ✅ Scan & sync permission
- ✅ Add/delete role
- ✅ Bulk grant/revoke
- ✅ Super Admin protection
- ✅ Floating save button
- ✅ Keyboard shortcut (Ctrl+S)
- ✅ Menu reorganization (di bawah Pengaturan)

---

## 📚 Related Documentation

- [RBAC_DOCUMENTATION.md](RBAC_DOCUMENTATION.md) - Dokumentasi RBAC general
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v5/introduction) - Package yang digunakan
