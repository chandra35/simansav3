# FEATURE: Reset Data Sistem dengan Backup & Archive Mode

**Tanggal**: 13 November 2025  
**Status**: ✅ COMPLETED  
**Developer**: AI Assistant

---

## 📋 Overview

Fitur untuk menghapus data sistem secara massal dengan keamanan tingkat enterprise:
- **Auto-backup** sebelum delete
- **Hybrid mode**: Archive (soft delete) atau Permanent (hard delete)
- **Password re-authentication** untuk setiap aksi
- **Per-feature delete**: Hapus Siswa saja, GTK saja, atau Kelas saja
- **Backup & Restore**: Kelola backup dengan download/restore/delete

---

## 🎯 User Requirements

1. ✅ Auto-backup sebelum delete
2. ✅ Menu Backup dan Restore
3. ✅ Hybrid: Permanent delete atau Archive (soft delete)
4. ✅ Super Admin perlu re-enter password untuk konfirmasi
5. ✅ Hapus per fitur (Siswa, GTK, Kelas, dll)
6. ✅ Hapus semua data sekaligus

---

## 📦 Files Created/Modified

### 1. Services (Business Logic)

#### `app/Services/DatabaseBackupService.php` (NEW - 356 lines)
**Purpose**: Handle database backup dan restore menggunakan mysqldump

**Key Methods**:
```php
createBackup($reason)           // Buat backup dengan mysqldump + ZIP
restoreBackup($filename)        // Extract ZIP dan restore ke database
listBackups()                   // List semua backup dengan metadata
deleteBackup($filename)         // Hapus file backup
cleanupOldBackups()             // Hapus backup lama, keep only 10
getTotalBackupSize()            // Hitung total ukuran semua backup
```

**Features**:
- Automatic ZIP compression
- File naming: `backup_manual_2025-11-13_102030.sql.zip`
- Auto-cleanup (max 10 backups)
- Human-readable file sizes (KB, MB, GB)
- Human-readable age (1 menit yang lalu, 2 jam yang lalu)

**Storage Location**: `storage/app/backups/database/`

---

#### `app/Services/SystemResetService.php` (NEW - 380 lines)
**Purpose**: Handle deletion logic dengan archive support

**Key Methods**:
```php
countAffectedData()                          // Hitung total data yang akan terhapus
resetAllData($mode, $autoBackup)             // Hapus SEMUA data
deleteSiswaOnly($mode, $autoBackup)          // Hapus data Siswa saja
deleteGtkOnly($mode, $autoBackup)            // Hapus data GTK saja
deleteKelasOnly($mode, $autoBackup)          // Hapus data Kelas saja
```

**Private Methods** (deletion order):
```php
_deleteSiswa($mode)              // Siswa + set kelas_saat_ini_id = NULL
_deleteOrtu($mode)               // Ortu siswa
_deleteDokumenSiswa($mode)       // Dokumen siswa
_deleteSiswaKelas($mode)         // Riwayat kelas siswa
_deleteMutasiSiswa($mode)        // Mutasi siswa
_deleteGtk($mode)                // GTK + set kelas.wali_kelas_id = NULL
_deleteTugasTambahan($mode)      // Tugas tambahan GTK
_deleteKelas($mode)              // Kelas
_deleteTahunPelajaran($mode)     // Tahun pelajaran
_deleteKurikulum($mode)          // Kurikulum
_deleteJurusan($mode)            // Jurusan
_deleteActivityLogs()            // Activity logs cleanup
```

**Mode Support**:
- `permanent`: Hard delete (forceDelete())
- `archive`: Soft delete (delete() - set deleted_at)

**Safety Features**:
- DB Transaction dengan rollback on error
- Auto-backup integration
- Critical logging untuk audit trail
- Dependency-aware deletion order
- Foreign key constraint handling

**Deletion Order** (safe):
```
1. Set NULL: siswa.kelas_saat_ini_id, kelas.wali_kelas_id
2. siswa_kelas (junction table)
3. mutasi_siswa
4. dokumen_siswa
5. ortu
6. siswa
7. tugas_tambahan
8. gtks
9. kelas
10. tahun_pelajaran
11. jurusan
12. kurikulum
13. activity_logs (related)
14. users (siswa/gtk accounts)
```

---

### 2. Controller

#### `app/Http/Controllers/Admin/SystemResetController.php` (NEW - 238 lines)
**Purpose**: HTTP layer untuk reset system dengan security checks

**Methods**:
| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/pengaturan/reset-system` | Tampilkan halaman utama dengan data counts |
| `verifyPassword()` | POST `/verify-password` | Verify password sebelum action |
| `deleteAll()` | POST `/delete-all` | Hapus SEMUA data |
| `deleteSiswa()` | POST `/delete-siswa` | Hapus data Siswa saja |
| `deleteGtk()` | POST `/delete-gtk` | Hapus data GTK saja |
| `deleteKelas()` | POST `/delete-kelas` | Hapus data Kelas saja |
| `createBackup()` | POST `/create-backup` | Buat manual backup |
| `downloadBackup()` | GET `/download-backup/{filename}` | Download backup file |
| `deleteBackup()` | DELETE `/delete-backup/{filename}` | Hapus backup file |
| `restoreBackup()` | POST `/restore-backup` | Restore dari backup |

**Security**:
- Super Admin only (role check)
- Password re-authentication setiap action
- Confirmation text validation (e.g., "HAPUS SEMUA DATA")
- Safety backup sebelum restore

---

### 3. Views

#### `resources/views/admin/pengaturan/reset-system.blade.php` (NEW - 470 lines)
**Purpose**: Halaman utama Reset Data Sistem

**Sections**:

1. **Alert Warning** - Peringatan untuk Super Admin
2. **Card: Hapus SEMUA Data**
   - Statistik data (Siswa, GTK, Kelas, dll)
   - Button untuk hapus semua
3. **Card: Hapus Per Fitur**
   - Button hapus Siswa (count)
   - Button hapus GTK (count)
   - Button hapus Kelas (count)
4. **Card: Backup & Restore**
   - Tabel list backups dengan metadata
   - Actions: Download, Restore, Delete
   - Button buat backup manual

**Modals**:

1. **deleteAllModal**
   - Mode selection (Archive/Permanent)
   - Auto-backup checkbox
   - Konfirmasi: "HAPUS SEMUA DATA"
   - Password input

2. **deleteFeatureModal**
   - Mode selection
   - Konfirmasi dinamis (HAPUS DATA SISWA, dll)
   - Password input

3. **restoreModal**
   - Konfirmasi: "RESTORE DATABASE"
   - Password input
   - Safety backup otomatis

**JavaScript Functions**:
```javascript
showDeleteAllModal()             // Show modal hapus semua
showDeleteModal(feature, count)  // Show modal per-feature
executeDeleteAll()               // Execute delete all via AJAX
executeDeleteFeature()           // Execute per-feature delete
createBackup()                   // Buat manual backup
showRestoreModal(filename)       // Show restore modal
executeRestore()                 // Execute restore
deleteBackupFile(filename)       // Delete backup file
```

**AJAX Endpoints**:
- POST `/pengaturan/reset-system/delete-all`
- POST `/pengaturan/reset-system/delete-siswa`
- POST `/pengaturan/reset-system/delete-gtk`
- POST `/pengaturan/reset-system/delete-kelas`
- POST `/pengaturan/reset-system/create-backup`
- POST `/pengaturan/reset-system/restore-backup`
- DELETE `/pengaturan/reset-system/delete-backup/{filename}`

---

### 4. Routes

#### `routes/web.php` (MODIFIED)
**Added 10 routes**:

```php
// Reset System
Route::get('/pengaturan/reset-system', [SystemResetController::class, 'index'])
    ->name('reset-system.index');
Route::post('/pengaturan/reset-system/verify-password', [SystemResetController::class, 'verifyPassword'])
    ->name('reset-system.verify-password');
Route::post('/pengaturan/reset-system/delete-all', [SystemResetController::class, 'deleteAll'])
    ->name('reset-system.delete-all');
Route::post('/pengaturan/reset-system/delete-siswa', [SystemResetController::class, 'deleteSiswa'])
    ->name('reset-system.delete-siswa');
Route::post('/pengaturan/reset-system/delete-gtk', [SystemResetController::class, 'deleteGtk'])
    ->name('reset-system.delete-gtk');
Route::post('/pengaturan/reset-system/delete-kelas', [SystemResetController::class, 'deleteKelas'])
    ->name('reset-system.delete-kelas');
Route::post('/pengaturan/reset-system/create-backup', [SystemResetController::class, 'createBackup'])
    ->name('reset-system.create-backup');
Route::get('/pengaturan/reset-system/download-backup/{filename}', [SystemResetController::class, 'downloadBackup'])
    ->name('reset-system.download-backup');
Route::delete('/pengaturan/reset-system/delete-backup/{filename}', [SystemResetController::class, 'deleteBackup'])
    ->name('reset-system.delete-backup');
Route::post('/pengaturan/reset-system/restore-backup', [SystemResetController::class, 'restoreBackup'])
    ->name('reset-system.restore-backup');
```

**Middleware**: `auth`, `admin` (Super Admin via role check dalam controller)

---

### 5. Menu

#### `config/adminlte.php` (MODIFIED)
**Added menu item**:

```php
[
    'text' => 'Reset Data Sistem',
    'route' => 'admin.reset-system.index',
    'icon' => 'fas fa-fw fa-exclamation-triangle',
    'icon_color' => 'danger',
    'can' => 'manage-settings',
    'active' => ['admin/pengaturan/reset-system*'],
],
```

**Location**: Tools → Reset Data Sistem (di bawah Update API Token)

---

### 6. Database Migration

#### `2025_11_13_020017_add_soft_deletes_to_multiple_tables_for_archive_mode.php` (NEW)
**Purpose**: Menambahkan kolom `deleted_at` untuk soft delete support

**Tables Modified** (12 tables):
1. `siswa`
2. `ortu`
3. `dokumen_siswa`
4. `siswa_kelas`
5. `mutasi_siswa`
6. `gtks` (bukan `gtk`)
7. `tugas_tambahan`
8. `kelas`
9. `tahun_pelajaran`
10. `kurikulum`
11. `jurusan`
12. `users`

**Column Added**: `deleted_at TIMESTAMP NULL`

**Rollback**: `dropSoftDeletes()` untuk semua tabel

---

### 7. Models Modified

**Added `SoftDeletes` trait** to:

1. ✅ `app/Models/Siswa.php`
2. ✅ `app/Models/Ortu.php`
3. ✅ `app/Models/DokumenSiswa.php`
4. ✅ `app/Models/SiswaKelas.php` (already had)
5. ✅ `app/Models/MutasiSiswa.php` (already had)
6. ✅ `app/Models/Gtk.php`
7. ✅ `app/Models/TugasTambahan.php`
8. ✅ `app/Models/Kelas.php` (already had)
9. ✅ `app/Models/TahunPelajaran.php` (already had)
10. ✅ `app/Models/Kurikulum.php` (already had)
11. ✅ `app/Models/Jurusan.php` (already had)
12. ✅ `app/Models/User.php`

**Import Added**:
```php
use Illuminate\Database\Eloquent\SoftDeletes;
```

**Trait Usage**:
```php
class Siswa extends Model
{
    use HasUuid, HasActivityLog, HasCreatedUpdatedBy, SoftDeletes;
    // ...
}
```

---

## 🔒 Security Features

### 1. Role-Based Access Control
- **Super Admin ONLY** via `hasRole('Super Admin')` check
- 403 Forbidden untuk user lain

### 2. Password Re-Authentication
- Setiap action memerlukan password ulang
- Hash check: `Hash::check($request->password, Auth::user()->password)`
- Prevent unauthorized action meskipun session masih valid

### 3. Confirmation Text
- User harus ketik konfirmasi exact match:
  - "HAPUS SEMUA DATA"
  - "HAPUS DATA SISWA"
  - "HAPUS DATA GTK"
  - "HAPUS DATA KELAS"
  - "RESTORE DATABASE"

### 4. Auto-Backup Before Action
- Backup otomatis sebelum delete all
- Safety backup sebelum restore
- Mencegah data loss

### 5. Transaction Rollback
- Semua delete dalam DB::transaction()
- Rollback otomatis jika error
- Data integrity terjaga

### 6. Critical Logging
- Log level CRITICAL untuk delete all
- Log level WARNING untuk per-feature delete
- Include: user_id, user_name, IP address, timestamp, deleted counts

---

## 📊 Database Relationship Handling

### Foreign Key Constraints

**CASCADE** (auto-delete):
- `kelas.tahun_pelajaran_id` → CASCADE
- `siswa_kelas.tahun_pelajaran_id` → CASCADE

**RESTRICT** (prevent delete):
- `kelas.kurikulum_id` → RESTRICT
  - **Solution**: Delete kelas first, then kurikulum

**SET NULL** (manual handling):
- `siswa.kelas_saat_ini_id` → Manual SET NULL before delete
- `kelas.wali_kelas_id` → Manual SET NULL before delete

### Deletion Order (Dependency-Safe)

```
1. NULL References First
   - siswa.kelas_saat_ini_id = NULL
   - kelas.wali_kelas_id = NULL

2. Child Tables (No Dependencies)
   - siswa_kelas
   - mutasi_siswa
   - dokumen_siswa
   - ortu

3. Parent Tables (Referenced by Others)
   - siswa
   - tugas_tambahan
   - gtks

4. System Tables
   - kelas (before kurikulum due to RESTRICT)
   - tahun_pelajaran
   - jurusan
   - kurikulum

5. Cleanup
   - activity_logs (related)
   - users (orphaned siswa/gtk accounts)
```

---

## 🎨 UI/UX Features

### Color Coding
- **Danger Red**: Hapus SEMUA Data
- **Warning Yellow**: Hapus Per Fitur
- **Primary Blue**: Backup & Restore
- **Success Green**: Buat Backup

### Icons
- 💣 Bomb icon untuk "Hapus SEMUA"
- 📋 List icon untuk "Hapus Per Fitur"
- 💾 Database icon untuk "Backup & Restore"
- ⚠️ Warning triangle untuk page header

### SweetAlert2 Feedback
- Loading state: "Menghapus...", "Mohon tunggu..."
- Success: "Berhasil!" dengan reload
- Error: "Gagal" dengan error message

### Real-time Statistics
- Badge counts untuk setiap entitas
- Total backup size dan count
- Human-readable file ages

---

## 🧪 Testing Checklist

### Manual Testing Required

- [ ] **Backup Creation**
  - [ ] Manual backup creates valid ZIP file
  - [ ] Auto-backup before delete works
  - [ ] Backup list shows metadata correctly
  - [ ] Old backups cleaned up (max 10)

- [ ] **Restore**
  - [ ] Restore from backup works
  - [ ] Safety backup created before restore
  - [ ] Data integrity after restore

- [ ] **Delete All**
  - [ ] Password wrong → error
  - [ ] Confirmation text wrong → error
  - [ ] Archive mode sets deleted_at
  - [ ] Permanent mode force deletes
  - [ ] All data actually deleted
  - [ ] No FK constraint violations

- [ ] **Delete Per-Feature**
  - [ ] Delete Siswa only
  - [ ] Delete GTK only
  - [ ] Delete Kelas only
  - [ ] Other data NOT affected

- [ ] **Security**
  - [ ] Non-Super Admin → 403
  - [ ] Password re-auth required
  - [ ] Transaction rollback on error
  - [ ] Critical logging works

- [ ] **UI/UX**
  - [ ] Counts displayed correctly
  - [ ] Modals open/close properly
  - [ ] AJAX success/error handling
  - [ ] Page reload after success

---

## ⚠️ Known Issues / Limitations

### 1. Mysqldump Dependency
- Requires `mysqldump` command available in PATH
- Windows: Need MySQL/MariaDB installed
- Alternative: Use Laravel backup packages

### 2. Large Database Performance
- Mysqldump can be slow on large databases
- ZIP compression takes time
- Consider background job for production

### 3. Lint Errors (Non-Blocking)
- SystemResetService: Doc comments, line length
- TugasTambahan: auth() helper (separate issue)
- These are style issues, not functional errors

### 4. No Progress Bar
- Delete/restore operations block UI
- Consider implementing job queue + progress tracking

### 5. Single Database Only
- No multi-database support
- Hardcoded to default DB connection

---

## 🚀 Future Enhancements

1. **Queue Integration**
   - Move backup/delete to background jobs
   - Real-time progress bar via WebSockets

2. **Scheduled Backups**
   - Cron job untuk auto-backup harian
   - Configurable retention policy

3. **Cloud Backup**
   - Upload backups ke S3/Google Drive
   - Off-site disaster recovery

4. **Restore Preview**
   - Show backup contents before restore
   - Compare current vs backup data

5. **Granular Restore**
   - Restore specific tables only
   - Restore specific records

6. **Audit Report**
   - PDF report of deleted data
   - Email notification to Super Admin

7. **Data Export**
   - Export to Excel before delete
   - Archive to external storage

---

## 📝 Usage Instructions

### Untuk Super Admin

1. **Akses Menu**
   - Login sebagai Super Admin
   - Sidebar → Tools → Reset Data Sistem

2. **Hapus Semua Data**
   - Klik "Hapus SEMUA Data"
   - Pilih mode: Archive atau Permanent
   - Centang "Auto-backup" (recommended)
   - Ketik: `HAPUS SEMUA DATA`
   - Masukkan password
   - Klik "Hapus SEMUA"

3. **Hapus Per Fitur**
   - Klik button fitur (Siswa/GTK/Kelas)
   - Pilih mode
   - Ketik konfirmasi (e.g., `HAPUS DATA SISWA`)
   - Masukkan password
   - Klik "Hapus"

4. **Buat Backup Manual**
   - Klik "Buat Backup" di section Backup & Restore
   - File akan tersimpan di tabel

5. **Download Backup**
   - Klik "Download" di row backup
   - File ZIP akan terdownload

6. **Restore Database**
   - Klik "Restore" di row backup
   - Ketik: `RESTORE DATABASE`
   - Masukkan password
   - Safety backup dibuat otomatis
   - Database di-restore

7. **Hapus Backup Lama**
   - Klik icon trash di row backup
   - Konfirmasi via SweetAlert2

---

## 🔧 Configuration

### Environment Variables Required

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Storage Permissions

Ensure writable:
```
storage/app/backups/database/
storage/logs/
```

### Mysqldump Path

Windows:
```
C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe
```

Linux/Mac:
```
/usr/bin/mysqldump
```

---

## 📚 Developer Notes

### Service Pattern
- Business logic isolated in Services
- Controller hanya HTTP layer
- Easy to test dan maintain

### Dependency Injection
```php
public function __construct(
    SystemResetService $resetService,
    DatabaseBackupService $backupService
) {
    $this->resetService = $resetService;
    $this->backupService = $backupService;
}
```

### Error Handling
```php
try {
    DB::beginTransaction();
    // ... operations
    DB::commit();
    return ['success' => true, ...];
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Error', ['exception' => $e]);
    return ['success' => false, 'message' => $e->getMessage()];
}
```

### Soft Delete Query
```php
// Archive mode
$model->delete(); // Sets deleted_at

// Permanent mode
$model->forceDelete(); // Removes from DB

// Include soft deleted
Model::withTrashed()->get();

// Only soft deleted
Model::onlyTrashed()->get();

// Restore
$model->restore();
```

---

## ✅ Completion Checklist

- [x] DatabaseBackupService created
- [x] SystemResetService created
- [x] SystemResetController created
- [x] View (reset-system.blade.php) created
- [x] Routes added (10 routes)
- [x] Menu added (Tools → Reset Data Sistem)
- [x] Migration for soft deletes created
- [x] Migration executed successfully
- [x] Models updated with SoftDeletes trait
- [x] Documentation completed

---

## 🎉 Status: READY FOR TESTING

Semua komponen sudah dibuat dan terintegrasi. Silakan lakukan manual testing sesuai checklist di atas.

**Next Steps**:
1. Test manual backup creation
2. Test delete all (archive mode)
3. Test delete per-feature
4. Test restore functionality
5. Verify transaction rollback
6. Check activity logs

---

**Created by**: AI Assistant  
**Date**: 13 November 2025  
**Version**: 1.0.0
