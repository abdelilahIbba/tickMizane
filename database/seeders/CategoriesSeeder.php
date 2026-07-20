<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Petit-Déjeuner',
                'name_ar'     => 'الفطور',
                'description' => 'Petit-déjeuner marocain traditionnel, healthy et enfant',
                'image'       => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Boissons',
                'name_ar'     => 'المشروبات',
                'description' => 'Eaux, sodas, jus frais, thés et cafés maison',
                'image'       => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Déjeuner Léger',
                'name_ar'     => 'الغداء الخفيف',
                'description' => 'Salades, grillades, tajines, sandwichs et pâtes — servis de 13h à 15h',
                'image'       => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Plats Marocains',
                'name_ar'     => 'الأطباق المغربية',
                'description' => 'Tajines, couscous et pastilla — sur réservation',
                'image'       => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Desserts',
                'name_ar'     => 'الحلويات',
                'description' => 'Fruits frais, pâtisseries marocaines, glace artisanale et pancakes',
                'image'       => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Menu Oussoul',
                'name_ar'     => 'قائمة أصول',
                'description' => 'Formule complète selon arrivage du jour — entrée, tajine, pâtisseries et thé',
                'image'       => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Les Petits Plaisirs',
                'name_ar'     => 'المتع الصغيرة',
                'description' => 'Suppléments, accompagnements et petites gourmandises maison',
                'image'       => 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }

        $this->command->info('✔ CategoriesSeeder : ' . count($categories) . ' catégories créées.');
    }
}
