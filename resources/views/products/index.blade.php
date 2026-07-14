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
        <form method="GET" action="{{ route('products.index') }}" class="mb-6 flex flex-wrap gap-4">
            <x-form.select 
                name="category_id" 
                placeholder="Toutes les catégories"
                :options="$categories->pluck('name', 'id')->toArray()"
                :selected="request('category_id')"
                class="w-48"
            />
            <x-form.select 
                name="status" 
                placeholder="Tous les statuts"
                :options="['active' => 'Actif', 'inactive' => 'Inactif']"
                :selected="request('status')"
                class="w-40"
            />
            <x-form.input 
                type="text" 
                name="search" 
                placeholder="Rechercher..."
                :value="request('search')"
                class="w-64"
            />
            <x-ui.button type="submit" variant="secondary">Filtrer</x-ui.button>
            @if(request()->hasAny(['category_id', 'status', 'search']))
                <x-ui.button variant="ghost" href="{{ route('products.index') }}">Réinitialiser</x-ui.button>
            @endif
        </form>
        
        {{-- Success/Error Alerts --}}
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif
        
        {{-- Products Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['Produit', 'Catégorie', 'Prix vente', 'Cuisine', 'Unité', 'Stock', 'Statut', 'Actions']">
                @forelse($produits as $product)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $product->display_image_url }}"
                                    alt="{{ $product->name }}"
                                    class="w-12 h-12 object-cover rounded-xl border border-gray-700"
                                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=120&q=80'"
                                >
                                <div>
                                    <p class="text-white font-medium">{{ $product->name }}</p>
                                    <p class="text-gray-500 text-sm">#{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $product->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($product->price_vente, 2) }} DH</td>
                        <td class="px-6 py-4">
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full {{ $product->kitchen_active ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30' }}">
                                {{ $product->kitchen_active ? 'Cuisine' : 'Direct' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            @php
                                $units = ['pcs' => 'Pièce', 'kg' => 'Kg', 'l' => 'Litre'];
                            @endphp
                            {{ $units[$product->unit] ?? $product->unit }}
                        </td>
                        <td class="px-6 py-4">
                            @if($product->stock_quantity <= $product->alert_stock)
                                <span class="text-red-400 font-semibold">{{ $product->stock_quantity }}</span>
                                <span class="text-red-400/60 text-xs ml-1">(alerte: {{ $product->alert_stock }})</span>
                            @else
                                <span class="text-gray-300">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($product->status === 'active')
                                <x-ui.badge variant="success">Actif</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Inactif</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button 
                                    variant="ghost" 
                                    size="sm"
                                    x-data
                                    @click="$dispatch('open-modal-stock-{{ $product->id }}')"
                                >
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm" href="{{ route('products.edit', $product) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </x-ui.button>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm" type="submit">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>Aucun produit trouvé</p>
                            <x-ui.button variant="primary" href="{{ route('products.create') }}" class="mt-4">
                                Créer un produit
                            </x-ui.button>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
            
            {{-- Pagination --}}
            @if($produits->hasPages())
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $produits->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
    
    {{-- Stock Update Modals --}}
    @foreach($produits as $product)
        <x-ui.modal id="stock-{{ $product->id }}" title="Ajuster le stock - {{ $product->name }}" size="sm">
            <form action="{{ route('products.update-stock', $product) }}" method="POST" class="space-y-4">
                @csrf
                <div class="text-center mb-4">
                    <p class="text-gray-400">Stock actuel:</p>
                    <p class="text-2xl font-bold text-white">{{ $product->stock_quantity }}</p>
                </div>
                <x-form.input 
                    type="number" 
                    name="quantity" 
                    label="Quantité" 
                    placeholder="Entrez la quantité"
                    required
                    min="1"
                />
                <x-form.select 
                    name="type" 
                    label="Type de mouvement"
                    :options="['in' => 'Entrée (+)', 'out' => 'Sortie (-)']"
                    required
                />
                <div class="flex justify-end gap-3 pt-4">
                    <x-ui.button variant="secondary" type="button" x-on:click="$dispatch('close-modal-stock-{{ $product->id }}')">
                        Annuler
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        Enregistrer
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endforeach
</x-layout.app>
