# Fitur Import Siswa dari Excel

## Deskripsi
Fitur ini memungkinkan admin untuk mengimpor data siswa secara massal menggunakan file Excel (.xlsx atau .xls) dengan validasi data yang komprehensif dan feedback yang informatif.

## URL Akses
```
http://127.0.0.1:8000/admin/siswa/import/form
```

## Kolom Wajib Diisi
1. **NISN** (Nomor Induk Siswa Nasional)
   - Format: 10 digit angka
   - Harus unik (tidak boleh duplikat)
   - Akan digunakan sebagai **username dan password**
   - Contoh: `0123456789`

2. **NIK** (Nomor Induk Kependudukan)
   - Format: 16 digit angka
   - Harus unik (tidak boleh duplikat)
   - Contoh: `1234567890123456`

3. **Nama Lengkap**
   - Text, tidak boleh kosong
   - Contoh: `Ahmad Rizki Pratama`

4. **Jenis Kelamin**
   - Format yang diterima: `L`, `P`, `Laki-laki`, atau `Perempuan`
   - Akan otomatis dinormalisasi menjadi `L` atau `P`
   - Contoh: `L` atau `Laki-laki`

**CATATAN**: Data orang tua **TIDAK** diisi saat import. Siswa akan melengkapi sendiri setelah login.

## Fitur Utama

### 1. Template Excel
- Download template Excel dengan format yang sudah ditentukan
- Berisi header kolom dan 2 baris data contoh
- Dilengkapi dengan catatan/notes tentang aturan validasi
- Format file: .xlsx

### 2. Upload dan Validasi
- Maksimal ukuran file: 2 MB
- Format yang diterima: .xlsx atau .xls
- Progress bar animasi saat upload dan validasi
- Batch processing: 100 record per batch untuk performa optimal

### 3. Feedback Hasil Import
- **Info Box** menampilkan:
  - Jumlah data berhasil diimport (hijau)
  - Jumlah data gagal (merah)
  - Total data yang diproses
  
- **Tabel Detail Error** (jika ada yang gagal):
  - Nomor baris di Excel
  - NISN yang bermasalah
  - Nama siswa
  - Detail kendala/error

### 4. Animasi dan UX
- Progress bar striped dengan animasi
- Fade in animation untuk progress section
- Slide in animation untuk result card
- Smooth scroll ke hasil import
- SweetAlert untuk konfirmasi
- Loading spinner saat proses

## Proses Import

### 1. Data Siswa
Setiap baris data akan membuat:
- **User Account**
  - Username: NISN
  - Email: `{NISN}@siswa.simansa.sch.id`
  - Password: **NISN** (sama dengan create siswa biasa)
  - Role: `Siswa`
  - UUID otomatis
  
- **Data Siswa**
  - NISN, NIK, Nama Lengkap, Jenis Kelamin
  - UUID otomatis
  - Linked ke user_id

- **Data Orang Tua**
  - **Dibuat kosong/NULL** (siswa yang melengkapi)
  - UUID otomatis
  - Linked ke siswa_id

### 2. Validasi
Sistem akan memvalidasi:
- Format NISN (10 digit)
- Format NIK (16 digit)
- Jenis Kelamin (L/P/Laki-laki/Perempuan)
- Duplikasi NISN di database
- Duplikasi NIK di database
- Kelengkapan semua kolom wajib

### 3. Error Handling
Jika terjadi error pada satu baris:
- Baris tersebut akan di-skip
- Baris lainnya tetap diproses
- Error dicatat dengan detail:
  - Nomor baris
  - Data yang bermasalah
  - Penyebab error

## Routes yang Ditambahkan
```php
// admin/siswa/import/form - GET
Route::get('/siswa/import/form', [SiswaImportController::class, 'index'])
    ->name('siswa.import');

// admin/siswa/import/template - GET
Route::get('/siswa/import/template', [SiswaImportController::class, 'downloadTemplate'])
    ->name('siswa.import.template');

// admin/siswa/import/process - POST
Route::post('/siswa/import/process', [SiswaImportController::class, 'import'])
    ->name('siswa.import.process');
```

## File yang Dibuat/Dimodifikasi

### File Baru
1. `app/Imports/SiswaImport.php` (178 lines)
   - Handle import logic
   - Validasi data
   - Batch processing
   - Error tracking

2. `app/Http/Controllers/Admin/SiswaImportController.php` (165 lines)
   - Controller untuk import
   - Generate template Excel
   - Process upload
   - Return JSON response

3. `resources/views/admin/siswa/import.blade.php` (476 lines)
   - UI import dengan animasi
   - Progress bar
   - Result display
   - Error table

### File yang Dimodifikasi
1. `routes/web.php`
   - Tambah import SiswaImportController
   - Tambah 3 routes import

2. `composer.json` (via composer require)
   - Tambah package maatwebsite/excel v3.1.67

## Dependencies
- `maatwebsite/excel` ^3.1
- `phpoffice/phpspreadsheet` 1.30.0 (dependency dari maatwebsite/excel)

## Contoh Data Excel

| NISN       | NIK              | Nama Lengkap        | Jenis Kelamin | Nama Ayah      |
|------------|------------------|---------------------|---------------|----------------|
| 0123456789 | 1234567890123456 | Ahmad Rizki Pratama | L             | Budi Santoso   |
| 0123456790 | 1234567890123457 | Siti Nurhaliza      | P             | Ahmad Yani     |
| 0123456791 | 1234567890123458 | Deni Saputra        | Laki-laki     | Deni Irawan    |

## Testing Checklist

### ✓ Setup Complete
- [x] Package maatwebsite/excel installed
- [x] Routes registered (3 routes)
- [x] Controller created
- [x] Import class created
- [x] View created with animations
- [x] Server running

### ⏳ Pending Testing
- [ ] Navigate to import page
- [ ] Download template file
- [ ] Test valid import (all data correct)
- [ ] Test duplicate NISN
- [ ] Test duplicate NIK
- [ ] Test invalid NISN format (9 digits)
- [ ] Test invalid NIK format (15 digits)
- [ ] Test invalid Jenis Kelamin
- [ ] Test missing required fields
- [ ] Verify user accounts created
- [ ] Verify siswa records created
- [ ] Verify ortu records created
- [ ] Verify default password works
- [ ] Verify progress bar animation
- [ ] Verify result display
- [ ] Verify error table display
- [ ] Test file size limit (>2MB)
- [ ] Test wrong file format (.csv, .doc)

### ⏳ Pending Configuration
- [ ] Create permission `import-siswa`
- [ ] Assign permission to roles (Super Admin, Admin, Operator)
- [ ] Add menu item to sidebar
- [ ] Test permission-based access

## Password Default
Semua siswa yang diimport akan memiliki:
- **Username**: NISN
- **Password**: **NISN** (sama dengan create siswa biasa)
- **Email**: `{NISN}@siswa.simansa.sch.id`

**PENTING**: 
- Siswa login dengan NISN sebagai username dan password
- Siswa **wajib melengkapi data orang tua** setelah login pertama kali

## Keamanan
- Validasi file type (hanya .xlsx dan .xls)
- Validasi file size (max 2MB)
- Database transaction untuk setiap row (jika gagal akan rollback)
- Validasi format NISN dan NIK
- Cek duplikasi sebelum insert
- Password di-hash menggunakan bcrypt

## Performa
- Batch insert: 100 records per batch
- Chunk reading: 100 rows per chunk
- Menghindari memory overflow untuk file besar
- Progress tracking realtime via XHR

## Troubleshooting

### Error: "Class 'Maatwebsite\Excel\Excel' not found"
**Solusi**: Jalankan `composer dump-autoload`

### Error: "Permission denied"
**Solusi**: 
1. Pastikan permission `import-siswa` sudah dibuat
2. Assign permission ke role user yang login

### Progress bar tidak muncul
**Solusi**: Pastikan jQuery sudah ter-load sebelum script import

### File tidak ter-upload
**Solusi**:
1. Cek ukuran file (max 2MB)
2. Cek format file (.xlsx atau .xls)
3. Cek permission folder storage

### Data tidak masuk database
**Solusi**:
1. Cek log Laravel di `storage/logs/laravel.log`
2. Pastikan semua kolom wajib terisi
3. Cek validasi format NISN (10 digit) dan NIK (16 digit)

## Catatan Penting
1. **Backup database** sebelum melakukan import massal
2. Test dengan data sample terlebih dahulu (2-3 baris)
3. Verifikasi hasil import sebelum memberikan akses ke siswa
4. Informasikan password default ke siswa
5. Monitoring log error jika ada masalah

## Screenshot
_To be added setelah testing_

## Changelog
- **2025-10-13**: Initial implementation
  - Import siswa dari Excel
  - Validasi NISN, NIK, Jenis Kelamin
  - Template Excel dengan sample data
  - Progress bar dengan animasi
  - Detail error reporting
  - Batch processing
