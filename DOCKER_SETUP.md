# Docker Architecture & Operations Guide

This guide details the containerized Docker setup for **TechMizane**.

## Services Architecture

The `docker-compose.yml` file defines 6 orchestration services:

| Service | Image | Description | Internal Port | Exposed Port |
|---|---|---|---|---|
| `postgres` | `postgres:16-alpine` | PostgreSQL 16 DB | 5432 | 5432 |
| `redis` | `redis:alpine` | Session, Cache & Queue | 6379 | 6379 |
| `app` | Custom `Dockerfile` | PHP 8.2-FPM App Server | 9000 | - |
| `queue` | Custom `Dockerfile` | Laravel Queue Worker | - | - |
| `nginx` | `nginx:alpine` | Web Reverse Proxy | 80 / 443 | 8000 / 8443 |
| `adminer` | `adminer:latest` | Database UI | 8080 | 8081 |

---

## Quick Start Commands

### 1. Build and Launch Containers
```bash
docker compose up -d --build
```

### 2. Run Database Migrations & Seeds
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link --force
```

### 3. Check Container Health
```bash
docker compose ps
```

---

## Common Docker Operational Commands

### View Service Logs
```bash
# View app logs
docker compose logs -f app

# View web server logs
docker compose logs -f nginx

# View queue worker logs
docker compose logs -f queue

# View database logs
docker compose logs -f postgres
```

### Execute Artisan Commands
```bash
docker compose exec app php artisan [command]
```

### Access Application Shell
```bash
docker compose exec app bash
```

### Run Integration Tests
```bash
docker compose exec app php artisan test
```

### Stop & Restart Services
```bash
# Stop containers
docker compose down

# Stop and wipe volume data (Fresh Reset)
docker compose down -v
```

---

## Environment Configuration (`.env.docker`)

Ensure your `.env` file uses container service names:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=techmizane
DB_USERNAME=techmizane
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=database
CACHE_STORE=database
```
