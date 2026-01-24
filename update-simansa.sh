#!/bin/bash

# ==============================================
# SIMANSA V3 UPDATE SCRIPT
# ==============================================
# Jalankan script ini dengan: ./update-simansa.sh
# Pastikan sudah chmod +x update-simansa.sh
# ==============================================

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Direktori aplikasi
APP_DIR="/home/manmetr1/simansa.man1metro.sch.id"

# Function untuk print dengan warna
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Header
echo ""
echo -e "${GREEN}=============================================="
echo "   SIMANSA V3 - UPDATE SCRIPT"
echo "=============================================="
echo -e "${NC}"

# Pindah ke direktori aplikasi
print_status "Pindah ke direktori aplikasi..."
cd $APP_DIR || { print_error "Gagal masuk ke direktori $APP_DIR"; exit 1; }
print_success "Direktori: $(pwd)"

# Cek apakah ada perubahan lokal yang belum di-commit
print_status "Mengecek status git..."
if [[ -n $(git status --porcelain) ]]; then
    print_warning "Ada perubahan lokal yang belum di-commit!"
    echo "Pilihan:"
    echo "  1) Simpan perubahan lokal (stash) dan lanjutkan update"
    echo "  2) Batalkan update"
    read -p "Pilih (1/2): " choice
    
    if [[ $choice == "1" ]]; then
        print_status "Menyimpan perubahan lokal..."
        git stash
        print_success "Perubahan lokal disimpan ke stash"
    else
        print_warning "Update dibatalkan"
        exit 0
    fi
fi

# Enable maintenance mode
print_status "Mengaktifkan maintenance mode..."
php artisan down --message="Sedang dalam proses update, silakan tunggu beberapa saat..." --retry=60 2>/dev/null || true

# Pull dari GitHub
print_status "Mengambil update dari GitHub..."
git fetch origin
git pull origin master
if [ $? -eq 0 ]; then
    print_success "Pull dari GitHub berhasil"
else
    print_error "Gagal pull dari GitHub"
    php artisan up 2>/dev/null || true
    exit 1
fi

# Update Composer dependencies (jika ada perubahan composer.json)
print_status "Mengecek dan update dependencies Composer..."
if [[ -f "composer.lock" ]]; then
    composer install --no-dev --optimize-autoloader --no-interaction
    if [ $? -eq 0 ]; then
        print_success "Composer dependencies updated"
    else
        print_warning "Composer install gagal, melanjutkan..."
    fi
fi

# Jalankan migrasi database
print_status "Menjalankan migrasi database..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Migrasi database berhasil"
else
    print_warning "Migrasi database mungkin tidak diperlukan atau ada error"
fi

# Clear semua cache
print_status "Membersihkan cache..."

echo "  - Clearing application cache..."
php artisan cache:clear

echo "  - Clearing config cache..."
php artisan config:clear

echo "  - Clearing route cache..."
php artisan route:clear

echo "  - Clearing view cache..."
php artisan view:clear

echo "  - Clearing compiled classes..."
php artisan clear-compiled 2>/dev/null || true

echo "  - Clearing event cache..."
php artisan event:clear 2>/dev/null || true

print_success "Semua cache telah dibersihkan"

# Rebuild cache untuk production
print_status "Membangun ulang cache untuk production..."

echo "  - Caching config..."
php artisan config:cache

echo "  - Caching routes..."
php artisan route:cache

echo "  - Caching views..."
php artisan view:cache

print_success "Cache production telah dibangun"

# Optimize
print_status "Mengoptimasi aplikasi..."
php artisan optimize 2>/dev/null || true

# Storage link (jika belum ada)
print_status "Mengecek storage link..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    print_success "Storage link dibuat"
else
    print_success "Storage link sudah ada"
fi

# Set permission (opsional, sesuaikan dengan kebutuhan)
print_status "Mengatur permission..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
print_success "Permission diatur"

# Disable maintenance mode
print_status "Menonaktifkan maintenance mode..."
php artisan up

# Tampilkan versi dan info
echo ""
echo -e "${GREEN}=============================================="
echo "   UPDATE SELESAI!"
echo "=============================================="
echo -e "${NC}"
print_status "Versi Laravel: $(php artisan --version)"
print_status "Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Tampilkan commit terakhir
print_status "Commit terakhir:"
git log -1 --pretty=format:"  %h - %s (%cr)" --abbrev-commit
echo ""
echo ""

print_success "Aplikasi SIMANSA V3 telah berhasil diupdate!"
echo ""
