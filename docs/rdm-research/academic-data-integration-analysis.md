# Analisis Integrasi Data Akademik RDM ke SIMANSA

Tanggal pemeriksaan: 30 Juli 2026.

Analisis ini mencakup identitas siswa, atribut keluarga, rombel, riwayat kelas,
nilai dari semester awal sampai akhir, absensi rapor, ekstrakurikuler, prestasi,
P5RA, serta kesiapan struktur SIMANSA. Semua pemeriksaan database dilakukan
secara read-only dan tidak menyimpan contoh identitas siswa.

## Keputusan arsitektur

RDM sebaiknya diperlakukan sebagai **sumber histori akademik**, bukan sebagai
master identitas yang bebas menimpa SIMANSA.

```text
RDM read-only
  -> staging bersnapshot
  -> dekripsi + pencocokan identitas
  -> validasi periode, kelas, mapel, dan kurikulum
  -> preview konflik
  -> apply idempotent
  -> histori/transkrip SIMANSA
```

Identitas SIMANSA tetap menjadi master. Nilai kosong dan atribut historis dapat
diperkaya dari RDM, tetapi konflik nama, NISN, tanggal lahir, dan data keluarga
harus melalui preview atau persetujuan, bukan overwrite otomatis.

## Cakupan data RDM

Jumlah aktual saat pemeriksaan:

| Tabel RDM | Baris | Fungsi |
|---|---:|---|
| `e_siswa` | 2.792 | master siswa dan atribut keluarga |
| `e_riwayatsiswa` | 6.313 | keanggotaan kelas per tahun |
| `e_kelas` | 154 | rombel per tahun ajaran |
| `e_ajar` | 6.549 | penugasan mapel, guru, kelas, semester |
| `e_mapel` | 104 | master mata pelajaran |
| `e_mapelpilihan` | 12.878 | pilihan mapel per siswa/periode |
| `e_komponennilai` | 42.745 | definisi komponen penilaian |
| `e_inputnilai` | 1.505.333 | nilai detail per komponen |
| `e_rapor` | 358.964 | nilai akhir rapor |
| `e_absenwalas` | 10.828 | S/I/A, tinggi, berat, catatan, kenaikan |
| `e_extrasiswa` | 7.704 | nilai ekstrakurikuler |
| `e_extrapilihan` | 8.030 | pilihan ekstrakurikuler |
| `e_prestasi` | 848 | prestasi siswa |
| `p_proyek` | 29 | proyek P5RA |
| `p_target` | 52 | target elemen P5RA |
| `p_nilai` | 1.773 | nilai elemen P5RA |
| `p_catatan` | 535 | catatan proyek per siswa |
| `k_nilai` | 0 | nilai kokurikuler model baru, belum digunakan |

Nilai rapor tersedia untuk tahun ajaran 2021/2022 sampai 2025/2026, masing-masing
semester ganjil dan genap.

## Model periode RDM

RDM mempunyai dua kelompok semester:

- `semester_id` 1 dan 2: Ganjil dan Genap, dipakai tabel nilai;
- `semester_id` 3–8: label Semester I–VI, tersedia pada master tetapi tidak
  menjadi kunci transaksi nilai.

Tingkat RDM dipetakan:

| RDM | SIMANSA | Semester global |
|---:|---:|---|
| 12 | Kelas X | 1–2 |
| 13 | Kelas XI | 3–4 |
| 14 | Kelas XII | 5–6 |

`e_kelas.semester_id` dan seluruh `e_riwayatsiswa.semester_id` kosong. Jadi
rombel RDM bersifat tahunan. Konteks semester yang benar berasal dari tabel
nilai, pengajaran, absensi, ekstra, dan rapor.

## Transisi kurikulum

Struktur nilai menunjukkan transisi bertahap:

- 2021/2022 dan 2022/2023: semua tingkat masih mempunyai nilai Pengetahuan dan
  Keterampilan;
- 2023/2024: kelas X hanya nilai utama/Pengetahuan, kelas XI–XII masih dua nilai;
- 2024/2025: kelas X–XI hanya nilai utama, kelas XII masih dua nilai;
- 2025/2026: seluruh tingkat hanya nilai utama.

Ini konsisten dengan perpindahan kohort dari K13 ke Kurikulum Merdeka. Importer
tidak boleh memakai aturan tunggal untuk semua tahun.

## Identitas dan atribut siswa

### Identitas inti

| RDM | Kondisi | Target SIMANSA |
|---|---|---|
| `siswa_id` | integer internal RDM | tabel mapping eksternal |
| `siswa_nis` | 1.734 terisi; semuanya 6 digit | bukan `nis_lokal` 18 digit |
| `siswa_nisn` | 2.789 terisi; terenkripsi deterministik | `siswa.nisn` setelah decrypt dan validasi |
| `siswa_nama` | 2.792 terisi; terenkripsi | `siswa.nama_lengkap` setelah decrypt |
| `siswa_gender` | seluruhnya terisi | `siswa.jenis_kelamin` |
| `siswa_tempat` | seluruhnya terisi | `siswa.tempat_lahir` |
| `siswa_tgllahir` | seluruhnya terisi | `siswa.tanggal_lahir` |
| `siswa_emis` | 2.726, wrapper terenkripsi | simpan opaque sebagai referensi |
| `peserta_didik_id` | 2.515, unik, panjang 119 | simpan opaque sebagai referensi |

NIS RDM tidak boleh dipanjangkan dengan menambahkan NSM. Ia merupakan identifier
enam digit yang berbeda dari skema NIS Lokal SIMANSA.

### Profil dan keluarga

RDM juga mempunyai:

- agama, alamat siswa, anak ke, asal sekolah, nomor telepon;
- nama ayah/ibu, kode pekerjaan dan nilai pekerjaan lain;
- alamat orang tua;
- nama, pekerjaan, alamat, dan telepon wali;
- tanggal diterima dan kelas saat diterima;
- alasan, tahun, dan semester mutasi.

Kondisi kelengkapan penting:

- alamat siswa: 2.757;
- nama ayah: 2.753;
- nama ibu: 2.768;
- alamat orang tua: 2.756;
- nama wali: 2.283;
- sekolah asal: 2.415;
- telepon siswa: 1.442;
- data mutasi: 214;
- foto/folder, pendidikan, NIK ayah/ibu, kode wilayah, telepon orang tua/wali:
  tidak terisi pada data saat ini.

SIMANSA belum memiliki field wali selengkap RDM. Jangan memasukkan data wali ke
field ayah/ibu. Pilihannya adalah memperluas `ortu` dengan kelompok field wali
atau membuat tabel `wali_siswa`.

Password RDM, field audit internal, dan ciphertext aktif tidak boleh diimpor ke
akun SIMANSA.

## Kualitas identifier

RDM mempunyai duplikasi:

| Identifier | Grup duplikat | Baris tambahan |
|---|---:|---:|
| NIS 6 digit | 28 | 55 |
| NISN terenkripsi | 35 | 35 |
| `siswa_emis` | 1 | 1 |

Semua siswa masih mempunyai minimal NIS atau NISN. Namun tidak satu pun
identifier RDM boleh langsung dianggap unik tanpa pemeriksaan.

Urutan matching yang direkomendasikan:

1. mapping RDM–SIMANSA yang telah diverifikasi manual;
2. NISN hasil dekripsi, hanya jika unik di kedua sistem;
3. `peserta_didik_id`/ID EMIS apabila sudah terhubung dengan snapshot EMIS;
4. nama + tanggal lahir + jenis kelamin untuk kandidat review;
5. NIS enam digit hanya sebagai bukti pendukung, bukan primary match.

Mapping final harus menyimpan `rdm_siswa_id` agar sinkron berikutnya tidak
mengulang fuzzy matching.

## Rombel dan riwayat kelas

Relasi utama:

```text
e_tahunajaran
  -> e_kelas
     -> e_riwayatsiswa
     -> e_ajar
     -> e_rapor
```

Masalah kualitas:

- 639 baris `e_riwayatsiswa` menunjuk kelas yang sudah tidak ada;
- 637 siswa mempunyai `e_siswa.kelas_id` yang tidak lagi ada;
- terdapat 54 grup riwayat ganda pada siswa+tahun yang sama;
- terdapat 10 siswa-periode nilai yang memakai dua kelas dalam semester sama;
- `e_riwayatsiswa` tidak mempunyai timestamp masuk/keluar atau urutan perubahan.

Karena itu sumber riwayat harus diberi tingkat kepercayaan:

1. `e_rapor`: kelas per semester, paling kuat untuk periode yang sudah bernilai;
2. `e_riwayatsiswa`: kelas tahunan, dipakai jika referensinya masih ada;
3. `e_siswa.kelas_id`: posisi terakhir, hanya jika kelas masih ada;
4. data yang konflik dipertahankan sebagai observasi dan masuk antrean review.

SIMANSA `siswa_kelas` sudah lebih baik karena memiliki tanggal masuk/keluar,
status, tingkat, dan catatan perpindahan. Namun ia tahunan, sehingga perlu tabel
snapshot semester agar perpindahan pada semester yang sama tidak hilang.

Usulan:

```text
siswa_periode_akademik
  id
  siswa_id
  tahun_pelajaran_id
  semester_periode       -- 1=ganjil, 2=genap
  semester_global        -- 1..6
  tingkat
  kelas_id nullable
  rdm_kelas_id nullable
  sumber
  confidence
  conflict_data JSON
  synced_at
```

`siswa_kelas` tetap menjadi riwayat operasional SIMANSA. Tabel baru menyimpan
snapshot historis per semester hasil rekonstruksi.

## Struktur nilai

### Nilai akhir

`e_rapor` mempunyai natural key:

```text
tahunajaran + semester + kelas + mapel + siswa + jenisnilai
```

Tidak ditemukan duplikasi pada natural key tersebut.

Field penting:

- `jenisnilai_id=1`: Pengetahuan/nilai utama;
- `jenisnilai_id=2`: Keterampilan;
- nilai 0–100;
- predikat;
- deskripsi kompetensi;
- deskripsi nilai minimum;
- kelas, tingkat, mapel, pengajaran, dan guru.

Kualitas:

- 358.964 nilai, tanpa nilai null atau di luar 0–100;
- 104.424 predikat kosong;
- 1.462 deskripsi utama kosong;
- hampir seluruh `rapor_deskmin` kosong.

Sebanyak 1.015 siswa memiliki enam periode nilai lengkap. Distribusi siswa
berdasarkan jumlah periode rapor:

| Periode tersedia | Siswa |
|---:|---:|
| 1 | 63 |
| 2 | 842 |
| 3 | 23 |
| 4 | 746 |
| 5 | 5 |
| 6 | 1.015 |

Siswa dengan 2/4 periode umumnya adalah kohort yang belum menyelesaikan seluruh
masa belajar pada rentang data.

### Nilai detail

`e_komponennilai` mendefinisikan komponen:

- Nilai Harian;
- PAS;
- Unjuk Kerja;
- Proyek;
- Portofolio;
- indikator Sikap Sosial dan Spiritual skala 1–4.

`e_inputnilai` menyimpan 1.505.333 nilai per siswa-komponen. Hanya empat nilai
berada di luar 0–100 dan harus dikarantina untuk pemeriksaan. Catatan komponen
seluruhnya kosong.

Import nilai detail bersifat opsional karena volumenya besar. Nilai final
`e_rapor` harus diselesaikan lebih dahulu.

## Data rapor non-mapel

### Absensi dan kondisi fisik

`e_absenwalas` berisi satu rekap siswa per semester:

- sakit, izin, alpa;
- catatan wali kelas;
- indikator naik/lulus;
- tinggi dan berat badan.

Tersedia 10.828 record dari 2021/2022 sampai 2025/2026.

### Ekstrakurikuler

Tersedia 7.704 nilai dengan skala 1–4 dan 8.030 pilihan anggota. Struktur
SIMANSA `ekstrakurikuler` dan `ekstrakurikuler_anggota` sudah dapat menampungnya,
setelah mapping kegiatan, tahun, siswa, dan konversi nilai ke predikat.

### Prestasi

RDM mempunyai 848 prestasi, tetapi hanya nama dan keterangan. Tabel
`prestasi_siswa` SIMANSA mewajibkan jenis, tingkat, peringkat, penyelenggara,
dan tanggal. Data RDM tidak boleh langsung dipaksakan ke enum tersebut.

Solusi: staging + status `perlu_dilengkapi`, atau longgarkan field wajib dengan
metadata `sumber_data=rdm`.

### P5RA dan kokurikuler

P5RA mempunyai 1.773 nilai skala 1–4 untuk 360 siswa, 29 proyek, serta 535
catatan. Relasi periode tidak selalu lengkap: data proyek 2024 ditemukan tanpa
semester pada pembina.

Tabel kokurikuler model baru masih kosong. Jangan menggabungkan P5RA lama dan
kokurikuler baru tanpa kolom jenis model.

## Kondisi SIMANSA saat ini

Produksi SIMANSA saat pemeriksaan:

| Data | Kondisi |
|---|---|
| Siswa | 1.738 |
| Orang tua | 1.734 |
| Tahun pelajaran | 2022/2023–2026/2027 |
| Kelas | baru tersedia untuk 2025/2026 dan 2026/2027 |
| Riwayat `siswa_kelas` | 2.645, baru dua tahun terakhir |
| `nilai_siswa` | 33.490 |
| Ekstrakurikuler/anggota | kosong |
| Prestasi siswa | kosong |

Nilai SIMANSA saat ini hanya mencakup satu kohort:

- Semester 1–2 pada 2023/2024;
- Semester 3–4 pada 2024/2025;
- Semester 5 pada 2025/2026;
- 355–361 siswa per semester;
- semuanya berasal dari `import_excel`;
- deskripsi pengetahuan/keterampilan seluruhnya kosong.

SIMANSA belum memiliki tahun 2021/2022. Tahun ini harus dibuat sebelum seluruh
histori RDM dapat diimpor.

## Masalah pada sinkron RDM yang sudah ada

`RdmSyncService` saat ini cukup untuk preview sederhana nilai aktif, tetapi
belum aman untuk migrasi histori:

1. `fetchRdmRows()` mengambil `siswa_nisn` dan `siswa_nama` tanpa dekripsi.
   Staging produksi mempunyai 44.003 mismatch siswa, sebagian ditandai oleh
   panjang ciphertext.
2. Staging tidak menyimpan `jenisnilai_id`, predikat, deskripsi, `ajar_id`,
   guru, atau kelas target SIMANSA.
3. Untuk K13, dua baris Pengetahuan dan Keterampilan masuk ke unique key
   `nilai_siswa` yang sama.
4. `applySync()` mengisi kedua kolom Pengetahuan dan Keterampilan dengan nilai
   baris yang sedang diproses. Baris berikutnya dapat menimpa keduanya.
5. Kurikulum Merdeka dan K13 ditentukan dari mapel, padahal transisi juga
   bergantung pada kohort/tingkat/periode.
6. Semester 6 dapat dihasilkan oleh mapper, tetapi `NilaiSiswa::SEMESTER_LABELS`
   hanya mendefinisikan Semester 1–5.
7. Deskripsi dan predikat asli RDM diabaikan.
8. Tidak ada mapping permanen `rdm_siswa_id -> siswa_id`.

Belum ada run yang di-apply pada produksi; enam run yang ditemukan masih
berstatus preview. Ini memberi ruang untuk memperbaiki pipeline sebelum
penulisan nilai historis.

## Desain tabel target

### Wajib

1. `rdm_siswa_mappings`
   - `rdm_siswa_id` unik;
   - `siswa_id`;
   - NIS/NISN fingerprint;
   - opaque `siswa_emis` dan `peserta_didik_id`;
   - metode, confidence, reviewer, dan waktu verifikasi.

2. `rdm_kelas_mappings`
   - kelas/tahun RDM ke kelas SIMANSA;
   - nama, tingkat, jurusan, kurikulum, dan status mapping.

3. `siswa_periode_akademik`
   - snapshot kelas per siswa dan semester;
   - sumber serta konflik.

4. Perluasan staging nilai
   - `rdm_jenisnilai_id`;
   - nilai, predikat, dua deskripsi;
   - `rdm_kelas_id`, `rdm_ajar_id`, `rdm_guru_id`;
   - hash payload dan status validasi.

5. Perbaikan `nilai_siswa`
   - dukungan Semester 6;
   - `kelas_id` historis nullable;
   - `source_ref`, `source_hash`, `synced_at`;
   - grouping K13 sebelum upsert.

### Tahap lanjutan

- `nilai_komponen` dan `nilai_siswa_detail`;
- `rekap_rapor_semester` untuk absensi, fisik, catatan, dan kenaikan;
- field wali atau tabel `wali_siswa`;
- tabel proyek/nilai P5RA dan kokurikuler dengan pembeda model;
- raw snapshot JSON privat untuk audit dan reprocessing.

## Aturan transformasi nilai

### Kurikulum Merdeka

```text
jenisnilai 1 -> nilai_siswa.nilai
jenisnilai 2 -> hanya jika memang ada pada kohort/periode tersebut
```

### K13

Sebelum upsert, pivot:

```text
GROUP BY siswa + mapel + tahun + semester
  jenisnilai 1 -> nilai_pengetahuan + deskripsi_pengetahuan
  jenisnilai 2 -> nilai_keterampilan + deskripsi_keterampilan
```

Nilai akhir tampilan tidak boleh dipalsukan dengan menyalin salah satu nilai.
Jika SIMANSA memerlukan `nilai`, rumus agregasinya harus ditentukan eksplisit
dan disimpan bersama versi aturan.

## Aturan sinkronisasi

- RDM selalu read-only.
- Semua proses dimulai dengan preview.
- Upsert memakai external ID dan natural key, bukan nama.
- Record yang sudah diubah manual di SIMANSA tidak ditimpa tanpa kebijakan
  precedence.
- Nilai import Excel yang sudah ada dibandingkan terlebih dahulu; sama berarti
  `unchanged`, berbeda berarti konflik.
- Data kosong RDM tidak menghapus data SIMANSA.
- Setiap batch menyimpan count source, matched, conflict, skipped, inserted,
  updated, dan checksum.
- Apply berjalan per tahun dan semester dalam transaksi/chunk.
- Proses dapat dilanjutkan setelah gagal tanpa menghasilkan duplikasi.

## Tahapan implementasi

1. **Fondasi dan audit**
   - buat mapping siswa/kelas dan staging baru;
   - perbaiki dekripsi dan identitas;
   - jangan menulis nilai dahulu.

2. **Identitas dan periode**
   - mapping siswa;
   - import kelas/tahun yang hilang;
   - bangun snapshot riwayat semester dengan confidence.

3. **Nilai akhir**
   - import `e_rapor`;
   - pivot K13;
   - dukung Semester 6;
   - bandingkan dengan 33.490 nilai existing.

4. **Rekap rapor**
   - absensi, fisik, catatan, ekstra, dan prestasi.

5. **Nilai detail**
   - komponen dan 1,5 juta nilai, hanya jika benar-benar diperlukan.

6. **P5RA/kokurikuler**
   - desain model terpisah dan lengkapi metadata periode.

7. **UI**
   - transkrip Semester 1–6;
   - riwayat kelas;
   - sumber data dan indikator konflik;
   - audit per batch.

## Kriteria aman sebelum apply pertama

- seluruh identifier duplikat sudah dikarantina;
- mapping siswa dan mapel mempunyai statistik terverifikasi;
- tahun 2021/2022 serta kelas historis tersedia;
- test fixture mencakup K13, Merdeka, mutasi, dua kelas dalam satu periode,
  kelas hilang, NISN duplikat, dan Semester 6;
- preview nilai existing vs RDM dapat diekspor;
- backup SIMANSA tersedia;
- dry-run menghasilkan checksum yang sama saat diulang;
- tidak ada write ke database RDM.
