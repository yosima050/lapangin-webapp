#!/bin/sh
set -e

# Buat symbolic link storage jika belum ada atau tidak valid
if [ ! -L /var/www/lapangin/public/storage ] || [ ! -e /var/www/lapangin/public/storage ]; then
    rm -rf /var/www/lapangin/public/storage
    php artisan storage:link --force || true
fi

# Pastikan package discovery dijalankan di runtime saat environment aktif
if [ ! -f /var/www/lapangin/bootstrap/cache/packages.php ]; then
    php artisan package:discover --ansi || true
fi

# Jalankan command (php-fpm / queue / scheduler)
exec "$@"

