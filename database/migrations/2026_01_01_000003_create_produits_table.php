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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price_vente', 10, 2);
            $table->decimal('price_achat', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('alert_stock')->default(10);
            $table->enum('unit', ['pcs', 'kg', 'l', 'portion', 'bol', 'verre', 'tasse', 'bouteille', 'théière', 'pièce', 'plateau'])->default('pcs');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('category_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
