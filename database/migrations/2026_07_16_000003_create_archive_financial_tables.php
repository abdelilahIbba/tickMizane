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

        DB::statement('CREATE TABLE IF NOT EXISTS archive.ventes (LIKE public.ventes INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)');
        DB::statement('CREATE TABLE IF NOT EXISTS archive.vente_details (LIKE public.vente_details INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)');
        DB::statement('CREATE TABLE IF NOT EXISTS archive.commandes (LIKE public.commandes INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)');
        DB::statement('CREATE TABLE IF NOT EXISTS archive.commande_details (LIKE public.commande_details INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)');
        DB::statement('CREATE TABLE IF NOT EXISTS archive.paiements (LIKE public.paiements INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING INDEXES)');

        DB::statement('CREATE INDEX IF NOT EXISTS archive_ventes_created_at_idx ON archive.ventes (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS archive_commandes_created_at_idx ON archive.commandes (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS archive_paiements_created_at_idx ON archive.paiements (created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->isPgsql()) {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS archive.paiements');
        DB::statement('DROP TABLE IF EXISTS archive.commande_details');
        DB::statement('DROP TABLE IF EXISTS archive.commandes');
        DB::statement('DROP TABLE IF EXISTS archive.vente_details');
        DB::statement('DROP TABLE IF EXISTS archive.ventes');
    }
};
