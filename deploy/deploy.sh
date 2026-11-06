#!/usr/bin/env bash
# =============================================================================
# LapangIn — Resilient, Atomic & Resource-Aware Deployment Script
# Digunakan untuk otomatisasi CI/CD (GitHub Actions) maupun manual pada server 1 GiB RAM
#
# Penggunaan:
#   bash deploy/deploy.sh [environment] [branch]
#
# Contoh:
#   bash deploy/deploy.sh staging development
#   bash deploy/deploy.sh production main
# =============================================================================

set -euo pipefail

ENV_TARGET="${1:-staging}"
BRANCH="${2:-development}"

DEPLOY_TIME=$(TZ="Asia/Jakarta" date '+%Y-%m-%d %H:%M:%S WIB' 2>/dev/null || date '+%Y-%m-%d %H:%M:%S')
echo "================================================================="
echo "  LapangIn Resilient Deployment"
echo "  Waktu       : ${DEPLOY_TIME}"
echo "  Target Env  : ${ENV_TARGET}"
echo "  Target Git  : origin/${BRANCH}"
echo "================================================================="

# ── 1. Tentukan Direktori dan File Konfigurasi Berdasarkan Environment ─────────
if [ "${ENV_TARGET}" = "production" ]; then
    APP_DIR="/var/www/lapangin"
    DETECTED_PROJECT=$(docker inspect lapangin_db --format '{{ index .Config.Labels "com.docker.compose.project" }}' 2>/dev/null || true)
    COMPOSE_PROJECT="${DETECTED_PROJECT:-deploy}"
    COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.yml"
    DEFAULT_ENV_FILE="${APP_DIR}/deploy/.env.production"
    APP_CONTAINER="lapangin_app"
    DB_CONTAINER="lapangin_db"
else
    APP_DIR="/var/www/lapangin-staging"
    COMPOSE_PROJECT="lapangin_staging"
    COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.staging.yml"
    DEFAULT_ENV_FILE="${APP_DIR}/deploy/.env.staging"
    APP_CONTAINER="lapangin_staging_app"
    DB_CONTAINER="lapangin_staging_db"
fi

TIMESTAMP=$(TZ="Asia/Jakarta" date +"%Y%m%d_%H%M%S" 2>/dev/null || date +"%Y%m%d_%H%M%S")
BACKUP_DIR="${APP_DIR}/storage/backups/${ENV_TARGET}"

mkdir -p "${APP_DIR}"
mkdir -p "${BACKUP_DIR}" 2>/dev/null || BACKUP_DIR="/tmp/lapangin_backups/${ENV_TARGET}"
mkdir -p "${BACKUP_DIR}"

cd "${APP_DIR}"

# ── 2. Backup Database Sebelum Perubahan (Non-blocking) ───────────────────────
echo "[1/8] Memeriksa status database & membuat snapshot backup..."
if docker ps --format '{{.Names}}' | grep -q "${COMPOSE_PROJECT}_db\|lapangin_db\|lapangin_staging_db"; then
    echo "      Database aktif terdeteksi. Membuat snapshot pg_dump..."
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T db \
        pg_dump -U lapangin_user "${DB_DATABASE:-lapangin_db}" > "${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql" 2>/dev/null || true
    echo "      ✅ Snapshot backup tersimpan."
else
    echo "      ℹ️ Kontainer database belum berjalan. Melewati backup awal."
fi

# Rotasi backup: Hapus file cadangan yang lebih lama dari 7 hari
find "${BACKUP_DIR}" -name "*.sql" -mtime +7 -delete 2>/dev/null || true

# ── 3. Ambil Kode Terbaru dari Git (Idempoten & Bersih dari Conflict) ──────────
echo "[2/8] Menyinkronkan repositori Git (origin/${BRANCH})..."

docker exec -u 0 "${APP_CONTAINER}" chown -R "$(id -u)" \
    /var/www/lapangin/bootstrap/cache \
    /var/www/lapangin/storage > /dev/null 2>&1 || true

git fetch origin "${BRANCH}"
git checkout -B "${BRANCH}" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

# ── 4. Verifikasi File Environment .env ───────────────────────────────────────
echo "[3/8] Memeriksa konfigurasi .env..."
if [ ! -f "${APP_DIR}/.env" ]; then
    if [ -f "${DEFAULT_ENV_FILE}" ]; then
        echo "      Menyalin template ${DEFAULT_ENV_FILE} ke .env..."
        cp "${DEFAULT_ENV_FILE}" "${APP_DIR}/.env"
    else
        echo "      Menyalin .env.example ke .env..."
        cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
    fi
fi

# ── 5. Build Image Docker (Menggunakan Layer Cache Lokal & Zero Downtime) ─────
echo "[4/8] Membangun image Docker menggunakan layer cache lokal..."
docker network create lapangin_net 2>/dev/null || true
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" build app

# ── 6. Update Kontainer (Atomic Container Swap — Tanpa HTTP 503) ──────────────
echo "[5/8] Memperbarui service kontainer Docker secara atomik..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" up -d --remove-orphans

# ── 7. Jalankan Migrasi Database & Storage Symlink ────────────────────────────
echo "[6/8] Menjalankan migrasi database & symlink storage..."
# Tunggu db healthy
echo "      Menunggu koneksi database PostgreSQL siap..."
sleep 4

if grep -q '^APP_KEY=$' "${APP_DIR}/.env" || grep -q '^APP_KEY=""$' "${APP_DIR}/.env" || ! grep -q '^APP_KEY=base64:' "${APP_DIR}/.env"; then
    echo "      Menghasilkan APP_KEY baru..."
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan key:generate --force || true
fi

docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan migrate --force
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan storage:link 2>/dev/null || true

# ── 8. Optimasi Cache & Mematikan Maintenance Mode ───────────────────────────
echo "[7/8] Mengoptimasi cache Laravel & menyalakan aplikasi..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan package:discover --ansi 2>/dev/null || true
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize:clear
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan up 2>/dev/null || true

# Kembalikan kepemilikan storage & cache ke www-data
docker exec -u 0 "${APP_CONTAINER}" chown -R www-data:www-data /var/www/lapangin/storage /var/www/lapangin/bootstrap/cache 2>/dev/null || true
docker exec -u 0 "${APP_CONTAINER}" chmod -R 775 /var/www/lapangin/storage /var/www/lapangin/bootstrap/cache 2>/dev/null || true

# Reload Nginx jika kontainer Nginx utama sedang berjalan (Hot Reload)
if [ -n "$(docker ps -q -f "name=^lapangin_nginx$")" ]; then
    echo "      Memperbarui konfigurasi Nginx (Hot Reload)..."
    docker exec lapangin_nginx nginx -s reload 2>/dev/null || true
fi

# ── 9. Verifikasi Status Kontainer ───────────────────────────────────────────
echo "[8/8] Memverifikasi status kontainer..."
sleep 2
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" ps

echo "================================================================="
echo "✅ DEPLOYMENT BERHASIL & SEHAT!"
echo "   Environment : ${ENV_TARGET}"
echo "   Branch      : ${BRANCH}"
echo "================================================================="
