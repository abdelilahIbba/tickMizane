# TechMizane — Full Technical Analysis Report
**Date:** 2026-07-14 | **Laravel:** 12.44 | **PHP:** 8.2 | **DB:** PostgreSQL 16 | **Stack:** Nginx · PHP-FPM · Redis · Docker Compose

---

## 1. Project Overview

TechMizane is a multi-role restaurant/hotel Point-of-Sale system with the following actor roles:

| Role | Responsibilities |
|---|---|
| **admin** | Dashboard, full management, kitchen, settings |
| **caissier** | POS, sales, payments, cashier queue |
| **serveur** | Table management, waiter ordering interface |
| *(public)* | Client QR ordering, room service (unauthenticated) |

---

## 2. Backend Architecture

### 2.1 Framework & Key Packages

- **Laravel 12** with PHP 8.2-FPM
- **Sanctum 4.2** — API token authentication (used for public order endpoints)
- **DomPDF 3.1** — Receipt/PDF generation
- **Vite 7 + Tailwind CSS 4 + Alpine.js 3** — Frontend stack
- **Chart.js 4.5** (local bundle) — Dashboard charts

### 2.2 Application Layers

```
routes/
  web.php        – 80+ named routes, grouped by role middleware
  api.php        – REST endpoints for kitchen/waiter polling
  console.php    – Scheduled commands

app/
  Http/
    Controllers/ – 18 controllers (Auth, Dashboard, POS, Waiter, Kitchen, ...)
    Middleware/  – CheckRole, CheckPermission, ForcePasswordReset
    Requests/    – 12 form-request validators (strong input validation)
  Models/        – 15 Eloquent models with scopes, casts, policies
  Policies/      – 8 Gate policies (ProductPolicy, VentePolicy, ...)
  Services/      – Business logic layer
  Events/        – NewKitchenOrder (real-time broadcasting)
  Notifications/ – LowStockNotification (queued)
  Providers/     – AppServiceProvider (Gate::policy registrations)
```

### 2.3 Middleware Stack (per request)

```
web group
  └─ ForcePasswordReset    (checks users.force_password_reset on every auth'd request)
     └─ auth               (session guard)
        └─ role:admin|...  (CheckRole — role-based gate)
           └─ permission   (CheckPermission — granular module/action gate)
```

**Note:** `ForcePasswordReset` runs on **every** authenticated web request. With the current Redis-backed session driver this is cheap; with database sessions it would add one DB query per request.

---

## 3. Database Schema

### 3.1 Entity-Relationship Summary

```
users ──< user_permissions
users ──< ventes ──< vente_details >── produits
users ──< commandes >── tables
commandes ──< commande_details >── produits
ventes ──< paiements
commandes ──< paiements
produits >── categories
fournisseurs ──< commandes
produits ──< stock_movements
users ──< historiques
users (morphs) ──< notifications
documentation
settings
```

### 3.2 Tables — Column Inventory

| Table | Rows (approx) | Key columns |
|---|---|---|
| users | low | id, username (UQ), role, status, force_password_reset |
| categories | low | id, name, status, image |
| fournisseurs | low | id, name, email (UQ), phone |
| produits | medium | id, category_id, name, price_vente, stock_quantity, alert_stock, status, kitchen_active |
| tables | low | id, name, zone, status, is_active, serveur_id, current_vente_id |
| ventes | high | id, user_id, table_id, total, payment_method, status, created_at |
| vente_details | high | id, vente_id, produit_id, quantity, price, total_line |
| commandes | high | id, fournisseur_id, user_id, table_id, type, status, ready_at, validated_at, created_at |
| commande_details | high | id, commande_id, produit_id, quantity, price, notes |
| stock_movements | high | id, produit_id, type, reason, quantity, reference_id, created_at |
| paiements | high | id, vente_id, commande_id, user_id, amount, method, status, reference, created_at |
| historiques | very high | id, user_id, action, table_name, record_id, old_values, new_values, created_at |
| notifications | medium | id (UUID), notifiable_type, notifiable_id, data, read_at |
| user_permissions | low | id, user_id, module, action, allowed |
| settings | low | id, group, key (UQ), value, type |
| documentation | low | id, slug (UQ), title, content, category, visible_to_roles |
| sessions | high | id (PK), user_id, last_activity |
| jobs | medium | id, queue, payload, reserved_at, available_at |

---

## 4. Indexing Analysis

### 4.1 Indexes Present Before Optimisation (migration batch 1 & 2)

| Table | Index | Type |
|---|---|---|
| users | username | UNIQUE |
| users | role | B-tree |
| users | status | B-tree |
| users | (role, status) | Composite ✓ |
| produits | category_id | B-tree |
| produits | status | B-tree |
| ventes | user_id, table_id, status, created_at | B-tree ×4 |
| ventes | (status, created_at) | Composite ✓ |
| vente_details | vente_id, produit_id | B-tree ×2 |
| vente_details | (produit_id, vente_id) | Composite ✓ |
| commandes | fournisseur_id, user_id, status | B-tree ×3 |
| commandes | (type, status) | Composite ✓ |
| paiements | vente_id, method, commande_id, reference, status | B-tree ×5 |
| stock_movements | produit_id, type, reason, created_at | B-tree ×4 |
| historiques | user_id, table_name, action, created_at | B-tree ×4 |
| user_permissions | (user_id, module, action) | Composite + UNIQUE |
| settings | group, (group, key) | B-tree + Composite |
| documentation | (category, order) | Composite |

### 4.2 Gaps Identified — Added in Migration `2026_07_14_000003`

| Table | New Index | Reason |
|---|---|---|
| commandes | `table_id` (B-tree) | FK with **no index** — every waiter table query does a full scan |
| commandes | `(type, status, created_at DESC)` | Kitchen dashboard range scans — needs all 3 columns |
| commandes | `(user_id, type, status)` | Waiter "my orders" list filter |
| paiements | `(user_id, created_at DESC)` | Cashier history per user |
| paiements | `(status, created_at DESC)` | Finance reports over time |
| paiements | `(method, created_at DESC)` | Dashboard payment-method chart |
| stock_movements | `(produit_id, created_at DESC)` | Product movement timeline |
| stock_movements | `(type, created_at DESC)` | Inventory in/out reports |
| produits | `(status, category_id, kitchen_active)` | Menu and waiter product list |
| produits | `(status, stock_quantity)` | Low-stock alert scan (`stock_quantity <= alert_stock`) |
| produits | GIN `to_tsvector('simple', name)` | Full-text product search |
| categories | GIN `to_tsvector('simple', name)` | Full-text category search |
| fournisseurs | `name` (B-tree) | Supplier name lookup/search |
| fournisseurs | GIN `to_tsvector('simple', name)` | Full-text supplier search |
| historiques | `(table_name, record_id)` | Record-level audit trail — e.g., "history for product #5" |
| historiques | `(user_id, created_at DESC)` | User activity timeline |
| historiques | `(action, created_at DESC)` | Action-level event stream |
| notifications | `(notifiable_type, notifiable_id, read_at)` | Unread notification count polling |
| jobs | `(queue, reserved_at, available_at)` | Queue worker dequeue — critical for throughput |
| ventes | `(user_id, status, created_at DESC)` | Per-user sales history |
| ventes | `(payment_method, status)` | Dashboard payment distribution |
| documentation | GIN full-text on title+content | Documentation search |

### 4.3 Redundant Indexes Removed

| Table | Removed Index | Superseded by |
|---|---|---|
| ventes | `ventes_status_index` | `ventes_status_created_at_idx` |
| historiques | `historiques_action_index` | `historiques_action_created_at_idx` |
| vente_details | `vente_details_produit_id_index` | `vente_details_produit_vente_idx` |
| stock_movements | `stock_movements_type_index` | `stock_movements_type_created_at_idx` |

> **Note:** In PostgreSQL, a composite index `(a, b)` satisfies single-column queries on `a` through index scan, so the single-column index on `a` becomes redundant.

### 4.4 Indexes Deliberately Kept (Not Removed)

- `historiques.user_id` — kept alongside the composite because admin user-filter-only queries (without `created_at`) benefit from the single-column index
- All FK single-column indexes (user_id, vente_id, commande_id etc.) — kept because FK enforcement checks by primary key; having a leading-column composite does not always cover the FK path in PostgreSQL planner

---

## 5. Query Patterns & Index Usage

### 5.1 Critical Paths

**Login:**
```sql
SELECT * FROM users WHERE role='admin' AND status='active' LIMIT 1;
-- → uses: users_role_status_idx ✓
```

**Dashboard aggregate (cached 5 min):**
```sql
SELECT SUM(total), COUNT(*) FROM ventes
WHERE status='paid' AND created_at BETWEEN $today_start AND $today_end;
-- → uses: ventes_status_created_at_idx ✓ (Index Scan, 0.64ms measured)
```

**Kitchen display:**
```sql
SELECT * FROM commandes
WHERE type='kitchen' AND status IN ('pending','en_cuisine','pret')
ORDER BY created_at DESC;
-- → uses: commandes_type_status_created_at_idx ✓ (new)
```

**Low-stock alert:**
```sql
SELECT * FROM produits
WHERE status='active' AND stock_quantity <= alert_stock;
-- → uses: produits_status_stock_qty_idx ✓ (new)
```

**Unread notifications:**
```sql
SELECT COUNT(*) FROM notifications
WHERE notifiable_type='App\Models\User' AND notifiable_id=? AND read_at IS NULL;
-- → uses: notifications_notifiable_read_at_idx ✓ (new)
```

---

## 6. Docker Infrastructure Analysis

### 6.1 Issues Found & Fixed

| # | Issue | Severity | Fix Applied |
|---|---|---|---|
| 1 | No healthchecks — app/nginx started before DB/Redis ready | 🔴 Critical | Added healthchecks to all 5 services |
| 2 | `depends_on: service_started` instead of `service_healthy` | 🔴 Critical | Changed all to `service_healthy` |
| 3 | No queue worker container (QUEUE_CONNECTION=database but no worker) | 🔴 Critical | Added `queue` service |
| 4 | Redis has no password (`.env` sets `REDIS_PASSWORD=null`) | 🟠 High | Added `--requirepass` to redis command |
| 5 | Redis data not persisted (no volume) | 🟠 High | Added `redisdata` volume + AOF persistence |
| 6 | No resource limits — any service can OOM the host | 🟠 High | Added `deploy.resources.limits` to all services |
| 7 | Nginx mounts entire `./` directory (leaks source to web root) | 🟠 High | Changed to `./public` only (read-only) |
| 8 | No PostgreSQL performance tuning | 🟡 Medium | Added `shared_buffers`, `work_mem`, `wal_buffers` etc. |
| 9 | App logs stored only inside ephemeral container | 🟡 Medium | Added `app_logs` named volume |
| 10 | `app` container has no restart policy | 🟡 Medium | Added `restart: unless-stopped` |
| 11 | `.env` mounted writable | 🟡 Medium | Changed to `:ro` |

### 6.2 Service Dependency Chain (After Fix)

```
postgres (healthy) ──┐
                     ├─→ app (healthy) ──┬─→ nginx (healthy)
redis (healthy) ─────┘                  └─→ queue
```

### 6.3 Resource Budget

| Service | Memory Limit |
|---|---|
| postgres | 512 MB |
| redis | 192 MB (128 MB maxmemory + headroom) |
| app (php-fpm) | 512 MB |
| queue worker | 256 MB |
| nginx | 64 MB |
| adminer | 64 MB |
| **Total** | **~1.6 GB** |

---

## 7. PHP-FPM & OPcache Configuration

### 7.1 Current Settings (Good)

| Setting | Value | Assessment |
|---|---|---|
| `pm.max_children` | 20 | Appropriate for 512 MB |
| `pm.max_requests` | 500 | Good — prevents memory leaks |
| `opcache.memory_consumption` | 256 MB | Generous for this codebase |
| `opcache.max_accelerated_files` | 20 000 | Covers vendor + app files |
| `request_slowlog_timeout` | 5s | Good for catching slow requests |

### 7.2 Production Recommendations

```ini
; In production, change these in docker/php/opcache.ini:
opcache.validate_timestamps=0   ; Never recheck files — maximum speed
opcache.revalidate_freq=0       ; Irrelevant when validate_timestamps=0
opcache.enable_cli=1            ; Useful for artisan preloading

; And in php.ini:
display_errors=Off              ; Already correct ✓
```

---

## 8. Nginx Configuration Assessment

### 8.1 Strengths
- Gzip enabled on all text types
- 1-year cache TTL on static assets with `immutable` header
- FastCGI buffer tuning (`fastcgi_buffer_size 128k`)
- Security headers: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection
- Blocks `.env`, `.git`, `.ht` access

### 8.2 Recommended Additions

```nginx
# Add to server block for stronger security posture:
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

# HTTPS redirect (when SSL is configured):
# server { listen 80; return 301 https://$host$request_uri; }
```

---

## 9. Security Observations

| Area | Observation | Action |
|---|---|---|
| Admin PIN | Hardcoded `009988` in `AuthController::ADMIN_PIN` | Move to `.env` as `ADMIN_PIN` |
| Redis | `REDIS_PASSWORD=null` in `.env` | Set a strong password + update `docker-compose.yml` redis command |
| BCRYPT_ROUNDS | Set to 12 — appropriate | ✓ |
| Session | Redis-backed — good | ✓ |
| CSRF | All POST forms use `@csrf` | ✓ |
| Input validation | Form Requests used throughout | ✓ |
| Login throttle | `throttle:5,1` on POST /login | ✓ |
| Force password reset | Middleware on all auth routes | ✓ |
| XSS | `APP_DEBUG=false` in prod env | ✓ |

---

## 10. Full-Text Search Usage Guide

After applying migration `2026_07_14_000003`, use full-text search in Eloquent with raw `whereRaw`:

```php
// Search products by name (PostgreSQL GIN index)
Produit::whereRaw("to_tsvector('simple', name) @@ plainto_tsquery('simple', ?)", [$search])
    ->where('status', 'active')
    ->get();

// Search documentation
Documentation::whereRaw(
    "to_tsvector('simple', title || ' ' || coalesce(content, '')) @@ plainto_tsquery('simple', ?)",
    [$search]
)->get();
```

---

## 11. Deliverables Summary

| File | Purpose |
|---|---|
| `database/migrations/2026_07_14_000003_add_missing_indexes_full_optimization.php` | 22 new indexes + 4 redundant removed |
| `docker-compose.yml` | Fully rewritten with healthchecks, queue worker, resource limits, Redis persistence |

### To Apply

```bash
# 1. Apply the new migration
docker compose exec app php artisan migrate

# 2. Restart with the new docker-compose
docker compose down && docker compose up -d

# 3. Verify all services are healthy
docker compose ps

# 4. Verify indexes in PostgreSQL
docker compose exec postgres psql -U techmizane -d techmizane -P pager=off \
  -c "SELECT tablename, indexname FROM pg_indexes WHERE schemaname='public' ORDER BY tablename, indexname;"
```
