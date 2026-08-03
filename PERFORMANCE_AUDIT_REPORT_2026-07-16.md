# TickMizane Full-System Performance Audit

Date: 2026-07-16
Scope: Laravel app, database/query patterns, cache, queue, middleware, Blade/frontend polling, Docker runtime

## Executive Priority Ranking (Impact x Effort)

| Rank | Issue | Impact | Effort | Priority Score | Area |
|---|---|---:|---:|---:|---|
| 1 | Database-backed session/cache/queue + no optimize caches at startup | 10 | 2 | 20 | Config/Infra |
| 2 | Permission matrix N+1 query explosion | 9 | 2 | 18 | DB/Controllers/Service |
| 3 | Aggressive polling and full page reload loops in kitchen/tables/waiter/cashier | 9 | 3 | 27 | Frontend + API |
| 4 | Kitchen polling endpoint returns both full JSON payload and server-rendered HTML every poll | 8 | 3 | 24 | Controller/View |
| 5 | POS and order creation loops execute per-item find queries and repeated writes | 8 | 3 | 24 | Controllers/Service |
| 6 | API reporting endpoints aggregate in PHP memory with wide fetches | 8 | 4 | 32 | API/DB |
| 7 | Heavy whereDate usage across reports and stats (weak index usage) | 7 | 3 | 21 | DB queries |
| 8 | Synchronous PDF generation on request path | 7 | 4 | 28 | Queue/Controller |
| 9 | Queue throughput constrained to a single worker process/service | 7 | 3 | 21 | Queue/Infra |
| 10 | Event constructor triggers eager relation loading in request thread | 6 | 2 | 12 | Events |
| 11 | Low-stock notification lookup repeated per stock operation | 6 | 3 | 18 | Service/Queue |
| 12 | Full dataset loads in selected controllers where pagination or projection is preferable | 5 | 2 | 10 | Query hygiene |

Note on score: used as ranking aid only. Estimated from production behavior patterns and code path frequency.

## Findings and Concrete Fixes

## 1) Database-backed session/cache/queue + no optimize caches

Location evidence:
- .env defaults: SESSION_DRIVER=database, CACHE_STORE=database, QUEUE_CONNECTION=database, BROADCAST_CONNECTION=log in [\.env.example](.env.example)
- Default config fallbacks to database in [config/cache.php](config/cache.php), [config/session.php](config/session.php), [config/queue.php](config/queue.php)
- Startup skips optimize caches in [docker/entrypoint.sh](docker/entrypoint.sh)

Root cause:
- Every request/session/cache operation adds avoidable database I/O.
- Laravel config/routes/views are not precompiled and cached on startup.

Fix:
1. Switch to Redis-backed runtime stores for session, cache, and queue.
2. Build Laravel caches during boot for production containers.

Concrete config change:
- Set environment values:
  - CACHE_STORE=redis
  - SESSION_DRIVER=redis
  - SESSION_CONNECTION=default
  - QUEUE_CONNECTION=redis
  - REDIS_HOST=redis
- In startup entrypoint, run on production-like profiles:
  - php artisan config:cache
  - php artisan route:cache
  - php artisan view:cache
  - php artisan event:cache

Expected gain:
- Lower DB load, lower tail latency, faster first request after deploy.

## 2) Permission matrix N+1 query pattern

Location evidence:
- Matrix generation loops in [app/Services/PermissionService.php](app/Services/PermissionService.php)
- Controller calls matrix in [app/Http/Controllers/Settings/PermissionManagementController.php](app/Http/Controllers/Settings/PermissionManagementController.php)
- Grid render in [resources/views/settings/permissions/show.blade.php](resources/views/settings/permissions/show.blade.php)

Root cause:
- getPermissionMatrix calls hasPermission per module-action cell.
- hasPermission performs one query per call when explicit permission absent.
- With 15 modules x 10 actions, one page load can trigger about 150 permission lookups.

Fix:
- Load all user permissions once, index in memory, compute matrix without additional queries.

Concrete code-level change:
- In PermissionService:
  - Fetch once: UserPermission::where(user_id)->get()->keyBy(module|action)
  - Replace per-cell hasPermission DB call with array lookup + default role fallback.

Expected gain:
- Permissions page query count collapses from O(modules x actions) to O(1) query set.

## 3) High-frequency polling and full page reload loops

Location evidence:
- 10s polling kitchen dashboard in [resources/views/kitchen/index.blade.php](resources/views/kitchen/index.blade.php)
- 5s polling kitchen display in [resources/views/kitchen/display.blade.php](resources/views/kitchen/display.blade.php)
- 30s full page reload waiter index in [resources/views/waiter/index.blade.php](resources/views/waiter/index.blade.php)
- 30s full page reload tables index in [resources/views/tables/index.blade.php](resources/views/tables/index.blade.php)
- 30s full page reload cashier pending in [resources/views/cashier/pending-orders.blade.php](resources/views/cashier/pending-orders.blade.php)

Root cause:
- Multiple screens refresh entire pages or large payloads frequently.
- This creates repeated full request lifecycle cost (middleware, Blade render, DB queries).

Fix:
- Replace full reload loops with lightweight JSON polling and DOM patching.
- Increase intervals where acceptable (for non-critical screens).
- Prefer websocket/broadcast for kitchen if enabling real-time channel later.

Concrete change guidance:
- Waiter/tables/cashier pages: replace window.location.reload with targeted fetch endpoint returning minimal counters/list deltas.
- Kitchen display: move from 5s to 10-15s polling if websocket not enabled, and request only needed fields.

Expected gain:
- Significant CPU and DB query reduction under multi-terminal usage.

## 4) Kitchen polling endpoint does double work per poll

Location evidence:
- JSON + server-rendered HTML returned every call in [app/Http/Controllers/KitchenController.php](app/Http/Controllers/KitchenController.php)
- Active orders endpoint used by kitchen polling in [resources/views/kitchen/index.blade.php](resources/views/kitchen/index.blade.php)

Root cause:
- Endpoint assembles full orders object and also renders Blade partial HTML every poll.
- High-frequency calls multiply rendering and serialization overhead.

Fix:
- Return either compact JSON only, or HTML only, not both.
- Prefer compact JSON with projected fields needed by the frontend card.

Concrete change guidance:
- In getActiveOrders:
  - Select required fields only.
  - Remove duplicate html rendering path or make it optional via query flag.

Expected gain:
- Lower response time and lower PHP CPU per poll.

## 5) POS/order loops cause per-item query overhead

Location evidence:
- Multiple Produit::find calls in loops in [app/Http/Controllers/PosController.php](app/Http/Controllers/PosController.php)
- Similar pattern in API POS controller in [app/Http/Controllers/Api/PosController.php](app/Http/Controllers/Api/PosController.php)
- Product lookups per item in kitchen order service in [app/Services/OrderService.php](app/Services/OrderService.php)

Root cause:
- Item processing repeatedly queries products by id in loops.
- This scales poorly with cart size and checkout volume.

Fix:
- Preload all referenced products with one whereIn query and map by id.
- Reuse mapped products for stock validation, totals, and detail creation.

Concrete change guidance:
- Build productMap once:
  - ids = collect(items)->pluck(product id)
  - products = Produit::whereIn(id, ids)->get()->keyBy(id)
- Replace per-loop find/findOrFail.

Expected gain:
- Checkout/order query count reduced from O(N) product queries to O(1).

## 6) API report endpoints aggregate in PHP memory

Location evidence:
- Wide get() + PHP-side sums/grouping in [app/Http/Controllers/Api/ReportController.php](app/Http/Controllers/Api/ReportController.php)

Root cause:
- Pulling large row sets into memory to aggregate increases latency and memory usage.

Fix:
- Push aggregates to SQL using selectRaw/groupBy/count/sum.
- Paginate or limit detailed reports.
- Add date-window defaults and caps on range size.

Concrete change guidance:
- Replace patterns like sales->sum/count in PHP with SQL aggregate queries.
- Keep only top N detail rows unless explicit export mode requested.

Expected gain:
- Strong reduction in memory and CPU for reporting APIs.

## 7) whereDate pattern reduces index efficiency

Location evidence:
- 56 whereDate usages across controllers/services/models from workspace scan
- Particularly frequent in [app/Http/Controllers/CashierPosController.php](app/Http/Controllers/CashierPosController.php), [app/Http/Controllers/KitchenController.php](app/Http/Controllers/KitchenController.php), [app/Http/Controllers/Api/ReportController.php](app/Http/Controllers/Api/ReportController.php)

Root cause:
- whereDate wraps timestamp column in function, often preventing straightforward index range scans.

Fix:
- Replace whereDate(column, date) with indexed range filters:
  - column >= startOfDay and column <= endOfDay

Expected gain:
- Better index usage and faster date-window queries.

## 8) Synchronous PDF generation on user request path

Location evidence:
- Kitchen ticket stream in [app/Http/Controllers/KitchenController.php](app/Http/Controllers/KitchenController.php)
- Cashier receipt/report generation in [app/Http/Controllers/CashierPosController.php](app/Http/Controllers/CashierPosController.php)

Root cause:
- DomPDF rendering is CPU-heavy and blocks request lifecycle.

Fix:
- Queue PDF generation and return downloadable when ready.
- Cache generated PDF files for short TTL for repeat downloads.

Important constraint respected:
- Payment/status logic remains unchanged. Only rendering path is async-optimized.

Expected gain:
- Lower p95 latency for cashier/kitchen workflows under load.

## 9) Queue worker throughput is limited

Location evidence:
- Single queue service and single worker command in [docker-compose.yml](docker-compose.yml)

Root cause:
- One worker process can become a bottleneck for queued notifications/events/PDF jobs.

Fix:
- Scale queue workers horizontally (multiple containers) or run multiple workers per container.
- Tune queue command for workload:
  - lower sleep for responsiveness
  - maintain timeout and retry settings

Expected gain:
- Better async backlog handling and reduced delay for queued tasks.

## 10) Broadcast event does synchronous relation loading

Location evidence:
- Constructor eager load in [app/Events/NewKitchenOrder.php](app/Events/NewKitchenOrder.php)

Root cause:
- Event constructor performs relation loading before queue handoff.

Fix:
- Pass scalar ids in constructor.
- Load data in broadcastWith or via dedicated lightweight payload query at broadcast time.

Expected gain:
- Lower synchronous DB work in request thread when emitting kitchen events.

## 11) Low-stock notification fanout in stock critical loops

Location evidence:
- Notification send and admin query in [app/Services/StockService.php](app/Services/StockService.php)
- Notification class in [app/Notifications/LowStockNotification.php](app/Notifications/LowStockNotification.php)

Root cause:
- checkLowStockAlert may run repeatedly during bulk/loop operations; admin lookup repeated.

Fix:
- Cache active admin ids for short TTL.
- Deduplicate notifications per product for window (for example per 10-30 minutes).

Expected gain:
- Fewer repeated notification jobs and fewer repeated admin lookup queries.

## 12) Full-load query patterns where projection/pagination should be enforced

Location evidence:
- Table dashboard full dataset load patterns in [app/Http/Controllers/TableController.php](app/Http/Controllers/TableController.php)
- API table map query pattern in [app/Http/Controllers/Api/TableController.php](app/Http/Controllers/Api/TableController.php)
- Legacy/duplicate supplier order paths in [app/Http/Controllers/CommandeController.php](app/Http/Controllers/CommandeController.php)

Root cause:
- Broad get() calls with full model columns and additional in-memory transforms.

Fix:
- Use select with required columns only.
- Paginate larger lists and date-history endpoints.
- Replace map with relation eager load and constrained subqueries.

Expected gain:
- Lower memory pressure and reduced transfer/render cost.

## Schema and Index Additions (Migration Required)

Current state:
- Many useful indexes already added in [database/migrations/2026_07_14_000003_add_missing_indexes_full_optimization.php](database/migrations/2026_07_14_000003_add_missing_indexes_full_optimization.php).

Still recommended additions:

1. Index for cashier/history queries on kitchen orders by updated_at and status
- Why: frequent filters on updated_at with payee status in cashier/kitchen history.
- Proposed PG index:
  - commandes(status, updated_at DESC) where type = 'kitchen'

2. Composite index for active tables dashboard filters
- Why: frequent active + status + zone filtering in table screens.
- Proposed index:
  - tables(is_active, status, zone)

Production migration safety:
- Use CREATE INDEX CONCURRENTLY on PostgreSQL for large tables.
- Run outside transaction for concurrent index operations.
- Schedule off-peak and monitor replication lag.
- No data backfill required; index-only migrations are low-risk when done concurrently.

## Middleware and Lifecycle Notes

Findings:
- Role and force password middleware are lightweight in current form:
  - [app/Http/Middleware/CheckRole.php](app/Http/Middleware/CheckRole.php)
  - [app/Http/Middleware/ForcePasswordReset.php](app/Http/Middleware/ForcePasswordReset.php)
- Permission middleware is defined but not currently applied on routes:
  - [app/Http/Middleware/CheckPermission.php](app/Http/Middleware/CheckPermission.php)

Optimization notes:
- No immediate middleware removal needed.
- If permission middleware is introduced at scale, add per-request permission cache (resolved once per user).

## Asset Delivery and Frontend Runtime Notes

Findings:
- Vite is used from Blade layout via @vite in [resources/views/components/layout/app.blade.php](resources/views/components/layout/app.blade.php).
- Nginx static caching and gzip are configured in [docker/nginx/conf.d/app.conf](docker/nginx/conf.d/app.conf).
- Current bottleneck is not asset minification; it is polling + full-page reload + backend endpoint work.

## Recommended Execution Plan

Phase 1 (Immediate, low risk, high gain)
1. Move cache/session/queue to Redis and enable Laravel optimize caches at startup.
2. Fix permission matrix N+1 by single-query permission preload.
3. Reduce polling cost: replace full reload loops with lightweight JSON refresh.
4. Refactor kitchen polling endpoint to return compact payload only.

Phase 2 (Medium effort)
1. Refactor checkout/order loops to bulk load product map.
2. Convert whereDate usage to timestamp range filters.
3. SQL-first rewrite for API reports and add pagination/range caps.

Phase 3 (Higher effort)
1. Queue PDF generation and cache outputs.
2. Scale queue workers and monitor throughput.
3. Add targeted indexes with concurrent migration strategy.

## Guardrails for Known Divergent Paths

As requested, no payment/status transition logic changes are applied here.
The following paths must remain behavior-identical unless explicitly approved:
- Vente payment path
- Commande kitchen payment path

Related files:
- [app/Http/Controllers/CashierPosController.php](app/Http/Controllers/CashierPosController.php)
- [app/Services/PaymentService.php](app/Services/PaymentService.php)
- [app/Services/OrderService.php](app/Services/OrderService.php)

## Final Summary

The biggest wins are architectural/runtime (Redis stores + cache compilation), query-shape fixes (permission matrix and cart loops), and polling minimization. These changes preserve business behavior while making the platform substantially lighter under concurrent restaurant operations.
