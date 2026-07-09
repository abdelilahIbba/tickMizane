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
                'status'      => 'active',
            ],
            [
                'name'        => 'Couscous',
                'description' => 'Couscous maison servis le vendredi et en spécial du jour',
                'status'      => 'active',
            ],
            [
                'name'        => 'Grillades & Méchoui',
                'description' => 'Viandes et poissons grillés, méchoui d\'agneau',
                'status'      => 'active',
            ],
            [
                'name'        => 'Soupes & Harira',
                'description' => 'Soupes traditionnelles : harira, bissara, chorba',
                'status'      => 'active',
            ],
            [
                'name'        => 'Entrées & Salades',
                'description' => 'Salades marocaines, briouates, zaalouk, taktouka',
                'status'      => 'active',
            ],
            [
                'name'        => 'Pastilla & Spécialités',
                'description' => 'Pastilla au pigeon, pastilla au poisson, rfissa',
                'status'      => 'active',
            ],
            [
                'name'        => 'Pains & Galettes',
                'description' => 'Khobz, msemen, meloui, batbout — tous faits maison',
                'status'      => 'active',
            ],
            [
                'name'        => 'Boissons',
                'description' => 'Thé à la menthe, jus frais, eaux minérales, cafés',
                'status'      => 'active',
            ],
            [
                'name'        => 'Desserts & Pâtisseries',
                'description' => 'Cornes de gazelle, chebakia, sellou, pastilla au lait',
                'status'      => 'active',
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $this->command->info('✔ CategoriesSeeder : ' . count($categories) . ' catégories créées.');
    }
}
