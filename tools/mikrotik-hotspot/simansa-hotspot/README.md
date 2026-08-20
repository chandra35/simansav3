# SIMANSA MikroTik Hotspot Portal

Paket ini dibuat untuk profil Hotspot MikroTik yang memakai `login-by=http-chap` dan autentikasi RADIUS SIMANSA. MAC-cookie dinonaktifkan agar tindakan putus sesi selalu meminta pengguna login ulang.

## Isi Paket

- `login.html` - kartu SIMANSA responsif dengan layout desktop dua kolom serta animasi DNA, molekul, dan matematika.
- `alogin.html` - notifikasi login berhasil dan pembuka halaman status pada tab baru.
- `status.html` - halaman status setelah login.
- `logout.html` - halaman logout.
- `error.html` - halaman error umum.
- `md5.js` - dukungan CHAP MikroTik.
- `assets/logo.png` - logo MAN 1 Metro dari SIMANSA.
- `assets/style.css` - tema halaman status, sukses, logout, dan error.
- `assets/login-v2.css` - tema editorial khusus halaman sukses dan status.
- `assets/portal.js` - interaksi form dan mapping pesan error.
- Nama lengkap pada halaman sukses/status berasal dari atribut RADIUS `Reply-Message` (`$(radius18)`) yang disinkronkan SIMANSA dalam format Base64 aman.
- Username dapat diingat secara lokal melalui pilihan pengguna. Password tetap diserahkan ke password manager browser dan tidak pernah disimpan mentah oleh portal.

## Cara Pasang di MikroTik

1. Backup folder hotspot lama dari menu **Files** MikroTik.
2. Upload seluruh isi folder ini ke direktori `hotspot` pada MikroTik.
3. Pastikan Hotspot Profile memakai:
   - `html-directory=hotspot`
   - `login-by=http-chap`
   - profile pengguna memakai `add-mac-cookie=no`
   - `use-radius=yes`
4. Coba dari perangkat baru atau mode incognito.

## Catatan Operasional

- Siswa login memakai NISN.
- Guru/GTK login memakai NIK, sedangkan tamu memakai akun yang dibuat admin di SIMANSA/RADIUS.
- Bantuan diarahkan ke `admin@man1metro.sch.id`.
- Jika Kampus 2 memakai router berbeda, salin paket yang sama ke router Kampus 2. Akun tetap sama karena RADIUS terpusat, tetapi sesi hotspot tetap dikelola per router.
- Mini-browser captive portal Android/iOS dapat bersifat sementara dan tidak selalu menyediakan autofill password. Untuk memakai password manager, buka `hotspot.man1metro.net` melalui browser utama.
- Untuk roaming tanpa login ulang lintas router, opsi paling stabil adalah Wi-Fi WPA2/WPA3 Enterprise berbasis RADIUS. Hotspot captive portal lebih cocok untuk akses tamu atau jaringan yang masih satu gateway/session domain.
