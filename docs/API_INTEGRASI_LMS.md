# Dokumentasi REST API Integrasi LMS

Versi: 1.0.0  
Status: aktif, read-only  
Basis URL produksi: https://simansa.man1metro.sch.id/api/v1

Dokumen ini adalah kontrak integrasi antara SIMANSA dan LMS MANSA. API v1
ditujukan untuk sinkronisasi data referensi; LMS tidak boleh mengubah data
master SIMANSA melalui endpoint ini.

## Dokumentasi interaktif

Halaman API Reference tersedia di:

https://simansa.man1metro.sch.id/docs/api

Halaman tersebut menyediakan pencarian endpoint, skema request/response,
contoh request, serta fasilitas Try It. Masukkan Bearer token hanya pada
perangkat dan jaringan yang tepercaya. Spesifikasi yang sama dapat diunduh
dari https://simansa.man1metro.sch.id/docs/api.json.

## Akses dan autentikasi

1. Masuk sebagai Super Admin SIMANSA.
2. Buka Pengaturan lalu REST API Integrasi.
3. Buat token dengan kemampuan lms:read dan simpan nilai token di secret
   manager LMS. Nilai token hanya muncul sekali.
4. Kirimkan kedua header berikut pada seluruh request data:

    Authorization: Bearer NILAI_TOKEN  
    Accept: application/json

Jangan memasukkan token ke kode sumber, file konfigurasi yang ter-commit,
log aplikasi, atau tangkapan layar. Cabut token dari halaman pengaturan
apabila token tidak lagi digunakan atau diduga bocor.

## Spesifikasi mesin

Dokumen OpenAPI 3.0.3 dapat diimpor ke Swagger UI, Postman, Insomnia, atau
generator klien:

https://simansa.man1metro.sch.id/api/v1/openapi.json

## Aturan umum

| Aturan | Nilai |
| --- | --- |
| Metode | GET |
| Format respons | JSON UTF-8 |
| Autentikasi | Laravel Sanctum Bearer token |
| Kemampuan minimum | lms:read |
| Batas laju | 60 request per menit per token/IP |
| Pagination default | 100 data |
| Pagination maksimum | 250 data |
| Zona waktu | Asia/Jakarta pada data operasional; timestamp API mengikuti serialisasi Laravel ISO-8601 |

Parameter query yang tersedia untuk kedua endpoint:

| Parameter | Tipe | Keterangan |
| --- | --- | --- |
| per_page | integer, 1 sampai 250 | Jumlah data per halaman. Default 100. |
| updated_since | ISO-8601 datetime | Hanya mengambil data dengan updated_at lebih baru dari nilai ini. |
| page | integer | Nomor halaman pagination Laravel. |

Untuk sinkronisasi bertahap, simpan timestamp sinkronisasi sukses terakhir
dan kirim kembali sebagai updated_since pada proses selanjutnya. Tambahkan
buffer waktu kecil di sisi LMS dan lakukan upsert berdasarkan id agar tidak
kehilangan perubahan di batas waktu.

## Endpoint siswa aktif

GET /lms/students

Mengambil siswa dengan status_siswa aktif. Data sensitif seperti NIK, alamat,
tanggal lahir, nomor telepon, dan data orang tua tidak dikirim.

Contoh request:

    curl --request GET "https://simansa.man1metro.sch.id/api/v1/lms/students?per_page=100&updated_since=2026-08-29T00:00:00%2B07:00" \
      --header "Accept: application/json" \
      --header "Authorization: Bearer NILAI_TOKEN"

Elemen setiap data:

| Field | Tipe | Keterangan |
| --- | --- | --- |
| id | string | ID internal siswa SIMANSA. Gunakan sebagai kunci upsert. |
| user_id | integer atau null | ID akun SIMANSA terkait bila tersedia. |
| nisn | string atau null | NISN siswa. |
| nama_lengkap | string | Nama lengkap siswa. |
| jenis_kelamin | string atau null | Nilai sumber SIMANSA, umumnya L atau P. |
| updated_at | ISO-8601 datetime | Waktu perubahan terakhir data sumber. |

## Endpoint GTK aktif

GET /lms/teachers

Mengambil GTK dengan status_aktif bernilai true. Endpoint ini digunakan untuk
membentuk atau memperbarui akun pengajar di LMS.

Contoh request:

    curl --request GET "https://simansa.man1metro.sch.id/api/v1/lms/teachers?per_page=100" \
      --header "Accept: application/json" \
      --header "Authorization: Bearer NILAI_TOKEN"

Elemen setiap data:

| Field | Tipe | Keterangan |
| --- | --- | --- |
| id | UUID string | ID internal GTK SIMANSA. Gunakan sebagai kunci upsert. |
| user_id | integer atau null | ID akun SIMANSA terkait bila tersedia. |
| nama_lengkap | string | Nama lengkap GTK. |
| nip | string atau null | NIP jika tersedia. |
| nik | string atau null | Identitas internal; perlakukan sebagai data rahasia. |
| email | string atau null | Email GTK. |
| jenis_ptk | string atau null | Kategori/jenis PTK dari SIMANSA. |
| updated_at | ISO-8601 datetime | Waktu perubahan terakhir data sumber. |

## Bentuk respons pagination

Setiap endpoint data menggunakan paginator Laravel:

    {
      "current_page": 1,
      "data": [
        {
          "id": "contoh-id",
          "nama_lengkap": "Contoh Nama",
          "updated_at": "2026-08-29T00:00:00.000000Z"
        }
      ],
      "per_page": 100,
      "total": 1,
      "next_page_url": null,
      "prev_page_url": null
    }

Lanjutkan request selama next_page_url tidak bernilai null. Jangan menyusun URL
halaman berikutnya sendiri; gunakan nilai URL yang dikembalikan server.

## Kode respons dan penanganan galat

| HTTP | Arti | Tindakan LMS |
| --- | --- | --- |
| 200 | Request berhasil. | Proses dan upsert setiap elemen data. |
| 401 | Token tidak ada, tidak valid, kedaluwarsa, atau sudah dicabut. | Hentikan sinkronisasi dan minta administrator membuat token baru. |
| 403 | Token valid tetapi tidak memiliki lms:read. | Buat token dengan kemampuan yang sesuai. |
| 422 | Query parameter tidak valid. | Perbaiki per_page atau updated_since. |
| 429 | Batas laju terlampaui. | Terapkan exponential backoff dan coba lagi setelah jeda. |
| 500 atau 503 | Gangguan sementara layanan. | Jangan mengubah checkpoint; ulangi dengan backoff. |

Contoh galat validasi:

    {
      "message": "The per page field must not be greater than 250.",
      "errors": {
        "per_page": [
          "The per page field must not be greater than 250."
        ]
      }
    }

## Rekomendasi implementasi sinkronisasi LMS

1. Jalankan sinkronisasi penuh pertama tanpa updated_since.
2. Simpan checkpoint hanya setelah seluruh halaman sukses diproses.
3. Gunakan id SIMANSA sebagai external_id unik di LMS.
4. Jalankan proses terjadwal secara bertahap, tidak paralel untuk token yang
   sama.
5. Catat jumlah data diterima, data dibuat, data diperbarui, dan galat tanpa
   pernah mencatat Bearer token.
6. Jika token dicabut, tandai sinkronisasi gagal secara aman dan beri notifikasi
   administrator.

## Batasan versi 1

API v1 belum menyediakan endpoint tulis, penghapusan, kelas/rombel, mata
pelajaran, atau autentikasi pengguna LMS. Penambahan kapabilitas tersebut
harus memakai versi atau kemampuan token baru agar kontrak v1 tetap stabil.
