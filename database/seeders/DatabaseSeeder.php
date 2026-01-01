<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Produit;
use App\Models\Fournisseur;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default users
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Caissier 1',
            'username' => 'caissier1',
            'password' => Hash::make('caisse123'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Serveur 1',
            'username' => 'serveur1',
            'password' => Hash::make('serveur123'),
            'role' => 'serveur',
            'status' => 'active',
        ]);

        // Create categories
        $boissons = Category::create(['name' => 'Boissons', 'description' => 'Boissons fraîches et chaudes', 'status' => 'active']);
        $snacks = Category::create(['name' => 'Snacks', 'description' => 'Chips, biscuits, confiseries', 'status' => 'active']);
        $epicerie = Category::create(['name' => 'Épicerie', 'description' => 'Produits de base', 'status' => 'active']);

        // Create produits
        Produit::create(['category_id' => $boissons->id, 'name' => 'Eau minérale 1.5L', 'price_vente' => 8.00, 'price_achat' => 5.00, 'stock_quantity' => 48, 'alert_stock' => 10, 'unit' => 'pcs']);
        Produit::create(['category_id' => $boissons->id, 'name' => 'Coca-Cola 33cl', 'price_vente' => 10.00, 'price_achat' => 6.50, 'stock_quantity' => 36, 'alert_stock' => 12, 'unit' => 'pcs']);
        Produit::create(['category_id' => $boissons->id, 'name' => 'Café', 'price_vente' => 12.00, 'price_achat' => 4.00, 'stock_quantity' => 100, 'alert_stock' => 20, 'unit' => 'pcs']);
        Produit::create(['category_id' => $snacks->id, 'name' => 'Chips Lays 150g', 'price_vente' => 15.00, 'price_achat' => 10.00, 'stock_quantity' => 24, 'alert_stock' => 8, 'unit' => 'pcs']);
        Produit::create(['category_id' => $epicerie->id, 'name' => 'Pain de mie', 'price_vente' => 12.00, 'price_achat' => 8.00, 'stock_quantity' => 5, 'alert_stock' => 3, 'unit' => 'pcs']);
        Produit::create(['category_id' => $epicerie->id, 'name' => 'Lait 1L', 'price_vente' => 9.00, 'price_achat' => 6.00, 'stock_quantity' => 30, 'alert_stock' => 10, 'unit' => 'l']);

        // Create fournisseurs
        Fournisseur::create(['name' => 'Boissons Maroc SARL', 'phone' => '+212 522-123456', 'email' => 'contact@boissonsmaroc.ma', 'address' => 'Zone Industrielle, Casablanca']);
        Fournisseur::create(['name' => 'Épicerie Gros', 'phone' => '+212 522-654321', 'email' => 'info@epiceriegros.ma', 'address' => 'Hay Mohammadi, Casablanca']);

        // Create tables
        for ($i = 1; $i <= 12; $i++) {
            Table::create([
                'name' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => 'free',
            ]);
        }
    }
}
