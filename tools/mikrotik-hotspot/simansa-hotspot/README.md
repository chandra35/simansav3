# SIMANSA MikroTik Hotspot Portal

Paket ini dibuat untuk profil Hotspot MikroTik yang memakai `login-by=cookie,http-chap` dan autentikasi RADIUS SIMANSA.

## Isi Paket

- `login.html` - halaman login profesional, responsif, dengan panduan akun siswa/GTK/tamu, toggle password, avatar, dan animasi ringan.
- `alogin.html` - notifikasi login berhasil dan pembuka halaman status pada tab baru.
- `status.html` - halaman status setelah login.
- `logout.html` - halaman logout.
- `error.html` - halaman error umum.
- `md5.js` - dukungan CHAP MikroTik.
- `assets/logo.png` - logo MAN 1 Metro dari SIMANSA.
- `assets/style.css` dan `assets/portal.js` - tampilan dan mapping pesan error.

## Cara Pasang di MikroTik

1. Backup folder hotspot lama dari menu **Files** MikroTik.
2. Upload seluruh isi folder ini ke direktori `hotspot` pada MikroTik.
3. Pastikan Hotspot Profile memakai:
   - `html-directory=hotspot`
   - `login-by=cookie,http-chap`
   - `use-radius=yes`
4. Coba dari perangkat baru atau mode incognito.

## Catatan Operasional

- Siswa login memakai NISN.
- Guru/GTK login memakai NIK, sedangkan tamu memakai akun yang dibuat admin di SIMANSA/RADIUS.
- Bantuan diarahkan ke `admin@man1metro.sch.id`.
- Jika Kampus 2 memakai router berbeda, salin paket yang sama ke router Kampus 2. Akun tetap sama karena RADIUS terpusat, tetapi sesi hotspot tetap dikelola per router.
- Untuk roaming tanpa login ulang lintas router, opsi paling stabil adalah Wi-Fi WPA2/WPA3 Enterprise berbasis RADIUS. Hotspot captive portal lebih cocok untuk akses tamu atau jaringan yang masih satu gateway/session domain.
