<x-layout.app title="Nouveau mouvement">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('stock.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux mouvements
            </a>
            <h1 class="text-3xl font-bold text-white">Nouveau mouvement</h1>
            <p class="text-gray-400 mt-1">Enregistrer un mouvement de stock</p>
        </div>
        
        {{-- Form Card --}}
        <x-ui.card>
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    Veuillez corriger les erreurs ci-dessous.
                </x-ui.alert>
            @endif
            
            <form action="{{ route('stock.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <x-form.select 
                    name="product_id" 
                    label="Produit"
                    :options="[
                        '1' => 'Eau minérale 1.5L',
                        '2' => 'Coca-Cola 33cl',
                        '3' => 'Chips Lays 150g',
                        '4' => 'Café moulu 250g',
                        '5' => 'Pain de mie',
                        '6' => 'Lait 1L',
                    ]"
                    required
                />
                
                <x-form.select 
                    name="type" 
                    label="Type de mouvement"
                    :options="['in' => 'Entrée (ajouter au stock)', 'out' => 'Sortie (retirer du stock)']"
                    required
                />
                
                <x-form.input 
                    type="number" 
                    name="quantity" 
                    label="Quantité" 
                    placeholder="0"
                    min="1"
                    required
                />
                
                <x-form.select 
                    name="reason" 
                    label="Raison"
                    :options="[
                        'commande' => 'Réception commande fournisseur',
                        'perte' => 'Perte / Casse',
                        'ajustement' => 'Ajustement d\'inventaire',
                        'retour' => 'Retour client',
                    ]"
                    required
                />
                
                <x-form.textarea 
                    name="notes" 
                    label="Notes" 
                    placeholder="Détails supplémentaires sur ce mouvement..."
                    rows="3"
                />
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                    <x-ui.button variant="secondary" href="{{ route('stock.index') }}">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        Enregistrer le mouvement
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layout.app>
