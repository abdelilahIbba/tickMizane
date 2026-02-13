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
     * Updates the status enum to include:
     * - en_cuisine: Order sent to kitchen by waiter
     * - pret: Kitchen marked order as ready
     * - payee: Cashier processed payment
     */
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM column
        // First, update any existing 'en_preparation' to 'en_cuisine' for consistency
        DB::statement("UPDATE commandes SET status = 'en_cuisine' WHERE status = 'en_preparation' AND type = 'kitchen'");
        
        // Modify the ENUM to include all statuses
        DB::statement("ALTER TABLE commandes MODIFY COLUMN status ENUM('pending', 'received', 'en_cuisine', 'en_preparation', 'pret', 'servi', 'payee', 'annule') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous status values
        DB::statement("UPDATE commandes SET status = 'en_preparation' WHERE status = 'en_cuisine'");
        DB::statement("UPDATE commandes SET status = 'servi' WHERE status IN ('pret', 'payee')");
        
        DB::statement("ALTER TABLE commandes MODIFY COLUMN status ENUM('pending', 'received', 'en_preparation', 'servi', 'annule') DEFAULT 'pending'");
    }
};
