<?php

namespace Database\Seeders;

use App\Models\Documentation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define common roles
        $admin = ['admin'];
        $staff = ['admin', 'caissier', 'serveur'];
        $kitchen = ['admin', 'serveur'];

        $docs = [
            // --- PAGES: POS ---
            [
                'title' => 'Guide du Point de Vente (POS)',
                'category' => 'POS',
                'icon' => 'fas fa-cash-register',
                'order' => 1,
                'visible_to_roles' => $staff,
                'content' => '
# Utilisation du POS

Le module POS est conçu pour la rapidité.

## Écran de Prise de Commande
Divisé en 3 colonnes :
1. **Catégories** : Filtrez les produits (Boissons, Plats...).
2. **Grille Produits** : Cliquez pour ajouter au panier.
3. **Ticket actuel** : Résumé de la commande.

## Actions Clés
- **Ajout rapide** : Cliquez sur un produit.
- **Quantité** : Utilisez les boutons `+` et `-` dans le ticket.
- **Notes** : Ajoutez une instruction (ex: "Sans glace") via l\'icône crayon.
- **Client** : Recherchez un client existant ou créez-en un nouveau.

> **Astuce** : Pour mettre une commande en attente, cliquez sur "Table" puis sélectionnez la table.
                ',
            ],
            // --- PAGES: KITCHEN ---
            [
                'title' => 'L\'Écran Cuisine (KDS)',
                'category' => 'Cuisine',
                'icon' => 'fas fa-utensils',
                'order' => 2,
                'visible_to_roles' => $kitchen,
                'content' => '
# Kitchen Display System (KDS)

Remplacez les tickets papier par un écran interactif.

## Fonctionnement
- Les nouvelles commandes arrivent avec un son "Ding".
- **Codes Couleurs** :
  - <span class="text-green-600 font-bold">Vert</span> : Nouvelle commande.
  - <span class="text-orange-500 font-bold">Orange</span> : En préparation (> 5min).
  - <span class="text-red-600 font-bold">Rouge</span> : En retard (> 15min).

## Workflow Cuisinier
1. Lire la commande.
2. Préparer les plats.
3. Cliquer sur **"PRÊT"** une fois terminé.
4. Le serveur reçoit une notification.
                ',
            ],
            // --- WORKFLOWS: ORDER CYCLE ---
            [
                'title' => 'Cycle de Vie d\'une Commande',
                'category' => 'Workflows',
                'icon' => 'fas fa-sync-alt',
                'order' => 3,
                'visible_to_roles' => $staff,
                'content' => '
# De la prise de commande à l\'encaissement

1. **Création** (POS) : Le serveur saisit la commande.
2. **Envoi** (Cuisine) : La commande part en production sur le KDS.
3. **Préparation** (Cuisine) : Le chef marque les plats comme "Prêts".
4. **Service** (Salle) : Le serveur apporte les plats.
5. **Encaissement** (Caisse) : Le client paie (Espèces, Carte, Mobile).

## Cas Particuliers
- **Annulation** : Possible tant que la commande n\'est pas payée (nécessite permission Admin pour lignes envoyées).
- **Remboursement** : Via le menu Historique > Détails > Rembourser.
                ',
            ],
            // --- ROLES ---
            [
                'title' => 'Guide des Rôles & Permissions',
                'category' => 'Rôles',
                'icon' => 'fas fa-user-tag',
                'order' => 4,
                'visible_to_roles' => $admin,
                'content' => '
# Qui fait quoi ?

### Administrateur
- Accès complet à tout le système.
- Peut voir les rapports financiers (Chiffre d\'affaires, Marge).
- Peut gérer les utilisateurs et les réglages système.

### Caissier
- Accès au POS pour encaisser.
- Gestion des fonds de caisse (Ouverture/Fermeture).
- Historique de ses propres ventes.
- **Interdit** : Modification de produits, Suppression d\'historique global.

### Serveur
- Prise de commande uniquement.
- Pas d\'accès à l\'encaissement (sauf si option "Serveur Encaisse" activée).
- Pas d\'accès aux rapports.
                ',
            ],
            // --- CONFIGURATION ---
            [
                'title' => 'Modes & Configuration',
                'category' => 'Configuration',
                'icon' => 'fas fa-sliders-h',
                'order' => 5,
                'visible_to_roles' => $admin,
                'content' => '
# Adapter techMizane à votre commerce

## Mode Restaurant vs Café
- **Mode Restaurant** : Active la gestion des tables, plans de salle, et service à table.
- **Mode Café/Fast-food** : Désactive les tables. Flux direct "Commande -> Paiement".

## Gestion des Stocks
- **Inventaire Simple** : Décompte automatique à la vente.
- **Alertes Stock Bas** : Configuration des seuils d\'alerte par produit.
                ',
            ]
        ];

        foreach ($docs as $doc) {
            Documentation::updateOrCreate(
                ['slug' => Str::slug($doc['title'])],
                [
                    'title' => $doc['title'],
                    'content' => $doc['content'], // In a real app, use Markdown parser if needed or keep raw
                    'category' => $doc['category'], 
                    'visible_to_roles' => $doc['visible_to_roles'],
                    'order' => $doc['order'],
                    'icon' => $doc['icon'],
                ]
            );
        }
    }
}
