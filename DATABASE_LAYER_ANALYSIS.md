# TechMizane POS - Database Layer Analysis Report

**Generated:** February 3, 2026  
**Scope:** Migrations, Models, and Audit Trail

---

## 📋 Executive Summary

| Area          | Status                | Issues Found  |
|---------------|-----------------------|---------------|
| Migrations    | ⚠️ Needs Improvement  | 12 issues     |
| Models        | ⚠️ Needs Improvement  | 15 issues     |
| Audit Trail   | ✅ Good               | 3 minor issues|

---

## 1. MIGRATIONS ANALYSIS

### 1.1 Missing Indexes on Frequently Queried Columns

| Table | Column | Reason | Priority |
|-------|--------|--------|----------|
| `produits` | `name` | Product search is common | 🔴 High |
| `users` | `username` | Already unique, good ✅ | - |
| `categories` | `name` | Category filtering | 🟡 Medium |
| `fournisseurs` | `name` | Supplier search | 🟡 Medium |
| `commandes` | `created_at` | Date-based queries | 🟡 Medium |
| `stock_movements` | `reference_id` | Polymorphic reference lookups | 🟡 Medium |
| `historiques` | `record_id` | Lookup by record | 🔴 High |

**File References:**
- [2026_01_01_000003_create_produits_table.php](database/migrations/2026_01_01_000003_create_produits_table.php#L13-L27) - Missing `name` index
- [2026_01_01_000011_create_historiques_table.php](database/migrations/2026_01_01_000011_create_historiques_table.php#L13-L28) - Missing `record_id` index

**Recommended Migration:**
```php
// Add missing indexes
Schema::table('produits', function (Blueprint $table) {
    $table->index('name');
});

Schema::table('historiques', function (Blueprint $table) {
    $table->index('record_id');
    $table->index(['table_name', 'record_id']); // Composite for record lookups
});

Schema::table('commandes', function (Blueprint $table) {
    $table->index('created_at');
});

Schema::table('stock_movements', function (Blueprint $table) {
    $table->index(['reason', 'reference_id']); // Composite for polymorphic lookups
});
```

---

### 1.2 Foreign Key Constraints Consistency

| Issue | Table | Details | File Reference |
|-------|-------|---------|----------------|
| ⚠️ Inconsistent ON DELETE | `stock_movements` | `reference_id` has no FK constraint (polymorphic) | [Line 18](database/migrations/2026_01_01_000009_create_stock_movements_table.php#L18) |
| ⚠️ Missing FK | `tables.current_vente_id` | Uses `nullOnDelete()` - OK ✅ | - |
| ⚠️ Cascade concern | `produits` | `onDelete('cascade')` on category - risky for products | [Line 15](database/migrations/2026_01_01_000003_create_produits_table.php#L15) |
| ⚠️ Cascade concern | `ventes` | User deletion cascades - should use `set null` for audit | [Line 15](database/migrations/2026_01_01_000005_create_ventes_table.php#L15) |

**Recommendations:**
1. Change `produits.category_id` from `cascade` to `restrict` to prevent accidental product deletion
2. Change `ventes.user_id` to `set null` with nullable column for audit integrity
3. Consider adding polymorphic foreign key validation for `stock_movements.reference_id`

---

### 1.3 Missing Fields

#### 1.3.1 Missing Timestamp Fields

| Table | Missing Field | Purpose | Priority |
|-------|---------------|---------|----------|
| `commandes` | `served_at` | Track when order was served | 🔴 High |
| `commandes` | `paid_at` | Track when payment was completed | 🔴 High |
| `commandes` | `cancelled_at` | Track cancellation time | 🟡 Medium |
| `ventes` | `paid_at` | Track payment completion time | 🔴 High |
| `ventes` | `cancelled_at` | Track cancellation time | 🟡 Medium |
| `tables` | `released_at` | Track when table was freed | 🟡 Medium |
| `users` | `password_changed_at` | Security audit | 🟡 Medium |

**File References:**
- [Commande model](app/Models/Commande.php#L26-L35) - Has `validated_at`, `ready_at` but missing `served_at`, `paid_at`
- [Vente model](app/Models/Vente.php#L26-L31) - Missing `paid_at`

#### 1.3.2 Missing Status/Tracking Fields

| Table | Missing Field | Purpose | Priority |
|-------|---------------|---------|----------|
| `fournisseurs` | `status` | Active/inactive supplier status | 🔴 High |
| `commande_details` | `status` | Per-item preparation status for kitchen | 🟡 Medium |
| `paiements` | `status` | Payment status (pending/confirmed/failed) | 🟡 Medium |
| `paiements` | `reference` | Transaction reference number | 🔴 High |
| `vente_details` | `discount` | Per-line discount | 🟡 Medium |
| `ventes` | `discount_total` | Total discount applied | 🟡 Medium |

---

### 1.4 Soft Deletes Implementation

**Current Status:** ❌ No soft deletes implemented

| Model | Should Have Soft Deletes | Priority | Reason |
|-------|-------------------------|----------|--------|
| `User` | ✅ Yes | 🔴 High | Audit trail preservation |
| `Produit` | ✅ Yes | 🔴 High | Historical sales reference |
| `Category` | ✅ Yes | 🟡 Medium | Already has `status` field (alternative approach) |
| `Fournisseur` | ✅ Yes | 🔴 High | Purchase history reference |
| `Vente` | ✅ Yes | 🔴 High | Financial records integrity |
| `Commande` | ✅ Yes | 🔴 High | Order history integrity |
| `Paiement` | ✅ Yes | 🔴 High | Payment records integrity |
| `Table` | ⚡ Optional | 🟢 Low | Has `is_active` field (alternative approach) |

**Recommended Migration:**
```php
// Add soft deletes to critical tables
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('produits', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('fournisseurs', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('ventes', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('commandes', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('paiements', function (Blueprint $table) {
    $table->softDeletes();
});
```

---

## 2. MODELS ANALYSIS

### 2.1 Relationship Definitions Completeness

#### 2.1.1 Missing Relationships

| Model | Missing Relationship | Type | File Reference |
|-------|---------------------|------|----------------|
| `User` | `assignedTables` | HasMany | [User.php#L56-L88](app/Models/User.php#L56-L88) |
| `User` | `kitchenOrders` | HasMany (filtered) | [User.php](app/Models/User.php) |
| `Commande` | `serveur` (alias) | BelongsTo (via table) | [Commande.php#L52-L82](app/Models/Commande.php#L52-L82) |
| `Produit` | `activeVentes` | HasManyThrough | [Produit.php](app/Models/Produit.php) |
| `Vente` | `commande` | HasOne | [Vente.php](app/Models/Vente.php) - kitchen orders link |
| `Table` | `commandes` | HasMany | [Table.php](app/Models/Table.php) |
| `StockMovement` | `user` | BelongsTo | [StockMovement.php](app/Models/StockMovement.php) - who made adjustment |

**Recommendations for [User.php](app/Models/User.php):**
```php
// Add missing relationships
public function assignedTables(): HasMany
{
    return $this->hasMany(Table::class, 'serveur_id');
}

public function kitchenOrders(): HasMany
{
    return $this->hasMany(Commande::class)->where('type', 'kitchen');
}

public function supplierOrders(): HasMany
{
    return $this->hasMany(Commande::class)->where('type', 'supplier');
}
```

**Recommendations for [Table.php](app/Models/Table.php):**
```php
public function commandes(): HasMany
{
    return $this->hasMany(Commande::class);
}

public function activeCommandes(): HasMany
{
    return $this->hasMany(Commande::class)
        ->whereIn('status', ['en_cuisine', 'en_preparation', 'pret']);
}
```

---

### 2.2 Missing Fillable/Guarded Properties

| Model | Issue | Details |
|-------|-------|---------|
| `User` | ⚠️ Missing guarded | Should guard `id`, `remember_token` explicitly or use `$guarded = []` with caution |
| `Historique` | ✅ Good | Complete fillable array |
| `UserPermission` | ✅ Good | Complete fillable array |

**All models use `$fillable` approach - this is consistent and good.**

---

### 2.3 Missing Casts

| Model | Column | Should Cast To | File Reference |
|-------|--------|---------------|----------------|
| `Category` | `status` | Enum (Laravel 11+) or string | [Category.php#L34-L40](app/Models/Category.php#L34-L40) |
| `Produit` | `status` | Enum or string | [Produit.php#L37-L49](app/Models/Produit.php#L37-L49) |
| `Commande` | `status` | Enum | [Commande.php#L40-L49](app/Models/Commande.php#L40-L49) |
| `Commande` | `type` | Enum | [Commande.php#L40-L49](app/Models/Commande.php#L40-L49) |
| `Vente` | `status` | Enum | [Vente.php#L37-L43](app/Models/Vente.php#L37-L43) |
| `Vente` | `payment_method` | Enum | [Vente.php#L37-L43](app/Models/Vente.php#L37-L43) |
| `Paiement` | `method` | Enum | [Paiement.php#L33-L40](app/Models/Paiement.php#L33-L40) |
| `StockMovement` | `type` | Enum | [StockMovement.php#L37-L44](app/Models/StockMovement.php#L37-L44) |
| `StockMovement` | `reason` | Enum | [StockMovement.php#L37-L44](app/Models/StockMovement.php#L37-L44) |
| `User` | `status` | Enum | [User.php#L42-L49](app/Models/User.php#L42-L49) |
| `User` | `role` | Enum | [User.php#L42-L49](app/Models/User.php#L42-L49) |

**Recommendation (Laravel 11+):**
```php
// In Commande.php casts()
protected function casts(): array
{
    return [
        'total' => 'decimal:2',
        'status' => \App\Enums\CommandeStatus::class,
        'type' => \App\Enums\CommandeType::class,
        'validated_at' => 'datetime',
        'ready_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

---

### 2.4 Accessor/Mutator Needs

| Model | Accessor Needed | Purpose | Priority |
|-------|-----------------|---------|----------|
| `Produit` | `formatted_price_vente` | Display formatted price with currency | 🟡 Medium |
| `Produit` | `profit_margin` | Calculate margin between purchase and sale | 🟡 Medium |
| `Vente` | `formatted_total` | Display formatted total with currency | 🟡 Medium |
| `Vente` | `items_count` | Total number of items | 🟡 Medium |
| `Commande` | `items_count` | Total number of items | 🟡 Medium |
| `Commande` | `wait_time` | Time since order creation | 🔴 High |
| `Table` | `status_badge` | HTML/class for status display | 🟡 Medium |
| `User` | `full_role_name` | Translated role name | 🟡 Medium |

**Recommendations for [Commande.php](app/Models/Commande.php):**
```php
// Add wait time accessor (critical for kitchen display)
public function getWaitTimeAttribute(): string
{
    $minutes = $this->created_at->diffInMinutes(now());
    
    if ($minutes < 60) {
        return $minutes . ' min';
    }
    
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    return "{$hours}h {$remainingMinutes}min";
}

public function getWaitTimeMinutesAttribute(): int
{
    return $this->created_at->diffInMinutes(now());
}

public function getItemsCountAttribute(): int
{
    return $this->details->sum('quantity');
}

public function getFormattedTotalAttribute(): string
{
    return number_format($this->total, 2) . ' DH';
}
```

**Recommendations for [Produit.php](app/Models/Produit.php):**
```php
public function getFormattedPriceVenteAttribute(): string
{
    return number_format($this->price_vente, 2) . ' DH';
}

public function getProfitMarginAttribute(): ?float
{
    if (!$this->price_achat || $this->price_achat == 0) {
        return null;
    }
    return (($this->price_vente - $this->price_achat) / $this->price_vente) * 100;
}
```

---

### 2.5 Missing Scope Methods

| Model | Scope Needed | Purpose | Priority |
|-------|--------------|---------|----------|
| `Produit` | `scopeInStock` | Products with stock > 0 | 🔴 High |
| `Produit` | `scopeOutOfStock` | Products with stock = 0 | 🔴 High |
| `Produit` | `scopeSearch($query, $term)` | Search by name | 🔴 High |
| `Produit` | `scopeInCategory($query, $categoryId)` | Filter by category | 🟡 Medium |
| `Commande` | `scopeCreatedToday` | Today's orders | 🟡 Medium |
| `Commande` | `scopeCreatedBetween($start, $end)` | Date range filter | 🔴 High |
| `Commande` | `scopeForTable($tableId)` | Orders for specific table | 🔴 High |
| `Vente` | `scopeCreatedBetween($start, $end)` | Date range filter | 🔴 High |
| `Vente` | `scopeByPaymentMethod($method)` | Filter by payment method | 🟡 Medium |
| `User` | `scopeActive` | Only active users | 🔴 High |
| `User` | `scopeByRole($role)` | Filter by role | 🔴 High |
| `StockMovement` | `scopeCreatedBetween($start, $end)` | Date range for reports | 🔴 High |
| `Historique` | `scopeForRecord($table, $id)` | Get history for specific record | 🔴 High |

**Recommendations for [Produit.php](app/Models/Produit.php):**
```php
public function scopeInStock($query)
{
    return $query->where('stock_quantity', '>', 0);
}

public function scopeOutOfStock($query)
{
    return $query->where('stock_quantity', '<=', 0);
}

public function scopeSearch($query, string $term)
{
    return $query->where('name', 'like', "%{$term}%");
}

public function scopeInCategory($query, int $categoryId)
{
    return $query->where('category_id', $categoryId);
}
```

**Recommendations for [User.php](app/Models/User.php):**
```php
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

public function scopeByRole($query, string $role)
{
    return $query->where('role', $role);
}

public function scopeBlocked($query)
{
    return $query->where('status', 'blocked');
}
```

**Recommendations for [Historique.php](app/Models/Historique.php):**
```php
public function scopeForRecord($query, string $tableName, int $recordId)
{
    return $query->where('table_name', $tableName)
                 ->where('record_id', $recordId);
}

public function scopeCreatedBetween($query, $start, $end)
{
    return $query->whereBetween('created_at', [$start, $end]);
}
```

---

## 3. AUDIT TRAIL (HISTORIQUE) ANALYSIS

### 3.1 Models Using LogsHistorique Trait

| Model | Uses Trait | Status |
|-------|------------|--------|
| `Category` | ✅ Yes | Logged |
| `Commande` | ✅ Yes | Logged |
| `CommandeDetail` | ✅ Yes | Logged |
| `Fournisseur` | ✅ Yes | Logged |
| `Paiement` | ✅ Yes | Logged |
| `Produit` | ✅ Yes | Logged |
| `StockMovement` | ✅ Yes | Logged |
| `Table` | ✅ Yes | Logged |
| `UserPermission` | ✅ Yes | Logged |
| `Vente` | ✅ Yes | Logged |
| `VenteDetail` | ✅ Yes | Logged |
| `User` | ❌ No | **NOT LOGGED** |
| `Historique` | ❌ No | Should not self-log |

### 3.2 Critical Missing: User Model Not Logging

**Issue:** [User.php](app/Models/User.php) does NOT use the `LogsHistorique` trait!

**Impact:** User creation, updates, and deletions are not automatically logged to the historique table.

**Note:** The `UserService` manually logs custom actions, but automatic model events (create/update/delete) are not captured.

**Recommendation:** Add `LogsHistorique` trait to User model:
```php
// User.php
use App\Traits\LogsHistorique;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, LogsHistorique;
    // ...
}
```

### 3.3 Custom Actions Logged

| Action | Service | Description |
|--------|---------|-------------|
| `user_created` | UserService | ✅ Logged |
| `user_updated` | UserService | ✅ Logged |
| `password_reset` | UserService | ✅ Logged |
| `password_changed` | UserService | ✅ Logged |
| `user_activated` | UserService | ✅ Logged |
| `user_deactivated` | UserService | ✅ Logged |
| `user_deleted` | UserService | ✅ Logged |
| `permission_changed` | PermissionService | ✅ Logged |
| `permission_removed` | PermissionService | ✅ Logged |
| `refund` | PaymentService | ✅ Logged |
| `cancel` | PaymentService | ✅ Logged |
| `receive` | OrderService | ✅ Logged |
| `status_change` | OrderService | ✅ Logged |

### 3.4 Missing Audit Actions

| Action | Should Be Logged | Priority |
|--------|-----------------|----------|
| Login attempt (success/fail) | ✅ Yes | 🔴 High |
| Logout | ✅ Yes | 🟡 Medium |
| Price changes on products | ✅ Yes (already via update) | - |
| Stock adjustments | ✅ Yes (already logged) | - |
| Table assignments | ✅ Yes (already via Table update) | - |
| Report generation | ⚡ Optional | 🟢 Low |
| Export data | ⚡ Optional | 🟡 Medium |

**Recommendation:** Add login/logout logging in authentication controllers:
```php
// In LoginController or similar
Historique::create([
    'user_id' => $user->id,
    'role' => $user->role,
    'action' => 'login',
    'table_name' => 'users',
    'record_id' => $user->id,
    'description' => 'Connexion utilisateur',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'device_type' => $this->detectDeviceType(request()->userAgent()),
]);
```

---

## 4. PRIORITY RECOMMENDATIONS SUMMARY

### 🔴 Critical (Implement Immediately)

1. **Add soft deletes** to `User`, `Produit`, `Fournisseur`, `Vente`, `Commande`, `Paiement`
2. **Add `LogsHistorique` trait** to User model
3. **Add missing indexes** on `produits.name`, `historiques.record_id`
4. **Add `served_at` and `paid_at`** timestamp fields to `commandes`
5. **Add `status` field** to `fournisseurs` table
6. **Add login/logout audit logging**

### 🟡 Medium (Implement Soon)

1. Add enum casts to all status/type columns
2. Add missing scope methods (`scopeActive`, `scopeByRole`, etc.)
3. Add `paid_at` to `ventes`
4. Add `reference` field to `paiements`
5. Add missing relationships (User → assignedTables, Table → commandes)
6. Change `produits.category_id` FK from cascade to restrict

### 🟢 Low (Nice to Have)

1. Add accessor methods for formatted values
2. Add additional scopes for reporting
3. Consider adding `cancelled_at` timestamps
4. Add `released_at` to tables

---

## 5. SUGGESTED NEW MIGRATION FILE

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft deletes
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('produits', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('name');
        });
        
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->softDeletes();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->index('status');
        });
        
        Schema::table('ventes', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });
        
        Schema::table('commandes', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->index('created_at');
        });
        
        Schema::table('paiements', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'failed', 'refunded'])->default('confirmed');
            $table->index('status');
        });
        
        // 2. Add missing indexes
        Schema::table('historiques', function (Blueprint $table) {
            $table->index('record_id');
            $table->index(['table_name', 'record_id']);
        });
        
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['reason', 'reference_id']);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
        
        // 3. Add per-item status for kitchen workflow
        Schema::table('commande_details', function (Blueprint $table) {
            $table->enum('status', ['pending', 'preparing', 'ready'])->default('pending');
            $table->index('status');
        });
    }

    public function down(): void
    {
        // Reverse all changes
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('produits', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['name']);
        });
        
        // ... etc
    }
};
```

---

## 6. FILES REQUIRING MODEL UPDATES

| File | Changes Needed |
|------|---------------|
| [app/Models/User.php](app/Models/User.php) | Add LogsHistorique trait, SoftDeletes trait, new relationships, scopes |
| [app/Models/Produit.php](app/Models/Produit.php) | Add SoftDeletes trait, new scopes, accessors |
| [app/Models/Fournisseur.php](app/Models/Fournisseur.php) | Add SoftDeletes trait, status field in fillable |
| [app/Models/Vente.php](app/Models/Vente.php) | Add SoftDeletes trait, paid_at in casts/fillable |
| [app/Models/Commande.php](app/Models/Commande.php) | Add SoftDeletes trait, new timestamp fields, accessors |
| [app/Models/Paiement.php](app/Models/Paiement.php) | Add SoftDeletes trait, reference and status in fillable |
| [app/Models/Table.php](app/Models/Table.php) | Add commandes relationship |
| [app/Models/StockMovement.php](app/Models/StockMovement.php) | Add user relationship |
| [app/Models/Historique.php](app/Models/Historique.php) | Add scopeForRecord |

---

**End of Analysis Report**
