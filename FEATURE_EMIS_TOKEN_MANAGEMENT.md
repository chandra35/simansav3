# FEATURE: EMIS Token Management

## Overview
Fitur untuk mengelola JWT Bearer Token yang digunakan untuk mengakses API EMIS Kemenag dalam fitur Cek NISN Siswa. Token EMIS bersifat JWT dengan masa berlaku terbatas (±4-5 jam), sehingga perlu diupdate secara berkala.

## Implementasi Details

### 1. Database Migration
**File:** `database/migrations/2025_11_12_170251_add_emis_token_to_app_settings.php`

Membuat table baru `api_tokens` untuk menyimpan API credentials:
- **id** (bigint, primary key)
- **name** (string, unique) - Identifier token (e.g., 'emis_api_token')
- **token** (text) - JWT token lengkap
- **description** (text nullable) - Keterangan fungsi token
- **expires_at** (timestamp nullable) - Waktu kadaluarsa (extracted from JWT)
- **created_at, updated_at** (timestamps)

Initial data:
```php
DB::table('api_tokens')->insert([
    'name' => 'emis_api_token',
    'token' => env('EMIS_BEARER_TOKEN'),
    'description' => 'Token Bearer untuk API EMIS Kemenag (Cek NISN)',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

**Alasan Struktur:**
- Awalnya mencoba menggunakan `app_settings` table, tapi table tersebut untuk pengaturan sekolah (nama_sekolah, npsn, logo, dll)
- Dibuat table baru `api_tokens` untuk fleksibilitas menyimpan berbagai API credentials di masa depan

### 2. Controller
**File:** `app/Http/Controllers/Admin/EmisTokenController.php`

**Methods:**
- `index()` - Show current token (masked) dan status expiry
- `update(Request $request)` - Validate dan save token baru

**Features:**
- ✅ Validasi JWT format (3 parts separated by dots)
- ✅ Decode JWT payload untuk extract expiry time
- ✅ Update token dengan `updateOrInsert()` (handle create/update)
- ✅ Logging activity untuk audit trail
- ✅ Permission check: Super Admin atau manage-settings

**Validation:**
```php
$request->validate([
    'token' => 'required|string|min:100'
]);
```

**JWT Validation:**
```php
private function validateJwtFormat($token)
{
    $parts = explode('.', $token);
    return count($parts) === 3;
}
```

**JWT Decode (Extract Expiry):**
```php
private function decodeJwt($token)
{
    $parts = explode('.', $token);
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
    return json_decode($payload, true);
}
```

### 3. View
**File:** `resources/views/admin/pengaturan/update-emis-token.blade.php`

**Sections:**
1. **Current Token Display**
   - Masked token (first 50 + last 20 chars)
   - Expiry status dengan badge warna (green = active, red = expired)

2. **Update Form**
   - Textarea untuk paste token baru
   - Real-time validation (JavaScript)
   - Auto-decode JWT untuk show expiry time
   - AJAX submit dengan SweetAlert2 feedback

3. **Info Card**
   - Cara mendapatkan token baru (step-by-step)
   - Link ke fitur Cek NISN untuk testing
   - Metadata (last update, token format, dll)

**JavaScript Features:**
- Real-time token format validation
- JWT decode untuk show expiry sebelum submit
- Badge indicators (Valid JWT, Expired, dll)
- AJAX form submission
- Auto-reload setelah success update

### 4. Service Update
**File:** `app/Services/EmisNisnService.php`

**Before:**
```php
$this->bearerToken = config('services.emis.bearer_token');
```

**After:**
```php
// Get token from database first, fallback to config
$tokenData = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
$this->bearerToken = $tokenData ? $tokenData->token : config('services.emis.bearer_token');
```

**Alasan Fallback:**
- Jika table kosong atau migration belum run, masih bisa pakai .env
- Smooth transition dari old system ke new system

### 5. Routes
**File:** `routes/web.php`

```php
// Pengaturan - Update EMIS Token (Super Admin Only)
Route::get('/pengaturan/update-emis-token', [App\Http\Controllers\Admin\EmisTokenController::class, 'index'])
    ->name('pengaturan.update-emis-token.index');
Route::post('/pengaturan/update-emis-token', [App\Http\Controllers\Admin\EmisTokenController::class, 'update'])
    ->name('pengaturan.update-emis-token.update');
```

### 6. Menu Configuration
**File:** `config/adminlte.php`

Added menu item:
```php
[
    'text' => 'Update Token EMIS',
    'route' => 'admin.pengaturan.update-emis-token.index',
    'icon' => 'fas fa-fw fa-key',
    'can' => 'manage-settings',
    'active' => ['admin/pengaturan/update-emis-token*'],
],
```

Position: Di submenu **Pengaturan**, setelah "Cek NISN Siswa"

## User Workflow

### Mendapatkan Token Baru dari EMIS
1. Login ke sistem EMIS Kemenag (https://api-emis.kemenag.go.id atau portal terkait)
2. Buka Developer Tools browser (F12)
3. Tab **Network** → Filter "Fetch/XHR"
4. Lakukan pencarian NISN atau akses API endpoint
5. Klik request yang muncul → Tab **Headers**
6. Cari **Authorization: Bearer eyJ0eXAi...**
7. Copy token (tanpa kata "Bearer")

### Update Token via UI
1. Login sebagai Super Admin
2. Menu **Pengaturan** → **Update Token EMIS**
3. Lihat status token saat ini (aktif/kadaluarsa)
4. Paste token baru di textarea
5. System auto-validate format JWT
6. Jika valid, muncul info expiry time
7. Klik **Update Token**
8. Success message → Token tersimpan
9. Test dengan **Cek NISN** feature

## Technical Notes

### JWT Token Structure
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9  ← Header (base64)
.
eyJpc3Mi...  ← Payload (base64, contains iat, exp, etc)
.
SflKxwRJ...  ← Signature
```

**Payload Example:**
```json
{
  "iss": "api-emis.kemenag.go.id",
  "iat": 1699800655,      // Issued at: 2024-11-12 20:50:55
  "exp": 1699816855,      // Expires at: 2024-11-13 01:20:55 (4.5 hours)
  "nbf": 1699800655,      // Not before
  "jti": "...",
  "sub": "...",
  "prv": "..."
}
```

**Token Lifetime:** ±16,200 seconds (4.5 hours)

### Security Considerations
1. **Permission Control:** Hanya Super Admin yang bisa update token
2. **Activity Logging:** Setiap update token dicatat di log dengan user info
3. **Masked Display:** Token ditampilkan sebagian untuk keamanan
4. **Validation:** Format JWT divalidasi sebelum simpan
5. **Database Storage:** Token disimpan encrypted jika Laravel encryption enabled

### Error Handling
- Invalid JWT format → Rejected dengan error message
- Missing token → Validation error
- DB error → Caught dan logged
- API request error → Fallback ke .env token

## Testing

### Test Update Token
```bash
# 1. Access via browser
http://localhost/admin/pengaturan/update-emis-token

# 2. Paste valid JWT token

# 3. Check database
SELECT * FROM api_tokens WHERE name = 'emis_api_token';

# 4. Test Cek NISN
http://localhost/admin/pengaturan/cek-nisn?nisn=0123456789
```

### Test Token Expiry Detection
```javascript
// In browser console
const token = 'eyJ0eXAi...'; // Your JWT
const parts = token.split('.');
const payload = JSON.parse(atob(parts[1]));
console.log('Expires:', new Date(payload.exp * 1000));
console.log('Is Expired:', payload.exp * 1000 < Date.now());
```

## Integration dengan Fitur Cek NISN

**File:** `app/Services/EmisNisnService.php`

Flow:
1. User akses Cek NISN
2. `EmisNisnService` instantiated
3. Constructor read token dari `api_tokens` table
4. If not found, fallback ke `config('services.emis.bearer_token')` (dari .env)
5. Token digunakan untuk API call ke EMIS
6. If expired → Error 401 Unauthorized
7. User update token via UI
8. Next Cek NISN request → Use new token

## Maintenance

### Token Refresh Schedule
- Monitor token expiry status via UI dashboard
- Idealnya refresh token sebelum kadaluarsa (e.g., setiap 4 jam)
- Setup reminder/notification untuk admin (future enhancement)

### Database Cleanup
```sql
-- Check old tokens
SELECT name, expires_at, updated_at 
FROM api_tokens 
WHERE expires_at < NOW();

-- No need to delete, updateOrInsert() handles it
```

### Logs Location
```bash
# Activity logs
storage/logs/laravel.log

# Search for EMIS Token updates
grep "EMIS Token Updated" storage/logs/laravel-*.log
```

## Future Enhancements

1. **Auto-Refresh Token:**
   - Cron job untuk auto-detect token expiry
   - Auto-request new token via API (jika endpoint tersedia)

2. **Token History:**
   - Track token changes dengan audit table
   - Show last 10 token updates

3. **Multiple API Tokens:**
   - Support multiple API credentials
   - Kemdikbud, Kemenag, BPS, dll

4. **Notification:**
   - Email/SMS notification saat token akan expire
   - Dashboard warning jika token < 1 hour before expiry

5. **Token Health Check:**
   - Endpoint untuk test token validity
   - Auto-check saat page load

## Troubleshooting

### Issue: "Token tidak valid"
**Cause:** Format JWT tidak sesuai  
**Solution:** Pastikan copy full token tanpa spasi/newline

### Issue: "Token sudah kadaluarsa"
**Cause:** Token JWT expired (>4.5 jam)  
**Solution:** Dapatkan token baru dari EMIS

### Issue: "Cek NISN masih error setelah update"
**Cause:** Cache issue atau wrong token  
**Solution:** 
```bash
php artisan cache:clear
php artisan config:clear
```

### Issue: "Permission denied"
**Cause:** User bukan Super Admin  
**Solution:** Login sebagai Super Admin atau user dengan permission `manage-settings`

## Related Files
- `app/Http/Controllers/Admin/EmisTokenController.php`
- `app/Services/EmisNisnService.php`
- `resources/views/admin/pengaturan/update-emis-token.blade.php`
- `database/migrations/2025_11_12_170251_add_emis_token_to_app_settings.php`
- `routes/web.php`
- `config/adminlte.php`

## Changelog
- **2024-11-12:** Initial implementation - Token management UI
- **2024-11-12:** Migration created - api_tokens table
- **2024-11-12:** EmisNisnService updated - Read from database
