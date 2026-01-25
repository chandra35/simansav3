# FEATURE: Force Password Change, SMTP Settings & Activity Logs

## Ringkasan

Dokumentasi fitur keamanan dan email yang telah diimplementasikan:

1. **Force Password Change** - Siswa wajib ganti password saat pertama login
2. **Encrypted Password** - Password dapat dilihat admin (encrypted di database)
3. **SMTP Settings** - Konfigurasi email dari database
4. **Forgot Password** - Reset password via email
5. **Loading Animation** - Lottie animation di dashboard siswa

---

## 1. Force Password Change

### Alur Kerja

1. Siswa baru dibuat dengan `is_first_login = true`
2. Saat login, middleware `ForcePasswordChange` mendeteksi status
3. Jika `is_first_login = true`, redirect ke `/siswa/force-setup`
4. Siswa wajib mengisi:
   - Password baru (min 8 karakter)
   - Email aktif
5. Setelah submit, `is_first_login = false`

### File Terkait

- `app/Http/Middleware/ForcePasswordChange.php`
- `resources/views/siswa/profile/force-setup.blade.php`
- `app/Http/Controllers/Siswa/ProfileController.php` (forceSetup, updateForceSetup)
- `bootstrap/app.php` (middleware registration)

### Middleware Registration

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
    ]);
    
    $middleware->web(append: [
        \App\Http\Middleware\ForcePasswordChange::class,
    ]);
})
```

---

## 2. Encrypted Password (Viewable by Admin)

### Konsep

Password disimpan dalam 2 kolom:
- `password` - Hash bcrypt (untuk authentication)
- `encrypted_password` - Enkripsi Laravel Crypt (untuk view admin)

### User Model

```php
// app/Models/User.php

protected $fillable = [
    // ...
    'encrypted_password',
];

protected $hidden = [
    'password',
    'remember_token',
    'encrypted_password',
];

// Getter - Decrypt password untuk view admin
public function getReadablePasswordAttribute(): ?string
{
    if (empty($this->encrypted_password)) {
        return null;
    }
    
    try {
        return Crypt::decryptString($this->encrypted_password);
    } catch (\Exception $e) {
        return null;
    }
}

// Setter - Encrypt password saat disimpan
public function setReadablePasswordAttribute($value): void
{
    $this->attributes['encrypted_password'] = Crypt::encryptString($value);
}
```

### Penggunaan di Controller

```php
// Saat membuat siswa baru
$user = User::create([...]);
$user->readable_password = $defaultPassword;
$user->save();

// Saat mengubah password
$user->readable_password = $request->password;
$user->update([
    'password' => Hash::make($request->password),
]);
```

### Tampilan di Admin

Di detail siswa (`resources/views/admin/siswa/index.blade.php`):
```javascript
<tr>
    <td>Password</td>
    <td>${siswa.user.readable_password ? 
        '<code class="text-danger">' + siswa.user.readable_password + '</code> <small class="text-muted">(encrypted)</small>' : 
        '<span class="text-muted">Tidak tersedia</span>'
    }</td>
</tr>
```

---

## 3. SMTP Settings

### Database Schema

```sql
-- Migration: add_smtp_settings_to_app_settings_table.php
ALTER TABLE app_settings ADD COLUMN smtp_host VARCHAR(255);
ALTER TABLE app_settings ADD COLUMN smtp_port INTEGER DEFAULT 587;
ALTER TABLE app_settings ADD COLUMN smtp_username VARCHAR(255);
ALTER TABLE app_settings ADD COLUMN smtp_password_encrypted TEXT;
ALTER TABLE app_settings ADD COLUMN smtp_encryption VARCHAR(10) DEFAULT 'tls';
ALTER TABLE app_settings ADD COLUMN smtp_from_address VARCHAR(255);
ALTER TABLE app_settings ADD COLUMN smtp_from_name VARCHAR(255);
ALTER TABLE app_settings ADD COLUMN smtp_enabled BOOLEAN DEFAULT FALSE;
```

### AppSetting Model

```php
// Encrypted password getter/setter
public function getSmtpPasswordAttribute(): ?string
{
    if (empty($this->smtp_password_encrypted)) {
        return null;
    }
    try {
        return Crypt::decryptString($this->smtp_password_encrypted);
    } catch (\Exception $e) {
        return null;
    }
}

public function setSmtpPasswordAttribute($value): void
{
    $this->attributes['smtp_password_encrypted'] = Crypt::encryptString($value);
}
```

### Konfigurasi Dinamis

```php
// AppSettingController.php
config([
    'mail.mailers.smtp.host' => $settings->smtp_host,
    'mail.mailers.smtp.port' => $settings->smtp_port,
    'mail.mailers.smtp.username' => $settings->smtp_username,
    'mail.mailers.smtp.password' => $settings->smtp_password,
    'mail.mailers.smtp.encryption' => $settings->smtp_encryption,
    'mail.from.address' => $settings->smtp_from_address,
    'mail.from.name' => $settings->smtp_from_name,
]);
```

### Routes

```php
Route::get('/settings/smtp', [AppSettingController::class, 'smtpSettings'])->name('admin.settings.smtp');
Route::put('/settings/smtp', [AppSettingController::class, 'updateSmtp'])->name('admin.settings.smtp.update');
Route::post('/settings/smtp/test', [AppSettingController::class, 'testSmtp'])->name('admin.settings.smtp.test');
```

---

## 4. Forgot Password

### Alur Kerja

1. User klik "Lupa Password?" di halaman login
2. Masukkan email terdaftar
3. System kirim email dengan link reset (60 menit expire)
4. User klik link, masukkan password baru
5. Password berhasil direset

### Files

- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/emails/reset-password.blade.php`

### Routes

```php
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
```

---

## 5. Loading Animation (Lottie)

### Dashboard Siswa

```html
<!-- Page Loading Overlay with Lottie Animation -->
<div class="page-loader" id="pageLoader">
    <div class="loader-content">
        <div class="lottie-container" id="lottieLoader"></div>
        <div class="loading-text">Memuat Dashboard...</div>
    </div>
</div>
```

```javascript
// Initialize Lottie
var loaderAnimation = lottie.loadAnimation({
    container: document.getElementById('lottieLoader'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'https://lottie.host/2fa0b14e-88bc-4f36-8e63-0b24b8d0c1d2/x6kISxXhXW.json'
});

// Hide loader after page load
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('pageLoader').classList.add('fade-out');
    }, 800);
});
```

---

## 6. Activity Logs

### Lokasi Menu

`config/adminlte.php` -> Sidebar -> Pengaturan -> Activity Logs

### Route

```
/admin/activity-logs
```

### Fitur

- View semua aktivitas user
- Filter berdasarkan tanggal, user, tipe aktivitas
- Detail aktivitas (old/new values)
- Hanya admin yang bisa akses

---

## Migration Commands

```bash
php artisan migrate
```

## Testing

1. **Force Password Change**
   - Login dengan siswa baru -> redirect ke force-setup
   - Isi password + email -> redirect ke dashboard

2. **Password View**
   - Admin buka detail siswa
   - Password terlihat dalam format `<code>`

3. **SMTP**
   - Admin Settings -> SMTP
   - Isi konfigurasi SMTP
   - Klik "Test Email"

4. **Forgot Password**
   - Halaman login -> "Lupa Password?"
   - Masukkan email -> cek inbox
   - Klik link -> reset password

---

## Keamanan

1. `encrypted_password` menggunakan Laravel Crypt (AES-256-CBC)
2. Password reset token expire dalam 60 menit
3. SMTP password juga encrypted di database
4. Activity logs mencatat semua perubahan sensitif
