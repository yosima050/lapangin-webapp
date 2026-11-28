#!/bin/sh
set -e

# Buat symbolic link storage jika belum ada atau tidak valid
if [ ! -L /var/www/lapangin/public/storage ] || [ ! -e /var/www/lapangin/public/storage ]; then
    rm -rf /var/www/lapangin/public/storage
    php artisan storage:link --force || true
fi

# Pastikan package discovery selalu sinkron dengan vendor terbaru saat container start
php artisan package:discover --ansi || true

# Jalankan command (php-fpm / queue / scheduler)
exec "$@"

