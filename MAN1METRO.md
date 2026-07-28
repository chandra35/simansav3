# Panduan Proyek MAN 1 Metro

Dokumen ini adalah titik awal konteks untuk sesi pengembangan baru. Baca dokumen ini sebelum mengubah SIMANSA atau PPDB.

Terakhir diperbarui: 28 Juli 2026, zona waktu Asia/Jakarta.

## Peta aplikasi

| Aplikasi | Direktori lokal Windows | Direktori produksi | Domain | Branch produksi |
|---|---|---|---|---|
| SIMANSA V3 | `D:\projek\simansav3` | `/www/wwwroot/simansa.man1metro.sch.id` | `https://simansa.man1metro.sch.id` | `master` |
| PPDB V3 | `D:\projek\ppdbv3` | `/home/manmetr1/ppdb.man1metro.sch.id` | `https://ppdb.man1metro.sch.id` | `main` |

Document root web PPDB adalah `/home/manmetr1/ppdb.man1metro.sch.id/public`.

## Stack teknis

### SIMANSA V3

- PHP `^8.2`; produksi saat dokumentasi dibuat menggunakan PHP 8.3.
- Laravel Framework `v11.46.1` dari `composer.lock`.
- AdminLTE `^3.15`.
- MySQL/MariaDB melalui koneksi Laravel `mysql`.
- Spatie Laravel Permission `^6.21`.
- Yajra DataTables `^11.1`.
- Maatwebsite Excel `^3.1`.
- Vite, Tailwind CSS, PostCSS, Axios.

Database:

| Lingkungan | Host | Port | Database | Username |
|---|---|---:|---|---|
| Lokal | `127.0.0.1` | 3306 | `simansav3` | `root` |
| Produksi | `localhost` | 3306 | `simansav3` | `simansa` |

### PPDB V3

- PHP `^8.2`.
- Laravel Framework `v12.52.0` dari `composer.lock`.
- AdminLTE `^3.15`.
- MySQL/MariaDB melalui koneksi Laravel `mysql`.
- Spatie Laravel Permission `^6.23`.
- Maatwebsite Excel `^3.1`.
- Vite, Tailwind CSS, Axios.

Database:

| Lingkungan | Host | Port | Database | Username |
|---|---|---:|---|---|
| Lokal | `127.0.0.1` | 3306 | `ppdbv3` | `root` |
| Produksi | hosting PPDB | 3306 | `manmetr1_ppdb` | `manmetr1_ppdb` |

PPDB juga dapat memakai koneksi integrasi SIMANSA dengan database produksi `manmetr1_simansa`. Nilai lengkap koneksi hanya boleh dibaca dari `.env` produksi.

## GitHub dan kredensial

Pemilik repository GitHub: `chandra35`.

| Aplikasi | Remote |
|---|---|
| SIMANSA V3 | `https://github.com/chandra35/simansav3` |
| PPDB V3 | `https://github.com/chandra35/ppdbv3.git` |

Panduan keamanan kredensial:

- Git lokal memakai autentikasi HTTPS yang sudah tersimpan di Git Credential Manager.
- Jangan menulis password GitHub atau personal access token ke dokumentasi maupun source code.
- Jangan commit `.env`, private key SSH, dump database, atau file kredensial.
- Jika autentikasi GitHub gagal, perbaiki Git Credential Manager atau jalankan login GitHub pada komputer pengguna.

## VM dan hosting produksi

### SIMANSA

- SSH alias: `simansa-vm`.
- Host: `172.16.250.7`.
- User: `root`.
- Metode autentikasi: SSH key, tanpa password.
- Private key lokal: `C:\Users\chandra35\.ssh\simansa_vm_ed25519`.
- Konfigurasi alias: `C:\Users\chandra35\.ssh\config`.
- Update script: `/www/wwwroot/simansa.man1metro.sch.id/update-simansa.sh`.

Private key hanya boleh digunakan dari komputer yang berwenang dan tidak boleh disalin ke repository.

### PPDB

- Hosting: `ppdb.man1metro.sch.id`.
- Endpoint yang terdokumentasi: `202.155.132.53`, port SSH `64000`.
- User: `manmetr1`.
- Alias yang digunakan panduan lama: `arzano`.
- Metode autentikasi: kredensial/SSH lokal yang sudah dikonfigurasi.
- Update script: `/home/manmetr1/ppdb.man1metro.sch.id/update-ppdb.sh`.

Password hosting, database, aplikasi, token, dan secret hanya disimpan di pengelola kredensial atau `.env` server. Jangan menyalinnya ke file Markdown.

## Aturan wajib setiap perubahan

Setiap perubahan kode atau dokumentasi wajib selesai sampai tahap berikut:

1. Periksa `git status -sb` dan diff.
2. Jangan gunakan `git add -A` bila ada file lokal yang tidak berkaitan.
3. Stage hanya file yang menjadi bagian perubahan.
4. Jalankan pemeriksaan sintaks, test relevan, dan build/compile bila diperlukan.
5. Commit dengan pesan singkat dan jelas.
6. Push ke branch produksi aplikasi:
   - SIMANSA: `origin/master`.
   - PPDB: `origin/main`.
7. Deploy aplikasi yang berubah.
8. Pastikan hash commit GitHub sama dengan hash di server.
9. Pastikan maintenance mode mati dan domain memberi respons normal.
10. Perbarui `perubahan-terakhir.md` agar sesi baru mengetahui pekerjaan terkini.

Perubahan dianggap belum selesai jika baru tersimpan lokal atau baru di-push tanpa deploy.

## Perintah operasional SIMANSA

```powershell
cd D:\projek\simansav3
git status -sb
php artisan test --testsuite=Unit
git add -- <file-yang-diubah>
git commit -m "pesan perubahan"
git push origin master
ssh simansa-vm "bash /www/wwwroot/simansa.man1metro.sch.id/update-simansa.sh"
```

Verifikasi:

```powershell
git fetch origin
git rev-parse origin/master
ssh simansa-vm "cd /www/wwwroot/simansa.man1metro.sch.id && git rev-parse HEAD && git status --porcelain && if [ -f storage/framework/down ]; then echo maintenance=on; else echo maintenance=off; fi"
```

## Perintah operasional PPDB

```powershell
cd D:\projek\ppdbv3
git status -sb
php artisan test
git add -- <file-yang-diubah>
git commit -m "pesan perubahan"
git push origin main
ssh manmetr1@arzano "bash /home/manmetr1/ppdb.man1metro.sch.id/update-ppdb.sh"
```

Jika alias `arzano` tidak tersedia, gunakan konfigurasi SSH resmi pengguna untuk endpoint produksi. Jangan menaruh password di command line atau dokumentasi.

## Catatan untuk sesi baru

- Kerjakan repository sesuai aplikasi yang disebut pengguna; jangan mencampur commit SIMANSA dan PPDB.
- Pertahankan perubahan lokal pengguna yang tidak terkait.
- Baca instruksi dan dokumentasi repository sebelum mengubah kode.
- Untuk SIMANSA, referensi internal tambahan tersedia di `docs/INTERNAL.md`.
- Untuk PPDB, referensi deployment tersedia di `DEPLOY_PRODUCTION.md` dan `update-ppdb.sh`.
- Setelah pekerjaan selesai, selalu commit, push, deploy, dan verifikasi.

perubahan terakhir baca di perubahan-terakhir.md.
