@props([
    'product' => null,
])

<div 
    x-data="{ quantity: 0 }"
    class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-pointer group"
    @click="$dispatch('add-to-cart', { product: {{ json_encode($product) }}, quantity: 1 })"
>
    {{-- Product Image --}}
    <div class="relative aspect-square bg-gray-900 overflow-hidden">
        @if($product->image ?? null)
            <img 
                src="{{ asset('storage/' . $product->image) }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        @endif
        
        {{-- Stock Badge --}}
        @if(($product->stock_quantity ?? 0) <= ($product->alert_stock ?? 5))
            <div class="absolute top-2 right-2">
                <span class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded-lg">
                    Stock bas
                </span>
            </div>
        @endif
    </div>
    
    {{-- Product Info --}}
    <div class="p-4 space-y-2">
        <h3 class="font-semibold text-white truncate group-hover:text-amber-400 transition-colors">
            {{ $product->name ?? 'Produit' }}
        </h3>
        
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-amber-400">
                {{ number_format($product->price_vente ?? 0, 2) }} DH
            </span>
            <span class="text-sm text-gray-400">
                Stock: {{ $product->stock_quantity ?? 0 }}
            </span>
        </div>
        
        {{-- Quick Add Button --}}
        <button 
            type="button"
            class="w-full mt-2 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-gray-900 font-semibold rounded-xl transition-all duration-200 min-h-[48px] flex items-center justify-center gap-2"
            @click.stop="$dispatch('add-to-cart', { product: {{ json_encode($product) }}, quantity: 1 })"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajouter
        </button>
    </div>
</div>
