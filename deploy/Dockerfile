# =============================================================================
# LapangIn — Dockerfile (PHP 8.3-FPM, Production/Staging)
# =============================================================================
FROM php:8.3-fpm-alpine

# ── System dependencies ───────────────────────────────────────────────────────
RUN apk add --no-cache \
    bash curl git unzip supervisor \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev oniguruma-dev icu-dev \
    postgresql-dev \
    nodejs npm

# ── PHP extensions ────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pgsql \
        gd zip bcmath intl mbstring \
        opcache pcntl

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP OPcache config (production) ──────────────────────────────────────────
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# ── Nonaktifkan fungsi PHP berbahaya ─────────────────────────────────────────
RUN echo "disable_functions = exec,passthru,shell_exec,system,proc_open,popen" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/security.ini

# ── Working directory ─────────────────────────────────────────────────────────
WORKDIR /var/www/lapangin

# ── Install dependencies (no dev) ────────────────────────────────────────────
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ── Copy source ───────────────────────────────────────────────────────────────
COPY . .

# ── Build frontend assets (Vite) ─────────────────────────────────────────────
RUN npm ci && npm run build && rm -rf node_modules

# ── Laravel bootstrap ─────────────────────────────────────────────────────────
RUN php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/lapangin \
    && chmod -R 755 /var/www/lapangin/storage \
    && chmod -R 755 /var/www/lapangin/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

