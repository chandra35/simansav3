# Fitur Cetak Dokumen - Batch Printing

## 📋 Deskripsi Fitur
Fitur untuk mencetak berbagai jenis dokumen secara batch (massal). Memungkinkan user untuk mencetak absensi beberapa kelas sekaligus dalam satu file PDF.

## ✨ Fitur Utama
- **Batch Print Absensi**: Cetak absensi beberapa kelas sekaligus dalam 1 PDF
- **Filter Dinamis**: Filter berdasarkan Tahun Pelajaran, Tingkat, Jurusan, Kurikulum
- **Pilih Kelas Spesifik**: Checkbox untuk memilih kelas yang akan dicetak
- **Single PDF Output**: Semua kelas dalam 1 file PDF, setiap kelas di halaman terpisah
- **Memory Optimized**: Logo diproses 1x, digunakan untuk semua kelas

## 🗂️ File yang Dibuat/Dimodifikasi

### 1. Controller
**File**: `app/Http/Controllers/Admin/CetakController.php`
- Method `index()`: Display halaman cetak dengan filter
- Method `cetakAbsensiBatch()`: Generate batch PDF
- Method `processLogo()`: Helper untuk resize logo
- Method `getKelasByFilter()`: AJAX endpoint untuk filter kelas

### 2. Routes
**File**: `routes/web.php`
```php
Route::middleware(['permission:view-kelas'])->group(function () {
    Route::get('/cetak', [CetakController::class, 'index'])->name('cetak.index');
    Route::post('/cetak/absensi-batch', [CetakController::class, 'cetakAbsensiBatch'])->name('cetak.absensi-batch');
    Route::get('/cetak/kelas-by-filter', [CetakController::class, 'getKelasByFilter'])->name('cetak.kelas-by-filter');
});
```

### 3. Views
**File**: `resources/views/admin/cetak/index.blade.php`
- Layout AdminLTE
- Filter form (Tahun Pelajaran, Tingkat, Jurusan, Kurikulum)
- AJAX load kelas berdasarkan filter
- Checkbox list untuk pilih kelas
- Submit button untuk cetak

**File**: `resources/views/admin/cetak/absensi-batch.blade.php`
- Template batch PDF
- Loop melalui semua kelas yang dipilih
- Page break antar kelas
- Reuse template cetak-absensi.blade.php

### 4. Config
**File**: `config/adminlte.php`
- Added menu item "Cetak Dokumen" dengan icon printer

## 🎯 Cara Penggunaan

### 1. Akses Menu Cetak
- Login sebagai user dengan permission `view-kelas`
- Klik menu **Cetak Dokumen** di sidebar

### 2. Filter Kelas
- Pilih **Tahun Pelajaran** (wajib)
- Pilih **Tingkat** (wajib) - contoh: 10, 11, 12
- Pilih **Jurusan** (opsional) - contoh: IPA, IPS
- Pilih **Kurikulum** (opsional)
- Klik tombol **Cari Kelas**

### 3. Pilih Kelas
- Sistem akan menampilkan daftar kelas sesuai filter
- Centang kelas yang akan dicetak (atau "Pilih Semua")
- Jumlah kelas terpilih akan ditampilkan

### 4. Cetak PDF
- Klik tombol **Cetak Absensi**
- PDF akan terbuka di tab baru
- Setiap kelas akan berada di halaman terpisah dalam 1 PDF

## 🔧 Konfigurasi Teknis

### Memory & Time Limit
```php
ini_set('memory_limit', '512M');  // For processing multiple classes
set_time_limit(300);              // 5 minutes timeout
```

### Logo Processing
- Logo di-resize 1x dengan GD Library
- Ukuran resize dari setting database: `logo_kemenag_height`, `logo_sekolah_height`
- Di-encode ke base64 untuk ditampilkan di PDF
- Reuse untuk semua kelas (hemat memory)

### Paper Size
- **Legal Portrait** (8.5" x 14" / 216mm x 356mm)
- Margin: 15mm top/bottom, 10mm left/right
- Memastikan semua konten fit di 1 halaman per kelas

## 📊 Database Query Optimization
```php
$kelasList = Kelas::with(['siswa', 'waliKelas', 'tahunPelajaran'])
    ->where('tahun_pelajaran_id', $tahunPelajaranId)
    ->where('tingkat', $tingkat)
    ->when($jurusanId, fn($q) => $q->where('jurusan_id', $jurusanId))
    ->when($kurikulumId, fn($q) => $q->where('kurikulum_id', $kurikulumId))
    ->when($kelasIds, fn($q) => $q->whereIn('id', $kelasIds))
    ->get();
```
- Eager loading untuk menghindari N+1 query
- Conditional where untuk filter opsional

## 🎨 UI/UX Features

### Filter Form
- Required fields marked dengan asterisk (*)
- Dropdown dengan default selected (tahun pelajaran aktif)
- Button loading state saat AJAX

### Kelas List
- Grid layout (3 kolom)
- Checkbox dengan label nama kelas + jumlah siswa
- "Pilih Semua" untuk convenience
- Counter jumlah kelas terpilih

### Validation
- Client-side: Check minimal 1 kelas dipilih
- Server-side: Validate permission dan data integrity
- SweetAlert untuk error/warning messages

## 🚀 Fitur Mendatang (Placeholder)
Menu cetak sudah menyediakan tempat untuk:
- **Daftar Nilai**: Cetak nilai siswa per mata pelajaran
- **Rapor**: Cetak rapor siswa
- **Surat Keterangan**: Generate surat-surat resmi

## 🔒 Permissions
- Route middleware: `permission:view-kelas`
- Controller authorization: `$this->authorize('view-kelas')`
- Menu sidebar: `'can' => 'view-kelas'`

## 📝 Notes
- PDF generate menggunakan **barryvdh/laravel-dompdf v3.1.1**
- Logo size configurable via Settings Aplikasi
- Template menggunakan inline CSS (dompdf requirement)
- Page break menggunakan `page-break-after: always`

## 🐛 Troubleshooting

### Memory Exhausted
- Tingkatkan memory_limit di controller atau php.ini
- Kurangi jumlah kelas yang dicetak sekaligus
- Pastikan logo sudah di-resize

### Logo Tidak Tampil
- Pastikan logo sudah diupload di Settings
- Check path file di storage
- Logo akan di-skip jika error (tidak menghentikan proses)

### PDF Layout Rusak
- Check setting logo size di Settings > Pengaturan Ukuran Logo
- Adjust logo_column_width (8-20%)
- Adjust logo_display_height (30-100px)

## 📦 Dependencies
- Laravel 11
- AdminLTE 3
- barryvdh/laravel-dompdf
- GD Library (PHP extension)
- jQuery (untuk AJAX)
- SweetAlert2 (untuk alerts)

## ✅ Testing Checklist
- [ ] Filter dengan Tahun Pelajaran saja
- [ ] Filter dengan Tahun Pelajaran + Tingkat
- [ ] Filter dengan semua parameter
- [ ] Pilih 1 kelas
- [ ] Pilih semua kelas (>5 kelas)
- [ ] Test dengan kelas tanpa siswa
- [ ] Test dengan kelas tanpa wali kelas
- [ ] Test tanpa logo
- [ ] Test dengan 1 logo saja
- [ ] Test dengan kedua logo
- [ ] Verify page break antar kelas
- [ ] Verify memory tidak exhausted dengan 20+ kelas

## 📅 Version History
- **v1.0** (2025-01-XX): Initial batch print feature
  - Batch absensi kelas
  - Filter by tahun pelajaran, tingkat, jurusan, kurikulum
  - Configurable logo size dari database
  - Legal portrait paper size
