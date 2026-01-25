# Fitur Menu SNBP (Eligibility Kelas 12)

## Deskripsi

Menu SNBP adalah fitur untuk mengelola eligibility siswa kelas 12 untuk program SNBP (Seleksi Nasional Berdasarkan Prestasi) atau program serupa. Admin dapat membuat menu dengan nama fleksibel (SNBP, SNMPTN, dll) dan menentukan siswa mana yang eligible atau tidak eligible.

## Fitur Utama

### 1. Admin: Manajemen Menu SNBP
- **Nama menu fleksibel**: Dapat dinamai sesuai kebutuhan (SNBP 2024, SNMPTN, dll)
- **Tahun Pelajaran**: Setiap menu terikat dengan satu tahun pelajaran
- **Periode Tampil**: Dapat mengatur tanggal/jam mulai dan berakhir tampilan konten
- **Konten Eligible**: Konten yang ditampilkan ke siswa yang eligible (rich text/HTML)
- **Konten Tidak Eligible**: Konten yang ditampilkan ke siswa yang tidak eligible (rich text/HTML)
- **Status Aktif**: Toggle untuk mengaktifkan/nonaktifkan menu
- **Readonly Mode**: Menu dari tahun pelajaran tidak aktif bersifat readonly

### 2. Admin: Assign Siswa Eligible
- Input via textarea dengan NISN (satu per baris)
- Dapat copy-paste dari Excel
- Validasi otomatis apakah NISN ada dan merupakan kelas 12
- Opsi untuk menghapus semua eligible sebelumnya atau menambahkan ke yang sudah ada
- Statistik real-time jumlah siswa eligible, tidak eligible, dan belum diassign

### 3. Admin: Assign Siswa Tidak Eligible
- Pilih dari daftar siswa kelas 12 yang belum diassign
- Filter berdasarkan kelas dan nama/NISN
- Checkbox untuk memilih multiple siswa
- Tombol "Pilih Semua" untuk kemudahan

### 4. Siswa: Melihat Status Eligibility
- **Menu SNBP hanya muncul untuk siswa kelas 12** (filter otomatis di sidebar)
- Siswa selain kelas 12 tidak akan melihat menu SNBP
- **Countdown Timer**: Menampilkan hitung mundur ke waktu mulai/berakhir
- Tampilan berbeda berdasarkan status:
  - **Eligible**: Tampilan dengan ikon sukses + konten eligible
  - **Tidak Eligible**: Tampilan dengan ikon peringatan + konten tidak eligible
  - **Belum Ditentukan**: Tampilan dengan info bahwa status belum ditentukan
  - **Belum Dimulai**: Jika belum mencapai tanggal mulai
  - **Telah Berakhir**: Jika sudah melewati tanggal berakhir

## Struktur Database

### Tabel: `snbp_menus`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | UUID | Primary Key |
| nama_menu | string | Nama menu (misal: "SNBP 2024") |
| tahun_pelajaran_id | UUID | Foreign key ke tahun_pelajaran |
| konten_eligible | text | Rich text/HTML untuk siswa eligible |
| konten_not_eligible | text | Rich text/HTML untuk siswa tidak eligible |
| is_active | boolean | Status aktif menu |
| tanggal_mulai | datetime | Waktu mulai tampil konten (nullable) |
| tanggal_berakhir | datetime | Waktu berakhir tampil konten (nullable) |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabel: `snbp_siswa`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | UUID | Primary Key |
| snbp_menu_id | UUID | Foreign key ke snbp_menus |
| siswa_id | UUID | Foreign key ke siswa |
| is_eligible | boolean | Status eligible (true/false) |
| created_at | timestamp | |
| updated_at | timestamp | |

## Routes

### Admin Routes
```
GET    /admin/snbp-menu                    -> index (Daftar menu)
GET    /admin/snbp-menu/create             -> create (Form tambah)
POST   /admin/snbp-menu                    -> store (Simpan menu baru)
GET    /admin/snbp-menu/{id}               -> show (Detail menu)
GET    /admin/snbp-menu/{id}/edit          -> edit (Form edit)
PUT    /admin/snbp-menu/{id}               -> update (Update menu)
DELETE /admin/snbp-menu/{id}               -> destroy (Hapus menu)
GET    /admin/snbp-menu/{id}/assign-eligible     -> assignEligible (Form assign eligible)
POST   /admin/snbp-menu/{id}/store-eligible      -> storeEligible (Simpan eligible)
GET    /admin/snbp-menu/{id}/assign-not-eligible -> assignNotEligible (Form assign tidak eligible)
POST   /admin/snbp-menu/{id}/store-not-eligible  -> storeNotEligible (Simpan tidak eligible)
DELETE /admin/snbp-menu/{id}/remove-assignment   -> removeAssignment (Hapus assignment)
```

### Siswa Routes
```
GET    /siswa/snbp                         -> index (Lihat status eligibility)
```

## Sidebar Menu

### Admin
```
Pengaturan
├── Pengaturan Aplikasi
├── Custom Menu Siswa
├── Menu SNBP        <-- Baru
└── Profile
```

### Siswa
```
SNBP (hanya muncul untuk kelas 12, disembunyikan otomatis untuk kelas lain)
```

## File yang Dibuat

### Models
- `app/Models/SnbpMenu.php`
- `app/Models/SnbpSiswa.php`

### Controllers
- `app/Http/Controllers/Admin/SnbpMenuController.php`
- `app/Http/Controllers/Siswa/SnbpController.php`

### Views - Admin
- `resources/views/admin/snbp-menu/index.blade.php`
- `resources/views/admin/snbp-menu/create.blade.php`
- `resources/views/admin/snbp-menu/edit.blade.php`
- `resources/views/admin/snbp-menu/show.blade.php`
- `resources/views/admin/snbp-menu/assign-eligible.blade.php`
- `resources/views/admin/snbp-menu/assign-not-eligible.blade.php`

### Views - Siswa
- `resources/views/siswa/snbp/index.blade.php`
- `resources/views/siswa/snbp/not-applicable.blade.php`

### Menu Filter
- `app/Menu/Filters/SnbpMenuFilter.php` - Filter untuk menyembunyikan menu SNBP bagi siswa non-kelas 12

### Migrations
- `database/migrations/2026_01_25_011429_create_snbp_menus_table.php`
- `database/migrations/2026_01_25_120000_add_periode_to_snbp_menus_table.php`

## Permission

Fitur ini menggunakan permission `manage-settings` yang sudah ada. Hanya user dengan permission ini yang dapat mengakses menu SNBP di admin panel.

## Catatan Penting

1. **Readonly Mode**: Menu dari tahun pelajaran yang tidak aktif tidak dapat diedit, dihapus, atau diassign siswa baru. Ini untuk menjaga integritas data historis.

2. **Satu Menu per Tahun Pelajaran**: Hanya bisa membuat satu menu SNBP per tahun pelajaran.

3. **Validasi Kelas 12**: Sistem hanya mengijinkan assign siswa yang terdaftar di kelas dengan tingkat 12 pada tahun pelajaran yang sama dengan menu.

4. **NISN sebagai Identifier**: Untuk assign eligible, gunakan NISN karena lebih unik dan mudah didapat dari data siswa.

5. **Summernote Editor**: Konten eligible dan tidak eligible menggunakan Summernote untuk rich text editing, termasuk gambar, link, format teks, dll.

## Cara Penggunaan

### Admin: Membuat Menu SNBP Baru
1. Buka menu Pengaturan > Menu SNBP
2. Klik "Tambah Menu"
3. Isi nama menu (contoh: "SNBP 2024")
4. Pilih tahun pelajaran (hanya tahun aktif)
5. Tulis konten untuk siswa eligible
6. Tulis konten untuk siswa tidak eligible
7. Aktifkan toggle jika menu sudah siap ditampilkan
8. Klik Simpan

### Admin: Assign Siswa Eligible
1. Buka detail menu SNBP
2. Klik "Assign Siswa Eligible"
3. Paste daftar NISN (satu per baris)
4. Opsional: centang "Hapus semua eligible sebelumnya"
5. Klik Simpan

### Admin: Assign Siswa Tidak Eligible
1. Buka detail menu SNBP
2. Klik "Assign Siswa Tidak Eligible"
3. Gunakan filter untuk mencari siswa
4. Centang siswa yang tidak eligible
5. Klik Simpan

### Siswa: Melihat Status
1. Login sebagai siswa kelas 12
2. Klik menu "SNBP" di sidebar
3. Lihat status dan informasi yang ditampilkan
