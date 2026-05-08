# Audit Sistem Simansa — Alur Akhir Tahun Ajaran

**Tanggal Audit:** 08 Mei 2026  
**Versi Simansa:** Laravel 11.46.1, PHP 8.3.30  
**Penulis:** Audit otomatis via GitHub Copilot

---

## 1. Arsitektur Data Inti

### Tabel Kunci

| Tabel | Tujuan | Catatan |
|---|---|---|
| `tahun_pelajaran` | Definisi tahun ajaran | Flag `is_active`, `semester_aktif` (Ganjil/Genap), `status` (aktif/non-aktif/selesai) |
| `kelas` | Kelas per tahun ajaran | Terikat `tahun_pelajaran_id`, `tingkat` (10/11/12) |
| `siswa_kelas` | Pivot siswa ↔ kelas | **Tabel history utama** — `status`: aktif/naik_kelas/tinggal_kelas/lulus/keluar |
| `nilai_siswa` | Nilai per semester | Terikat `tahun_pelajaran_id`, `semester` (1–5) |
| `pengumuman_kelulusan` | Status lulus kelas XII | `status`: lulus/lulus_bersyarat/tidak_lulus, tracking kapan siswa buka |
| `mutasi_siswa` | Mutasi masuk/keluar | Lengkap dengan surat, verifikasi, sekolah asal/tujuan |
| `siswa_lulusan` | Jalur masuk PTN | SNBP/SNBT/SPAN-PTKIN/Poltekkes/Lainnya |
| `activity_logs` | Audit trail umum | `old_values`, `new_values`, `changed_fields` |

### Relasi Penting

```
TahunPelajaran
  └── HasMany Kelas (kelas per tahun)
        └── BelongsToMany Siswa via siswa_kelas (HISTORY TABLE)

Siswa
  ├── kelasHistory() → semua kelas yang pernah diikuti
  ├── kelasAktif()   → kelas aktif sekarang
  ├── nilaiSiswa()   → semua nilai per semester/tahun
  ├── dataLulusan()  → jalur PTN (SiswaLulusan)
  └── mutasiHistory() → riwayat mutasi
```

---

## 2. Alur Akhir Tahun Ajaran

### Urutan Langkah yang Benar

```
1. [Admin] Proses Kelulusan Kelas XII
   → Buat PengumumanKelulusan per siswa
   → Update siswa_kelas.status = 'lulus'
   → Update siswa.status_siswa = 'lulus'

2. [Admin] Proses Naik Kelas (X→XI, XI→XII)
   → Buat SiswaKelas baru di tahun pelajaran tujuan
   → Update SiswaKelas lama status = 'naik_kelas'
   → Update siswa.kelas_saat_ini_id ke kelas baru

3. [Admin] Arsipkan Tahun Lama
   → Set tahun_pelajaran.status = 'selesai'
   → Set tahun_pelajaran.is_active = false

4. [Admin] Aktifkan Tahun Baru
   → Set tahun_pelajaran.is_active = true (hanya satu boleh aktif)

5. [Admin] Buat Jadwal Pelajaran Baru
   → Manual via halaman Jadwal Pelajaran
   ⚠️ Belum ada fitur copy jadwal otomatis

6. [Admin] Import Siswa Kelas X Baru
   → Via PPDB import atau manual
   → Assign ke kelas X yang baru dibuat

7. [Admin] Assign Wali Kelas
   → Update wali_kelas_id per kelas di halaman Manajemen Kelas
```

### Apa yang Berubah Saat Tahun Ajaran Berganti?

| Data | Berubah? | Mekanisme |
|---|---|---|
| Profil siswa (nama, NISN, alamat) | **Tidak** | Tetap, tidak berubah |
| Kelas siswa | **Ya** | Buat record `siswa_kelas` baru di tahun baru |
| Status siswa kelas XII | **Ya** | `status_siswa = 'lulus'` |
| Nilai/rapor | **Tidak berubah** | Nilai lama tetap, nilai baru dibuat di semester/tahun baru |
| Jadwal pelajaran | **Ya** | Harus dibuat ulang per tahun/semester |
| Absensi | **Ya** | Terikat ke jadwal, otomatis ikut tahun baru |

---

## 3. Fitur Yang Sudah Ada

| Fitur | Status | Lokasi |
|---|---|---|
| CRUD Tahun Pelajaran | ✅ Ada | `TahunPelajaranController` |
| Set Aktif Tahun Pelajaran | ✅ Ada | `POST /tahun-pelajaran/{id}/set-active` |
| Ganti Semester | ✅ Ada | `POST /tahun-pelajaran/{id}/change-semester` |
| CRUD Kelas | ✅ Ada | `KelasController` |
| Assign Siswa ke Kelas | ✅ Ada | `KelasController@storeSiswa` |
| Assign Siswa via NISN bulk | ✅ Ada | `KelasController@storeSiswaNISN` |
| Pengumuman Kelulusan (per-siswa) | ✅ Ada | `PengumumanKelulusanController` |
| Laporan Lulusan & PTN | ✅ Ada | `LulusanController` |
| Import Siswa (Excel) | ✅ Ada | `SiswaImportController` |
| Import EMIS | ✅ Ada | `EmisImportController` |
| Nilai/Rapor | ✅ Ada | `NilaiController` |
| Jadwal Pelajaran | ✅ Ada | `JadwalPelajaranController` |
| Mutasi Siswa | ✅ Ada (model) | `MutasiSiswa` model ada, controller ada |

---

## 4. Gap Fitur yang Teridentifikasi

### 4.1 ✅ SUDAH DIIMPLEMENTASIKAN (rilis ini)

**Fitur: Proses Akhir Tahun (`/admin/kenaikan-kelas`)**

- **Batch Kelulusan Kelas XII** — Memproses semua siswa kelas XII sekaligus:
  - Buat record `PengumumanKelulusan` dengan status default yang bisa dipilih
  - Update `siswa_kelas.status = 'lulus'`  
  - Update `siswa.status_siswa = 'lulus'` (opsional)
  - Siswa yang sudah ada record pengumuman kelulusan dilewati otomatis

- **Batch Naik Kelas (X→XI, XI→XII)** — Pindahkan siswa antar tahun pelajaran:
  - UI mapping kelas: pilih kelas asal → kelas tujuan secara visual
  - Preview jumlah siswa aktif sebelum proses
  - Buat `SiswaKelas` baru di tahun pelajaran tujuan
  - Update record lama: `status = 'naik_kelas'`, `tanggal_keluar` diisi
  - Update `siswa.kelas_saat_ini_id`
  - Siswa yang sudah aktif di tahun tujuan dilewati otomatis
  - Validasi: kelas tujuan harus tingkat lebih tinggi dari kelas asal

**File yang dibuat/diubah:**
- `app/Http/Controllers/Admin/KenaikanKelasController.php` ← **BARU**
- `resources/views/admin/kenaikan-kelas/index.blade.php` ← **BARU**
- `routes/web.php` ← tambah 6 routes + import KenaikanKelasController
- `config/adminlte.php` ← tambah menu "Proses Akhir Tahun"

### 4.2 ❌ BELUM DIIMPLEMENTASIKAN

| Gap | Prioritas | Catatan |
|---|---|---|
| **Copy Jadwal Pelajaran** dari tahun sebelumnya | Tinggi | Harus buat ulang manual tiap tahun |
| **Tinggal Kelas** (siswa tidak naik) | Sedang | Harus diproses manual per-siswa di KelasController |
| **Wizard buka tahun ajaran baru** end-to-end | Sedang | Langkah 3–7 di atas masih manual terpisah |
| **Validasi duplikat** siswa di 2 kelas aktif bersamaan | Sedang | Bisa terjadi jika proses tidak urut |
| **Batch mutasi masuk** siswa baru dari PPDB ke kelas X | Rendah | Saat ini via import manual |
| **Notifikasi/email siswa** saat naik kelas | Rendah | — |

---

## 5. History & Audit Trail

### Yang Sudah Ter-track

| Data | History Ada? | Cara Lihat |
|---|---|---|
| Kelas siswa per tahun | ✅ Ya | `siswa_kelas` dengan status & tanggal masuk/keluar |
| Nilai per semester | ✅ Ya | `nilai_siswa` dengan `tahun_pelajaran_id` + `semester` |
| Mutasi siswa | ✅ Ya | `mutasi_siswa` lengkap dengan dokumen & verifikasi |
| Kelulusan kelas XII | ✅ Ya | `pengumuman_kelulusan` + `siswa_lulusan` (jalur PTN) |
| Log aktivitas umum | ✅ Ya | `activity_logs` (Spatie Activity Log) |
| Login/online history | ✅ Ya | `user_sessions` |

### Cara Query History Kelas Siswa

```php
// Semua kelas yang pernah diikuti siswa (terurut dari terbaru)
$siswa->kelasHistory()->with('tahunPelajaran')->get();

// Contoh output:
// XII IPA 1 (2025/2026) — status: lulus
// XI IPA 1 (2024/2025) — status: naik_kelas
// X IPA 1  (2023/2024) — status: naik_kelas
```

---

## 6. Catatan Teknis Penting

### Spatie Permission & Gate::before
- Spatie mendaftarkan `Gate::before` yang jalan **sebelum** semua `Gate::define`
- Jika nama gate **sama** dengan Spatie permission di DB, Spatie langsung return `true` (bypass Gate::define)
- **Solusi**: Gunakan nama gate yang **tidak ada** di Spatie DB untuk sidebar visibility
  - Contoh: `sidebar-siswa-smartq` bukan `siswa-smartq-access`

### Konvensi Kode Kelas
Format: `{TINGKAT_ROMAWI}-{JURUSAN_CODE}-{NOMOR}-{TAHUN_MULAI}`  
Contoh: `X-IPA-1-2025`, `XI-IPS-2-2025`, `XII-UMUM-1-2025`

### Semester Encoding di `nilai_siswa`
| `semester` | Kelas | Periode |
|---|---|---|
| 1 | X | Ganjil |
| 2 | X | Genap |
| 3 | XI | Ganjil |
| 4 | XI | Genap |
| 5 | XII | Ganjil (hanya 1 semester) |

### Status `siswa_kelas`
| Status | Arti |
|---|---|
| `aktif` | Siswa sedang di kelas ini |
| `naik_kelas` | Dipromosikan ke tingkat lebih tinggi |
| `tinggal_kelas` | Tidak naik, tetap di tingkat sama tahun depan |
| `lulus` | Menyelesaikan kelas XII |
| `keluar` | Keluar dari sekolah (mutasi/DO) |
