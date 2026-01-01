<x-layout.app title="Ventes">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Ventes</h1>
                <p class="text-gray-400 mt-1">Historique des ventes</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('pos.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle vente
            </x-ui.button>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.stat-card 
                title="Ventes aujourd'hui" 
                value="{{ number_format(12500, 2) }} DH"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            <x-ui.stat-card 
                title="Nombre de ventes" 
                value="48"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
            />
            <x-ui.stat-card 
                title="Panier moyen" 
                value="{{ number_format(260.42, 2) }} DH"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'
            />
        </div>
        
        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-4">
            <x-form.input 
                type="date" 
                name="date_from" 
                label=""
                class="w-40"
            />
            <x-form.input 
                type="date" 
                name="date_to" 
                label=""
                class="w-40"
            />
            <x-form.select 
                name="payment_method" 
                placeholder="Mode de paiement"
                :options="['cash' => 'Espèces', 'card' => 'Carte', 'mixed' => 'Mixte']"
                class="w-48"
            />
        </div>
        
        {{-- Sales Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['N° Vente', 'Date', 'Articles', 'Total', 'Paiement', 'Caissier', 'Actions']">
                @foreach([
                    ['id' => 48, 'date' => now()->subMinutes(15), 'items' => 5, 'total' => 245.50, 'method' => 'cash', 'cashier' => 'Ahmed'],
                    ['id' => 47, 'date' => now()->subMinutes(45), 'items' => 3, 'total' => 89.00, 'method' => 'card', 'cashier' => 'Ahmed'],
                    ['id' => 46, 'date' => now()->subHours(1), 'items' => 8, 'total' => 512.00, 'method' => 'mixed', 'cashier' => 'Fatima'],
                    ['id' => 45, 'date' => now()->subHours(2), 'items' => 2, 'total' => 35.00, 'method' => 'cash', 'cashier' => 'Ahmed'],
                    ['id' => 44, 'date' => now()->subHours(3), 'items' => 12, 'total' => 890.50, 'method' => 'card', 'cashier' => 'Fatima'],
                ] as $sale)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">#{{ str_pad($sale['id'], 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $sale['date']->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $sale['items'] }} articles</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($sale['total'], 2) }} DH</td>
                        <td class="px-6 py-4">
                            @if($sale['method'] === 'cash')
                                <x-ui.badge variant="success">Espèces</x-ui.badge>
                            @elseif($sale['method'] === 'card')
                                <x-ui.badge variant="info">Carte</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Mixte</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $sale['cashier'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" size="sm" href="{{ route('ventes.show', $sale['id']) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm" href="{{ route('payments.receipt', $sale['id']) }}">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
        
        {{-- Pagination --}}
        <div class="mt-6 flex justify-center">
            <nav class="flex items-center gap-2">
                <button class="px-4 py-2 bg-gray-800 text-gray-400 rounded-lg hover:bg-gray-700">Précédent</button>
                <button class="px-4 py-2 bg-amber-500 text-gray-900 rounded-lg font-medium">1</button>
                <button class="px-4 py-2 bg-gray-800 text-gray-400 rounded-lg hover:bg-gray-700">2</button>
                <button class="px-4 py-2 bg-gray-800 text-gray-400 rounded-lg hover:bg-gray-700">3</button>
                <button class="px-4 py-2 bg-gray-800 text-gray-400 rounded-lg hover:bg-gray-700">Suivant</button>
            </nav>
        </div>
    </div>
</x-layout.app>
