<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class MenuTemporaireV4Seeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Petit-dejeuner Exterieur & Supplement',
                'description' => 'Formules petit-dejeuner pour clients exterieurs ou options supplementaires.',
                'status' => 'active',
            ],
            [
                'name' => 'Boissons',
                'description' => 'Boissons fraiches et chaudes de la carte Oussoul.',
                'status' => 'active',
            ],
            [
                'name' => 'Dejeuner Leger',
                'description' => 'Selection de plats legers servis au dejeuner.',
                'status' => 'active',
            ],
            [
                'name' => 'Plats Marocains',
                'description' => 'Specialites marocaines proposees sur reservation.',
                'status' => 'active',
            ],
            [
                'name' => 'Desserts',
                'description' => 'Desserts et douceurs de fin de repas.',
                'status' => 'active',
            ],
            [
                'name' => 'Menu Terroir Oussoul',
                'description' => 'Formule terroir selon arrivage du jour.',
                'status' => 'active',
            ],
            [
                'name' => 'Petits Plaisirs Petit-dejeuner',
                'description' => 'Supplements a la carte pour le petit-dejeuner des residents.',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $products = [
            ['category' => 'Petit-dejeuner Exterieur & Supplement', 'name' => 'Petit-dejeuner marocain traditionnel', 'price_vente' => 70.00],
            ['category' => 'Petit-dejeuner Exterieur & Supplement', 'name' => 'Petit-dejeuner Healthy', 'price_vente' => 70.00],
            ['category' => 'Petit-dejeuner Exterieur & Supplement', 'name' => 'Petit-dejeuner Enfant', 'price_vente' => 50.00],

            ['category' => 'Boissons', 'name' => 'Eau 50 cl', 'price_vente' => 10.00],
            ['category' => 'Boissons', 'name' => 'Eau 1,5 L', 'price_vente' => 20.00],
            ['category' => 'Boissons', 'name' => 'Soda', 'price_vente' => 20.00],
            ['category' => 'Boissons', 'name' => 'Eau gazeuse', 'price_vente' => 20.00],
            ['category' => 'Boissons', 'name' => "Jus d'orange presse", 'price_vente' => 30.00],
            ['category' => 'Boissons', 'name' => 'Jus Detox (citron, concombre et menthe)', 'price_vente' => 35.00],
            ['category' => 'Boissons', 'name' => 'Mojito Maison (sans alcool)', 'price_vente' => 35.00],
            ['category' => 'Boissons', 'name' => 'Citronnade maison', 'price_vente' => 35.00],
            ['category' => 'Boissons', 'name' => 'Ice Tea', 'price_vente' => 25.00],
            ['category' => 'Boissons', 'name' => 'The a la menthe', 'price_vente' => 25.00],
            ['category' => 'Boissons', 'name' => 'The marocain aux herbes du jardin', 'price_vente' => 25.00],
            ['category' => 'Boissons', 'name' => 'The noir', 'price_vente' => 25.00],
            ['category' => 'Boissons', 'name' => 'Cafe Expresso', 'price_vente' => 20.00],
            ['category' => 'Boissons', 'name' => 'Cafe au lait', 'price_vente' => 30.00],

            ['category' => 'Dejeuner Leger', 'name' => 'Salade marocaine', 'price_vente' => 35.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Entree Oussoul Signature (3 assortiments marocains)', 'price_vente' => 60.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Salade Cesar Maison', 'price_vente' => 70.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Assiette de grillades mixtes', 'price_vente' => 135.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Tajine Kefta avec entree du jour', 'price_vente' => 120.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Tajine de crevettes sauce Pil-Pil', 'price_vente' => 100.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Spaghetti Bolognaise maison', 'price_vente' => 85.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Sandwich maison (poulet ou viande hachee)', 'price_vente' => 80.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Sandwich maison au thon', 'price_vente' => 60.00],
            ['category' => 'Dejeuner Leger', 'name' => 'Burger Fermier Oussoul', 'price_vente' => 85.00],

            ['category' => 'Plats Marocains', 'name' => 'Tajine de poulet aux citrons confits et olives', 'price_vente' => 110.00],
            ['category' => 'Plats Marocains', 'name' => 'Tajine boeuf aux pruneaux', 'price_vente' => 130.00],
            ['category' => 'Plats Marocains', 'name' => 'Tajine de poisson du Detroit a la chermoula', 'price_vente' => 120.00],
            ['category' => 'Plats Marocains', 'name' => 'Couscous boeuf', 'price_vente' => 90.00],
            ['category' => 'Plats Marocains', 'name' => 'Couscous poulet', 'price_vente' => 80.00],
            ['category' => 'Plats Marocains', 'name' => 'Pastilla traditionnelle au poulet', 'price_vente' => 90.00],
            ['category' => 'Plats Marocains', 'name' => 'Pastilla traditionnelle aux fruits de mer', 'price_vente' => 90.00],

            ['category' => 'Desserts', 'name' => 'Assiette de fruits frais de saison', 'price_vente' => 60.00],
            ['category' => 'Desserts', 'name' => 'Assortiment de patisseries marocaines (3 pieces)', 'price_vente' => 45.00],
            ['category' => 'Desserts', 'name' => 'Glace artisanale (2 boules)', 'price_vente' => 40.00],
            ['category' => 'Desserts', 'name' => 'Pancakes maison au chocolat avec glace', 'price_vente' => 60.00],

            ['category' => 'Menu Terroir Oussoul', 'name' => 'Menu Terroir Oussoul (entree + tajine + patisserie + the)', 'price_vente' => 180.00],

            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => "Jus d'orange presse", 'price_vente' => 20.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Jus Detox (citron, concombre et menthe)', 'price_vente' => 25.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Salade de fruits frais', 'price_vente' => 25.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Yaourt nature', 'price_vente' => 10.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Muesli', 'price_vente' => 25.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Fruits sec (amande et dattes)', 'price_vente' => 10.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Oeuf supplementaire', 'price_vente' => 10.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Oeuf au Khlie', 'price_vente' => 25.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Fromage a tartiner', 'price_vente' => 5.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Fromage fermier du village', 'price_vente' => 10.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Pancakes maison (2 pieces avec accompagnement)', 'price_vente' => 25.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Amlou', 'price_vente' => 5.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Chocolat', 'price_vente' => 5.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Charcuterie', 'price_vente' => 20.00],
            ['category' => 'Petits Plaisirs Petit-dejeuner', 'name' => 'Thon', 'price_vente' => 15.00],
        ];

        foreach ($products as $item) {
            $category = Category::where('name', $item['category'])->firstOrFail();

            $name = $item['name'];
            $salePrice = (float) $item['price_vente'];

            Produit::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $name,
                ],
                [
                    'price_vente' => $salePrice,
                    'price_achat' => round($salePrice * 0.45, 2),
                    'stock_quantity' => $this->inferStockQuantity($item['category']),
                    'alert_stock' => $this->inferAlertStock($item['category']),
                    'unit' => $this->inferUnit($name),
                    'status' => 'active',
                    'kitchen_active' => $this->inferKitchenActive($item['category'], $name),
                ]
            );
        }

        $this->command?->info('MenuTemporaireV4Seeder: ' . count($categories) . ' categories et ' . count($products) . ' produits synchronises.');
    }

    private function inferUnit(string $name): string
    {
        $normalized = mb_strtolower($name);

        if (str_contains($normalized, 'eau') || str_contains($normalized, 'soda') || str_contains($normalized, 'ice tea')) {
            return 'bouteille';
        }

        if (str_contains($normalized, 'jus') || str_contains($normalized, 'citronnade') || str_contains($normalized, 'mojito')) {
            return 'verre';
        }

        if (str_contains($normalized, 'cafe')) {
            return 'tasse';
        }

        if (str_contains($normalized, 'the')) {
            return 'tasse';
        }

        return 'portion';
    }

    private function inferKitchenActive(string $category, string $name): bool
    {
        $normalized = mb_strtolower($name);

        if ($category === 'Boissons') {
            if (str_contains($normalized, 'eau') || str_contains($normalized, 'soda') || str_contains($normalized, 'ice tea')) {
                return false;
            }
        }

        return true;
    }

    private function inferStockQuantity(string $category): int
    {
        if ($category === 'Boissons') {
            return 120;
        }

        if ($category === 'Petits Plaisirs Petit-dejeuner') {
            return 60;
        }

        return 40;
    }

    private function inferAlertStock(string $category): int
    {
        if ($category === 'Boissons') {
            return 20;
        }

        if ($category === 'Petits Plaisirs Petit-dejeuner') {
            return 12;
        }

        return 8;
    }
}