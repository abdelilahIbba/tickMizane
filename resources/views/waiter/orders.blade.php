<x-layout.app title="Mes Commandes">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Mes Commandes</h1>
            <p class="text-gray-400 mt-1">Historique de vos commandes</p>
        </div>
        <a href="{{ route('waiter.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Retour aux tables
        </a>
    </div>

    <!-- Desktop Table (hidden on mobile, visible on desktop) -->
    {{-- Version ordinateur : Affichage sous forme de tableau (classique) --}}
    <div class="hidden md:block bg-gray-900/50 backdrop-blur-sm rounded-xl border border-gray-800 shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead class="bg-gray-900/80">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Commande</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Table</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date/Heure</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Articles</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 bg-transparent">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">#{{ $order->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        Table {{ $order->table->numero ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        {{ $order->details->count() }} articles
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-amber-400">
                        {{ number_format($order->total, 2) }} DH
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->status === 'en_preparation')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                En préparation
                            </span>
                        @elseif($order->status === 'servi')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                Servi
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-800 text-gray-400 border border-gray-700">
                                {{ ucfirst($order->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('waiter.order.show', $order) }}" 
                           class="text-blue-400 hover:text-blue-300 font-semibold transition-colors">
                            Détails
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Aucune commande trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card List (visible on mobile, hidden on desktop) -->
    {{-- Version mobile : Liste de cartes (cards layout) pour une meilleure ergonomie tactile --}}
    <div class="grid grid-cols-1 gap-4 md:hidden">
        @forelse($orders as $order)
        <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-xl p-4 shadow-md flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-sm font-bold text-white">Commande #{{ $order->id }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    @if($order->status === 'en_preparation')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-500/20 text-orange-400 border border-orange-500/30">
                            En préparation
                        </span>
                    @elseif($order->status === 'servi')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-500/20 text-green-400 border border-green-500/30">
                            Servi
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700">
                            {{ ucfirst($order->status) }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="flex justify-between items-center border-t border-gray-800 pt-3">
                <div class="text-xs text-gray-400">
                    <span class="font-medium text-gray-300">Table {{ $order->table->numero ?? 'N/A' }}</span>
                    <span class="mx-1.5">•</span>
                    <span>{{ $order->details->count() }} articles</span>
                </div>
                <div class="text-sm font-bold text-amber-400">
                    {{ number_format($order->total, 2) }} DH
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-3">
                <a href="{{ route('waiter.order.show', $order) }}" 
                   class="block w-full py-2 bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 text-blue-400 text-center font-semibold rounded-lg text-xs transition-colors">
                    Détails de la commande
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-12 bg-gray-900/50 rounded-xl border border-gray-800 shadow">
            <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-400 font-medium">Aucune commande trouvée</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
</x-layout.app>
