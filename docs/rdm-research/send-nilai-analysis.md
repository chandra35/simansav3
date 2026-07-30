# Analisis Pengiriman Nilai RDM ke Pusat

Tanggal pemeriksaan: 30 Juli 2026.

Pemeriksaan dilakukan secara read-only terhadap bundle frontend aktif, access
log RDM, struktur dan agregat database, serta respons CORS API pusat. Tidak ada
tombol kirim yang ditekan dan tidak ada paket nilai yang dikirim.

## Ringkasan

Berbeda dari Sync EMIS, pengiriman nilai RDM ke pusat dilakukan langsung oleh
browser:

```text
Browser proktor
  -> backend RDM lokal: siapkan paket nilai kelas
  <- array JSON per siswa
  -> API pusat RDM: kirim JSON dengan Bearer token
  <- status berhasil/gagal
  -> backend RDM lokal: tandai kelas selesai dikirim
```

Tujuan API pusat yang tertanam pada bundle:

```text
https://rdm.hdmadrasah.id/api/
```

API pusat pada saat pemeriksaan berjalan di belakang `Tengine`, memberikan
header `X-Powered-By: Express`, dan menerima CORS dari aplikasi RDM. Preflight
untuk header `authorization,content-type` mendapat HTTP 204.

## Autentikasi dan format transport

Service frontend `ApiPusat` melakukan:

```text
POST https://rdm.hdmadrasah.id/api/{endpoint}
Content-Type: application/json; charset=utf-8
Authorization: Bearer {accessToken}
```

Payload dibentuk dengan `JSON.stringify()`. Bearer token diambil oleh
`getToken()` dari cookie Angular bernama `accessToken`.

Paket tidak dienkripsi sebagai satu blob pada layer aplikasi. Minimal objek
`siswa.siswa_nama` tersedia sebagai plaintext di JavaScript karena dipakai
untuk menampilkan progres. Kerahasiaan saat transit bergantung pada HTTPS/TLS
dan Bearer token.

Bearer token aktif, cookie sesi, dan isi data siswa tidak disimpan dalam arsip.

## Syarat tombol kirim

Daftar kelas diambil dari endpoint lokal:

```text
POST proktor/kirimnilai/data
```

Frontend menampilkan tombol:

- **Kirim** jika `persenkirim == 100` dan `nilailock == 2`;
- **Batal Kirim** jika `nilailock == 3`.

Makna internal seluruh status lock tidak ditentukan hanya dari frontend.
Namun status `3` terbukti merupakan keadaan setelah pengiriman selesai.

## Alur kirim satu kelas

1. Proktor menekan **Kirim** dan mengonfirmasi dialog.
2. Browser memanggil endpoint lokal:

   ```text
   POST proktor/kirimnilai/prepare
   ```

   Payload request adalah objek baris kelas dari tabel.

3. Backend lokal membaca data RDM dan mengembalikan `response.data` sebagai
   array paket. Frontend menyimpannya pada `dataPusat`.
4. Bundle aktif mengirim setiap elemen secara berurutan:

   ```text
   POST https://rdm.hdmadrasah.id/api/newkirimnilai
   ```

   Satu request membawa satu objek siswa beserta data rapornya. Frontend
   menampilkan progres berdasarkan `dataPusat[index].siswa.siswa_nama`.
5. Setelah seluruh siswa mendapat respons `success`, browser memanggil:

   ```text
   POST proktor/kirimnilai/done
   ```

6. Backend lokal mengubah status pengiriman kelas dan daftar kelas dimuat ulang.

Access log menunjukkan respons `prepare` historis berukuran sekitar 52–81 KB
per kelas. Jarak `prepare` ke `done` umumnya puluhan detik sampai sekitar dua
menit, sesuai pengiriman siswa secara berurutan ke domain pusat.

## Alur Kirim Semua

1. Browser meminta kelas yang belum terkirim:

   ```text
   POST proktor/kirimnilai/getkirimkelas
   ```

2. Untuk setiap kelas, browser memanggil `proktor/kirimnilai/prepare`.
3. Seluruh array siswa untuk satu kelas dikirim sekaligus ke:

   ```text
   POST https://rdm.hdmadrasah.id/api/newkirimnilaibulk
   ```

4. Respons pusat diperiksa. Jika `success == true` dan `gagal == 0`, browser
   memanggil `proktor/kirimnilai/done`, lalu melanjutkan kelas berikutnya.

Jadi **Kirim** satu kelas memakai request pusat per siswa, sedangkan
**Kirim Semua** memakai satu request bulk untuk setiap kelas.

## Endpoint lama yang masih tertinggal

Bundle aktif masih memuat fungsi untuk endpoint:

```text
https://rdm.hdmadrasah.id/api/kirimnilai
https://rdm.hdmadrasah.id/api/kirimnilaibulk
```

Alur tombol aktif memakai endpoint dengan awalan `new`. Fungsi lama tampak
sebagai kode kompatibilitas/warisan dan tidak menjadi jalur utama tombol saat
ini.

## Penandaan status lokal

Database lokal memakai tabel `e_kelaslock`:

| Field | Fungsi |
|---|---|
| `lembaga_id` | identitas lembaga |
| `kelas_id` | kelas yang dikirim |
| `tahunajaran_id` | tahun ajaran |
| `semester_id` | semester |
| `kelaslock_status` | status proses/kunci |
| `kelaslock_tanggal` | waktu perubahan |

Agregat saat pemeriksaan menunjukkan status `3` pada 235 record. Timestamp
status `3` terbaru, `2026-06-24 09:52:36`, sama persis dengan request
`proktor/kirimnilai/done` pada access log. Ini mengonfirmasi bahwa tahap
`done` menandai kelas telah terkirim secara lokal.

Endpoint **Batal Kirim** yang terlihat dari browser hanya:

```text
POST proktor/kirimnilai/batalkirim
```

Controller backend diproteksi ionCube. Karena itu belum dapat dibuktikan secara
statis apakah endpoint ini hanya mengubah status lokal atau juga menghubungi
pusat dari sisi server.

## Data sumber lokal

Nilai rapor utama tersimpan pada `e_rapor`, dengan konteks:

- lembaga, tahun ajaran, dan semester;
- tingkat dan kelas;
- mata pelajaran, pengajaran, dan guru;
- siswa;
- nilai angka;
- predikat;
- deskripsi kompetensi;
- deskripsi nilai minimum.

Backend `prepare` diproteksi ionCube, sehingga daftar lengkap key JSON dan
seluruh tabel tambahan yang digabungkan belum dapat dinyatakan terverifikasi
hanya dari source statis. Ukuran response serta bentuk akses frontend
mengonfirmasi bahwa hasilnya merupakan array objek siswa dengan data rapor
bersarang, bukan ciphertext tunggal.

## Kelemahan yang ditemukan

Bundle memiliki parameter penghitung retry sampai tiga kali, tetapi kondisi
hasil minifikasi saat ini berbentuk ekuivalen:

```text
if (response.success) {
    lanjut;
} else if (response.success) {
    retry;
} else {
    tampilkan error;
}
```

Cabang retry tidak mungkin tercapai karena kondisi kedua sama dengan kondisi
pertama. Respons `success: false` langsung berhenti. Ini berlaku pada beberapa
jalur kirim per siswa dan bulk. Belum dipastikan apakah source asli memang
memiliki kesalahan tersebut atau bundle produksi dibangun dari revisi yang
bermasalah.

## Kesimpulan keamanan dan operasional

- Nilai keluar dari browser proktor langsung menuju pusat RDM.
- Siapa pun yang memegang cookie `accessToken` dapat membentuk header Bearer;
  token harus diperlakukan sebagai secret.
- Paket JSON siswa terlihat di DevTools browser sebelum dikirim.
- `done` baru dipanggil setelah frontend menilai seluruh pengiriman berhasil.
- Putusnya koneksi setelah pusat berhasil tetapi sebelum `done` dapat membuat
  status pusat dan lokal berbeda.
- Sebaliknya, `prepare` tanpa `done` tidak menandai kelas terkirim.
- Untuk audit payload lengkap, metode paling aman adalah menangkap satu request
  nyata dari DevTools dengan redaksi identitas dan token, bukan menebak isi
  controller ionCube atau mengirim paket percobaan.
