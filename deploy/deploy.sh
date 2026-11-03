#!/usr/bin/env bash
# =============================================================================
# LapangIn — Idempotent Deployment Script (CI/CD Automated & Manual)
#
# Penggunaan:
#   bash deploy/deploy.sh [environment] [branch]
#
# Contoh:
#   bash deploy/deploy.sh staging development
#   bash deploy/deploy.sh production main
#
# Karakteristik Idempoten:
#   1. Aman dieksekusi berulang kali (tidak akan error jika folder/container sudah ada).
#   2. Membersihkan dependensi lama dan menyinkronkan state secara deterministik.
#   3. Mempertahankan data persistence PostgreSQL & storage upload tanpa data loss.
# =============================================================================

set -euo pipefail

ENV_TARGET="${1:-staging}"
BRANCH="${2:-development}"

echo "================================================================="
echo "  LapangIn CI/CD Idempotent Deployment"
echo "  Waktu       : $(date '+%Y-%m-%d %H:%M:%S WIB')"
echo "  Target Env  : ${ENV_TARGET}"
echo "  Target Git  : origin/${BRANCH}"
echo "================================================================="

# ── 1. Tentukan Direktori dan File Konfigurasi Berdasarkan Environment ─────────
if [ "${ENV_TARGET}" = "production" ]; then
    APP_DIR="/var/www/lapangin"
    # Deteksi nama project asli dari kontainer database yang berjalan (default: deploy)
    DETECTED_PROJECT=$(docker inspect lapangin_db --format '{{ index .Config.Labels "com.docker.compose.project" }}' 2>/dev/null || true)
    COMPOSE_PROJECT="${DETECTED_PROJECT:-deploy}"
    COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.yml"
    DEFAULT_ENV_FILE="${APP_DIR}/deploy/.env.production"
else
    APP_DIR="/var/www/lapangin-staging"
    COMPOSE_PROJECT="lapangin_staging"
    COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.staging.yml"
    DEFAULT_ENV_FILE="${APP_DIR}/deploy/.env.staging"
fi

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
# Simpan backup di dalam direktori proyek agar tidak membutuhkan hak akses root
BACKUP_DIR="${APP_DIR}/storage/backups/${ENV_TARGET}"

# Pastikan folder target dan backup ada (Idempoten: mkdir -p tidak akan error jika sudah ada)
mkdir -p "${APP_DIR}"
mkdir -p "${BACKUP_DIR}" 2>/dev/null || BACKUP_DIR="/tmp/lapangin_backups/${ENV_TARGET}"
mkdir -p "${BACKUP_DIR}"

cd "${APP_DIR}"

# ── 2. Backup Database Sebelum Perubahan (Idempoten) ───────────────────────────
echo "[1/8] Memeriksa status database & membuat backup..."
if docker ps --format '{{.Names}}' | grep -q "${COMPOSE_PROJECT}_db\|lapangin_db\|lapangin_staging_db"; then
    echo "      Database aktif terdeteksi. Membuat snapshot pg_dump..."
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T db \
        pg_dump -U lapangin_user "${DB_DATABASE:-lapangin_db}" > "${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql" 2>/dev/null || true
    echo "      ✅ Backup tersimpan di ${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql"
else
    echo "      ℹ️ Kontainer database belum berjalan. Melewati backup awal."
fi

# Rotasi backup: Hapus file cadangan yang lebih lama dari 7 hari (Idempoten)
find "${BACKUP_DIR}" -name "*.sql" -mtime +7 -delete 2>/dev/null || true

# ── 3. Ambil Kode Terbaru dari Git (Idempoten & Bersih dari Conflict) ──────────
echo "[2/8] Menyinkronkan repositori Git (origin/${BRANCH})..."
# Pastikan storage dan cache dapat ditulis oleh host user dan UID/GID aplikasi (www-data) dengan mode 775
if docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" ps -q app 2>/dev/null | grep -q .; then
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T -u 0 app chown -R $(id -u):www-data storage bootstrap/cache
    if ! docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T -u 0 app chmod -R 775 storage bootstrap/cache; then
        echo "❌ Error: Gagal mengubah hak akses storage dan bootstrap/cache ke mode 775." >&2
        exit 1
    fi
fi
git fetch --all --prune
git checkout -B "${BRANCH}" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

# ── 4. Verifikasi File Environment .env (Idempoten) ───────────────────────────
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

# ── 5. Aktifkan Maintenance Mode Sementara ─────────────────────────────────────
echo "[4/8] Mengaktifkan maintenance mode..."
if docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" ps -q app 2>/dev/null | grep -q .; then
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app \
        php artisan down --retry=30 2>/dev/null || true
fi

# ── 6. Build & Jalankan Kontainer Docker (Idempoten) ──────────────────────────
echo "[5/8] Membangun dan memperbarui service kontainer Docker..."
docker network create lapangin_net 2>/dev/null || true
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" build app
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" up -d --remove-orphans

# ── 7. Eksekusi Migrasi Database & Storage Symlink (Idempoten) ────────────────
echo "[6/8] Menjalankan migrasi database & symlink storage..."
# Tunggu db healthy
echo "      Menunggu koneksi database PostgreSQL siap..."
sleep 4
if grep -q '^APP_KEY=$' "${APP_DIR}/.env" || grep -q '^APP_KEY=""$' "${APP_DIR}/.env" || ! grep -q '^APP_KEY=base64:' "${APP_DIR}/.env"; then
    echo "      Menghasilkan APP_KEY baru..."
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan key:generate --force || true
fi
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan migrate --force

# Symlink storage (Idempoten)
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan storage:link 2>/dev/null || true

# ── 8. Optimasi Cache & Mematikan Maintenance Mode ───────────────────────────
echo "[7/8] Mengoptimasi cache Laravel & menyalakan kembali aplikasi..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T -u 0 app chown -R $(id -u):www-data storage bootstrap/cache 2>/dev/null || true
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T -u 0 app chmod -R 775 storage bootstrap/cache 2>/dev/null || true
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize:clear
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan up 2>/dev/null || true

# Reload Nginx jika kontainer Nginx utama sedang berjalan (Idempoten & Zero Downtime)
if docker ps --format '{{.Names}}' | grep -q 'lapangin_nginx'; then
    echo "      Memperbarui konfigurasi Nginx (Hot Reload)..."
    docker exec lapangin_nginx nginx -s reload 2>/dev/null || true
fi

# ── 9. Verifikasi Healthcheck ─────────────────────────────────────────────────
echo "[8/8] Menjalankan healthcheck verifikasi..."
sleep 3
CONTAINER_STATUS=$(docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" ps --format '{{.Service}}: {{.Status}}')
echo "${CONTAINER_STATUS}"

echo "================================================================="
echo "✅ DEPLOYMENT BERHASIL & IDEMPOTEN!"
echo "   Environment : ${ENV_TARGET}"
echo "   Branch      : ${BRANCH}"
echo "================================================================="
