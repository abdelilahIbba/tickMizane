<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Administration ────────────────────────────────────────────
            [
                'name'     => 'Hassan Alaoui',
                'username' => 'admin',
                'password' => Hash::make('Admin@2024'),
                'role'     => 'admin',
                'status'   => 'active',
            ],

            // ── Caissiers ─────────────────────────────────────────────────
            [
                'name'     => 'Fatima Zahra Benali',
                'username' => 'caissier1',
                'password' => Hash::make('Caisse@123'),
                'role'     => 'caissier',
                'status'   => 'active',
            ],
            [
                'name'     => 'Khadija Moussaoui',
                'username' => 'caissier2',
                'password' => Hash::make('Caisse@123'),
                'role'     => 'caissier',
                'status'   => 'active',
            ],

            // ── Serveurs ──────────────────────────────────────────────────
            [
                'name'     => 'Youssef El Fassi',
                'username' => 'serveur1',
                'password' => Hash::make('Serveur@123'),
                'role'     => 'serveur',
                'status'   => 'active',
            ],
            [
                'name'     => 'Amine Berrada',
                'username' => 'serveur2',
                'password' => Hash::make('Serveur@123'),
                'role'     => 'serveur',
                'status'   => 'active',
            ],
            [
                'name'     => 'Nadia Tazi',
                'username' => 'serveur3',
                'password' => Hash::make('Serveur@123'),
                'role'     => 'serveur',
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('✔ UsersSeeder : ' . count($users) . ' utilisateurs créés.');
    }
}
