<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Add commande support for kitchen orders
            $table->foreignId('commande_id')->nullable()->after('vente_id')->constrained('commandes')->onDelete('cascade');
            
            // Make vente_id nullable since we now support commande payments too
            $table->foreignId('vente_id')->nullable()->change();
            
            // Add reference number for receipts
            $table->string('reference', 50)->nullable()->after('method');
            
            // Add user who processed the payment
            $table->foreignId('user_id')->nullable()->after('reference')->constrained('users')->onDelete('set null');
            
            // Add status for tracking
            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('completed')->after('user_id');
            
            // Add notes
            $table->text('notes')->nullable()->after('status');
            
            // Indexes
            $table->index('commande_id');
            $table->index('reference');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['commande_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['commande_id', 'reference', 'user_id', 'status', 'notes']);
        });
    }
};
