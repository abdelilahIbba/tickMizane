<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class ProduitsSeeder extends Seeder
{
    public function run(): void
    {
        $byName = fn(string $name) => Category::where('name', $name)->firstOrFail()->id;

        $produits = [
            // ── Tajines ──────────────────────────────────────────────────────
            ['category' => 'Tajines', 'name' => 'Tajine de poulet aux olives et citron confit',       'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=640&q=80', 'price_vente' => 75.00,  'price_achat' => 32.00, 'stock_quantity' => 40, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => "Tajine d'agneau aux pruneaux et amandes",            'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?auto=format&fit=crop&w=640&q=80', 'price_vente' => 95.00,  'price_achat' => 45.00, 'stock_quantity' => 30, 'alert_stock' => 6,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine de kefta aux oeufs et tomates',               'image' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=640&q=80', 'price_vente' => 65.00,  'price_achat' => 27.00, 'stock_quantity' => 35, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine de merlan aux chermoula',                     'image' => 'https://images.unsplash.com/photo-1519984388953-d2406bc725e1?auto=format&fit=crop&w=640&q=80', 'price_vente' => 85.00,  'price_achat' => 38.00, 'stock_quantity' => 20, 'alert_stock' => 5,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => "Tajine de legumes a l'huile d'argan",                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 55.00,  'price_achat' => 18.00, 'stock_quantity' => 25, 'alert_stock' => 6,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => "Tajine M'qalli (poulet au safran et gingembre)",    'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80', 'price_vente' => 80.00,  'price_achat' => 35.00, 'stock_quantity' => 28, 'alert_stock' => 6,  'unit' => 'portion'],

            // ── Couscous ─────────────────────────────────────────────────────
            ['category' => 'Couscous', 'name' => 'Couscous Royal (poulet, agneau, merguez)',          'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 110.00, 'price_achat' => 55.00, 'stock_quantity' => 20, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous au poulet et sept legumes',               'image' => 'https://images.unsplash.com/photo-1574484284002-952d92456975?auto=format&fit=crop&w=640&q=80', 'price_vente' => 80.00,  'price_achat' => 35.00, 'stock_quantity' => 25, 'alert_stock' => 5,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous tfaya (agneau aux raisins secs)',         'image' => 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?auto=format&fit=crop&w=640&q=80', 'price_vente' => 90.00,  'price_achat' => 42.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous Bidaoui au lait et beurre smen',         'image' => 'https://images.unsplash.com/photo-1514326640560-7d063ef2aed5?auto=format&fit=crop&w=640&q=80', 'price_vente' => 70.00,  'price_achat' => 28.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],

            // ── Grillades & Mechoui ──────────────────────────────────────────
            ['category' => 'Grillades & Méchoui', 'name' => "Mechoui d'agneau (250g)",               'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=640&q=80', 'price_vente' => 100.00, 'price_achat' => 52.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Brochettes de kefta (4 pieces)',        'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=640&q=80', 'price_vente' => 55.00,  'price_achat' => 22.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => "Brochettes d'agneau (4 pieces)",        'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 65.00,  'price_achat' => 30.00, 'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Sardines grillees (6 pieces)',          'image' => 'https://images.unsplash.com/photo-1519984388953-d2406bc725e1?auto=format&fit=crop&w=640&q=80', 'price_vente' => 45.00,  'price_achat' => 15.00, 'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => "Mrouzia d'agneau aux epices",           'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80', 'price_vente' => 95.00,  'price_achat' => 48.00, 'stock_quantity' => 10, 'alert_stock' => 3,  'unit' => 'portion'],

            // ── Soupes & Harira ──────────────────────────────────────────────
            ['category' => 'Soupes & Harira', 'name' => 'Harira maison (bol)',                       'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 25.00,  'price_achat' => 8.00,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'bol'],
            ['category' => 'Soupes & Harira', 'name' => "Bissara (puree de feves a l'huile d'olive)",'image' => 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?auto=format&fit=crop&w=640&q=80', 'price_vente' => 20.00,  'price_achat' => 6.00,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'bol'],
            ['category' => 'Soupes & Harira', 'name' => 'Chorba de vermicelles au poulet',           'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=640&q=80', 'price_vente' => 22.00,  'price_achat' => 7.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'bol'],

            // ── Entrees & Salades ────────────────────────────────────────────
            ['category' => 'Entrées & Salades', 'name' => "Zaalouk (caviar d'aubergine)",            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=640&q=80', 'price_vente' => 20.00,  'price_achat' => 6.00,  'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Taktouka (salade poivrons-tomates)',      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 18.00,  'price_achat' => 5.00,  'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Briouates au fromage et fines herbes (6 pcs)', 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=640&q=80', 'price_vente' => 30.00, 'price_achat' => 10.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Briouates a la viande hachee (6 pcs)',   'image' => 'https://images.unsplash.com/photo-1574484284002-952d92456975?auto=format&fit=crop&w=640&q=80', 'price_vente' => 35.00,  'price_achat' => 13.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Salade marocaine (tomates, concombre, oignons)', 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=640&q=80', 'price_vente' => 22.00, 'price_achat' => 7.00, 'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => '5 salades marocaines (plateau)',          'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=640&q=80', 'price_vente' => 55.00,  'price_achat' => 18.00, 'stock_quantity' => 20, 'alert_stock' => 5,  'unit' => 'plateau'],

            // ── Pastilla & Specialites ───────────────────────────────────────
            ['category' => 'Pastilla & Spécialités', 'name' => 'Pastilla au pigeon (4 pers.)',       'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=640&q=80', 'price_vente' => 160.00, 'price_achat' => 80.00, 'stock_quantity' => 8,  'alert_stock' => 2,  'unit' => 'pièce'],
            ['category' => 'Pastilla & Spécialités', 'name' => 'Pastilla au poisson et fruits de mer', 'image' => 'https://images.unsplash.com/photo-1519984388953-d2406bc725e1?auto=format&fit=crop&w=640&q=80', 'price_vente' => 140.00, 'price_achat' => 70.00, 'stock_quantity' => 8, 'alert_stock' => 2, 'unit' => 'pièce'],
            ['category' => 'Pastilla & Spécialités', 'name' => 'Rfissa au poulet et lentilles',      'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80', 'price_vente' => 85.00,  'price_achat' => 38.00, 'stock_quantity' => 12, 'alert_stock' => 3,  'unit' => 'portion'],

            // ── Pains & Galettes ─────────────────────────────────────────────
            ['category' => 'Pains & Galettes', 'name' => 'Khobz (pain traditionnel maison)',         'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=640&q=80', 'price_vente' => 5.00,   'price_achat' => 1.50,  'stock_quantity' => 100,'alert_stock' => 20, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Msemen (crepe feuilletee)',                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=640&q=80', 'price_vente' => 8.00,   'price_achat' => 2.50,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Meloui (crepe roulee au miel)',            'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=640&q=80', 'price_vente' => 10.00,  'price_achat' => 3.00,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Batbout (pain farci kefta-fromage)',       'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=640&q=80', 'price_vente' => 18.00,  'price_achat' => 6.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Harcha (galette semoule au beurre)',       'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=640&q=80', 'price_vente' => 8.00,   'price_achat' => 2.50,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'pièce'],

            // ── Boissons ─────────────────────────────────────────────────────
            ['category' => 'Boissons', 'name' => 'The a la menthe (theiere)',                        'image' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=640&q=80', 'price_vente' => 25.00,  'price_achat' => 5.00,  'stock_quantity' => 200,'alert_stock' => 30, 'unit' => 'théière',   'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => 'The a la menthe (verre)',                          'image' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=640&q=80', 'price_vente' => 10.00,  'price_achat' => 2.00,  'stock_quantity' => 300,'alert_stock' => 50, 'unit' => 'verre',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => "Jus d'orange frais presse",                       'image' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=640&q=80', 'price_vente' => 20.00,  'price_achat' => 7.00,  'stock_quantity' => 80, 'alert_stock' => 15, 'unit' => 'verre',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => "Jus d'avocat au lait",                            'image' => 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?auto=format&fit=crop&w=640&q=80', 'price_vente' => 25.00,  'price_achat' => 10.00, 'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'verre',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => 'Jus de grenade frais',                            'image' => 'https://images.unsplash.com/photo-1586348943529-beaae6c28db9?auto=format&fit=crop&w=640&q=80', 'price_vente' => 22.00,  'price_achat' => 8.00,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'verre',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => "Citronnade a la fleur d'oranger",                 'image' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=640&q=80', 'price_vente' => 18.00,  'price_achat' => 5.00,  'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'verre',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => 'Cafe marocain (noir)',                             'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=640&q=80', 'price_vente' => 12.00,  'price_achat' => 3.00,  'stock_quantity' => 150,'alert_stock' => 25, 'unit' => 'tasse',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => 'Cafe au lait (nous-nous)',                         'image' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=640&q=80', 'price_vente' => 14.00,  'price_achat' => 4.00,  'stock_quantity' => 100,'alert_stock' => 20, 'unit' => 'tasse',     'kitchen_active' => 1],
            ['category' => 'Boissons', 'name' => 'Eau minerale Sidi Ali 50cl',                      'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?auto=format&fit=crop&w=640&q=80', 'price_vente' => 6.00,   'price_achat' => 2.50,  'stock_quantity' => 120,'alert_stock' => 24, 'unit' => 'bouteille', 'kitchen_active' => 0],
            ['category' => 'Boissons', 'name' => 'Eau minerale Ain Atlas 1.5L',                     'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?auto=format&fit=crop&w=640&q=80', 'price_vente' => 10.00,  'price_achat' => 4.00,  'stock_quantity' => 80, 'alert_stock' => 18, 'unit' => 'bouteille', 'kitchen_active' => 0],
            ['category' => 'Boissons', 'name' => 'Eau gazeuse Belvita 50cl',                        'image' => 'https://images.unsplash.com/photo-1568213816046-0ee1c42bd559?auto=format&fit=crop&w=640&q=80', 'price_vente' => 8.00,   'price_achat' => 3.50,  'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'bouteille', 'kitchen_active' => 0],

            // ── Desserts & Patisseries ───────────────────────────────────────
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Cornes de gazelle (3 pcs)',          'image' => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=640&q=80', 'price_vente' => 20.00,  'price_achat' => 7.00,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Chebakia au miel et sesame (4 pcs)', 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=640&q=80', 'price_vente' => 18.00,  'price_achat' => 6.00,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Sellou (amlou de fete)',             'image' => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=640&q=80', 'price_vente' => 22.00,  'price_achat' => 8.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Pastilla au lait (individuel)',      'image' => 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?auto=format&fit=crop&w=640&q=80', 'price_vente' => 35.00,  'price_achat' => 13.00, 'stock_quantity' => 20, 'alert_stock' => 5,  'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Plateau de patisseries marocaines (8 pcs)', 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=640&q=80', 'price_vente' => 50.00, 'price_achat' => 18.00, 'stock_quantity' => 15, 'alert_stock' => 4, 'unit' => 'plateau'],
        ];

        foreach ($produits as $p) {
            $catName = $p['category'];
            $cat = Category::where('name', $catName)->firstOrFail();

            Produit::updateOrCreate(
                ['name' => $p['name'], 'category_id' => $cat->id],
                [
                    'image'          => $p['image'],
                    'price_vente'    => $p['price_vente'],
                    'price_achat'    => $p['price_achat'],
                    'stock_quantity' => $p['stock_quantity'],
                    'alert_stock'    => $p['alert_stock'],
                    'unit'           => $p['unit'],
                    'status'         => 'active',
                    'kitchen_active' => $p['kitchen_active'] ?? 1,
                ]
            );
        }

        $this->command->info('ProduitsSeeder : ' . count($produits) . ' produits crees.');
    }
}
