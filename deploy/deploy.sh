#!/usr/bin/env bash
# =============================================================================
# LapangIn — Deploy Script (jalankan di VPS sebagai deploy user)
# Penggunaan: bash deploy/deploy.sh [branch]
# Contoh:     bash deploy/deploy.sh development
# =============================================================================
set -euo pipefail

BRANCH="${1:-development}"
APP_DIR="/var/www/lapangin"
COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.yml"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/lapangin"

echo "======================================================"
echo " LapangIn Deploy Script — $(date '+%Y-%m-%d %H:%M:%S')"
echo " Branch: ${BRANCH}"
echo "======================================================"

# ── 1. Backup database sebelum deploy ─────────────────────────────────────────
echo "[1/7] Backup database PostgreSQL..."
mkdir -p "${BACKUP_DIR}"
docker compose -f "${COMPOSE_FILE}" exec -T db \
    pg_dump -U lapangin_user lapangin_db \
    > "${BACKUP_DIR}/lapangin_db_${TIMESTAMP}.sql"
echo "      Backup saved: ${BACKUP_DIR}/lapangin_db_${TIMESTAMP}.sql"

# Hapus backup lebih dari 7 hari
find "${BACKUP_DIR}" -name "*.sql" -mtime +7 -delete

# ── 2. Pull latest code ───────────────────────────────────────────────────────
echo "[2/7] Pulling latest code dari branch '${BRANCH}'..."
cd "${APP_DIR}"
git fetch origin
git checkout "${BRANCH}"
git pull origin "${BRANCH}"

# ── 3. Aktifkan maintenance mode ──────────────────────────────────────────────
echo "[3/7] Mengaktifkan maintenance mode..."
docker compose -f "${COMPOSE_FILE}" exec -T app \
    php artisan down --retry=60 --secret="lapangin-bypass-$(openssl rand -hex 8)"

# ── 4. Build Docker image baru ────────────────────────────────────────────────
echo "[4/7] Building Docker image..."
docker compose -f "${COMPOSE_FILE}" build app

# ── 5. Jalankan migrasi database ──────────────────────────────────────────────
echo "[5/7] Menjalankan database migrations..."
docker compose -f "${COMPOSE_FILE}" run --rm app php artisan migrate --force

# ── 6. Restart semua services ─────────────────────────────────────────────────
echo "[6/7] Restart services..."
docker compose -f "${COMPOSE_FILE}" up -d --remove-orphans

# ── 7. Matikan maintenance mode ───────────────────────────────────────────────
echo "[7/7] Mematikan maintenance mode..."
sleep 5
docker compose -f "${COMPOSE_FILE}" exec -T app php artisan up

echo ""
echo "✅ Deploy selesai! Aplikasi berjalan di $(grep APP_URL "${APP_DIR}/.env" | cut -d= -f2)"

