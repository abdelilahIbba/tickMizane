#!/bin/bash
set -e

cd /var/www/html

# ── Fix permissions at runtime (needed when volume-mounted) ──────────────────
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ── Generate app key if not set ──────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] Generating application key..."
    php artisan key:generate --no-interaction --force
fi

# ── Wait for PostgreSQL and run migrations ───────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --no-interaction --force

# ── Cache configuration (skip in development) ────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "[entrypoint] Caching config, routes, views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "[entrypoint] Clearing caches (development mode)..."
    php artisan config:clear
    php artisan cache:clear 2>/dev/null || true
fi

# ── Run seeders only on fresh installs (skip tinker, use direct DB check) ────
if [ "$APP_ENV" != "production" ] && [ -z "$SKIP_SEEDING" ]; then
    # Check if users table has rows using php directly instead of artisan tinker
    USERS_EXISTS=$(php -r "
        try {
            \$pdo = new PDO('pgsql:host=\$_ENV[\"DB_HOST\"];port=\$_ENV[\"DB_PORT\"];dbname=\$_ENV[\"DB_DATABASE\"]', \$_ENV['DB_USERNAME'], \$_ENV['DB_PASSWORD']);
            \$stmt = \$pdo->query('SELECT COUNT(*) as cnt FROM users');
            echo \$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        } catch (Exception \$e) {
            echo '0';
        }
    " 2>/dev/null || echo "0")
    
    if [ "$USERS_EXISTS" = "0" ]; then
        echo "[entrypoint] Seeding database (no users found)..."
        php artisan db:seed --no-interaction --force 2>/dev/null || echo "[entrypoint] Seeding skipped or failed"
    else
        echo "[entrypoint] Database already seeded ($USERS_EXISTS users exist), skipping."
    fi
fi

# ── Start the requested command (default: php-fpm) ───────────────────────────
echo "[entrypoint] Starting: $*"
exec "$@"
