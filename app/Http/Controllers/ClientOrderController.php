<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Produit;
use App\Events\NewKitchenOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientOrderController extends Controller
{
    public function menu(Request $request)
    {
        $locationType = $request->query('type');

        $categories = Category::where('status', 'active')
            ->with(['produits' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        $menuData = $categories->map(function ($cat) {
            return [
                'id'       => $cat->id,
                'name'     => $cat->name,
                'products' => $cat->produits->map(fn ($p) => [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'price' => (float) $p->price_vente,
                    'image' => $p->display_image_url,
                    'unit'  => $p->unit ?? 'pcs',
                ])->values(),
            ];
        })->values();

        return view('client.order', [
            'menuData'     => $menuData,
            'locationType' => $locationType,
        ]);
    }

    public function submitOrder(Request $request)
    {
        $rules = [
            'location_type'  => 'required|in:restaurant,pool,room',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|integer|exists:produits,id',
            'items.*.qty'    => 'required|integer|min:1|max:100',
            'phone'          => 'required|string|max:30',
            'order_notes'    => 'nullable|string|max:500',
        ];

        if ($request->input('location_type') === 'restaurant') {
            $rules['table_number'] = 'required|string|max:20';
        } elseif ($request->input('location_type') === 'room') {
            $rules['room_number']  = 'required|string|max:30';
            $rules['client_name']  = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        try {
            $commande = DB::transaction(function () use ($validated, $request) {
                $type = $validated['location_type'];

                $phone      = $request->input('phone', '');
                $extraNotes = $request->input('order_notes', '');

                $notes = match ($type) {
                    'restaurant' => 'Commande client - Table n ' . $request->input('table_number')
                                  . ' | Tel: ' . $phone,
                    'pool'       => 'Commande client - Piscine'
                                  . ' | Tel: ' . $phone,
                    'room'       => 'Room service - Chambre n ' . $request->input('room_number')
                                  . ' - ' . $request->input('client_name')
                                  . ' | Tel: ' . $phone
                                  . ' | Livraison estimee : 2h',
                };

                if ($extraNotes) {
                    $notes .= ' | Notes: ' . $extraNotes;
                }

                $hasKitchenItems = false;
                $resolvedItems   = [];

                foreach ($validated['items'] as $item) {
                    $produit = Produit::findOrFail($item['id']);
                    $resolvedItems[] = [$item, $produit];
                    if ($produit->isKitchenActive()) {
                        $hasKitchenItems = true;
                    }
                }

                $commande = Commande::create([
                    'user_id'      => null,
                    'table_id'     => null,
                    'total'        => 0,
                    'status'       => $hasKitchenItems ? 'en_cuisine' : 'pret',
                    'type'         => 'kitchen',
                    'waiter_notes' => $notes,
                ]);

                $total = 0;

                foreach ($resolvedItems as [$item, $produit]) {
                    CommandeDetail::create([
                        'commande_id' => $commande->id,
                        'produit_id'  => $produit->id,
                        'quantity'    => $item['qty'],
                        'price'       => $produit->price_vente,
                        'notes'       => null,
                    ]);
                    $total += $produit->price_vente * $item['qty'];
                }

                $commande->update(['total' => $total]);

                if (! $hasKitchenItems) {
                    $commande->update(['ready_at' => now()]);
                }

                if ($hasKitchenItems) {
                    event(new NewKitchenOrder($commande));
                }

                return $commande;
            });

            return response()->json([
                'success'  => true,
                'order_id' => $commande->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la creation de la commande.',
            ], 500);
        }
    }
}