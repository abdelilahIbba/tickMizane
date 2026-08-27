@echo off
setlocal enabledelayedexpansion

echo ========================================================
echo   TechMizane POS - Automated Setup From Scratch
echo ========================================================
echo.

:: Check if Docker is available
docker --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Docker detected. Initializing Docker Compose environment...
    echo.
    
    if not exist .env (
        echo [1/6] Copying .env.docker to .env...
        copy .env.docker .env
    ) else (
        echo [1/6] .env file already exists.
    )

    echo [2/6] Starting Docker containers...
    docker compose up -d --build

    echo [3/6] Generating Application Key...
    docker compose exec app php artisan key:generate

    echo [4/6] Running Database Migrations & Indexes...
    docker compose exec app php artisan migrate --force

    echo [5/6] Seeding Initial Database & User Credentials...
    docker compose exec app php artisan db:seed --force

    echo [6/6] Linking Storage & Building Assets...
    docker compose exec app php artisan storage:link --force
    
    where npm >nul 2>&1
    if %errorlevel% equ 0 (
        call npm install
        call npm run build
    )

    echo.
    echo ========================================================
    echo   [OK] TechMizane Docker Setup Complete!
    echo ========================================================
    echo  App URL:      http://localhost:8000
    echo  Adminer UI:   http://localhost:8081
    echo  Super Admin:  Username: devnapp | PIN: 009988
    echo ========================================================
    goto :end
)

:: Native PHP environment fallback
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Docker not found. Falling back to Native PHP environment...
    echo.

    if not exist .env (
        echo [1/6] Copying .env.example to .env...
        copy .env.example .env
    ) else (
        echo [1/6] .env file already exists.
    )

    echo [2/6] Installing PHP dependencies via Composer...
    call composer install --no-interaction

    echo [3/6] Generating Application Key...
    call php artisan key:generate

    echo [4/6] Running Database Migrations...
    call php artisan migrate --force

    echo [5/6] Seeding Initial Database...
    call php artisan db:seed --force

    echo [6/6] Linking Storage & Building Assets...
    call php artisan storage:link --force

    where npm >nul 2>&1
    if %errorlevel% equ 0 (
        call npm install
        call npm run build
    )

    echo.
    echo ========================================================
    echo   [OK] TechMizane Native Setup Complete!
    echo ========================================================
    echo  Run "php artisan serve" to start the local web server.
    echo  App URL: http://localhost:8000
    echo ========================================================
    goto :end
)

echo [ERROR] Neither Docker nor PHP could be found!
echo Please install Docker Desktop or PHP 8.2+ and Composer first.
pause
exit /b 1

:end
pause
