<x-layout.app title="Détails de la vente">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('ventes.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux ventes
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Vente #{{ str_pad($vente->id ?? 48, 6, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-400 mt-1">{{ now()->format('d/m/Y à H:i') }}</p>
                </div>
                <x-ui.button variant="primary" href="{{ route('payments.receipt', $vente ?? 48) }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer le reçu
                </x-ui.button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sale Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Items --}}
                <x-ui.card title="Articles" :padding="false">
                    <x-ui.table :headers="['Produit', 'Prix unitaire', 'Quantité', 'Total']">
                        @foreach([
                            ['name' => 'Eau minérale 1.5L', 'price' => 5.00, 'qty' => 3],
                            ['name' => 'Coca-Cola 33cl', 'price' => 8.00, 'qty' => 2],
                            ['name' => 'Chips Lays 150g', 'price' => 15.00, 'qty' => 1],
                            ['name' => 'Pain de mie', 'price' => 12.00, 'qty' => 1],
                            ['name' => 'Lait 1L', 'price' => 10.00, 'qty' => 2],
                        ] as $item)
                            <tr class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-white">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ number_format($item['price'], 2) }} DH</td>
                                <td class="px-6 py-4 text-gray-300">{{ $item['qty'] }}</td>
                                <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($item['price'] * $item['qty'], 2) }} DH</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            </div>
            
            {{-- Summary --}}
            <div class="space-y-6">
                <x-ui.card title="Résumé">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Sous-total</span>
                            <span class="text-white">78.00 DH</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">TVA (20%)</span>
                            <span class="text-white">15.60 DH</span>
                        </div>
                        <div class="flex justify-between pt-4 border-t border-gray-700">
                            <span class="text-white font-semibold">Total TTC</span>
                            <span class="text-2xl font-bold text-amber-400">93.60 DH</span>
                        </div>
                    </div>
                </x-ui.card>
                
                <x-ui.card title="Paiement">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Mode</span>
                            <x-ui.badge variant="success">Espèces</x-ui.badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Montant reçu</span>
                            <span class="text-white">100.00 DH</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Monnaie rendue</span>
                            <span class="text-emerald-400">6.40 DH</span>
                        </div>
                    </div>
                </x-ui.card>
                
                <x-ui.card title="Informations">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Caissier</span>
                            <span class="text-white">Ahmed</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Date</span>
                            <span class="text-white">{{ now()->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Heure</span>
                            <span class="text-white">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layout.app>
