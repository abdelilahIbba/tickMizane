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
        Schema::table('historiques', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('description');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('old_values')->nullable()->after('user_agent');
            $table->json('new_values')->nullable()->after('old_values');
            $table->string('device_type', 20)->nullable()->after('new_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'old_values', 'new_values', 'device_type']);
        });
    }
};
