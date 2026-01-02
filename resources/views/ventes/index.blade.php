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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card 
                title="Ventes aujourd'hui" 
                value="{{ number_format($todaySales, 2) }} DH"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            <x-ui.stat-card 
                title="Nombre de ventes" 
                value="{{ $todayCount }}"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
            />
            <x-ui.stat-card 
                title="Total Espèces" 
                value="{{ number_format($cashTotal, 2) }} DH"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'
            />
            <x-ui.stat-card 
                title="Total Carte" 
                value="{{ number_format($cardTotal, 2) }} DH"
                color="purple"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
            />
        </div>
        
        {{-- Filters --}}
        <form method="GET" action="{{ route('ventes.index') }}" class="mb-6 flex flex-wrap gap-4">
            <x-form.input 
                type="date" 
                name="date" 
                label=""
                :value="request('date')"
                class="w-40"
            />
            <x-form.select 
                name="payment_method" 
                placeholder="Mode de paiement"
                :options="['cash' => 'Espèces', 'carte' => 'Carte', 'mixte' => 'Mixte']"
                :selected="request('payment_method')"
                class="w-48"
            />
            <x-form.select 
                name="status" 
                placeholder="Statut"
                :options="['paid' => 'Payée', 'pending' => 'En attente', 'cancelled' => 'Annulée']"
                :selected="request('status')"
                class="w-40"
            />
            <x-ui.button type="submit" variant="secondary">Filtrer</x-ui.button>
            @if(request()->hasAny(['date', 'payment_method', 'status']))
                <x-ui.button variant="ghost" href="{{ route('ventes.index') }}">Réinitialiser</x-ui.button>
            @endif
        </form>
        
        {{-- Sales Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['N° Vente', 'Date', 'Articles', 'Total', 'Paiement', 'Statut', 'Caissier', 'Actions']">
                @forelse($ventes as $vente)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">#{{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $vente->details->count() }} articles</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($vente->total, 2) }} DH</td>
                        <td class="px-6 py-4">
                            @if($vente->payment_method === 'cash')
                                <x-ui.badge variant="success">Espèces</x-ui.badge>
                            @elseif($vente->payment_method === 'carte')
                                <x-ui.badge variant="info">Carte</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Mixte</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($vente->status === 'paid')
                                <x-ui.badge variant="success">Payée</x-ui.badge>
                            @elseif($vente->status === 'pending')
                                <x-ui.badge variant="warning">En attente</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">Annulée</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $vente->user->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" size="sm" href="{{ route('ventes.show', $vente) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p>Aucune vente trouvée</p>
                            <x-ui.button variant="primary" href="{{ route('pos.index') }}" class="mt-4">
                                Créer une vente
                            </x-ui.button>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
            
            {{-- Pagination --}}
            @if($ventes->hasPages())
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $ventes->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layout.app>
