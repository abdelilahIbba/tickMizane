#!/bin/bash
set -e

echo "🚀 Starting TechMizane container..."

# Ensure Laravel public storage link is valid.
# If a real directory exists at public/storage, move its content into
# storage/app/public before recreating the proper symlink.
if [ -d public/storage ] && [ ! -L public/storage ]; then
	echo "🛠️  Fixing public/storage (directory -> symlink)..."
	mkdir -p storage/app/public
	cp -a public/storage/. storage/app/public/ 2>/dev/null || true
	rm -rf public/storage
fi

echo "🔗 Ensuring storage symlink..."
php artisan storage:link --force

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force

# Build Laravel caches for performance (skip for now to avoid Redis errors during startup)
# Caching will happen on first request if needed

echo "✅ Container started successfully"

# Keep container running
exec "$@"
