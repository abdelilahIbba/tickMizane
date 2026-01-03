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
        Schema::table('tables', function (Blueprint $table) {
            // Add new columns for enhanced table management
            $table->integer('places')->default(4)->after('name');
            $table->string('zone')->nullable()->after('places');
            $table->foreignId('current_vente_id')->nullable()->after('status')->constrained('ventes')->nullOnDelete();
            $table->foreignId('serveur_id')->nullable()->after('current_vente_id')->constrained('users')->nullOnDelete();
            $table->timestamp('occupied_at')->nullable()->after('serveur_id');
            $table->text('notes')->nullable()->after('occupied_at');
            $table->boolean('is_active')->default(true)->after('notes');
            $table->string('qr_code')->nullable()->after('is_active');
            
            // Performance indexes
            $table->index('zone');
            $table->index('is_active');
            $table->index('serveur_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['current_vente_id']);
            $table->dropForeign(['serveur_id']);
            $table->dropIndex(['zone']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['serveur_id']);
            $table->dropColumn([
                'places',
                'zone',
                'current_vente_id',
                'serveur_id',
                'occupied_at',
                'notes',
                'is_active',
                'qr_code',
            ]);
        });
    }
};
