<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            // ── Salle principale ─────────────────────────────────────────────
            ['name' => 'T01', 'places' => 4, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T02', 'places' => 4, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T03', 'places' => 2, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T04', 'places' => 2, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T05', 'places' => 6, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T06', 'places' => 6, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T07', 'places' => 4, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T08', 'places' => 4, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T09', 'places' => 8, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],
            ['name' => 'T10', 'places' => 8, 'zone' => 'Salle principale', 'status' => 'free', 'is_active' => true],

            // ── Terrasse ─────────────────────────────────────────────────────
            ['name' => 'TR01', 'places' => 2, 'zone' => 'Terrasse',        'status' => 'free', 'is_active' => true],
            ['name' => 'TR02', 'places' => 2, 'zone' => 'Terrasse',        'status' => 'free', 'is_active' => true],
            ['name' => 'TR03', 'places' => 4, 'zone' => 'Terrasse',        'status' => 'free', 'is_active' => true],
            ['name' => 'TR04', 'places' => 4, 'zone' => 'Terrasse',        'status' => 'free', 'is_active' => true],
            ['name' => 'TR05', 'places' => 6, 'zone' => 'Terrasse',        'status' => 'free', 'is_active' => true],

            // ── Salon VIP (salon marocain traditionnel) ──────────────────────
            ['name' => 'VIP01', 'places' => 10, 'zone' => 'Salon VIP',     'status' => 'free', 'is_active' => true],
            ['name' => 'VIP02', 'places' => 12, 'zone' => 'Salon VIP',     'status' => 'free', 'is_active' => true],
            ['name' => 'VIP03', 'places' => 8,  'zone' => 'Salon VIP',     'status' => 'free', 'is_active' => true],

            // ── Bar & Comptoir ───────────────────────────────────────────────
            ['name' => 'B01', 'places' => 2, 'zone' => 'Bar & Comptoir',   'status' => 'free', 'is_active' => true],
            ['name' => 'B02', 'places' => 2, 'zone' => 'Bar & Comptoir',   'status' => 'free', 'is_active' => true],
            ['name' => 'B03', 'places' => 2, 'zone' => 'Bar & Comptoir',   'status' => 'free', 'is_active' => true],
        ];

        foreach ($tables as $t) {
            Table::create($t);
        }

        $this->command->info('✔ TablesSeeder : ' . count($tables) . ' tables créées.');
    }
}
