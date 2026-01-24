# 🚀 Deployment Guide - Ubuntu Server

Panduan lengkap deploy aplikasi SIMANSA v3 ke Ubuntu Server dengan workflow GitHub.

---

## 📋 Prerequisites

### Server Requirements:
- Ubuntu 20.04 / 22.04 LTS
- RAM minimal 2GB
- Storage minimal 10GB
- Root/sudo access

### Software Stack:
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Nginx / Apache
- Composer
- Git

---

## 🔧 Part 1: Initial Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install PHP 8.2 & Extensions
```bash
# Add PHP repository
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 dengan extensions yang dibutuhkan
sudo apt install php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-imagick -y

# Verify installation
php -v
```

### 3. Install MySQL
```bash
sudo apt install mysql-server -y

# Secure MySQL installation
sudo mysql_secure_installation

# Login ke MySQL
sudo mysql -u root -p
```

**Create Database & User:**
```sql
CREATE DATABASE simansav3_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'simansav3_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON simansav3_db.* TO 'simansav3_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify
composer --version
```

### 5. Install Git
```bash
sudo apt install git -y
git --version
```

### 6. Install Nginx
```bash
sudo apt install nginx -y

# Start & enable
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx
```

---

## 📦 Part 2: Clone Project dari GitHub

### 1. Create Web Directory
```bash
sudo mkdir -p /var/www
cd /var/www
```

### 2. Clone Repository
```bash
# Clone dari GitHub (gunakan HTTPS atau SSH)
sudo git clone https://github.com/chandra35/simansav3.git

# Set ownership
sudo chown -R $USER:www-data simansav3
cd simansav3
```

### 3. Install Dependencies
```bash
# Install PHP packages
composer install --no-dev --optimize-autoloader

# Install Node packages (jika ada)
# sudo apt install nodejs npm -y
# npm install
# npm run build
```

---

## ⚙️ Part 3: Configure Application

### 1. Setup Environment File
```bash
# Copy .env.example
cp .env.example .env

# Edit .env
nano .env
```

**Configure .env:**
```env
APP_NAME=SIMANSA
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3_db
DB_USERNAME=simansav3_user
DB_PASSWORD=your_secure_password_here

# Storage Configuration
DOKUMEN_STORAGE_PATH=/var/www/simansav3/dokumen-siswa
DOKUMEN_AUTO_CREATE=true
DOKUMEN_CHECK_WRITABLE=true

# Image Compression (Auto-compress files >2MB)
DOKUMEN_COMPRESS_ENABLED=true
DOKUMEN_MAX_SIZE_MB=5
DOKUMEN_IMAGE_QUALITY=85
DOKUMEN_MAX_WIDTH=1920
DOKUMEN_MAX_HEIGHT=1920
DOKUMEN_CONVERT_PNG=true

# Session & Cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Save: `Ctrl+O`, Exit: `Ctrl+X`

### 2. Generate Application Key
```bash
php artisan key:generate
```

### 3. Set Permissions
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/simansav3

# Set directory permissions
sudo find /var/www/simansav3 -type d -exec chmod 755 {} \;
sudo find /var/www/simansav3 -type f -exec chmod 644 {} \;

# Set writable directories
sudo chmod -R 775 /var/www/simansav3/storage
sudo chmod -R 775 /var/www/simansav3/bootstrap/cache
sudo chmod -R 755 /var/www/simansav3/dokumen-siswa

# Ensure www-data can write
sudo chown -R www-data:www-data /var/www/simansav3/storage
sudo chown -R www-data:www-data /var/www/simansav3/bootstrap/cache
sudo chown -R www-data:www-data /var/www/simansav3/dokumen-siswa
```

### 4. Run Migrations
```bash
# Run migrations
php artisan migrate --force

# Seed database (jika ada seeders)
php artisan db:seed --force
```

### 5. Optimize for Production
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 🌐 Part 4: Configure Nginx

### 1. Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/simansav3
```

**Nginx Config:**
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/simansav3/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Max upload size (sesuaikan dengan DOKUMEN_MAX_SIZE_MB)
    client_max_body_size 5M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Enable Site
```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/simansav3 /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## 🔒 Part 5: Setup SSL (HTTPS)

### Using Let's Encrypt (Certbot)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Follow prompts:
# - Enter email
# - Agree to terms
# - Choose redirect HTTP to HTTPS (option 2)

# Test auto-renewal
sudo certbot renew --dry-run
```

Certificate auto-renews via cron. Nginx config akan otomatis update.

---

## 🔄 Part 6: Update Application (Git Workflow)

### Workflow: Development → GitHub → Production

#### **Di Development (Windows/Rumah):**
```powershell
# Edit code, test locally
php artisan serve

# Commit changes
git add .
git commit -m "Add new feature"
git push origin main
```

#### **Di Production (Ubuntu Server):**
```bash
# SSH ke server
ssh username@your-server-ip

# Navigate ke project
cd /var/www/simansav3

# Backup database (optional tapi recommended)
mysqldump -u simansav3_user -p simansav3_db > ~/backup_$(date +%Y%m%d_%H%M%S).sql

# Pull latest changes
git pull origin main

# Install/update dependencies (jika composer.json berubah)
composer install --no-dev --optimize-autoloader

# Run new migrations (jika ada)
php artisan migrate --force

# Clear all cache
php artisan optimize:clear

# Rebuild production cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx

# Check logs
tail -f storage/logs/laravel.log
```

### Quick Update Script
Create file: `update.sh`
```bash
#!/bin/bash

echo "🔄 Pulling latest changes..."
git pull origin main

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "🗄️  Running migrations..."
php artisan migrate --force

echo "🧹 Clearing cache..."
php artisan optimize:clear

echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔄 Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx

echo "✅ Update complete!"
```

Make executable:
```bash
chmod +x update.sh

# Usage
./update.sh
```

---

## 🛡️ Part 7: Security & Maintenance

### 1. Setup Firewall (UFW)
```bash
# Enable firewall
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check status
sudo ufw status
```

### 2. Setup Automatic Backups

Create backup script: `/home/username/backup_simansav3.sh`
```bash
#!/bin/bash

# Variables
BACKUP_DIR="/home/username/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="simansav3_db"
DB_USER="simansav3_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup dokumen-siswa
tar -czf $BACKUP_DIR/dokumen_$DATE.tar.gz /var/www/simansav3/dokumen-siswa

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -name "dokumen_*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable:
```bash
chmod +x ~/backup_simansav3.sh
```

**Setup Cron (Daily Backup at 2 AM):**
```bash
crontab -e

# Add line:
0 2 * * * /home/username/backup_simansav3.sh >> /home/username/backup.log 2>&1
```

### 3. Monitor Logs
```bash
# Laravel logs
tail -f /var/www/simansav3/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### 4. Disk Space Monitoring
```bash
# Check disk usage
df -h

# Check dokumen-siswa folder size
du -sh /var/www/simansav3/dokumen-siswa

# Check largest files
du -ah /var/www/simansav3/dokumen-siswa | sort -rh | head -20
```

---

## 🐛 Troubleshooting

### Issue 1: Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/simansav3
sudo chmod -R 775 /var/www/simansav3/storage
sudo chmod -R 775 /var/www/simansav3/bootstrap/cache
sudo chmod -R 755 /var/www/simansav3/dokumen-siswa
```

### Issue 2: 500 Internal Server Error
```bash
# Check error logs
tail -f /var/www/simansav3/storage/logs/laravel.log

# Clear cache
php artisan optimize:clear

# Check file permissions
ls -la /var/www/simansav3
```

### Issue 3: Upload File Failed
```bash
# Check storage writable
ls -la /var/www/simansav3/dokumen-siswa

# Check PHP upload settings
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Edit if needed
sudo nano /etc/php/8.2/fpm/php.ini
# Set:
# upload_max_filesize = 5M
# post_max_size = 5M

sudo systemctl restart php8.2-fpm
```

### Issue 4: Image Compression Not Working
```bash
# Check GD/Imagick installed
php -m | grep -i gd
php -m | grep -i imagick

# Install if missing
sudo apt install php8.2-gd php8.2-imagick -y
sudo systemctl restart php8.2-fpm

# Check config
php artisan tinker
>>> config('simansa.dokumen_compression.enabled')
```

### Issue 5: Database Connection Failed
```bash
# Test MySQL connection
mysql -u simansav3_user -p simansav3_db

# Check .env credentials
cat /var/www/simansav3/.env | grep DB_

# Check MySQL running
sudo systemctl status mysql
```

---

## 📊 Performance Optimization

### 1. Enable OPcache
```bash
sudo nano /etc/php/8.2/fpm/php.ini

# Add/uncomment:
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

sudo systemctl restart php8.2-fpm
```

### 2. Enable Nginx Gzip
```bash
sudo nano /etc/nginx/nginx.conf

# Add in http block:
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;

sudo systemctl reload nginx
```

### 3. Laravel Queue (Optional)
```bash
# Install Supervisor
sudo apt install supervisor -y

# Create config
sudo nano /etc/supervisor/conf.d/simansav3-worker.conf
```

**Supervisor Config:**
```ini
[program:simansav3-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/simansav3/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/simansav3/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start simansav3-worker:*
```

---

## 🎯 Deployment Checklist

### Initial Setup:
- [ ] Ubuntu server ready (RAM 2GB+, Storage 10GB+)
- [ ] PHP 8.2 installed with all extensions
- [ ] MySQL installed and configured
- [ ] Composer installed
- [ ] Nginx installed and configured
- [ ] Git installed
- [ ] Project cloned from GitHub
- [ ] Dependencies installed (`composer install`)
- [ ] `.env` configured (database, storage, etc)
- [ ] APP_KEY generated
- [ ] Permissions set correctly
- [ ] Migrations run
- [ ] Production cache built
- [ ] Nginx virtual host configured
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Backup script setup
- [ ] Tested upload dokumen (check compression works)

### After Each Update:
- [ ] Backup database (optional)
- [ ] `git pull origin main`
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan optimize:clear`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `sudo systemctl restart php8.2-fpm`
- [ ] `sudo systemctl reload nginx`
- [ ] Check logs for errors

---

## 📚 Additional Resources

### Useful Commands:
```bash
# Check running processes
ps aux | grep php
ps aux | grep nginx

# Check port usage
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# View real-time logs
tail -f /var/www/simansav3/storage/logs/laravel.log

# Clear storage cache
php artisan storage:link
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Check Laravel info
php artisan about
```

### Important Files Locations:
```
Application:        /var/www/simansav3
Nginx Config:       /etc/nginx/sites-available/simansav3
Nginx Logs:         /var/log/nginx/
PHP Config:         /etc/php/8.2/fpm/php.ini
PHP-FPM Logs:       /var/log/php8.2-fpm.log
Laravel Logs:       /var/www/simansav3/storage/logs/
Dokumen Storage:    /var/www/simansav3/dokumen-siswa
Backups:            /home/username/backups
```

---

## 🎓 Production Best Practices

1. **Always backup before update**
2. **Test in development first**
3. **Monitor logs after deployment**
4. **Keep system packages updated**: `sudo apt update && sudo apt upgrade`
5. **Regular database backups** (automated via cron)
6. **Monitor disk space** for `dokumen-siswa` folder
7. **Use environment variables** for sensitive data
8. **Never commit `.env` to git**
9. **Set `APP_DEBUG=false` in production**
10. **Use HTTPS** (SSL certificate)

---

## 🆘 Support

Jika ada masalah:
1. Check Laravel logs: `/var/www/simansav3/storage/logs/laravel.log`
2. Check Nginx logs: `/var/log/nginx/error.log`
3. Check PHP-FPM logs: `/var/log/php8.2-fpm.log`
4. Test configuration: `sudo nginx -t`
5. Rollback jika perlu: `git reset --hard HEAD~1`

---

**Happy Deploying! 🚀**

**Version:** 1.0  
**Last Updated:** 12 November 2025  
**Author:** GitHub Copilot
