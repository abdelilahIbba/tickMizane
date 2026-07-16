<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function isPgsql(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->isPgsql()) {
            return;
        }

        DB::statement('CREATE SCHEMA IF NOT EXISTS archive');

        DB::statement(
            'CREATE TABLE IF NOT EXISTS archive.historiques '
            .'(LIKE public.historiques INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)'
        );

        DB::statement('CREATE INDEX IF NOT EXISTS archive_historiques_created_at_idx ON archive.historiques (created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->isPgsql()) {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS archive.historiques');
    }
};
