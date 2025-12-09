# DEBUG: Update API Token EMIS - Tidak Bisa Simpan

## Perubahan Yang Dilakukan:

### 1. **Fix Database Insert Issue**
**File:** `app/Http/Controllers/Admin/ApiTokenController.php`

**Problem:** 
- Saat insert token baru, field `created_at` tidak di-set
- Menyebabkan NULL value atau error

**Solution:**
```php
DB::table('api_tokens')->updateOrInsert(
    ['name' => $tokenType],
    [
        'token' => $token,
        'description' => $tokenInfo['description'],
        'expires_at' => $expiresAt,
        'created_at' => DB::raw('COALESCE(created_at, NOW())'), // ✅ FIX: Set created_at
        'updated_at' => now()
    ]
);
```

### 2. **Tambahkan Logging untuk Debug**
**Logging di method `update()`:**
- Log request received
- Log unauthorized access attempt
- Log database operation result
- Log validation errors
- Log success/failure

**Cara cek log:**
```bash
# Realtime monitoring
php artisan tail

# Atau manual
Get-Content storage/logs/laravel.log -Tail 100 | Select-String "API Token"
```

### 3. **Better Error Handling**
**Changes:**
- Wrap validation dalam try-catch
- Return response JSON yang jelas untuk setiap error
- HTTP status code yang tepat (422 untuk validation, 403 untuk unauthorized, 500 untuk server error)

## Cara Testing:

### Test 1: Database Operation
```bash
php test_token_update.php
```
**Expected:** ✓ Semua test pass

### Test 2: Via Browser
1. Login sebagai Super Admin
2. Buka: http://127.0.0.1:8000/admin/pengaturan/update-api-token
3. Pilih tab "Token EMIS (NISN)" atau "Token Kemenag (NIP)"
4. Paste token baru (minimal 100 karakter)
5. Klik "Update Token"
6. **Buka Browser Console (F12)** → Tab Console dan Network

**Cek di Console:**
- Ada error JavaScript?
- Ada error AJAX?

**Cek di Network tab:**
- Cari request POST ke `/admin/pengaturan/update-api-token`
- Status code: 200 (success) atau berapa?
- Response body: apa isinya?

### Test 3: Cek Permission
```bash
php artisan tinker
```
```php
$user = \App\Models\User::find(1); // Ganti 1 dengan user ID Anda
echo $user->hasRole('Super Admin') ? 'YES' : 'NO';
echo $user->can('manage-settings') ? 'YES' : 'NO';
```

## Troubleshooting:

### Jika Masih Gagal, Periksa:

1. **CSRF Token**
```javascript
// Di browser console
console.log(document.querySelector('meta[name="csrf-token"]').content);
```

2. **Route Exists**
```bash
php artisan route:list --name=update-api-token
```

3. **JavaScript Error**
```
Buka Console (F12) → ada error merah?
```

4. **Network Request Details**
```
F12 → Network → POST request → Response tab
Lihat isi response JSON
```

5. **Session/Auth**
```bash
php artisan tinker
```
```php
echo \Auth::check() ? 'Logged in' : 'Not logged in';
```

## Kemungkinan Error & Solusi:

| Error | Penyebab | Solusi |
|-------|----------|---------|
| "Unauthorized access" | User bukan Super Admin | Login sebagai Super Admin |
| "Token minimal 100 karakter" | Token terlalu pendek | Copy token lengkap (JWT biasanya 200+ char) |
| "CSRF token mismatch" | Session expired | Refresh halaman, login ulang |
| "500 Internal Server Error" | Database/Server error | Cek `storage/logs/laravel.log` |
| Nothing happens (no response) | JavaScript error | Buka Console (F12), cek error merah |

## Log Monitoring:

Untuk melihat apa yang terjadi saat Anda klik "Update Token":

```bash
# Terminal 1: Monitor log
php artisan tail

# Terminal 2: Browser
# Buka halaman update token dan klik Update
```

**Yang harus muncul di log:**
```
[timestamp] local.INFO: API Token Update Request Received {"user_id":1,"request_data":{"_token":"...","token_type":"emis_api_token"}}
[timestamp] local.INFO: API Token Update - Database Operation {"affected":true,"token_type":"emis_api_token"}
[timestamp] local.INFO: API Token Updated {"token_type":"emis_api_token","user_id":1,...}
```

## Next Steps:

Silakan coba update token lagi dan:
1. **Buka Browser Console (F12)**
2. **Klik Update Token**
3. **Screenshot:**
   - Console tab (ada error?)
   - Network tab → POST request → Response
4. **Atau jalankan:** `Get-Content storage/logs/laravel.log -Tail 50`

Laporkan hasil yang Anda lihat!
