<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RoomServiceController extends Controller
{
    protected string $menuFilePath;
    protected string $ordersFilePath;

    public function __construct()
    {
        $this->menuFilePath = storage_path('app/room_service_menu.json');
        $this->ordersFilePath = storage_path('app/room_service_orders.json');

        // Ensure storage directory exists
        if (!File::exists(storage_path('app'))) {
            File::makeDirectory(storage_path('app'), 0755, true);
        }

        // Initialize files if they don't exist
        $this->initializeMenuFile();
        $this->initializeOrdersFile();
    }

    /**
     * Get pre-defined rooms list.
     */
    public function getRooms(): array
    {
        return [
            ['id' => 1, 'number' => '101', 'token' => 'tok101', 'status' => 'active'],
            ['id' => 2, 'number' => '102', 'token' => 'tok102', 'status' => 'active'],
            ['id' => 3, 'number' => '103', 'token' => 'tok103', 'status' => 'active'],
            ['id' => 4, 'number' => '104', 'token' => 'tok104', 'status' => 'active'],
            ['id' => 5, 'number' => '201', 'token' => 'tok201', 'status' => 'active'],
            ['id' => 6, 'number' => '202', 'token' => 'tok202', 'status' => 'active'],
            ['id' => 7, 'number' => '203', 'token' => 'tok203', 'status' => 'active'],
            ['id' => 8, 'number' => '204', 'token' => 'tok204', 'status' => 'active'],
            ['id' => 9, 'number' => '301', 'token' => 'tok301', 'status' => 'active'],
            ['id' => 10, 'number' => '302', 'token' => 'tok302', 'status' => 'active'],
            ['id' => 11, 'number' => '303', 'token' => 'tok303', 'status' => 'active'],
            ['id' => 12, 'number' => '304', 'token' => 'tok304', 'status' => 'active'],
        ];
    }

    /**
     * Guest menu interface.
     */
    public function guestMenu(Request $request)
    {
        $roomNumber = $request->query('room');
        $token = $request->query('token');

        $rooms = $this->getRooms();
        $selectedRoom = null;

        // Find room if code was scanned
        if ($roomNumber) {
            foreach ($rooms as $r) {
                if ($r['number'] == $roomNumber && $r['token'] == $token) {
                    $selectedRoom = $r;
                    break;
                }
            }
        }

        // Get DB items to enrich room service menu dynamically
        $dbCategories = Category::active()->with('produits')->get();
        
        // Load custom room service menu items (CRUD items)
        $menuItems = $this->getMenuItems();

        // If no custom items exist, seed some dynamically from DB or defaults
        if (count($menuItems) === 0) {
            $this->seedDefaultMenuItems($dbCategories);
            $menuItems = $this->getMenuItems();
        }

        // Extract unique categories for filtering chips
        $categories = collect($menuItems)->pluck('category')->unique()->values()->all();

        return view('room-service.menu', compact('selectedRoom', 'rooms', 'menuItems', 'categories'));
    }

    /**
     * Guest submit order.
     */
    public function submitOrder(Request $request)
    {
        $request->validate([
            'room_number' => 'required',
            'delivery_time' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.temperature' => 'required|string',
            'items.*.customizations' => 'nullable|array',
            'items.*.guest' => 'required|string',
        ]);

        $orders = $this->getOrders();

        $newOrder = [
            'id' => 'RS-' . strtoupper(Str::random(5)),
            'room_number' => $request->room_number,
            'delivery_time' => $request->delivery_time,
            'status' => 'pending', // pending, approved, preparing, ready, delivered
            'total' => collect($request->items)->sum(fn($i) => $i['price'] * $i['quantity']),
            'items' => $request->items,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $orders[] = $newOrder;
        $this->saveOrders($orders);

        return response()->json([
            'success' => true,
            'order_id' => $newOrder['id'],
            'message' => 'Votre commande a été envoyée avec succès.'
        ]);
    }

    /**
     * Guest success page & tracking.
     */
    public function orderSuccess($orderId)
    {
        $orders = $this->getOrders();
        $order = collect($orders)->firstWhere('id', $orderId);

        if (!$order) {
            abort(404, "Commande introuvable");
        }

        return view('room-service.success', compact('order'));
    }

    /**
     * JSON Endpoint for order status polling.
     */
    public function getOrderStatus($orderId)
    {
        $orders = $this->getOrders();
        $order = collect($orders)->firstWhere('id', $orderId);

        if (!$order) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'status' => $order['status']
        ]);
    }

    /**
     * Admin dashboard room service page.
     */
    public function adminIndex()
    {
        $orders = $this->getOrders();
        $menuItems = $this->getMenuItems();

        // Categorize orders
        $pendingOrders = collect($orders)->where('status', 'pending')->sortByDesc('created_at')->values()->all();
        $activeOrders = collect($orders)->whereIn('status', ['approved', 'preparing', 'ready'])->sortByDesc('created_at')->values()->all();
        $deliveredOrders = collect($orders)->where('status', 'delivered')->sortByDesc('created_at')->values()->all();

        return view('settings.room-service.index', compact('pendingOrders', 'activeOrders', 'deliveredOrders', 'menuItems'));
    }

    /**
     * QR Codes Page (كود باك).
     */
    public function qrCodesIndex()
    {
        $rooms = $this->getRooms();
        return view('settings.room-service.qr-codes', compact('rooms'));
    }

    /**
     * Store new menu item (Admin CRUD).
     */
    public function storeMenuItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string', // Category name (e.g. Tajines)
            'meal_type' => 'required|string', // Breakfast, Lunch/Dinner
            'requires_advance_time' => 'nullable|boolean',
            'allow_temperature' => 'nullable|boolean',
            'customizations' => 'nullable|string', // comma-separated
            'image_url' => 'nullable|string',
        ]);

        $items = $this->getMenuItems();

        $customizations = [];
        if ($request->customizations) {
            $customizations = array_filter(array_map('trim', explode(',', $request->customizations)));
        }

        $newItem = [
            'id' => uniqid(),
            'name' => $request->name,
            'description' => $request->description ?? '',
            'price' => (float) $request->price,
            'category' => $request->category,
            'meal_type' => $request->meal_type,
            'requires_advance_time' => $request->has('requires_advance_time') || $request->requires_advance_time == 1,
            'allow_temperature' => $request->has('allow_temperature') || $request->allow_temperature == 1,
            'customizations' => array_values($customizations),
            'image_url' => $request->image_url ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
            'available' => true,
        ];

        $items[] = $newItem;
        $this->saveMenuItems($items);

        return back()->with('success', 'Article de menu ajouté avec succès !');
    }

    /**
     * Update menu item (Admin CRUD).
     */
    public function updateMenuItem(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'meal_type' => 'required|string',
            'requires_advance_time' => 'nullable|boolean',
            'allow_temperature' => 'nullable|boolean',
            'customizations' => 'nullable|string',
            'image_url' => 'nullable|string',
            'available' => 'nullable|boolean',
        ]);

        $items = $this->getMenuItems();
        $updated = false;

        $customizations = [];
        if ($request->customizations) {
            $customizations = array_filter(array_map('trim', explode(',', $request->customizations)));
        }

        foreach ($items as &$item) {
            if ($item['id'] == $id) {
                $item['name'] = $request->name;
                $item['description'] = $request->description ?? '';
                $item['price'] = (float) $request->price;
                $item['category'] = $request->category;
                $item['meal_type'] = $request->meal_type;
                $item['requires_advance_time'] = $request->has('requires_advance_time') || $request->requires_advance_time == 1;
                $item['allow_temperature'] = $request->has('allow_temperature') || $request->allow_temperature == 1;
                $item['customizations'] = array_values($customizations);
                if ($request->image_url) {
                    $item['image_url'] = $request->image_url;
                }
                $item['available'] = $request->has('available') ? ($request->available == 1) : $item['available'];
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveMenuItems($items);
            return back()->with('success', 'Article de menu mis à jour !');
        }

        return back()->with('error', 'Article introuvable.');
    }

    /**
     * Delete menu item (Admin CRUD).
     */
    public function deleteMenuItem($id)
    {
        $items = $this->getMenuItems();
        $filtered = collect($items)->filter(fn($item) => $item['id'] != $id)->values()->all();

        if (count($items) !== count($filtered)) {
            $this->saveMenuItems($filtered);
            return back()->with('success', 'Article de menu supprimé.');
        }

        return back()->with('error', 'Article introuvable.');
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,preparing,ready,delivered'
        ]);

        $orders = $this->getOrders();
        $updated = false;

        foreach ($orders as &$order) {
            if ($order['id'] == $orderId) {
                $order['status'] = $request->status;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveOrders($orders);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Order not found'], 404);
    }

    /**
     * Delete order.
     */
    public function deleteOrder($orderId)
    {
        $orders = $this->getOrders();
        $filtered = collect($orders)->filter(fn($order) => $order['id'] != $orderId)->values()->all();

        if (count($orders) !== count($filtered)) {
            $this->saveOrders($filtered);
            return back()->with('success', 'Commande supprimée avec succès.');
        }

        return back()->with('error', 'Commande introuvable.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    private function getMenuItems(): array
    {
        if (!File::exists($this->menuFilePath)) {
            return [];
        }
        return json_decode(File::get($this->menuFilePath), true) ?: [];
    }

    private function saveMenuItems(array $items): void
    {
        File::put($this->menuFilePath, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getOrders(): array
    {
        if (!File::exists($this->ordersFilePath)) {
            return [];
        }
        return json_decode(File::get($this->ordersFilePath), true) ?: [];
    }

    private function saveOrders(array $orders): void
    {
        File::put($this->ordersFilePath, json_encode(array_values($orders), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function initializeMenuFile(): void
    {
        if (!File::exists($this->menuFilePath)) {
            File::put($this->menuFilePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    private function initializeOrdersFile(): void
    {
        if (!File::exists($this->ordersFilePath)) {
            File::put($this->ordersFilePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Seed initial mock items using database products/categories if available,
     * otherwise using hardcoded luxury Moroccan food in French.
     */
    private function seedDefaultMenuItems($dbCategories): void
    {
        $defaults = [];

        // Try to read products from DB to make them matching the actual app
        if ($dbCategories->count() > 0) {
            foreach ($dbCategories as $cat) {
                $isLunchDinner = in_array($cat->name, ['Tajines', 'Couscous', 'Grillades & Méchoui', 'Pastilla & Spécialités']);
                
                foreach ($cat->produits->take(3) as $prod) {
                    $customizations = ["Sans sel", "Sans épices"];
                    if ($cat->name === 'Tajines' || $cat->name === 'Couscous') {
                        $customizations[] = "Sans légumes";
                        $customizations[] = "Extra fromage";
                    } elseif ($cat->name === 'Boissons') {
                        $customizations = ["Sans sucre", "Avec glaçons"];
                    }

                    $defaults[] = [
                        'id' => uniqid(),
                        'name' => $prod->name,
                        'description' => $cat->description ?: 'Plat savoureux préparé par nos chefs.',
                        'price' => (float) $prod->price_vente,
                        'category' => $cat->name, // Category name (e.g. Tajines)
                        'meal_type' => $isLunchDinner ? 'Lunch/Dinner' : 'Breakfast',
                        'requires_advance_time' => $isLunchDinner,
                        'allow_temperature' => in_array($cat->name, ['Boissons', 'Soupes & Harira']),
                        'customizations' => $customizations,
                        'image_url' => $prod->display_image_url,
                        'available' => true,
                    ];
                }
            }
        }

        // Add additional fallback default items if empty (French representation)
        if (count($defaults) === 0) {
            $defaults = [
                // Petit déjeuner
                [
                    'id' => uniqid(),
                    'name' => 'Petit Déjeuner Beldi Marocain',
                    'description' => 'Msemen, Harcha, Batbout, miel d’argan, fromage jben, olives noires, thé à la menthe.',
                    'price' => 45.00,
                    'category' => 'Petit Déjeuner',
                    'meal_type' => 'Breakfast',
                    'requires_advance_time' => false,
                    'allow_temperature' => true,
                    'customizations' => ['Sans sel', 'Sans olives', 'Miel extra', 'Sans beurre'],
                    'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                [
                    'id' => uniqid(),
                    'name' => 'Msemen Chaud au Miel',
                    'description' => 'Crêpe marocaine feuilletée servie chaude avec du beurre et du miel sauvage.',
                    'price' => 15.00,
                    'category' => 'Petit Déjeuner',
                    'meal_type' => 'Breakfast',
                    'requires_advance_time' => false,
                    'allow_temperature' => false,
                    'customizations' => ['Sans beurre', 'Sans miel'],
                    'image_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                // Lunch/Dinner
                [
                    'id' => uniqid(),
                    'name' => 'Tajine de Poulet aux Olives & Citron',
                    'description' => 'Poulet fermier mijoté aux épices, olives violettes et citron confit de Marrakech.',
                    'price' => 75.00,
                    'category' => 'Tajines',
                    'meal_type' => 'Lunch/Dinner',
                    'requires_advance_time' => true,
                    'allow_temperature' => false,
                    'customizations' => ['Sans sel', 'Sans épices', 'Sans olives', 'Sans citron'],
                    'image_url' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                [
                    'id' => uniqid(),
                    'name' => 'Tajine d’Agneau aux Pruneaux',
                    'description' => 'Agneau tendre mijoté avec des pruneaux caramélisés, des amandes grillées et sésame.',
                    'price' => 85.00,
                    'category' => 'Tajines',
                    'meal_type' => 'Lunch/Dinner',
                    'requires_advance_time' => true,
                    'allow_temperature' => false,
                    'customizations' => ['Sans sel', 'Sans épices', 'Sans amandes', 'Sans pruneaux'],
                    'image_url' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                [
                    'id' => uniqid(),
                    'name' => 'Couscous Royal aux Sept Légumes',
                    'description' => 'Semoule fine cuite à la vapeur, viandes d’agneau, poulet, merguez et légumes frais du jour.',
                    'price' => 95.00,
                    'category' => 'Couscous',
                    'meal_type' => 'Lunch/Dinner',
                    'requires_advance_time' => true,
                    'allow_temperature' => false,
                    'customizations' => ['Sans sel', 'Sans épices', 'Sans légumes', 'Sans pois chiches'],
                    'image_url' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                // Drinks
                [
                    'id' => uniqid(),
                    'name' => 'Thé à la Menthe Traditionnel',
                    'description' => 'Le fameux thé marocain infusé à la menthe fraîche "Naanaâ".',
                    'price' => 15.00,
                    'category' => 'Boissons',
                    'meal_type' => 'Breakfast',
                    'requires_advance_time' => false,
                    'allow_temperature' => true,
                    'customizations' => ['Sans sucre', 'Menthe extra'],
                    'image_url' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
                [
                    'id' => uniqid(),
                    'name' => 'Jus d’Orange Frais',
                    'description' => 'Jus d’oranges pressées à la minute, frais et vitaminé.',
                    'price' => 20.00,
                    'category' => 'Boissons',
                    'meal_type' => 'Breakfast',
                    'requires_advance_time' => false,
                    'allow_temperature' => false,
                    'customizations' => ['Sans sucre', 'Avec glaçons'],
                    'image_url' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=400&q=80',
                    'available' => true,
                ],
            ];
        }

        $this->saveMenuItems($defaults);
    }
}
