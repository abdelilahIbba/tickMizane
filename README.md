# TechMizane — Point-of-Sale & Hospitality Management Platform

**TechMizane** is a modern, high-performance Point-of-Sale (POS) and management platform designed for restaurants, hotels, pools, and hospitality establishments.

---

## 🚀 Quick Setup From Scratch (1-Command Install)

To set up TechMizane from a clean repository clone:

### On Windows
```cmd
.\setup.bat
```

### On Linux / macOS
```bash
chmod +x setup.sh && ./setup.sh
```

### Via Composer
```bash
composer run setup
```

📖 **For detailed installation options, network configuration, and production deployment, read [SETUP.md](file:///c:/Users/T490s%20Ha/Desktop/devnapp%20prod/techMizane/techMizane/SETUP.md).**

---

## 📌 Features & System Overview

- **Multi-Role Surfaces**:
  - 👑 **Admin Dashboard (`/admin`)**: Menu catalog, stock procurement, user permissions, audit trail, financial analytics.
  - 💳 **Cashier POS (`/pos`, `/cashier`)**: High-speed sales checkout, split payments, ticket printouts.
  - 📱 **Waiter Terminal (`/waiter`)**: Mobile/tablet order entry, real-time table status management.
  - 🍳 **Kitchen Display (`/kitchen`)**: Real-time kitchen ticket prep queue.
  - 📲 **Client QR Order (`/order`)**: Self-service QR code ordering for tables, rooms, and pool areas.

- **Robust Architecture**:
  - **Framework**: Laravel 12.x on PHP 8.2+
  - **Database**: PostgreSQL 16 (optimized indexes & full-text search)
  - **Cache & Queues**: Redis 7+
  - **Frontend**: Blade, Vite 7, Tailwind CSS 4, Alpine.js, Chart.js
  - **Documents**: DomPDF ticket and receipt printing

---

## 🛠️ Docker Architecture & Services

The containerized stack configured in `docker-compose.yml` includes:

- `laravel_app`: PHP 8.2-FPM application engine.
- `laravel_postgres`: PostgreSQL 16 database.
- `laravel_redis`: Redis session, cache & job queue.
- `laravel_nginx`: High-performance Nginx web server (ports `8000` / `8443`).
- `laravel_queue`: Dedicated Laravel queue worker for asynchronous jobs.
- `laravel_adminer`: Lightweight database management tool (port `8081`).

---

## 🧪 Testing & Verification

Run the full integration test suite:

```bash
# Docker Environment
docker compose exec app php artisan test

# Native Environment
php artisan test
```

---

## 📚 Documentation Directory

- 📗 [SETUP.md](file:///c:/Users/T490s%20Ha/Desktop/devnapp%20prod/techMizane/techMizane/SETUP.md) — Comprehensive Client Setup & Production Installation Guide.
- 🐳 [DOCKER_SETUP.md](file:///c:/Users/T490s%20Ha/Desktop/devnapp%20prod/techMizane/techMizane/DOCKER_SETUP.md) — Containerized Architecture & Operations Guide.
- 📋 [DEVELOPER_ONBOARDING_REPORT.md](file:///c:/Users/T490s%20Ha/Desktop/devnapp%20prod/techMizane/techMizane/DEVELOPER_ONBOARDING_REPORT.md) — Developer Architecture & Code Base Deep Dive.
- 📊 [TECHNICAL_ANALYSIS_REPORT.md](file:///c:/Users/T490s%20Ha/Desktop/devnapp%20prod/techMizane/techMizane/TECHNICAL_ANALYSIS_REPORT.md) — Database Indexing & Performance Report.
