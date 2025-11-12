# 📁 Dokumen Siswa Storage Configuration

## Overview

Sistem penyimpanan dokumen siswa yang flexible dengan support untuk:
- **Custom storage path** via environment variable
- **Automatic fallback** jika primary storage tidak writable
- **Git-ignored** folder untuk file upload
- **Cross-platform** compatible (Windows & Linux)

---

## 📍 Default Storage Location

```
simansav3/
└── dokumen-siswa/          # Git-ignored folder
    ├── .gitkeep           # Tracked (agar folder ada di repo)
    ├── 0123456789/        # Folder per NISN siswa
    │   ├── uuid1.pdf
    │   ├── uuid2.jpg
    │   └── ...
    └── ...
```

---

## ⚙️ Configuration

### 1. Environment Variables (.env)

```env
# Custom storage path (optional)
DOKUMEN_STORAGE_PATH=/path/custom/dokumen-siswa

# Auto create folder if not exists (default: true)
DOKUMEN_AUTO_CREATE=true

# Check writable before use (default: true)
DOKUMEN_CHECK_WRITABLE=true

# Log when using fallback storage (default: true)
DOKUMEN_LOG_FALLBACK=true
```

### 2. Config File (config/simansa.php)

```php
'dokumen_storage' => [
    'primary_path' => env('DOKUMEN_STORAGE_PATH', base_path('dokumen-siswa')),
    'fallback_path' => storage_path('app/private/dokumen-siswa'),
    'auto_create' => env('DOKUMEN_AUTO_CREATE', true),
    'check_writable' => env('DOKUMEN_CHECK_WRITABLE', true),
    'log_fallback' => env('DOKUMEN_LOG_FALLBACK', true),
],
```

---

## 🚀 Deployment

### Development (Windows)

```powershell
# Clone repo
git clone https://github.com/chandra35/simansav3.git
cd simansav3

# Install dependencies
composer install

# Setup .env (optional custom path)
# DOKUMEN_STORAGE_PATH=D:\Backup-Dokumen-Siswa

# Run migration
php artisan migrate

# Upload akan masuk ke: simansav3\dokumen-siswa\{NISN}\{UUID}.ext
# Folder tidak akan ke-push ke git (di-ignore)
```

### Production (Ubuntu)

```bash
# Pull latest code
cd /var/www/simansav3
git pull origin main

# Create dokumen folder (if not exists)
mkdir -p dokumen-siswa
chown -R www-data:www-data dokumen-siswa
chmod -R 755 dokumen-siswa

# Optional: Custom path
# nano .env
# DOKUMEN_STORAGE_PATH=/mnt/dokumen-siswa

# Run migration
php artisan migrate

# Upload akan masuk ke: /var/www/simansav3/dokumen-siswa/{NISN}/{UUID}.ext
```

---

## 🔄 Migrate Existing Files

Jika ada file lama di `storage/app/private/dokumen-siswa/`, gunakan script migrate:

```bash
# Run migration script
php migrate_dokumen_storage.php

# Output:
# Found X documents to migrate
# Success: X
# Skipped: X
# Failed: X
```

**Note:** Script TIDAK akan delete file lama (untuk safety). Hapus manual setelah verify.

---

## 🛠️ Usage

### Upload File

```php
use App\Helpers\StorageHelper;

// Get writable disk (auto-detect)
$disk = StorageHelper::getDokumenDisk();

// Upload file
$path = "{$nisn}/{$fileName}";
Storage::disk($disk)->put($path, $fileContent);

// Save to database
DokumenSiswa::create([
    'file_path' => $path,
    'storage_disk' => $disk,  // Track which disk
    // ...
]);
```

### Read File

```php
// Get disk from database
$disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);

// Check exists
if (Storage::disk($disk)->exists($dokumen->file_path)) {
    $content = Storage::disk($disk)->get($dokumen->file_path);
}
```

### Delete File

```php
$disk = $dokumen->storage_disk ?? StorageHelper::getDiskFromPath($dokumen->file_path);
Storage::disk($disk)->delete($dokumen->file_path);
```

---

## 📊 Storage Info

```php
use App\Helpers\StorageHelper;

// Get storage status
$info = StorageHelper::getStorageInfo();

/*
Array (
    [active_disk] => dokumen
    [primary] => Array (
        [disk] => dokumen
        [path] => /var/www/simansav3/dokumen-siswa
        [writable] => true
        [exists] => true
    )
    [fallback] => Array (
        [disk] => dokumen_fallback
        [path] => /var/www/simansav3/storage/app/private/dokumen-siswa
        [writable] => true
        [exists] => true
    )
)
*/
```

---

## 🔐 Security

1. **Private Storage**: File tidak bisa diakses langsung via URL
2. **UUID Filename**: Filename pakai UUID, tidak bisa diprediksi
3. **Authentication**: Preview/download harus login dan punya akses
4. **Audit Trail**: Track access count dan last accessed time

---

## 🐧 Ubuntu Production Setup

### Option A: Default (di dalam project)

```bash
# Folder di dalam project
/var/www/simansav3/dokumen-siswa/

# Permission
sudo chown -R www-data:www-data /var/www/simansav3/dokumen-siswa
sudo chmod -R 755 /var/www/simansav3/dokumen-siswa
```

### Option B: External Drive

```bash
# Mount external drive
sudo mkdir -p /mnt/dokumen-siswa
sudo mount /dev/sdb1 /mnt/dokumen-siswa

# Auto-mount on boot
echo "/dev/sdb1 /mnt/dokumen-siswa ext4 defaults 0 2" | sudo tee -a /etc/fstab

# Permission
sudo chown -R www-data:www-data /mnt/dokumen-siswa
sudo chmod -R 755 /mnt/dokumen-siswa

# .env
DOKUMEN_STORAGE_PATH=/mnt/dokumen-siswa
```

### Option C: NAS/Network Storage

```bash
# Mount NFS
sudo mkdir -p /mnt/dokumen-siswa
echo "192.168.1.100:/share/dokumen /mnt/dokumen-siswa nfs defaults 0 0" | sudo tee -a /etc/fstab
sudo mount -a

# Permission
sudo chown -R www-data:www-data /mnt/dokumen-siswa

# .env
DOKUMEN_STORAGE_PATH=/mnt/dokumen-siswa
```

---

## 💾 Backup Strategy

### Daily Backup (Cron)

```bash
# Create backup script
sudo nano /usr/local/bin/backup-dokumen.sh
```

```bash
#!/bin/bash
# Backup dokumen siswa

SOURCE="/var/www/simansav3/dokumen-siswa"
BACKUP="/backup/dokumen-siswa"
DATE=$(date +%Y%m%d)

# Rsync to local backup
rsync -avz --delete $SOURCE/ $BACKUP/ >> /var/log/dokumen-backup.log 2>&1

# Optional: Compress weekly backup
if [ $(date +%u) -eq 7 ]; then
    tar -czf $BACKUP-$DATE.tar.gz $BACKUP/
    find /backup -name "dokumen-siswa-*.tar.gz" -mtime +30 -delete
fi
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-dokumen.sh

# Add to crontab
sudo crontab -e
# Add line:
0 2 * * * /usr/local/bin/backup-dokumen.sh
```

---

## 🔍 Monitoring

### Check Disk Space

```bash
# Check storage usage
df -h /var/www/simansav3/dokumen-siswa

# Check file count
find /var/www/simansav3/dokumen-siswa -type f | wc -l

# Check largest files
du -h /var/www/simansav3/dokumen-siswa | sort -rh | head -10
```

### Check Permissions

```bash
# Check owner
ls -la /var/www/simansav3/dokumen-siswa

# Check writable
sudo -u www-data test -w /var/www/simansav3/dokumen-siswa && echo "OK" || echo "NOT WRITABLE"
```

---

## 📝 Git Workflow

```bash
# File upload tidak kepush (di-ignore)
git add .
git commit -m "Update feature"
git push origin main
# ✅ Folder dokumen-siswa tidak kepush

# Di server
git pull origin main
# ✅ Folder dokumen-siswa tetap ada (tidak ke-overwrite)
# ✅ File upload tetap aman
```

---

## ❓ Troubleshooting

### Permission Denied

```bash
# Fix permission
sudo chown -R www-data:www-data /var/www/simansav3/dokumen-siswa
sudo chmod -R 755 /var/www/simansav3/dokumen-siswa

# Check SELinux (jika aktif)
sudo setsebool -P httpd_unified 1
sudo chcon -R -t httpd_sys_rw_content_t /var/www/simansav3/dokumen-siswa
```

### Fallback Storage Triggered

```bash
# Check log
tail -f storage/logs/laravel.log | grep "fallback"

# Verify primary path writable
php artisan tinker
>>> use App\Helpers\StorageHelper;
>>> StorageHelper::getStorageInfo();
```

### Migration Failed

```bash
# Re-run migration script
php migrate_dokumen_storage.php

# Check specific file
php artisan tinker
>>> $doc = App\Models\DokumenSiswa::find('uuid');
>>> Storage::disk($doc->storage_disk)->exists($doc->file_path);
```

---

## 📚 Related Files

- `config/simansa.php` - Storage configuration
- `config/filesystems.php` - Disk definitions
- `app/Helpers/StorageHelper.php` - Storage helper functions
- `app/Http/Controllers/Siswa/DokumenController.php` - Upload/download logic
- `database/migrations/2025_11_12_024628_add_storage_disk_to_dokumen_siswa_table.php` - Add storage_disk column
- `migrate_dokumen_storage.php` - Migration script
- `.gitignore` - Ignore dokumen-siswa folder

---

## 🎯 Summary

✅ Folder di dalam project (tidak perlu setup eksternal)  
✅ Git-ignored (tidak kepush, repository tetap kecil)  
✅ Cross-platform (Windows & Linux)  
✅ Flexible path via .env  
✅ Auto-fallback jika primary error  
✅ Easy backup (rsync/tar)  
✅ Production-ready dengan permission management  

---

**Author:** GitHub Copilot  
**Date:** 12 November 2025  
**Version:** 1.0
