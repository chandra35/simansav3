# Fitur Cek NIP (Kemenag BE-PINTAR API)

## Deskripsi
Fitur ini memungkinkan Super Admin untuk mengecek data pegawai berdasarkan NIP melalui API Kemenag BE-PINTAR.

## Konfigurasi

### 1. Tambahkan ke file .env

```env
# Kemenag BE-PINTAR API Configuration
KEMENAG_API_URL=https://be-pintar.kemenag.go.id/api/v1
KEMENAG_BEARER_TOKEN=your_bearer_token_here
```

### 2. Cara Mendapatkan Bearer Token

Bearer token harus didapatkan dari Kemenag. Token yang valid formatnya seperti ini:
```
Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**PENTING**: 
- Simpan hanya token tanpa kata "Bearer" di depannya
- Token akan otomatis ditambahkan prefix "Bearer" oleh service

### 3. Clear Cache

Setelah menambahkan ke .env, jalankan:
```bash
php artisan config:clear
php artisan optimize:clear
```

## Cara Penggunaan

1. Login sebagai **Super Admin**
2. Buka menu **Pengaturan → Cek NIP**
3. Masukkan NIP yang ingin dicek (tepat 18 digit)
4. Klik tombol **Cek NIP**
5. Hasil akan ditampilkan dalam 6 kartu:
   - Data Pribadi
   - Data Kepegawaian
   - Data Jabatan
   - Satuan Kerja
   - Kontak
   - Pensiun

## Data yang Ditampilkan

### Data Pribadi
- NIP / NIP Baru
- Nama Lengkap
- Tempat/Tanggal Lahir
- Jenis Kelamin
- Agama
- Pendidikan

### Data Kepegawaian
- Status Pegawai (PNS/PPPK)
- Pangkat/Golongan
- TMT CPNS
- TMT Pangkat
- Masa Kerja
- Gaji Pokok

### Data Jabatan
- Jabatan
- Level Jabatan
- TMT Jabatan

### Satuan Kerja
- Unit Kerja (Satker 1-4)
- Grup Satuan Kerja
- Keterangan Satuan Kerja

### Kontak
- Email
- Telepon/HP
- Alamat Lengkap dengan Provinsi

### Pensiun
- TMT Pensiun
- Usia Pensiun

## Error Handling

Service akan menangani berbagai error:

1. **Token tidak valid/expired**
   - Error: "Token API expired atau invalid"
   - Solusi: Perbarui KEMENAG_BEARER_TOKEN di .env

2. **NIP tidak ditemukan**
   - Error: "NIP tidak ditemukan dalam database Kemenag"
   - Solusi: Periksa kembali NIP yang diinput

3. **Koneksi bermasalah**
   - Error: "Tidak dapat terhubung ke server API Kemenag"
   - Solusi: Periksa koneksi internet

4. **Server error**
   - Error: "Server API Kemenag sedang bermasalah"
   - Solusi: Tunggu beberapa saat dan coba lagi

5. **Format NIP salah**
   - Error: "Format NIP tidak valid"
   - Solusi: Pastikan NIP berupa angka tepat 18 digit

## Logging

Setiap pengecekan NIP akan dicatat di log Laravel (`storage/logs/laravel.log`):

```
[timestamp] INFO: NIP Check Request
- user_id: 1
- user_name: Admin
- nip: 250096804
- timestamp: 2024-11-11 10:00:00

[timestamp] INFO: NIP Check Result
- user_id: 1
- nip: 250096804
- success: true
- message: Data ditemukan
```

## Security

- ✅ Hanya Super Admin atau user dengan permission `manage-settings` yang bisa akses
- ✅ Bearer token disimpan di .env (tidak di-commit ke git)
- ✅ Semua request dicatat di log dengan user_id
- ✅ Input NIP divalidasi (numeric, tepat 18 digit)

## Troubleshooting

### Menu tidak muncul
- Pastikan login sebagai Super Admin
- Clear cache: `php artisan optimize:clear`

### Error "Bearer token tidak dikonfigurasi"
- Tambahkan `KEMENAG_BEARER_TOKEN` di file .env
- Jalankan `php artisan config:clear`

### Error 401 Unauthorized
- Token expired atau invalid
- Minta token baru ke Kemenag

### Timeout
- API Kemenag lambat atau down
- Tunggu beberapa saat dan coba lagi
- Default timeout: 30 detik

## File yang Terlibat

1. **Controller**: `app/Http/Controllers/Admin/NipCheckerController.php`
2. **Service**: `app/Services/KemenagNipService.php`
3. **View**: `resources/views/admin/pengaturan/cek-nip.blade.php`
4. **Config**: `config/services.php`
5. **Routes**: `routes/web.php`
6. **Menu**: `config/adminlte.php`

## API Reference

**Endpoint**: `POST https://be-pintar.kemenag.go.id/api/v1/cek_nip`

**Headers**:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
Origin: https://pintar.kemenag.go.id
Referer: https://pintar.kemenag.go.id/
```

**Request Body**:
```json
{
  "nip": "198909092025211087"
}
```

**Response Success (200)**:
```json
{
  "code": 200,
  "message": "Found",
  "data": {
    "NIP": "250096804",
    "NIP_BARU": "198909092025211087",
    "NAMA": "CANDRA HUDA BUANA",
    "STATUS_PEGAWAI": "PPPK",
    ...
  }
}
```

**Response Not Found (404)**:
```json
{
  "code": 404,
  "message": "Not Found",
  "data": null
}
```
