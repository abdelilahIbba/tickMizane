<x-layout.app title="Produits">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Produits</h1>
                <p class="text-gray-400 mt-1">Gérez votre catalogue de produits</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('products.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau produit
            </x-ui.button>
        </div>
        
        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-4">
            <x-form.select 
                name="category" 
                placeholder="Toutes les catégories"
                :options="['1' => 'Boissons', '2' => 'Snacks', '3' => 'Épicerie', '4' => 'Hygiène']"
                class="w-48"
            />
            <x-form.select 
                name="status" 
                placeholder="Tous les statuts"
                :options="['active' => 'Actif', 'archived' => 'Archivé']"
                class="w-40"
            />
            <x-form.input 
                type="text" 
                name="search" 
                placeholder="Rechercher..."
                class="w-64"
            />
        </div>
        
        {{-- Success Alert --}}
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        
        {{-- Products Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['Produit', 'Catégorie', 'Prix vente', 'Unité', 'Stock', 'Statut', 'Actions']">
                @foreach([
                    ['id' => 1, 'name' => 'Eau minérale 1.5L', 'category' => 'Boissons', 'price' => 5.00, 'unit' => 'Bouteille', 'stock' => 3, 'alert' => 10, 'status' => 'active'],
                    ['id' => 2, 'name' => 'Coca-Cola 33cl', 'category' => 'Boissons', 'price' => 8.00, 'unit' => 'Canette', 'stock' => 45, 'alert' => 20, 'status' => 'active'],
                    ['id' => 3, 'name' => 'Chips Lays 150g', 'category' => 'Snacks', 'price' => 15.00, 'unit' => 'Paquet', 'stock' => 28, 'alert' => 15, 'status' => 'active'],
                    ['id' => 4, 'name' => 'Café moulu 250g', 'category' => 'Épicerie', 'price' => 45.00, 'unit' => 'Paquet', 'stock' => 5, 'alert' => 10, 'status' => 'active'],
                    ['id' => 5, 'name' => 'Sucre 1kg', 'category' => 'Épicerie', 'price' => 12.00, 'unit' => 'Kg', 'stock' => 2, 'alert' => 5, 'status' => 'active'],
                    ['id' => 6, 'name' => 'Shampooing 400ml', 'category' => 'Hygiène', 'price' => 35.00, 'unit' => 'Flacon', 'stock' => 18, 'alert' => 8, 'status' => 'archived'],
                ] as $product)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-700 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $product['name'] }}</p>
                                    <p class="text-gray-500 text-sm">#{{ str_pad($product['id'], 4, '0', STR_PAD_LEFT) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $product['category'] }}</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($product['price'], 2) }} DH</td>
                        <td class="px-6 py-4 text-gray-300">{{ $product['unit'] }}</td>
                        <td class="px-6 py-4">
                            @if($product['stock'] < $product['alert'])
                                <span class="text-red-400 font-semibold">{{ $product['stock'] }}</span>
                                <span class="text-red-400/60 text-xs ml-1">(alerte: {{ $product['alert'] }})</span>
                            @else
                                <span class="text-gray-300">{{ $product['stock'] }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($product['status'] === 'active')
                                <x-ui.badge variant="success">Actif</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Archivé</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button 
                                    variant="ghost" 
                                    size="sm"
                                    x-data
                                    @click="$dispatch('open-modal-stock-{{ $product['id'] }}')"
                                >
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm" href="{{ route('products.edit', $product['id']) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
    
    {{-- Stock Update Modal --}}
    <x-ui.modal id="stock-1" title="Ajuster le stock" size="sm">
        <form class="space-y-4">
            <x-form.input 
                type="number" 
                name="quantity" 
                label="Quantité" 
                placeholder="Entrez la quantité"
            />
            <x-form.select 
                name="type" 
                label="Type de mouvement"
                :options="['in' => 'Entrée (achat)', 'out' => 'Sortie (perte)']"
            />
            <x-form.textarea 
                name="reason" 
                label="Raison" 
                placeholder="Raison du mouvement..."
                rows="2"
            />
        </form>
        <x-slot:footer>
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal-stock-1')">
                Annuler
            </x-ui.button>
            <x-ui.button variant="primary">
                Enregistrer
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</x-layout.app>
