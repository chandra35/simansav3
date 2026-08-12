#!/bin/bash

# SIMANSA V3 - deployment production yang cepat dan terukur.
# Snapshot mencegah script berubah di tengah proses saat git fast-forward.
set -Eeuo pipefail

if [[ "${SIMANSA_DEPLOY_SNAPSHOT:-0}" != "1" ]]; then
    SNAPSHOT_FILE="$(mktemp /tmp/simansa-deploy.XXXXXX)"
    cp "$0" "$SNAPSHOT_FILE"
    chmod 700 "$SNAPSHOT_FILE"
    SIMANSA_DEPLOY_SNAPSHOT=1 SIMANSA_DEPLOY_SNAPSHOT_FILE="$SNAPSHOT_FILE" exec bash "$SNAPSHOT_FILE" "$@"
fi

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }
now_ms() { date +%s%3N; }

run_timed() {
    local label="$1" started elapsed
    shift
    started="$(now_ms)"
    "$@"
    elapsed=$(( $(now_ms) - started ))
    print_success "$label selesai dalam $((elapsed / 1000)).$(printf '%03d' $((elapsed % 1000))) detik"
}

detect_app_dir() {
    local candidate
    for candidate in "${APP_DIR:-}" "/www/wwwroot/simansa.man1metro.sch.id" "/home/simansa/htdocs/simansa.man1metro.sch.id" "/home/manmetr1/simansa.man1metro.sch.id"; do
        if [[ -n "$candidate" && -d "$candidate" ]]; then echo "$candidate"; return 0; fi
    done
    return 1
}

APP_DIR="$(detect_app_dir)" || { print_error "Direktori aplikasi tidak ditemukan. Set APP_DIR terlebih dahulu."; exit 1; }
AUTO_STASH="${AUTO_STASH:-1}"
GIT_REMOTE="${GIT_REMOTE:-origin}"
GIT_BRANCH="${GIT_BRANCH:-master}"
APP_USER="${APP_USER:-www}"
APP_GROUP="${APP_GROUP:-www}"
HEALTH_URL="${HEALTH_URL:-https://simansa.man1metro.sch.id}"
PHP_BIN="${PHP_BIN:-$(command -v /www/server/php/83/bin/php || command -v php8.3 || command -v php || true)}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"
MAINTENANCE_ON=0
DEPLOY_STARTED="$(now_ms)"
MAINTENANCE_STARTED=0

[[ -n "$PHP_BIN" ]] || { print_error "Binary PHP tidak ditemukan."; exit 1; }

run_as_app_user() {
    if [[ "$(id -u)" -eq 0 ]]; then sudo -u "$APP_USER" "$@"; else "$@"; fi
}
run_artisan() { run_as_app_user "$PHP_BIN" artisan "$@"; }

cleanup_on_exit() {
    local exit_code=$?
    if [[ $MAINTENANCE_ON -eq 1 ]]; then
        run_artisan up >/dev/null 2>&1 || true
        print_warning "Maintenance dinonaktifkan otomatis setelah proses terhenti."
    fi
    [[ -n "${SIMANSA_DEPLOY_SNAPSHOT_FILE:-}" ]] && rm -f "$SIMANSA_DEPLOY_SNAPSHOT_FILE"
    exit "$exit_code"
}
trap cleanup_on_exit EXIT

echo ""
echo -e "${GREEN}=============================================="
echo "   SIMANSA V3 - FAST UPDATE"
echo -e "==============================================${NC}"

cd "$APP_DIR"
print_success "Direktori: $(pwd)"

exec 9>/tmp/simansa-production-deploy.lock
if ! flock -n 9; then print_error "Deploy lain masih berjalan."; exit 1; fi

if [[ -n "$(git status --porcelain)" ]]; then
    if [[ "$AUTO_STASH" != "1" ]]; then print_error "Ditemukan perubahan lokal dan AUTO_STASH=0."; exit 1; fi
    STASH_MSG="auto-stash update-simansa $(date '+%Y-%m-%d %H:%M:%S')"
    print_warning "Perubahan lokal disimpan otomatis: $STASH_MSG"
    git stash push -u -m "$STASH_MSG"
fi

# Waktu jaringan tidak lagi masuk masa maintenance.
run_timed "Git fetch" git fetch "$GIT_REMOTE" "$GIT_BRANCH"
OLD_HEAD="$(git rev-parse HEAD)"
TARGET_HEAD="$(git rev-parse FETCH_HEAD)"

if [[ "$OLD_HEAD" == "$TARGET_HEAD" ]]; then
    TOTAL_ELAPSED=$(( $(now_ms) - DEPLOY_STARTED ))
    print_success "Aplikasi sudah menggunakan commit terbaru. Tidak ada downtime."
    print_success "Pemeriksaan selesai dalam $((TOTAL_ELAPSED / 1000)).$(printf '%03d' $((TOTAL_ELAPSED % 1000))) detik"
    exit 0
fi

CHANGED_FILES="$(git diff --name-only "$OLD_HEAD" "$TARGET_HEAD")"
COMPOSER_CHANGED=0; MIGRATION_CHANGED=0; RUNTIME_CHANGED=0
grep -Eq '^(composer\.json|composer\.lock)$' <<< "$CHANGED_FILES" && COMPOSER_CHANGED=1 || true
grep -Eq '^database/migrations/' <<< "$CHANGED_FILES" && MIGRATION_CHANGED=1 || true
grep -Eq '^(app|bootstrap|config|database|public|resources|routes)/|^(artisan|composer\.(json|lock))$' <<< "$CHANGED_FILES" && RUNTIME_CHANGED=1 || true
print_status "Perubahan: $(wc -l <<< "$CHANGED_FILES" | tr -d ' ') file"

if [[ $RUNTIME_CHANGED -eq 1 ]]; then
    print_status "Mengaktifkan maintenance mode..."
    run_artisan down --retry=60 >/dev/null 2>&1 || true
    MAINTENANCE_ON=1
    MAINTENANCE_STARTED="$(now_ms)"
fi

run_timed "Git fast-forward" git merge --ff-only "$TARGET_HEAD"

if [[ $COMPOSER_CHANGED -eq 1 ]]; then
    [[ -n "$COMPOSER_BIN" ]] || { print_error "Composer dibutuhkan tetapi binary tidak ditemukan."; exit 1; }
    run_timed "Composer install" env COMPOSER_ALLOW_SUPERUSER=1 "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
else
    print_success "Composer dilewati; manifest dependency tidak berubah."
fi

if [[ $MIGRATION_CHANGED -eq 1 ]]; then
    run_timed "Migrasi database" run_artisan migrate --force
else
    print_success "Migrasi dilewati; file migration tidak berubah."
fi

if [[ $RUNTIME_CHANGED -eq 1 ]]; then
    run_timed "Pembersihan cache" run_artisan optimize:clear
    run_timed "Pembuatan cache production" run_artisan optimize

    if [[ ! -L public/storage ]]; then run_timed "Storage link" run_artisan storage:link; fi

    # Jangan traversal seluruh storage (551 MB/puluhan ribu direktori).
    install -d -o "$APP_USER" -g "$APP_GROUP" -m 2775 storage/framework storage/framework/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache

    print_status "Menonaktifkan maintenance mode..."
    run_artisan up
    MAINTENANCE_ON=0
    MAINTENANCE_ELAPSED=$(( $(now_ms) - MAINTENANCE_STARTED ))
    print_success "Maintenance berlangsung $((MAINTENANCE_ELAPSED / 1000)).$(printf '%03d' $((MAINTENANCE_ELAPSED % 1000))) detik"
fi

if command -v curl >/dev/null 2>&1; then
    HTTP_CODE="$(curl -L -sS -o /dev/null -w '%{http_code}' --max-time 15 "$HEALTH_URL")"
    [[ "$HTTP_CODE" =~ ^(200|301|302)$ ]] || { print_error "Health check gagal: HTTP $HTTP_CODE"; exit 1; }
    print_success "Health check HTTP $HTTP_CODE"
fi

TOTAL_ELAPSED=$(( $(now_ms) - DEPLOY_STARTED ))
echo ""
print_success "Update selesai dalam $((TOTAL_ELAPSED / 1000)).$(printf '%03d' $((TOTAL_ELAPSED % 1000))) detik"
print_status "Commit: $(git log -1 --pretty=format:'%h - %s')"
print_status "Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
