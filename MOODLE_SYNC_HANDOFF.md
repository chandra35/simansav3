# Handoff Integrasi SIMANSA–Moodle

Dokumen ini dibuat untuk session Codex/agent berikutnya.

## Status saat ini

### Pengembangan preview dan progress (10 September 2026)

- Ditambahkan alur preview AJAX sebelum sinkronisasi, konfirmasi SweetAlert2, queue job `moodle-sync`, dan overlay progress live berbasis polling `MoodleSyncRun`.
- Progress menyimpan tahap aktif, jumlah item diproses, total item, serta ringkasan created/updated/failed.
- Preview saat ini menghitung rencana dari data SIMANSA; perbandingan created/updated/unchanged terhadap Moodle baru dapat diverifikasi setelah token dan service Web Service Moodle aktif.
- Migration progress sudah ditambahkan. Worker produksi perlu memproses queue `moodle-sync` selain queue `lms-webhooks`.
- Service `SIMANSA User Sync` sudah dibuat di Moodle dengan 11 fungsi Web Service yang diperlukan.
- Token permanen sudah dibuat, disimpan terenkripsi pada konfigurasi SIMANSA, dan berhasil diuji melalui `core_webservice_get_site_info`.

### Perbaikan sinkronisasi kohor (10 September 2026)

- Moodle 4.5 mengharuskan parameter `categorytype` pada pembuatan/pembaruan kohor; payload lama memakai `contextid` dan menyebabkan 73 kohor gagal dengan `Invalid parameter value detected`.
- Payload sudah diperbaiki ke `categorytype=system` pada commit `099aaeee`.
- Queue worker khusus `moodle-sync` sudah diaktifkan di production. Run lama tidak diulang otomatis karena sinkronisasi kohor adalah operasi tulis.

Modul integrasi Moodle sudah dibuat di repository `simansav3` dan sudah dideploy ke VM SIMANSA.

- URL produksi: `https://simansa.man1metro.sch.id/admin/moodle-sync`
- Commit produksi terakhir: `3a406efae20c6d28294ec5a78dfdfbf6346614fd`
- VM SIMANSA: alias SSH `simansa-vm`
- Path aplikasi produksi: `/www/wwwroot/simansa.man1metro.sch.id`
- Maintenance mode: mati
- Migration integrasi Moodle: sudah berhasil dijalankan

## Temuan Moodle

- VM Moodle: `172.16.253.2`
- Hostname: `lms-server`
- SSH: berhasil sebagai `root`
- Moodle: `4.5.10+ (Build: 20260306)`
- `enablewebservices`: aktif
- Protokol REST: aktif
- Tabel token REST Moodle: saat pengecekan belum memiliki token aktif
- Endpoint REST tanpa token merespons `invalidtoken`
- Service `PPDB Integration` tersedia tetapi belum berisi fungsi Web Service user
- Endpoint custom SMART-Q/PPDB tetap berjalan dan tidak boleh dianggap sebagai API sinkronisasi master SIMANSA

Jangan menyimpan token atau password di dokumen, source code, commit, atau output log.

## Fitur modul SIMANSA

File utama:

- `app/Http/Controllers/Admin/MoodleSyncController.php`
- `app/Services/MoodleSyncService.php`
- `app/Models/MoodleIntegration.php`
- `app/Models/MoodleSyncRun.php`
- `app/Models/MoodleSyncItem.php`
- `resources/views/admin/moodle-sync/index.blade.php`
- `database/migrations/2026_09_10_100000_create_moodle_sync_tables.php`

Menu: **Manajemen Data → Integrasi Moodle**.

Fitur:

- Konfigurasi URL Moodle dan token Web Service
- Penyimpanan token terenkripsi
- Tes koneksi `core_webservice_get_site_info`
- Preview jumlah siswa, GTK, kohor, dan kategori
- Sinkronisasi user siswa dan GTK
- Username siswa = NISN
- Username GTK = NIK
- Password awal akun baru = NISN/NIK
- Pembuatan/pembaruan kohor berdasarkan rombel aktif SIMANSA
- Pengisian anggota kohor siswa
- Pembuatan/pembaruan kategori kursus berdasarkan tahun pelajaran dan rombel
- Riwayat run dan item sinkronisasi dengan status created/updated/failed
- Tidak menghapus akun, kohor, kategori, atau kursus Moodle otomatis

## Permission

- `view-moodle-sync`
- `manage-moodle-sync`

Migration memberi akses awal kepada role `Super Admin`, `Admin`, dan `Operator` bila role tersebut sudah tersedia.

## Yang belum dilakukan

1. Buat service Moodle khusus `SIMANSA User Sync`.
2. Aktifkan fungsi Web Service minimal:
   - `core_webservice_get_site_info`
   - `core_user_get_users_by_field`
   - `core_user_create_users`
   - `core_user_update_users`
   - `core_cohort_get_cohorts`
   - `core_cohort_create_cohorts`
   - `core_cohort_update_cohorts`
   - `core_cohort_add_cohort_members`
   - `core_course_get_categories`
   - `core_course_create_categories`
   - `core_course_update_categories`
3. ~~Buat token khusus untuk service tersebut.~~ Selesai.
4. ~~Masukkan URL dan token melalui halaman Integrasi Moodle.~~ Selesai; token tersimpan terenkripsi.
5. ~~Jalankan tes koneksi.~~ Selesai; Moodle mengembalikan nama situs E-Learning MAN 1 Metro.
6. Jalankan sinkronisasi user dalam skala terbatas/preview terlebih dahulu.
7. Verifikasi hasil akun, kohor, dan kategori sebelum memakai sinkronisasi penuh.

## Catatan teknis penting

- SIMANSA adalah sumber data utama; Moodle adalah target sinkronisasi.
- Jangan menjalankan sinkronisasi penuh sebelum token dan fungsi Moodle diverifikasi.
- Password tidak boleh disimpan ke tabel `moodle_sync_items`; kode saat ini menghapus password dari payload audit.
- Jika ingin mengaktifkan sinkronisasi otomatis, tambahkan scheduler setelah pengujian manual stabil.
- Sinkronisasi kategori saat ini hanya mengelola kategori, bukan membuat isi kursus, kuis, materi, atau nilai.
- Sinkronisasi role guru dan enrolment kursus dapat ditambahkan setelah kontrak kategori/kursus Moodle disepakati.

## Verifikasi cepat

```powershell
cd D:\projek\simansav3
php artisan view:cache
php artisan route:list --name=admin.moodle-sync
ssh simansa-vm "cd /www/wwwroot/simansa.man1metro.sch.id && php artisan migrate:status && git rev-parse HEAD"
```
