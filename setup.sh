#!/usr/bin/env bash

set -e

echo "========================================================"
echo "  TechMizane POS - Automated Setup From Scratch"
echo "========================================================"
echo ""

# Check if Docker is available
if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    echo "[INFO] Docker detected. Initializing Docker Compose environment..."
    echo ""

    if [ ! -f .env ]; then
        echo "[1/6] Copying .env.docker to .env..."
        cp .env.docker .env
    else
        echo "[1/6] .env file already exists."
    fi

    echo "[2/6] Starting Docker containers..."
    docker compose up -d --build

    echo "[3/6] Generating Application Key..."
    docker compose exec app php artisan key:generate

    echo "[4/6] Running Database Migrations & Indexes..."
    docker compose exec app php artisan migrate --force

    echo "[5/6] Seeding Initial Database & User Credentials..."
    docker compose exec app php artisan db:seed --force

    echo "[6/6] Linking Storage & Building Assets..."
    docker compose exec app php artisan storage:link --force

    if command -v npm >/dev/null 2>&1; then
        npm install
        npm run build
    fi

    echo ""
    echo "========================================================"
    echo "  [OK] TechMizane Docker Setup Complete!"
    echo "========================================================"
    echo " App URL:      http://localhost:8000"
    echo " Adminer UI:   http://localhost:8081"
    echo " Super Admin:  Username: devnapp | PIN: 009988"
    echo "========================================================"
    exit 0
fi

# Native PHP environment fallback
if command -v php >/dev/null 2>&1; then
    echo "[INFO] Docker not found or not running. Falling back to Native PHP environment..."
    echo ""

    if [ ! -f .env ]; then
        echo "[1/6] Copying .env.example to .env..."
        cp .env.example .env
    else
        echo "[1/6] .env file already exists."
    fi

    echo "[2/6] Installing PHP dependencies via Composer..."
    composer install --no-interaction

    echo "[3/6] Generating Application Key..."
    php artisan key:generate

    echo "[4/6] Running Database Migrations..."
    php artisan migrate --force

    echo "[5/6] Seeding Initial Database..."
    php artisan db:seed --force

    echo "[6/6] Linking Storage & Building Assets..."
    php artisan storage:link --force

    if command -v npm >/dev/null 2>&1; then
        npm install
        npm run build
    fi

    echo ""
    echo "========================================================"
    echo "  [OK] TechMizane Native Setup Complete!"
    echo "========================================================"
    echo " Run 'php artisan serve' to start the local web server."
    echo " App URL: http://localhost:8000"
    echo "========================================================"
    exit 0
fi

echo "[ERROR] Neither Docker nor PHP could be found!"
echo "Please install Docker or PHP 8.2+ and Composer first."
exit 1
