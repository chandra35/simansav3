# Perubahan Terakhir MAN 1 Metro

Tanggal pembaruan: 2 Agustus 2026, zona waktu Asia/Jakarta.

## Ringkasan terkini

### Standardisasi hero modul dan tautan kontak siswa

- `MAN1METRO.md` dan `docs/UI_DESIGN_PRINCIPLES_UPDATED.md` kini menetapkan pola Data Siswa sebagai struktur wajib modul baru: judul/breadcrumb di `content_header`, satu hero `bg-gradient-primary` utuh di awal `content`, dan card operasional `card-outline card-primary`.
- Daftar Siswa Wali Kelas, Absensi Harian, Rekap Absensi, dan Cetak ID Card Siswa telah dipindahkan dari hero generik terpisah ke pola tersebut.
- Grid hero, filter absensi, toolbar cetak, dan tombol aksi memperoleh breakpoint ponsel agar tidak menimbulkan horizontal scroll.
- Nomor HP siswa, ayah, dan ibu pada detail Data Siswa, modal detail Data Siswa, serta detail siswa Wali Kelas kini berupa tautan native `tel:` yang bisa diketuk untuk membuka aplikasi telepon/kontak perangkat.
- Detail Wali Kelas kini membaca field orang tua yang benar, yaitu `hp_ayah` dan `hp_ibu`.

File terkait:

- `MAN1METRO.md`
- `docs/UI_DESIGN_PRINCIPLES_UPDATED.md`
- `resources/views/admin/gtk/wali/siswa/index.blade.php`
- `resources/views/admin/gtk/wali/absensi/index.blade.php`
- `resources/views/admin/gtk/wali/absensi/rekap.blade.php`
- `resources/views/admin/cetak/id-card-siswa-index.blade.php`
- `resources/views/admin/siswa/index.blade.php`
- `resources/views/admin/siswa/show.blade.php`
- `resources/views/admin/gtk/wali/siswa/show.blade.php`

### Penyelarasan UI akun GTK

- Dashboard GTK, Profil Saya, dan Ganti Password kini memakai struktur yang sama dengan halaman Data Siswa: judul/breadcrumb standar, satu hero `bg-gradient-primary` utuh selebar konten, serta card operasional `card-outline card-primary`.
- Koreksi lanjutan menghapus hero generik dua kolom yang membuat gradient dan chip status terlihat terputus. Status akun kini menyatu di sisi kanan hero sehingga distribusi warna dan ruang lebih seimbang.
- Dashboard merangkum identitas GTK dalam grid adaptif, menyediakan aksi akun yang jelas, serta merapikan panel perwalian dan jadwal.
- Profil GTK menampilkan status kelengkapan pada hero dan memakai action bar sticky di dalam form sehingga tidak menutupi konten atau bergantung pada lebar sidebar.
- Wizard password tetap mempertahankan tiga langkah, dilengkapi autofill browser yang benar, label tombol aksesibel, feedback konfirmasi live, dan susunan tombol/identitas satu kolom pada ponsel.
- Breakpoint desktop, tablet, dan ponsel ditambahkan secara scoped pada masing-masing halaman tanpa mengubah proses penyimpanan data.

File terkait:

- `resources/views/admin/gtk/dashboard.blade.php`
- `resources/views/admin/gtk/profile/index.blade.php`
- `resources/views/admin/gtk/profile/password.blade.php`
- `tests/Unit/GtkAccountUiArchitectureTest.php`

### Portal Wali Kelas ("Kelas Saya") untuk akun GTK

- Menu sidebar adaptif: GTK yang menjadi wali kelas aktif memperoleh seksi "Kelas Saya" (Daftar Siswa, Absensi Harian, Rekap Absensi, Catatan Siswa, Jadwal Kelas). Gate `sidebar-wali-kelas-menu` memakai prefix `sidebar-` agar tidak bentrok dengan Spatie `Gate::before`.
- Daftar siswa hanya-baca yang otomatis dibatasi ke rombel yang diampu, lengkap dengan detail siswa hanya-baca (tanpa tombol ubah/hapus). Scope dilakukan di level query, bukan sekadar menyembunyikan tombol.
- Absensi harian rombel dengan tombol "Hadir Semua", simpan draft/final, penguncian sesi final, serta audit lengkap via `StudentAttendanceAuditService`. `session_key` identik dengan modul absensi admin sehingga monitoring pusat membaca sesi yang sama.
- Rekap absensi hanya-baca per hari/minggu/bulan: ringkasan total per status dan rekap per siswa.
- Catatan pembinaan per siswa (tabel `catatan_wali_kelas`) yang dapat dibaca guru BK/konseling, dengan kategori, penanda penting, serta CRUD ringkas.
- Jadwal hanya-baca: jadwal pelajaran rombel dan jadwal mengajar pribadi (bila akun memiliki data GTK).

File utama:

- `database/migrations/2026_08_01_090000_create_catatan_wali_kelas_table.php`
- `app/Models/CatatanWaliKelas.php`
- `app/Http/Controllers/Admin/WaliKelas/`
- `resources/views/admin/gtk/wali/`
- `tests/Unit/WaliKelasPortalTest.php`

### Refaktor final Asrama: satu asrama, rombel SIMANSA, pengasuh, dan kamar

- Konsep Unit Asrama dan kelas Asrama terpisah dihapus dari UI. Modul memakai satu Asrama, sedangkan rombel menunjuk langsung ke master kelas SIMANSA.
- Role khusus `Operator Asrama` ditambahkan. Hanya Admin/Super Admin yang dapat menetapkan operator; operator memperoleh seluruh kewenangan Asrama tanpa akses administrasi umum SIMANSA.
- GTK Asrama kini dapat diberi kombinasi kewenangan sebagai pengasuh rombel, pengasuh kamar, dan pengampu mapel.
- Satu rombel mendukung beberapa pengasuh. Santri dapat dibagi per orang atau satu rombel penuh, dengan satu pengasuh utama per santri.
- Manajemen kamar ditambahkan untuk gedung putra/putri, meliputi kode/nama kamar, lantai, kapasitas, satu pengasuh utama, penempatan santri, validasi jenis kelamin, dan riwayat perpindahan. Satu pengasuh dapat menangani beberapa kamar.
- Assignment santri dapat dilakukan per rombel SIMANSA atau per siswa. Identitas siswa/GTK tetap bersumber dari master SIMANSA.
- Otorisasi input nilai dan rapor telah disesuaikan untuk banyak pengasuh per rombel.
- Rapor memakai teks Unicode Arab asli dan penandatangan `Pengasuh Rombel / مشرف الفصل`.
- Seluruh halaman Asrama memakai modal baru, dropdown Select2 yang dapat dicari, desain responsif, konfirmasi aksi, dan overlay loading/progres.
- Migrasi menjaga data lama: rombel lama dipetakan ke kelas SIMANSA berdasarkan tahun/nama dan wali lama dimigrasikan menjadi pengasuh utama.

File utama:

- `database/migrations/2026_07_31_110000_refactor_asrama_to_single_boarding_school.php`
- `database/migrations/2026_07_31_111000_migrate_legacy_asrama_caregivers.php`
- `app/Http/Controllers/Asrama/`
- `resources/views/asrama/`
- `MODUL_ASRAMA.md`

### Modul mandiri Asrama dan Rapor Arab

- Menu utama `ASRAMA` ditambahkan ke sidebar dengan submenu Dashboard, Unit, Santri, Asatidz, Kelas, Mapel, Input Nilai, dan Rapor.
- Seluruh data operasional menggunakan tabel berprefiks `asrama_`; identitas santri dan asatidz tetap merujuk master `siswa` dan `gtks` SIMANSA.
- Siswa dapat di-assign per orang atau sekaligus dari rombel reguler. Profil siswa menampilkan status santri, nomor induk, serta kelas/unit asrama aktif.
- Kelas asrama mendukung tahun pelajaran SIMANSA, wali kelas dari asatidz, ketua kelas dari santri aktif, dan penugasan pengampu per semester.
- Akun GTK yang ditugaskan memperoleh portal dan akses input nilai. Data dibatasi pada mapel yang diampu atau kelas yang menjadi tanggung jawab wali.
- Akun siswa yang menjadi santri memperoleh portal Asrama dan hanya dapat membuka rapor miliknya yang sudah diterbitkan.
- Rapor memuat nilai mapel bilingual Arab–Latin, terbilang Arab, jumlah, rata-rata, kebersihan, kelakuan, kerajinan, kehadiran, keputusan, tanggal Masehi/Hijriah, dan tanda tangan.
- Penerbitan membuat snapshot dan mengunci nilai. Pembatalan terbit membuka kembali nilai untuk koreksi.
- Permission Asrama masuk ke permission matrix; Admin dan Super Admin memperoleh akses awal melalui migration.
- Master awal 26 mata pelajaran sesuai contoh rapor tersedia melalui `AsramaMapelSeeder`.
- Seluruh 139 unit test lulus (925 assertions), dua workflow test Asrama lulus (16 assertions), seluruh Blade berhasil dikompilasi, dan build Vite produksi berhasil.

Dokumentasi:

- `MODUL_ASRAMA.md`

File utama:

- `database/migrations/2026_07_31_100000_create_asrama_module_tables.php`
- `app/Http/Controllers/Asrama/`
- `app/Models/Asrama*.php`
- `resources/views/asrama/`
- `tests/Unit/AsramaModuleTest.php`

### Watermark logo sekolah 3D pada live polling

- Background setiap kartu paket live polling menggunakan logo sekolah aktual sebagai watermark besar dan utuh di belakang kandidat.
- Logo mempunyai opacity rendah, warna teredam, mode blend, perspektif 3D ringan, dua cincin kedalaman, dan bayangan lembut agar menyatu dengan dark mode.
- Watermark bergerak sangat lambat untuk memberikan kesan ruang yang hidup tanpa bersaing dengan foto kandidat maupun statistik.
- Watermark ditempatkan pada lapisan tersendiri di bawah header, foto, teks, dan meter sehingga tetap terlihat samar di area kosong tanpa terpotong oleh celah antarpaket.
- Ukuran watermark menyesuaikan desktop dan perangkat kecil, tidak menerima interaksi, serta disembunyikan dari pembaca layar.
- Animasi watermark dinonaktifkan otomatis untuk preferensi `prefers-reduced-motion`.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 141 pengujian, 915 assertions.

File utama:

- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Jumlah suara tampil pada kandidat dan meter perolehan lebih berbodi

- Setiap figur Ketua, Wakil Ketua, Sekretaris, atau Bendahara pada live polling kini memiliki badge jumlah suara.
- Badge menggunakan label `suara paket` karena surat suara diberikan kepada paket; seluruh anggota dalam paket yang sama secara benar menampilkan jumlah yang sama.
- Jumlah pada badge diperbarui bersama data polling setiap empat detik dan mendapat pulse ketika suara paket berubah.
- Bar statistik diperbesar menjadi meter adaptif setinggi 24–36 px, atau 20 px pada layar desktop yang sangat pendek.
- Meter kini mempunyai track berlapis, padding badan, highlight permukaan, bayangan bagian dalam, skala interval, dan fill ber-volume. Perolehan kecil tetap terlihat dengan minimum fill 18 px setelah paket memperoleh suara.
- Titik energi pada ujung fill turut diperbesar agar proporsi dan arah pertumbuhan mudah terlihat dari jauh.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 140 pengujian, 907 assertions.

File utama:

- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Statistik perolehan live polling lebih jelas dan hidup

- Bar statistik per paket telah ditingkatkan bertahap dari ukuran awal 7 px menjadi meter berbodi 24–36 px, dengan batas 20 px pada layar desktop yang sangat pendek agar seluruh paket tetap muat.
- Label `Statistik perolehan`, jumlah suara, dan persentase diperbesar. Pengecilan label ekstrem saat terdapat 5–6 paket dihapus.
- Track bar memiliki penanda interval, kontras lebih kuat, dan bayangan dalam agar panjang perolehan mudah dibandingkan dari layar monitoring.
- Isi bar memakai animasi aliran warna dan kilau bergerak, disertai titik energi pada ujung perolehan yang telah memiliki suara.
- Ketika jumlah suara berubah, panel statistik memberi pulse singkat dan angka baru muncul dengan animasi pop. Animasi tidak terus mengganggu ketika data tidak berubah.
- Paket teratas mendapatkan cahaya bar yang lebih kuat dan perpindahan posisi tetap memakai animasi ranking yang sudah ada.
- Seluruh animasi dinonaktifkan bagi perangkat dengan preferensi `prefers-reduced-motion`.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 140 pengujian, 902 assertions.

File utama:

- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Halaman voting siswa lebih ringkas dan fokus pada kandidat

- Hero judul, status jadwal, informasi kerahasiaan, dan pesan keberhasilan dipadatkan agar tidak menghabiskan layar sebelum daftar kandidat.
- Kode bukti partisipasi tetap ditampilkan setelah memilih, tetapi dalam blok kecil di sisi pesan keberhasilan. Kode ini merupakan referensi acak yang membuktikan status partisipasi tanpa mengungkap paket pilihan.
- Bagian Tentang Pemilihan dan Cara Memilih dipindahkan ke panel `Informasi dan panduan pemilihan` yang dapat dibuka setelah daftar paket kandidat.
- Heading kandidat diperkecil dan keterangan tambahan disederhanakan pada layar ponsel.
- Tampilan responsif mempertahankan hierarki ringkas pada desktop maupun perangkat kecil.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 139 pengujian, 892 assertions.

File utama:

- `resources/views/siswa/osis-election/index.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Seluruh kartu paket dapat dicoblos

- Pada halaman Pemilihan OSIS siswa, seluruh area kartu paket kini dapat diklik atau disentuh untuk memilih kandidat, tidak terbatas pada tombol di bagian bawah.
- Kartu yang dapat dipilih memiliki penanda `Klik untuk coblos`, efek sorot saat hover/fokus, dan status terpilih sebelum modal konfirmasi dibuka.
- Interaksi menggunakan tombol transparan native sehingga tetap dapat digunakan melalui keyboard dan memiliki label aksesibilitas.
- Tab Visi dan Misi tetap bisa dibuka tanpa memicu pemilihan paket.
- Tombol `Coblos Paket` tetap tersedia sebagai aksi yang eksplisit. Klik kartu maupun tombol mengarah ke modal konfirmasi password yang sama, sehingga suara tidak langsung tersimpan akibat salah sentuh.
- Perilaku otomatis menyesuaikan status pemilih: kartu hanya interaktif ketika voting sedang dibuka, siswa belum memilih, dan paket tersebut bukan paket siswa sendiri.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 138 pengujian, 886 assertions.

File utama:

- `resources/views/siswa/osis-election/index.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Perbaikan voting siswa dan pengumuman Pemilihan OSIS

- Endpoint coblos siswa tidak lagi memakai throttle route generik yang dapat menampilkan halaman mentah `429 Too Many Requests`.
- Pembatasan kini khusus untuk percobaan password yang salah, menggunakan kunci per siswa dan per pemilihan. Maksimal lima kesalahan dalam 60 detik tetap melindungi akun, tetapi respons selalu kembali ke halaman pemilihan dengan pesan dan hitung mundur yang ramah.
- Batas percobaan langsung dibersihkan setelah password benar. Koreksi password mempertahankan paket yang dipilih dan membuka kembali modal setelah pesan dibaca.
- Submit ganda ditahan pada browser. Jaminan satu suara tetap menggunakan transaksi, row lock pemilih, dan pemeriksaan `has_voted` pada server.
- Setelah konfirmasi, tampil animasi surat suara dicoblos, diberi stempel, lalu masuk ke kotak suara digital sebelum request dikirim. Animasi menghormati preferensi `prefers-reduced-motion`.
- Seluruh halaman akun siswa kini menerima overlay pengumuman untuk pemilihan yang dipublikasikan pada tahun aktif:
  - sebelum mulai, overlay menampilkan countdown hari, jam, menit, dan detik serta tombol mengenali kandidat;
  - saat voting terbuka, pesan berubah otomatis menjadi ajakan mencoblos dan countdown menuju penutupan;
  - saat dijeda, pengingat menjelaskan status panitia;
  - setelah overlay ditutup, pengingat kecil tetap berada di sudut layar;
  - overlay hanya muncul sekali per tab untuk setiap fase, lalu muncul kembali ketika fase berubah dari terjadwal menjadi terbuka;
  - siswa yang sudah memilih dan halaman Pemilihan OSIS sendiri tidak ditutupi overlay.
- Route cache dibersihkan dan dibangun ulang; route voting terverifikasi hanya memakai middleware `web`, `auth`, dan `impersonation:siswa`, tanpa `throttle:5,1`.
- Blade berhasil dikompilasi dan seluruh unit test lulus: 137 pengujian, 878 assertions.

File utama:

- `app/Exceptions/InvalidVotePasswordException.php`
- `app/Http/Controllers/Siswa/OsisElectionController.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/OsisElectionService.php`
- `resources/views/partials/student-election-overlay.blade.php`
- `resources/views/siswa/osis-election/index.blade.php`
- `routes/web.php`

### Preview leger RDM otomatis berdasarkan kohor

- Proses sync tetap idempotent untuk Semester 5 dan periode lain: satu siswa-mapel-tahun-semester hanya boleh mempunyai satu record. Nilai sama dilewati, nilai berbeda ditahan sebagai konflik, dan periode baru dibuat tanpa menggandakan Semester 1–4.
- Template upload nilai kini dibentuk dari mapel aktual semester/tahun yang dipilih dengan fallback periode kohor dan mapping RDM. Parser upload membaca kode mapel dari header Excel mulai kolom F, sehingga tidak lagi bergantung pada posisi tetap mapel K13. Batas kolom template dihitung dari header aktual agar tidak terbentuk kolom kosong setelah mapel terakhir.
- Menu Nilai memiliki halaman Perangkingan untuk satu semester atau akumulasi seluruh semester kohor, baik per rombel maupun seluruh rombel. Nilai kumulatif merata-ratakan rata-rata semester agar bobot setiap semester sama; siswa yang mapelnya belum lengkap ditandai dan tidak diberi ranking.
- Perangkingan menampilkan rank rombel dan rank tingkat secara berdampingan, jumlah mapel setiap semester, status kelengkapan, serta export Excel sesuai filter.
- Form Integrasi RDM hanya meminta tahun roster, tingkat aktif, dan rombel SIMANSA; pilihan tahun, semester, tingkat, serta kelas sumber RDM dihapus.
- Sistem membentuk perjalanan akademik siswa secara otomatis: Kelas 10 mengambil Semester 1–2, Kelas 11 Semester 1–4, dan Kelas 12 Semester 1–5.
- Tahun ajaran, tingkat X/XI/XII, dan semester ganjil/genap RDM diturunkan dari tahun masuk kohor. Pencocokan siswa tetap ketat berdasarkan NISN siswa yang aktif pada roster terpilih.
- Seluruh semester digabung dalam satu preview dan satu aksi Apply. Periode RDM yang belum tersedia ditandai serta dilewati tanpa dianggap sebagai kegagalan.
- Hasil preview menampilkan kartu cakupan per semester, jumlah siswa yang memiliki histori, kelengkapan pada seluruh periode yang tersedia, total nilai, dan dampak Apply.
- Nilai lama tetap aman: record sama dilewati, konflik ditahan, dan hanya nilai baru yang dapat ditambahkan.
- Apply nilai puluhan ribu record diproses per batch 500 baris dan ditulis dengan bulk insert dalam satu transaksi, sehingga tidak lagi memuat seluruh staging sebagai model sekaligus dan tidak melewati batas memori PHP 128 MB.
- Transaksi Apply memiliki retry deadlock; kegagalan tak terduga dikembalikan sebagai pesan halaman yang aman serta dicatat bersama ID run, tanpa meninggalkan nilai yang tersimpan sebagian.
- Rekap nilai semester tidak lagi mencocokkan nilai Merdeka `M-*` dengan daftar kode lama secara ketat. Kolom dibentuk dari ID mapel yang benar-benar mempunyai nilai pada pasangan semester/tahun terpilih.
- Export semester, export leger lintas semester, dan export SPAN-PTKIN memakai sumber mapel aktual yang sama. Export lintas semester menggunakan gabungan mapel, sedangkan SPAN membentuk daftar mapel khusus untuk setiap semester.
- Query export dibatasi pada pasangan semester dan tahun pelajaran yang tepat agar nilai dari periode lain tidak ikut tercampur.
- Export Excel besar membaca nilai bertahap ke lookup ringan dan memakai cache sel batch pada penyimpanan file khusus, sehingga leger Semester 1–5 tidak menahan puluhan ribu model Eloquent maupun seluruh sel di memori PHP 128 MB; pembersihannya juga tidak mengganggu cache aplikasi.
- Workbook melepaskan seluruh cache sel miliknya setelah stream download selesai agar file cache sementara tidak menumpuk di server.

### Perbaikan sync nilai RDM dan leger siswa aktif

- Preview Integrasi RDM sekarang memakai roster SIMANSA sebagai acuan: hanya siswa berstatus aktif pada tahun pelajaran, tingkat, dan kelas SIMANSA yang dipilih yang dapat masuk staging.
- Tingkat sumber RDM dipilih terpisah dari tingkat aktif SIMANSA, sehingga siswa kelas XII saat ini dapat mengambil histori nilai ketika masih berada di tingkat X atau XI.
- Identitas siswa RDM didekripsi secara batch lalu dicocokkan ketat menggunakan NISN; NIS RDM enam digit tidak lagi dipakai sebagai fallback otomatis.
- Nilai K13 Pengetahuan dan Keterampilan dipasangkan dalam satu record, sedangkan nilai Kurikulum Merdeka tetap memakai nilai utama. Predikat dan deskripsi rapor ikut disimpan.
- Proses apply hanya memasukkan nilai yang benar-benar baru. Nilai yang sudah sama dilewati dan nilai berbeda ditandai konflik tanpa menimpa data SIMANSA.
- Pemetaan semester mencakup Semester 1–6. Export leger kelas XII tetap Semester 1–5 untuk SNBP dan menyediakan opsi Semester 6 untuk arsip lengkap kelulusan.
- Ringkasan preview menampilkan jumlah siswa aktif, siswa yang cocok di RDM, nilai baru, nilai sama, serta konflik yang ditahan.
- UI preview dibagi menjadi alur sumber RDM, Semester Leger tujuan, dan roster aktif agar admin tidak tertukar antara jumlah siswa dan jumlah record nilai.
- Dampak Apply dijelaskan per kategori: ditambahkan, dilewati karena sama, konflik ditahan, dan mapping bermasalah. Preview juga menyediakan sampel nilai K13/Merdeka, daftar siswa yang belum ditemukan, detail audit, dan tombol Apply dengan jumlah nilai yang benar-benar akan ditulis.
- Kurikulum kini dideteksi otomatis dari metadata kurikulum setiap mapel RDM dengan fallback struktur jenis nilai. Kurikulum Merdeka hanya mengisi nilai utama; kolom Pengetahuan/Keterampilan hanya digunakan untuk K13.
- Preview lama tanpa metadata kurikulum tidak dapat di-Apply dan harus dibuat ulang, sehingga data Merdeka tidak mungkin masuk sebagai nilai K13.
- Halaman Nilai Leger aktif memakai roster aktif dan otomatis membuka tahun pelajaran sesuai tingkat serta posisi semester. Nilai alumni tetap tersimpan sebagai arsip, tetapi tidak tercampur dalam statistik atau tabel operasional siswa aktif.

### Rancangan integrasi histori akademik RDM ke SIMANSA

- Struktur 2.792 siswa RDM, 6.313 riwayat kelas, 358.964 nilai akhir, 1.505.333 nilai komponen, absensi rapor, ekstra, prestasi, serta P5RA telah dianalisis secara read-only.
- Histori nilai tersedia dari 2021/2022 sampai 2025/2026; 1.015 siswa mempunyai enam periode nilai lengkap. Rombel RDM bersifat tahunan sedangkan periode semester harus diturunkan dari transaksi nilai.
- Ditemukan kelas historis yang hilang, identifier duplikat, dan kelemahan pipeline sinkron saat ini yang dapat mencampur nilai Pengetahuan/Keterampilan K13 serta belum mendukung histori Semester 6 secara utuh.
- Desain mapping siswa/kelas, snapshot periode akademik, transformasi K13–Merdeka, staging idempotent, serta tahapan implementasi disimpan di `docs/rdm-research/academic-data-integration-analysis.md`.

### Arsip analisis pengiriman nilai RDM ke pusat

- Alur pengiriman nilai telah dipetakan secara read-only tanpa mengirim nilai: backend RDM lokal menyiapkan paket JSON per kelas, browser mengirimnya langsung ke `rdm.hdmadrasah.id` memakai Bearer token, lalu backend lokal menandai kelas selesai.
- Pengiriman satu kelas berjalan per siswa melalui `newkirimnilai`, sedangkan Kirim Semua berjalan bulk per kelas melalui `newkirimnilaibulk`.
- Status selesai dikonfirmasi pada `e_kelaslock.kelaslock_status = 3`; timestamp database cocok dengan request `proktor/kirimnilai/done` pada access log.
- Analisis autentikasi, endpoint lama, kelemahan retry, batas controller ionCube, dan risiko perbedaan status pusat-lokal disimpan di `docs/rdm-research/send-nilai-analysis.md`.

### Arsip analisis Sync EMIS RDM

- Alur tombol Sync EMIS RDM telah dipetakan secara read-only dari bundle frontend, access log, struktur database, dan agregat data VM tanpa menjalankan proses sinkronisasi.
- Browser memanggil endpoint lokal RDM secara berurutan untuk verifikasi tahun ajaran, mengambil rombel per halaman, menyinkronkan siswa per halaman, dan memetakan siswa baru ke kelas.
- RDM menyimpan relasi EMIS terenkripsi pada `siswa_emis`, tetapi seluruh `siswa_nis` yang terisi hanya berformat 6 digit; field tersebut bukan `local_nis` EMIS lengkap 18 digit.
- Temuan, endpoint, batas investigasi backend ionCube, serta implikasinya untuk pembandingan NIS Lokal SIMANSA disimpan di `docs/rdm-research/`.

### Edit nama siswa dan normalisasi input huruf kapital

- Akun siswa dapat mengubah Nama Lengkap melalui menu Data Diri; perubahan disimpan serentak pada profil siswa dan nama akun login.
- Input teks bebas pada Data Diri dan Data Orangtua otomatis tampil sebagai huruf kapital saat diketik dan dinormalisasi kembali di server sebelum disimpan.
- Normalisasi mencakup nama siswa, NPSN alfanumerik, tempat lahir, hobi, cita-cita, alamat siswa, nama ayah/ibu, dan alamat orangtua.
- Email, password, identifier numerik, kode wilayah, serta nilai internal pilihan/status dipertahankan formatnya agar validasi dan relasi aplikasi tidak rusak.
- Endpoint penyimpanan profil tidak lagi memakai seluruh payload request; hanya field tervalidasi yang dapat diteruskan ke model.
- Perubahan nama siswa, nama akun, email opsional, serta data diri disimpan dalam satu transaksi database.

### Metadata kelas asal pada daftar mutasi siswa

- Daftar mutasi siswa menampilkan metadata `Asal kelas` tepat di bawah NISN.
- Mutasi keluar baru menyimpan snapshot kelas aktif siswa sebelum kelas dilepas saat persetujuan.
- Mutasi keluar lama yang belum memiliki snapshot tetap menampilkan kelas asal dari riwayat kelas pada tahun pelajaran mutasi.
- Query daftar memuat riwayat dan kelas terkait sekaligus agar fallback tidak menimbulkan query per baris.
- Validasi lokal `OutgoingStudentMutationTest` lulus 5 pengujian dengan 44 assertions.

### Konfirmasi SweetAlert2 pada Pengaturan Presensi

- Konfirmasi seed hari libur nasional 2026, penghapusan lokasi absensi, dan penghapusan hari libur tidak lagi memakai dialog bawaan browser.
- Ketiga aksi sekarang memakai modal SweetAlert2 yang konsisten, memiliki penjelasan tindakan, tombol batal yang menjadi fokus awal, dan hanya mengirim form setelah admin mengonfirmasi.

### Pemantauan absensi siswa dan dashboard siswa

- Absensi siswa telah tersedia untuk guru mapel berdasarkan jadwal aktif pada hari yang dipilih dan untuk wali kelas dalam mode harian pada rombel tahun aktif; tanggal default tetap hari ini dan tanggal masa depan ditolak.
- Admin, operator, WAKA, kepala madrasah, dan Super Admin memperoleh menu `Pemantauan Siswa` yang menampilkan seluruh roster tahun aktif, termasuk siswa yang belum direkam, lengkap dengan ringkasan status, filter kelas/status/tanggal, pencarian, catatan, pencatat, serta pintasan ke sesi dan riwayat.
- Catatan per siswa pada input absensi guru/wali kelas dipindahkan dari input kecil di tabel ke modal responsif dengan penghitung karakter, ringkasan catatan, serta aksi tambah, ubah, atau hapus. Catatan tetap tersimpan bersama draft/finalisasi dan masuk jejak audit perubahan record.
- Dashboard siswa menggunakan header putih ringkas berisi identitas, kelas, tahun pelajaran, dan semester; hero ungu serta overlay Lottie yang menahan tampilan awal dihapus tanpa mengubah modul profil, kelengkapan, pengumuman, maupun fungsi dashboard lainnya.

### Tabel Data Siswa responsif dan seimbang

- Lebar kolom tabel Data Siswa memakai proporsi tetap yang berjumlah satu layout penuh; Nama/NISN diperkecil dari 28% menjadi 19%, JK menjadi 4%, dan Kelas menjadi 7%.
- Kolom status, keberadaan, tanggal masuk, dan aksi memperoleh ruang terukur sehingga tabel desktop tampil utuh dan seimbang tanpa celah kolom berlebihan.
- Pada layar lebih sempit tabel mempertahankan keterbacaan dengan lebar minimum, wrapping terkontrol, dan horizontal scroll sentuh yang halus.

### Penyelarasan UI Data GTK

- Halaman Data GTK memakai struktur visual operasional terbaru dengan hero ringkas, kartu statistik standar, panel filter, dan tabel yang seluruh gayanya dibatasi pada scope GTK agar tidak berbenturan dengan style halaman lain.
- Kolom NIK, Kode Guru, Kategori PTK, dan Username dihapus sebagai kolom terpisah. Metadata NIK, Kode GTK, Username, dan Jenis PTK kini disusun vertikal per baris di bawah nama GTK; kolom Jenis PTK terpisah juga dihapus agar tidak duplikat.
- Identitas GTK menampilkan foto profil asli bila tersedia. Tanpa foto, sistem memakai avatar SVG lokal bergaya Muslim/Muslimah sesuai jenis kelamin dengan animasi ringan yang menghormati preferensi reduced motion.
- Kolom Status Kepegawaian dan Jabatan dihapus dari daftar. Metadata menampilkan nama rombel jika GTK menjadi Wali Kelas pada tahun pelajaran aktif; status kelengkapan kepegawaian tetap tersedia sebagai `Data Kepeg`.
- Tabel GTK memakai layout tetap dengan proporsi profesional: nomor 4%, identitas 40%, masing-masing status 19%, dan aksi 18%. Header serta isi kolom disejajarkan, badge status dibuat konsisten, baris memperoleh zebra/hover halus, dan tombol aksi diberi jarak serta radius seragam.
- Perubahan filter langsung memuat ulang DataTable melalui AJAX dengan transisi halus, indikator status nonblocking, jeda singkat untuk mencegah request bertumpuk, serta tetap mempertahankan pencarian berdasarkan metadata identitas.

### Performa penyimpanan impor NIS Lokal

- Konfirmasi impor NIS Lokal tidak lagi mengunci, memeriksa duplikasi, menyimpan model, dan membuat activity log terpisah untuk setiap siswa.
- Kelayakan rombel dan kepemilikan NIS divalidasi secara massal dalam transaksi, lalu baris yang berubah disimpan dalam batch maksimal 500 siswa dengan satu log aktivitas ringkasan.
- Metadata tahun masuk NIS, nomor urut, waktu pencatatan, serta admin pencatat tetap disimpan; optimasi hanya menghilangkan query dan event model berulang.
- Pemilih file memakai dropzone responsif yang mendukung klik dan drag-and-drop, menampilkan nama serta ukuran file, memvalidasi ekstensi/ukuran sebelum upload, menyediakan aksi hapus/ganti file, dan memperjelas status saat preview diproses.

### Overlay download template NIS Lokal

- Tombol `Unduh Template` pada menu NIS Lokal ditandai sebagai download non-navigasi sehingga tidak mengaktifkan overlay perpindahan halaman.
- Overlay global kini menekan event `beforeunload` untuk tautan download, target tab baru, atau `data-no-overlay`, lalu memastikan overlay kembali tersembunyi jika halaman tetap aktif setelah file diterima browser.

### Status EMIS dan Keberadaan read-only

- Daftar `Data Siswa` memiliki kolom `Keberadaan` yang mengambil verifikasi dari rombel aktif pada tahun pelajaran aktif.
- Status `EMIS` dan `Keberadaan` selalu terlihat bagi pengguna yang dapat membuka Data Siswa atau detail rombel, tetapi hanya Super Admin yang memperoleh tombol perubahan.
- Endpoint perubahan tunggal maupun bulk Keberadaan diperketat dengan gate Super Admin; kepemilikan permission `edit-kelas` saja tidak lagi cukup.
- Siswa tanpa rombel aktif ditampilkan sebagai `Tanpa Rombel`, sedangkan status yang belum diverifikasi ditampilkan sebagai `Belum dicek`.

### Identitas Wali Kelas dan Ketua Kelas

- Dashboard siswa menampilkan nama Wali Kelas dan Ketua Kelas rombel aktif, termasuk kartu identitas masing-masing serta penanda jika siswa tersebut sendiri adalah Ketua Kelas.
- Dashboard GTK dengan peran Wali Kelas memiliki ringkasan seluruh rombel perwaliannya pada tahun aktif, lengkap dengan jumlah siswa, nama Wali Kelas, dan Ketua Kelas.
- Daftar Kelas utama dan kartu kelas pada menu Jadwal Pelajaran kini sama-sama menampilkan Wali Kelas serta Ketua Kelas. Detail rombel tetap menjadi tempat penetapan keduanya.

### Periode Pengumuman Kelulusan dan Data Lulusan/PTN

- `Pengumuman Kelulusan` kini terikat pada angkatan tahun pelajaran saat admin menyimpan publikasi. Menu, kartu dashboard, halaman, dan aksi buka amplop baru tersedia setelah jadwal tayang serta hanya untuk siswa kelas 12 yang hasil individualnya sudah ditetapkan.
- `Data Lulusan` ditegaskan sebagai pendataan studi lanjut/PTN. Admin dapat memilih angkatan kelas 12, waktu mulai, waktu tutup, serta membuka atau menutup akses siswa aktif.
- Menu, form, penyimpanan, serta pencarian referensi kampus/prodi memakai aturan akses yang sama sehingga URL langsung tidak dapat melewati periode.
- Siswa berstatus lulus/alumni tetap dapat memperbarui data studi lanjut berdasarkan riwayat kelas 12 walaupun periode siswa aktif sudah ditutup.

### Overlay dan penutupan tab Login As

- Form `Login As` yang membuka tab baru tidak lagi mengaktifkan overlay pada tab admin asal.
- Tombol `Kembali ke Admin` mengakhiri impersonasi, menghapus cookie scoped, memfokuskan tab admin asal, lalu menutup tab Login As.
- Jika browser menolak penutupan tab otomatis, halaman konfirmasi menyediakan tautan kembali ke halaman admin.

### Login As melewati force setup

- Admin yang memakai `Login As` ke akun siswa/GTK dengan password awal langsung dapat membuka dashboard target.
- Status `is_first_login`, password, dan data keamanan akun target tidak diubah oleh Login As.
- Jika URL force setup siswa dibuka saat Login As, sistem kembali ke dashboard siswa; request perubahan password tetap ditolak.

### Ketua Kelas pada rombel dan rekam didik siswa

- Detail rombel kini dapat menetapkan atau mengosongkan Ketua Kelas dari daftar siswa aktif.
- Siswa yang sedang menjabat mendapat badge `Ketua Kelas` pada detail rombel, daftar siswa, dan profil siswa.
- Masa jabatan menyimpan waktu mulai, selesai, dan admin yang menetapkan pada riwayat `siswa_kelas`.
- Pergantian ketua, pindah rombel, keluar rombel, atau mutasi menutup masa jabatan aktif tanpa menghapus rekam didiknya.
- Detail siswa memiliki tabel `Rekam Didik & Jabatan Kelas` lintas tahun pelajaran serta activity log penetapan dan akhir jabatan.

### Verifikasi keberadaan siswa di rombel

Detail rombel sekarang memiliki kolom `Keberadaan` untuk pengguna yang memiliki izin edit kelas. Tombol `Ada / Belum dicek` menyimpan hasil verifikasi fisik siswa pada keanggotaan rombel aktif, lengkap dengan waktu dan akun pemeriksa. Status verifikasi otomatis direset ketika siswa ditempatkan atau dipindahkan ke rombel lain.

Setelah pindah rombel berhasil, dialog menampilkan tombol `Selesai` dan memuat ulang halaman rombel asal; halaman tidak lagi berpindah otomatis ke rombel tujuan.

Perubahan database menggunakan migration `2026_07_29_090000_add_keberadaan_verification_to_siswa_kelas.php`.

Hotfix pada tanggal yang sama mengganti directive PHP singkat di tabel detail rombel menjadi blok Blade yang kompatibel dengan compiler produksi, sehingga halaman detail rombel tidak lagi menghasilkan `ParseError`.

Filter tahun pelajaran, tingkat, kurikulum, dan jurusan pada daftar kelas sekarang langsung memuat ulang tabel ketika pilihan berubah. Tombol `Terapkan` dihapus, sedangkan tombol `Reset` tetap tersedia.

Tombol `Setujui Mutasi` pada detail mutasi kembali merespons setelah plugin SweetAlert2 diaktifkan pada halaman tersebut. Alur persetujuan juga memiliki fallback konfirmasi native dan submit POST biasa apabila plugin eksternal gagal dimuat.

Detail rombel sekarang memiliki aksi bulk `Cek Keberadaan Semua`. Setelah konfirmasi, seluruh siswa aktif yang masih berstatus `Belum dicek` ditandai `Ada` sekaligus, dengan waktu dan akun pemeriksa tetap tercatat.

Form mutasi siswa diperjelas: hasil pencarian siswa pada dropdown memakai warna, border, dan tipografi yang lebih tegas; alasan mutasi keluar menjadi dropdown pilihan baku; serta tanggal mutasi dapat ditentukan langsung pada langkah detail mutasi dan ditampilkan kembali pada ringkasan. Form edit memakai pilihan alasan yang sama dan tetap mendukung nilai lama.

### Login As siswa dan GTK tanpa menghapus sesi admin

Data Siswa dan Data GTK kini mempunyai tombol `Login As` khusus akun dengan role `Super Admin` atau `Admin`. Tombol bekerja langsung tanpa modal konfirmasi dan tanpa alasan, lalu membuka akun tujuan pada tab baru.

Sesi admin utama tidak diganti. Sistem memakai token acak yang hanya disimpan dalam bentuk hash dan dua cookie `HttpOnly` terpisah dengan masa aktif 60 menit:

- cookie siswa hanya berlaku pada path `/siswa`;
- cookie GTK hanya berlaku pada path `/admin/gtk`.

Halaman impersonasi menampilkan banner identitas dan tombol `Kembali ke Admin`. Tombol logout pada menu pengguna juga diarahkan untuk hanya mengakhiri mode Login As. Perubahan password siswa/GTK diblokir selama impersonasi. Admin tidak dapat Login As ke dirinya sendiri, akun tidak aktif, serta akun Admin/Super Admin/Operator lain.

Setiap mulai dan mengakhiri Login As dicatat ke activity log. Tabel `user_impersonations` juga menyimpan admin pelaksana, akun tujuan, tipe akun, IP, user-agent, waktu kedaluwarsa, pemakaian terakhir, dan status penghentian. Permission baru `impersonate-users` diberikan hanya kepada role `Super Admin` dan `Admin`.

File utama:

- `app/Http/Controllers/Admin/UserImpersonationController.php`
- `app/Http/Middleware/ApplyUserImpersonation.php`
- `app/Models/UserImpersonation.php`
- `database/migrations/2026_07_28_170000_create_user_impersonations_table.php`
- `resources/views/partials/impersonation-banner.blade.php`
- `tests/Feature/UserImpersonationFlowTest.php`
- `tests/Unit/UserImpersonationArchitectureTest.php`

Validasi lokal:

- migrasi nyata berhasil;
- permission terverifikasi aktif pada Super Admin/Admin dan tidak aktif pada Operator;
- alur riil siswa dan GTK membuktikan cookie path terpisah, halaman target tampil sebagai akun tujuan, penghentian impersonasi berhasil, dan sesi admin tetap aktif;
- 77 pengujian lulus dengan 514 assertions;
- pengujian alur riil turut merender dashboard siswa dalam mode Login As dan memverifikasi banner identitas;
- hanya muncul enam deprecation PDO lama yang sudah dikenal.

### Export Excel siswa menjaga digit NIK

Export siswa kini memakai custom value binder untuk menyimpan NIK dan identifier numerik panjang sebagai string eksplisit di XLSX. Kolom NISN, NIS Lokal, nomor tes, username, password default, NIK siswa, nomor HP, NPSN, NIK/HP orang tua, dan No. KK tidak lagi diproses sebagai angka Excel sehingga digit terakhir maupun nol di depan tetap utuh. Posisi format dan lebar 34 kolom juga diselaraskan kembali sampai kolom `AH`.

Validasi round-trip XLSX membuktikan NIK 16 digit, NIS Lokal 18 digit, NIK orang tua, NISN dengan nol di depan, dan No. KK tetap identik serta bertipe string setelah workbook disimpan dan dibuka kembali.

File terkait:

- `app/Exports/SiswaExport.php`
- `tests/Unit/SiswaExportIdentifierTest.php`

### Hotfix kompilasi detail Pemilihan OSIS

Sintaks Blade singkat `@php(...)` yang menempel langsung dengan HTML menyebabkan `ParseError: unexpected token ">"` pada detail pemilihan di PHP produksi. Seluruh assignment kandidat pada panel admin dan halaman pemilih telah diubah menjadi blok `@php ... @endphp`. Directive kondisi tersembunyi pada form pengaturan juga dipisahkan per baris. Lima compiled view terkait OSIS sudah diperiksa dengan `php -l` dan seluruhnya valid.

### Paket kandidat dinamis, jeda pemilihan, dan peringkat Live Poll

Pemilihan OSIS kini mendukung susunan pengurus dan jumlah paket yang lebih fleksibel:

1. Saat membuat pemilihan, admin memilih posisi kandidat melalui checklist Ketua, Wakil Ketua, Sekretaris, dan Bendahara.
2. Ketua wajib dipilih dan setiap paket minimal memiliki dua posisi. Pemilihan baru memakai default Ketua dan Wakil Ketua.
3. Pemilihan lama tetap memakai susunan Ketua, Sekretaris, dan Bendahara agar paket yang sudah tersimpan tidak berubah.
4. Modal tambah/edit paket membentuk slot kandidat secara otomatis sesuai checklist dan tetap mencegah satu siswa dipakai pada dua posisi atau paket.
5. Pemilihan berstatus publik dapat dijeda. Saat dijeda, voting berhenti tanpa menghapus DPT, suara, paket, atau kandidat.
6. Dalam mode jeda admin dapat mengubah informasi pemilihan, petunjuk, jadwal, serta profil paket seperti nomor, nama, slogan, visi, misi, program, dan pesan.
7. Tahun pelajaran, DPT, kebijakan hak pilih kandidat, susunan posisi, dan identitas siswa kandidat tetap terkunci.
8. Admin dapat melanjutkan kembali pemilihan setelah jadwal selesai diperpanjang bila diperlukan.
9. Halaman pemilih dan Live Poll menampilkan status `Dijeda` secara tegas selama voting dihentikan.
10. Live Poll menyesuaikan satu sampai enam paket dalam satu baris desktop dan memakai mode lebih padat untuk lima atau enam paket; layar kecil tetap tersusun vertikal.
11. Paket otomatis diurutkan dari suara tertinggi. Perpindahan peringkat memakai animasi FLIP sehingga perubahan posisi terlihat halus tanpa reload.
12. Warna paket melekat pada nomor paket, sehingga identitas visual tidak bertukar saat urutan peringkat berubah.

File terkait:

- `database/migrations/2026_07_28_130000_add_dynamic_roles_and_pause_to_osis_elections.php`
- `app/Models/OsisElection.php`
- `app/Models/OsisPackage.php`
- `app/Services/OsisElectionService.php`
- `app/Http/Controllers/Admin/OsisElectionController.php`
- `app/Http/Controllers/PublicOsisPollingController.php`
- `resources/views/admin/osis-election/form.blade.php`
- `resources/views/admin/osis-election/show.blade.php`
- `resources/views/public/osis-polling.blade.php`
- `resources/views/siswa/osis-election/index.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`
- `tests/Unit/OsisElectionStateTest.php`

### Live Polling OSIS satu layar

Layar publik `live-polling-osis` disempurnakan sebagai dashboard monitoring satu viewport:

1. Desktop dan layar presentasi memakai tinggi tepat `100dvh` serta grid adaptif sehingga header, judul, countdown, metrik, paket kandidat, hasil, dan footer muat tanpa scroll.
2. Ukuran logo, judul, statistik, kartu paket, foto kandidat, dan area hasil dipadatkan secara proporsional tanpa mengurangi keterbacaan.
3. Tersedia mode ekstra-compact otomatis untuk layar desktop dengan tinggi maksimal 760px.
4. Foto kandidat mempertahankan rasio portrait dengan ukuran seimbang agar wajah tetap mudah dikenali tanpa membuat halaman melampaui viewport.
5. Setiap paket memperoleh aksen warna tersendiri pada nomor, garis cahaya, panel, dan progress hasil sehingga mudah dibedakan dari jarak jauh.
6. Hingga empat paket dapat berjajar dalam satu baris desktop.
7. Tablet dan ponsel tetap memakai alur vertikal dengan scroll normal agar tidak memaksa konten menjadi terlalu kecil.
8. Live refresh, countdown, mode readonly, anonimitas pemilih, dan tombol fullscreen tetap dipertahankan.
9. Konflik CSS pada empty state diperbaiki sehingga panel `Belum Ada Live Polling` tidak muncul ketika pemilihan aktif.

File terkait:

- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Pengaturan sekolah vertikal dan monitoring Pemilihan OSIS

Penyempurnaan terbaru:

1. Form Data Sekolah memakai lebar baca maksimum 620px agar tidak melebar berlebihan pada monitor desktop.
2. Nama sekolah, NPSN, NSM, sumber data, alamat, RT, RW, kode pos, provinsi, kota/kabupaten, kecamatan, dan kelurahan/desa ditampilkan berurutan secara vertikal.
3. NSM menjadi field readonly dan hanya diperbarui oleh tombol `Ambil Data` melalui Referensi Kemendikdasmen serta pelengkapan EMIS berdasarkan NPSN.
4. Penyimpanan Pengaturan tidak lagi menerima perubahan NSM dari input form biasa sehingga awalan NIS Lokal tetap bersumber dari referensi.
5. Foto pada browser kandidat di modal tambah/edit Paket OSIS diperkecil menjadi thumbnail 58x72px dengan susunan kartu horizontal yang lebih ringkas.
6. API kandidat memakai pagination 12 siswa per halaman. Daftar berikutnya dimuat otomatis ketika pengguna menggulir mendekati bagian bawah browser kandidat.
7. Tombol `Live Poll Fullscreen` tersedia dari detail Pemilihan OSIS yang telah dipublikasikan dan membuka layar monitoring readonly di tab/jendela baru.
8. Layar Live Poll memiliki tombol untuk masuk/keluar mode fullscreen.
9. Pemilihan yang sudah dipublikasikan tetapi belum mencapai waktu mulai tetap dapat ditampilkan sebagai layar monitoring dengan status `MENUNGGU` dan countdown menuju waktu mulai.
10. Setelah waktu mulai, countdown otomatis berubah menjadi sisa waktu pemungutan suara dan hasil tetap diperbarui berkala tanpa reload.

File terkait:

- `app/Http/Controllers/Admin/AppSettingController.php`
- `app/Http/Controllers/Admin/OsisElectionController.php`
- `app/Http/Controllers/PublicOsisPollingController.php`
- `resources/views/admin/settings/edit.blade.php`
- `resources/views/admin/osis-election/show.blade.php`
- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/SettingsUiArchitectureTest.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Penataan ulang UI/UX Pengaturan Aplikasi

Halaman Pengaturan Aplikasi ditata ulang agar alur pengisian lebih jelas dan responsif:

1. Urutan informasi sekolah menjadi Identitas Sekolah & Logo, Alamat Sekolah/Wilayah Administratif, Informasi Kontak, Kepala Sekolah, lalu Media Sosial.
2. Area branding ditempatkan setelah informasi sekolah: Logo Sekolah, Pengaturan Ukuran Logo untuk Cetak PDF, lalu Kop Surat.
3. Identitas dan unggah logo dipisahkan menjadi kartu mandiri agar halaman lebih mudah dipindai.
4. Tampilan kartu dinormalisasi dengan header putih, garis aksen, jarak konsisten, dan bayangan ringan.
5. Area unggah logo, tombol Ambil Data NPSN, pilihan mode kop, unggah kop, informasi kepala sekolah, serta tombol simpan menyesuaikan layar tablet dan ponsel.
6. Preview kop surat dapat digeser horizontal di layar sempit sehingga komposisi dokumen tidak rusak.
7. Tombol Simpan Pengaturan dibuat tetap mudah dijangkau dan berubah menjadi tombol selebar layar pada ponsel.
8. Ditambahkan `SettingsUiArchitectureTest` untuk menjaga urutan bagian dan aturan responsif.
9. Area data sekolah dipadatkan dengan header dan body kartu yang lebih ringkas, kontrol setinggi 36px, serta jarak field yang konsisten.
10. Alamat, RT, RW, kode pos, dan empat tingkat wilayah administratif kini disusun vertikal dalam satu kolom terpusat.
11. Telepon, email, website, serta kanal media sosial juga disusun vertikal agar konsisten dan mudah dipindai.
12. Panel petunjuk wilayah diringkas menjadi satu callout, metadata sumber data memakai panel status ringan, dan informasi kepala sekolah memakai foto serta tabel yang lebih compact.
13. Seluruh susunan kembali bertumpuk secara responsif pada layar tablet/ponsel agar field tetap nyaman disentuh dan dibaca.
14. Penyempurnaan berikutnya menyatukan Identitas, Alamat/Wilayah, Kontak, Kepala Sekolah, Media Sosial, dan Logo/PDF ke dalam satu panel utama seperti pola visual Detail Siswa.
15. Panel utama memiliki contextual hero dan ringkasan NPSN/NSM.
16. Seluruh kelompok data tampil sebagai satu form kontinu, bukan kartu-kartu terpisah; Kop Surat dan Audit tetap menjadi alat lanjutan di bawah panel utama.
17. Footer Simpan tidak lagi sticky/mengambang sehingga tidak menutupi field saat pengguna menggulir halaman.
18. Penyempurnaan terbaru menggabungkan seluruh field identitas, alamat/wilayah, kontak, kepala sekolah, dan media sosial menjadi satu form kontinu tanpa header section berulang.
19. Nama Sekolah, NPSN/Ambil Data, NSM readonly, dan Sumber Data disusun vertikal agar urutan pengisian lebih tegas.
20. Navigasi anchor dihapus karena tidak lagi diperlukan; pemisah tegas baru digunakan ketika masuk ke area Logo/PDF.

File terkait:

- `resources/views/admin/settings/edit.blade.php`
- `tests/Unit/SettingsUiArchitectureTest.php`

### NIS Lokal siswa dan autofill identitas sekolah

SIMANSA kini memiliki modul `Manajemen Data > NIS Lokal` dengan ketentuan:

1. Format NIS Lokal adalah `NSM 12 digit + tahun masuk 2 digit + nomor urut 4 digit`.
2. Nomor urut dimulai kembali dari `0001` setiap tahun masuk.
3. Generator hanya berlaku untuk siswa aktif tingkat 10 pada tahun pelajaran aktif.
4. Urutan generator bersifat deterministik: rombel X-1 sampai X-13, lalu nama siswa A-Z di setiap rombel.
5. Saat generator disimpan, `nomor_urut_absen` setiap rombel tingkat 10 ikut disinkronkan berdasarkan urutan nama.
6. Preview generator disimpan sebagai token privat selama 30 menit dan harus dikonfirmasi sebelum perubahan database.
7. Sequence dikunci dalam transaksi agar operator yang bekerja bersamaan tidak menerbitkan nomor ganda.
8. NIS yang sudah diterbitkan tidak dibuat ulang; perubahan dan penerbitan dicatat dalam activity log.
9. Tingkat 11/12 diperbarui melalui Excel dengan kolom `nislokal`, `nisn`, `namalengkap`.
10. Impor Excel menyediakan upload progress, live preview, validasi format/duplikasi, pencocokan utama NISN, dan smart name matching untuk nama mirip atau disingkat.
11. Baris Excel yang bermasalah ditandai dan tidak ikut disimpan; hanya baris berstatus siap yang diproses setelah konfirmasi.
12. NIS Lokal ditampilkan pada data siswa, detail siswa, detail rombel, ekspor Excel, dan digunakan sebagai identitas NIS utama pada pencocokan RDM dengan fallback username lama.
13. Permission baru `manage-nis-lokal` diberikan awal kepada Super Admin, Admin, dan Operator.

Pengaturan Aplikasi sudah memiliki NPSN. Pada perubahan ini ditambahkan NSM dan tombol `Ambil Data` yang memakai layanan Referensi Kemendikdasmen serta pelengkapan EMIS yang sebelumnya digunakan Statistik Siswa. Hasil resmi mengisi nama sekolah, NPSN, NSM, alamat, wilayah, kode pos, telepon, email, dan website bila tersedia.

File utama:

- `app/Services/NisLokalService.php`
- `app/Http/Controllers/Admin/NisLokalController.php`
- `resources/views/admin/nis-lokal/index.blade.php`
- `app/Services/AppSettingSchoolEnrichmentService.php`
- `resources/views/admin/settings/edit.blade.php`
- `database/migrations/2026_07_28_030000_add_nis_lokal_support.php`
- `tests/Unit/NisLokalArchitectureTest.php`

### Modal penugasan wali kelas

Modal `Tugaskan Wali Kelas` pada detail rombel telah diperbarui:

1. Dropdown memakai Select2 dan selalu menyediakan kolom pencarian.
2. Pencarian dapat memakai nama guru, jenis guru, atau nama rombel.
3. Kandidat hanya user aktif dengan data GTK berkategori `Pendidik` dan jenis `Guru Mapel`/`Guru BK`.
4. Tenaga kependidikan tidak lagi muncul dan ditolak pula oleh validasi server.
5. Guru yang sudah menjadi wali kelas lain tetap tersedia.
6. Di samping nama guru ditampilkan jenis guru dan metadata rombel aktif, misalnya `Wali: XII-A6`.
7. Guru yang belum memiliki rombel diberi metadata `Belum menjadi wali kelas`.
8. Modal dan dropdown dinormalisasi menggunakan tampilan default Bootstrap/AdminLTE dan Select2.
9. Gradient, renderer opsi khusus, badge warna, serta override tinggi/border/focus Select2 telah dihapus untuk mencegah bentrok dengan custom CSS global.
10. Metadata tetap tampil dalam satu baris teks opsi: `Nama | Jenis Guru | Status Rombel`.

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

## Validasi terakhir

- Seluruh unit test SIMANSA: 72 lulus, 447 assertions; 4 peringatan deprecation PDO lama.
- Migrasi OSIS baru berhasil dijalankan nyata pada database lokal; migrasi awal juga dinormalisasi dari `timestamp` wajib ke `dateTime` agar kompatibel dengan MySQL lama pada instalasi baru.
- Blade template dan sintaks JavaScript Live Poll berhasil dikompilasi/divalidasi.
- Feature test contoh bawaan masih gagal pada ekspektasi lama HTTP 200 untuk `/`, karena aplikasi memang mengalihkan root dengan HTTP 302; kegagalan ini tidak terkait fitur OSIS.
- Fitur paket dinamis dan mode jeda tercatat pada commit `2decaf8`, telah di-push dan di-deploy ke produksi.
- Migrasi produksi `2026_07_28_130000_add_dynamic_roles_and_pause_to_osis_elections` selesai pada batch 92; kolom `candidate_roles`, `paused_at`, dan `vice_chairman_id` telah terverifikasi.
- GitHub dan VM sinkron, maintenance mode OFF, Live Poll HTTP 200, login HTTP 200, serta halaman admin anonim mengarah ke login.
- Unit test khusus NIS Lokal: 4 lulus, 18 assertions.
- Preview generator telah diuji read-only terhadap database lokal: 13 rombel terurut X-1 sampai X-13, siswa terurut nama, serta NIS awal/akhir konsisten.
- Simulasi transaksi penuh berhasil menerbitkan 439 NIS dan mengisi seluruh nomor absen tingkat 10; transaksi kemudian di-rollback dan diverifikasi tidak meninggalkan NIS/sequence.
- Workbook Excel nyata tingkat 11 berhasil dipreview: pencocokan NISN tepat, skor nama 100%, satu baris siap, tanpa penyimpanan data.
- Autofill NPSN `10648374` berhasil diuji langsung terhadap Referensi Kemendikdasmen dan memetakan nama MAN 1 Metro, alamat, RT/RW, seluruh kode wilayah, kode pos, telepon, email, serta website; transaksi pengujian di-rollback.
- Blade template berhasil dikompilasi.
- Commit fitur `85bd949` berhasil di-push dan di-deploy; migrasi produksi selesai.
- Produksi memiliki kolom NIS Lokal, sequence, NSM `131118720001`, permission `manage-nis-lokal`, serta akses awal untuk Super Admin, Admin, dan Operator.
- Tidak ada NIS yang diterbitkan otomatis setelah deploy: jumlah NIS terisi `0` dan sequence `0`.
- Preview read-only produksi menemukan 517 siswa tingkat 10 pada tahun masuk 2026, terurut X-1 sampai X-13, dengan usulan awal `131118720001260001` dan akhir `131118720001260517`.
- GitHub dan VM sinkron, maintenance mode OFF, halaman login HTTP 200, dan rute NIS Lokal mengarahkan pengguna anonim ke login.

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
