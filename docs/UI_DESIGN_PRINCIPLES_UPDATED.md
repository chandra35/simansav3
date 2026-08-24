# Catatan Penting UI SIMANSA

Dokumen ini menjadi acuan saat mengedit menu/fitur lama atau membuat menu/fitur baru di SIMANSA.

## Prinsip Utama

Fondasi visual SIMANSA mengikuti AdminLTE default. Custom CSS hanya boleh memperhalus tampilan, memperkuat identitas visual, dan merapikan pengalaman pengguna tanpa mengganti perilaku dasar komponen global.

Acuan utama desain adalah gaya halaman **Data Siswa / Manajemen Peserta Didik**.

Tampilan yang dijadikan standar:

- sidebar ungu-biru tetap tegas
- topbar gradient biru ke teal tetap dipakai
- halaman memakai background abu sangat muda
- judul halaman ringkas, jelas, dan tidak terlalu besar
- hero/banner utama memakai biru cerah atau gradient biru yang lembut
- hero harus ringkas, informatif, dan tidak menyerupai landing page
- card statistik memakai background putih, border halus, radius sedang, dan shadow lembut
- card operasional memakai background putih dengan header yang jelas
- card utama boleh memakai aksen garis atas biru/ungu tipis
- teks utama memakai slate gelap dengan kontras tinggi
- teks sekunder memakai abu kebiruan
- angka statistik boleh memakai warna semantik yang konsisten
- tombol utama memakai warna solid dan mudah dikenali
- layout operasional ringkas, rapi, padat, dan tidak terlalu dekoratif
- ruang kosong harus proporsional dan tidak membuat halaman terasa kosong

## Karakter Visual yang Wajib Dipertahankan

- warna utama SIMANSA adalah biru cerah
- ungu-biru digunakan untuk sidebar, navigasi aktif, dan aksen identitas
- teal digunakan sebagai aksen sekunder dan pasangan gradient
- putih digunakan untuk card dan area kerja utama
- abu sangat muda digunakan untuk background halaman dan panel filter
- hijau digunakan untuk status berhasil, aktif, atau data lengkap
- amber/oranye digunakan untuk peringatan atau data yang belum lengkap
- merah hanya digunakan untuk bahaya, kegagalan, atau tindakan destruktif
- jangan membuat palet baru untuk setiap modul
- jangan menggunakan terlalu banyak warna kuat dalam satu area

## Struktur Halaman Standar

Urutan visual halaman operasional wajib mengikuti halaman Data Siswa:

1. Judul halaman dan breadcrumb
2. Satu hero/banner utuh yang menjelaskan fungsi modul
3. Statistik utama dalam card putih
4. Card operasional utama
5. Filter, form, tabel, atau konten kerja

Hero tidak wajib pada semua halaman. Jika digunakan, hero harus memiliki fungsi nyata, bukan hanya menampilkan sapaan.

### Pola markup wajib

Gunakan struktur berikut saat halaman membutuhkan hero:

```blade
@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-icon text-primary"></i> Judul Modul</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="...">Dashboard</a></li>
                <li class="breadcrumb-item active">Judul Modul</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="scope-modul">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">...</div>
                <div class="col-lg-4 mt-3 mt-lg-0">...</div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">...</div>
</div>
@stop
```

Ketentuan struktur:

- `content_header` hanya berisi judul dan breadcrumb; jangan menaruh hero di sana.
- Hero berada di awal `content`, memakai satu card gradient penuh selebar area konten.
- Informasi atau statistik sisi kanan harus tetap berada di dalam card gradient yang sama.
- Jangan memisahkan hero menjadi panel gradient dan chip/card putih di sampingnya.
- Card operasional utama memakai `card card-outline card-primary` dengan header putih.
- Gunakan grid Bootstrap agar kolom kanan otomatis turun di bawah deskripsi pada layar sempit.

## Hero dan Banner

Hero atau banner diperbolehkan dengan ketentuan:

- memakai `card bg-gradient-primary text-white` seperti halaman Data Siswa
- berbentuk satu bidang utuh selebar konten, bukan dua card yang terpisah
- tinggi ringkas dan tidak mendominasi layar
- memiliki judul, deskripsi singkat, dan bila perlu statistik penting
- tidak menggunakan ilustrasi besar
- tidak memiliki ruang kosong berlebihan
- tidak dibuat seperti landing page
- seluruh custom style harus memakai scope modul
- jika perlu penyesuaian radius/shadow, selector harus diawali wrapper modul dan tidak boleh mengubah `.card` global

## Card Statistik

Card statistik harus mengikuti karakter berikut:

- background putih
- border abu tipis
- radius sedang
- shadow lembut
- label statistik kecil dan tegas
- angka statistik lebih besar dan mudah dipindai
- deskripsi singkat memakai teks sekunder
- warna angka mengikuti makna data, bukan dekorasi
- maksimal 3–5 card dalam satu baris desktop
- turun menjadi 1–2 kolom pada mobile

## Card Operasional

Card operasional utama harus:

- memakai background putih
- memiliki header yang jelas
- boleh memakai `card-outline card-primary` atau aksen garis atas tipis
- menempatkan judul di kiri dan tombol aksi di kanan
- menjaga isi tetap ringkas dan mudah dipindai
- menghindari terlalu banyak nested card
- memakai spacing konsisten

## Filter, Form, dan Tabel

Filter dan form harus:

- menggunakan Bootstrap grid
- memakai label di atas input
- textarea biasa harus dapat diubah tingginya secara vertikal; jangan menaruh `height: auto !important` pada selector `.form-control` global karena akan memblokir drag native browser
- text editor visual harus mempertahankan resize handle bawaan editornya
- memiliki tinggi input yang konsisten
- maksimal empat kolom pada desktop
- turun rapi pada tablet dan mobile
- menempatkan tombol Reset, Export, atau aksi terkait secara jelas
- memakai panel abu sangat muda bila perlu membedakan area filter

Tabel harus:

- tetap ringkas dan operasional
- mudah dipindai
- menggunakan badge status yang lembut
- menjaga tombol aksi tetap ringkas
- tidak mengubah setiap baris menjadi card besar pada desktop

Pagination harus memakai gaya AdminLTE/Bootstrap yang netral secara global:

- nomor dan kontrol pagination menggunakan teks abu gelap dengan latar putih
- halaman aktif menggunakan abu muda, bukan biru atau warna aksen lain
- jangan membuat pagination per halaman dengan gradient, pill, gap dekoratif, atau kombinasi warna khusus

### Standar DataTables Global

Semua DataTables mengikuti partial standar AdminLTE SIMANSA, bukan CSS lokal per halaman, kecuali ada kebutuhan data yang benar-benar khusus.

- empty-state hanya muncul satu kali di dalam tabel: gunakan Bahasa Indonesia yang ringkas, misalnya `Tidak ada data tersedia`; `infoEmpty` harus kosong agar tidak muncul teks kedua di luar tabel
- kontrol `Tampilkan _MENU_ data` memakai lebar natural/compact dan satu indikator dropdown native; jangan menambah background arrow atau icon kedua
- baris footer DataTables memiliki padding bawah agar informasi dan pagination tidak menempel pada batas card
- header tabel memakai pembatas bawah abu-abu halus tetapi tegas untuk membedakan header dari isi
- pagination tetap netral sesuai aturan global di atas

## Tombol

Tombol harus langsung terbaca dan memiliki hierarki jelas.

- aksi utama memakai `btn-primary`
- aksi positif memakai `btn-success`
- aksi netral memakai `btn-secondary`
- aksi peringatan memakai `btn-warning`
- aksi destruktif memakai `btn-danger`
- tombol solid lebih diutamakan daripada outline yang pucat
- tombol di header card boleh memakai ukuran `btn-sm`
- icon digunakan secukupnya
- radius tombol tetap mengikuti karakter AdminLTE dan tidak dibuat terlalu bulat
- jangan memakai warna yang sama untuk semua jenis aksi

## Aturan Wajib Saat Edit atau Buat Fitur

1. Jangan override komponen global terlalu luas.
2. Pakai class khusus per halaman atau per modul.
3. Biarkan AdminLTE bekerja untuk komponen standar.
4. Jangan paksa card berwarna menjadi putih.
5. Jangan merusak class bawaan seperti `bg-*`, `bg-gradient-*`, `btn-*`, `card-outline`, `small-box`, dan `info-box`.
6. Custom CSS hanya boleh memperhalus tampilan.
7. Hindari selector global luas pada `.card`, `.card-header`, `.btn`, `.form-control`, dan judul halaman.
8. Jangan memakai `!important` kecuali benar-benar diperlukan dan tetap scoped.
9. Jangan membuat komponen baru jika komponen AdminLTE/Bootstrap yang ada sudah cukup.
10. Jangan membuat gaya baru yang bertentangan dengan halaman Data Siswa.

## Scope CSS Per Modul

Semua custom CSS wajib memakai wrapper khusus per halaman atau modul agar style tidak menabrak fitur lain.

Custom style yang dibuat untuk satu modul tidak boleh memengaruhi:

- Dashboard
- Data Siswa
- SMART-Q
- Kenaikan Kelas
- Cetak Dokumen
- Profil GTK
- Dashboard GTK

## Warna Utama

Palet acuan:

- Primary: `#2563eb`
- Primary terang: `#3b82f6`
- Ungu navigasi: mengikuti sidebar SIMANSA
- Accent teal: `#0f766e`
- Success: hijau AdminLTE / emerald
- Warning: amber/oranye
- Danger: merah
- Text utama: slate gelap
- Text sekunder: abu kebiruan
- Background halaman: abu sangat muda
- Card: putih
- Border: abu kebiruan sangat tipis

Jangan membuat palet baru untuk tiap fitur kecuali ada alasan kuat.

## Spacing, Radius, dan Shadow

- gunakan spacing yang konsisten
- padding card harus proporsional
- radius card sedang, tidak terlalu bulat
- shadow lembut dan tidak gelap
- hindari border warna-warni berlebihan
- hindari garis neon
- hindari efek glassmorphism berlebihan
- jangan membuat hero, card, atau filter terlalu tinggi

## Responsive

Setiap perubahan harus tetap rapi pada desktop, laptop, tablet, dan mobile.

Pada mobile:

- statistik turun menjadi satu atau dua kolom
- tombol boleh wrap
- card header boleh menjadi dua baris
- filter menjadi satu kolom
- hero tetap ringkas
- tabel tidak merusak layout
- tidak ada horizontal scroll dari struktur halaman utama

## Checklist Sebelum Commit

- judul halaman terbaca jelas
- breadcrumb rapi
- hero tidak terlalu besar
- hero memiliki fungsi
- warna hero konsisten dengan halaman Data Siswa
- card statistik konsisten
- card berwarna tidak berubah putih
- teks putih tidak berada di atas background putih
- tombol utama terlihat tegas
- tombol sekunder tidak bersaing dengan tombol utama
- filter tersusun rapi
- tabel tetap ringkas dan mudah dibaca
- spacing antarelemen konsisten
- mobile tidak berantakan
- CSS baru memakai scope modul
- tidak ada selector global berbahaya
- tidak ada palet baru yang tidak perlu
- halaman tetap terasa sebagai bagian dari SIMANSA

## Catatan Teknis

Rule global di `public/css/custom-compact.css` harus digunakan dengan sangat hati-hati.

Card normal boleh dirapikan secara global selama selector tidak memengaruhi card dengan class `bg-*`, `bg-gradient-*`, `small-box`, `info-box`, `alert`, `callout`, dan `card-outline`.

Jika memakai `!important`, selector harus spesifik pada modul dan tidak boleh mengubah tampilan komponen global lain.

## Keputusan Desain

Mulai sekarang, desain seperti halaman **Data Siswa / Manajemen Peserta Didik** menjadi baseline utama SIMANSA.

Ciri utamanya:

- biru cerah sebagai warna utama konten
- sidebar ungu-biru yang tegas
- topbar gradient biru ke teal
- hero ringkas dan informatif
- card statistik putih
- card operasional yang jelas
- tombol solid
- filter terstruktur
- spacing rapi
- border halus
- shadow lembut
- desain modern tanpa meninggalkan AdminLTE
- custom CSS hanya memperhalus, bukan mengganti seluruh gaya

Saat mengedit UI, jangan mendesain ulang SIMANSA dari nol. Gunakan AdminLTE sebagai fondasi dan jadikan halaman Data Siswa sebagai referensi visual utama.
