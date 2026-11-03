# =============================================================================
# LapangIn — Dockerfile (PHP 8.3-FPM, Production/Staging)
# =============================================================================
FROM php:8.3-fpm-alpine

# ── System dependencies ───────────────────────────────────────────────────────
RUN apk add --no-cache \
    bash curl git unzip supervisor \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev \
    postgresql-dev

# ── PHP extensions (pdo, mbstring, opcache sudah bawaan php:8.3-fpm-alpine) ───
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql pgsql \
        gd zip bcmath pcntl

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP OPcache config (production) ──────────────────────────────────────────
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# ── Security & Limits (JANGAN nonaktifkan proc_open/popen karena dibutuhkan Laravel) ──
RUN echo "disable_functions = exec,passthru,shell_exec,system" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/security.ini

# ── Working directory ─────────────────────────────────────────────────────────
WORKDIR /var/www/lapangin

# ── Install dependencies (no dev) ────────────────────────────────────────────
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ── Copy source ───────────────────────────────────────────────────────────────
COPY . .

# ── Generate full autoloader with package discovery ──────────────────────────
RUN composer dump-autoload --optimize --no-dev

# ── Frontend assets (Vite) ───────────────────────────────────────────────────
# Asset frontend sudah terkompilasi dan tersedia di direktori public/build/ (terlacak Git).
# Tidak perlu kompilasi ulang via nodejs di dalam container agar build cepat dan hemat RAM.

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/lapangin \
    && chmod -R 775 /var/www/lapangin/storage \
    && chmod -R 775 /var/www/lapangin/bootstrap/cache

# ── Entrypoint script ─────────────────────────────────────────────────────────
COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
