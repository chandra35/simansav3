# UI Button Style Guide

Panduan singkat ini dibuat supaya tombol di SIMANSA tetap mudah dibaca, konsisten, dan tidak tenggelam di atas panel berwarna atau background gradient.

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
- evaluasi screenshot desktop dan mobile sebelum final
