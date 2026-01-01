@props([
    'items' => [],
    'total' => 0,
])

<div class="flex flex-col h-full bg-gray-800 border-l border-gray-700">
    {{-- Cart Header --}}
    <div class="px-6 py-4 border-b border-gray-700">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Panier
            </h2>
            <span class="px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-sm font-semibold">
                {{ count($items) }} articles
            </span>
        </div>
    </div>
    
    {{-- Cart Items --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        @forelse($items as $index => $item)
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-white truncate">{{ $item['name'] ?? 'Produit' }}</h4>
                        <p class="text-sm text-gray-400">{{ number_format($item['price'] ?? 0, 2) }} DH</p>
                    </div>
                    <button 
                        type="button"
                        class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors"
                        @click="$dispatch('remove-from-cart', { index: {{ $index }} })"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Quantity Controls --}}
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors"
                            @click="$dispatch('update-quantity', { index: {{ $index }}, delta: -1 })"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="w-12 text-center text-lg font-semibold text-white">
                            {{ $item['quantity'] ?? 1 }}
                        </span>
                        <button 
                            type="button"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors"
                            @click="$dispatch('update-quantity', { index: {{ $index }}, delta: 1 })"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                    </div>
                    <span class="text-lg font-bold text-amber-400">
                        {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }} DH
                    </span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center py-12">
                <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-400 text-lg">Panier vide</p>
                <p class="text-gray-500 text-sm mt-1">Ajoutez des produits pour commencer</p>
            </div>
        @endforelse
    </div>
    
    {{-- Cart Footer --}}
    <div class="border-t border-gray-700 p-4 space-y-4 bg-gray-900">
        {{-- Subtotal --}}
        <div class="space-y-2">
            <div class="flex justify-between text-gray-400">
                <span>Sous-total</span>
                <span>{{ number_format($total, 2) }} DH</span>
            </div>
            <div class="flex justify-between text-gray-400">
                <span>TVA (20%)</span>
                <span>{{ number_format($total * 0.2, 2) }} DH</span>
            </div>
            <div class="flex justify-between text-xl font-bold text-white pt-2 border-t border-gray-700">
                <span>Total TTC</span>
                <span class="text-amber-400">{{ number_format($total * 1.2, 2) }} DH</span>
            </div>
        </div>
        
        {{-- Payment Method --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">Mode de paiement</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="payment-method-btn active py-3 px-4 bg-amber-500 text-gray-900 font-semibold rounded-xl" data-method="cash">
                    Espèces
                </button>
                <button type="button" class="payment-method-btn py-3 px-4 bg-gray-700 text-gray-300 font-semibold rounded-xl hover:bg-gray-600" data-method="card">
                    Carte
                </button>
                <button type="button" class="payment-method-btn py-3 px-4 bg-gray-700 text-gray-300 font-semibold rounded-xl hover:bg-gray-600" data-method="mixed">
                    Mixte
                </button>
            </div>
        </div>
        
        {{-- Checkout Button --}}
        <button 
            type="button"
            class="w-full py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-lg rounded-xl transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="$dispatch('checkout')"
            {{ count($items) === 0 ? 'disabled' : '' }}
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Valider la vente
        </button>
        
        {{-- Clear Cart --}}
        <button 
            type="button"
            class="w-full py-3 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-xl transition-colors"
            @click="$dispatch('clear-cart')"
        >
            Vider le panier
        </button>
    </div>
</div>
