<x-layout.app title="Mouvements de stock">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Mouvements de stock</h1>
                <p class="text-gray-400 mt-1">Suivi des entrées et sorties de stock</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('stock.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau mouvement
            </x-ui.button>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card 
                title="Entrées du mois" 
                value="156"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>'
            />
            <x-ui.stat-card 
                title="Sorties du mois" 
                value="89"
                color="red"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>'
            />
            <x-ui.stat-card 
                title="Pertes du mois" 
                value="12"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
            />
            <x-ui.stat-card 
                title="Produits en alerte" 
                value="8"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
            />
        </div>
        
        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-4">
            <x-form.select 
                name="type" 
                placeholder="Type de mouvement"
                :options="['in' => 'Entrée', 'out' => 'Sortie']"
                class="w-40"
            />
            <x-form.select 
                name="reason" 
                placeholder="Raison"
                :options="['vente' => 'Vente', 'commande' => 'Commande', 'perte' => 'Perte', 'ajustement' => 'Ajustement']"
                class="w-40"
            />
            <x-form.input 
                type="date" 
                name="date" 
                class="w-40"
            />
        </div>
        
        {{-- Movements Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['Date', 'Produit', 'Type', 'Quantité', 'Raison', 'Référence']">
                @foreach([
                    ['date' => now()->subHours(2), 'product' => 'Eau minérale 1.5L', 'type' => 'out', 'qty' => 5, 'reason' => 'vente', 'ref' => 'VENTE-0048'],
                    ['date' => now()->subHours(3), 'product' => 'Coca-Cola 33cl', 'type' => 'out', 'qty' => 3, 'reason' => 'vente', 'ref' => 'VENTE-0047'],
                    ['date' => now()->subHours(5), 'product' => 'Chips Lays 150g', 'type' => 'in', 'qty' => 48, 'reason' => 'commande', 'ref' => 'CMD-0010'],
                    ['date' => now()->subDays(1), 'product' => 'Pain de mie', 'type' => 'out', 'qty' => 2, 'reason' => 'perte', 'ref' => 'PERTE-003'],
                    ['date' => now()->subDays(1), 'product' => 'Café moulu 250g', 'type' => 'in', 'qty' => 24, 'reason' => 'commande', 'ref' => 'CMD-0009'],
                    ['date' => now()->subDays(2), 'product' => 'Lait 1L', 'type' => 'in', 'qty' => 36, 'reason' => 'commande', 'ref' => 'CMD-0008'],
                    ['date' => now()->subDays(2), 'product' => 'Biscuits Oreo', 'type' => 'out', 'qty' => 10, 'reason' => 'ajustement', 'ref' => 'ADJ-001'],
                ] as $movement)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-gray-300">{{ $movement['date']->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-white">{{ $movement['product'] }}</td>
                        <td class="px-6 py-4">
                            @if($movement['type'] === 'in')
                                <x-ui.badge variant="success">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                    Entrée
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    </svg>
                                    Sortie
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="{{ $movement['type'] === 'in' ? 'text-emerald-400' : 'text-red-400' }} font-semibold">
                                {{ $movement['type'] === 'in' ? '+' : '-' }}{{ $movement['qty'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($movement['reason'] === 'vente')
                                <x-ui.badge variant="info">Vente</x-ui.badge>
                            @elseif($movement['reason'] === 'commande')
                                <x-ui.badge variant="primary">Commande</x-ui.badge>
                            @elseif($movement['reason'] === 'perte')
                                <x-ui.badge variant="warning">Perte</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Ajustement</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-400 font-mono text-sm">{{ $movement['ref'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
