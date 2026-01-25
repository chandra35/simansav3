# FEATURE: Fitur Baru SIMANSA v3

Dokumentasi ini menjelaskan fitur-fitur baru yang ditambahkan ke sistem SIMANSA v3 untuk mendukung pengelolaan pendidikan madrasah yang lebih komprehensif.

## Daftar Fitur Baru

### 1. 📢 Pengumuman
Sistem pengumuman untuk menyebarkan informasi ke seluruh civitas akademika.

**Fitur:**
- Kategori pengumuman: Umum, Akademik, Kegiatan, Pengumuman, Penting
- Prioritas: Normal, Tinggi, Urgent
- Target: Semua, Siswa, Guru, Wali Murid
- Pin pengumuman penting
- Lampiran file
- Tanggal aktif (mulai & selesai)

**Routes:**
- `admin/pengumuman` - Index
- `admin/pengumuman/create` - Tambah
- `admin/pengumuman/{id}` - Detail
- `admin/pengumuman/{id}/edit` - Edit

---

### 2. 📅 Kalender Akademik
Kalender kegiatan akademik dengan integrasi FullCalendar.

**Fitur:**
- Kategori: Akademik, Libur, Kegiatan, Ujian, Rapat, Lainnya
- Warna berbeda per kategori
- Drag & drop event
- Filter per tahun pelajaran
- Recurring events (harian, mingguan, bulanan, tahunan)
- Tandai hari libur

**Routes:**
- `admin/kalender-akademik` - View kalender
- `admin/kalender-akademik/events` - API get events

---

### 3. 🏆 Prestasi Siswa
Pencatatan prestasi dan penghargaan siswa.

**Fitur:**
- Jenis: Akademik, Non-Akademik, Olahraga, Seni, Keagamaan, Lainnya
- Tingkat: Sekolah s/d Internasional
- Peringkat: Juara 1-3, Harapan 1-3, Peserta
- Upload sertifikat & foto
- Verifikasi prestasi
- Pencatatan pembina

**Routes:**
- `admin/prestasi-siswa` - Index
- `admin/prestasi-siswa/create` - Tambah
- `admin/prestasi-siswa/{id}` - Detail
- `admin/prestasi-siswa/{id}/verify` - Verifikasi

---

### 4. ⚽ Ekstrakurikuler
Pengelolaan kegiatan ekstrakurikuler madrasah.

**Fitur:**
- Data ekskul dengan pembina
- Jadwal kegiatan (hari & waktu)
- Kuota maksimal anggota
- Biaya ekskul
- Penanda wajib/pilihan
- Manajemen anggota:
  - Status: Aktif, Tidak Aktif, Keluar
  - Jabatan anggota
  - Penilaian & predikat (A-E)

**Routes:**
- `admin/ekstrakurikuler` - Index
- `admin/ekstrakurikuler/{id}/anggota` - Kelola anggota

---

### 5. 🕐 Jadwal Pelajaran
Pengelolaan jadwal pelajaran per kelas.

**Fitur:**
- Jadwal per hari & jam ke
- Waktu mulai & selesai
- Ruangan
- Filter: Tahun pelajaran, Kelas, Hari
- Deteksi bentrokan jadwal guru
- View timetable per kelas
- Semester 1 & 2

**Routes:**
- `admin/jadwal-pelajaran` - Index (tabel)
- `admin/jadwal-pelajaran/timetable` - View roster
- `admin/jadwal-pelajaran/create` - Tambah

---

### 6. 📝 Catatan Konseling (BK)
Pencatatan layanan bimbingan konseling.

**Fitur:**
- Jenis konseling: Individual, Kelompok, Klasikal
- Kategori masalah: Akademik, Pribadi, Sosial, Karir, Keluarga, Lainnya
- Status: Dalam Proses, Selesai, Perlu Tindak Lanjut
- Jadwal tindak lanjut
- Penanda catatan rahasia
- Laporan per siswa

**Routes:**
- `admin/catatan-konseling` - Index
- `admin/catatan-konseling/create` - Tambah
- `admin/catatan-konseling-report/siswa` - Laporan per siswa

---

### 7. 💰 Pembayaran (SPP)
Sistem pembayaran dan tagihan siswa.

**Komponen:**

#### Jenis Pembayaran
- Kategori: SPP, Daftar Ulang, Seragam, Kegiatan, Lainnya
- Nominal default
- Penanda wajib/bulanan

#### Tagihan
- Generate tagihan massal per kelas
- Periode bulan & tahun
- Tanggal jatuh tempo
- Status: Belum Bayar, Cicilan, Lunas
- Tracking sisa tagihan

#### Pembayaran
- Nomor transaksi otomatis
- Metode: Tunai, Transfer, QRIS, Virtual Account
- Upload bukti pembayaran
- Verifikasi admin
- Status: Pending, Verified, Rejected

#### Laporan
- Total tagihan vs terbayar
- Count per status
- Filter tahun pelajaran & jenis

**Routes:**
- `admin/pembayaran/jenis` - Jenis pembayaran
- `admin/pembayaran/tagihan` - Tagihan
- `admin/pembayaran` - Pembayaran
- `admin/pembayaran/laporan` - Laporan

---

### 8. 📄 Surat Keterangan
Pembuatan surat keterangan otomatis.

**Komponen:**

#### Template Surat
- Kategori: Keterangan Aktif, Lulus, Pindah, Berkelakuan Baik, Rekomendasi, Lainnya
- Template content dengan variabel
- Variabel otomatis: nama, NISN, kelas, alamat, dll

#### Surat Keterangan
- Generate nomor surat otomatis (format: 001/SK/MA/I/2024)
- Workflow: Draft → Pending → Approved → Printed
- Preview sebelum cetak
- Cetak ke PDF

**Routes:**
- `admin/surat-keterangan/template` - Template
- `admin/surat-keterangan` - Surat
- `admin/surat-keterangan/{id}/print` - Cetak PDF

---

## Struktur Menu

```
📊 Dashboard
📁 Manajemen Data
  ├── Data Siswa
  ├── Sekolah Asal
  └── Data GTK
📚 Akademik
  ├── Tahun Pelajaran
  ├── Kurikulum
  ├── Mata Pelajaran
  ├── Manajemen Kelas
  ├── Jadwal Pelajaran ⭐ NEW
  ├── Kalender Akademik ⭐ NEW
  ├── Cetak Dokumen
  └── Mutasi Siswa
👥 Kesiswaan ⭐ NEW
  ├── Prestasi Siswa
  ├── Ekstrakurikuler
  └── Catatan Konseling
💰 Keuangan ⭐ NEW
  ├── Jenis Pembayaran
  ├── Tagihan
  ├── Pembayaran
  └── Laporan Keuangan
📧 Layanan Surat ⭐ NEW
  ├── Template Surat
  └── Surat Keterangan
📢 Informasi ⭐ NEW
  └── Pengumuman
📊 Laporan & Monitoring
⚙️ Pengaturan
👤 User & Role
🔧 Tools
```

---

## Model & Database

### Tabel Baru
1. `pengumuman` - Pengumuman
2. `kalender_akademik` - Kalender akademik
3. `prestasi_siswa` - Prestasi siswa
4. `ekstrakurikuler` - Data ekskul
5. `ekstrakurikuler_anggota` - Anggota ekskul
6. `jadwal_pelajaran` - Jadwal pelajaran
7. `catatan_konseling` - Catatan BK
8. `jenis_pembayaran` - Jenis pembayaran
9. `tagihan` - Tagihan siswa
10. `pembayaran` - Transaksi pembayaran
11. `template_surat` - Template surat
12. `surat_keterangan` - Surat keterangan

### Model Baru
1. `Pengumuman`
2. `KalenderAkademik`
3. `PrestasiSiswa`
4. `Ekstrakurikuler`
5. `EkstrakurikulerAnggota`
6. `JadwalPelajaran`
7. `CatatanKonseling`
8. `JenisPembayaran`
9. `Tagihan`
10. `Pembayaran`
11. `TemplateSurat`
12. `SuratKeterangan`

---

## Controller Baru

1. `PengumumanController` - CRUD pengumuman
2. `KalenderAkademikController` - CRUD kalender + FullCalendar API
3. `PrestasiSiswaController` - CRUD + verifikasi prestasi
4. `EkstrakurikulerController` - CRUD ekskul + manajemen anggota
5. `JadwalPelajaranController` - CRUD jadwal + timetable view
6. `CatatanKonselingController` - CRUD + laporan per siswa
7. `PembayaranController` - Jenis, Tagihan, Pembayaran, Laporan
8. `SuratKeteranganController` - Template + Surat + Print PDF

---

## Dependencies

Untuk fitur cetak surat PDF, pastikan package berikut terinstall:

```bash
composer require barryvdh/laravel-dompdf
```

Untuk FullCalendar (kalender akademik), menggunakan CDN:
- https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js
- https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css

---

## Permissions

Fitur-fitur baru menggunakan permission `admin-access` yang sudah ada.
Untuk pengaturan permission lebih granular, dapat ditambahkan:

- `view-pengumuman`, `create-pengumuman`, `edit-pengumuman`, `delete-pengumuman`
- `view-kalender`, `create-kalender`, `edit-kalender`, `delete-kalender`
- `view-prestasi`, `create-prestasi`, `edit-prestasi`, `verify-prestasi`
- `view-ekskul`, `create-ekskul`, `edit-ekskul`, `manage-ekskul-anggota`
- `view-jadwal`, `create-jadwal`, `edit-jadwal`
- `view-konseling`, `create-konseling`, `edit-konseling`
- `view-pembayaran`, `create-pembayaran`, `verify-pembayaran`
- `view-surat`, `create-surat`, `approve-surat`, `print-surat`

---

## Tanggal Implementasi

**Ditambahkan:** 24 Januari 2026 (berdasarkan nama file migrasi)

---

## Catatan

1. Semua tabel menggunakan UUID sebagai primary key
2. Semua model menggunakan SoftDeletes
3. Foreign key ke tabel: siswa, tahun_pelajaran, gtk, kelas, mapel
4. View menggunakan AdminLTE dengan DataTables
