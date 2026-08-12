# Perubahan Terakhir MAN 1 Metro

Tanggal pembaruan: 12 Agustus 2026, zona waktu Asia/Jakarta.

## Ringkasan terkini

### Pemusatan Setting Hotspot

- Badge status FreeRADIUS pada seluruh hero Hotspot memakai latar terang dengan teks hijau/merah berkontras tinggi agar tidak tenggelam oleh warna gradient.
- Halaman **Akun Hotspot** kini difokuskan pada statistik akun, daftar akun, sinkronisasi, profile akun, serta akun tamu.
- Detail server FreeRADIUS, status RADIUS, Profile RADIUS, MikroTik/NAS, script konfigurasi, dan tautan dashboard FreeRADIUS dipusatkan pada halaman **Hotspot > Setting**.
- Menu Profile RADIUS pada sidebar digantikan oleh Setting; halaman Profile RADIUS tetap tersedia dari pusat Setting.
- Akses Setting dibatasi dengan permission `manage-hotspot`, sedangkan Monitoring dan Log Autentikasi tetap memakai akses baca `view-hotspot`.

### Audit dan hardening Hotspot

- Endpoint Hotspot dipisahkan menjadi akses baca `view-hotspot` dan akses perubahan `manage-hotspot`.
- Fallback password berupa username/NISN/NIK dihapus; akun tanpa password aman ditolak sampai password SIMANSA di-reset.
- Password akun tamu disimpan terenkripsi agar perubahan status dan masa berlaku selalu tersinkron ke FreeRADIUS.
- Sinkronisasi berkala menonaktifkan akun tamu yang kedaluwarsa.
- Kredensial produksi dihapus dari script deployment dan dokumentasi publik, wajib diberikan melalui environment atau secret store saat provisioning.
- Infrastruktur MikroTik, FreeRADIUS, MariaDB, scheduler, NAS, profile, accounting, serta rekonsiliasi akun telah diaudit langsung.
- Monitoring Hotspot kini menampilkan foto, identitas, rombel, profile, perangkat, pemakaian data, histori sesi, modal detail, serta link ke halaman siswa/GTK dan rombel.
- Log autentikasi memiliki halaman khusus dengan klasifikasi login berhasil, username tidak dikenal, akun nonaktif/expired, password belum aman, dan dugaan password salah.
- Password percobaan tidak lagi disimpan pada `radpostauth`; nilai historisnya sudah dikosongkan.
- Profile RADIUS dipindahkan ke halaman khusus dengan pemeriksaan drift terhadap `radgroupreply`/`radgroupcheck`, atribut `Mikrotik-Group`, dan tombol sinkronisasi.
- Dashboard daloRADIUS dapat dibuka langsung dari navigasi modul Hotspot.
- Dynamic Simple Queue Hotspot tetap dipertahankan. RouterOS menolak komentar pada objek dinamis, sehingga nama lengkap ditampilkan di monitoring SIMANSA tanpa membuat queue statis yang berisiko bentrok.
- Kebijakan login mempertahankan kompatibilitas akun GTK lama: password yang sama dengan NIK 16 digit tetap diterima khusus role guru; password siswa yang sama dengan NISN tetap ditolak.

File terkait:

- `routes/web.php`
- `app/Console/Commands/HotspotSync.php`
- `app/Http/Controllers/Admin/HotspotController.php`
- `app/Models/HotspotUser.php`
- `database/migrations/2026_08_12_140000_harden_hotspot_access_and_guest_credentials.php`
- `deploy/setup_mikrotik.sh`
- `deploy/install-radius.sh`
- `tests/Unit/HotspotSecurityArchitectureTest.php`

### Modul Alumni Kesiswaan

- Menu **Kesiswaan > Alumni** menjadi arsip khusus siswa yang sudah lulus dan tidak bercampur dengan siswa aktif.
- Daftar alumni menyediakan pencarian identitas, filter tahun kelulusan dan jenis kelamin, statistik ringkas, serta grafik jumlah alumni lintas angkatan.
- Halaman detail menyajikan identitas, data kelulusan, histori kelas, catatan perpindahan, dan riwayat tujuan perguruan tinggi.
- Finalisasi kelulusan kelas XII sekarang selalu mengubah status siswa menjadi `lulus`, mengosongkan referensi kelas aktif, dan mempertahankan seluruh histori kelas.
- Migrasi koreksi mengarsipkan data kelulusan lama yang masih berstatus siswa aktif.

File terkait:

- `app/Http/Controllers/Admin/AlumniController.php`
- `resources/views/admin/alumni/index.blade.php`
- `resources/views/admin/alumni/show.blade.php`
- `app/Http/Controllers/Admin/KenaikanKelasController.php`
- `resources/views/admin/kenaikan-kelas/index.blade.php`
- `database/migrations/2026_08_12_120000_archive_graduated_students_as_alumni.php`
- `tests/Unit/AlumniModuleArchitectureTest.php`

### Perbaikan tombol browse foto profil GTK

- Klik pada area upload foto GTK kini langsung membuka pemilih file bawaan browser.
- Input file dipindahkan keluar dari elemen dropzone untuk mencegah event klik berulang akibat bubbling.
- Tombol **Pilih Ulang** dan akses keyboard Enter/Spasi menggunakan mekanisme browse yang sama.

File terkait:

- `resources/views/admin/gtk/index.blade.php`
- `tests/Unit/GtkIndexUiArchitectureTest.php`

### Rasio galeri OSIM dan update foto profil GTK

- Galeri kandidat pada Live Polling OSIM kini menggunakan bingkai potret 4:5 seperti foto profil, dengan `object-fit: contain` dan posisi tengah agar foto terlihat utuh.
- Menu aksi pada Data GTK kini menyediakan **Update foto profil** langsung tanpa membuka halaman edit.
- Modal foto mendukung pemilihan file dan drag-and-drop, crop potret 4:5, zoom, rotasi, preview, progress AJAX, serta pemilihan ulang.
- File kamera JPG, PNG, atau WEBP hingga 20 MB dapat dipilih. Browser menghasilkan JPEG 720 × 900 dan mengompresnya sebelum upload; server kembali menormalisasi serta mengompresi hasil untuk keamanan dan konsistensi.
- Foto lama baru dihapus setelah foto baru berhasil tersimpan, sehingga kegagalan upload tidak menghilangkan foto sebelumnya.

File terkait:

- `app/Http/Controllers/Admin/GtkController.php`
- `resources/views/admin/gtk/index.blade.php`
- `resources/views/admin/gtk/edit.blade.php`
- `resources/views/public/osis-polling.blade.php`
- `tests/Unit/GtkIndexUiArchitectureTest.php`
- `tests/Unit/OsisElectionExperienceTest.php`

### Perbaikan duplikasi Polling Aktif pada akun siswa

- Gate sidebar Polling Aktif kini dipisahkan antara responden GTK dan Siswa.
- Akun siswa hanya melihat menu polling siswa pada posisi yang benar di bawah **Pemilihan OSIM**; menu polling GTK tidak lagi ikut muncul di bagian atas sidebar.
- Akun GTK murni tetap memperoleh menu polling GTK apabila memiliki polling yang sedang aktif.

File terkait:

- `app/Providers/AuthServiceProvider.php`
- `config/adminlte.php`
- `tests/Unit/PollingSidebarArchitectureTest.php`

### Navigasi slot pada Monitor Jadwal publik

- Slot jam pada `/monitor-jadwal` kini dapat diklik untuk menampilkan daftar kelas, mata pelajaran, dan guru pada jam yang dipilih.
- Pilihan manual diberi penanda visual tersendiri dan tidak ditimpa oleh pembaruan jam otomatis maupun refresh berkala.
- Tombol **Jadwal Saat Ini** mengembalikan monitor ke mode live dan memusatkan kembali timeline pada slot yang sedang berlangsung.
- Slot timeline menggunakan elemen tombol yang mendukung keyboard dan tetap responsif pada layar TV maupun perangkat seluler.

File terkait:

- `resources/views/public/jadwal-monitor.blade.php`
- `tests/Unit/JadwalWakakurImportServiceTest.php`

### Kegiatan pembuka pada konfigurasi jam pelajaran

- Generator jam pelajaran kini menyediakan pengaturan **Upacara hari Senin** dengan durasi bawaan 30 menit dan **Religi selain Senin** dengan durasi bawaan 15 menit.
- Upacara dan Religi disimpan sebagai slot pembuka tanpa nomor jam, sehingga pelajaran pertama tetap menggunakan `jam_ke = 1`.
- Preview menampilkan perbandingan jadwal Senin dan selain Senin secara langsung sebelum admin melakukan generate.
- Waktu pelajaran, istirahat, dan jadwal mapel digeser sesuai kegiatan pembuka serta dibatasi agar tidak melewati jam pulang.
- Pengaturan kegiatan pembuka tersimpan per tahun pelajaran dan diterapkan ke semester ganjil maupun genap.

File terkait:

- `app/Http/Controllers/Admin/JadwalJamConfigController.php`
- `app/Models/TahunPelajaran.php`
- `database/migrations/2026_08_10_090000_add_opening_activities_to_tahun_pelajaran_table.php`
- `resources/views/admin/jadwal-jam-config/index.blade.php`
- `tests/Unit/JadwalOpeningActivityArchitectureTest.php`

### Import jadwal Excel Wakakur

- Modul **Import Jadwal Wakakur** membaca template `.xls/.xlsx` dengan sheet `Kode_GTK_mapel` dan `jadwal`, termasuk kode gabungan seperti `56S`.
- Upload selalu menghasilkan preview; kode GTK, mapel, kelas, dan slot ganda harus valid sebelum jadwal semester ditimpa.
- Slot BK dilaporkan tetapi tidak dimasukkan sebagai jadwal kelas reguler; jadwal yang valid disimpan dengan slot waktu dari konfigurasi jam SIMANSA.
- Normalisasi kelas menyamakan format template `12-A1` dengan master kelas `XII-A1`.
- Jika kode GTK pada revisi template berubah, importer memakai nama GTK pada sheet kode sebagai fallback eksak yang aman.

### Sinkronisasi kode jadwal Wakakur

- Master mata pelajaran menyimpan `kode_jadwal` terpisah untuk format Aâ€“Z dari jadwal Wakakur 2026/2027; kode internal `kode_mapel` tetap dipertahankan demi nilai dan RDM.
- Migrasi mengisi 26 kode dari A (Qur'an Hadist) hingga Z (Bimbingan Konseling), dan penyegaran mapping jadwal akan mempertahankannya.
- Tampilan slot jadwal kini memprioritaskan kode Wakakur, dengan fallback aman ke kode internal pada data lama.

### Perbaikan identitas GTK

- Form Edit GTK pada tab Data Pribadi kini menyediakan field **PEG ID / ID PTK** di samping NIK dan NUPTK.
- Nilai divalidasi unik pada server sebelum disimpan, lalu ikut tampil pada ringkasan identitas di panel kiri.
- Field memakai data `peg_id` yang telah ada pada tabel GTK dan proses sinkronisasi workbook, sehingga tidak memerlukan migrasi baru.

File terkait:

- `app/Http/Controllers/Admin/GtkController.php`
- `resources/views/admin/gtk/edit.blade.php`
- `tests/Unit/GtkEditUiArchitectureTest.php`

### Pusat Face Recognition GTK dan Siswa

- Menu Presensi kini memiliki modul **Face Recognition** terpusat untuk Registrasi Wajah serta Verifikasi & Data biometrik.
- Admin dapat berpindah antara data GTK dan Siswa melalui pilihan responden yang konsisten pada hero halaman; seluruh daftar, statistik, registrasi, dan antrean verifikasi mengikuti pilihan tersebut.
- Approval, penolakan, reset, dan penghapusan data wajah kini mendukung GTK maupun Siswa dengan scope `user_type` yang divalidasi di server.
- Akun GTK dan Siswa hanya dapat melakukan registrasi pertama atau satu kali registrasi ulang setelah admin membuka izin. Setelah tersimpan, endpoint mengunci perubahan kembali secara otomatis.
- Pada akun pengguna, data yang sudah terekam hanya menampilkan preview hasil registrasi, status akses, riwayat aktivitas biometrik, serta rekap presensi pribadi per bulan, tahun, dan tanggal tanpa tombol presensi mandiri.
- Admin dapat merekam ulang secara langsung atau membuka izin registrasi ulang sementara dari halaman Verifikasi & Data, termasuk untuk rekaman yang sudah ditolak/nonaktif.
- Registrasi wajah pada akun GTK/siswa ditegaskan bukan sebagai pencatatan presensi. Presensi tetap dilakukan melalui kiosk resmi di area madrasah.
- Seluruh registrasi, registrasi ulang, approval, penolakan, reset, pembukaan izin, dan penghapusan dicatat pada activity log Face Recognition.
- Endpoint descriptor massal pada akun siswa tetap tidak tersedia; pemeriksaan duplikasi dijalankan di server. Bug evaluasi pesan duplikasi yang dapat menyebabkan respons 500 pada wajah nonduplikat juga diperbaiki.
- Foto hasil kamera disimpan sebagai preview dengan validasi MIME gambar, batas ukuran 2 MB, dan nama file yang dikendalikan server.
- Descriptor wajah hanya tersedia untuk admin tepercaya dan Mode Kiosk GTK juga dibatasi pada admin. Operasional Absensi GTK dan Absensi Siswa tetap menjadi modul terpisah.
- Hero, statistik, tombol jenis responden, dan konfirmasi tindakan mengikuti baseline UI Data Siswa serta SweetAlert2.

File terkait:

- `app/Http/Controllers/Admin/FaceRegistrationController.php`
- `app/Models/FaceEncoding.php`
- `database/migrations/2026_08_06_003000_add_self_registration_lock_to_face_encodings.php`
- `config/adminlte.php`
- `routes/web.php`
- `resources/views/admin/absensi/face-register.blade.php`
- `resources/views/admin/absensi/face-verification.blade.php`
- `tests/Feature/FaceRecognitionAdminTest.php`
- `tests/Unit/StudentAttendanceArchitectureTest.php`

### Struktur modul Presensi dan Absensi GTK

- Sidebar Presensi kini memisahkan modul **Absensi GTK** dan **Absensi Siswa** agar fungsi kedua jenis responden tidak bercampur.
- Presensi Hari Ini, Mode Kiosk, Rekap Bulanan, Registrasi Wajah, Verifikasi Wajah, dan Pengaturan Presensi dipusatkan di dalam submenu Absensi GTK.
- Permission setiap halaman dan penanda menu aktif tetap mengikuti kewenangan pengguna yang sudah ada.

File terkait:

- `config/adminlte.php`
- `tests/Unit/StudentAttendanceArchitectureTest.php`

### Integritas GTK dan penugasan Asrama

- Halaman Pengasuh & Pengajar Asrama tetap dapat membaca profil GTK yang sudah terlanjur dihapus secara lunak, sehingga data historis tidak lagi menyebabkan Internal Server Error.
- GTK historis diberi penanda **GTK dihapus**, tidak dapat diedit, tetapi tetap dapat dilepas dari tim Asrama melalui alur pembersihan yang aman.
- Penghapusan GTK dari Data GTK kini ditolak apabila GTK masih tercatat pada tim Asrama. Admin harus melepas beban rombel, kamar, dan mapel lalu menghapus penugasannya dari Asrama terlebih dahulu.
- Pengujian mencakup proteksi penghapusan GTK yang masih ditugaskan dan pembacaan relasi GTK yang sudah dihapus secara lunak.

File terkait:

- `app/Models/AsramaAsatidz.php`
- `app/Http/Controllers/Admin/GtkController.php`
- `resources/views/asrama/master/asatidz.blade.php`
- `tests/Feature/AsramaWorkflowTest.php`

### Modul Absensi Siswa untuk Super Admin

- Modul sebenarnya sudah tersedia di `/admin/absensi-siswa`, tetapi sebelumnya belum ditampilkan pada sidebar admin. Menu **Absensi Siswa** kini ditempatkan di dalam **Presensi** bagi akun manajerial yang memiliki izin.
- Submenu mencakup Input Absensi, Pemantauan Harian seluruh siswa, dan Analitik Kehadiran; modul Presensi utama tetap khusus presensi masuk/pulang GTK.
- Super Admin dapat memilih seluruh kelas aktif untuk absensi harian maupun seluruh jadwal aktif kelas untuk absensi per mapel. Wali kelas dan guru tetap dibatasi pada kelas/jadwal masing-masing.
- Teks antarmuka menyesuaikan scope admin dan seluruh perubahan data tetap melewati permission, validasi kelas/jadwal, finalisasi, serta audit yang sudah ada.
- Pola aktif sidebar Presensi diperketat agar URL `absensi-siswa` tidak ikut membuka menu Presensi GTK.

File terkait:

- `app/Providers/AuthServiceProvider.php`
- `config/adminlte.php`
- `app/Http/Controllers/Admin/AbsensiSiswaController.php`
- `resources/views/admin/absensi/siswa.blade.php`
- `tests/Unit/StudentAttendanceArchitectureTest.php`

### Aksi tabel responsif untuk perangkat mobile

- Tabel Data Siswa kini mengikuti pola Data Kelas: kolom penting tetap terlihat, sedangkan informasi lain dapat dibuka sebagai child row pada layar sempit.
- Tombol aksi siswa di child row ditampilkan sebagai grup ikon berukuran sentuh untuk detail, edit, reset password, login sebagai siswa, dan hapus sesuai izin pengguna.
- Daftar riwayat Polling menggunakan baris detail ringan pada mobile; jadwal, target responden, dan aksi laporan/preset/edit dapat dibuka tanpa scroll horizontal.
- Tabel Status Responden pada laporan Polling juga memakai DataTables Responsive agar aksi unlock dan jawaban tetap mudah dijangkau di ponsel.
- Tampilan desktop, otorisasi setiap aksi, dan alur server-side pagination tetap dipertahankan.
- Responsive child-row dibatasi hanya untuk viewport mobile maksimal `767,98px`; laptop/desktop selalu memakai tabel penuh tanpa tombol expand.
- Klik foto siswa menghentikan propagasi event sehingga hanya membuka preview foto dan tidak ikut membuka child-row.
- Perhitungan lebar tabel Data Siswa pada mobile memakai layout otomatis dan header tidak dibungkus per huruf, sehingga DataTables dapat memindahkan kolom sekunder ke child-row tanpa badge atau teks saling menimpa.
- Tabel Status Responden Polling kini memakai renderer child-row khusus mobile: Responden, Status, dan Rombel diprioritaskan, sedangkan waktu, jawaban, serta aksi ditampilkan sebagai pasangan label-nilai yang tidak bertumpuk.

File terkait:

- `app/Http/Controllers/Admin/SiswaController.php`
- `resources/views/admin/siswa/index.blade.php`
- `resources/views/admin/polling/index.blade.php`
- `resources/views/admin/polling/show.blade.php`
- `tests/Unit/StudentTableResponsiveLayoutTest.php`
- `tests/Unit/PollingModuleArchitectureTest.php`

### Optimalisasi laporan admin Polling & Survei

- Card `Target Responden`, `Sudah Mengisi`, `Belum Mengisi`, dan `Partisipasi` kini dapat diklik untuk membuka sekaligus memfilter tabel Status Responden.
- Badge permintaan unlock juga menjadi pintasan langsung ke daftar responden yang meminta pembukaan jawaban.
- Pilihan pada hasil polling diurutkan berdasarkan jumlah pemilih terbanyak; pilihan dengan jumlah sama tetap mengikuti urutan konfigurasi awal.
- Kolom pertanyaan TKA `Pilih tepat dua mata pelajaran pilihan...` diringkas menjadi `Mapel Pilihan` tanpa mengubah pertanyaan maupun data yang tersimpan.
- N+1 query saat membaca rombel aktif dihilangkan dengan memakai relasi yang sudah dimuat. Halaman awal kini menggunakan ringkasan laporan dan tidak lagi membentuk seluruh baris tabel responden.
- Filter status diteruskan ke endpoint DataTables sehingga tabel AJAX hanya mengembalikan kelompok responden yang sedang dipilih.
- Terjemahan DataTables ditanam langsung pada halaman laporan sehingga tabel tidak lagi menunggu berkas bahasa dari CDN eksternal.

File terkait:

- `app/Http/Controllers/Admin/PollingController.php`
- `app/Services/PollingAudienceService.php`
- `app/Services/PollingReportService.php`
- `resources/views/admin/polling/show.blade.php`
- `tests/Unit/PollingReportServiceTest.php`
- `tests/Unit/PollingModuleArchitectureTest.php`

### Perbaikan force password siswa pada perangkat mobile

- Tombol `Lanjut` dan `Simpan & Amankan Akun Saya` tidak lagi bergantung pada status `disabled` dari JavaScript, sehingga autofill/password manager mobile yang tidak memicu event `input` tidak membuat wizard berhenti merespons.
- Validasi password, konfirmasi, dan email wajib tetap dijalankan saat submit serta diperkuat oleh validasi backend; tombol dikunci hanya setelah form valid benar-benar dikirim untuk mencegah submit ganda.
- Form memakai overlay penyimpanan lokal tanpa ditumpuk overlay navigasi global, dengan tombol aksi selebar layar dan tinggi sentuh minimum pada ponsel.
- Inisialisasi form tidak lagi gagal ketika CDN Toastr atau Cropper belum termuat, dan sintaks JavaScript kritis dibuat kompatibel dengan browser mobile lama.
- Ditambahkan pengujian regresi untuk alur siswa pasca-reset admin dengan email pribadi tetap dipertahankan serta progressive enhancement tombol submit.

File terkait:

- `resources/views/siswa/profile/force-setup.blade.php`
- `tests/Feature/StudentForceSetupFlowTest.php`

### Perbaikan resize textarea global

- Textarea pada seluruh form SIMANSA kembali dapat diperbesar atau diperkecil secara vertikal melalui handle native browser.
- Akar masalah diperbaiki pada style bersama: `height: auto !important` kini hanya berlaku untuk input/select dan tidak lagi mengalahkan tinggi hasil drag pada textarea.
- Perubahan mempertahankan lebar textarea agar layout responsif tidak menimbulkan scroll horizontal, serta tidak mengubah perilaku resize bawaan editor visual.

File terkait:

- `public/css/custom-compact.css`
- `docs/UI_DESIGN_PRINCIPLES_UPDATED.md`
- `tests/Unit/UiDesignConsistencyTest.php`

### Modul Polling & Survei lintas akun

- Angka jumlah pada setiap opsi hasil polling kini dapat diklik untuk membuka modal daftar pemilih lengkap dengan rombel/peran dan waktu memilih.
- Tabel `Status Responden` menggunakan DataTables AJAX dengan pencarian serta pagination, sehingga laporan besar tidak lagi merender seluruh baris di halaman awal.
- Setiap jawaban otomatis terkunci setelah dikirim. Responden dapat meminta buka kunci bila diizinkan, admin mendapat badge permintaan dan tombol persetujuan, lalu izin revisi habis setelah jawaban dikirim kembali.
- Judul/Jadwal dan Deskripsi polling kini dipisahkan ke card berbeda agar form lebih terstruktur dan uraian panjang tidak bercampur dengan identitas polling.
- Deskripsi menggunakan editor visual Summernote yang mendukung paragraf/Enter, tebal, miring, garis bawah, serta daftar; hasilnya tampil dalam card `Informasi Polling` pada preview, halaman responden, laporan admin, dan PDF.
- Format deskripsi disanitasi terpusat pada model: elemen berbahaya dan atribut HTML dibuang, sedangkan ringkasan daftar serta modal pengingat memakai versi teks biasa.
- Builder kini memiliki preview responsif yang menampilkan simulasi layar siswa/GTK berdasarkan form saat itu, termasuk jadwal, pertanyaan, opsi, aturan pilihan, dan persetujuan tanpa menyimpan jawaban.
- Dropdown `Preset Cepat` tidak lagi terpotong oleh batas hero; stacking dan overflow hero diperbaiki secara scoped agar menu selalu muncul di atas card operasional berikutnya.
- Setiap polling kini merekam snapshot tahun ajaran, semester, tanggal pembuatan, serta sumber polling jika dibuat dari riwayat. Data ini tetap stabil saat tahun ajaran aktif berubah.
- Daftar admin menjadi `Riwayat Polling & Preset`; setiap entri dapat disalin ke builder baru tanpa mengubah polling dan hasil lama.
- Aksi hapus diganti menjadi arsip non-destruktif. Polling ditutup, tetapi konfigurasi, target, respons, laporan, dan jejak audit tetap tersimpan.
- Builder menyediakan beberapa preset cepat (TKA Kelas XII, Survei Kepuasan, dan Konfirmasi Kegiatan), selain preset tanpa batas yang berasal dari seluruh riwayat polling.
- Salinan polling menyimpan hubungan ke sumbernya dan meminta pengelola memeriksa kembali target aktif, khususnya bila preset berasal dari tahun ajaran lama.
- Pemilih target diperjelas menjadi tiga pilihan responden: Siswa, GTK, atau Siswa & GTK, masing-masing dengan mode Semua dan Custom.
- Target siswa custom menyediakan checklist tingkat X/XI/XII serta daftar rombel aktif yang bisa dicari dan dipilih semua.
- Target GTK custom menyediakan kategori Guru/Staf dan modal tabel GTK individual dengan foto, NIK, ID PTK, jenis PTK, pencarian, serta pilihan massal pada hasil yang tampil.
- Daftar rombel kini langsung difilter berdasarkan tingkat yang dicentang, sedangkan tabel GTK mengikuti kategori Guru/Staf; pencarian dan `Pilih semua yang tampil` menghormati filter gabungan tersebut.
- Scope kategori dan GTK individual disimpan sebagai target backend tersendiri; akses menu, pengingat, halaman pengisian, dan penyimpanan jawaban tetap memakai pemeriksaan scope yang sama.
- Admin dapat membuat polling generik, mengatur periode buka/tutup, opsi perubahan jawaban, persetujuan responden, serta pertanyaan pilihan tunggal, pilihan ganda, ya/tidak, dan teks.
- Target siswa dapat dibatasi berdasarkan tingkat dan rombel aktif; target GTK dapat dibatasi berdasarkan jenis PTK dan role. Validasi target juga dijalankan kembali di backend sehingga URL langsung tidak dapat membuka polling di luar scope akun.
- Preset `TKA Kelas XII` menyediakan contoh siap pakai untuk pemilihan tepat dua mata pelajaran, tetapi builder tetap dapat digunakan untuk kebutuhan polling lain.
- Menu `Polling Aktif` hanya muncul pada akun siswa/GTK yang menjadi target selama jadwal terbuka. Responden yang belum mengisi memperoleh pengingat SweetAlert2 yang lembut dan dapat memilih `Ingatkan Nanti`.
- Setiap akun hanya memiliki satu respons per polling. Jawaban dapat diperbarui hanya jika diizinkan pembuat polling dan seluruh ID opsi divalidasi terhadap pertanyaannya.
- Dashboard hasil menampilkan target, jumlah respons, tingkat partisipasi, statistik per pilihan, serta status setiap responden. Laporan lengkap tersedia dalam Excel dan PDF.
- UI admin dan responden mengikuti Bootstrap 4/AdminLTE, hero utuh, card netral, empty state informatif, serta grid satu kolom pada layar ponsel.

File terkait:

- `app/Http/Controllers/Admin/PollingController.php`
- `app/Http/Controllers/PollingResponseController.php`
- `app/Services/PollingAudienceService.php`
- `app/Services/PollingReportService.php`
- `database/migrations/2026_08_03_090000_create_polling_module_tables.php`
- `database/migrations/2026_08_03_110000_add_history_metadata_to_pollings.php`
- `resources/views/admin/polling/`
- `resources/views/polling/respondent/`
- `docs/POLLING_SURVEI.md`
- `tests/Feature/PollingResponseFlowTest.php`
- `tests/Unit/PollingModuleArchitectureTest.php`

### Perapihan tabel Data Siswa

- Proporsi kolom Foto dan Nama/NISN diseimbangkan serta diberi lebar minimum agar avatar tidak lagi bertabrakan dengan identitas siswa.
- Kolom Kelas diperlebar dan isi rombel, badge `Asrama Kampus 2`, serta penanda ketua kelas disusun vertikal agar tidak meluber ke kolom status.
- Deretan tombol aksi diringkas menjadi satu dropdown Bootstrap `Aksi` yang rata kanan dan tetap memakai ikon FontAwesome untuk detail, edit, reset password, login sebagai siswa, dan hapus.
- Seluruh permission dan handler aksi lama dipertahankan; menu hapus dipisahkan secara visual sebagai tindakan berisiko.
- Tabel tetap menggunakan proporsi desktop yang seimbang dengan fallback scroll horizontal pada layar sempit.

File terkait:

- `app/Http/Controllers/Admin/SiswaController.php`
- `resources/views/admin/siswa/index.blade.php`
- `tests/Unit/StudentTableResponsiveLayoutTest.php`

### Penyempurnaan UI Edit GTK

- Halaman Edit GTK mengikuti struktur operasional standar: judul dan breadcrumb pada `content_header`, satu hero gradient utuh di area konten, lalu card form `card-outline card-primary`.
- Identitas ringkas NIK dan jenis PTK tetap berada di dalam hero dengan panel transparan agar warna tidak terputus oleh card putih.
- Workspace memakai layout enterprise Bootstrap dua kolom: sidebar `col-md-3` berisi foto, dropzone, ringkasan NIK/NUPTK, dan `nav-pills` vertikal; form tab aktif berada pada card utama `col-md-9`.
- Pasangan field menggunakan grid dua kolom, RT/RW/Kode Pos dibagi tiga kolom seimbang, dan pilihan wilayah tetap memakai Select2 responsif.
- Setiap form memiliki aksi Reset dan Simpan yang jelas; dropzone dapat dioperasikan melalui keyboard, ID email akun dipisahkan dari email pribadi, dan pembaruan nama hanya menyentuh judul profil yang benar.

File terkait:

- `resources/views/admin/gtk/edit.blade.php`
- `public/css/custom-compact.css`
- `tests/Unit/GtkEditUiArchitectureTest.php`

### Identitas profesional pada daftar GTK

- Daftar GTK menampilkan kolom ID PTK, Status Inpassing, dan Status Sertifikasi langsung pada tabel utama.
- ID PTK dapat dicari serta diurutkan; status ditampilkan sebagai badge semantik dan nilai kosong diberi label `Belum tercatat`.
- Proporsi kolom dan lebar minimum tabel disesuaikan agar identitas tetap terbaca serta dapat digeser horizontal pada perangkat sempit.
- Mengikuti pola resmi `Flexible table width` DataTables, tabel desktop dibiarkan menghitung ulang lebar secara otomatis terhadap containernya tanpa `scrollX`; overflow horizontal hanya menjadi fallback pada perangkat sempit. Pilihan jumlah data dan kolom pencarian tetap seimbang di luar area data.
- Lebar kolom tidak lagi dipaksa dengan persentase: DataTables dan browser menyesuaikannya terhadap isi aktual. Lima tombol per baris diringkas menjadi satu dropdown `Aksi`, sementara konfirmasi reset password dan hapus tetap memakai alur SweetAlert2 yang sama.
- Struktur tabel diringkas menjadi lima kolom Bootstrap: No, Profil, Peran, Status, dan Aksi. NIK/ID PTK tampil berdampingan di Profil, jenis PTK/wali kelas berada di Peran, dan empat indikator status menggunakan badge soft-color dua kolom dengan seluruh sel disejajarkan vertikal di tengah.
- Select Aksi diganti menjadi Bootstrap dropdown button `btn-sm btn-outline-primary` dengan ikon per menu, pemisah tindakan sensitif, dan `dropdown-menu-right`. Tooltip Bootstrap diaktifkan kembali setiap DataTables selesai menggambar baris untuk tombol aksi dan indikator status.

File terkait:

- `app/Http/Controllers/Admin/GtkController.php`
- `resources/views/admin/gtk/index.blade.php`
- `public/css/custom-compact.css`
- `tests/Unit/GtkIndexUiArchitectureTest.php`

### Sinkronisasi identitas profesional GTK dari Manajemen PTK

- Tabel GTK ditambah kolom unik PEG ID, NRG, dan NPK serta kolom status inpassing dan sertifikasi; kolom NUPTK yang sudah ada tetap digunakan.
- Perintah `gtk:sync-ptk` membaca workbook Manajemen PTK, menjalankan dry-run secara default, menghasilkan laporan CSV, serta hanya memperbarui pasangan berkeyakinan tinggi ketika opsi `--apply` diberikan.
- Smart matching memprioritaskan NIK, NIP, dan NUPTK; sisa data dibandingkan memakai nama yang dinormalisasi tanpa gelar, dukungan singkatan token, tanggal lahir, skor minimum, dan margin terhadap kandidat kedua.
- Konflik antar-identitas, nama yang tidak mendukung identitas, pasangan ganda, dan skor rendah tidak pernah diperbarui otomatis.
- Sebelum transaksi apply, command menyimpan snapshot JSON lengkap untuk seluruh GTK yang akan diubah.
- Dry-run lokal dan produksi terhadap 158 baris workbook dan 122 GTK sama-sama menemukan 114 pasangan pasti, 7 ambigu, dan 37 tidak ditemukan.
- Transaksi produksi memperbarui 114 GTK: 114 PEG ID, 56 NRG, 79 NPK, 7 status inpassing, dan 114 status sertifikasi. Tidak ada duplikasi PEG ID/NRG/NPK; 7 kasus ambigu serta 37 data di luar GTK aktif tidak diubah.
- Snapshot sebelum transaksi tersimpan di `storage/app/ptk-sync/backup-before-20260802-180447.json`; laporan hasil dicatat di server dan disalin ke Downloads pengguna sebagai `rekap_manajemen_ptk_hasil_matching.csv`.
- Verifikasi manual menyelesaikan tiga kasus ambigu: SARIPIN dipasangkan ke `f1b1f3ef-cc29-4319-858f-81976a5482cd`, IWAN SAPUTRA ke `67176bef-cb9a-48ca-b184-8bceca419d16`, dan BADAR AZIZ ke `89a3dbe2-03c0-4383-bd27-5e07fd5d8854`; empat kasus ambigu dan 37 data yang belum tersedia tetap dilewati.
- NUPTK BADAR AZIZ yang sebelumnya tersimpan pada ISMI AZIZAH telah dipindahkan ke pemilik yang benar. Snapshot sebelum koreksi manual tersimpan di `storage/app/ptk-sync/backup-manual-match-20260802-183527.json`.

File terkait:

- `app/Console/Commands/SyncGtkPtkWorkbook.php`
- `app/Services/GtkPtkMatcher.php`
- `app/Models/Gtk.php`
- `database/migrations/2026_08_02_180000_add_professional_identifiers_to_gtks_table.php`
- `tests/Unit/GtkPtkMatcherTest.php`

### Penyempurnaan UI Analitik Kehadiran

- Hero Analitik Kehadiran diringkas mengikuti baseline Data Siswa, dengan informasi tahun pelajaran dan periode analisis yang seimbang di dalam satu card gradient.
- Filter disusun menjadi empat kelompok responsif: tahun pelajaran, tingkat, kelas, dan rentang tanggal, disertai aksi reset serta tautan Absensi Harian.
- Lima KPI dibuat lebih padat dengan ikon dan warna semantik yang lembut tanpa border warna-warni berlebihan.
- Distribusi status kini menampilkan jumlah dan persentase; Smart Suggestion serta tabel siswa memakai card outline AdminLTE dengan empty state yang proporsional.
- CSS lama yang saling menimpa dan memakai `!important` dihapus; seluruh style baru dibatasi oleh wrapper `attendance-analytics-page`.

File terkait:

- `resources/views/admin/absensi/analytics.blade.php`
- `tests/Unit/StudentAttendanceArchitectureTest.php`

### Pemisahan kehadiran siswa dan Presensi GTK

- Analitik Kehadiran dipindahkan ke menu Kelas Saya dan dibatasi ketat pada rombel aktif wali kelas; kelas yang diajar sebagai guru mapel tidak lagi ikut memperluas scope.
- Analitik kini menggabungkan sesi kehadiran final, presensi harian, indikator risiko, serta jumlah/riwayat catatan wali kelas per siswa.
- Absensi Siswa, Pemantauan Siswa, dan analitik siswa diturunkan dari kelompok Presensi. Route lama dipertahankan sementara untuk kompatibilitas, sedangkan alur wali diarahkan ke Absensi Harian dan Rekap Absensi di Kelas Saya.
- Dashboard, rekap, data kiosk, registrasi/verifikasi wajah, dan pengaturan pada modul Presensi kini khusus GTK. Akun GTK biasa hanya melihat data presensinya sendiri; pengelola berwenang dapat melihat seluruh GTK.
- Kiosk menolak tipe pengguna selain GTK, input manual memvalidasi target benar-benar GTK, dan editor menolak perubahan record siswa.
- Ekspor rekap bulanan yang sebelumnya placeholder kini menghasilkan CSV UTF-8 yang dapat dibuka di aplikasi spreadsheet.
- Dashboard, rekap, analitik, detail siswa, dan pengaturan telah diselaraskan dengan hero utuh, card netral, serta layout responsif.

File terkait:

- `app/Http/Controllers/Admin/AbsensiController.php`
- `app/Http/Controllers/Admin/AbsensiSettingController.php`
- `app/Http/Controllers/Admin/FaceRegistrationController.php`
- `app/Http/Controllers/Admin/StudentAttendanceAnalyticsController.php`
- `config/adminlte.php`
- `resources/views/admin/absensi/`
- `tests/Unit/StudentAttendanceArchitectureTest.php`

### Modal Catatan Siswa dan scope Statistik Siswa Wali Kelas

- Pemilihan siswa pada Catatan Siswa kini langsung membuka form tulis di modal responsif; composer panjang tidak lagi mengambil area bawah halaman.
- Setelah modal ditutup, area utama menampilkan riwayat siswa terpilih secara penuh, lengkap dengan foto, identitas, filter kategori, dan tombol Tulis Catatan.
- Statistik Siswa kini mendeteksi akun GTK yang menjadi wali kelas aktif, bukan hanya akun dengan nama role `Wali Kelas`.
- Daftar kelas, KPI, status login, kelengkapan data, NPSN, sekolah asal, domisili, grafik, peta, serta endpoint siswa belum EMIS dibatasi ke rombel aktif yang diampu.
- Manipulasi `kelas_id` atau NPSN sekolah di luar cakupan wali menghasilkan 404; aksi checker NPSN dan pelengkapan sekolah ditolak bagi akun GTK Wali Kelas.
- Mode wali kelas bersifat hanya-baca, memakai tautan detail/list portal wali, dan menu Statistik Siswa ditempatkan di kelompok Kelas Saya. Menu Statistik global hanya ditampilkan untuk akun pengelola yang berizin.
- Header Statistik Siswa diselaraskan dengan pola Data Siswa: judul/breadcrumb standar dan satu hero gradient utuh di dalam area konten.

File terkait:

- `app/Http/Controllers/Admin/SiswaStatisticsController.php`
- `app/Providers/AuthServiceProvider.php`
- `config/adminlte.php`
- `resources/views/admin/gtk/wali/catatan/index.blade.php`
- `resources/views/admin/siswa/statistics.blade.php`
- `tests/Unit/StudentStatisticsRoleScopeTest.php`
- `tests/Unit/WaliKelasPortalTest.php`

### Catatan siswa Wali Kelas berbasis profil siswa

- Halaman Catatan Siswa kini memakai alur berpusat pada siswa: wali kelas memilih kartu siswa berfoto terlebih dahulu, lalu menulis dan membaca riwayat catatan siswa tersebut.
- Galeri siswa menampilkan foto, nama, NISN, nomor absen, pencarian cepat, serta penanda visual siswa aktif; susunannya responsif dari desktop hingga ponsel.
- Hero utuh, statistik ringkas, composer, empty state, dan card riwayat diselaraskan dengan baseline UI Data Siswa admin.
- Editor catatan mendukung teks tebal/miring/garis bawah, daftar, undo/redo, emoji, simbol, serta awalan kalimat cepat. Isi HTML dibatasi ke format aman tanpa atribut, skrip, gambar, atau tautan.
- Riwayat menampilkan foto siswa, kategori, status penting, status dibaca BK, serta aksi edit/hapus dengan SweetAlert2.
- Pemilihan dan penyimpanan siswa divalidasi terhadap rombel aktif yang dipilih, sedangkan statistik dan riwayat tetap dibatasi pada wali kelas pembuat catatan.
- Catatan teks lama tetap aman, mempertahankan pergantian baris, dan dapat ditampilkan bersama format baru.

File terkait:

- `app/Http/Controllers/Admin/WaliKelas/CatatanController.php`
- `app/Models/CatatanWaliKelas.php`
- `resources/views/admin/gtk/wali/catatan/index.blade.php`
- `tests/Unit/CatatanWaliKelasTest.php`
- `tests/Unit/WaliKelasPortalTest.php`

### Scope Sekolah Asal berdasarkan jenis akun

- Modul Sekolah Asal kini membedakan akun pengelola dan akun GTK Wali Kelas pada query, statistik, menu, detail sekolah, serta daftar siswanya.
- Super Admin/Admin/Operator/Kepala Madrasah/WAKA tetap melihat data global sesuai permission, sedangkan GTK murni/Wali Kelas hanya melihat sekolah asal siswa pada rombel aktif yang diampunya.
- Statistik untuk wali kelas sekarang dihitung dari sekolah dalam rombelnya, bukan lagi dari seluruh referensi sekolah.
- Tombol Bulk Lengkapi, sinkronisasi per sekolah, dan aksi Lengkapi Data disembunyikan serta ditolak di controller untuk akun wali kelas.
- NPSN di luar lingkup rombel wali kelas menghasilkan 404, sehingga sekolah maupun siswa lain tidak dapat diakses dengan memanipulasi URL.
- Menu Sekolah Asal untuk wali kelas dipindahkan ke kelompok Kelas Saya; menu global Manajemen Data memakai gate khusus akun pengelola.

File terkait:

- `app/Http/Controllers/Admin/SekolahAsalController.php`
- `app/Providers/AuthServiceProvider.php`
- `config/adminlte.php`
- `resources/views/admin/sekolah-asal/index.blade.php`
- `resources/views/admin/sekolah-asal/show.blade.php`
- `tests/Unit/SchoolOriginRoleScopeTest.php`

### Penyelarasan Data Siswa Wali Kelas dengan Data Siswa admin

- Halaman Data Siswa Wali Kelas kini mengikuti struktur Data Siswa admin dengan hero operasional, empat card statistik, filter jenis kelamin/kelengkapan, tabel padat, serta status Verval, EMIS, keberadaan, dan tanggal masuk.
- Tabel tetap bersifat hanya-baca dan seluruh data tetap dibatasi pada rombel aktif yang diampu oleh wali kelas.
- Modal detail kini memakai enam tab responsif: Data Siswa, Data Diri, Orang Tua, Sekolah Asal, Dokumen, dan Catatan.
- Informasi identitas, alamat dan wilayah, akun login, status kelengkapan, kontak klik-telepon, data ayah/ibu, metadata dokumen, dan catatan pembinaan ditampilkan lebih lengkap.
- Password siswa sengaja tidak ditampilkan kepada wali kelas; akses dokumen tetap mengikuti izin `view-siswa` yang sudah berlaku.

File terkait:

- `app/Http/Controllers/Admin/WaliKelas/SiswaController.php`
- `resources/views/admin/gtk/wali/siswa/index.blade.php`
- `resources/views/admin/gtk/wali/siswa/partials/detail.blade.php`
- `tests/Unit/UiDesignConsistencyTest.php`

### Modal detail siswa, overlay kontak, dan SweetAlert2

- Klik nomor telepon (`tel:`) atau email (`mailto:`) kini menonaktifkan overlay navigasi global sehingga aplikasi telepon/kontak dapat terbuka tanpa meninggalkan lapisan pemuatan di halaman.
- Detail siswa pada daftar Wali Kelas kini dibuka sebagai modal responsif dan scrollable. Data tetap dibatasi oleh otorisasi rombel pada controller, sementara halaman detail langsung tetap tersedia sebagai fallback.
- Identitas siswa, data orang tua, nomor telepon yang dapat diketuk, dan catatan terakhir dimuat secara asinkron ke dalam modal.
- Notifikasi validasi/berhasil, kegagalan memuat data, serta konfirmasi finalisasi dan penghapusan pada alur GTK terkait telah dipindahkan dari alert browser/Bootstrap ke SweetAlert2.

File terkait:

- `resources/views/vendor/adminlte/master.blade.php`
- `app/Http/Controllers/Admin/WaliKelas/SiswaController.php`
- `resources/views/admin/gtk/wali/siswa/index.blade.php`
- `resources/views/admin/gtk/wali/siswa/partials/detail.blade.php`
- `resources/views/admin/gtk/wali/absensi/index.blade.php`
- `resources/views/admin/gtk/wali/catatan/index.blade.php`
- `resources/views/admin/gtk/profile/index.blade.php`
- `resources/views/admin/gtk/import.blade.php`
- `resources/views/admin/siswa/index.blade.php`
- `tests/Unit/UiDesignConsistencyTest.php`

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
 - Import jadwal Wakakur kini memblokir konfirmasi bila slot jam per hari belum mempunyai waktu mulai/selesai, agar jadwal tidak tersimpan tanpa waktu.
 - Generate Konfigurasi Jam kini memakai fallback form POST bila JavaScript tidak termuat dan sekaligus menyinkronkan slot Senin--Jumat ke jadwal_hari_jam untuk kedua semester.
 - Tahun Pelajaran kini memiliki pengaturan 5 atau 6 hari kerja yang menjadi sumber hari operasional untuk slot jadwal, impor Wakakur, dan format ekspor absensi kelas.
 - Konfirmasi penimpaan pada import Jadwal Wakakur kini memakai SweetAlert2 dengan ringkasan target, jumlah slot, dan jadwal yang akan ditimpa.
 - Ditambahkan Monitor Jadwal Belajar real-time: layar penuh, jam WIB, status sesi berlangsung/berikutnya, dan daftar kelas-mapel-guru aktif.
 - Tampilan Monitor Jadwal kini memakai kartu mapel beraksen warna konsisten, ikon kontekstual, hero lebih hidup, serta timeline sesi aktif yang kontras untuk layar besar.
 - Monitor Jadwal kini menampilkan slot istirahat dari konfigurasi jam, progres waktu jeda, dan informasi jam pelajaran berikutnya.
## Hotspot GTK: kompatibilitas password NIK (12 Agustus 2026)

- Login pada Log Autentikasi Hotspot berasal dari captive portal MikroTik yang diautentikasi FreeRADIUS, bukan login web SIMANSA.
- Password yang sama dengan username tetap ditolak untuk siswa, tetapi diterima khusus GTK bila username berupa NIK 16 digit.
- NIK menjadi fallback password hotspot untuk akun GTK lama yang belum memiliki `encrypted_password`, sehingga akun dapat disinkronkan kembali ke FreeRADIUS.
- Password percobaan tidak disimpan atau ditampilkan pada log autentikasi.
