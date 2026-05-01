# UI Button Style Guide

Panduan singkat ini dibuat supaya tombol di SIMANSA tetap mudah dibaca, konsisten, dan tidak tenggelam di atas panel berwarna atau background gradient.

## Kebijakan Fondasi UI

Mulai sekarang, fondasi global tampilan SIMANSA harus mengikuti **AdminLTE default** sejauh mungkin.

Artinya:

- jangan override global untuk `.card`, `.card-header`, `.btn`, `.nav-link`, `.content-wrapper`, atau komponen shell utama lainnya
- navbar, sidebar, pagination, form control, card dasar, dan table dasar dibiarkan mengikuti AdminLTE
- styling khusus hanya boleh diterapkan lewat class lokal / namespace seperti:
  - `simansa-dashboard-*`
  - `simansa-student-*`
  - `simansa-announcement-*`
  - `simansa-filter-*`

Tujuan kebijakan ini:

- mencegah bentrok visual saat fitur baru dibuat
- membuat halaman baru tampil stabil tanpa perlu perbaikan tambahan
- mengurangi bug seperti tombol putih, card aneh, pagination rusak, atau teks tidak terbaca

## Wajib Dibaca Saat Menambah atau Mengedit Fitur

Dokumen ini **wajib dibaca** ketika:

1. membuat fitur / halaman baru
2. mengedit fitur lama
3. merapikan UI/UX halaman yang sudah ada

Saat sedang menyentuh satu fitur, sekalian cek apakah:

- tombol di halaman itu sudah mengikuti guide ini
- card / filter / toolbar masih terlalu bergantung pada override global lama
- ada bagian kecil yang bisa langsung dirapikan sambil jalan

Prinsipnya: **nyicil perbaikan UI saat menyentuh fitur**, tapi tetap pakai fondasi yang aman.

## Tujuan

- Menjaga keterbacaan tombol pada semua halaman admin dan siswa
- Menghindari tombol outline yang "hilang" di background terang atau kebiruan
- Menyamakan gaya CTA utama dan aksi sekunder

## Aturan Dasar

1. Tombol aksi utama gunakan style solid dengan kontras tinggi.
2. Tombol yang ditempatkan di dalam panel berwarna, alert, chip, atau area gradient jangan memakai `btn-outline-primary` polos.
3. Tombol outline hanya dipakai jika background di belakangnya benar-benar netral dan kontras border-nya tetap jelas.
4. Pastikan label tombol terbaca dalam sekali lihat, terutama untuk aksi penting seperti `Simpan`, `Tambah`, `Reset`, `Hapus Filter`, dan `Lihat Detail`.

## Pola Yang Dipakai

### 1. Primary CTA

Gunakan untuk:

- `Simpan`
- `Tambah`
- `Proses`
- `Lanjutkan`

Contoh class:

```html
<button class="btn simansa-btn-strong">Simpan</button>
```

Karakter:

- background solid / gradient biru gelap
- teks putih
- font-weight 600
- hover tetap kontras

### 2. Contrast Button on Tinted Surface

Gunakan untuk tombol yang berada di:

- `alert-info`
- panel filter statistik
- panel biru muda / gradient lembut
- header kecil berwarna

Contoh class:

```html
<a class="btn simansa-btn-contrast">Hapus Filter Statistik</a>
```

Karakter:

- background putih
- border biru tegas
- teks biru tua
- hover menjadi solid biru

### 3. Neutral Secondary

Gunakan untuk:

- `Batal`
- `Tutup`
- `Kembali`

Contoh:

```html
<button class="btn btn-secondary">Batal</button>
```

## Yang Sebaiknya Dihindari

Jangan pakai pola ini di panel berwarna:

```html
<a class="btn btn-outline-primary">Aksi</a>
```

Masalahnya:

- border tipis sering hilang
- teks biru muda kalah dengan background
- di layar tertentu terlihat seperti disabled padahal aktif

## Checklist Sebelum Selesai

- Tombol masih terbaca saat dilihat cepat
- Kontras teks vs background jelas
- Hover state tidak menurunkan keterbacaan
- Di mobile, tombol penting tidak terlalu sempit
- Outline button tidak menyaru dengan elemen dekoratif

## Catatan Implementasi

Saat menambah halaman baru:

- prioritaskan `simansa-btn-strong` untuk CTA utama
- gunakan `simansa-btn-contrast` jika tombol berada di dalam panel berwarna
- gunakan pagination Bootstrap/AdminLTE, jangan biarkan view jatuh ke style bawaan yang tidak jelas
- kalau butuh card/hero khusus, bungkus dengan class lokal halaman, jangan ubah `.card` global
- evaluasi screenshot desktop dan mobile sebelum final

Saat mengedit halaman lama:

- hindari menambah override baru di `resources/views/vendor/adminlte/master.blade.php` kecuali benar-benar utilitas global yang aman
- kalau menemukan style global yang bentrok, prioritaskan pindahkan ke style lokal halaman
- untuk komponen baru, lebih baik mulai dari default AdminLTE lalu poles lokal seperlunya

## Content Header & Breadcrumb

Mulai sekarang, area `content_header` juga mengikuti prinsip **AdminLTE-first**.

Aturannya:

- jangan bungkus semua `content_header` secara global dengan hero custom
- file wrapper global `resources/views/vendor/adminlte/partials/cwrapper/cwrapper-default.blade.php` harus merender `@yield('content_header')` secara normal
- kalau sebuah halaman butuh hero visual, hero itu harus dibuat **lokal di halaman tersebut**, bukan dipaksakan ke semua halaman
- breadcrumb standar harus tetap memakai pola AdminLTE / Bootstrap biasa:

```html
@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Judul Halaman</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Halaman</li>
            </ol>
        </div>
    </div>
@endsection
```

Kalau ingin hero khusus, gunakan class lokal seperti:

- `simansa-page-hero`
- `simansa-hero`
- atau namespace fitur lain

Tetapi pemakaiannya harus **eksplisit per halaman**, misalnya pada dashboard, halaman ringkasan, atau halaman landing modul tertentu.

Tujuannya:

- breadcrumb tidak hilang saat fitur baru dibuat
- halaman CRUD/detail/form tetap stabil dan familiar
- hero visual hanya muncul di tempat yang memang perlu

## Toolbar & Filter

Selain tombol, ada dua area yang sekarang perlu ikut konsisten karena paling sering dipakai admin:

### 1. Toolbar halaman

Gunakan pola:

- aksi utama di kanan memakai `simansa-btn-strong`
- aksi sekunder yang masih penting boleh `btn-info` atau `simansa-btn-contrast`
- jangan menumpuk terlalu banyak tombol kecil tanpa pengelompokan

Contoh:

```html
<div class="simansa-toolbar">
  <div></div>
  <div class="simansa-toolbar__group">
    <a class="btn btn-sm simansa-btn-contrast">Permission Matrix</a>
    <a class="btn btn-sm simansa-btn-strong">Tambah User</a>
  </div>
</div>
```

### 2. Filter operasional

Untuk halaman seperti `Data Siswa`, `Cetak Dokumen`, `Data User`, dan dashboard laporan:

- bungkus filter dalam `simansa-filter-panel`
- beri label jelas dengan icon kecil
- pakai tombol pencarian yang kontras
- tampilkan ringkasan hasil/seleksi dekat area hasil supaya admin tidak perlu scan ulang layar

## Card & Surface

- gunakan `simansa-management-card` atau `simansa-surface-card` untuk area kerja utama
- hindari card kecil bertumpuk tanpa hirarki
- ringkasan cepat sebaiknya pakai mini stat atau stat card, bukan alert biasa
- footer aksi form gunakan CTA kuat di kanan dan aksi kembali/batal yang netral
