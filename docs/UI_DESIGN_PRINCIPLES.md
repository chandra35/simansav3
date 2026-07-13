# Catatan Penting UI SIMANSA

Dokumen ini menjadi acuan saat mengedit menu/fitur lama atau membuat menu/fitur baru di SIMANSA.

## Prinsip Utama

Fondasi visual SIMANSA mengikuti AdminLTE default. Custom CSS hanya boleh memperhalus tampilan, bukan mengganti perilaku dasar komponen global.

Tampilan yang disukai dan dijadikan standar:

- sidebar ungu-biru tetap tegas
- topbar gradient biru ke teal tetap dipakai
- halaman memakai background abu sangat muda
- card putih bersih dengan border halus dan shadow lembut
- card berwarna seperti `bg-primary`, `bg-success`, `bg-gradient-primary`, dan sejenisnya harus tetap mengikuti warna aslinya
- teks harus kontras dan terbaca jelas
- tombol solid lebih diutamakan daripada outline yang pucat
- layout operasional ringkas, rapi, dan tidak terlalu dekoratif

## Aturan Wajib Saat Edit atau Buat Fitur

1. Jangan override komponen global terlalu luas.

   Hindari membuat rule seperti ini jika tidak benar-benar diperlukan:

   ```css
   .card { background: ... !important; }
   .card-header { background: ... !important; }
   .btn { ... !important; }
   .content-header h1 { color: ... !important; }
   ```

   Jika harus mengatur global, pastikan tidak merusak class bawaan AdminLTE seperti `bg-*`, `bg-gradient-*`, `btn-*`, `card-outline`, `small-box`, dan `info-box`.

2. Pakai class khusus per halaman atau per modul.

   Contoh yang aman:

   ```html
   <div class="simansa-siswa-management">
       ...
   </div>
   ```

   Lalu CSS-nya:

   ```css
   .simansa-siswa-management .simansa-hero { ... }
   .simansa-siswa-management .simansa-stat-card { ... }
   ```

   Pola ini mencegah style satu fitur menabrak fitur lain.

3. Biarkan AdminLTE bekerja untuk komponen standar.

   Untuk card biasa:

   ```html
   <div class="card">
       <div class="card-header">
           <h3 class="card-title">Judul</h3>
       </div>
       <div class="card-body">...</div>
   </div>
   ```

   Untuk card berwarna:

   ```html
   <div class="card bg-gradient-primary text-white">
       ...
   </div>
   ```

   Jangan paksa card berwarna menjadi putih.

4. Hero/banner boleh custom, tapi harus scoped.

   Boleh pakai hero seperti SMART-Q:

   ```html
   <div class="card bg-gradient-primary text-white">
       ...
   </div>
   ```

   Atau hero custom seperti Data Siswa, asalkan dibungkus class modul:

   ```html
   <div class="simansa-siswa-management">
       <div class="simansa-hero">...</div>
   </div>
   ```

5. Warna utama yang direkomendasikan.

   - Primary: biru `#2563eb` atau AdminLTE `btn-primary`
   - Accent: teal `#0f766e`
   - Success: hijau AdminLTE / emerald
   - Warning: amber/oranye
   - Danger: merah
   - Text utama: slate gelap
   - Background halaman: abu muda

   Jangan membuat palet baru untuk tiap fitur kecuali ada alasan kuat.

6. Tombol harus langsung terbaca.

   Gunakan:

   ```html
   <button class="btn btn-primary">Simpan</button>
   <button class="btn btn-success">Proses</button>
   <button class="btn btn-secondary">Batal</button>
   ```

   Untuk tombol custom yang sudah ada:

   ```html
   <button class="btn simansa-btn-strong">Simpan</button>
   <button class="btn simansa-btn-contrast">Aksi</button>
   ```

   Hindari `btn-outline-*` di atas background berwarna atau gradient jika kontrasnya kurang.

## Checklist Sebelum Commit

- Judul halaman terbaca jelas.
- Card berwarna tidak berubah putih.
- Teks putih tidak berada di atas background putih.
- Tombol utama terlihat tegas.
- Tabel tetap ringkas dan mudah dibaca.
- Mobile tidak berantakan.
- CSS baru memakai scope modul, bukan selector global luas.
- Jika mengubah `public/css/custom-compact.css`, cek minimal halaman:
  - Dashboard
  - Data Siswa
  - SMART-Q
  - Kenaikan Kelas
  - Cetak Dokumen

## Catatan Teknis

Rule global di `public/css/custom-compact.css` harus hati-hati. Pola aman untuk card:

```css
.card:not([class*="bg-"]) {
    background: var(--bg-card) !important;
}

.card-header:not([class*="bg-"]) {
    background: ...;
}
```

Pola di atas menjaga card normal tetap rapi, tetapi card `bg-*` dan `bg-gradient-*` tetap memakai warna default.

## Keputusan Desain

Mulai sekarang, desain seperti halaman SMART-Q setelah perbaikan menjadi baseline:

- bersih
- tegas
- tidak terlalu ramai
- menggunakan warna bawaan yang sudah cocok
- custom hanya untuk memperhalus, bukan mengganti seluruh gaya

