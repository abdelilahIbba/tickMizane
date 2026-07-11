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
                'name'        => 'Tajines',
                'description' => 'Tajines traditionnels marocains mijotés au charbon de bois',
                'image'       => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Couscous',
                'description' => 'Couscous maison servis le vendredi et en spécial du jour',
                'image'       => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Grillades & Méchoui',
                'description' => 'Viandes et poissons grillés, méchoui d\'agneau',
                'image'       => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Soupes & Harira',
                'description' => 'Soupes traditionnelles : harira, bissara, chorba',
                'image'       => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Entrées & Salades',
                'description' => 'Salades marocaines, briouates, zaalouk, taktouka',
                'image'       => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Pastilla & Spécialités',
                'description' => 'Pastilla au pigeon, pastilla au poisson, rfissa',
                'image'       => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Pains & Galettes',
                'description' => 'Khobz, msemen, meloui, batbout — tous faits maison',
                'image'       => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Boissons',
                'description' => 'Thé à la menthe, jus frais, eaux minérales, cafés',
                'image'       => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=640&q=80',
                'status'      => 'active',
            ],
            [
                'name'        => 'Desserts & Pâtisseries',
                'description' => 'Cornes de gazelle, chebakia, sellou, pastilla au lait',
                'image'       => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=640&q=80',
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
