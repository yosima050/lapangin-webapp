#!/bin/sh
set -e

# Buat symbolic link storage jika belum ada
if [ ! -L /var/www/lapangin/public/storage ]; then
    php artisan storage:link || true
fi

# Jalankan command (php-fpm / queue / scheduler)
exec "$@"

