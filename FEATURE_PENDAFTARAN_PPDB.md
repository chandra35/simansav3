# FEATURE: Pendaftaran PPDB (Multi-Step Registration)

## Ringkasan

Fitur pendaftaran PPDB (Penerimaan Peserta Didik Baru) dengan sistem multi-step form yang memungkinkan calon siswa mendaftar secara online dengan proses yang terstruktur.

## Arsitektur Sistem

### 1. Flow Pendaftaran (5 Langkah)

```
┌─────────────────────────────────────────────────────────────────────┐
│                        FLOW PENDAFTARAN                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐      │
│  │ STEP 1   │───▶│ STEP 2   │───▶│ STEP 3   │───▶│ STEP 4   │      │
│  │ Validasi │    │  Data    │    │  Data    │    │  Upload  │      │
│  │  NISN    │    │ Pribadi  │    │ Orangtua │    │ Dokumen  │      │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘      │
│                                                        │            │
│                                                        ▼            │
│                                                  ┌──────────┐      │
│                                                  │ STEP 5   │      │
│                                                  │ Review & │      │
│                                                  │  Submit  │      │
│                                                  └──────────┘      │
│                                                        │            │
│                                                        ▼            │
│                                                  ┌──────────┐      │
│                                                  │ SUCCESS  │      │
│                                                  │  PAGE    │      │
│                                                  └──────────┘      │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 2. Status Pendaftaran

```
┌───────────┐     ┌───────────┐     ┌───────────┐     ┌───────────┐
│   DRAFT   │────▶│ SUBMITTED │────▶│ VERIFIED  │────▶│ ACCEPTED  │
│           │     │           │     │           │     │           │
└───────────┘     └───────────┘     └───────────┘     └───────────┘
                         │                 │
                         │                 │
                         ▼                 ▼
                  ┌───────────┐     ┌───────────┐
                  │ REJECTED  │     │ REJECTED  │
                  │           │     │           │
                  └───────────┘     └───────────┘
```

## Struktur Database

### Tables

1. **pendaftaran_ppdb** - Data utama pendaftaran
   - UUID primary key
   - Data pribadi (nisn, nama, tanggal_lahir, dll)
   - Data orangtua (nama_ayah, nama_ibu, pekerjaan, dll)
   - Status workflow (draft, submitted, verified, accepted, rejected)
   - Token untuk akses (lanjut pendaftaran / cek status)

2. **dokumen_pendaftaran** - Dokumen yang diunggah
   - UUID primary key
   - Foreign key ke pendaftaran_ppdb
   - jenis_dokumen, nama_file, path_file, mime_type, ukuran

3. **jurusan_ppdb** - Master data jurusan
   - UUID primary key
   - kode, nama, deskripsi, kuota, urutan
   - is_active untuk enable/disable

4. **pengaturan_ppdb** - Konfigurasi PPDB
   - UUID primary key
   - Periode (tanggal_buka, tanggal_tutup, tanggal_pengumuman)
   - Jalur tersedia (reguler, prestasi, afirmasi, zonasi)
   - Biaya dan informasi lainnya

## Routes

### Public Routes (ppdb/pendaftaran)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /ppdb/pendaftaran | ppdb.pendaftaran.index | Landing page pendaftaran |
| GET | /ppdb/pendaftaran/step1 | ppdb.pendaftaran.step1 | Form validasi NISN |
| POST | /ppdb/pendaftaran/step1 | ppdb.pendaftaran.process-step1 | Process NISN validation |
| GET | /ppdb/pendaftaran/step2 | ppdb.pendaftaran.step2 | Form data pribadi |
| POST | /ppdb/pendaftaran/step2 | ppdb.pendaftaran.process-step2 | Process data pribadi |
| GET | /ppdb/pendaftaran/step3 | ppdb.pendaftaran.step3 | Form data orangtua |
| POST | /ppdb/pendaftaran/step3 | ppdb.pendaftaran.process-step3 | Process data orangtua |
| GET | /ppdb/pendaftaran/step4 | ppdb.pendaftaran.step4 | Form upload dokumen |
| POST | /ppdb/pendaftaran/step4 | ppdb.pendaftaran.process-step4 | Process upload dokumen |
| GET | /ppdb/pendaftaran/step5 | ppdb.pendaftaran.step5 | Review dan submit |
| POST | /ppdb/pendaftaran/submit | ppdb.pendaftaran.submit | Final submission |
| GET | /ppdb/pendaftaran/success/{token} | ppdb.pendaftaran.success | Halaman sukses |
| GET | /ppdb/pendaftaran/cek-status | ppdb.pendaftaran.cek-status | Form cek status |
| POST | /ppdb/pendaftaran/cek-status | ppdb.pendaftaran.process-cek-status | Process cek status |

### Admin Routes (admin/ppdb)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /admin/ppdb/pendaftaran | admin.ppdb.pendaftaran.index | List pendaftar |
| GET | /admin/ppdb/pendaftaran/data | admin.ppdb.pendaftaran.data | DataTables endpoint |
| GET | /admin/ppdb/pendaftaran/{id} | admin.ppdb.pendaftaran.show | Detail pendaftar |
| POST | /admin/ppdb/pendaftaran/{id}/verify | admin.ppdb.pendaftaran.verify | Verifikasi |
| POST | /admin/ppdb/pendaftaran/{id}/accept | admin.ppdb.pendaftaran.accept | Terima |
| POST | /admin/ppdb/pendaftaran/{id}/reject | admin.ppdb.pendaftaran.reject | Tolak |
| DELETE | /admin/ppdb/pendaftaran/{id} | admin.ppdb.pendaftaran.destroy | Hapus |
| GET | /admin/ppdb/pendaftaran-export | admin.ppdb.pendaftaran.export | Export Excel |
| GET | /admin/ppdb/jurusan | admin.ppdb.jurusan.index | Kelola jurusan |
| GET | /admin/ppdb/jurusan/create | admin.ppdb.jurusan.create | Tambah jurusan |
| POST | /admin/ppdb/jurusan | admin.ppdb.jurusan.store | Simpan jurusan |
| GET | /admin/ppdb/jurusan/{id}/edit | admin.ppdb.jurusan.edit | Edit jurusan |
| PUT | /admin/ppdb/jurusan/{id} | admin.ppdb.jurusan.update | Update jurusan |
| DELETE | /admin/ppdb/jurusan/{id} | admin.ppdb.jurusan.destroy | Hapus jurusan |
| POST | /admin/ppdb/jurusan/{id}/toggle-status | admin.ppdb.jurusan.toggle-status | Toggle status |
| GET | /admin/ppdb/pengaturan | admin.ppdb.pengaturan.index | Pengaturan PPDB |
| PUT | /admin/ppdb/pengaturan | admin.ppdb.pengaturan.update | Update pengaturan |
| POST | /admin/ppdb/pengaturan/toggle | admin.ppdb.pengaturan.toggle | Toggle pendaftaran |

## File Struktur

### Models
```
app/Models/
├── PendaftaranPpdb.php     # Model utama pendaftaran
├── DokumenPendaftaran.php  # Model dokumen
├── JurusanPpdb.php         # Model jurusan
└── PengaturanPpdb.php      # Model pengaturan
```

### Controllers
```
app/Http/Controllers/
├── Ppdb/
│   └── PendaftaranController.php  # Controller publik
└── Admin/
    └── Ppdb/
        ├── PendaftaranController.php  # Admin kelola pendaftar
        ├── JurusanController.php       # Admin kelola jurusan
        └── PengaturanController.php    # Admin pengaturan PPDB
```

### Views
```
resources/views/
├── ppdb/
│   └── pendaftaran/
│       ├── index.blade.php           # Landing page
│       ├── closed.blade.php          # Pendaftaran ditutup
│       ├── step1-nisn.blade.php      # Form NISN
│       ├── step2-data-pribadi.blade.php   # Form data pribadi
│       ├── step3-data-orangtua.blade.php  # Form data orangtua
│       ├── step4-upload-dokumen.blade.php # Form upload
│       ├── step5-review.blade.php    # Review
│       ├── success.blade.php         # Sukses
│       ├── cek-status.blade.php      # Form cek status
│       └── status.blade.php          # Tampil status
└── admin/
    └── ppdb/
        ├── pendaftaran/
        │   ├── index.blade.php       # List pendaftar
        │   └── show.blade.php        # Detail pendaftar
        ├── jurusan/
        │   ├── index.blade.php       # List jurusan
        │   ├── create.blade.php      # Form tambah
        │   └── edit.blade.php        # Form edit
        └── pengaturan/
            └── index.blade.php       # Form pengaturan
```

## Fitur Utama

### 1. Multi-Step Form dengan Session
- Data pendaftaran disimpan di database (bukan session)
- Setiap step menyimpan progress
- Token unik untuk melanjutkan/mengakses pendaftaran
- Dapat dilanjutkan kapan saja

### 2. Validasi NISN
- Integrasi dengan API Kemendikbud (opsional)
- Cek duplikasi NISN per tahun pelajaran
- Auto-continue untuk draft yang sudah ada

### 3. Upload Dokumen
- Multiple file upload
- Validasi tipe file (PDF, gambar)
- Preview dokumen
- Delete dokumen yang sudah diunggah

### 4. Jalur Pendaftaran
- Reguler (umum)
- Prestasi (siswa berprestasi)
- Afirmasi (keluarga tidak mampu)
- Zonasi (berdasarkan jarak)

### 5. Admin Features
- DataTables dengan filter status/jalur/jurusan
- Verifikasi dan persetujuan pendaftaran
- Export data ke Excel
- Dashboard statistik

## Cara Penggunaan

### Setup Awal (Admin)

1. **Konfigurasi Pengaturan PPDB**
   - Akses menu: PPDB Management → Pengaturan PPDB
   - Set tahun pelajaran aktif
   - Set tanggal buka dan tutup pendaftaran
   - Pilih jalur yang tersedia
   - Isi informasi persyaratan dan alur pendaftaran
   - Klik "Buka Pendaftaran" untuk mengaktifkan

2. **Kelola Jurusan**
   - Akses menu: PPDB Management → Kelola Jurusan
   - Tambah jurusan yang tersedia
   - Set kuota masing-masing jurusan

### Proses Pendaftaran (Calon Siswa)

1. Akses halaman pendaftaran: `/ppdb/pendaftaran`
2. Klik "Mulai Pendaftaran"
3. Isi dan validasi NISN (Step 1)
4. Lengkapi data pribadi (Step 2)
5. Lengkapi data orangtua (Step 3)
6. Upload dokumen persyaratan (Step 4)
7. Review dan submit (Step 5)
8. Simpan nomor pendaftaran untuk cek status

### Verifikasi (Admin)

1. Akses menu: PPDB Management → Data Pendaftar
2. Klik pendaftar dengan status "Submitted"
3. Periksa data dan dokumen
4. Pilih aksi: Verifikasi / Tolak
5. Untuk yang sudah diverifikasi, dapat diterima/ditolak final

## Menu AdminLTE

```
📁 PPDB Management
├── 📊 Dashboard PPDB
├── 👥 Data Pendaftar
├── 🎓 Kelola Jurusan
├── ⚙️ Pengaturan PPDB
├── ── KONTEN WEBSITE ──
├── 🖼️ Kelola Slider
├── 📰 Kelola Berita
├── 📅 Jadwal PPDB
└── ⚙️ Pengaturan Site
```

## Testing

### URL Testing

1. **Public Pages:**
   - http://127.0.0.1:7000/ppdb/pendaftaran
   - http://127.0.0.1:7000/ppdb/pendaftaran/step1
   - http://127.0.0.1:7000/ppdb/pendaftaran/cek-status

2. **Admin Pages (login required):**
   - http://127.0.0.1:7000/admin/ppdb/pendaftaran
   - http://127.0.0.1:7000/admin/ppdb/jurusan
   - http://127.0.0.1:7000/admin/ppdb/pengaturan

## Notes

- Pendaftaran menggunakan token unik untuk keamanan
- Draft dapat dilanjutkan tanpa batas waktu
- Status dapat dicek menggunakan NISN + tanggal lahir
- Export Excel tersedia untuk admin
- Semua data menggunakan UUID untuk keamanan
