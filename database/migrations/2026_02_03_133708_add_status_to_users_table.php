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
            // Status column already exists, just add the new columns
            $table->boolean('force_password_reset')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('force_password_reset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['force_password_reset', 'last_login_at']);
        });
    }
};
