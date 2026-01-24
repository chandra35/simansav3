# Cara Kerja Import Siswa dari Excel

## Ringkasan Singkat
Import siswa adalah fitur untuk membuat banyak data siswa sekaligus (bulk insert) menggunakan file Excel. **Prosesnya SAMA PERSIS dengan create siswa biasa**, perbedaannya hanya pada **jumlah data yang diinput** (1 vs banyak).

---

## Perbandingan: Import vs Create Siswa

### 1. **Create Siswa Biasa** (Manual via Form)

**Lokasi**: `app/Http/Controllers/Admin/SiswaController.php` → `store()` method

**Proses**:
```
User mengisi form → Submit → Validasi → Buat 1 User → Buat 1 Siswa → Buat 1 Ortu
```

**Kode**:
```php
public function store(Request $request)
{
    // 1. Validasi input form
    $validated = $request->validate([
        'nisn' => 'required|unique:users,username|size:10',
        'nama_lengkap' => 'required',
        'jenis_kelamin' => 'required|in:L,P',
        'nik' => 'required|unique:siswa,nik|size:16',
    ]);

    DB::beginTransaction();
    try {
        // 2. Buat User
        $user = User::create([
            'username' => $request->nisn,
            'email' => $request->nisn . '@siswa.simansa.sch.id',
            'password' => Hash::make($request->nisn),
        ]);
        $user->assignRole('Siswa');

        // 3. Buat Siswa
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nisn' => $request->nisn,
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        // 4. Buat Ortu (kosong dulu)
        Ortu::create(['siswa_id' => $siswa->id]);

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil ditambahkan'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

**Karakteristik**:
- ✅ 1 siswa per transaksi
- ✅ Input manual via form
- ✅ Validasi langsung Laravel Request
- ✅ Error langsung muncul di form
- ✅ User melihat form input

---

### 2. **Import Siswa** (Bulk via Excel)

**Lokasi**: `app/Imports/SiswaImport.php` → `collection()` method

**Proses**:
```
User upload Excel → Parse Excel → Loop setiap baris → Validasi → Buat User + Siswa + Ortu
                                  ↓ (paralel untuk 100 baris)
                            Batch Processing
```

**Kode**:
```php
public function collection(Collection $rows)
{
    $rowNumber = 1; // Excel row tracker
    
    foreach ($rows as $row) {
        $rowNumber++;
        
        DB::beginTransaction();
        try {
            // 1. Validasi custom (format, duplikasi)
            $this->validateRequiredFields($row, $rowNumber);
            
            // 2. Buat User
            $user = User::create([
                'username' => $row['nisn'],
                'email' => $this->generateEmail($row['nisn']),
                'password' => Hash::make('password123'), // Default password
            ]);
            $user->assignRole('Siswa');
            
            // 3. Buat Siswa
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nisn' => $row['nisn'],
                'nik' => $row['nik'],
                'nama_lengkap' => $row['nama_lengkap'],
                'jenis_kelamin' => strtoupper($row['jenis_kelamin']),
                'data_diri_completed' => false,
                'data_ortu_completed' => false,
            ]);
            
            // 4. Buat Ortu (kosong/NULL, siswa yang melengkapi)
            Ortu::create([
                'siswa_id' => $siswa->id,
                // Semua field NULL, siswa akan melengkapi sendiri
            ]);
            
            DB::commit();
            $this->results['success']++;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->results['failed']++;
            $this->results['errors'][] = [
                'row' => $rowNumber,
                'nisn' => $row['nisn'] ?? '-',
                'nama' => $row['nama_lengkap'] ?? '-',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $this->results;
}
```

**Karakteristik**:
- ✅ Banyak siswa per transaksi (bulk)
- ✅ Input via Excel file
- ✅ Validasi custom di dalam loop
- ✅ Error dikumpulkan dalam array
- ✅ User tidak melihat form input
- ✅ Batch processing (100 record per batch)
- ✅ Chunk reading (100 row per chunk)

---

## Perbedaan Utama

| Aspek | Create Siswa Biasa | Import Siswa (Excel) |
|-------|-------------------|---------------------|
| **Input** | Form manual | File Excel |
| **Jumlah** | 1 siswa | Banyak siswa sekaligus |
| **Validasi** | Laravel Request Validation | Custom validation dalam loop |
| **Password** | NISN (default) | **NISN (default)** ✅ |
| **Error Handling** | Langsung di form | Array dikumpulkan, ditampilkan di table |
| **Transaction** | 1 transaction per siswa | 1 transaction per baris Excel |
| **Performance** | Biasa (1 siswa) | Optimized dengan batch & chunk |
| **User Experience** | Step-by-step | Upload → Progress bar → Result |
| **Data Ortu** | **Dibuat kosong (NULL)** | **Dibuat kosong (NULL)** ✅ |
| **Email Format** | `{nisn}@siswa.simansa.sch.id` | `{nisn}@siswa.simansa.sch.id` ✅ |

---

## Perubahan Penting (Update Terbaru)

### 1. Password
**Sebelum**: `password123` (berbeda dengan create siswa)
**Sesudah**: **NISN** (sama dengan create siswa) ✅

### 2. Data Orang Tua
**Sebelum**: Langsung isi `nama_ayah` dan `status_ayah` dari Excel
**Sesudah**: **Dibuat NULL/kosong**, siswa yang melengkapi sendiri ✅

### 3. Kolom Excel
**Sebelum**: 5 kolom (NISN, NIK, Nama Lengkap, Jenis Kelamin, Nama Ayah)
**Sesudah**: **4 kolom** (NISN, NIK, Nama Lengkap, Jenis Kelamin) ✅

**Alasan**: Import harus **sama persis** dengan create siswa biasa, perbedaannya hanya jumlah input.

---

## Validasi Import

### 1. Format NISN
```php
if (strlen($nisn) != 10 || !is_numeric($nisn)) {
    throw new \Exception("NISN harus 10 digit angka");
}
```

### 2. Format NIK
```php
if (strlen($nik) != 16 || !is_numeric($nik)) {
    throw new \Exception("NIK harus 16 digit angka");
}
```

### 3. Jenis Kelamin
```php
$validGenders = ['L', 'P', 'Laki-laki', 'Perempuan'];
if (!in_array($jenisKelamin, $validGenders)) {
    throw new \Exception("Jenis Kelamin harus L/P atau Laki-laki/Perempuan");
}

// Normalisasi
if ($jenisKelamin == 'Laki-laki') $jenisKelamin = 'L';
if ($jenisKelamin == 'Perempuan') $jenisKelamin = 'P';
```

### 4. Duplikasi NISN
```php
if (User::where('username', $nisn)->exists()) {
    throw new \Exception("NISN sudah terdaftar");
}
```

### 5. Duplikasi NIK
```php
if (Siswa::where('nik', $nik)->exists()) {
    throw new \Exception("NIK sudah terdaftar");
}
```

---

## Batch Processing & Chunk Reading

### Batch Insert
```php
public function batchSize(): int
{
    return 100; // Insert 100 records per query
}
```
**Keuntungan**: Lebih cepat daripada insert 1-per-1

### Chunk Reading
```php
public function chunkSize(): int
{
    return 100; // Read 100 rows per chunk
}
```
**Keuntungan**: Hemat memory untuk file Excel besar

---

## Alur Lengkap Import

```
1. User klik "Import dari Excel"
   ↓
2. User download template Excel
   ↓
3. User isi data siswa di Excel
   ↓
4. User upload file Excel
   ↓
5. Frontend: AJAX upload dengan progress bar (0% → 90%)
   ↓
6. Backend: Parse Excel file
   ↓
7. Backend: Loop setiap baris
   ↓ (untuk setiap baris)
8. Validasi format (NISN, NIK, Jenis Kelamin)
   ↓
9. Cek duplikasi (NISN, NIK)
   ↓
10. DB Transaction BEGIN
   ↓
11. Create User (dengan role Siswa)
   ↓
12. Create Siswa
   ↓
13. Create Ortu (isi nama ayah + status_ayah)
   ↓
14. DB Transaction COMMIT
   ↓ (jika berhasil)
15. results['success']++
   ↓ (jika error)
16. DB Transaction ROLLBACK
   ↓
17. results['failed']++
   ↓
18. results['errors'][] = detail error
   ↓ (setelah semua baris diproses)
19. Frontend: Progress bar 100%
   ↓
20. Frontend: Tampilkan result card
   ↓
21. Tampilkan: Berhasil | Gagal | Total
   ↓
22. Tampilkan table error (jika ada yang gagal)
   ↓
23. User klik "Lihat Data Siswa" atau "Import Lagi"
```

---

## Data yang Dibuat per Siswa

### 1. Tabel `users`
```php
- id (UUID)
- username (NISN)
- email ({nisn}@siswa.simansa.sch.id)
- password (bcrypt('password123'))
- role: 'Siswa'
- created_by (auth user id)
```

### 2. Tabel `siswa`
```php
- id (UUID)
- user_id (dari users)
- nisn (10 digit)
- nik (16 digit)
- nama_lengkap
- jenis_kelamin (L/P)
- data_diri_completed (false)
- data_ortu_completed (false)
- created_by (auth user id)
```

### 3. Tabel `ortu`
```php
- id (UUID)
- siswa_id (dari siswa)
- Semua field lain: NULL (siswa yang melengkapi)
- created_by (auth user id)
```

**Total per siswa**: 3 record (1 User + 1 Siswa + 1 Ortu kosong)

---

## Format File Excel Template

### Header Row (Baris 1)
| NISN | NIK | Nama Lengkap | Jenis Kelamin |
|------|-----|--------------|---------------|

### Sample Data (Baris 2-3)
| 0123456789 | 1234567890123456 | Ahmad Rizki Pratama | L |
| 0123456790 | 1234567890123457 | Siti Nurhaliza | P |

### Notes Section
```
CATATAN PENTING:
1. NISN harus 10 digit angka dan unik (akan digunakan sebagai username dan password)
2. NIK harus 16 digit angka dan unik
3. Jenis Kelamin: L (Laki-laki) atau P (Perempuan)
4. Semua kolom wajib diisi
5. Password default: sama dengan NISN
6. Siswa akan melengkapi data orang tua sendiri setelah login
```

---

## Password Default

Semua siswa yang diimport akan memiliki:
- **Username**: NISN
- **Password**: **NISN** (sama dengan create siswa biasa)
- **Email**: `{NISN}@siswa.simansa.sch.id`

⚠️ **PENTING**: Siswa harus melengkapi data orang tua setelah login pertama kali!

---

## Troubleshooting

### Error: NISN sudah terdaftar
**Solusi**: Cek database, NISN harus unik. Hapus atau ubah NISN yang duplikat di Excel.

### Error: NIK sudah terdaftar
**Solusi**: Cek database, NIK harus unik. Hapus atau ubah NIK yang duplikat di Excel.

### Error: NISN harus 10 digit
**Solusi**: Pastikan kolom NISN di Excel berformat TEXT (bukan NUMBER) agar tidak kehilangan leading zero.

### Error: NIK harus 16 digit
**Solusi**: Pastikan kolom NIK di Excel berformat TEXT (bukan NUMBER) agar tidak kehilangan leading zero.

---

## Tips Menggunakan Import

1. **Backup database** sebelum import massal
2. **Test dengan 2-3 baris** dulu sebelum import ratusan data
3. **Format kolom NISN dan NIK sebagai TEXT** di Excel
4. **Periksa duplikasi** sebelum upload
5. **Gunakan template yang disediakan** untuk menghindari error format
6. **Catat password default** (`password123`) untuk diberikan ke siswa
7. **Monitoring hasil import** di result card untuk memastikan semua berhasil

---

## Kesimpulan

**Import Siswa** adalah versi **bulk/massal** dari **Create Siswa** biasa. Prosesnya **SAMA PERSIS**:
- ✅ Password = NISN
- ✅ Ortu dibuat kosong/NULL
- ✅ Siswa melengkapi data sendiri
- ✅ Email format sama
- ✅ Role sama (Siswa)

**Perbedaan hanya**:
- ⚡ Create Siswa: 1 siswa per transaksi
- ⚡ Import Siswa: Banyak siswa sekaligus dengan batch processing

**Optimizations di Import**:
- ✅ Batch processing (100 per batch)
- ✅ Chunk reading (hemat memory)
- ✅ Error tracking per baris
- ✅ Progress bar UX

Gunakan **Create Siswa** untuk 1-2 siswa, gunakan **Import** untuk puluhan/ratusan siswa sekaligus.
