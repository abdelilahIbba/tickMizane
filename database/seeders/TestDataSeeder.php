<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Produit;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Only seeds data in testing environment.
     */
    public function run(): void
    {
        // Only run in test environment
        if (!app()->environment('testing')) {
            return;
        }

        // Create test users
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'name' => 'Test Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('password123'),
            'name' => 'Test Cashier',
            'role' => 'caissier',
            'status' => 'active',
        ]);

        User::factory()->create([
            'username' => 'waiter01',
            'password' => bcrypt('password123'),
            'name' => 'Test Waiter',
            'role' => 'serveur',
            'status' => 'active',
        ]);

        // Create categories
        $beverages = Category::factory()->create([
            'name' => 'Beverages',
            'description' => 'Hot and cold drinks',
        ]);

        $food = Category::factory()->create([
            'name' => 'Food',
            'description' => 'Main courses and snacks',
        ]);

        // Create products
        Produit::factory()->count(10)->create([
            'category_id' => $beverages->id,
        ]);

        Produit::factory()->count(15)->create([
            'category_id' => $food->id,
        ]);

        // Create a few products with low stock
        Produit::factory()->count(3)->lowStock()->create([
            'category_id' => $food->id,
        ]);

        // Create tables
        Table::factory()->count(20)->create();
    }
}

