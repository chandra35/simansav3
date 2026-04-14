# Audit Akun GTK - 2026-04-14

## Ringkasan
- Audit dilakukan untuk menu, gate, route, dan permission akun GTK (guru/staff).
- Tujuan: memastikan akses sesuai fungsi role dan tidak bocor ke area admin yang tidak relevan.

## Temuan Utama

### 1. Boundary area admin terlalu longgar
- `AdminMiddleware` hanya memblok role `siswa`.
- Akun non-siswa (termasuk GTK) tetap bisa masuk namespace `/admin`.
- Risiko: akses URL langsung ke modul admin yang semestinya tidak dipakai GTK.
- Referensi: `app/Http/Middleware/AdminMiddleware.php`

### 2. Dashboard admin global belum dibatasi permission khusus
- Route dashboard admin ada di grup `auth + admin` tanpa middleware permission tambahan.
- Controller dashboard admin tidak memanggil `authorize` khusus.
- Risiko: akun GTK bisa membuka dashboard global bila tahu URL.
- Referensi: `routes/web.php`, `app/Http/Controllers/Admin/DashboardController.php`

### 3. Mismatch menu vs permission (contoh mapel)
- Role GTK default mendapat `view-mata-pelajaran`.
- Menu `Mata Pelajaran` memakai `can: view-kurikulum`.
- Dampak: permission ada, menu tidak tampil (membingungkan pengguna).
- Referensi: `database/seeders/PermissionSeeder.php`, `config/adminlte.php`

### 4. Presensi GTK berpotensi tidak konsisten
- Parent menu presensi tampil melalui gate `staff-presensi-menu`.
- Sebagian child menu ditentukan permission lain (`view-absensi`, `face-registration-admin`, dll).
- Dampak: menu bisa tampil parsial/terasa setengah aktif.
- Referensi: `config/adminlte.php`, `routes/web.php`

### 5. Gate `gtk-menu-only` belum mempertimbangkan semua role campuran
- Gate memblok `Siswa`, `Super Admin`, `Admin`.
- Belum eksplisit menangani kombinasi lain seperti GTK + Operator/Waka.
- Dampak: potensi tumpang tindih tampilan menu personal GTK.
- Referensi: `app/Providers/AuthServiceProvider.php`

## Snapshot Menu GTK Saat Ini (Default)
- Dashboard Saya (`admin.gtk.dashboard`)
- Profil Saya (`admin.gtk.profile`)
- Data Siswa (jika punya `view-siswa`)
- Sekolah Asal (jika punya `view-siswa`)
- Lulusan (jika punya `view-siswa`)
- Presensi tertentu (tergantung kombinasi gate + permission)

## Rekomendasi Perbaikan (Besok)

### Tahap 1 - Hardening akses (prioritas tinggi)
- Tambahkan permission guard eksplisit pada route dashboard admin global.
- Revisi `AdminMiddleware` agar bukan sekadar "bukan siswa", tapi sesuai role yang diizinkan.
- Audit route admin yang sensitif untuk memastikan semuanya dilindungi `permission` atau `can`.

### Tahap 2 - Konsistensi menu vs permission
- Selaraskan `can` pada menu dengan permission default role GTK.
- Rapikan modul akademik agar GTK-guru hanya melihat yang relevan.
- Pastikan parent-child menu presensi konsisten sehingga tidak muncul menu kosong/parsial.

### Tahap 3 - Model role kerja GTK
- Pisahkan paket capability:
  - GTK-Guru
  - GTK-Staff TU
- Gunakan `tugas_tambahan` sebagai eskalasi akses berbasis masa tugas.
- Definisikan matriks permission per persona (siapa boleh lihat/edit apa).

### Tahap 4 - Validasi akhir
- Uji akun contoh:
  - GTK murni
  - GTK + tugas tambahan
  - Operator
  - Admin
- Uji akses via menu dan akses via URL langsung.
- Dokumentasikan hasil dan checklist regression.

## Catatan Implementasi
- Fokus awal disarankan pada hardening route dan dashboard global.
- Perubahan menu sebaiknya dilakukan setelah matriks permission disepakati.
