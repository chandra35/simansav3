# Cara Kerja Import Siswa vs Create Siswa Manual

## 📋 Perbandingan

### 1. CREATE SISWA MANUAL (Form Biasa)

**Lokasi**: `app/Http/Controllers/Admin/SiswaController.php` → method `store()`

**Alur Kerja**:
```
1. User mengisi form manual (satu-satu)
2. Validasi input di controller
3. Create User account
4. Create Siswa record
5. Create Ortu record (KOSONG, akan diisi siswa nanti)
6. Commit transaction
7. Return success response
```

**Data yang Dibuat**:
```php
// User
- name: dari form
- username: NISN
- email: {nisn}@student.man1metro.sch.id
- password: NISN (default)
- role: 'siswa' (hardcoded)
- is_first_login: true

// Siswa
- user_id: dari user yang baru dibuat
- nisn: dari form
- nama_lengkap: dari form
- jenis_kelamin: dari form
- data_diri_completed: false (default)
- data_ortu_completed: false (default)

// Ortu
- siswa_id: dari siswa yang baru dibuat
- (SEMUA KOLOM KOSONG) → siswa yang isi sendiri nanti
```

**Validasi**:
- ✅ NISN required, unique
- ✅ Nama Lengkap required
- ✅ Jenis Kelamin required (L/P)
- ❌ Tidak validasi NIK
- ❌ Tidak validasi Nama Ayah
- ❌ Tidak validasi format NISN (10 digit)

---

### 2. IMPORT SISWA DARI EXCEL (Bulk Upload)

**Lokasi**: `app/Imports/SiswaImport.php`

**Alur Kerja**:
```
1. User upload file Excel (banyak data sekaligus)
2. System baca file dengan Maatwebsite/Excel
3. Loop setiap baris (dengan chunk 100 rows)
4. Untuk setiap baris:
   a. Validasi data wajib
   b. Check duplicate NISN
   c. Check duplicate NIK
   d. Create User account
   e. Assign role 'Siswa' (dari Spatie Permission)
   f. Create Siswa record
   g. Create Ortu record (dengan Nama Ayah)
   h. Commit transaction
5. Track hasil: berhasil/gagal/error
6. Return summary + detail error
```

**Data yang Dibuat**:
```php
// User
- name: dari Excel
- username: NISN dari Excel
- email: {nisn}@siswa.simansa.sch.id  ← BEDA DOMAIN!
- password: 'password123' (default)  ← BEDA PASSWORD!
- is_first_login: true

// Siswa
- user_id: dari user yang baru dibuat
- nisn: dari Excel (WAJIB 10 digit)
- nik: dari Excel (WAJIB 16 digit)
- nama_lengkap: dari Excel
- jenis_kelamin: dari Excel (dinormalisasi ke L/P)
- data_diri_completed: false
- data_ortu_completed: false

// Ortu
- siswa_id: dari siswa yang baru dibuat
- nama_ayah: dari Excel (WAJIB DIISI!)  ← BEDA!
- status_ayah: 'Masih Hidup' (default)
```

**Validasi Lengkap**:
- ✅ NISN required, unique, HARUS 10 DIGIT
- ✅ NIK required, unique, HARUS 16 DIGIT
- ✅ Nama Lengkap required
- ✅ Jenis Kelamin required (L/P/Laki-laki/Perempuan)
- ✅ Nama Ayah WAJIB diisi
- ✅ Auto-normalize jenis kelamin (Laki-laki → L, Perempuan → P)
- ✅ Check duplicate NISN di database
- ✅ Check duplicate NIK di database

---

## 🔍 Perbedaan Utama

| Aspek | Create Manual | Import Excel |
|-------|---------------|--------------|
| **Jumlah Data** | Satu-satu | Bulk (banyak sekaligus) |
| **Email Domain** | `@student.man1metro.sch.id` | `@siswa.simansa.sch.id` |
| **Password Default** | NISN | `password123` |
| **Role Assignment** | Hardcoded string `'siswa'` | Spatie Role `assignRole('Siswa')` |
| **NIK** | Tidak wajib | WAJIB (16 digit) |
| **Nama Ayah** | Tidak diisi | WAJIB diisi |
| **Data Ortu** | Kosong semua | Nama Ayah terisi |
| **Validasi NISN** | Basic unique | 10 digit + unique |
| **Validasi NIK** | Tidak ada | 16 digit + unique |
| **Jenis Kelamin** | L/P saja | L/P/Laki-laki/Perempuan (auto-normalize) |
| **Error Handling** | Stop jika error | Lanjut, track error per row |
| **Batch Processing** | N/A | 100 rows per batch |
| **Progress Tracking** | Tidak ada | Ada (progress bar) |
| **Error Report** | Alert saja | Detail per baris (tabel) |

---

## 🔧 Teknologi yang Digunakan

### Import Excel Menggunakan:

1. **Maatwebsite/Excel** (v3.1.67)
   - Package Laravel untuk import/export Excel
   - Wrapper dari PhpOffice/PhpSpreadsheet

2. **Implements Interfaces**:
   ```php
   ToCollection         → Process data as Laravel Collection
   WithHeadingRow       → Gunakan baris 1 sebagai key array
   WithValidation       → Validasi otomatis
   SkipsEmptyRows       → Skip baris kosong
   WithBatchInserts     → Insert per batch (performa)
   WithChunkReading     → Baca file per chunk (memory efficient)
   ```

3. **Batch & Chunk**:
   ```php
   public function batchSize(): int {
       return 100;  // Insert 100 records sekaligus
   }
   
   public function chunkSize(): int {
       return 100;  // Baca 100 baris sekaligus
   }
   ```

4. **Database Transaction per Row**:
   ```php
   foreach ($rows as $row) {
       DB::beginTransaction();
       try {
           // Create User
           // Create Siswa
           // Create Ortu
           DB::commit();
           $success++;
       } catch (\Exception $e) {
           DB::rollBack();
           $failed++;
           $errors[] = [...];
       }
   }
   ```

---

## 📊 Contoh Data Excel

### Template Format:

| NISN       | NIK              | Nama Lengkap        | Jenis Kelamin | Nama Ayah      |
|------------|------------------|---------------------|---------------|----------------|
| 0123456789 | 1234567890123456 | Ahmad Rizki Pratama | L             | Budi Santoso   |
| 0123456790 | 1234567890123457 | Siti Nurhaliza      | P             | Ahmad Yani     |
| 0123456791 | 1234567890123458 | Deni Saputra        | Laki-laki     | Deni Irawan    |

**Notes**:
- Kolom `Jenis Kelamin` bisa: `L`, `P`, `Laki-laki`, atau `Perempuan`
- System akan auto-convert `Laki-laki` → `L` dan `Perempuan` → `P`

---

## ⚠️ Error Handling

### Create Manual:
```php
// Jika error, langsung stop dan return error message
catch (\Exception $e) {
    DB::rollBack();
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
```

### Import Excel:
```php
// Jika error pada 1 baris, skip dan lanjut ke baris berikutnya
catch (\Exception $e) {
    DB::rollBack();
    $this->results['failed']++;
    $this->results['errors'][] = [
        'row' => $rowNumber,      // Baris ke berapa di Excel
        'nisn' => $row['nisn'],   // NISN yang bermasalah
        'nama' => $row['nama'],   // Nama siswa
        'error' => $e->getMessage() // Detail error
    ];
    continue; // Lanjut ke baris berikutnya
}
```

### Output Import:
```json
{
    "success": true,
    "message": "Import selesai",
    "data": {
        "success_count": 47,
        "failed_count": 3,
        "total": 50,
        "errors": [
            {
                "row": 15,
                "nisn": "0123456789",
                "nama": "Ahmad Test",
                "error": "NISN 0123456789 sudah terdaftar di sistem"
            },
            {
                "row": 23,
                "nisn": "9876543210",
                "nama": "Budi Test",
                "error": "NIK harus 16 digit angka"
            },
            {
                "row": 38,
                "nisn": "5555555555",
                "nama": "Citra Test",
                "error": "NIK 3201234567890123 sudah terdaftar di sistem"
            }
        ]
    }
}
```

---

## 🎯 Kapan Pakai Yang Mana?

### Gunakan **Create Manual** Jika:
- ✅ Tambah siswa baru 1-2 orang saja
- ✅ Data siswa belum lengkap (cuma ada NISN dan Nama)
- ✅ Data akan dilengkapi siswa sendiri nanti
- ✅ Tidak ada file Excel

### Gunakan **Import Excel** Jika:
- ✅ Tambah siswa baru massal (10+ siswa)
- ✅ Sudah ada data lengkap di Excel
- ✅ Punya NISN, NIK, dan Nama Ayah
- ✅ Butuh import cepat dan efisien
- ✅ Ingin tracking error per data

---

## 🔐 Keamanan

### Create Manual:
- Password default = NISN (mudah ditebak!)
- Harus segera diganti oleh siswa

### Import Excel:
- Password default = `password123` (lebih secure karena sama semua)
- Bisa diinfokan ke semua siswa sekaligus
- Harus segera diganti oleh siswa

**Best Practice**:
- Setelah import, segera informasikan ke siswa:
  - Username: NISN mereka
  - Password: `password123`
  - Instruksi: Ganti password saat login pertama

---

## 📈 Performance

### Create Manual:
- 1 siswa = 3 query (User, Siswa, Ortu)
- Tidak ada optimasi khusus
- Cukup untuk tambah sedikit data

### Import Excel:
- **Batch Insert**: 100 records per batch
- **Chunk Reading**: 100 rows per chunk (hemat memory)
- **Database Transaction**: Per row (jika 1 gagal, tidak affect yang lain)
- Efisien untuk import ratusan data

**Contoh**:
- Import 500 siswa:
  - Tanpa batch: 1500 query (500 User + 500 Siswa + 500 Ortu)
  - Dengan batch: ~15 batch (500/100 = 5 batch × 3 table = 15 batch)
  - Lebih cepat dan efisien!

---

## 🐛 Troubleshooting

### Error: "NISN sudah terdaftar"
**Create Manual**: Langsung gagal, harus ganti NISN
**Import Excel**: Row tersebut di-skip, baris lain tetap jalan

### Error: "NIK sudah terdaftar"
**Create Manual**: Tidak ada validasi NIK, tidak akan terjadi
**Import Excel**: Row tersebut di-skip, detail error ditampilkan

### Error: "Jenis Kelamin tidak valid"
**Create Manual**: Harus L atau P di dropdown
**Import Excel**: Bisa L/P/Laki-laki/Perempuan, auto-normalize

### Error: "NISN harus 10 digit"
**Create Manual**: Tidak ada validasi format
**Import Excel**: Validasi ketat, harus 10 digit angka

---

## 💡 Kesimpulan

**Import Excel** adalah fitur yang:
1. ✅ Lebih lengkap validasinya
2. ✅ Lebih aman (validasi format NISN/NIK)
3. ✅ Lebih cepat untuk data banyak
4. ✅ Lebih informatif error handling
5. ✅ Sudah terisi sebagian data Ortu (Nama Ayah)
6. ✅ Tracking progress realtime
7. ✅ Batch processing untuk performa

**Create Manual** lebih cocok untuk:
1. ✅ Tambah data sedikit (1-2 siswa)
2. ✅ Data belum lengkap
3. ✅ Tidak punya Excel

**Rekomendasi**: 
- Gunakan **Import Excel** untuk siswa baru massal (awal tahun ajaran)
- Gunakan **Create Manual** untuk siswa pindahan/mutasi satu-satu
