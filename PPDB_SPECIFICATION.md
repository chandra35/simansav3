# 📋 PPDB Application - Specification Document

> Dokumen spesifikasi lengkap aplikasi PPDB terintegrasi dengan SIMANSA v3

---

## 📌 Overview

Aplikasi PPDB adalah sistem penerimaan siswa baru yang terintegrasi dengan database SIMANSA. Sistem ini dirancang untuk:
- ✅ Validasi NISN dari Kemendikbud
- ✅ Verifikasi jenjang pendidikan (Grade 9 untuk MAN)
- ✅ Upload & verifikasi berkas
- ✅ Admin management lengkap seperti SIMANSA
- ✅ Workflow approval dengan GTK sebagai verifikator

---

## 🎨 UI/UX Flow

### **1. LANDING PAGE (Public)**

```
┌─────────────────────────────────────────────────────┐
│  PPDB MAN [NAMA SEKOLAH] - TAHUN 2025/2026         │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │                                             │   │
│  │     WELCOME IMAGE/BANNER                    │   │
│  │     (Hero Section dengan info umum PPDB)    │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  Periode Pendaftaran: 1 - 15 Januari 2025          │
│  Kuota: 200 siswa                                  │
│  Jenjang: SMP/MTs Grade 9                          │
│                                                     │
│  ┌────────────────────────────────────────────┐    │
│  │   [LOGIN]  atau  [MENDAFTAR SEKARANG]      │    │
│  └────────────────────────────────────────────┘    │
│                                                     │
│  ╔════════════════════════════════════════════╗    │
│  ║ INFO BOX:                                 ║    │
│  ║ • Persyaratan                             ║    │
│  ║ • Jadwal Tes                              ║    │
│  ║ • Download Template Dokumen               ║    │
│  ╚════════════════════════════════════════════╝    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **2. LOGIN PAGE**

```
┌─────────────────────────────────────────────────────┐
│            LOGIN - PPDB MAN 2025/2026              │
├─────────────────────────────────────────────────────┤
│                                                     │
│         ┌───────────────────────────────┐          │
│         │  Form Login Pendaftar          │          │
│         ├───────────────────────────────┤          │
│         │                               │          │
│         │  Email/NISN:                  │          │
│         │  [_______________________]    │          │
│         │                               │          │
│         │  Password:                    │          │
│         │  [_______________________]    │          │
│         │                               │          │
│         │  [ ] Remember Me              │          │
│         │                               │          │
│         │  [LOGIN]                      │          │
│         │                               │          │
│         │  Lupa Password? [RESET]       │          │
│         │  Belum punya akun? [DAFTAR]   │          │
│         │                               │          │
│         └───────────────────────────────┘          │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **3. REGISTER PAGE - STEP 1: VALIDASI NISN**

```
┌─────────────────────────────────────────────────────┐
│     PENDAFTARAN PPDB - VALIDASI NISN               │
│     Step 1 dari 4                                  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Progress: [████░░░░░░░░░░░░░░░░] 25%              │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  LANGKAH 1: VALIDASI NISN                   │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  NISN*:                                     │   │
│  │  [0123456789            ] [VALIDASI]        │   │
│  │   (Format: 10 digit)                        │   │
│  │                                             │   │
│  │  ✅ NISN Valid!                             │   │
│  │  ────────────────────────────────────────   │   │
│  │  Nama:           Ahmad Ridho                │   │
│  │  Tempat/TTL:     Jakarta / 10 Mei 2009      │   │
│  │  Jenjang Saat:   Grade 9 SMP Negeri 1       │   │
│  │  Provinsi/Kab:   DKI Jakarta / Jakarta Pusat│   │
│  │                                             │   │
│  │  ✅ Jenjang VALID (Grade 9 SMP/MTs)         │   │
│  │     Memenuhi syarat untuk mendaftar MAN     │   │
│  │                                             │   │
│  │  Email untuk Login*:                        │   │
│  │  [email@example.com      ]                  │   │
│  │                                             │   │
│  │  Password*:                                 │   │
│  │  [___________________] [SHOW/HIDE]          │   │
│  │  (Minimal 8 karakter, 1 uppercase, 1 angka)│   │
│  │                                             │   │
│  │  Konfirmasi Password*:                      │   │
│  │  [___________________] [SHOW/HIDE]          │   │
│  │                                             │   │
│  │                                             │   │
│  │  [KEMBALI]  [LANJUT KE DATA PRIBADI →]      │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  Info Box:                                          │
│  ⓘ NISN akan divalidasi dengan database            │
│    Kemendikbud. Pastikan NISN benar.              │
│                                                     │
└─────────────────────────────────────────────────────┘

VALIDASI ERROR CONTOH:
❌ NISN Tidak Valid
❌ NISN Tidak Ditemukan
❌ Jenjang Tidak Memenuhi Syarat (Grade 8 - Terlalu Rendah)
❌ Sudah Mendaftar Sebelumnya
```

---

### **4. REGISTER PAGE - STEP 2: DATA PRIBADI**

```
┌─────────────────────────────────────────────────────┐
│     PENDAFTARAN PPDB - DATA PRIBADI                │
│     Step 2 dari 4                                  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Progress: [████████░░░░░░░░░░░░░] 50%              │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  LANGKAH 2: DATA PRIBADI                    │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Data dari NISN (Read-only):               │   │
│  │  ────────────────────────────────────────   │   │
│  │  Nama Lengkap:                              │   │
│  │  [Ahmad Ridho          ] (auto-fill)        │   │
│  │                                             │   │
│  │  Tempat Lahir:                              │   │
│  │  [Jakarta            ] (auto-fill)          │   │
│  │                                             │   │
│  │  Tanggal Lahir:                             │   │
│  │  [10 Mei 2009        ] (auto-fill)          │   │
│  │                                             │   │
│  │  Jenis Kelamin:                             │   │
│  │  (⦿) Laki-laki  (○) Perempuan (auto-fill)  │   │
│  │                                             │   │
│  │  Data Tambahan (Wajib Diisi):              │   │
│  │  ────────────────────────────────────────   │   │
│  │  Agama*:                                    │   │
│  │  [▼ Islam          ]                        │   │
│  │                                             │   │
│  │  No. HP Pribadi*:                           │   │
│  │  [08123456789      ]                        │   │
│  │                                             │   │
│  │  No. HP Orang Tua*:                         │   │
│  │  [08987654321      ]                        │   │
│  │                                             │   │
│  │  Alamat Rumah*:                             │   │
│  │  [Jl. Merdeka No. 123, RT.01 RW.02]         │   │
│  │                                             │   │
│  │  Kelurahan/Desa*:                           │   │
│  │  [Menteng        ]                          │   │
│  │                                             │   │
│  │  Kecamatan*:                                │   │
│  │  [Menteng        ]                          │   │
│  │                                             │   │
│  │  Kabupaten/Kota*:                           │   │
│  │  [Jakarta Pusat    ]                        │   │
│  │                                             │   │
│  │  Provinsi*:                                 │   │
│  │  [DKI Jakarta     ]                         │   │
│  │                                             │   │
│  │  Asal Sekolah*:                             │   │
│  │  [SMP Negeri 1 Jakarta  ]                   │   │
│  │                                             │   │
│  │  [KEMBALI]  [LANJUT KE UPLOAD DOKUMEN →]   │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **5. REGISTER PAGE - STEP 3: UPLOAD DOKUMEN**

```
┌─────────────────────────────────────────────────────┐
│     PENDAFTARAN PPDB - UPLOAD DOKUMEN              │
│     Step 3 dari 4                                  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Progress: [████████████░░░░░░░░] 75%               │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  LANGKAH 3: UPLOAD DOKUMEN                  │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Dokumen yang Diperlukan:                   │   │
│  │  ────────────────────────────────────────   │   │
│  │                                             │   │
│  │  1️⃣  Ijazah SMP/MTs*                         │   │
│  │     [Drag & Drop atau PILIH FILE]           │   │
│  │     Format: PDF, JPG, PNG (Max: 5MB)        │   │
│  │     ✅ scan_ijazah.pdf (125 KB)             │   │
│  │     [X] Hapus                               │   │
│  │                                             │   │
│  │  2️⃣  Akta Kelahiran*                        │   │
│  │     [Drag & Drop atau PILIH FILE]           │   │
│  │     Format: PDF, JPG, PNG (Max: 5MB)        │   │
│  │     ✅ akta_kelahiran.jpg (250 KB)          │   │
│  │     [X] Hapus                               │   │
│  │                                             │   │
│  │  3️⃣  Kartu Keluarga (KK)*                   │   │
│  │     [Drag & Drop atau PILIH FILE]           │   │
│  │     Format: PDF, JPG, PNG (Max: 5MB)        │   │
│  │     ✅ kartu_keluarga.jpg (350 KB)          │   │
│  │     [X] Hapus                               │   │
│  │                                             │   │
│  │  4️⃣  Foto 4x6 Berwarna*                     │   │
│  │     [Drag & Drop atau PILIH FILE]           │   │
│  │     Format: JPG, PNG (Max: 2MB)             │   │
│  │     Ukuran: 4x6 cm (300 dpi)                │   │
│  │     ✅ foto_4x6.jpg (180 KB)                │   │
│  │     [X] Hapus                               │   │
│  │                                             │   │
│  │  5️⃣  Piagam Prestasi (Opsional)             │   │
│  │     [Drag & Drop atau PILIH FILE]           │   │
│  │     Format: PDF, JPG, PNG (Max: 5MB)        │   │
│  │     ☐ Tidak ada / Tidak upload              │   │
│  │                                             │   │
│  │  📥 Progress Upload:                        │   │
│  │     Mengupload: scan_ijazah.pdf...          │   │
│  │     [████████░░░░░░░░░░░░░░░░] 40%          │   │
│  │                                             │   │
│  │  ✅ Semua dokumen berhasil diupload         │   │
│  │                                             │   │
│  │  [KEMBALI]  [LANJUT KE REVIEW →]            │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  Info Box:                                          │
│  ⓘ Pastikan semua dokumen jelas dan terbaca.      │
│    Dokumen tidak jelas akan diminta untuk diupload│
│    ulang oleh verifikator.                        │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **6. REGISTER PAGE - STEP 4: REVIEW & SUBMIT**

```
┌─────────────────────────────────────────────────────┐
│     PENDAFTARAN PPDB - REVIEW & SUBMIT             │
│     Step 4 dari 4                                  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Progress: [██████████████████░░] 95%               │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  LANGKAH 4: REVIEW & SUBMIT                 │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  ✓ Review Data Anda:                        │   │
│  │  ════════════════════════════════════════   │   │
│  │                                             │   │
│  │  📋 DATA PRIBADI                            │   │
│  │  ─────────────────────────────────────────  │   │
│  │  Nama:              Ahmad Ridho             │   │
│  │  NISN:              0123456789              │   │
│  │  TTL:               Jakarta, 10 Mei 2009    │   │
│  │  Alamat:            Jl. Merdeka No. 123     │   │
│  │  No. HP:            08123456789             │   │
│  │  [EDIT]                                     │   │
│  │                                             │   │
│  │  📎 DOKUMEN (4/4 Lengkap)                   │   │
│  │  ─────────────────────────────────────────  │   │
│  │  ✅ Ijazah SMP/MTs                          │   │
│  │  ✅ Akta Kelahiran                          │   │
│  │  ✅ Kartu Keluarga                          │   │
│  │  ✅ Foto 4x6 Berwarna                       │   │
│  │  [LIHAT DOKUMEN]                            │   │
│  │                                             │   │
│  │  📝 PERSETUJUAN                             │   │
│  │  ─────────────────────────────────────────  │   │
│  │  ☑ Saya menyatakan bahwa data dan dokumen   │   │
│  │    yang saya upload adalah benar dan dapat  │   │
│  │    dipertanggungjawabkan secara hukum.      │   │
│  │                                             │   │
│  │  ☑ Saya telah membaca dan menyetujui        │   │
│  │    kebijakan privasi dan tata tertib PPDB.  │   │
│  │                                             │   │
│  │  [KEMBALI]  [SUBMIT PENDAFTARAN]            │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ⓘ Setelah submit, Anda akan menerima email       │
│    konfirmasi dan nomor pendaftaran sementara.    │
│                                                     │
└─────────────────────────────────────────────────────┘

SETELAH SUBMIT:
┌─────────────────────────────────────────────────────┐
│  ✅ PENDAFTARAN BERHASIL!                           │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Terima kasih telah mendaftar di PPDB MAN 2025!   │
│                                                     │
│  📋 Data Pendaftaran Anda:                         │
│  ─────────────────────────────────────────────     │
│  Nomor Pendaftaran Sementara: PPDB-2025-000123   │
│  Nama:                        Ahmad Ridho         │
│  NISN:                        0123456789          │
│  Status:                      ⏳ Menunggu Review   │
│                                                     │
│  ✉️ Email konfirmasi telah dikirim ke:           │
│     email@example.com                             │
│                                                     │
│  📌 Langkah Berikutnya:                            │
│  • Tunggu verifikasi dokumen (max 3 hari kerja)  │
│  • Cek email untuk update status                  │
│  • Login dashboard untuk melihat progres          │
│                                                     │
│  [BACK TO HOME]                                   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **7. DASHBOARD PENDAFTAR (Calon Siswa)**

```
┌─────────────────────────────────────────────────────┐
│  DASHBOARD PPDB - AHMAD RIDHO                      │
│  [PROFILE] [LOGOUT]                               │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Welcome back, Ahmad Ridho! 👋                     │
│  Status Pendaftaran: ⏳ Pending Review             │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📊 STATUS PENDAFTARAN                      │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Nomor Pendaftaran:    PPDB-2025-000123     │   │
│  │  Status:               ⏳ Pending Review     │   │
│  │  Tanggal Daftar:       10 Januari 2025      │   │
│  │  Terakhir Update:      10 Januari 2025      │   │
│  │                                             │   │
│  │  Timeline:                                  │   │
│  │  ┌──────────┐      ┌──────────┐             │   │
│  │  │ Daftar   │ ──→ │ Review   │ ──→ [Approve]│   │
│  │  │ ✅       │     │ ⏳       │             │   │
│  │  └──────────┘     └──────────┘             │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📋 DATA PENDAFTARAN                        │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  NISN:              0123456789              │   │
│  │  Nama Lengkap:      Ahmad Ridho             │   │
│  │  TTL:               Jakarta, 10 Mei 2009    │   │
│  │  Alamat:            Jl. Merdeka No. 123     │   │
│  │  No. HP:            08123456789             │   │
│  │                                             │   │
│  │  [EDIT DATA]                                │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📎 DOKUMEN YANG DIUPLOAD                   │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  1. Ijazah SMP/MTs           [⏳ Review]     │   │
│  │  2. Akta Kelahiran           [⏳ Review]     │   │
│  │  3. Kartu Keluarga           [⏳ Review]     │   │
│  │  4. Foto 4x6 Berwarna        [⏳ Review]     │   │
│  │                                             │   │
│  │  [LIHAT DOKUMEN]  [UPLOAD ULANG]            │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📞 HUBUNGI KAMI                            │   │
│  ├─────────────────────────────────────────────┤   │
│  │  Email: ppdb@man.id                         │   │
│  │  Phone: (021) 1234-5678                     │   │
│  │  WhatsApp: 0812-3456-7890                   │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘

SETELAH VERIFIKASI APPROVED:
┌─────────────────────────────────────────────────────┐
│  Status: ✅ APPROVED - DOKUMEN LENGKAP             │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ✅ Dokumen Anda telah diverifikasi dan diterima.  │
│                                                     │
│  Nomor Pendaftaran Final: PPDB-2025-000123         │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  🎫 CETAK NOMOR PENDAFTARAN                 │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  [🖨️ CETAK PDF] [📧 EMAIL PDF] [💾 DOWNLOAD] │   │
│  │                                             │   │
│  │  Gunakan nomor pendaftaran untuk mengikuti  │   │
│  │  tes tulis pada tanggal yang dijadwalkan.   │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🔐 ADMIN DASHBOARD

### **8. Admin Settings - Konfigurasi PPDB**

```
┌─────────────────────────────────────────────────────┐
│  ADMIN PPDB - SETTINGS                             │
│  [DASHBOARD] [PENDAFTAR] [SETTINGS] [LOGOUT]       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  KONFIGURASI PPDB                                  │
│  ════════════════════════════════════════════      │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📋 INFORMASI PPDB                          │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Tahun Pelajaran*:                          │   │
│  │  [▼ 2025/2026        ]                      │   │
│  │                                             │   │
│  │  Jenjang Target*:                           │   │
│  │  [▼ MAN (Grade 9 SMP/MTs) ]                │   │
│  │                                             │   │
│  │  Kuota Penerimaan*:                         │   │
│  │  [200]                                      │   │
│  │                                             │   │
│  │  Tanggal Dibuka*:                           │   │
│  │  [01/01/2025]                               │   │
│  │                                             │   │
│  │  Tanggal Ditutup*:                          │   │
│  │  [15/01/2025]                               │   │
│  │                                             │   │
│  │  Status Pendaftaran*:                       │   │
│  │  (⦿) Buka  (○) Tutup                        │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📄 DOKUMEN YANG DIAKTIFKAN                 │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Jenis Dokumen:                             │   │
│  │  ☑ Ijazah SMP/MTs          [Required] [X]   │   │
│  │  ☑ Akta Kelahiran          [Required] [X]   │   │
│  │  ☑ Kartu Keluarga          [Required] [X]   │   │
│  │  ☑ Foto 4x6 Berwarna       [Required] [X]   │   │
│  │  ☑ Piagam Prestasi         [Optional] [X]   │   │
│  │  ☐ Surat Sehat             [Optional] [X]   │   │
│  │                                             │   │
│  │  [+ TAMBAH JENIS DOKUMEN]                   │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  🔍 VALIDASI NISN                           │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  Validasi dengan Kemendikbud:               │   │
│  │  (⦿) Aktif  (○) Nonaktif                    │   │
│  │                                             │   │
│  │  Grade/Jenjang Minimum yang Diizinkan:      │   │
│  │  [▼ Grade 9 SMP/MTs ]                       │   │
│  │                                             │   │
│  │  Izinkan grade lebih tinggi:                │   │
│  │  ☑ Ya  ☐ Tidak                              │   │
│  │                                             │   │
│  │  Cegah pendaftar ganda:                     │   │
│  │  ☑ Ya  ☐ Tidak                              │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  [BATAL]  [SIMPAN PENGATURAN]                      │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **9. Admin - Verifikator Management**

```
┌─────────────────────────────────────────────────────┐
│  ADMIN PPDB - KELOLA VERIFIKATOR                   │
│  [DASHBOARD] [PENDAFTAR] [VERIFIKATOR] [SETTINGS]  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  DAFTAR VERIFIKATOR                                │
│  ════════════════════════════════════════════      │
│                                                     │
│  Filter: [Search nama...] [Filter by Jenis Dokumen]│
│                                                     │
│  ┌──────────────────────────────────────────────┐   │
│  │ No│ Nama GTK         │ NIP    │ Dokumen    │ Aksi│
│  ├──────────────────────────────────────────────┤   │
│  │ 1 │ Sri Handini      │031234  │ Ijazah     │[📋] │
│  │   │ (Guru Bahasa)    │        │ Piagam     │[❌] │
│  │   │                  │        │ Surat Sehat│     │
│  ├──────────────────────────────────────────────┤   │
│  │ 2 │ Ahmad Wijaya     │031235  │ Foto       │[📋] │
│  │   │ (Guru Matematika)│        │ KK         │[❌] │
│  ├──────────────────────────────────────────────┤   │
│  │ 3 │ Siti Nurhaliza   │031236  │ Akta       │[📋] │
│  │   │ (Guru IPA)       │        │ Ijazah     │[❌] │
│  │   │                  │        │ KK         │     │
│  ├──────────────────────────────────────────────┤   │
│  └──────────────────────────────────────────────┘   │
│                                                     │
│  [+ TAMBAH VERIFIKATOR]                            │
│                                                     │
│  ┌──────────────────────────────────────────────┐   │
│  │  📝 TAMBAH VERIFIKATOR BARU                  │   │
│  ├──────────────────────────────────────────────┤   │
│  │                                              │   │
│  │  Pilih dari data GTK*:                       │   │
│  │  [Select2: Cari nama/NIP GTK...]             │   │
│  │  (Hanya GTK yang belum menjadi verifikator)  │   │
│  │                                              │   │
│  │  Tipe Dokumen yang Diverifikasi*:            │   │
│  │  ☑ Ijazah                                    │   │
│  │  ☑ Akta Kelahiran                            │   │
│  │  ☑ Kartu Keluarga                            │   │
│  │  ☑ Foto 4x6                                  │   │
│  │  ☐ Piagam Prestasi                           │   │
│  │                                              │   │
│  │  [BATAL] [TAMBAH]                            │   │
│  │                                              │   │
│  └──────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **10. Admin - Data Pendaftar**

```
┌─────────────────────────────────────────────────────┐
│  ADMIN PPDB - DATA PENDAFTAR                       │
│  [DASHBOARD] [PENDAFTAR] [SETTINGS] [LOGOUT]       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  DATA PENDAFTAR PPDB 2025/2026                     │
│  Total: 245 pendaftar                              │
│                                                     │
│  Filter:                                            │
│  [Search NISN/Nama] [Status▼] [Asal Sekolah▼]      │
│                                                     │
│  ┌──────────────────────────────────────────────┐   │
│  │ No│ NISN       │ Nama           │ Status   │ Aksi│
│  ├──────────────────────────────────────────────┤   │
│  │ 1 │ 0123456789 │ Ahmad Ridho    │ ⏳ Review│[👁️]│
│  │   │            │ SMP N 1 Jkt    │          │[✏️]│
│  │   │            │ 10 Jan 2025    │          │[❌]│
│  ├──────────────────────────────────────────────┤   │
│  │ 2 │ 0123456790 │ Siti Nurhaliza │ ✅ Approve│[👁️]│
│  │   │            │ SMP N 2 Jkt    │          │[✏️]│
│  │   │            │ 10 Jan 2025    │          │[❌]│
│  ├──────────────────────────────────────────────┤   │
│  │ 3 │ 0123456791 │ Budi Santoso   │ ❌ Reject │[👁️]│
│  │   │            │ SMP Swastaaa   │          │[✏️]│
│  │   │            │ 11 Jan 2025    │          │[❌]│
│  ├──────────────────────────────────────────────┤   │
│  └──────────────────────────────────────────────┘   │
│                                                     │
│  [Export Excel] [Import Data] [Bulk Action]         │
│                                                     │
│  Status Legend:                                     │
│  ⏳ Pending Review  ✅ Approved  ❌ Rejected        │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

### **11. Admin - Detail Pendaftar & Verifikasi Dokumen**

```
┌─────────────────────────────────────────────────────┐
│  DETAIL PENDAFTAR - AHMAD RIDHO                    │
│  [← KEMBALI]                                       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Status: ⏳ Pending Review                          │
│  Nomor Pendaftaran: PPDB-2025-000123               │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  👤 DATA PRIBADI                            │   │
│  ├─────────────────────────────────────────────┤   │
│  │  NISN:              0123456789              │   │
│  │  Nama Lengkap:      Ahmad Ridho             │   │
│  │  TTL:               Jakarta, 10 Mei 2009    │   │
│  │  Jenis Kelamin:     Laki-laki               │   │
│  │  Alamat:            Jl. Merdeka No. 123     │   │
│  │  No. HP Pribadi:    08123456789             │   │
│  │  No. HP Ortu:       08987654321             │   │
│  │  Asal Sekolah:      SMP Negeri 1 Jakarta    │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  📎 VERIFIKASI DOKUMEN                      │   │
│  ├─────────────────────────────────────────────┤   │
│  │                                             │   │
│  │  1. Ijazah SMP/MTs                          │   │
│  │     Status: ⏳ Menunggu Verifikasi          │   │
│  │     File: scan_ijazah.pdf (125 KB)          │   │
│  │     [👁️ LIHAT] [⬇️ DOWNLOAD]                 │   │
│  │     Verifikator: [Select2 - GTK...]         │   │
│  │     Catatan: [________________]             │   │
│  │     ☐ Setujui  ☐ Tolak dengan alasan       │   │
│  │        [Alasan tolak...]                    │   │
│  │                                             │   │
│  │  2. Akta Kelahiran                          │   │
│  │     Status: ⏳ Menunggu Verifikasi          │   │
│  │     File: akta_kelahiran.jpg (250 KB)       │   │
│  │     [👁️ LIHAT] [⬇️ DOWNLOAD]                 │   │
│  │     Verifikator: [Select2 - GTK...]         │   │
│  │     Catatan: [________________]             │   │
│  │     ☐ Setujui  ☐ Tolak dengan alasan       │   │
│  │        [Alasan tolak...]                    │   │
│  │                                             │   │
│  │  3. Kartu Keluarga                          │   │
│  │     Status: ⏳ Menunggu Verifikasi          │   │
│  │     File: kartu_keluarga.jpg (350 KB)       │   │
│  │     [👁️ LIHAT] [⬇️ DOWNLOAD]                 │   │
│  │     Verifikator: [Select2 - GTK...]         │   │
│  │     Catatan: [________________]             │   │
│  │     ☐ Setujui  ☐ Tolak dengan alasan       │   │
│  │        [Alasan tolak...]                    │   │
│  │                                             │   │
│  │  4. Foto 4x6 Berwarna                       │   │
│  │     Status: ⏳ Menunggu Verifikasi          │   │
│  │     File: foto_4x6.jpg (180 KB)             │   │
│  │     [👁️ LIHAT] [⬇️ DOWNLOAD]                 │   │
│  │     Verifikator: [Select2 - GTK...]         │   │
│  │     Catatan: [________________]             │   │
│  │     ☐ Setujui  ☐ Tolak dengan alasan       │   │
│  │        [Alasan tolak...]                    │   │
│  │                                             │   │
│  │  RINGKASAN VERIFIKASI:                      │   │
│  │  ├─ Ijazah:        ⏳                        │   │
│  │  ├─ Akta:          ⏳                        │   │
│  │  ├─ KK:            ⏳                        │   │
│  │  └─ Foto:          ⏳                        │   │
│  │                                             │   │
│  │  Status Keseluruhan: ⏳ Menunggu             │   │
│  │                                             │   │
│  │  [BATAL]  [SIMPAN VERIFIKASI]               │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘

SETELAH VERIFIKASI (Semua Dokumen Approved):
┌─────────────────────────────────────────────────────┐
│  Status: ✅ APPROVED                               │
│  Nomor Pendaftaran Final: PPDB-2025-000123         │
│  Tanggal Approval: 12 Januari 2025, 14:30          │
├─────────────────────────────────────────────────────┤
│                                                     │
│  RINGKASAN VERIFIKASI:                             │
│  ├─ Ijazah:        ✅ Disetujui oleh Sri Handini   │
│  ├─ Akta:          ✅ Disetujui oleh Siti Nurh...  │
│  ├─ KK:            ✅ Disetujui oleh Ahmad Wijaya  │
│  └─ Foto:          ✅ Disetujui oleh Sri Handini   │
│                                                     │
│  [✅ APPROVE ALL] [GENERATE NOMOR AKHIR]           │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 📊 Database Schema

### **Tabel: calon_siswa**
```sql
CREATE TABLE calon_siswa (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Validasi NISN
    nisn VARCHAR(10) NOT NULL UNIQUE,
    nisn_valid BOOLEAN DEFAULT FALSE,
    
    -- Data Pribadi
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('laki-laki', 'perempuan'),
    agama ENUM('islam', 'kristen', 'katolik', 'hindu', 'budha', 'konghucu'),
    
    -- Kontak
    no_hp_pribadi VARCHAR(15),
    no_hp_ortu VARCHAR(15),
    email VARCHAR(100) UNIQUE NOT NULL,
    
    -- Alamat
    alamat_rumah TEXT,
    kelurahan VARCHAR(100),
    kecamatan VARCHAR(100),
    kabupaten_kota VARCHAR(100),
    provinsi VARCHAR(100),
    
    -- Asal Sekolah
    asal_sekolah VARCHAR(150),
    
    -- Status
    status_verifikasi ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    status_admisi ENUM('pending', 'diterima', 'cadangan', 'ditolak') DEFAULT 'pending',
    
    -- Nilai
    nilai_tes DECIMAL(5,2),
    nilai_wawancara DECIMAL(5,2),
    rata_rata_nilai DECIMAL(5,2),
    ranking INT,
    
    -- Nomor Pendaftaran
    nomor_pendaftaran_sementara VARCHAR(50),
    nomor_pendaftaran_final VARCHAR(50) UNIQUE,
    
    -- Foreign Keys
    tahun_pelajaran_id UUID NOT NULL,
    user_id UUID NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tahun_pelajaran_id) REFERENCES tahun_pelajaran(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### **Tabel: calon_dokumen**
```sql
CREATE TABLE calon_dokumen (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    calon_siswa_id UUID NOT NULL,
    
    -- Tipe dokumen
    jenis_dokumen ENUM(
        'ijazah',
        'akta_kelahiran',
        'kartu_keluarga',
        'foto_4x6',
        'piagam_prestasi',
        'surat_sehat'
    ) NOT NULL,
    
    -- File
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    
    -- Verifikasi
    status_verifikasi ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verifikator_id UUID,
    catatan_verifikasi TEXT,
    tanggal_verifikasi TIMESTAMP,
    alasan_tolak TEXT,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (calon_siswa_id) REFERENCES calon_siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (verifikator_id) REFERENCES gtk(id)
);
```

### **Tabel: ppdb_settings**
```sql
CREATE TABLE ppdb_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    tahun_pelajaran_id UUID NOT NULL,
    
    -- Informasi PPDB
    jenjang_target VARCHAR(50), -- 'MAN', 'MA', 'SMA', dll
    kuota_penerimaan INT DEFAULT 200,
    tanggal_dibuka DATE NOT NULL,
    tanggal_ditutup DATE NOT NULL,
    status_pendaftaran BOOLEAN DEFAULT TRUE,
    
    -- Validasi NISN
    validasi_nisn_aktif BOOLEAN DEFAULT TRUE,
    grade_minimum VARCHAR(50), -- 'Grade 9 SMP/MTs'
    izinkan_grade_lebih_tinggi BOOLEAN DEFAULT FALSE,
    cegah_pendaftar_ganda BOOLEAN DEFAULT TRUE,
    
    -- Dokumen
    dokumen_aktif JSON, -- Array jenis dokumen yang aktif
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tahun_pelajaran_id) REFERENCES tahun_pelajaran(id)
);
```

### **Tabel: ppdb_verifikator**
```sql
CREATE TABLE ppdb_verifikator (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    gtk_id UUID NOT NULL,
    ppdb_settings_id UUID NOT NULL,
    
    -- Jenis dokumen yang diverifikasi
    jenis_dokumen_aktif JSON, -- Array jenis dokumen
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gtk_id) REFERENCES gtk(id),
    FOREIGN KEY (ppdb_settings_id) REFERENCES ppdb_settings(id) ON DELETE CASCADE,
    UNIQUE(gtk_id, ppdb_settings_id)
);
```

---

## 📋 Feature Checklist

### **Frontend - Calon Siswa**
- ✅ Landing Page PPDB
- ✅ Login Page
- ✅ Register Step 1: Validasi NISN
  - ✅ Input NISN (10 digit)
  - ✅ Validasi ke Kemendikbud API
  - ✅ Check Grade (harus Grade 9 SMP/MTs)
  - ✅ Show data dari NISN
  - ✅ Form input email & password
- ✅ Register Step 2: Data Pribadi
  - ✅ Auto-fill dari NISN
  - ✅ Form data tambahan
- ✅ Register Step 3: Upload Dokumen
  - ✅ Drag & drop upload
  - ✅ Multiple file upload
  - ✅ File validation (size, format)
- ✅ Register Step 4: Review & Submit
  - ✅ Review all data
  - ✅ Checkbox persetujuan
  - ✅ Submit registration
- ✅ Dashboard Calon
  - ✅ Status tracking
  - ✅ Document status
  - ✅ Cetak nomor pendaftaran (setelah approved)

### **Frontend - Admin**
- ✅ Settings PPDB
  - ✅ Tahun pelajaran
  - ✅ Jenjang target
  - ✅ Kuota penerimaan
  - ✅ Tanggal buka/tutup
  - ✅ Validasi NISN config
  - ✅ Dokumen config
- ✅ Verifikator Management
  - ✅ List verifikator (dari GTK)
  - ✅ Select2 GTK picker
  - ✅ Assign dokumen per verifikator
  - ✅ Add/remove verifikator
- ✅ Data Pendaftar
  - ✅ List semua pendaftar
  - ✅ Filter & search
  - ✅ Detail view
- ✅ Verifikasi Dokumen
  - ✅ View dokumen
  - ✅ Assign verifikator
  - ✅ Approve/Reject
  - ✅ Generate nomor pendaftaran

### **Backend - API**
- ✅ NISN Validation API
- ✅ Register endpoint
- ✅ Upload dokumen endpoint
- ✅ Dashboard calon endpoint
- ✅ Admin list pendaftar endpoint
- ✅ Admin verifikasi endpoint
- ✅ Generate nomor pendaftaran endpoint

---

## 🔄 Workflow Summary

```
CALON SISWA:
Validasi NISN → Input Data Pribadi → Upload Dokumen → Submit → 
[Status: Pending Review] → 
[Admin verifikasi] → 
[Status: Approved] → 
[Calon: Cetak Nomor Pendaftaran] ✅

ADMIN:
Settings PPDB → Manage Verifikator → 
Review Pendaftar → Assign Verifikator → 
Verifikator Review Dokumen → 
Approve Semua → 
Generate Nomor Akhir → 
[Calon bisa cetak] ✅

VERIFIKATOR:
Login → View Dokumen → Review → Approve/Reject ✅
```

---

Ini adalah gambaran lengkap aplikasi PPDB berdasarkan requirement Anda. Sudah siap untuk implementasi? 🚀

