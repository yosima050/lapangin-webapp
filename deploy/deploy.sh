#!/usr/bin/env bash
# =============================================================================
# LapangIn — Resilient, Atomic & Resource-Aware Deployment Script
# Digunakan untuk otomatisasi CI/CD (GitHub Actions) maupun manual pada server 1 GiB RAM
#
# Penggunaan:
#   bash deploy/deploy.sh [environment] [branch] [app_image]
#
# Contoh:
#   bash deploy/deploy.sh staging development ghcr.io/yosima050/lapangin-webapp/app:sha123
#   bash deploy/deploy.sh production main ghcr.io/yosima050/lapangin-webapp/app:sha123
# =============================================================================

set -euo pipefail

# ── 0. Concurrency Lock (Mencegah overlapping CI/CD runs bertabrakan) ──────────
exec 200>/tmp/lapangin_deploy.lock
if ! flock -n 200; then
    echo "❌ Deployment lain sedang berjalan di server ini (/tmp/lapangin_deploy.lock terkunci)."
    echo "   Membatalkan proses ini agar tidak terjadi benturan file atau database."
    exit 1
fi

ENV_TARGET="${1:-staging}"
BRANCH="${2:-development}"
APP_IMAGE="${3:-${APP_IMAGE:-}}"
export APP_IMAGE

DEPLOY_TIME=$(TZ="Asia/Jakarta" date '+%Y-%m-%d %H:%M:%S WIB')
echo "================================================================="
echo "  LapangIn Resilient Deployment"
echo "  Waktu       : ${DEPLOY_TIME}"
echo "  Target Env  : ${ENV_TARGET}"
echo "  Target Git  : origin/${BRANCH}"
if [ -n "${APP_IMAGE}" ]; then
    echo "  Image Target: ${APP_IMAGE}"
fi
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
    HEALTH_URL="http://127.0.0.1/up"
else
    APP_DIR="/var/www/lapangin-staging"
    COMPOSE_PROJECT="lapangin_staging"
    COMPOSE_FILE="${APP_DIR}/deploy/docker-compose.staging.yml"
    DEFAULT_ENV_FILE="${APP_DIR}/deploy/.env.staging"
    APP_CONTAINER="lapangin_staging_app"
    DB_CONTAINER="lapangin_staging_db"
    HEALTH_URL="http://127.0.0.1:8080/up"
fi

TIMESTAMP=$(TZ="Asia/Jakarta" date +"%Y%m%d_%H%M%S")
BACKUP_DIR="${APP_DIR}/storage/backups/${ENV_TARGET}"

mkdir -p "${APP_DIR}"
mkdir -p "${BACKUP_DIR}" 2>/dev/null || BACKUP_DIR="/tmp/lapangin_backups/${ENV_TARGET}"
mkdir -p "${BACKUP_DIR}"

cd "${APP_DIR}"

# Simpan image saat ini untuk mekanisme rollback otomatis jika deploy gagal
PREVIOUS_IMAGE=$(docker inspect --format='{{.Config.Image}}' "${APP_CONTAINER}" 2>/dev/null || true)

rollback_on_failure() {
    echo "⚠️ Deployment mengalami kegagalan!"
    if [ -n "${PREVIOUS_IMAGE}" ]; then
        echo "🔄 Menjalankan rollback otomatis ke image sebelumnya (${PREVIOUS_IMAGE})..."
        APP_IMAGE="${PREVIOUS_IMAGE}" docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" up -d --remove-orphans || true
        echo "ℹ️ Rollback container ke ${PREVIOUS_IMAGE} selesai."
    fi
}
trap rollback_on_failure ERR

# ── 2. Backup Database Sebelum Perubahan (Validasi file non-empty) ─────────────
echo "[1/8] Memeriksa status database & membuat snapshot backup..."
# Gunakan docker ps -q -f name= untuk menghindari SIGPIPE di bawah pipefail
if [ -n "$(docker ps -q -f "name=^${DB_CONTAINER}$")" ]; then
    BACKUP_FILE="${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql"
    echo "      Database aktif terdeteksi (${DB_CONTAINER}). Menjalankan pg_dump..."

    DB_USER=$(grep -E '^DB_USERNAME=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d ' "\r' || echo "lapangin_user")
    DB_NAME=$(grep -E '^DB_DATABASE=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d ' "\r' || echo "lapangin_db")
    [ -z "${DB_USER}" ] && DB_USER="lapangin_user"
    [ -z "${DB_NAME}" ] && DB_NAME="lapangin_db"

    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T db \
        pg_dump -U "${DB_USER}" "${DB_NAME}" > "${BACKUP_FILE}" 2>/dev/null || true

    if [ -s "${BACKUP_FILE}" ]; then
        echo "      ✅ Backup tersimpan dan valid (${BACKUP_FILE}, size: $(stat -c%s "${BACKUP_FILE}" 2>/dev/null || wc -c < "${BACKUP_FILE}") bytes)"
    else
        echo "      ❌ Gagal membuat snapshot backup atau ukuran file 0 byte!"
        rm -f "${BACKUP_FILE}"
        if [ "${ENV_TARGET}" = "production" ]; then
            echo "      ⛔ Membatalkan deployment production demi melindungi integritas data!"
            exit 1
        else
            echo "      ⚠️ Peringatan: Backup staging kosong, melanjutkan deploy..."
        fi
    fi
else
    echo "      ℹ️ Kontainer database ${DB_CONTAINER} belum berjalan. Melewati backup awal."
fi

# Rotasi backup: Hapus file cadangan yang lebih lama dari 7 hari
find "${BACKUP_DIR}" -name "*.sql" -mtime +7 -delete 2>/dev/null || true

# ── 3. Ambil Kode Terbaru dari Git (Hanya File Konfigurasi / Manifest) ─────────
echo "[2/8] Menyinkronkan repositori Git (origin/${BRANCH})..."

# Pulihkan kepemilikan file yang mungkin ditulis oleh kontainer Docker (www-data)
if [ -n "$(docker ps -q -f "name=^${APP_CONTAINER}$")" ]; then
    docker exec -u 0 "${APP_CONTAINER}" chown -R "$(id -u)" \
        /var/www/lapangin/bootstrap/cache \
        /var/www/lapangin/storage > /dev/null 2>&1 || true
fi

git fetch --all --prune
git checkout -B "${BRANCH}" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

# ── 4. Verifikasi File Environment .env & Validasi APP_KEY ────────────────────
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

# Validasi Keamanan APP_KEY
HAS_VALID_APP_KEY=false
if grep -q '^APP_KEY=base64:' "${APP_DIR}/.env"; then
    HAS_VALID_APP_KEY=true
fi

if [ "${HAS_VALID_APP_KEY}" = "false" ]; then
    if [ "${ENV_TARGET}" = "production" ]; then
        echo "❌ FATAL: APP_KEY belum diisi atau tidak valid di ${APP_DIR}/.env!"
        echo "   Mengenerate APP_KEY baru di production akan merusak data terenkripsi dan membatalkan semua user sessions."
        echo "   Silakan tentukan APP_KEY yang konsisten di .env server production."
        exit 1
    else
        echo "      ⚠️ APP_KEY belum ada di staging. Membuat APP_KEY baru secara aman..."
        NEW_KEY=$(docker run --rm --entrypoint php "${APP_IMAGE:-lapangin-app:staging}" -r 'echo "base64:".base64_encode(random_bytes(32));' 2>/dev/null || true)
        if [ -n "${NEW_KEY}" ]; then
            sed -i "s|^APP_KEY=.*|APP_KEY=${NEW_KEY}|" "${APP_DIR}/.env"
        fi
    fi
fi

# ── 5. Siapkan Image Kontainer (Pull dari Registry GHCR atau Build Fallback) ──
echo "[4/8] Menyiapkan Image Docker Aplikasi..."
docker network create lapangin_net 2>/dev/null || true
if [ "${ENV_TARGET}" = "staging" ]; then
    docker network create lapangin_staging_net 2>/dev/null || true
fi

PULLED_SUCCESS=false
if [ -n "${APP_IMAGE}" ]; then
    echo "      📥 Mengunduh pre-built image dari GHCR: ${APP_IMAGE}..."
    if docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" pull app; then
        PULLED_SUCCESS=true
        echo "      ✅ Image berhasil diunduh tanpa beban build di server VPS."
    fi
fi

if [ "${PULLED_SUCCESS}" = "false" ]; then
    echo "      ℹ️ Image pre-built tidak tersedia atau pull gagal. Melakukan build lokal..."
    docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" build app
fi

# ── 6. Pastikan Database Sehat & Jalankan Migrasi Sebelum Container Live ──────
echo "[5/8] Memastikan database aktif dan menjalankan migrasi skema..."
# Nyalakan service db terlebih dahulu jika belum hidup
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" up -d db

echo "      Menunggu kesiapan database via pg_isready..."
DB_USER=$(grep -E '^DB_USERNAME=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d ' "\r' || echo "lapangin_user")
DB_NAME=$(grep -E '^DB_DATABASE=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d ' "\r' || echo "lapangin_db")
[ -z "${DB_USER}" ] && DB_USER="lapangin_user"
[ -z "${DB_NAME}" ] && DB_NAME="lapangin_db"

MAX_RETRIES=30
RETRY_COUNT=0
until docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T db pg_isready -U "${DB_USER}" -d "${DB_NAME}" >/dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ "${RETRY_COUNT}" -ge "${MAX_RETRIES}" ]; then
        echo "❌ Database tidak siap setelah 30 detik. Aborting deployment."
        exit 1
    fi
    sleep 1
done
echo "      ✅ Database PostgreSQL siap."

# Eksekusi migrasi sebelum traffic berpindah ke container baru
echo "      Menjalankan database migration (--force)..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" run --rm --no-deps app php artisan migrate --force

# Symlink storage
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" run --rm --no-deps app php artisan storage:link 2>/dev/null || true

# ── 7. Update Kontainer (Atomic Container Swap) & Optimasi Cache ──────────────
echo "[6/8] Memperbarui service kontainer Docker secara atomik..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" up -d --remove-orphans

echo "[7/8] Mengoptimasi cache Laravel & menyalakan aplikasi..."
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize:clear
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan optimize
docker compose -p "${COMPOSE_PROJECT}" -f "${COMPOSE_FILE}" exec -T app php artisan up 2>/dev/null || true

# Pastikan permission storage & cache dimiliki www-data
docker exec -u 0 "${APP_CONTAINER}" chown -R www-data:www-data /var/www/lapangin/storage /var/www/lapangin/bootstrap/cache 2>/dev/null || true
docker exec -u 0 "${APP_CONTAINER}" chmod -R 775 /var/www/lapangin/storage /var/www/lapangin/bootstrap/cache 2>/dev/null || true

# Reload Nginx jika kontainer Nginx utama sedang berjalan
if [ -n "$(docker ps -q -f "name=^lapangin_nginx$")" ]; then
    echo "      Memperbarui konfigurasi Nginx (Hot Reload)..."
    docker exec lapangin_nginx nginx -s reload 2>/dev/null || true
fi

# ── 8. Verifikasi Real Healthcheck HTTP Endpoint ──────────────────────────────
echo "[8/8] Menjalankan verifikasi Healthcheck HTTP (${HEALTH_URL})..."
HEALTHY=false
for i in $(seq 1 15); do
    if curl -fsS -o /dev/null "${HEALTH_URL}" 2>/dev/null; then
        echo "      ✅ Healthcheck HTTP 200 OK berhasil (percobaan ${i}/15)."
        HEALTHY=true
        break
    fi
    echo "      ⏳ Percobaan ${i}/15: Menunggu HTTP 200 di ${HEALTH_URL}..."
    sleep 3
done

if [ "${HEALTHY}" != "true" ]; then
    echo "❌ Healthcheck GAGAL: Aplikasi tidak merespons HTTP 200 di ${HEALTH_URL} setelah 45 detik!"
    exit 1
fi

# ── 9. Pembersihan Disk (Dangling Images & Build Cache) ────────────────────────
echo "🧹 Membersihkan image lama dan cache yang tidak terpakai..."
docker image prune -f --filter "until=24h" 2>/dev/null || docker image prune -f 2>/dev/null || true

# Hapus trap karena deployment berhasil tanpa error
trap - ERR

echo "================================================================="
echo "✅ DEPLOYMENT BERHASIL & SEHAT!"
echo "   Environment : ${ENV_TARGET}"
echo "   Branch      : ${BRANCH}"
echo "   Image       : ${APP_IMAGE:-lokal}"
echo "================================================================="
