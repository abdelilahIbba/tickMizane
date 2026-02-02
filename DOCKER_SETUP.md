# Docker Setup Guide for Laravel Project

## Requirements
- Docker Desktop
- Docker Compose

## Quick Start

1. **Build the Docker images:**
   ```bash
   docker-compose build
   ```

2. **Start the containers:**
   ```bash
   docker-compose up -d
   ```

3. **Generate the app key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

4. **Run migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

5. **Access your application:**
   - App: http://localhost
   - MySQL: localhost:3306
   - Redis: localhost:6379

## Common Commands

### View logs
```bash
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Execute artisan commands
```bash
docker-compose exec app php artisan [command]
```

### Stop containers
```bash
docker-compose down
```

### Stop and remove volumes
```bash
docker-compose down -v
```

### Build fresh
```bash
docker-compose build --no-cache
```

### Access container shell
```bash
docker-compose exec app bash
```

### Install packages
```bash
# PHP packages
docker-compose exec app composer install

# Node packages
docker-compose exec app npm install
```

## Environment Configuration

Update your `.env` file with:
```
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
```

## Services

- **PHP-FPM 8.2**: Application server
- **Nginx**: Web server
- **MySQL 8.0**: Database
- **Redis**: Cache and queue driver

## Ports

- HTTP: 80
- HTTPS: 443
- MySQL: 3306
- Redis: 6379
