# Standar Tabel Responsif SIMANSA

Dokumen ini adalah acuan untuk daftar data berbasis DataTables pada modul admin.

## Prinsip utama

- Utamakan keterbacaan data, bukan memaksa semua kolom tampil dalam ruang sempit.
- Mode tabel ditentukan dari **lebar area kartu tabel**, bukan hanya lebar layar. Sidebar pada tablet dapat membuat area konten jauh lebih sempit.
- Gunakan breakpoint lebar kartu `900px`: di bawah nilai ini gunakan mode ringkas (collapse); di atasnya gunakan tabel desktop lengkap.

## Desktop / ruang lebar

- Tampilkan seluruh kolom sesuai kebutuhan operasional.
- Kolom **Aksi** berada paling kanan dan memakai dropdown ringkas.
- Kolom Aksi boleh sticky agar tetap tersedia ketika tabel digeser horizontal.
- Header tabel memakai latar `#f5f8fc`, huruf kapital kecil, bobot 800, warna `#60718d` dan ukuran sekitar `.69rem`.
- Sel data memakai ukuran sekitar `.86rem`, padding `14px 11px`, dengan garis pemisah `#edf1f6`.
- Pagination mengikuti tampilan AdminLTE/default: border abu-abu, teks biru standar, tanpa efek biru yang berlebihan.

## Tablet portrait dan mobile / ruang sempit

- Tampilkan hanya kolom identitas inti di baris utama: kontrol collapse, nomor, kode, dan nama.
- Seluruh metadata lain (kurikulum, struktur, fase, rumpun, JP, integrasi, status) masuk ke detail collapse.
- Aksi juga masuk ke detail collapse. Pada mode ini gunakan tombol ikon, tersusun dari kiri dan dapat membungkus bila perlu.
- Jangan memakai `table-layout: fixed` untuk memaksa semua kolom desktop ke ruang sempit; ini menyebabkan teks terpecah per huruf.
- Detail collapse memakai pasangan label–nilai yang rapi, label minimum `92px`, label huruf kapital kecil, dan garis pemisah ringan antaritem.

## Implementasi DataTables

```javascript
const tableCard = $('.nama-kartu-tabel');
const useCompactTable = tableCard.innerWidth() < 900;

$('#nama-tabel').DataTable({
  responsive: useCompactTable,
  scrollX: !useCompactTable,
  autoWidth: false,
});
```

- Beri kelas `all` pada kolom inti saat mode ringkas.
- Beri kelas `none` pada kolom metadata dan aksi saat mode ringkas supaya masuk child row.
- Jangan merender dua mekanisme aksi di baris desktop; desktop hanya dropdown, mode ringkas hanya aksi ikon di child row.

## Acuan implementasi

Implementasi referensi berada di `resources/views/admin/mapel/index.blade.php`.
Gunakan pola tersebut untuk modul lain, lalu sesuaikan kolom inti dan nama kelasnya tanpa mengubah prinsip responsif di atas.
