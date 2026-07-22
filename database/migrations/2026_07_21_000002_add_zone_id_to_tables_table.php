<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('zone_id')
                ->nullable()
                ->after('zone')
                ->constrained('zones')
                ->nullOnDelete();
        });

        $zoneNames = DB::table('tables')
            ->whereNotNull('zone')
            ->where('zone', '!=', '')
            ->distinct()
            ->pluck('zone');

        foreach ($zoneNames as $zoneName) {
            $zoneId = DB::table('zones')->insertGetId([
                'name' => $zoneName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tables')
                ->where('zone', $zoneName)
                ->whereNull('zone_id')
                ->update(['zone_id' => $zoneId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }
};
