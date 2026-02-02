# Waiter & Kitchen System - Quick Setup Script
# Run this in PowerShell from project root

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Waiter Tablet & Kitchen Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if composer is available
Write-Host "[1/5] Checking dependencies..." -ForegroundColor Yellow
if (!(Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Composer not found! Please install Composer first." -ForegroundColor Red
    exit 1
}

# Install PDF library if needed
Write-Host "[2/5] Installing PDF library..." -ForegroundColor Yellow
composer require barryvdh/laravel-dompdf --no-interaction

# Run migrations
Write-Host "[3/5] Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force

# Optional: Fresh install with seed
$seed = Read-Host "Do you want to seed test data? (y/n)"
if ($seed -eq "y" -or $seed -eq "Y") {
    Write-Host "[4/5] Seeding database..." -ForegroundColor Yellow
    php artisan db:seed --force
} else {
    Write-Host "[4/5] Skipping seed..." -ForegroundColor Yellow
}

# Clear caches
Write-Host "[5/5] Clearing caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  ✅ Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "📱 Waiter Interface: http://localhost:8000/waiter" -ForegroundColor Cyan
Write-Host "🍳 Kitchen Dashboard: http://localhost:8000/kitchen" -ForegroundColor Cyan
Write-Host ""
Write-Host "Test Accounts:" -ForegroundColor Yellow
Write-Host "  Waiter 1: serveur1 / serveur123" -ForegroundColor White
Write-Host "  Waiter 2: serveur2 / serveur123" -ForegroundColor White
Write-Host "  Admin (Kitchen): admin / 009988" -ForegroundColor White
Write-Host ""
Write-Host "📖 Full documentation: WAITER_KITCHEN_IMPLEMENTATION.md" -ForegroundColor Cyan
Write-Host ""
