<x-layout.app title="Détails de la commande">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('commandes.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux commandes
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Commande #{{ str_pad($commande->id ?? 12, 4, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-400 mt-1">Créée le {{ now()->subDays(1)->format('d/m/Y') }}</p>
                </div>
                <div class="flex gap-3">
                    <x-ui.button variant="success">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Marquer comme reçue
                    </x-ui.button>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Order Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Items --}}
                <x-ui.card title="Produits commandés" :padding="false">
                    <x-ui.table :headers="['Produit', 'Quantité', 'Prix unitaire', 'Total']">
                        @foreach([
                            ['name' => 'Eau minérale 1.5L', 'qty' => 48, 'price' => 3.50],
                            ['name' => 'Coca-Cola 33cl', 'qty' => 72, 'price' => 5.00],
                            ['name' => 'Jus d\'orange 1L', 'qty' => 24, 'price' => 12.00],
                            ['name' => 'Café Express (pack)', 'qty' => 10, 'price' => 85.00],
                        ] as $item)
                            <tr class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-white">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ $item['qty'] }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ number_format($item['price'], 2) }} DH</td>
                                <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($item['qty'] * $item['price'], 2) }} DH</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            </div>
            
            {{-- Summary --}}
            <div class="space-y-6">
                <x-ui.card title="Résumé">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Statut</span>
                            <x-ui.badge variant="warning">En attente</x-ui.badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Produits</span>
                            <span class="text-white">4 références</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Quantité totale</span>
                            <span class="text-white">154 unités</span>
                        </div>
                        <div class="flex justify-between pt-4 border-t border-gray-700">
                            <span class="text-white font-semibold">Total</span>
                            <span class="text-2xl font-bold text-amber-400">5,420.00 DH</span>
                        </div>
                    </div>
                </x-ui.card>
                
                <x-ui.card title="Fournisseur">
                    <div class="space-y-3">
                        <div>
                            <span class="text-lg font-semibold text-white">Boissons Maroc SARL</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Téléphone</span>
                            <span class="text-white">+212 5XX-XXXXXX</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Email</span>
                            <span class="text-white">contact@boissons.ma</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layout.app>
