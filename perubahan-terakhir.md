# Perubahan Terakhir MAN 1 Metro

Tanggal pembaruan: 28 Juli 2026, zona waktu Asia/Jakarta.

## Ringkasan terkini

### Modal penugasan wali kelas

Modal `Tugaskan Wali Kelas` pada detail rombel telah diperbarui:

1. Dropdown memakai Select2 dan selalu menyediakan kolom pencarian.
2. Pencarian dapat memakai nama guru, jenis guru, atau nama rombel.
3. Kandidat hanya user aktif dengan data GTK berkategori `Pendidik` dan jenis `Guru Mapel`/`Guru BK`.
4. Tenaga kependidikan tidak lagi muncul dan ditolak pula oleh validasi server.
5. Guru yang sudah menjadi wali kelas lain tetap tersedia.
6. Di samping nama guru ditampilkan jenis guru dan metadata rombel aktif, misalnya `Wali: XII-A6`.
7. Guru yang belum memiliki rombel diberi metadata `Belum menjadi wali kelas`.
8. Tampilan modal dibuat lebih lebar, responsif, dan dilengkapi konteks rombel tujuan.

File terkait:

- `app/Http/Controllers/Admin/KelasController.php`
- `resources/views/admin/kelas/show.blade.php`
- `tests/Unit/WaliKelasAssignmentUiTest.php`

Koreksi terbaru menempatkan tiga menu operasional sebagai item pertama tepat di bawah menu Akademik dengan urutan:

1. Manajemen Kelas
2. Cetak Dokumen
3. Mutasi Siswa

Setelah tiga item tersebut, menu dilanjutkan dengan Tahun Pelajaran, Kurikulum, Mata Pelajaran, dan menu akademik lainnya. Koreksi mencakup `config/adminlte.php` dan penguatan tes urutan pada `tests/Unit/ClassDetailStudentMetadataTest.php`.

Perubahan aplikasi terakhir sebelum pembuatan dokumentasi ini berada pada commit SIMANSA `9c283eb` (`feat: rapikan menu dan metadata asal kelas`) dan telah di-push serta di-deploy ke produksi.

Perubahan yang dilakukan:

1. Sidebar Akademik dirapikan menjadi urutan:
   - Manajemen Kelas
   - Cetak Dokumen
   - Mutasi Siswa
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

- Unit test SIMANSA: 60 lulus, 315 assertions.
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
