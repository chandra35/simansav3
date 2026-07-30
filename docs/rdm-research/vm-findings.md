# RDM Madrasah — Catatan Teknis dan Temuan Enkripsi

## Infrastruktur

| Item | Nilai |
|---|---|
| VM RDM | `172.16.251.2` |
| Akses | SSH key-based, tanpa password interaktif |
| Stack | aaPanel, PHP + ionCube Loader, MariaDB |
| Aplikasi | `/www/wwwroot/rapor.man1metro.sch.id/` |
| Domain | `rapor.man1metro.sch.id` |
| Database | `man1metrosch_rdm` |
| Konfigurasi DB | `/www/wwwroot/rapor.man1metro.sch.id/config.php` |

Password, token aktif, dan private key tidak disalin ke arsip ini.

## Kondisi source RDM

RDM berbasis CodeIgniter 3. Sebagian besar source PHP dalam `application/`
diproteksi ionCube dan domain-locked. Helper penting:

```text
application/helpers/openssl_helper.php
```

File tersebut hanya dapat menjalankan fungsi aslinya pada runtime yang memiliki
ionCube Loader dan konteks domain RDM yang benar.

## Kolom terenkripsi

Tabel `e_siswa`:

| Kolom | Kondisi |
|---|---|
| `siswa_nama` | terenkripsi |
| `siswa_nisn` | terenkripsi |
| `siswa_nis` | plaintext |

Kolom NIS plaintext kemudian dimanfaatkan sebagai pre-match agar jumlah
permintaan dekripsi nama dan NISN dapat dikurangi.

## Test vector terverifikasi

```text
siswa_nis        = 198614
siswa_nisn_enc   = Z/8/2MKfG/FaT1QrBfJykA==
siswa_nama_enc   = FKKuH3CLMv5qpqrzYJYIgd1QnkqDObQcrQ/oIHxl16I=

siswa_nisn_plain = 3033264643
siswa_nama_plain = AMANDA FATMAWATI
```

Contoh lintas instalasi RDM:

```text
rrtqgmpYpYaPOfXHyX4sNQ== -> ADELYA SEPTIANI
```

Temuan lintas instalasi menunjukkan format enkripsi RDM konsisten.

## Inventaris fungsi helper

Inventaris fungsi diperoleh melalui probe yang dijalankan pada runtime RDM:

```text
safe_b64encode($string)
safe_b64decode($string)
ssl_encrypt($key, $iv, $data)
ssl_decrypt($key, $data)
enkrip($string)
dekrip($string)
mysql_encrypt($data)
mysql_decrypt($data)
```

### Wrapper `enkrip` / `dekrip`

Temuan:

```text
passphrase = rdmM4drasahK3r3n
iv         = m4dr4s4hb1s4d0n9
format     = cipher_base64:iv_base64
```

Wrapper ini bukan fungsi yang digunakan untuk kolom `e_siswa.siswa_nama` dan
`e_siswa.siswa_nisn`.

### Fungsi database

Fungsi database:

```text
mysql_encrypt($data)
mysql_decrypt($data)
```

Karakteristik:

- menerima/menghasilkan string Base64 tanpa pemisah `:`;
- deterministik: plaintext yang sama menghasilkan ciphertext yang sama;
- key tertanam dalam helper ionCube;
- key tidak berhasil diekstrak atau direplikasi dengan PHP/OpenSSL biasa.

## Kandidat key yang diuji dan gagal

### `rdm.ini`

```text
RDMMadrasah=madrasahHebat
```

Nilai ini bukan key `mysql_encrypt()`/`mysql_decrypt()`.

### Passphrase wrapper

```text
rdmM4drasahK3r3n
SHA-256:
c4901b456766b581a38988aa5706a2fd543ecfa830962a0b9498ab41d9b627a2
```

Passphrase tersebut digunakan keluarga `enkrip()`/`dekrip()`, tetapi tidak
cocok untuk ciphertext database.

## Solusi dekripsi yang berhasil

Karena key database berada dalam ionCube, dekripsi dilakukan dengan memanggil
fungsi asli RDM dari endpoint helper pada VM:

```text
POST /periksasiswa/dec.php
Content-Type: application/json
Authorization: token yang disimpan di environment

Input : ["ciphertext-1", "ciphertext-2"]
Output: ["plaintext-1", "plaintext-2"]
```

Endpoint:

1. memuat `application/helpers/openssl_helper.php`;
2. memanggil `mysql_decrypt()` untuk setiap nilai;
3. menjaga urutan indeks input dan output;
4. mengembalikan input asli jika nilai kosong/tidak valid.

## Optimasi yang kemudian diterapkan

- Pre-match menggunakan `siswa_nis` plaintext.
- Dekripsi hanya field yang benar-benar diperlukan.
- Chunk 25 nilai per request.
- Maksimal 5 chunk berjalan paralel.
- Cache berdasarkan hash ciphertext.
- Cache disimpan permanen di file, tidak ikut terhapus `cache:clear`.
- Nilai hanya di-cache jika hasil berbeda dari ciphertext dan tidak kosong.
- Hasil gagal tidak boleh masuk cache.
- Timeout per request dibatasi dan proses dapat fallback tanpa crash.

## Hubungan dengan investigasi EMIS

Kasus EMIS memiliki arah berbeda:

```text
RDM  : ciphertext database -> decrypt -> plaintext
EMIS : ID numerik daftar -> encrypt -> token URL detail
```

Pasangan uji EMIS yang telah diketahui:

```text
ID numerik = 11271803
Token URL  = d0VhbFdkYWM2ZDBHNlFEVWdObWNDZz09
NISN       = 0085855689
local_nis  = 121218720001214844
```

Frontend EMIS menunjukkan pola:

1. AES-CBC;
2. padding PKCS7;
3. ciphertext CryptoJS dalam Base64;
4. string Base64 tersebut di-Base64-kan kembali untuk URL.

Konfigurasi runtime yang dicari:

```text
CRYPTO_KEY
Base64IVkeys
```

Fungsi enkripsi EMIS dianggap benar hanya jika ID `11271803` menghasilkan token
uji di atas secara identik.
