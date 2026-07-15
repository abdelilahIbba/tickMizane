FROM php:8.2-fpm

# Install system dependencies
# NOTE: npm is NOT included — it pulls 400+ heavy packages and belongs on the host only
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# NOTE: opcache is already compiled in php:8.2-fpm — we only need the ini file to enable it
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# -------------------------------------------------------
# OPcache — enable via ini (extension already built in)
# -------------------------------------------------------
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini

# -------------------------------------------------------
# PHP runtime settings
# -------------------------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php-custom.ini

# -------------------------------------------------------
# PHP-FPM pool settings (dynamic workers, slow log, etc.)
# -------------------------------------------------------
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# On Windows bind mounts, www-data often cannot write to mounted files.
# Use root workers in local dev to keep Laravel storage writable.
RUN sed -i 's/^user = www-data$/user = root/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = www-data$/group = root/' /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Create required Laravel storage directories
RUN mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views \
    && mkdir -p /app/storage/framework/cache \
    && mkdir -p /app/storage/logs \
    && mkdir -p /app/bootstrap/cache

# Set permissions
RUN chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 9000

# -R = allow running as root (needed for Windows bind mounts)
CMD ["php-fpm", "-R"]
