#!/bin/bash
set -e

echo "🚀 Starting TechMizane container..."

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force

# Build Laravel caches for performance (skip for now to avoid Redis errors during startup)
# Caching will happen on first request if needed

echo "✅ Container started successfully"

# Keep container running
exec "$@"
