# ─────────────────────────────────────────────
# Stage 1 – Build front-end assets (Node / Vite)
# ─────────────────────────────────────────────
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ─────────────────────────────────────────────
# Stage 2 – PHP-FPM application image
# ─────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS app

LABEL maintainer="techMizane"

# System dependencies
RUN apk add --no-cache \
        bash \
        curl \
        git \
        libpng-dev \
        libjpeg-turbo-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        zip \
        unzip

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_pgsql \
        pgsql \
        zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

# Copy application source
COPY . .

# Copy pre-built Vite assets from the frontend stage
COPY --from=frontend /app/public/build ./public/build

# Run post-install scripts (e.g. package:discover)
RUN composer run-script post-autoload-dump

# Storage & cache directories
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# PHP runtime configuration
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
# Make PHP-FPM listen on all interfaces so nginx can reach it cross-container
COPY .docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Container entrypoint
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
