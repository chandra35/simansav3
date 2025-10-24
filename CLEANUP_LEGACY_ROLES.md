# Cleanup Legacy Roles - Documentation

**Date:** October 24, 2025  
**Phase:** 4.5 - Cleanup Old 'Guru' & 'Staff TU' Roles

---

## 📋 Overview

Sistem role telah diupgrade dari role-based system lama (Guru, Staff TU) menjadi **GTK base role + Tugas Tambahan** system yang lebih fleksibel.

### **Old System:**
- Role "Guru" → untuk semua tenaga pengajar
- Role "Staff TU" → untuk staff tata usaha
- Fixed permissions per role
- Tidak fleksibel untuk multiple roles

### **New System:**
- Role "GTK" → **Base role** untuk semua Guru & Tenaga Kependidikan
- **Tugas Tambahan** → Additional roles yang bisa di-assign/revoke kapan saja:
  - Kepala Madrasah (max 1 active)
  - Wali Kelas
  - BK (Bimbingan Konseling)
  - Admin
  - Operator
  - Bendahara

---

## 🔧 Cleanup Process

### 1. **Migration Command** (Sudah dijalankan sebelumnya)
```bash
php artisan migrate:guru-to-gtk
```
- Migrates all users with "Guru" role → "GTK" role
- Migrates all users with "Staff TU" role → "GTK" role
- Preserves permissions
- Logs all changes

### 2. **Cleanup Commands** (Baru dibuat)

#### Delete "Guru" Role
```bash
php artisan cleanup:guru-role --force
```
**Result:**
- ✅ Role "Guru" deleted
- ✅ 21 permissions detached
- ✅ 0 users affected (sudah dimigrate)

#### Delete "Staff TU" Role
```bash
php artisan cleanup:staff-tu-role --force
```
**Result:**
- ✅ Role "Staff TU" deleted
- ✅ 14 permissions detached
- ✅ 0 users affected (sudah dimigrate)

---

## 📝 Files Modified

### 1. **Seeders Updated**

#### `database/seeders/RolePermissionSeeder.php`
**Before:**
```php
// 8a. GURU - Teaching focus (KEEP FOR BACKWARD COMPATIBILITY, will be migrated to GTK)
$guru = Role::firstOrCreate(['name' => 'Guru']);
$guru->givePermissionTo([...]);

// 9. STAFF TU (Tata Usaha) - Administrative support (KEEP FOR BACKWARD COMPATIBILITY)
$staffTU = Role::firstOrCreate(['name' => 'Staff TU']);
$staffTU->givePermissionTo([...]);
```

**After:**
```php
// 8a. GURU role has been REMOVED - Migrated to GTK + Tugas Tambahan system
// Run "php artisan cleanup:guru-role" if old Guru role still exists

// 9. STAFF TU role has been REMOVED - Migrated to GTK + Tugas Tambahan system
// Staff TU users should be migrated to GTK role with appropriate permissions
```

#### `database/seeders/PermissionSeeder.php`
**Before:**
```php
$guru = Role::firstOrCreate(['name' => 'Guru', 'guard_name' => 'web']);
$guruPermissions = Permission::whereIn('name', [...])->pluck('name')->toArray();
$guru->syncPermissions($guruPermissions);
```

**After:**
```php
$gtk = Role::firstOrCreate(['name' => 'GTK', 'guard_name' => 'web']);
$gtkPermissions = Permission::whereIn('name', [
    'view-siswa',
    'view-kelas',
    'view-mata-pelajaran',
    'view-gtk-dashboard',
    'edit-gtk-profile',
    'change-password-gtk',
])->pluck('name')->toArray();
$gtk->syncPermissions($gtkPermissions);
```

### 2. **New Commands Created**

- `app/Console/Commands/CleanupGuruRole.php`
- `app/Console/Commands/CleanupStaffTuRole.php`

Both commands have:
- ✅ Safety checks (verify no users have the role)
- ✅ Detailed output showing permissions
- ✅ Confirmation prompt (unless `--force`)
- ✅ Transaction rollback on error
- ✅ Success/failure messages

---

## 🎯 Current Role Structure

After cleanup, the system now has **10 roles**:

| ID  | Role Name        | Permissions | Description                          |
|-----|------------------|-------------|--------------------------------------|
| 1   | Super Admin      | 93          | All permissions                      |
| 2   | Kepala Madrasah  | 56          | Full management access               |
| 3   | WAKA             | 34          | Management access (no delete)        |
| 4   | Admin            | 76          | Data management                      |
| 5   | Operator         | 36          | Data entry                           |
| 6   | BK               | 12          | Student counseling                   |
| 7   | Wali Kelas       | 16          | Class management                     |
| 12  | Bendahara        | 9           | Financial management                 |
| 11  | **GTK**          | **5**       | **Base role** for all GTK            |
| 10  | Siswa            | 6           | Student access                       |

**Note:** Roles 8 (Guru) and 9 (Staff TU) have been **permanently deleted**.

---

## ✅ Verification

Run this to verify cleanup:
```bash
php artisan tinker --execute="Spatie\Permission\Models\Role::orderBy('name')->get(['id', 'name'])"
```

Expected: **10 roles**, no "Guru" or "Staff TU" role.

---

## 🚀 Benefits of New System

### **Flexibility:**
- GTK dapat punya multiple roles (contoh: GTK + Wali Kelas + BK)
- Roles dapat di-assign/revoke tanpa hapus user
- Permissions dapat di-customize per user (Hybrid RBAC)

### **Tracking:**
- Semua tugas tambahan tercatat di table `tugas_tambahan`
- Ada history: mulai_tugas, created_by, is_active
- Bisa generate SK dan laporan

### **Business Logic:**
- Kepala Madrasah constraint: hanya 1 aktif
- Auto-assign saat assign Wali Kelas di Manajemen Kelas
- Permission otomatis update saat role berubah

---

## 📚 Related Documentation

- `MIGRATION_GURU_TO_GTK.md` - Migration process documentation
- `HYBRID_RBAC_SYSTEM.md` - Hybrid RBAC implementation
- `TUGAS_TAMBAHAN_FEATURE.md` - Tugas Tambahan feature guide

---

## 🔄 Rollback (Not Recommended)

If you need to restore old roles for any reason:

```bash
# 1. Re-seed dengan seeder lama (restore dari git history)
php artisan db:seed --class=RolePermissionSeeder

# 2. Migrate users kembali ke Guru/Staff TU
# (Manual process - tidak ada command untuk ini)
```

**Warning:** Rollback akan merusak data `tugas_tambahan` yang sudah ada.

---

## ✅ Phase 4.5 Complete!

- [x] Created cleanup commands
- [x] Deleted "Guru" role from database
- [x] Deleted "Staff TU" role from database
- [x] Updated RolePermissionSeeder.php
- [x] Updated PermissionSeeder.php
- [x] Verified role count: 10 roles remaining
- [x] Documented cleanup process

**Next Phase:** 4.6 - Make columns NOT NULL (kategori_ptk, jenis_ptk)
