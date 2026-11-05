# Campuly — production image (PHP-FPM + Nginx + Supervisor: web, queue worker, scheduler)
# Works on any VPS, Render, Railway, Fly.io, Koyeb, Cloud Run, Sevalla.
# Starting point: STEP 0.6 of MASTER_PROMPT.md finishes and tests it.

# ---------- 1. Front-end build ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js postcss.config.js ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY public ./public
# April UI and Livewire assets are imported from vendor/, so bring composer deps first.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache php84 php84-phar php84-mbstring php84-openssl php84-tokenizer php84-dom php84-xml php84-xmlwriter php84-curl php84-fileinfo php84-iconv php84-zip php84-intl php84-bcmath php84-gd php84-sodium \
    && ln -sf /usr/bin/php84 /usr/bin/php
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
RUN npm run build

# ---------- 2. Runtime ----------
FROM php:8.4-fpm-alpine AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    RUN_MIGRATIONS=false \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

RUN apk add --no-cache nginx supervisor bash icu-libs libzip libpng libjpeg-turbo freetype libpq mysql-client postgresql-client sqlite \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev sqlite-dev linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl opcache pcntl pdo_mysql pdo_pgsql pdo_sqlite zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-campuly.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/campuly-entrypoint
RUN chmod +x /usr/local/bin/campuly-entrypoint

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data storage bootstrap/cache \
    && rm -rf tests node_modules

EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s CMD wget -qO- http://127.0.0.1:8080/health || exit 1
ENTRYPOINT ["campuly-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
