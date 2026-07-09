#!/bin/bash

# ==============================================

set -o pipefail
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
detect_app_dir() {
    local candidates=(
        "${APP_DIR}"
        "/www/wwwroot/simansa.man1metro.sch.id"
        "/home/simansa/htdocs/simansa.man1metro.sch.id"
        "/home/manmetr1/simansa.man1metro.sch.id"
    )

    for candidate in "${candidates[@]}"; do
        if [[ -n "$candidate" && -d "$candidate" ]]; then
            echo "$candidate"
            return 0
        fi
    done

    return 1
}

APP_DIR="$(detect_app_dir)" || {
    echo -e "${RED}[ERROR]${NC} Direktori aplikasi tidak ditemukan. Set APP_DIR terlebih dahulu."
    exit 1
}

AUTO_STASH="${AUTO_STASH:-1}"
GIT_REMOTE="${GIT_REMOTE:-origin}"
GIT_BRANCH="${GIT_BRANCH:-master}"
APP_USER="${APP_USER:-www}"
APP_GROUP="${APP_GROUP:-www}"

MAINTENANCE_ON=0

cleanup_on_exit() {
    if [[ $MAINTENANCE_ON -eq 1 ]]; then
        run_artisan up 2>/dev/null || true
    fi
}

trap cleanup_on_exit EXIT

PHP_BIN="${PHP_BIN:-$(command -v /www/server/php/83/bin/php || command -v php8.3 || command -v php || true)}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"

if [[ -z "$PHP_BIN" ]]; then
    echo -e "${RED}[ERROR]${NC} Binary PHP tidak ditemukan."
    exit 1
fi

run_as_app_user() {
    if [[ "$(id -u)" -eq 0 ]]; then
        sudo -u "$APP_USER" "$@"
    else
        "$@"
    fi
}

run_artisan() {
    run_as_app_user "$PHP_BIN" artisan "$@"
}

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
cd "$APP_DIR" || { print_error "Gagal masuk ke direktori $APP_DIR"; exit 1; }
print_success "Direktori: $(pwd)"

# Cek apakah ada perubahan lokal yang belum di-commit
print_status "Mengecek status git..."
if [[ -n $(git status --porcelain) ]]; then
    if [[ "$AUTO_STASH" == "1" ]]; then
        print_warning "Ada perubahan lokal yang belum di-commit. Auto-stash aktif, perubahan akan disimpan otomatis."
        STASH_MSG="auto-stash update-simansa $(date '+%Y-%m-%d %H:%M:%S')"
        git stash push -u -m "$STASH_MSG"
        if [ $? -eq 0 ]; then
            print_success "Perubahan lokal disimpan ke stash: $STASH_MSG"
        else
            print_error "Gagal melakukan stash otomatis"
            exit 1
        fi
    else
        print_error "Ditemukan perubahan lokal dan AUTO_STASH=0. Update dihentikan."
        exit 1
    fi
fi

# Enable maintenance mode
print_status "Mengaktifkan maintenance mode..."
run_artisan down --retry=60 2>/dev/null || true
MAINTENANCE_ON=1

# Pull dari GitHub
print_status "Mengambil update dari GitHub..."
git fetch "$GIT_REMOTE"
git pull --ff-only "$GIT_REMOTE" "$GIT_BRANCH"
if [ $? -eq 0 ]; then
    print_success "Pull dari GitHub berhasil"
else
    print_error "Gagal pull dari GitHub"
    exit 1
fi

# Update Composer dependencies (jika ada perubahan composer.json)
print_status "Mengecek dan update dependencies Composer..."
if [[ -f "composer.lock" ]]; then
    composer_status=0

    if [[ -n "$COMPOSER_BIN" ]]; then
        "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
        composer_status=$?
    else
        print_warning "Composer tidak ditemukan, melewati composer install"
        composer_status=2
    fi

    if [ $composer_status -eq 0 ]; then
        print_success "Composer dependencies updated"
    else
        print_warning "Composer install gagal, melanjutkan..."
    fi
fi

# Jalankan migrasi database
print_status "Menjalankan migrasi database..."
run_artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Migrasi database berhasil"
else
    print_warning "Migrasi database mungkin tidak diperlukan atau ada error"
fi

# Clear semua cache
print_status "Membersihkan cache..."

echo "  - Clearing application cache..."
run_artisan cache:clear

echo "  - Clearing config cache..."
run_artisan config:clear

echo "  - Clearing route cache..."
run_artisan route:clear

echo "  - Clearing view cache..."
run_artisan view:clear

echo "  - Clearing compiled classes..."
run_artisan clear-compiled 2>/dev/null || true

echo "  - Clearing event cache..."
run_artisan event:clear 2>/dev/null || true

print_success "Semua cache telah dibersihkan"

# Rebuild cache untuk production
print_status "Membangun ulang cache untuk production..."

echo "  - Caching config..."
run_artisan config:cache

echo "  - Caching routes..."
run_artisan route:cache

echo "  - Caching views..."
run_artisan view:cache

print_success "Cache production telah dibangun"

# Optimize
print_status "Mengoptimasi aplikasi..."
run_artisan optimize 2>/dev/null || true

# Storage link (jika belum ada)
print_status "Mengecek storage link..."
if [ ! -L "public/storage" ]; then
    run_artisan storage:link
    print_success "Storage link dibuat"
else
    print_success "Storage link sudah ada"
fi

# Set ownership/permission (penting agar cache tidak permission denied)
print_status "Mengatur permission..."
chown -R "$APP_USER":"$APP_GROUP" storage bootstrap/cache 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 2775 {} \; 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
print_success "Permission diatur"

# Disable maintenance mode
print_status "Menonaktifkan maintenance mode..."
run_artisan up
MAINTENANCE_ON=0

# Tampilkan versi dan info
echo ""
echo -e "${GREEN}=============================================="
echo "   UPDATE SELESAI!"
echo "=============================================="
echo -e "${NC}"
print_status "Versi Laravel: $("$PHP_BIN" artisan --version)"
print_status "Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Tampilkan commit terakhir
print_status "Commit terakhir:"
git log -1 --pretty=format:"  %h - %s (%cr)" --abbrev-commit
echo ""
echo ""

print_success "Aplikasi SIMANSA V3 telah berhasil diupdate!"
echo ""
