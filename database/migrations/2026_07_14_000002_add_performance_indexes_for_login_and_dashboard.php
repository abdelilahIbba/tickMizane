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
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status'], 'users_role_status_idx');
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'ventes_status_created_at_idx');
        });

        Schema::table('vente_details', function (Blueprint $table) {
            $table->index(['produit_id', 'vente_id'], 'vente_details_produit_vente_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vente_details', function (Blueprint $table) {
            $table->dropIndex('vente_details_produit_vente_idx');
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->dropIndex('ventes_status_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_status_idx');
        });
    }
};
