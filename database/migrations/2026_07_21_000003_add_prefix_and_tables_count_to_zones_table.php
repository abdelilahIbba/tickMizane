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
        Schema::table('zones', function (Blueprint $table) {
            $table->string('prefix', 10)->nullable()->after('name');
            $table->unsignedInteger('tables_count')->default(0)->after('description');
        });

        DB::table('zones')->orderBy('id')->get()->each(function (object $zone): void {
            $name = (string) ($zone->name ?? '');
            $prefix = $this->derivePrefix($name);
            $count = DB::table('tables')->where('zone_id', $zone->id)->count();

            DB::table('zones')->where('id', $zone->id)->update([
                'prefix' => $prefix,
                'tables_count' => $count,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['prefix', 'tables_count']);
        });
    }

    private function derivePrefix(string $zoneName): string
    {
        $clean = strtoupper(trim($zoneName));
        $first = substr(preg_replace('/[^A-Z0-9]/', '', $clean) ?: 'Z', 0, 1);

        return $first !== '' ? $first : 'Z';
    }
};
