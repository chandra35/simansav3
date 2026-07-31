# Modul Asrama SIMANSA

Modul Asrama adalah domain mandiri di SIMANSA. Identitas orang dan rombel tetap
menggunakan master `siswa`, `gtks`, `users`, dan `kelas`; tabel operasional
Asrama memakai prefiks `asrama_`.

## Prinsip data

- Hanya ada satu Asrama, sehingga tidak ada menu atau pemilihan unit.
- Pemisahan tempat tinggal dilakukan melalui kamar pada gedung putra/putri.
- Rombel Asrama menunjuk langsung ke rombel SIMANSA melalui `kelas_id`.
- Satu rombel dapat mempunyai beberapa pengasuh.
- Setiap santri pada rombel mempunyai satu pengasuh utama pada satu waktu.
- Pengasuh kamar terpisah dari pengasuh rombel; satu pengasuh dapat menangani
  beberapa kamar.
- Satu santri hanya dapat mempunyai satu kamar aktif. Riwayat kamar lama tetap
  disimpan.

## Alur awal

1. Admin atau Super Admin membuka `ASRAMA > Operator Asrama`.
2. Tetapkan GTK berakun aktif sebagai `Operator Asrama`.
3. Operator menambahkan GTK pada `Pengasuh & Pengajar` serta memilih
   kewenangan pengasuh rombel, pengasuh kamar, dan/atau pengampu mapel.
4. Aktifkan rombel SIMANSA pada `Rombel Asrama`. Seluruh siswa rombel dapat
   langsung disinkronkan sebagai santri.
5. Tambahkan beberapa pengasuh pada rombel, lalu bagi santri per pengasuh atau
   sekaligus satu rombel.
6. Buat kamar putra/putri, tetapkan pengasuh kamar, lalu tempatkan santri.
7. Tetapkan pengampu mata pelajaran per rombel dan semester.
8. Pengampu mengisi nilai dari akun masing-masing.
9. Pengasuh melengkapi sikap, kehadiran, keputusan, dan menerbitkan rapor.

## Akses

Role `Operator Asrama` mendapat permission pengelolaan Asrama tetapi tidak
mendapat permission administrasi umum SIMANSA. Hanya Admin/Super Admin dengan
`manage-asrama-operator` yang dapat memberikan atau mencabut role ini.

Permission tambahan:

- `manage-asrama-operator`
- `manage-asrama-kamar`

Akun santri aktif mendapat `view-asrama-portal`. GTK yang aktif dalam tim Asrama
mendapat portal dan akses input nilai; data tetap dibatasi berdasarkan penugasan
mapel atau rombel yang diasuh.

## Rapor Arab

Rapor menampilkan nama mapel Arab–Latin, terbilang nilai dalam Unicode Arab,
jumlah, rata-rata, kebersihan, kelakuan, kerajinan, kehadiran, keputusan,
tanggal Masehi/Hijriah, serta tanda tangan Kepala Asrama dan Pengasuh Rombel.
Tampilan RTL memakai Noto Naskh Arabic dengan fallback Amiri dan Traditional
Arabic.

Saat diterbitkan, rapor menyimpan snapshot dan terkunci. Penerbitan ditolak bila
nilai mapel belum lengkap. Pembatalan terbit membuka kembali nilai untuk koreksi.

## UI

Seluruh halaman Asrama memakai desain panel responsif, modal dengan hierarki
informasi yang jelas, Select2 untuk pencarian data besar, serta overlay loading
dan progress pada proses simpan, sinkronisasi, assignment, dan penerbitan.
