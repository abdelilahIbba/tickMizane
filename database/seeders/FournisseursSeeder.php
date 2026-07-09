<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Illuminate\Database\Seeder;

class FournisseursSeeder extends Seeder
{
    public function run(): void
    {
        $fournisseurs = [
            [
                'name'    => 'Boucherie El Amine',
                'phone'   => '0522-441-230',
                'email'   => 'elamine.boucherie@gmail.com',
                'address' => 'Derb Sultan, Casablanca',
            ],
            [
                'name'    => 'Épicerie Centrale du Souk',
                'phone'   => '0537-221-875',
                'email'   => 'epicerie.souk@hotmail.com',
                'address' => 'Souk El Had, Rabat',
            ],
            [
                'name'    => 'Laiterie Atlas',
                'phone'   => '0528-889-100',
                'email'   => 'contact@laiterie-atlas.ma',
                'address' => 'Zone Industrielle Ait Melloul, Agadir',
            ],
            [
                'name'    => 'Boulangerie & Pâtisserie Alaoui',
                'phone'   => '0524-334-567',
                'email'   => 'alaoui.boulangerie@gmail.com',
                'address' => 'Guéliz, Marrakech',
            ],
            [
                'name'    => 'Marché de légumes Derb Sultan',
                'phone'   => '0661-778-902',
                'email'   => null,
                'address' => 'Marché Municipal Derb Sultan, Casablanca',
            ],
            [
                'name'    => 'Épices & Arômes Fès',
                'phone'   => '0535-634-212',
                'email'   => 'epices.fes@gmail.com',
                'address' => 'Médina de Fès, Fès',
            ],
            [
                'name'    => 'Poissonnerie Atlantique',
                'phone'   => '0539-943-301',
                'email'   => 'atlantique.poisson@gmail.com',
                'address' => 'Port de pêche, Tanger',
            ],
        ];

        foreach ($fournisseurs as $f) {
            Fournisseur::create($f);
        }

        $this->command->info('✔ FournisseursSeeder : ' . count($fournisseurs) . ' fournisseurs créés.');
    }
}
