<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Table;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteDetail;

class DashboardStatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer les données existantes avant de générer
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        VenteDetail::truncate();
        Vente::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $caissiers = User::whereIn('role', ['caissier', 'admin'])->pluck('id');
        $tables = Table::pluck('id');
        $produits = Produit::all();
        
        if ($caissiers->isEmpty() || $produits->isEmpty()) {
            $this->command->warn('Veuillez d\'abord lancer DatabaseSeeder pour avoir des utilisateurs et des produits.');
            return;
        }

        $paymentMethods = ['cash', 'cash', 'cash', 'carte', 'carte', 'mixte'];

        $this->command->info('Génération des ventes pour les 6 derniers mois...');

        // 1. Générer des données pour les 6 derniers mois (Revenus Mensuels)
        for ($i = 6; $i >= 1; $i--) {
            $daysInMonth = Carbon::now()->subMonths($i)->daysInMonth;
            
            // Generate some random sales for this month
            $salesCount = rand(50, 100);
            for ($s = 0; $s < $salesCount; $s++) {
                $randomDay = rand(1, $daysInMonth);
                $randomHour = rand(8, 22);
                $randomMinute = rand(0, 59);
                
                $date = Carbon::now()->subMonths($i)->day($randomDay)->hour($randomHour)->minute($randomMinute);
                $this->createRandomSale($date, $caissiers, $tables, $produits, $paymentMethods);
            }
        }

        $this->command->info('Génération des ventes pour les 7 derniers jours...');

        // 2. Générer des données riches pour les 7 derniers jours (Ventes Hebdomadaires)
        for ($i = 7; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // More sales for recent days!
            $salesCount = rand(15, 35);
            
            // Plus de ventes le week-end
            if ($date->isWeekend()) {
                $salesCount += rand(10, 20);
            }
            
            for ($s = 0; $s < $salesCount; $s++) {
                $randomHour = rand(8, 22);
                $randomMinute = rand(0, 59);
                $saleDate = $date->copy()->hour($randomHour)->minute($randomMinute);
                
                $this->createRandomSale($saleDate, $caissiers, $tables, $produits, $paymentMethods);
            }
        }

        $this->command->info('Génération des ventes pour aujourd\'hui (Ventes Horaires)...');

        // 3. Générer des données pour aujourd'hui, heure par heure (Répartition Horaire)
        $currentHour = Carbon::now()->hour;
        for ($h = 8; $h <= min($currentHour, 22); $h++) {
            // Peak hours 12h-14h and 19h-21h
            $isPeakHour = ($h >= 12 && $h <= 14) || ($h >= 19 && $h <= 21);
            $salesInHour = $isPeakHour ? rand(5, 15) : rand(1, 6);
            
            for ($s = 0; $s < $salesInHour; $s++) {
                $randomMinute = rand(0, 59);
                $saleDate = Carbon::today()->hour($h)->minute($randomMinute);
                
                $this->createRandomSale($saleDate, $caissiers, $tables, $produits, $paymentMethods);
            }
        }

        $this->command->info('Génération terminée avec succès !');
    }

    private function createRandomSale($date, $caissiers, $tables, $produits, $paymentMethods)
    {
        $vente = Vente::create([
            'user_id' => $caissiers->random(),
            'table_id' => rand(1, 100) > 30 ? $tables->random() : null, // 70% chance to be connected to a table
            'total' => 0,
            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
            'status' => 'paid',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $itemCount = rand(1, 5);
        $total = 0;
        
        $shuffledProducts = $produits->shuffle()->take($itemCount);

        foreach ($shuffledProducts as $produit) {
            $qty = rand(1, 3);
            $lineTotal = $produit->price_vente * $qty;
            $total += $lineTotal;

            VenteDetail::create([
                'vente_id' => $vente->id,
                'produit_id' => $produit->id,
                'quantity' => $qty,
                'price' => $produit->price_vente,
                'total_line' => $lineTotal,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $vente->update(['total' => $total]);
    }
}
