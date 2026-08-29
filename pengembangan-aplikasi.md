# Rencana Pengembangan Aplikasi MAN 1 Metro

Dokumen ini mencatat arah pengembangan lintas aplikasi, terutama LMS Ujian yang direncanakan. Baca bersama [MAN1METRO.md](MAN1METRO.md) sebelum membuat, memindahkan, atau mengubah aplikasi terkait.

Terakhir diperbarui: 28 Agustus 2026, zona waktu Asia/Jakarta.

## Aturan pencatatan LMS

- Setiap perubahan pada LMS—baik ketika masih dibuat di workspace baru maupun setelah repository/VM tersedia—wajib dicatat di dokumen ini pada bagian **Log Perubahan LMS**.
- Catat tanggal, workspace/repository, ringkasan perubahan, dampak integrasi SIMANSA/Moodle, dan status pengujian atau deployment.
- Kredensial, token API, private key, password database, serta konfigurasi rahasia tidak boleh dicatat di sini. Simpan hanya pada `.env`, credential manager, atau konfigurasi server yang berwenang.
- Bila perubahan juga menyentuh SIMANSA atau Moodle, catat perubahan operasionalnya pada `perubahan-terakhir.md` repository terkait.

## LMS Ujian — Rencana awal

### Tujuan

Membangun LMS Ujian mandiri untuk Try Out, TKA, UTBK, kedinasan, dan evaluasi sekolah. LMS harus tetap dapat berjalan tanpa Moodle, dengan pengalaman mengerjakan ujian modern, analitik, nilai, ranking, dan pengawasan ujian yang terukur.

### Arsitektur dan kepemilikan data

```text
SIMANSA ── siswa, GTK, kelas, rombel, mapel, tahun ajaran
   │
   └── LMS Ujian ── bank soal mandiri, ujian, jawaban, nilai,
                    ranking, analitik, pengawasan
                    │
                    └── Moodle (integrasi opsional dua arah)
```

| Sistem | Peran utama |
|---|---|
| SIMANSA | Sumber utama identitas siswa/GTK internal, kelas, rombel, mata pelajaran, tahun ajaran, dan status aktif. |
| LMS Ujian | Sumber utama bank soal baru, blueprint/paket ujian, jadwal, peserta, jawaban, nilai, ranking, analitik, dan data pengawasan. |
| Moodle | Sumber atau tujuan sinkronisasi opsional untuk bank soal/kategori dan materi ujian lama; bukan ketergantungan LMS. |

Siswa dan GTK internal disinkronkan dari SIMANSA memakai identitas stabil seperti NISN/NIP. LMS dapat memiliki data lokal hanya untuk peserta eksternal, alumni, proktor, atau pembuat soal yang tidak berada di SIMANSA.

### Bank soal mandiri

- Guru dapat membuat dan mengelola soal langsung di LMS.
- Metadata minimal: mapel, topik, kelas/tingkat, tag, indikator, tingkat kesulitan, tipe soal, bobot, pembahasan, status review, dan pemilik soal.
- Editor mendukung HTML aman, gambar, tabel, LaTeX/MathJax untuk matematika/fisika/kimia, serta teks Arab RTL.
- Jenis soal direncanakan mencakup pilihan ganda tunggal/multi-jawaban, benar-salah, menjodohkan, isian singkat, numerik, uraian, dan soal berbasis berkas/media bila diperlukan.
- Blueprint dapat menetapkan sebaran topik, jumlah soal, bobot, tingkat kesulitan, dan pengacakan paket.

### Integrasi Moodle

- Moodle saat ini berada pada VM `lms-server`, memakai Moodle `4.5.10+`, dan REST Web Service aktif.
- Tahap awal menggunakan impor/ekspor Moodle XML atau GIFT agar dapat diaudit dan dipratinjau.
- Tahap lanjutan memakai connector API khusus dengan token berhak minimum; jangan akses database Moodle secara langsung.
- Sinkronisasi dua arah menyimpan pemetaan ID Moodle–LMS, sumber data, versi/hash, waktu sinkron, status, dan konflik.
- Setiap kategori harus memiliki sumber utama yang jelas agar perubahan dari Moodle dan LMS tidak saling menimpa.

### Impor konten guru

- **Excel `.xlsx`**: jalur impor massal utama dengan template baku untuk pertanyaan, opsi, kunci, bobot, pembahasan, kategori, tag, serta media.
- **Word `.docx`**: menggunakan template/gaya atau tabel baku; hasil masuk sebagai draft dan wajib melalui pratinjau/validasi sebelum dipublikasikan.
- **Media**: Excel/Word dapat dibungkus dalam ZIP bersama gambar atau lampiran; sistem memetakan media dan melaporkan baris gagal secara jelas.

### Pengalaman ujian dan pengawasan

- Antarmuka ujian modern dengan navigasi nomor soal, status terjawab/ragu/belum dijawab, timer yang jelas, autosave, pemulihan koneksi, dan ringkasan sebelum kirim.
- Pengawasan mencatat kejadian seperti pindah tab, kehilangan fokus, koneksi terputus, atau aktivitas mencurigakan. Deteksi aplikasi lain pada ponsel tidak dapat dijamin oleh browser biasa; untuk mode ketat gunakan aplikasi kiosk/Safe Exam Browser yang dikelola perangkat.
- Nilai, ranking, analisis butir, distribusi nilai, capaian per topik, dan rekapan per kelas/tingkat menjadi tanggung jawab LMS.

### Tahap implementasi yang disarankan

1. Tetapkan repository, VM, domain, autentikasi, dan kontrak sinkronisasi SIMANSA.
2. Bangun identitas pengguna, sinkron siswa/GTK/kelas dari SIMANSA, serta bank soal mandiri.
3. Tambahkan editor soal, impor Excel/Word, media, validasi, review, dan blueprint.
4. Bangun mesin ujian, autosave, paket acak, nilai, ranking, serta analitik dasar.
5. Tambahkan impor/ekspor Moodle XML/GIFT.
6. Tambahkan connector REST Moodle satu arah, lalu dua arah setelah mekanisme konflik dan audit siap.
7. Tambahkan mode pengawasan/kiosk dan analitik lanjutan sesuai kebijakan perangkat sekolah.

## Log Perubahan LMS

| Tanggal | Workspace/repository | Perubahan | Integrasi/dampak | Verifikasi/deployment |
|---|---|---|---|---|
| 28 Agustus 2026 | Belum dibuat | Rencana awal LMS Ujian dicatat: bank soal mandiri, data internal dari SIMANSA, integrasi Moodle opsional, serta impor Excel/Word. | Moodle 4.5.10+ dan REST Web Service sudah diverifikasi tersedia pada VM `lms-server`. | Belum ada implementasi atau deployment. |
| 28 Agustus 2026 | Belum ada repository; Proxmox VM 107 `lms-ujian` | VM Ubuntu Server 24.04.4 LTS baru diprovisikan untuk LMS: 4 vCPU, RAM 8 GiB, disk 100 GiB, boot otomatis, cloud-init, dan QEMU guest agent. | Berada di VLAN 253 bersama Moodle, memakai IP internal statis `172.16.253.3`; belum ada perubahan pada SIMANSA atau Moodle. | VM berjalan; SSH key-only, jaringan, disk, dan guest agent telah diverifikasi. |
| 28 Agustus 2026 | Belum ada repository; Proxmox VM 107 `lms-ujian` | Akun administrasi VM diganti dari `lmsadmin` menjadi `candra`; home directory, SSH key, dan hak sudo tanpa password diselaraskan. | Tidak ada dampak pada SIMANSA atau Moodle. | Login SSH dan `sudo` dengan akun `candra` telah diverifikasi. |
| 28 Agustus 2026 | Belum ada repository; Proxmox VM 107 `lms-ujian` | Akses administrasi root jarak jauh diaktifkan sesuai kebijakan pengguna dan aaPanel dipasang sebagai panel operasional. Layanan systemd aaPanel disesuaikan agar tetap aktif setelah reboot pada Ubuntu 24.04. | Tidak ada dampak pada SIMANSA atau Moodle. | SSH root, listener HTTPS aaPanel, proses panel, firewall VM, dan layanan start-up telah diverifikasi. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; Proxmox VM 107 `lms-ujian` | Laravel 12 awal dipasang dengan ID UUID, login AJAX dua kolom, ilustrasi madrasah, pembatasan login pengguna aktif, serta test autentikasi. Skrip `/usr/local/bin/deploy-lms.sh` menarik `origin/main`, memasang dependensi, membangun aset, menjalankan migrasi/cache, dan memperbaiki izin runtime. | SIMANSA/Moodle belum dihubungkan; SQLite dipakai sementara hanya untuk bootstrap aplikasi hingga kontrak API sinkronisasi SIMANSA siap. | Commit produksi dideploy pada `172.16.253.3` menggunakan Nginx, PHP 8.3, Node.js 22; migrasi awal berhasil dan HTTP internal memberi respons 200. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Stack Ubuntu hasil `apt` dihentikan/dihapus lalu layanan dipindahkan ke aaPanel: Nginx 1.30, PHP 8.5 dengan ekstensi Laravel, MariaDB, PHP Project, dan database `lmsmansa`. Skrip deploy disesuaikan untuk runtime aaPanel. | Database aplikasi dipindahkan dari SQLite bootstrap ke MariaDB lokal khusus LMS; aplikasi belum terhubung ke API SIMANSA/Moodle. | Konfigurasi Nginx aaPanel lulus uji, migrasi MariaDB berhasil, commit LMS `232b9dc` terdeploy, dan HTTP internal maupun `https://lms.man1metro.sch.id` memberi respons 200. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Halaman login LMS diperbarui: hero ilustrasi madrasah responsif, identitas LMS MANSA, form lebih aksesibel, tombol lihat/sembunyikan sandi, serta status loading/error untuk login AJAX. | Tidak mengubah data SIMANSA atau Moodle. | Test Laravel lulus 4/4, aset Vite dibangun, commit LMS `f50b813` dipush/deploy, dan domain publik memberi respons HTTP 200. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | URL aplikasi produksi dipaksa ke HTTPS agar URL aset Vite, gambar hero, dan form login tetap benar saat LMS berada di belakang Nginx Proxy Manager. | Tidak mengubah data SIMANSA atau Moodle. | Commit LMS `e346d30` dipush/deploy; inspeksi HTML domain publik memastikan aset dan endpoint menggunakan `https://` tanpa mixed content. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Footer halaman login menampilkan identitas `IT MAN 1 Metro © 2025` serta waktu render respons dalam milidetik, dengan fallback yang aman untuk test. | Tidak mengubah data SIMANSA atau Moodle. | Test Laravel lulus 4/4; commit LMS `1c657e1` dipush/deploy dan domain publik menampilkan footer serta waktu render. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Fondasi UUID modul LMS ditambahkan: tingkat/rombel dan keanggotaan untuk sinkronisasi, bank soal, soal, ujian, percobaan/jawaban ujian, serta audit impersonasi admin. | Struktur siap menerima sinkronisasi API SIMANSA tanpa mengakses database SIMANSA langsung; belum ada data sumber yang disalin. | Test Laravel lulus 4/4; commit LMS `f76bc7e` dipush/deploy, migrasi MariaDB batch 2 berhasil, dan domain publik HTTP 200. |
| 28 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Dashboard peran dan modul activity log ditambahkan. Login berhasil/gagal serta logout mencatat pengguna, IP, perangkat, metadata, dan waktu; dashboard menampilkan aktivitas terakhir serta ringkasan awal guru/siswa. | Tidak mengubah data SIMANSA atau Moodle; log internal LMS siap dipakai oleh modul ujian dan impersonasi. | Test Laravel lulus 4/4; commit LMS `0004963` dipush/deploy, migrasi activity log batch 3 berhasil, domain publik HTTP 200. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Modul Bank Soal dibuat untuk GTK/guru dan administrator: daftar responsif, formulir pembuatan, status draf, UUID, pembatasan akses siswa, dan pencatatan `question_bank.created`. | Tidak mengubah data SIMANSA/Moodle; modul menjadi fondasi editor soal, review guru, dan penyusunan ujian berikutnya. | Test Laravel lulus 6/6 (19 assertion); commit dipush, dideploy melalui `/usr/local/bin/deploy-lms.sh`, lalu domain publik diverifikasi. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Editor butir soal pilihan ganda ditambahkan pada tiap Bank Soal: empat opsi, kunci jawaban, bobot, pembahasan, status draf, UUID, dan log `question.created`. | Guru hanya dapat mengelola bank miliknya; administrator dapat mereview seluruh bank. Siap menjadi sumber penyusun ujian. | Test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy melalui skrip VM. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Sidebar dashboard dan aksi cepat diselaraskan dengan modul aktif. Bank Soal kini membuka rute pengelolaan yang benar; modul lain diberi status segera hadir. | Navigasi responsif tetap mempertahankan menu aktif di desktop, tablet, dan ponsel. | Test Laravel lulus 7/7 (24 assertion), Blade cache berhasil; commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Design system LMS MANSA dibakukan dengan layout Blade bersama: sidebar, topbar, footer, token desain, komponen form/kartu/tombol, state menu aktif, dan responsivitas. Dashboard, Bank Soal, serta Editor Soal kini memakainya. | Modul internal berikutnya wajib memakai `layouts/app`; login tetap terpisah sebagai halaman publik. | Blade cache dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Komponen UI polish bersama menambahkan motion masuk halus, transisi interaktif, stagger grid, focus state aksesibel, dan footer sticky dengan garis highlight pada seluruh halaman internal. | Menghormati preferensi pengurangan gerak perangkat; seluruh modul yang memakai `layouts/app` mendapat perilaku visual sama. | Blade cache dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Workspace Bank Soal ditata ulang dengan grid 12 kolom: hero konteks, ringkasan, koleksi bank, panduan alur kerja, kartu/batas premium, serta footer `svh` yang menetap di dasar viewport. | Pola workspace ini menjadi acuan halaman daftar/modul LMS berikutnya agar ruang lebar tetap terasa terarah. | Blade cache dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Ritme visual Bank Soal dipadatkan: tinggi hero/kartu/empty state, gap grid, padding, tombol, dan footer diselaraskan untuk layar desktop. | Ruang kerja tetap premium dan terbaca, tetapi tidak lagi menyisakan area kosong berlebihan; pola compact berlaku pada komponen UI bersama. | Blade cache dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Tabler UI 1.4 dipasang lokal sebagai foundation UI area internal melalui entry Vite khusus, dengan token warna LMS MANSA di atasnya. | Login publik tetap terisolasi; shell/modul internal akan dikonversi bertahap ke komponen Tabler agar konsisten. | Vite build berhasil, Blade cache dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Bank Soal dikonversi ke komponen Tabler: grid Bootstrap, card, badge, tombol, alert, avatar, empty state, dan panel panduan. | Menjadi referensi visual/teknis untuk migrasi Dashboard dan editor soal tanpa mengubah backend atau login publik. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Dashboard dikonversi ke grid dan komponen Tabler: welcome card, statistik, action card, timeline aktivitas, dan state responsif. | Dashboard dan Bank Soal kini berbagi sistem komponen yang sama; editor soal menjadi tahap berikutnya. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Keputusan foundation UI diubah: Tabler dilepas dan AdminLTE 4.9.1 + Bootstrap dipasang lokal melalui Vite sebagai sistem internal tunggal. | Login publik tetap terisolasi; shell dan komponen internal dimigrasikan ke AdminLTE tanpa mengubah backend. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Form Buat Bank Soal dimigrasikan ke AdminLTE dengan card header/footer, grid Bootstrap, validasi, status draf, dan panel panduan. | Alur pembuatan bank soal kini konsisten dengan foundation AdminLTE serta siap menjadi pola form modul berikutnya. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Shell internal dan Dashboard dibangun ulang ke layout default AdminLTE; hero besar serta seluruh theme override custom dihapus. | CSS internal hanya memuat Bootstrap dan AdminLTE bawaan; semua modul berikutnya wajib mengikuti komponen standar ini. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Skin premium berbasis komponen AdminLTE diterapkan pada shell. Daftar dan form Tambah Soal dibangun ulang memakai grid, card, table, form, badge, serta action button seragam. | Dashboard, Bank Soal, dan modul soal memakai fondasi AdminLTE yang sama tanpa hero besar; login publik tetap terpisah. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Daftar Bank Soal dibangun ulang ke pola AdminLTE: tabel koleksi, ringkasan, panduan, badge status, aksi kelola, dan empty state. | Tidak ada hero atau komponen UI lama tersisa pada halaman daftar Bank Soal; konsisten dengan Dashboard dan modul Soal. | Vite build dan test Laravel lulus 7/7 (24 assertion); cache Blade lokal sempat terkunci Windows, sedangkan cache deploy Linux dijalankan normal. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Integrasi UI diverifikasi memakai distribusi npm resmi ColorlibHQ/AdminLTE v4.9.1, ditambah layer warna resmi dan theme primary AdminLTE. | Tidak ada penyalinan demo/source repository ke produksi; hanya aset build resmi dipakai melalui Vite. | Vite build dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Rebuild visual mengacu komposisi demo resmi AdminLTE: shell standar, Dashboard small-box/progress/card/table, dan form Buat Bank Soal card-primary. | Hero, skin, serta komponen visual lama dihapus; area internal mengikuti pola demo AdminLTE resmi. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Rebuild penuh UI internal memakai Bootstrap 5 dan Bootstrap Icons saja. Design system LMS baru diterapkan pada shell, Dashboard, daftar/form Bank Soal, serta daftar/form Soal. | AdminLTE dilepas untuk mencegah konflik; seluruh modul internal kini berbagi komponen, token, dan responsivitas Bootstrap LMS yang sama. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); commit dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Density workspace internal dipadatkan melalui stylesheet Vite khusus: ukuran sidebar, topbar, heading, statistik, kartu, tabel, form, tombol, grid, dan footer diperkecil seragam pada desktop. | Seluruh modul yang memakai `layouts/app` mendapat skala compact yang sama tanpa mengubah breakpoint mobile atau backend. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); siap dipush dan dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | UI aktif dibangun ulang dengan AdminLTE 4.9.1 resmi/Bootstrap 5.3: login, dashboard, daftar dan form Bank Soal, serta daftar dan form Soal. DataTables AJAX, Select2, SweetAlert2, Toastr, dan indikator loading live menjadi fondasi interaksi. | Tidak mengubah kontrak SIMANSA atau Moodle; endpoint tabel menjaga scope data guru/admin dan respons form AJAX tetap memakai validasi Laravel. | Vite build dan test Laravel lulus 7/7 (24 assertion); deployment produksi masih perlu dijalankan. |
| 29 Agustus 2026 | `chandra35/lmsmansa` | Palet LMS diselaraskan ke navy-teal dengan aksen hijau-bumi/amber, dan density desktop diperkecil lagi pada shell serta seluruh komponen standar. | Tidak ada dampak backend atau integrasi SIMANSA/Moodle. | Vite build dan test Laravel lulus 7/7 (24 assertion); deployment produksi memerlukan kredensial SSH yang tersedia. |
| 29 Agustus 2026 | `chandra35/lmsmansa` | Dashboard mengganti kartu statistik blok warna penuh dengan kartu metrik terang beraksen navy-teal/green/amber/slate, ikon panel lembut, dan density ringkas. | Tidak ada dampak backend atau integrasi SIMANSA/Moodle. | Vite build dan test Laravel lulus 7/7 (24 assertion); deploy produksi masih terblokir oleh autentikasi SSH. |
| 29 Agustus 2026 | `chandra35/lmsmansa` | Layout dan dashboard LMS dirombak menjadi EDUNOVA Fresh Academic memakai AdminLTE 4: navbar, sidebar, footer modular, kartu metrik, tabel registrasi, agenda, serta modal pratinjau registrasi. | Tidak mengubah kontrak SIMANSA/Moodle atau domain data LMS; komponen registrasi pada dashboard adalah pratinjau UI, bukan penyimpanan data peserta. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion); deploy tetap menunggu akses SSH VM. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Commit tema EDUNOVA `c1e81e6` dideploy via skrip produksi LMS. | Tidak ada dampak SIMANSA/Moodle. | Dependency, Vite build, migrasi, dan cache sukses; HTTP internal dan domain publik `/dashboard` memberi 200. |
| 29 Agustus 2026 | `chandra35/lmsmansa` | Konten dashboard diselaraskan ke data LMS nyata dan modul Ujian diaktifkan untuk guru/admin: draf, jadwal, durasi, pengacakan, audit log, dan scope otorisasi. Warna teks/icon sidebar putih dipertegas. | Tidak mengubah kontrak SIMANSA/Moodle; ujian belum memuat pemilihan soal atau attempt peserta. | Vite build, Blade cache, dan test Laravel lulus 9/9 (31 assertion); siap dideploy. |
| 29 Agustus 2026 | `chandra35/lmsmansa`; aaPanel VM 107 `lms-ujian` | Fondasi UI dibangun ulang memakai AdminLTE 4.9.1 resmi ColorlibHQ/Bootstrap 5.3: shell resmi, assets Vite lokal, SweetAlert2, Toastr, Select2, DataTables Bootstrap 5, dan progress loading global. Dashboard dimigrasikan ke widget AdminLTE. | Modul lama dipertahankan lewat alias komponen agar tetap satu sistem selama konversi daftar/form Bank Soal dan editor soal; rancangan engine ujian skala besar masih memerlukan implementasi domain serta uji beban terukur. | Vite build, Blade cache, dan test Laravel lulus 7/7 (24 assertion). |
| 29 Agustus 2026 | LMS MANSA; aaPanel VM 107 lms-ujian | Fondasi sinkronisasi direktori SIMANSA ditambahkan: client Bearer token, retry/timeout, pagination, command dry-run, dan staging siswa/GTK ber-UUID. | Memakai API SIMANSA v1 read-only; tidak membuat akun login atau password fiktif karena endpoint SSO, username, kelas, dan kredensial belum tersedia dalam kontrak v1. | Test client lulus 2/2 (4 assertion); commit 1c221cb dipush. Deploy menunggu akses SSH VM LMS dan pengisian token pada .env server. |
| 29 Agustus 2026 | LMS MANSA; aaPanel VM 107 lms-ujian | Modul Pengaturan Integrasi SIMANSA dibangun untuk Admin LMS: token disimpan terenkripsi, dapat diganti/dihapus, dan diuji dengan request kecil sebelum sinkronisasi. | Token tidak perlu disimpan pada source code atau ditampilkan kembali; akses dibatasi ke is_lms_admin. | Lint, test client, daftar rute, dan cache Blade lokal lulus; siap dipush/deploy. |
| 29 Agustus 2026 | LMS MANSA; aaPanel VM 107 lms-ujian | Modul pengaturan token, client sinkronisasi, dan staging directory dideploy ke produksi LMS. | Token dapat dikelola Admin LMS lewat UI terenkripsi; data API SIMANSA tetap read-only. | Commit 7e857f4, build Vite dan dua migrasi berhasil; halaman terlindungi mengarahkan pengguna tanpa sesi ke login. |
| 29 Agustus 2026 | LMS MANSA; aaPanel VM 107 lms-ujian | Hotfix sidebar LMS: directive Blade dipisahkan ke struktur multi-baris agar compiler tidak menghasilkan PHP dengan endif tidak valid. | Navigasi internal kembali dapat dirender tanpa error 500; tidak mengubah hak akses maupun integrasi SIMANSA. | Commit 230d2f0 dideploy, build Vite dan cache Blade produksi berhasil; dashboard dan pengaturan integrasi memberi redirect login normal saat tanpa sesi. |
