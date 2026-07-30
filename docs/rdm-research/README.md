# Arsip Riset Enkripsi RDM dan Kaitannya dengan EMIS

Arsip ini dibuat pada 30 Juli 2026 agar hasil investigasi RDM yang sebelumnya
hanya tersimpan di `C:\RDMMadrasah` dan riwayat terminal tidak hilang.

## Isi arsip

- `vm-findings.md` — temuan teknis VM, format enkripsi, key/IV yang ditemukan,
  key yang gagal, test vector, dan solusi dekripsi.
- `rdm-tools-architecture.md` — arsitektur sinkronisasi serta endpoint cipher.
- `source/periksasiswa/dec.php` — salinan aman pola endpoint dekripsi RDM.
- `source/singkronerdm/decrypt-client.php` — implementasi klien batch yang
  sudah dinetralkan dari kredensial aktif.
- `source/probe/function-probe.php` — pola probe untuk menginventarisasi fungsi
  helper ionCube melalui runtime RDM.

- `sync-emis-analysis.md` — alur tombol Sync EMIS RDM, endpoint lokal,
  penyimpanan relasi EMIS, dan batas hasil investigasi.

## Temuan paling penting

RDM memiliki dua keluarga fungsi kriptografi yang berbeda:

1. `enkrip()` / `dekrip()`:
   - passphrase: `rdmM4drasahK3r3n`
   - IV: `m4dr4s4hb1s4d0n9`
   - format: `cipher_base64:iv_base64`
2. `mysql_encrypt()` / `mysql_decrypt()`:
   - dipakai untuk kolom database seperti `siswa_nama` dan `siswa_nisn`
   - output berupa Base64 tanpa pemisah titik dua
   - key tertanam dalam helper ionCube dan tidak berhasil diekstrak

Nilai `RDMMadrasah=madrasahHebat` pernah ditemukan dalam `rdm.ini`, tetapi
terbukti bukan key untuk enkripsi kolom database.

## Keamanan

Arsip ini sengaja tidak menyimpan:

- password database aktif;
- private SSH key;
- token endpoint aktif;
- bearer token EMIS.

Nilai tersebut harus diambil dari secret store atau konfigurasi environment.
Passphrase dan IV riset tetap dicatat karena diperlukan untuk membedakan
algoritma wrapper dengan algoritma database.

## Sumber asli

- `C:\RDMMadrasah\RDMTOOLS.md`
- `C:\RDMMadrasah\docs\vm-findings.md`
- `C:\RDMMadrasah\rdm_dec\`
- `C:\RDMMadrasah\htdocs\application\helpers\openssl_helper.php`
- Riwayat PowerShell sekitar baris 19065–19336
- VM RDM: `/www/wwwroot/rapor.man1metro.sch.id`
