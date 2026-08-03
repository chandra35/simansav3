# Modul Polling & Survei

Modul ini menyediakan builder polling generik untuk siswa dan GTK. Pengelola dapat menentukan jadwal, target responden, pertanyaan, dan aturan pengisian tanpa membuat modul baru untuk setiap kebutuhan.

## Hak akses

- `manage-polling`: membuat, mengubah, menerbitkan, menutup, mengarsipkan, dan menggunakan riwayat sebagai preset.
- `view-polling-results`: membuka dashboard hasil serta mengunduh Excel/PDF.
- Super Admin, Admin, dan Operator menerima kedua permission. Kepala Madrasah dan WAKA menerima akses laporan.
- Siswa dan GTK tidak memakai permission pengelola; akses mereka ditentukan oleh jenis akun, target polling, dan jadwal aktif.

## Target responden

- Jenis responden dipilih melalui `Siswa`, `GTK`, atau `Siswa & GTK`.
- Siswa: mode `Semua` atau `Custom`. Mode custom menyediakan tingkat X/XI/XII dan checklist rombel aktif dengan pencarian serta `Pilih semua yang tampil`. Daftar rombel langsung mengikuti tingkat yang dicentang.
- GTK: mode `Semua` atau `Custom`. Mode custom menyediakan kategori Guru/Staf dan pemilihan GTK individual melalui modal tabel yang dapat dicari. Isi modal langsung mengikuti kategori yang dicentang.
- Beberapa target dalam jenis responden yang sama memakai aturan OR. Contohnya, kategori Guru ditambah dua GTK individual akan menargetkan seluruh guru serta kedua GTK tersebut. Polling dengan audiens `Siswa & GTK` harus mempunyai target untuk keduanya.
- Pemeriksaan target dipusatkan di `PollingAudienceService` dan digunakan oleh menu, modal pengingat, halaman pengisian, serta endpoint penyimpanan.

## Jenis pertanyaan

- Pilihan tunggal
- Pilihan ganda dengan batas minimum/maksimum
- Ya/Tidak
- Jawaban singkat
- Jawaban panjang

Menu `Preset Cepat` menyediakan contoh TKA Kelas XII, Survei Kepuasan, dan Konfirmasi Kegiatan. Preset TKA memuat 18 mata pelajaran pilihan SMA/MA sesuai daftar pada surat contoh. Semua isinya tetap dapat disunting sebelum disimpan.

## Riwayat dan preset

- Setiap polling menyimpan snapshot tahun ajaran aktif, semester aktif, tanggal dibuat, jadwal, target, pertanyaan, dan hasil. Snapshot tidak ikut berubah ketika tahun ajaran aktif berganti.
- Polling tidak dihapus dari riwayat. Aksi Arsip menutup polling agar tidak menerima respons baru, tetapi laporan dan konfigurasinya tetap tersedia.
- Tombol `Jadikan Preset` atau ikon salin pada daftar riwayat membuka builder baru dengan identitas, aturan, pertanyaan, dan target dari polling lama. Polling baru menyimpan referensi ke polling sumber sehingga rantai penggunaannya dapat diaudit.
- Saat memakai riwayat lintas tahun ajaran, pengelola wajib memeriksa kembali target. Rombel yang ditampilkan selalu rombel aktif pada tahun berjalan; target rombel lama yang tidak aktif tidak ditawarkan kembali.

## Siklus polling

1. Simpan sebagai draft dan periksa pertanyaan serta target.
2. Terbitkan polling. Menu responden baru muncul ketika waktu mulai tercapai.
3. Responden mengisi satu respons. Perubahan hanya tersedia bila `Izinkan perubahan jawaban` aktif.
4. Pantau partisipasi dan statistik dari halaman detail.
5. Tutup/arsipkan polling atau biarkan jadwal berakhir, kemudian unduh Excel/PDF. Riwayatnya dapat dipakai kembali sebagai preset kapan saja.

Struktur pertanyaan dan target dikunci setelah respons pertama masuk agar laporan historis tidak berubah makna. Data identitas, tingkat, dan rombel responden juga disimpan sebagai snapshot ketika jawaban dikirim.

## Pengingat

Modal SweetAlert2 hanya ditampilkan kepada responden target yang belum mengisi. Tombol `Ingatkan Nanti` menyimpan waktu tunda sesuai interval polling. Di sisi browser modal dibatasi sekali per sesi tab agar tidak mengganggu navigasi.

## Laporan

Dashboard menampilkan jumlah target, sudah/belum mengisi, persentase partisipasi, distribusi setiap opsi, dan tabel jawaban per responden. Responden lama tetap dipertahankan pada laporan walaupun kemudian berpindah kelas atau tidak lagi termasuk target aktif.
