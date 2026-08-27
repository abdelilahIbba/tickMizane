# Client Update Guide After Pull

This guide explains how to deploy the latest product import and product delete fixes after pulling from `main`.

## 1. Pull Latest Code

```bash
git checkout main
git pull origin main
```

## 2. Update Backend Dependencies

```bash
composer install --no-interaction --prefer-dist
```

Important: this release requires `shuchkin/simplexls` for legacy `.xls` import support.

## 3. Update Frontend Dependencies and Build Assets

```bash
npm install
npm run build
```

## 4. Run Database and Cache Maintenance

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan storage:link --force
```

## 5. If Using Docker (Recommended)

```bash
docker compose up -d --build
docker compose exec app composer install --no-interaction --prefer-dist
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan storage:link --force
```

## 6. Run Validation Tests

Product import and duplicate skip behavior:

```bash
php artisan test tests/Feature/Products/ProductImportTest.php
```

Product delete protection behavior:

```bash
php artisan test tests/Feature/Products/ProductDeleteTest.php
```

Docker equivalent:

```bash
docker compose exec -T app php artisan test tests/Feature/Products/ProductImportTest.php
docker compose exec -T app php artisan test tests/Feature/Products/ProductDeleteTest.php
```

## 7. What This Release Changes

- Product import from Excel now:
  - creates categories automatically when missing,
  - creates products with default stock quantity = `100`,
  - skips already existing products automatically,
  - reports created and skipped counts in the UI.

- Product delete now:
  - allows deletion when product is only linked to unpaid/cancelled sales,
  - blocks deletion when linked to paid sales history.

## 8. Versioning Recommendation For Client Release

This repository does not use a dedicated runtime `APP_VERSION` variable by default.
Use git tags + changelog for client version tracking:

```bash
# Example
git tag -a v1.1.0 -m "Product import duplicate-skip + delete guard fix"
git push origin v1.1.0
```

Also add release notes under `CHANGELOG.md` before publishing to client environments.
