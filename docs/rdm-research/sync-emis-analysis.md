# Analisis Alur Sync EMIS pada RDM

Tanggal pemeriksaan: 30 Juli 2026.

Pemeriksaan dilakukan secara read-only pada frontend, access log, struktur
database, dan agregat data VM RDM. Proses Sync EMIS tidak dijalankan.

## Ringkasan

Browser tidak memanggil API EMIS secara langsung. Tombol **Sync Emis** memanggil
endpoint lokal RDM secara berurutan. Backend RDM yang diproteksi ionCube
kemudian menangani komunikasi keluar dan mengembalikan data per halaman.

Alur yang terlihat:

```text
Tombol Sync Emis
  -> verifikasi tahun ajaran EMIS
  -> ambil seluruh rombel EMIS per halaman
  -> ambil/sinkronkan siswa EMIS per halaman
     -> siswa RDM yang sudah cocok diperbarui otomatis
     -> siswa baru/bermasalah ditampung untuk dipetakan ke kelas
  -> simpan siswa terpilih ke kelas RDM
```

## Endpoint lokal yang digunakan frontend

Semua endpoint berikut dipanggil melalui service `ApiServer` RDM:

| Tahap | Endpoint | Payload utama |
|---|---|---|
| Memeriksa status awal | `proktor/checkEmis` | konteks sesi |
| Verifikasi tahun ajaran | `proktor/chekTaEmisNew` | `tingkat_id`, `kelas_id` |
| Mengambil rombel | `proktor/siswa/getRombelEmis` | `page`, `ta` |
| Sinkron siswa massal | `proktor/siswa/syncEmis/{lembaga_id_terenkripsi}` | `page`, `ta` |
| Simpan siswa baru | `proktor/siswa/simpanEmis` | `tingkat_id`, `kelas_id`, `siswa[]` |
| Sinkron rombel | `proktor/siswa/syncRombelEmis/{lembaga_id_terenkripsi}` | `page`, `ta` |
| Sinkron satu siswa dalam rombel | `proktor/siswa/syncdatasiswaemis/{kelas_id}` | objek siswa |
| Sinkron satu siswa RDM | `proktor/siswa/syncsiswaemis` | objek baris siswa |
| Pemeriksaan NISN manual | `proktor/siswa/cheknisn` | data siswa/NISN |

Nama endpoint ditulis sesuai source RDM, termasuk ejaan `chek` dan `Syncron`.

## Perilaku paging dan pencocokan

1. `chekTaEmisNew` menghasilkan ID tahun ajaran EMIS yang dipakai sebagai `ta`.
2. `getRombelEmis` dipanggil mulai halaman 1 dan mengikuti nilai `next`.
3. Setelah rombel selesai, `syncEmis` dipanggil mulai halaman 1 dan kembali
   mengikuti `next`.
4. Elemen pada `response.update` dihitung sebagai siswa lama yang berhasil
   diperbarui otomatis.
5. Elemen pada `response.data` dianggap membutuhkan tindakan atau merupakan
   siswa baru. Data ini ditampilkan dalam modal pemetaan kelas.
6. Saat disimpan, frontend memastikan `siswa_nis` tidak kosong, lalu mengirim
   kumpulan siswa ke `simpanEmis`.

Access log produksi mengonfirmasi urutan tersebut. Satu proses historis
memanggil `getRombelEmis` beberapa halaman, kemudian `syncEmis` puluhan halaman
secara berurutan dengan jeda beberapa detik. Ini bukan satu request besar.

## Data EMIS yang disimpan RDM

Tabel `e_siswa` mempunyai tiga field yang perlu dibedakan:

| Field | Kondisi pada 30 Juli 2026 | Makna |
|---|---:|---|
| `siswa_nis` | 1.734 terisi; seluruhnya tepat 6 digit | NIS yang digunakan RDM |
| `siswa_emis` | 2.726 terisi; seluruhnya panjang 66 | relasi/pengenal EMIS terenkripsi |
| `peserta_didik_id` | 2.515 terisi; seluruhnya panjang 119 | pengenal peserta didik tersandi/terbungkus |

Total baris `e_siswa` saat pemeriksaan adalah 2.792. Semua baris yang mempunyai
`peserta_didik_id` juga mempunyai `siswa_emis`.

Tidak ada `siswa_nis` 18 digit dan tidak ada nilai yang diawali NSM
`121218720001` maupun `131118720001`. Karena itu, `siswa_nis` RDM tidak boleh
langsung dianggap sebagai `local_nis` EMIS lengkap.

## Format pengenal lembaga dan siswa EMIS

Nilai `{lembaga_id_terenkripsi}` pada request lokal berformat wrapper RDM
`enkrip()`:

```text
outer Base64(cipher_base64:iv_base64)
```

Bagian IV yang telah diverifikasi menghasilkan:

```text
m4dr4s4hb1s4d0n9
```

Nilai `siswa_emis` juga selalu sepanjang 66 karakter dan secara struktural
serupa. Pemanggilan `mysql_decrypt()` tidak membukanya, sehingga field tersebut
bukan ciphertext keluarga enkripsi kolom database. Dugaan kuatnya,
`siswa_emis` adalah ID siswa EMIS yang dibungkus dengan `enkrip()`. Isi
plaintext-nya belum dinyatakan terverifikasi karena implementasi `dekrip()`
berada dalam helper ionCube.

## Batas yang belum dapat dibuktikan secara statis

Controller backend PHP diproteksi ionCube. Oleh karena itu, pemeriksaan source
statis belum dapat memastikan:

- URL API eksternal yang dipanggil backend;
- apakah backend langsung menghubungi `api-emis.kemenag.go.id` atau melalui
  layanan pusat RDM;
- sumber dan mekanisme refresh token eksternal;
- apakah backend mengambil detail tiap siswa atau menerima semua atribut dari
  endpoint daftar/proksi.

Yang sudah pasti: browser hanya berkomunikasi dengan endpoint lokal RDM.
`ApiPusat` ke layanan pusat RDM memang terdapat pada bundle frontend, tetapi
fungsi Sync EMIS di atas memakai `ApiServer`, bukan `ApiPusat`.

## Implikasi untuk SIMANSA

RDM membuktikan bahwa sinkronisasi dapat dimulai dari daftar paginated dan
pengenal siswa yang disimpan permanen. Namun RDM bukan sumber langsung untuk
`local_nis` EMIS 18 digit karena field NIS RDM hanya berisi 6 digit.

Untuk fitur pembandingan SIMANSA, jalur yang aman tetap:

1. ambil ID siswa dari hasil daftar EMIS;
2. bentuk atau gunakan token detail siswa;
3. ambil `results.local_nis` dari detail;
4. validasi awalan terhadap NSM aktif pada pengaturan;
5. bandingkan, warnai sama/berbeda, dan tandai duplikat.

RDM dapat dijadikan referensi untuk strategi paging, pencocokan bertahap,
penyimpanan ID eksternal, dan proses per halaman; bukan sebagai pengganti
nilai `local_nis` lengkap.
