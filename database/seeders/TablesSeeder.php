<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [

            // ── Restaurant intérieur RDC — TR1 à TR8 ────────────────────────
            ['name' => 'TR1', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR2', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR3', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR4', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR5', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR6', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR7', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR8', 'places' => 4, 'zone' => 'Restaurant', 'status' => 'free', 'is_active' => true],

            // ── Restaurant étage 2 — TR9 à TR16 ─────────────────────────────
            ['name' => 'TR9',  'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR10', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR11', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR12', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR13', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR14', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR15', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],
            ['name' => 'TR16', 'places' => 4, 'zone' => 'Restaurant Étage 2', 'status' => 'free', 'is_active' => true],

            // ── Terrasse — TS1 à TS10 ────────────────────────────────────────
            ['name' => 'TS1',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS2',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS3',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS4',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS5',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS6',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS7',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS8',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS9',  'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],
            ['name' => 'TS10', 'places' => 4, 'zone' => 'Terrasse', 'status' => 'free', 'is_active' => true],

            // ── Salon — SL1 à SL4 ───────────────────────────────────────────
            ['name' => 'SL1', 'places' => 8,  'zone' => 'Salon', 'status' => 'free', 'is_active' => true],
            ['name' => 'SL2', 'places' => 8,  'zone' => 'Salon', 'status' => 'free', 'is_active' => true],
            ['name' => 'SL3', 'places' => 10, 'zone' => 'Salon', 'status' => 'free', 'is_active' => true],
            ['name' => 'SL4', 'places' => 10, 'zone' => 'Salon', 'status' => 'free', 'is_active' => true],

            // ── Piscine — PS1 à PS5 ──────────────────────────────────────────
            ['name' => 'PS1', 'places' => 4, 'zone' => 'Piscine', 'status' => 'free', 'is_active' => true],
            ['name' => 'PS2', 'places' => 4, 'zone' => 'Piscine', 'status' => 'free', 'is_active' => true],
            ['name' => 'PS3', 'places' => 4, 'zone' => 'Piscine', 'status' => 'free', 'is_active' => true],
            ['name' => 'PS4', 'places' => 4, 'zone' => 'Piscine', 'status' => 'free', 'is_active' => true],
            ['name' => 'PS5', 'places' => 4, 'zone' => 'Piscine', 'status' => 'free', 'is_active' => true],
        ];

        foreach ($tables as $t) {
            Table::create($t);
        }

        $this->command->info('✔ TablesSeeder : ' . count($tables) . ' tables créées.');
    }
}
