<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('UsersSeeder ignoré en production. Créez les comptes via une procédure sécurisée.');

            return;
        }

        $users = [
            // ── Administration ────────────────────────────────────────────
            [
                'name'     => 'Hassan Alaoui',
                'username' => 'admin',
                'role'     => 'admin',
                'status'   => 'active',
            ],

            // ── Caissiers ─────────────────────────────────────────────────
            [
                'name'     => 'Fatima Zahra Benali',
                'username' => 'caissier1',
                'role'     => 'caissier',
                'status'   => 'active',
            ],
            [
                'name'     => 'Khadija Moussaoui',
                'username' => 'caissier2',
                'role'     => 'caissier',
                'status'   => 'active',
            ],

            // ── Serveurs ──────────────────────────────────────────────────
            [
                'name'     => 'Youssef El Fassi',
                'username' => 'serveur1',
                'role'     => 'serveur',
                'status'   => 'active',
            ],
            [
                'name'     => 'Amine Berrada',
                'username' => 'serveur2',
                'role'     => 'serveur',
                'status'   => 'active',
            ],
            [
                'name'     => 'Nadia Tazi',
                'username' => 'serveur3',
                'role'     => 'serveur',
                'status'   => 'active',
            ],
        ];

        $credentials = [];
        $generatedPins = [];
        $existingPasswordHashes = User::whereNotIn('username', array_column($users, 'username'))
            ->pluck('password');

        foreach ($users as $user) {
            do {
                $pin = (string) random_int(10_000_000, 99_999_999);
            } while (
                in_array($pin, $generatedPins, true)
                || SuperAdmin::matchesPin($pin)
                || $existingPasswordHashes->contains(
                    fn (string $passwordHash): bool => Hash::check($pin, $passwordHash),
                )
            );

            $generatedPins[] = $pin;

            User::updateOrCreate(
                ['username' => $user['username']],
                [
                    ...$user,
                    'password' => Hash::make($pin),
                    'force_password_reset' => true,
                ],
            );

            $credentials[] = [$user['username'], $pin, $user['role']];
        }

        $this->command?->info('UsersSeeder : identifiants temporaires générés. Ils seront remplacés au prochain seed.');
        $this->command?->table(['Utilisateur', 'PIN temporaire', 'Rôle'], $credentials);
    }
}
