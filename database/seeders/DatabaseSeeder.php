<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            FournisseursSeeder::class,
            CategoriesSeeder::class,
            ProduitsSeeder::class,
            TablesSeeder::class,
            RestaurantSettingsSeeder::class,
            DocumentationSeeder::class,
            DashboardStatsSeeder::class,
        ]);
    }
}

