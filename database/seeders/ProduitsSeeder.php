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
            ['category' => 'Tajines', 'name' => 'Tajine de poulet aux olives et citron confit',  'price_vente' => 75.00,  'price_achat' => 32.00, 'stock_quantity' => 40, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine d\'agneau aux pruneaux et amandes',      'price_vente' => 95.00,  'price_achat' => 45.00, 'stock_quantity' => 30, 'alert_stock' => 6,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine de kefta aux œufs et tomates',          'price_vente' => 65.00,  'price_achat' => 27.00, 'stock_quantity' => 35, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine de merlan aux chermoula',                'price_vente' => 85.00,  'price_achat' => 38.00, 'stock_quantity' => 20, 'alert_stock' => 5,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine de légumes à l\'huile d\'argan',        'price_vente' => 55.00,  'price_achat' => 18.00, 'stock_quantity' => 25, 'alert_stock' => 6,  'unit' => 'portion'],
            ['category' => 'Tajines', 'name' => 'Tajine M\'qalli (poulet au safran et gingembre)', 'price_vente' => 80.00, 'price_achat' => 35.00, 'stock_quantity' => 28, 'alert_stock' => 6, 'unit' => 'portion'],

            // ── Couscous ─────────────────────────────────────────────────────
            ['category' => 'Couscous', 'name' => 'Couscous Royal (poulet, agneau, merguez)',    'price_vente' => 110.00, 'price_achat' => 55.00, 'stock_quantity' => 20, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous au poulet et sept légumes',          'price_vente' => 80.00,  'price_achat' => 35.00, 'stock_quantity' => 25, 'alert_stock' => 5,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous tfaya (agneau aux raisins secs)',    'price_vente' => 90.00,  'price_achat' => 42.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Couscous', 'name' => 'Couscous Bidaoui au lait et beurre smen',    'price_vente' => 70.00,  'price_achat' => 28.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],

            // ── Grillades & Méchoui ──────────────────────────────────────────
            ['category' => 'Grillades & Méchoui', 'name' => 'Méchoui d\'agneau (250g)',        'price_vente' => 100.00, 'price_achat' => 52.00, 'stock_quantity' => 15, 'alert_stock' => 4,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Brochettes de kefta (4 pièces)', 'price_vente' => 55.00,  'price_achat' => 22.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Brochettes d\'agneau (4 pièces)', 'price_vente' => 65.00,  'price_achat' => 30.00, 'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Sardines grillées (6 pièces)',   'price_vente' => 45.00,  'price_achat' => 15.00, 'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Grillades & Méchoui', 'name' => 'Mrouzia d\'agneau aux épices',   'price_vente' => 95.00,  'price_achat' => 48.00, 'stock_quantity' => 10, 'alert_stock' => 3,  'unit' => 'portion'],

            // ── Soupes & Harira ──────────────────────────────────────────────
            ['category' => 'Soupes & Harira', 'name' => 'Harira maison (bol)',                  'price_vente' => 25.00,  'price_achat' => 8.00,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'bol'],
            ['category' => 'Soupes & Harira', 'name' => 'Bissara (purée de fèves à l\'huile d\'olive)', 'price_vente' => 20.00, 'price_achat' => 6.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'bol'],
            ['category' => 'Soupes & Harira', 'name' => 'Chorba de vermicelles au poulet',     'price_vente' => 22.00,  'price_achat' => 7.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'bol'],

            // ── Entrées & Salades ────────────────────────────────────────────
            ['category' => 'Entrées & Salades', 'name' => 'Zaalouk (caviar d\'aubergine)',     'price_vente' => 20.00,  'price_achat' => 6.00,  'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Taktouka (salade poivrons-tomates)', 'price_vente' => 18.00, 'price_achat' => 5.00,  'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Briouates au fromage et fines herbes (6 pcs)', 'price_vente' => 30.00, 'price_achat' => 10.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Briouates à la viande hachée (6 pcs)', 'price_vente' => 35.00, 'price_achat' => 13.00, 'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Salade marocaine (tomates, concombre, oignons)', 'price_vente' => 22.00, 'price_achat' => 7.00, 'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'portion'],
            ['category' => 'Entrées & Salades', 'name' => 'Plateau de 5 salades marocaines',  'price_vente' => 55.00,  'price_achat' => 18.00, 'stock_quantity' => 20, 'alert_stock' => 5,  'unit' => 'portion'],

            // ── Pastilla & Spécialités ───────────────────────────────────────
            ['category' => 'Pastilla & Spécialités', 'name' => 'Pastilla au pigeon (4 pers.)', 'price_vente' => 160.00, 'price_achat' => 80.00, 'stock_quantity' => 8,  'alert_stock' => 2,  'unit' => 'pièce'],
            ['category' => 'Pastilla & Spécialités', 'name' => 'Pastilla au poisson et fruits de mer', 'price_vente' => 140.00, 'price_achat' => 70.00, 'stock_quantity' => 8, 'alert_stock' => 2, 'unit' => 'pièce'],
            ['category' => 'Pastilla & Spécialités', 'name' => 'Rfissa au poulet et lentilles', 'price_vente' => 85.00,  'price_achat' => 38.00, 'stock_quantity' => 12, 'alert_stock' => 3,  'unit' => 'portion'],

            // ── Pains & Galettes ─────────────────────────────────────────────
            ['category' => 'Pains & Galettes', 'name' => 'Khobz (pain traditionnel maison)',   'price_vente' => 5.00,   'price_achat' => 1.50,  'stock_quantity' => 100,'alert_stock' => 20, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Msemen (crêpe feuilletée)',          'price_vente' => 8.00,   'price_achat' => 2.50,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Meloui (crêpe roulée au miel)',      'price_vente' => 10.00,  'price_achat' => 3.00,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Batbout (pain farci kefta-fromage)', 'price_vente' => 18.00,  'price_achat' => 6.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'pièce'],
            ['category' => 'Pains & Galettes', 'name' => 'Harcha (galette semoule au beurre)', 'price_vente' => 8.00,   'price_achat' => 2.50,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'pièce'],

            // ── Boissons ─────────────────────────────────────────────────────
            ['category' => 'Boissons', 'name' => 'Thé à la menthe (théière)',                   'price_vente' => 25.00,  'price_achat' => 5.00,  'stock_quantity' => 200,'alert_stock' => 30, 'unit' => 'théière'],
            ['category' => 'Boissons', 'name' => 'Thé à la menthe (verre)',                    'price_vente' => 10.00,  'price_achat' => 2.00,  'stock_quantity' => 300,'alert_stock' => 50, 'unit' => 'verre'],
            ['category' => 'Boissons', 'name' => 'Jus d\'orange frais pressé',                 'price_vente' => 20.00,  'price_achat' => 7.00,  'stock_quantity' => 80, 'alert_stock' => 15, 'unit' => 'verre'],
            ['category' => 'Boissons', 'name' => 'Jus d\'avocat au lait',                      'price_vente' => 25.00,  'price_achat' => 10.00, 'stock_quantity' => 50, 'alert_stock' => 10, 'unit' => 'verre'],
            ['category' => 'Boissons', 'name' => 'Jus de grenade frais',                       'price_vente' => 22.00,  'price_achat' => 8.00,  'stock_quantity' => 40, 'alert_stock' => 10, 'unit' => 'verre'],
            ['category' => 'Boissons', 'name' => 'Citronnade à la fleur d\'oranger',           'price_vente' => 18.00,  'price_achat' => 5.00,  'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'verre'],
            ['category' => 'Boissons', 'name' => 'Café marocain (noir)',                       'price_vente' => 12.00,  'price_achat' => 3.00,  'stock_quantity' => 150,'alert_stock' => 25, 'unit' => 'tasse'],
            ['category' => 'Boissons', 'name' => 'Café au lait (nous-nous)',                   'price_vente' => 14.00,  'price_achat' => 4.00,  'stock_quantity' => 100,'alert_stock' => 20, 'unit' => 'tasse'],
            ['category' => 'Boissons', 'name' => 'Eau minérale Sidi Ali 50cl',                 'price_vente' => 6.00,   'price_achat' => 2.50,  'stock_quantity' => 120,'alert_stock' => 24, 'unit' => 'bouteille'],
            ['category' => 'Boissons', 'name' => 'Eau minérale Aïn Atlas 1.5L',               'price_vente' => 10.00,  'price_achat' => 4.00,  'stock_quantity' => 80, 'alert_stock' => 18, 'unit' => 'bouteille'],
            ['category' => 'Boissons', 'name' => 'Eau gazeuse Belvita 50cl',                  'price_vente' => 8.00,   'price_achat' => 3.50,  'stock_quantity' => 60, 'alert_stock' => 12, 'unit' => 'bouteille'],

            // ── Desserts & Pâtisseries ───────────────────────────────────────
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Cornes de gazelle (3 pcs)',   'price_vente' => 20.00,  'price_achat' => 7.00,  'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Chebakia au miel et sésame (4 pcs)', 'price_vente' => 18.00, 'price_achat' => 6.00, 'stock_quantity' => 60, 'alert_stock' => 15, 'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Sellou (amlou de fête)',      'price_vente' => 22.00,  'price_achat' => 8.00,  'stock_quantity' => 30, 'alert_stock' => 8,  'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Pastilla au lait (individuel)', 'price_vente' => 35.00, 'price_achat' => 13.00, 'stock_quantity' => 20, 'alert_stock' => 5, 'unit' => 'portion'],
            ['category' => 'Desserts & Pâtisseries', 'name' => 'Plateau de pâtisseries marocaines (8 pcs)', 'price_vente' => 50.00, 'price_achat' => 18.00, 'stock_quantity' => 15, 'alert_stock' => 4, 'unit' => 'plateau'],
        ];

        foreach ($produits as $p) {
            $catId = $byName($p['category']);
            Produit::create([
                'category_id'    => $catId,
                'name'           => $p['name'],
                'price_vente'    => $p['price_vente'],
                'price_achat'    => $p['price_achat'],
                'stock_quantity' => $p['stock_quantity'],
                'alert_stock'    => $p['alert_stock'],
                'unit'           => $p['unit'],
                'status'         => 'active',
            ]);
        }

        $this->command->info('✔ ProduitsSeeder : ' . count($produits) . ' produits créés.');
    }
}
