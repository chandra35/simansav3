# Arsitektur RDM Tools dan Cipher Gateway

## Tujuan

RDM Tools dibuat untuk membaca serta membandingkan data RDM tanpa mengubah
source inti RDM. Dekripsi hanya dilakukan untuk kebutuhan tampilan/matching.

## Alur

```text
[SIMANSA / RDM Tools]
        |
        | read-only SQL
        v
[Database RDM]
        |
        | ciphertext yang belum ada di cache
        v
[Cipher endpoint pada runtime RDM]
        |
        | mysql_decrypt() dari helper ionCube
        v
[Plaintext untuk tampilan/matching]
```

## Gateway yang pernah dibuat

```text
periksasiswa/api.php
  - list_tables
  - get_schema
  - get_data

periksasiswa/dec.php
  - POST array ciphertext
  - output array plaintext

rdmgate/sync.php
  - versi gateway sinkronisasi

rdmgate/cipher.php
  - versi gateway dekripsi untuk tampilan
```

Semua endpoint harus memakai token dari environment. Jangan menyalin token
lama dari source historis.

## Integritas data

- Database RDM selalu diperlakukan read-only oleh SIMANSA.
- Ciphertext asli tidak ditimpa pada database produksi.
- Plaintext hanya digunakan untuk proses matching/tampilan.
- Cache plaintext harus berada di storage privat.
- Field yang gagal didekripsi tidak boleh ditandai sukses.

## Masalah performa yang pernah terjadi

Implementasi awal mengirim chunk besar secara berurutan. Pada jumlah siswa
ratusan, request dapat melewati batas PHP-FPM.

Perbaikan bertahap:

1. cache per ciphertext;
2. chunk diperkecil;
3. `curl_multi` untuk paralelisme;
4. concurrency dibatasi;
5. pre-match menggunakan NIS plaintext;
6. hanya dekripsi nama untuk siswa yang sudah cocok melalui NIS;
7. dekripsi nama dan NISN untuk siswa yang belum cocok;
8. hasil gagal tidak dimasukkan cache.

## Lokasi implementasi SIMANSA

```text
app/Services/RdmMatchingService.php
resources/views/admin/rdm-matching/index.blade.php
```

Riwayat commit utama:

```text
4a9e533  implementasi matching dan endpoint dekripsi
290fe8b  cache dan pengurangan chunk
26b45cb  paralel curl_multi
f0928f6  pre-match NIS
cbf82b3  cache file permanen
04437d8  batas concurrency dan validasi hasil cache
```
