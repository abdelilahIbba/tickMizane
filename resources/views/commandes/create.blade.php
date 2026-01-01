<x-layout.app title="Nouvelle commande">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="orderForm()">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('commandes.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux commandes
            </a>
            <h1 class="text-3xl font-bold text-white">Nouvelle commande</h1>
            <p class="text-gray-400 mt-1">Créer une commande fournisseur</p>
        </div>
        
        <form action="{{ route('commandes.store') }}" method="POST" class="space-y-6">
            @csrf
            
            {{-- Supplier Selection --}}
            <x-ui.card title="Fournisseur">
                <x-form.select 
                    name="fournisseur_id" 
                    label="Sélectionner un fournisseur"
                    :options="['1' => 'Boissons Maroc SARL', '2' => 'Épicerie Gros', '3' => 'Snacks Distribution', '4' => 'Hygiène Plus']"
                    required
                />
            </x-ui.card>
            
            {{-- Order Lines --}}
            <x-ui.card title="Produits">
                <div class="space-y-4">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="grid grid-cols-12 gap-4 p-4 bg-gray-900 rounded-xl">
                            <div class="col-span-5">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Produit</label>
                                <select 
                                    x-model="line.product_id"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option value="1">Eau minérale 1.5L</option>
                                    <option value="2">Coca-Cola 33cl</option>
                                    <option value="3">Chips Lays 150g</option>
                                    <option value="4">Café moulu 250g</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Quantité</label>
                                <input 
                                    type="number" 
                                    x-model="line.quantity"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                    min="1"
                                >
                            </div>
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Prix unitaire</label>
                                <input 
                                    type="number" 
                                    x-model="line.price"
                                    step="0.01"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                >
                            </div>
                            <div class="col-span-2 flex items-end">
                                <button 
                                    type="button"
                                    @click="removeLine(index)"
                                    class="w-full py-3 bg-red-500/20 text-red-400 rounded-xl hover:bg-red-500/30 transition-colors"
                                >
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <button 
                        type="button"
                        @click="addLine()"
                        class="w-full py-3 border-2 border-dashed border-gray-700 rounded-xl text-gray-400 hover:border-amber-500 hover:text-amber-400 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter un produit
                    </button>
                </div>
                
                {{-- Total --}}
                <div class="mt-6 pt-6 border-t border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-300">Total de la commande</span>
                        <span class="text-2xl font-bold text-amber-400" x-text="total.toFixed(2) + ' DH'"></span>
                    </div>
                </div>
            </x-ui.card>
            
            {{-- Notes --}}
            <x-ui.card title="Notes">
                <x-form.textarea 
                    name="notes" 
                    label=""
                    placeholder="Notes ou instructions particulières..."
                    rows="3"
                />
            </x-ui.card>
            
            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('commandes.index') }}">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Créer la commande
                </x-ui.button>
            </div>
        </form>
    </div>
    
    @push('scripts')
    <script>
        function orderForm() {
            return {
                lines: [
                    { product_id: '', quantity: 1, price: 0 }
                ],
                
                get total() {
                    return this.lines.reduce((sum, line) => sum + (line.quantity * line.price), 0);
                },
                
                addLine() {
                    this.lines.push({ product_id: '', quantity: 1, price: 0 });
                },
                
                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                }
            }
        }
    </script>
    @endpush
</x-layout.app>
