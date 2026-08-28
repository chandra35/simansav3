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
