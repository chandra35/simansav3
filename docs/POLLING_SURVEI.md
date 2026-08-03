# Modul Polling & Survei

Modul ini menyediakan builder polling generik untuk siswa dan GTK. Pengelola dapat menentukan jadwal, target responden, pertanyaan, dan aturan pengisian tanpa membuat modul baru untuk setiap kebutuhan.

## Hak akses

- `manage-polling`: membuat, mengubah, menerbitkan, menutup, dan menghapus polling yang belum memiliki respons.
- `view-polling-results`: membuka dashboard hasil serta mengunduh Excel/PDF.
- Super Admin, Admin, dan Operator menerima kedua permission. Kepala Madrasah dan WAKA menerima akses laporan.
- Siswa dan GTK tidak memakai permission pengelola; akses mereka ditentukan oleh jenis akun, target polling, dan jadwal aktif.

## Target responden

- Siswa: semua siswa, tingkat X/XI/XII, atau rombel aktif tertentu.
- GTK: semua GTK, jenis PTK, atau role tertentu.
- Beberapa target dalam jenis responden yang sama memakai aturan OR. Polling dengan audiens `Siswa & GTK` harus mempunyai target untuk keduanya.
- Pemeriksaan target dipusatkan di `PollingAudienceService` dan digunakan oleh menu, modal pengingat, halaman pengisian, serta endpoint penyimpanan.

## Jenis pertanyaan

- Pilihan tunggal
- Pilihan ganda dengan batas minimum/maksimum
- Ya/Tidak
- Jawaban singkat
- Jawaban panjang

Gunakan tombol `Preset TKA Kelas XII` untuk membuat polling contoh pemilihan tepat dua mapel TKA bagi siswa tingkat XII. Preset memuat 18 mata pelajaran pilihan SMA/MA sesuai daftar pada surat contoh; isinya tetap dapat disunting sebelum disimpan.

## Siklus polling

1. Simpan sebagai draft dan periksa pertanyaan serta target.
2. Terbitkan polling. Menu responden baru muncul ketika waktu mulai tercapai.
3. Responden mengisi satu respons. Perubahan hanya tersedia bila `Izinkan perubahan jawaban` aktif.
4. Pantau partisipasi dan statistik dari halaman detail.
5. Tutup polling atau biarkan jadwal berakhir, kemudian unduh Excel/PDF.

Struktur pertanyaan dan target dikunci setelah respons pertama masuk agar laporan historis tidak berubah makna. Data identitas, tingkat, dan rombel responden juga disimpan sebagai snapshot ketika jawaban dikirim.

## Pengingat

Modal SweetAlert2 hanya ditampilkan kepada responden target yang belum mengisi. Tombol `Ingatkan Nanti` menyimpan waktu tunda sesuai interval polling. Di sisi browser modal dibatasi sekali per sesi tab agar tidak mengganggu navigasi.

## Laporan

Dashboard menampilkan jumlah target, sudah/belum mengisi, persentase partisipasi, distribusi setiap opsi, dan tabel jawaban per responden. Responden lama tetap dipertahankan pada laporan walaupun kemudian berpindah kelas atau tidak lagi termasuk target aktif.
