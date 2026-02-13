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
            // Drop the foreign key constraint first
            $table->dropForeign(['fournisseur_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('fournisseur_id')->nullable()->change();
            
            // Re-add the foreign key with nullable support
            $table->foreign('fournisseur_id')
                  ->references('id')
                  ->on('fournisseurs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
            $table->unsignedBigInteger('fournisseur_id')->nullable(false)->change();
            $table->foreign('fournisseur_id')
                  ->references('id')
                  ->on('fournisseurs')
                  ->onDelete('cascade');
        });
    }
};
