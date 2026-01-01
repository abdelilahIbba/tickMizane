<x-layout.app title="Commandes fournisseurs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Commandes fournisseurs</h1>
                <p class="text-gray-400 mt-1">Gérez vos commandes de réapprovisionnement</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('commandes.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle commande
            </x-ui.button>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.stat-card 
                title="Commandes en attente" 
                value="5"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            <x-ui.stat-card 
                title="Valeur en attente" 
                value="{{ number_format(15680, 2) }} DH"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            <x-ui.stat-card 
                title="Commandes du mois" 
                value="12"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
            />
        </div>
        
        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-4">
            <x-form.select 
                name="status" 
                placeholder="Tous les statuts"
                :options="['pending' => 'En attente', 'received' => 'Reçue', 'cancelled' => 'Annulée']"
                class="w-48"
            />
            <x-form.select 
                name="fournisseur" 
                placeholder="Tous les fournisseurs"
                :options="['1' => 'Fournisseur A', '2' => 'Fournisseur B', '3' => 'Fournisseur C']"
                class="w-48"
            />
        </div>
        
        {{-- Orders Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['N° Commande', 'Fournisseur', 'Date', 'Produits', 'Total', 'Statut', 'Actions']">
                @foreach([
                    ['id' => 12, 'fournisseur' => 'Boissons Maroc SARL', 'date' => now()->subDays(1), 'items' => 8, 'total' => 5420.00, 'status' => 'pending'],
                    ['id' => 11, 'fournisseur' => 'Épicerie Gros', 'date' => now()->subDays(2), 'items' => 15, 'total' => 3280.00, 'status' => 'pending'],
                    ['id' => 10, 'fournisseur' => 'Snacks Distribution', 'date' => now()->subDays(3), 'items' => 6, 'total' => 1890.00, 'status' => 'received'],
                    ['id' => 9, 'fournisseur' => 'Boissons Maroc SARL', 'date' => now()->subDays(5), 'items' => 10, 'total' => 4560.00, 'status' => 'received'],
                    ['id' => 8, 'fournisseur' => 'Hygiène Plus', 'date' => now()->subDays(7), 'items' => 12, 'total' => 2340.00, 'status' => 'cancelled'],
                ] as $order)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">#{{ str_pad($order['id'], 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4 text-white">{{ $order['fournisseur'] }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $order['date']->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $order['items'] }} produits</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($order['total'], 2) }} DH</td>
                        <td class="px-6 py-4">
                            @if($order['status'] === 'pending')
                                <x-ui.badge variant="warning">En attente</x-ui.badge>
                            @elseif($order['status'] === 'received')
                                <x-ui.badge variant="success">Reçue</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">Annulée</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" size="sm" href="{{ route('commandes.show', $order['id']) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </x-ui.button>
                                @if($order['status'] === 'pending')
                                    <x-ui.button variant="ghost" size="sm">
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </x-ui.button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
