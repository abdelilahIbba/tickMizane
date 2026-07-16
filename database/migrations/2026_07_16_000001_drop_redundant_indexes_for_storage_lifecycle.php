<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DROP INDEX CONCURRENTLY cannot run inside a transaction.
     */
    public $withinTransaction = false;

    private function isPgsql(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->isPgsql()) {
            return;
        }

        $indexesToDrop = [
            'commandes_type_status_index',
            'paiements_method_index',
            'paiements_status_index',
            'historiques_user_id_index',
            'historiques_table_name_index',
            'jobs_queue_index',
            'notifications_notifiable_type_notifiable_id_index',
            'settings_group_index',
            'user_permissions_user_id_module_action_index',
        ];

        foreach ($indexesToDrop as $indexName) {
            DB::statement("DROP INDEX CONCURRENTLY IF EXISTS {$indexName}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->isPgsql()) {
            return;
        }

        $recreateStatements = [
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS commandes_type_status_index ON commandes (type, status)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS paiements_method_index ON paiements (method)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS paiements_status_index ON paiements (status)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS historiques_user_id_index ON historiques (user_id)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS historiques_table_name_index ON historiques (table_name)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS jobs_queue_index ON jobs (queue)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS notifications_notifiable_type_notifiable_id_index ON notifications (notifiable_type, notifiable_id)',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS settings_group_index ON settings ("group")',
            'CREATE INDEX CONCURRENTLY IF NOT EXISTS user_permissions_user_id_module_action_index ON user_permissions (user_id, module, action)',
        ];

        foreach ($recreateStatements as $statement) {
            DB::statement($statement);
        }
    }
};
