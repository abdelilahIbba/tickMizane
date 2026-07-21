<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UsersSeeder - تهيئة بيانات المستخدمين
 *
 * ينشئ حسابات المستخدمين الافتراضيين للنظام:
 * - مديران (admin): omar, hisham
 * - صناديق (caissier): caissier1, caissier2
 * - نادلون (serveur): mohamed, asmaa
 *
 * يستخدم updateOrCreate لتجنب التكرار عند إعادة التشغيل
 */
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Administration ────────────────────────────────────────────
            [
                'name'     => 'Omar',
                'username' => 'omar',
                'password' => Hash::make('Admin@2024'),
                'role'     => 'admin',
                'status'   => 'active',
            ],
            [
                'name'     => 'Hisham',
                'username' => 'hisham',
                'password' => Hash::make('Admin@2024'),
                'role'     => 'admin',
                'status'   => 'active',
            ],

            // ── Caissiers ─────────────────────────────────────────────────
            [
                'name'     => 'Fatima Zahra',
                'username' => 'caissier1',
                'password' => Hash::make('Caisse@123'),
                'role'     => 'caissier',
                'status'   => 'active',
            ],
            [
                'name'     => 'Khadija',
                'username' => 'caissier2',
                'password' => Hash::make('Caisse@123'),
                'role'     => 'caissier',
                'status'   => 'active',
            ],

            // ── Serveurs ──────────────────────────────────────────────────
            [
                'name'     => 'Mohamed',
                'username' => 'mohamed',
                'password' => Hash::make('Serveur@123'),
                'role'     => 'serveur',
                'status'   => 'active',
            ],
            [
                'name'     => 'Asmaa',
                'username' => 'asmaa',
                'password' => Hash::make('Serveur@123'),
                'role'     => 'serveur',
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }

        $this->command->info('✔ UsersSeeder : ' . count($users) . ' utilisateurs créés.');
    }
}
