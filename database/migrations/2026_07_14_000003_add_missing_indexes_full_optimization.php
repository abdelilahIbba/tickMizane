<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Full schema indexing optimisation pass.
 *
 * Adds:
 *  – Composite indexes for every multi-column filter / join used in controllers
 *  – GIN full-text indexes (PostgreSQL) on searchable text fields
 *  – Drops genuinely redundant single-column indexes made obsolete by composites
 */
return new class extends Migration
{
    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function isPgsql(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }

    /** Create an index only if it does not already exist (idempotent). */
    private function createIndexIfMissing(string $table, string $name, string $ddl): void
    {
        if ($this->isPgsql()) {
            $exists = DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $name]
            );
            if (!$exists) {
                DB::statement($ddl);
            }
        }
        // For other drivers Blueprint handles IF NOT EXISTS implicitly via try/catch
    }

    /** Drop an index only if it exists (idempotent). */
    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->isPgsql()) {
            $exists = DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $name]
            );
            if ($exists) {
                DB::statement("DROP INDEX IF EXISTS {$name}");
            }
        }
    }

    // -----------------------------------------------------------------------
    // UP
    // -----------------------------------------------------------------------

    public function up(): void
    {
        // ===================================================================
        // 1. commandes – table_id FK had no index (high-frequency join)
        // ===================================================================
        Schema::table('commandes', function (Blueprint $table) {
            // table_id is queried on nearly every waiter/kitchen request
            if (!$this->isPgsql()) {
                $table->index('table_id', 'commandes_table_id_idx');
            }
        });

        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'commandes',
                'commandes_table_id_idx',
                'CREATE INDEX IF NOT EXISTS commandes_table_id_idx ON commandes (table_id)'
            );

            // Kitchen dashboard: type + status + created_at range scans
            $this->createIndexIfMissing(
                'commandes',
                'commandes_type_status_created_at_idx',
                'CREATE INDEX IF NOT EXISTS commandes_type_status_created_at_idx ON commandes (type, status, created_at DESC)'
            );

            // User-specific kitchen/waiter order list
            $this->createIndexIfMissing(
                'commandes',
                'commandes_user_type_status_idx',
                'CREATE INDEX IF NOT EXISTS commandes_user_type_status_idx ON commandes (user_id, type, status)'
            );
        } else {
            Schema::table('commandes', function (Blueprint $table) {
                $table->index(['type', 'status', 'created_at'], 'commandes_type_status_created_at_idx');
                $table->index(['user_id', 'type', 'status'], 'commandes_user_type_status_idx');
            });
        }

        // ===================================================================
        // 2. paiements – financial report queries
        // ===================================================================
        if ($this->isPgsql()) {
            // Cashier history: filter by user + date range
            $this->createIndexIfMissing(
                'paiements',
                'paiements_user_created_at_idx',
                'CREATE INDEX IF NOT EXISTS paiements_user_created_at_idx ON paiements (user_id, created_at DESC)'
            );

            // Finance report: filter by status + date range
            $this->createIndexIfMissing(
                'paiements',
                'paiements_status_created_at_idx',
                'CREATE INDEX IF NOT EXISTS paiements_status_created_at_idx ON paiements (status, created_at DESC)'
            );

            // Method distribution (payment methods chart)
            $this->createIndexIfMissing(
                'paiements',
                'paiements_method_created_at_idx',
                'CREATE INDEX IF NOT EXISTS paiements_method_created_at_idx ON paiements (method, created_at DESC)'
            );
        } else {
            Schema::table('paiements', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'paiements_user_created_at_idx');
                $table->index(['status', 'created_at'], 'paiements_status_created_at_idx');
                $table->index(['method', 'created_at'], 'paiements_method_created_at_idx');
            });
        }

        // ===================================================================
        // 3. stock_movements – product history queries
        // ===================================================================
        if ($this->isPgsql()) {
            // Product movement history ordered by date
            $this->createIndexIfMissing(
                'stock_movements',
                'stock_movements_produit_created_at_idx',
                'CREATE INDEX IF NOT EXISTS stock_movements_produit_created_at_idx ON stock_movements (produit_id, created_at DESC)'
            );

            // All movements of a type in a period (inventory reports)
            $this->createIndexIfMissing(
                'stock_movements',
                'stock_movements_type_created_at_idx',
                'CREATE INDEX IF NOT EXISTS stock_movements_type_created_at_idx ON stock_movements (type, created_at DESC)'
            );
        } else {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->index(['produit_id', 'created_at'], 'stock_movements_produit_created_at_idx');
                $table->index(['type', 'created_at'], 'stock_movements_type_created_at_idx');
            });
        }

        // ===================================================================
        // 4. produits – menu, low-stock, and kitchen-active queries
        // ===================================================================
        if ($this->isPgsql()) {
            // Menu rendering: active products by category (most common query)
            $this->createIndexIfMissing(
                'produits',
                'produits_status_category_kitchen_idx',
                'CREATE INDEX IF NOT EXISTS produits_status_category_kitchen_idx ON produits (status, category_id, kitchen_active)'
            );

            // Low-stock alert scans (status = active AND stock_quantity <= alert_stock)
            $this->createIndexIfMissing(
                'produits',
                'produits_status_stock_qty_idx',
                'CREATE INDEX IF NOT EXISTS produits_status_stock_qty_idx ON produits (status, stock_quantity)'
            );

            // Full-text search on product name (GIN for fast LIKE / @@)
            $this->createIndexIfMissing(
                'produits',
                'produits_name_fts_idx',
                "CREATE INDEX IF NOT EXISTS produits_name_fts_idx ON produits USING gin (to_tsvector('simple', name))"
            );
        } else {
            Schema::table('produits', function (Blueprint $table) {
                $table->index(['status', 'category_id', 'kitchen_active'], 'produits_status_category_kitchen_idx');
                $table->index(['status', 'stock_quantity'], 'produits_status_stock_qty_idx');
                // MySQL full-text
                $table->fullText('name', 'produits_name_fts_idx');
            });
        }

        // ===================================================================
        // 5. categories – full-text search
        // ===================================================================
        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'categories',
                'categories_name_fts_idx',
                "CREATE INDEX IF NOT EXISTS categories_name_fts_idx ON categories USING gin (to_tsvector('simple', name))"
            );
        } else {
            Schema::table('categories', function (Blueprint $table) {
                $table->fullText('name', 'categories_name_fts_idx');
            });
        }

        // ===================================================================
        // 6. fournisseurs – supplier search by name
        // ===================================================================
        Schema::table('fournisseurs', function (Blueprint $table) {
            if (!$this->isPgsql()) {
                $table->index('name', 'fournisseurs_name_idx');
                $table->fullText('name', 'fournisseurs_name_fts_idx');
            }
        });

        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'fournisseurs',
                'fournisseurs_name_idx',
                'CREATE INDEX IF NOT EXISTS fournisseurs_name_idx ON fournisseurs (name)'
            );
            $this->createIndexIfMissing(
                'fournisseurs',
                'fournisseurs_name_fts_idx',
                "CREATE INDEX IF NOT EXISTS fournisseurs_name_fts_idx ON fournisseurs USING gin (to_tsvector('simple', name))"
            );
        }

        // ===================================================================
        // 7. historiques – audit trail lookups
        // ===================================================================
        if ($this->isPgsql()) {
            // Record-level history (most common: "show changes for produit #42")
            $this->createIndexIfMissing(
                'historiques',
                'historiques_table_record_idx',
                'CREATE INDEX IF NOT EXISTS historiques_table_record_idx ON historiques (table_name, record_id)'
            );

            // User activity timeline
            $this->createIndexIfMissing(
                'historiques',
                'historiques_user_created_at_idx',
                'CREATE INDEX IF NOT EXISTS historiques_user_created_at_idx ON historiques (user_id, created_at DESC)'
            );

            // Action-level timeline (e.g., all password_changed events)
            $this->createIndexIfMissing(
                'historiques',
                'historiques_action_created_at_idx',
                'CREATE INDEX IF NOT EXISTS historiques_action_created_at_idx ON historiques (action, created_at DESC)'
            );
        } else {
            Schema::table('historiques', function (Blueprint $table) {
                $table->index(['table_name', 'record_id'], 'historiques_table_record_idx');
                $table->index(['user_id', 'created_at'], 'historiques_user_created_at_idx');
                $table->index(['action', 'created_at'], 'historiques_action_created_at_idx');
            });
        }

        // ===================================================================
        // 8. notifications – unread notification counts and polling
        // ===================================================================
        if ($this->isPgsql()) {
            // The primary query: "unread notifications for user X"
            // morphs() already creates notifiable_type + notifiable_id.
            // We extend it with read_at for WHERE read_at IS NULL filtering.
            $this->createIndexIfMissing(
                'notifications',
                'notifications_notifiable_read_at_idx',
                'CREATE INDEX IF NOT EXISTS notifications_notifiable_read_at_idx ON notifications (notifiable_type, notifiable_id, read_at)'
            );
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_at_idx');
            });
        }

        // ===================================================================
        // 9. jobs – queue worker dequeue performance
        // ===================================================================
        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'jobs',
                'jobs_queue_reserved_available_idx',
                'CREATE INDEX IF NOT EXISTS jobs_queue_reserved_available_idx ON jobs (queue, reserved_at, available_at)'
            );
        } else {
            Schema::table('jobs', function (Blueprint $table) {
                $table->index(['queue', 'reserved_at', 'available_at'], 'jobs_queue_reserved_available_idx');
            });
        }

        // ===================================================================
        // 10. ventes – user-level sales report (not covered by existing indexes)
        // ===================================================================
        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'ventes',
                'ventes_user_status_created_at_idx',
                'CREATE INDEX IF NOT EXISTS ventes_user_status_created_at_idx ON ventes (user_id, status, created_at DESC)'
            );

            // Payment method distribution over time (dashboard chart)
            $this->createIndexIfMissing(
                'ventes',
                'ventes_payment_method_status_idx',
                'CREATE INDEX IF NOT EXISTS ventes_payment_method_status_idx ON ventes (payment_method, status)'
            );
        } else {
            Schema::table('ventes', function (Blueprint $table) {
                $table->index(['user_id', 'status', 'created_at'], 'ventes_user_status_created_at_idx');
                $table->index(['payment_method', 'status'], 'ventes_payment_method_status_idx');
            });
        }

        // ===================================================================
        // 11. documentation – slug lookups and full-text content search
        // ===================================================================
        if ($this->isPgsql()) {
            $this->createIndexIfMissing(
                'documentation',
                'documentation_fts_idx',
                "CREATE INDEX IF NOT EXISTS documentation_fts_idx ON documentation USING gin (to_tsvector('simple', title || ' ' || coalesce(content, '')))"
            );
        } else {
            Schema::table('documentation', function (Blueprint $table) {
                $table->fullText(['title', 'content'], 'documentation_fts_idx');
            });
        }

        // ===================================================================
        // 12. Drop genuinely redundant single-column indexes now superseded
        //     by composites. Only drop when the composite covers the same
        //     leading column (PostgreSQL can use a composite for single-col).
        // ===================================================================
        if ($this->isPgsql()) {
            // ventes_status_index is superseded by ventes_status_created_at_idx
            $this->dropIndexIfExists('ventes', 'ventes_status_index');

            // historiques_action_index is superseded by historiques_action_created_at_idx
            $this->dropIndexIfExists('historiques', 'historiques_action_index');

            // vente_details_produit_id_index is superseded by vente_details_produit_vente_idx
            $this->dropIndexIfExists('vente_details', 'vente_details_produit_id_index');

            // stock_movements_type_index is superseded by stock_movements_type_created_at_idx
            $this->dropIndexIfExists('stock_movements', 'stock_movements_type_index');
        }
    }

    // -----------------------------------------------------------------------
    // DOWN
    // -----------------------------------------------------------------------

    public function down(): void
    {
        if ($this->isPgsql()) {
            $toDrop = [
                'commandes_table_id_idx',
                'commandes_type_status_created_at_idx',
                'commandes_user_type_status_idx',
                'paiements_user_created_at_idx',
                'paiements_status_created_at_idx',
                'paiements_method_created_at_idx',
                'stock_movements_produit_created_at_idx',
                'stock_movements_type_created_at_idx',
                'produits_status_category_kitchen_idx',
                'produits_status_stock_qty_idx',
                'produits_name_fts_idx',
                'categories_name_fts_idx',
                'fournisseurs_name_idx',
                'fournisseurs_name_fts_idx',
                'historiques_table_record_idx',
                'historiques_user_created_at_idx',
                'historiques_action_created_at_idx',
                'notifications_notifiable_read_at_idx',
                'jobs_queue_reserved_available_idx',
                'ventes_user_status_created_at_idx',
                'ventes_payment_method_status_idx',
                'documentation_fts_idx',
            ];

            foreach ($toDrop as $idx) {
                DB::statement("DROP INDEX IF EXISTS {$idx}");
            }
        } else {
            Schema::table('commandes', function (Blueprint $table) {
                $table->dropIndex('commandes_table_id_idx');
                $table->dropIndex('commandes_type_status_created_at_idx');
                $table->dropIndex('commandes_user_type_status_idx');
            });
            Schema::table('paiements', function (Blueprint $table) {
                $table->dropIndex('paiements_user_created_at_idx');
                $table->dropIndex('paiements_status_created_at_idx');
                $table->dropIndex('paiements_method_created_at_idx');
            });
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropIndex('stock_movements_produit_created_at_idx');
                $table->dropIndex('stock_movements_type_created_at_idx');
            });
            Schema::table('produits', function (Blueprint $table) {
                $table->dropIndex('produits_status_category_kitchen_idx');
                $table->dropIndex('produits_status_stock_qty_idx');
                $table->dropFullText('produits_name_fts_idx');
            });
            Schema::table('categories', function (Blueprint $table) {
                $table->dropFullText('categories_name_fts_idx');
            });
            Schema::table('fournisseurs', function (Blueprint $table) {
                $table->dropIndex('fournisseurs_name_idx');
                $table->dropFullText('fournisseurs_name_fts_idx');
            });
            Schema::table('historiques', function (Blueprint $table) {
                $table->dropIndex('historiques_table_record_idx');
                $table->dropIndex('historiques_user_created_at_idx');
                $table->dropIndex('historiques_action_created_at_idx');
            });
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_notifiable_read_at_idx');
            });
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropIndex('jobs_queue_reserved_available_idx');
            });
            Schema::table('ventes', function (Blueprint $table) {
                $table->dropIndex('ventes_user_status_created_at_idx');
                $table->dropIndex('ventes_payment_method_status_idx');
            });
            Schema::table('documentation', function (Blueprint $table) {
                $table->dropFullText('documentation_fts_idx');
            });
        }
    }
};
