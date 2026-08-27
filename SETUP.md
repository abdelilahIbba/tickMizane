# TechMizane — Client Setup & Installation Guide

Welcome to **TechMizane**, a comprehensive Point-of-Sale (POS) and management platform for restaurant, hotel, and hospitality operations.

This document provides a **full project analysis** and **step-by-step setup instructions from scratch** for deploying the application on client servers, local POS terminals, or development environments.

---

## 1. Project Analysis & System Architecture

### 1.1 Technical Stack Overview

| Component | Technology | Description |
|---|---|---|
| **Framework** | Laravel 12.x | High-performance PHP web framework |
| **Runtime** | PHP 8.2+ | Server-side script interpreter |
| **Database** | PostgreSQL 16 | Primary relational database (or MySQL / SQLite for local) |
| **Cache & Queue** | Redis 7+ | Session storage, queue processing, and system caching |
| **Frontend Stack** | Vite 7, Tailwind CSS 4, Alpine.js | Reactive, lightweight UI rendering engine |
| **PDF Engine** | DomPDF 3.1 | Receipt, ticket, and report PDF generation |
| **Web Server** | Nginx / Apache | High-concurrency reverse proxy & web server |
| **Containers** | Docker & Docker Compose | Containerized application orchestration |

### 1.2 Multi-Role Surface Overview

TechMizane supports 5 distinct operational surfaces:

1. **Super Admin / Admin (`/admin`, `/dashboard`)**: Catalog management, stock procurement, user roles, financial reports, system configuration.
2. **Cashier POS (`/cashier`, `/pos`)**: High-speed sales checkout, ticket split/merge, payment processing (Cash, Card, Room Charge), receipt PDF printing.
3. **Waiter Terminal (`/waiter`)**: Mobile/tablet interface for table status management, taking orders, and kitchen ticket dispatching.
4. **Kitchen Display System - KDS (`/kitchen`)**: Real-time kitchen prep pipeline with live order statuses (`en_preparation`, `pret`, `servi`).
5. **Client QR Self-Service (`/order`)**: Public mobile-optimized ordering interface for restaurant tables, pool, or room service.

---

## 2. System & Dependency Requirements

### 2.1 Hardware Requirements

- **Minimum**: 2 CPU Cores, 2 GB RAM, 10 GB Storage.
- **Recommended**: 4 CPU Cores, 4 GB RAM, 20 GB SSD Storage.

### 2.2 Software Prerequisites

#### Option A: Docker Deployment (Recommended)
- **Docker Engine**: 24.0+
- **Docker Compose**: 2.20+
- **Git**: 2.30+

#### Option B: Native Deployment (Bare-Metal Windows / Linux)
- **PHP**: 8.2 or 8.3 with extensions: `pdo_pgsql` (or `pdo_mysql`), `mbstring`, `gd`, `xml`, `curl`, `zip`, `bcmath`, `exif`.
- **Composer**: 2.6+
- **Node.js**: 18.0+ & **npm**: 9.0+
- **Database**: PostgreSQL 16 (or MySQL 8.0+ / SQLite 3)
- **Git**: 2.30+

---

## 3. Quick Start: Setup From Scratch (One-Command Setup)

For fast deployment from a freshly cloned repository, use the automated setup scripts.

### On Windows (Command Prompt / PowerShell)
Run the automated Windows setup script:
```cmd
.\setup.bat
```

### On Linux / macOS (Terminal)
Run the automated Shell setup script:
```bash
chmod +x setup.sh && ./setup.sh
```

### Via Composer (Native Environment)
```bash
composer run setup
```

---

## 4. Method 1: Docker Compose Setup (Step-by-Step)

Docker Compose provides a reproducible, containerized environment including PHP-FPM, Nginx, PostgreSQL 16, Redis, and a Queue Worker.

### Step 1: Clone Repository
```bash
git clone https://github.com/abdelilahIbba/tickMizane.git techMizane
cd techMizane
```

### Step 2: Prepare Environment File
Copy `.env.docker` to `.env`:
```bash
# On Linux/macOS
cp .env.docker .env

# On Windows (PowerShell)
Copy-Item .env.docker .env
```

> [!TIP]
> Inspect `.env` to verify your target database credentials and application settings:
> ```env
> DB_CONNECTION=pgsql
> DB_HOST=postgres
> DB_PORT=5432
> DB_DATABASE=techmizane
> DB_USERNAME=techmizane
> DB_PASSWORD=secret
> APP_PORT=8000
> ```

### Step 3: Build & Start Docker Containers
```bash
docker compose up -d --build
```
*This launches 6 services:*
- `laravel_app` (PHP-FPM 8.2 app server)
- `laravel_postgres` (PostgreSQL 16 DB)
- `laravel_redis` (Redis cache & session)
- `laravel_nginx` (Web proxy on port 8000 & 8443)
- `laravel_queue` (Background queue worker)
- `laravel_adminer` (Database UI on port 8081)

### Step 4: Verify Container Health
```bash
docker compose ps
```
Ensure all services show status `healthy` or `running`.

### Step 5: Initialize Application (Key, Migrations & Seeds)
Run the initialization commands inside the app container:

```bash
# 1. Generate Application Key
docker compose exec app php artisan key:generate

# 2. Run Database Migrations & Performance Indexes
docker compose exec app php artisan migrate --force

# 3. Seed Initial Demo Data & User Credentials
docker compose exec app php artisan db:seed --force

# 4. Create Storage Symlink
docker compose exec app php artisan storage:link --force

# 5. Clear & Rebuild Application Caches
docker compose exec app php artisan optimize:clear
```

### Step 6: Install Frontend Dependencies & Build Assets
Run Vite asset compilation:
```bash
# Install npm dependencies on host or container
npm install

# Build compiled CSS/JS assets
npm run build
```

---

## 5. Method 2: Native Bare-Metal Setup (Step-by-Step)

If installing directly on a machine with PHP, PostgreSQL/MySQL, and Node.js:

### Step 1: Clone Repository & Enter Directory
```bash
git clone https://github.com/abdelilahIbba/tickMizane.git techMizane
cd techMizane
```

### Step 2: Install PHP Dependencies
```bash
composer install --optimize-autoloader --no-interaction
```

### Step 3: Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Edit `.env` and set your database connection details:
```env
APP_NAME=techMizane
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=techmizane
DB_USERNAME=postgres
DB_PASSWORD=your_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

SUPER_ADMIN_ENABLED=true
SUPER_ADMIN_USERNAME=devnapp
SUPER_ADMIN_PIN=009988
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5: Execute Database Migrations & Seeders
Ensure your database (`techmizane`) has been created in PostgreSQL/MySQL, then run:
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

### Step 6: Install Frontend Packages & Compile Assets
```bash
npm install
npm run build
```

### Step 7: Launch Local Web Server & Queue Worker
In primary terminal:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
In a second terminal (for background jobs/notifications):
```bash
php artisan queue:work --tries=3
```

---

## 6. Accessing the Application & Initial Credentials

Once installed, open your web browser at: **`http://localhost:8000`** (or your server's IP address).

### 6.1 Super Admin Access (Synthetic Account)
- **Login URL**: `http://localhost:8000/login`
- **Username**: `devnapp`
- **PIN**: `009988` *(configured via `SUPER_ADMIN_PIN` in `.env`)*

### 6.2 Seeded Staff Accounts (Generated by `db:seed`)
When running `php artisan db:seed`, temporary 8-digit PINs are dynamically generated and displayed in your terminal output:

| Username | Role | Default Access | Description |
|---|---|---|---|
| `admin` / `omar` / `hisham` | `admin` | Full back-office | System control, catalog, reports |
| `caissier1` / `caissier2` | `caissier` | POS Checkout | Sales, cash register, receipt printing |
| `serveur1` / `serveur2` | `serveur` | Waiter Terminal | Table orders & kitchen dispatch |

> [!IMPORTANT]
> Upon first login with staff accounts, the system prompts for a **Password/PIN Reset** (`force_password_reset = true`) to ensure security compliance on client machines.

---

## 7. Local LAN Setup for Waiter Tablets & Kitchen Displays

To allow Waiter tablets and Kitchen screens on the same Wi-Fi / Local Network to connect to the POS server:

### 7.1 Find Server IP Address
- **Windows**: Open Command Prompt and run `ipconfig` (e.g., `192.168.1.50`).
- **Linux/macOS**: Run `hostname -I` or `ifconfig`.

### 7.2 Open Windows Firewall Ports
If running on Windows host, open port `8000` (HTTP) and `8443` (HTTPS) by running `open-firewall-admin.bat` as Administrator, or execute in PowerShell:
```powershell
netsh advfirewall firewall add rule name="TechMizane POS 8000" dir=in action=allow protocol=TCP localport=8000
```

### 7.3 LAN Device Access URLs
- **Waiter Tablet**: `http://<SERVER_IP>:8000/waiter`
- **Kitchen Display**: `http://<SERVER_IP>:8000/kitchen`
- **Client QR Menu**: `http://<SERVER_IP>:8000/order`

---

## 8. Operational Verification & Testing

Verify that the system is fully operational by executing automated test suites:

### In Docker Environment
```bash
docker compose exec app php artisan test
```

### In Native Environment
```bash
php artisan test
```

Expected result: All unit and feature tests should pass cleanly (Auth, POS Checkout, Cashier Reporting, Kitchen Pipeline, Client Ordering, System Settings).

---

## 9. Troubleshooting & FAQ

### Issue 1: Database Connection Refused
- **Docker**: Verify PostgreSQL container is healthy (`docker compose ps`). Wait 15-20 seconds on first boot for DB initialization.
- **Native**: Ensure PostgreSQL service is running on host (`sudo systemctl status postgresql` or Windows Services).

### Issue 2: Storage Permission Error (`storage/logs/laravel.log` cannot be opened)
Run permission fix commands:
```bash
# On Linux/Docker host
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue 3: Kitchen Orders Not Updating in Real-time
Ensure the queue worker is running:
```bash
# Docker
docker compose logs -f queue

# Native
php artisan queue:listen
```

### Issue 4: Resetting Database to Clean State
To wipe all data and start completely fresh:
```bash
# Docker
docker compose exec app php artisan migrate:fresh --seed

# Native
php artisan migrate:fresh --seed
```

---

## 10. Summary Command Cheatsheet

```bash
# Full Docker Fresh Boot
docker compose down -v
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:force --seed
npm install && npm run build

# Check Logs
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f postgres
```
