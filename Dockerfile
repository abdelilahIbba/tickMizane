FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd

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

# In local development this project is bind-mounted from the host.
# Keep image builds network-light to avoid transient dependency download failures.
# Use host-installed dependencies (vendor, node_modules, public/build) instead.

# Set permissions
RUN chown -R www-data:www-data /app

EXPOSE 9000

CMD ["php-fpm"]
