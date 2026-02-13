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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('module'); // e.g., 'pos', 'kitchen', 'inventory', 'reports', 'settings'
            $table->string('action'); // e.g., 'view', 'create', 'edit', 'delete', 'send_to_kitchen'
            $table->boolean('allowed')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'module', 'action']);
            $table->unique(['user_id', 'module', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
