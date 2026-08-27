<x-layout.app title="Détails de la commande">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('commandes.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux commandes
            </a>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-3xl font-bold text-white">Commande #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-400 mt-1">{{ $commande->table->name ?? 'Table' }} · {{ $commande->created_at?->format('d/m/Y H:i') }}</p>
                    @if($commande->venteNumber())
                        <p class="text-amber-300 mt-1 font-medium">Vente {{ $commande->venteNumber() }}</p>
                    @endif
                </div>
                <div class="flex gap-3 flex-wrap">
                    @if($commande->table_id && (auth()->user()?->isAdmin() || auth()->user()?->isServeur()) && $commande->isOpenForEdit())
                        <x-ui.button variant="secondary" href="{{ route('waiter.table.order', $commande->table) }}">
                            Ajouter depuis la prise de commande
                        </x-ui.button>
                    @endif
                    @can('update', $commande)
                        <x-ui.button variant="primary" href="{{ route('commandes.edit', $commande) }}">
                            Modifier
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="Produits commandés" :padding="false">
                    <x-ui.table :headers="['Produit', 'Quantité', 'Prix unitaire', 'Total']">
                        @forelse($commande->details as $detail)
                            <tr class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-white">
                                    {{ $detail->produit->name ?? 'Produit' }}
                                    @if($detail->notes)
                                        <div class="text-xs text-gray-500">{{ $detail->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-300">{{ $detail->quantity }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ number_format($detail->price, 2) }} DH</td>
                                <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($detail->total, 2) }} DH</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Aucun produit</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card title="Paiement">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Statut</span>
                            @if($commande->isPaid())
                                <x-ui.badge variant="success">Payée</x-ui.badge>
                            @elseif($commande->status === 'annule')
                                <x-ui.badge variant="danger">Annulée</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Non payée</x-ui.badge>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Cuisine</span>
                            <span class="text-white">{{ $commande->status_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Table</span>
                            <span class="text-white">{{ $commande->table->name ?? ('Table '.$commande->table_id) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Vente</span>
                            <span class="text-amber-300">{{ $commande->venteNumber() ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between pt-4 border-t border-gray-700">
                            <span class="text-white font-semibold">Total</span>
                            <span class="text-2xl font-bold text-amber-400">{{ number_format($commande->total, 2) }} DH</span>
                        </div>
                    </div>
                </x-ui.card>

                @if($commande->waiter_notes)
                    <x-ui.card title="Notes">
                        <p class="text-gray-300">{{ $commande->waiter_notes }}</p>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-layout.app>
