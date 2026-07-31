# Modul Asrama SIMANSA

Modul Asrama adalah domain mandiri di SIMANSA. Seluruh tabel operasional memakai
prefiks `asrama_`, sementara identitas orang tetap menggunakan master `siswa`,
`gtks`, dan `users` yang sudah ada.

## Ruang lingkup

- Unit asrama dan kepala asrama dari master GTK.
- Santri dari master siswa, lengkap dengan nomor induk asrama.
- Asatidz dari master GTK beserta akses akun otomatis.
- Kelas asrama per tahun pelajaran, wali kelas, dan ketua kelas.
- Assign santri per siswa atau dari seluruh anggota rombel reguler.
- Mata pelajaran bilingual Arab–Latin.
- Pengampu per kelas, mapel, dan semester.
- Input nilai massal sesuai penugasan pengampu.
- Sikap, kehadiran, keputusan, draft, penerbitan, dan penguncian rapor.
- Portal santri untuk membaca rapor yang sudah diterbitkan.
- Cetak A4 dengan RTL Arab melalui print engine browser.

## Alur awal operator

1. Jalankan migration dan `AsramaMapelSeeder`.
2. Buat Unit Asrama.
3. Assign GTK sebagai asatidz.
4. Buat kelas untuk tahun pelajaran aktif.
5. Assign santri dari rombel reguler atau pilih siswa satu per satu.
6. Tetapkan wali kelas dan ketua kelas.
7. Tambahkan pengampu mapel.
8. Asatidz mengisi nilai dari akun masing-masing.
9. Wali melengkapi sikap dan kehadiran.
10. Pengguna dengan permission penerbitan menerbitkan dan mencetak rapor.

## Akses dan permission

- `view-asrama`
- `manage-asrama`
- `manage-asrama-santri`
- `manage-asrama-asatidz`
- `manage-asrama-kelas`
- `manage-asrama-mapel`
- `manage-asrama-pengampu`
- `input-nilai-asrama`
- `manage-rapor-asrama`
- `publish-rapor-asrama`
- `print-rapor-asrama`
- `view-asrama-portal`
- `asrama-rapor-access`

Admin dan Super Admin memperoleh seluruh permission melalui migration. Akun
santri memperoleh `view-asrama-portal` secara langsung selama keanggotaan aktif.
Akun GTK memperoleh portal dan input nilai selama penugasan asatidz aktif.
Data yang terlihat tetap dibatasi oleh penugasan pengampu atau wali kelas.

## Penerbitan rapor

Rapor draft selalu membaca nilai terbaru. Ketika diterbitkan, identitas, institusi,
mapel, nilai, jumlah, dan rata-rata disimpan sebagai snapshot. Nilai untuk santri
tersebut tidak dapat diubah sampai penerbitan dibatalkan. Mekanisme ini menjaga
arsip tetap konsisten walaupun master mapel atau identitas penandatangan berubah.
Penerbitan ditolak sampai seluruh mapel aktif pada kelas dan semester tersebut
memiliki nilai.

Tulisan Arab dicetak memakai `dir="rtl"` dan prioritas font Noto Naskh Arabic,
Amiri, lalu Traditional Arabic. Penyimpanan PDF dilakukan dari dialog cetak
browser agar Arabic shaping dan campuran teks RTL/LTR tetap akurat.
