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
        Schema::table('commandes', function (Blueprint $table) {
            // Add table relationship for restaurant orders
            $table->foreignId('table_id')->nullable()->after('user_id')->constrained('tables')->onDelete('set null');
            
            // Add order type to differentiate supplier orders from kitchen orders
            $table->enum('type', ['supplier', 'kitchen'])->default('supplier')->after('status');
            
            // Add waiter notes for special instructions
            $table->text('waiter_notes')->nullable()->after('type');
            
            // Extend status enum to include kitchen workflow states
            // We'll handle this by modifying the column
            $table->dropColumn('status');
        });
        
        Schema::table('commandes', function (Blueprint $table) {
            $table->enum('status', ['pending', 'received', 'en_preparation', 'servi', 'annule'])->default('pending')->after('total');
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['table_id', 'type', 'waiter_notes']);
            $table->dropColumn('status');
        });
        
        Schema::table('commandes', function (Blueprint $table) {
            $table->enum('status', ['pending', 'received'])->default('pending');
        });
    }
};
