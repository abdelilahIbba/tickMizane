<x-layout.app title="Modifier commande">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="orderForm()">
        <div class="mb-8">
            <a href="{{ route('commandes.show', $commande) }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la commande
            </a>
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-3xl font-bold text-white">Modifier la commande</h1>
                    <p class="text-gray-400 mt-1">Commande #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }} · {{ $commande->table->name ?? 'Table' }}</p>
                    @if($commande->venteNumber())
                        <p class="text-amber-300 mt-1">Vente {{ $commande->venteNumber() }}</p>
                    @endif
                </div>
                @if($commande->table_id)
                    <x-ui.button variant="secondary" href="{{ route('waiter.table.order', $commande->table) }}">
                        Ajouter depuis la prise de commande
                    </x-ui.button>
                @endif
            </div>
        </div>

        <form action="{{ route('commandes.update', $commande) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <x-ui.card title="Produits">
                <div class="space-y-4">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="grid grid-cols-12 gap-4 p-4 bg-gray-900 rounded-xl">
                            <div class="col-span-5">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Produit</label>
                                <select
                                    x-model="line.product_id"
                                    x-bind:name="'items[' + index + '][produit_id]'"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                    required
                                    @change="syncPrice(line)"
                                >
                                    <option value="">Sélectionner...</option>
                                    @foreach($produits as $produit)
                                        <option value="{{ $produit->id }}">{{ $produit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Quantité</label>
                                <input
                                    type="number"
                                    x-model="line.quantity"
                                    x-bind:name="'items[' + index + '][quantity]'"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                    min="1"
                                    required
                                >
                            </div>
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Prix unitaire</label>
                                <input
                                    type="number"
                                    x-model="line.price"
                                    x-bind:name="'items[' + index + '][price]'"
                                    step="0.01"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white"
                                    required
                                >
                            </div>
                            <div class="col-span-2 flex items-end">
                                <button
                                    type="button"
                                    @click="removeLine(index)"
                                    class="w-full py-3 bg-red-500/20 text-red-400 rounded-xl hover:bg-red-500/30 transition-colors"
                                    :disabled="lines.length === 1"
                                    :class="{ 'opacity-50 cursor-not-allowed': lines.length === 1 }"
                                >
                                    Retirer
                                </button>
                            </div>
                        </div>
                    </template>

                    <button
                        type="button"
                        @click="addLine()"
                        class="w-full py-3 border-2 border-dashed border-gray-700 rounded-xl text-gray-400 hover:border-amber-500 hover:text-amber-400 transition-colors flex items-center justify-center gap-2"
                    >
                        Ajouter un produit
                    </button>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-300">Total de la commande</span>
                        <span class="text-2xl font-bold text-amber-400" x-text="total.toFixed(2) + ' DH'"></span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Notes">
                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Notes serveur..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                >{{ old('notes', $commande->waiter_notes) }}</textarea>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('commandes.show', $commande) }}">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Mettre à jour
                </x-ui.button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function orderForm() {
            return {
                lines: @json($orderLines),
                catalog: @json($productCatalog),

                get total() {
                    return this.lines.reduce((sum, line) => sum + (Number(line.quantity) * Number(line.price)), 0);
                },

                syncPrice(line) {
                    const product = this.catalog[line.product_id];
                    if (product) {
                        line.price = product.price;
                    }
                },

                addLine() {
                    this.lines.push({ product_id: '', quantity: 1, price: 0, notes: '' });
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
