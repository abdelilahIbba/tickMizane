#!/bin/bash

# Run migrations
php artisan migrate --force

# Run seeders if needed
# php artisan db:seed --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set permissions
chown -R www-data:www-data /app/storage
chown -R www-data:www-data /app/bootstrap/cache

echo "Container started successfully"

# Keep container running
tail -f /dev/null
