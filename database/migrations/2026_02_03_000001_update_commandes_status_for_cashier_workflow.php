<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Updates the status column to include:
     * - en_cuisine: Order sent to kitchen by waiter
     * - pret: Kitchen marked order as ready
     * - payee: Cashier processed payment
     */
    public function up(): void
    {
        // Migrate data: rename old value for consistency
        DB::table('commandes')
            ->where('status', 'en_preparation')
            ->where('type', 'kitchen')
            ->update(['status' => 'en_cuisine']);

        // Expand the allowed values for the status column
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL: drop old CHECK constraint and add updated one
            DB::statement('ALTER TABLE commandes DROP CONSTRAINT IF EXISTS commandes_status_check');
            DB::statement(
                "ALTER TABLE commandes ADD CONSTRAINT commandes_status_check " .
                "CHECK (status::text = ANY(ARRAY['pending','received','en_cuisine','en_preparation','pret','servi','payee','annule']::text[]))"
            );
        } else {
            DB::statement("ALTER TABLE commandes MODIFY COLUMN status ENUM('pending','received','en_cuisine','en_preparation','pret','servi','payee','annule') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data
        DB::table('commandes')->where('status', 'en_cuisine')->update(['status' => 'en_preparation']);
        DB::table('commandes')->whereIn('status', ['pret', 'payee'])->update(['status' => 'servi']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE commandes DROP CONSTRAINT IF EXISTS commandes_status_check');
            DB::statement(
                "ALTER TABLE commandes ADD CONSTRAINT commandes_status_check " .
                "CHECK (status::text = ANY(ARRAY['pending','received','en_preparation','servi','annule']::text[]))"
            );
        } else {
            DB::statement("ALTER TABLE commandes MODIFY COLUMN status ENUM('pending','received','en_preparation','servi','annule') DEFAULT 'pending'");
        }
    }
};

