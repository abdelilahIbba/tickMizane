<x-layout.app title="Historique des paiements">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Historique des paiements</h1>
                <p class="text-gray-400">Commandes payées - {{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('d/m/Y') : 'Aujourd\'hui' }}</p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('cashier.pending') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                    Commandes en attente
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-4 mb-6">
        <form action="{{ route('cashier.history') }}" method="GET" class="flex items-center gap-4">
            <label class="text-gray-300">Date:</label>
            <input type="date" 
                   name="date" 
                   value="{{ request('date', today()->format('Y-m-d')) }}"
                   class="px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Filtrer
            </button>
        </form>
    </div>

    {{-- Cancellation Alert Banner --}}
    @if($cancelledCount > 0)
    <div class="bg-red-900/40 backdrop-blur-sm rounded-lg border border-red-500/60 shadow-lg shadow-red-500/10 p-5 mb-6 animate-pulse-slow">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-red-300">
                    ⚠️ {{ $cancelledCount }} annulation{{ $cancelledCount > 1 ? 's' : '' }} détectée{{ $cancelledCount > 1 ? 's' : '' }}
                </h3>
                <p class="text-red-400/80 mt-1">
                    Un total de <span class="font-bold text-red-300">{{ number_format($cancelledTotal, 2) }} DH</span> 
                    a été annulé {{ request('date') ? 'le ' . \Carbon\Carbon::parse(request('date'))->format('d/m/Y') : 'aujourd\'hui' }}.
                    Les lignes annulées sont marquées en rouge dans le tableau ci-dessous.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-{{ $cancelledCount > 0 ? '3' : '2' }} gap-4 mb-6">
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-green-500/30 shadow-lg p-6">
            <p class="text-gray-400">Total des ventes</p>
            <p class="text-3xl font-bold text-green-400">{{ number_format($totalRevenue, 2) }} DH</p>
        </div>
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6">
            <p class="text-gray-400">Nombre de commandes</p>
            <p class="text-3xl font-bold text-white">{{ $orders->total() }}</p>
        </div>
        @if($cancelledCount > 0)
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-red-500/30 shadow-lg p-6">
            <p class="text-gray-400">Annulations</p>
            <p class="text-3xl font-bold text-red-400">{{ $cancelledCount }}</p>
            <p class="text-sm text-red-400/70 mt-1">{{ number_format($cancelledTotal, 2) }} DH</p>
        </div>
        @endif
    </div>

    <!-- Orders Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-950/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cmd #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Table</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Serveur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Produits</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Heure</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($orders as $order)
                    @php $isCancelled = $order->status === 'annule'; @endphp
                    <tr class="{{ $isCancelled ? 'bg-red-950/40 border-l-4 border-l-red-500' : 'hover:bg-gray-800/30' }}">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="{{ $isCancelled ? 'text-red-400 line-through' : 'text-white' }} font-medium">#{{ $order->id }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="{{ $isCancelled ? 'text-red-400/70' : 'text-white' }}">Table {{ $order->table->numero ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="{{ $isCancelled ? 'text-red-400/70' : 'text-gray-300' }}">{{ $order->user->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm {{ $isCancelled ? 'text-red-400/60 line-through' : 'text-gray-400' }} max-w-xs truncate">
                                {{ $order->details->pluck('produit.name')->implode(', ') }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="{{ $isCancelled ? 'text-red-400 line-through' : 'text-emerald-400' }} font-bold">{{ number_format($order->total, 2) }} DH</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($isCancelled)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400 border border-red-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annulée
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Payée
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="{{ $isCancelled ? 'text-red-400/60' : 'text-gray-400' }}">{{ $order->updated_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if(!$isCancelled)
                                    <a href="{{ route('cashier.receipt', $order) }}"
                                       class="text-blue-400 hover:text-blue-300 text-sm">
                                        Reçu PDF
                                    </a>

                                    @if(auth()->user()?->isAdmin())
                                        <form action="{{ route('cashier.history.cancel', ['commande' => $order, 'date' => request('date')]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Confirmer l\'annulation de la vente #{{ $order->id }} ? Le stock sera restauré.');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-300 text-sm font-medium">
                                                Annuler vente
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-red-400/50 text-xs italic">Vente annulée</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            Aucune commande payée pour cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }
    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>
</x-layout.app>
