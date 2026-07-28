# Perubahan Terakhir MAN 1 Metro

Tanggal pembaruan: 28 Juli 2026, zona waktu Asia/Jakarta.

## Ringkasan terkini

Perubahan aplikasi terakhir sebelum pembuatan dokumentasi ini berada pada commit SIMANSA `9c283eb` (`feat: rapikan menu dan metadata asal kelas`) dan telah di-push serta di-deploy ke produksi.

Perubahan yang dilakukan:

1. Sidebar Akademik dirapikan menjadi urutan:
   - Manajemen Kelas
   - Mutasi Siswa
   - Cetak Dokumen
2. Daftar siswa pada detail rombel diurutkan alfabetis berdasarkan nama.
3. Detail rombel tingkat XI dan XII menampilkan metadata `Asal kelas` di bawah nama siswa.
4. Metadata asal kelas mengambil rombel tingkat sebelumnya pada tahun pelajaran sebelumnya.
5. Tingkat X tidak menampilkan metadata asal kelas.
6. Jika histori tidak ditemukan, sistem menampilkan `Belum tercatat`.
7. Perubahan sebelumnya pada commit `e21fa22` membuat nama kelas di tabel data siswa menjadi link menuju detail rombel hanya bagi user dengan permission `view-detail-kelas`.

## File aplikasi yang terakhir diubah

- `app/Http/Controllers/Admin/KelasController.php`
- `config/adminlte.php`
- `resources/views/admin/kelas/show.blade.php`
- `tests/Unit/ClassDetailStudentMetadataTest.php`
- `app/Http/Controllers/Admin/SiswaController.php`
- `tests/Unit/StudentClassLinkAccessTest.php`

## Validasi terakhir

- Unit test SIMANSA: 58 lulus, 300 assertions.
- Blade template berhasil dikompilasi.
- GitHub dan VM telah sinkron pada commit aplikasi `9c283eb28b1ef24b95751a8c3be563f50f1829db`.
- Maintenance mode produksi: OFF.
- Halaman login produksi: HTTP 200.

## Dokumentasi sesi baru

Pada sesi ini dibuat:

- `MAN1METRO.md` sebagai peta proyek, stack, database, metode kredensial, lokasi produksi, serta aturan commit–push–deploy.
- `perubahan-terakhir.md` sebagai handoff singkat pekerjaan terbaru.

Commit yang memuat dokumentasi ini dapat dilihat dengan:

```bash
git log -1 --oneline -- MAN1METRO.md perubahan-terakhir.md
```

## Instruksi untuk pekerjaan berikutnya

1. Baca `MAN1METRO.md`.
2. Baca file ini.
3. Periksa status Git sebelum menyentuh file.
4. Kerjakan hanya aplikasi yang diminta.
5. Setelah perubahan selesai, wajib test, commit, push, deploy, dan verifikasi.
6. Ganti isi ringkasan file ini dengan perubahan terbaru atau tambahkan entri terbaru di bagian paling atas.
