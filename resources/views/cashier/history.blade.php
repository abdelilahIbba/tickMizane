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

    <!-- Summary -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-green-500/30 shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-400">Total des ventes</p>
                <p class="text-3xl font-bold text-green-400">{{ number_format($totalRevenue, 2) }} DH</p>
            </div>
            <div>
                <p class="text-gray-400">Nombre de commandes</p>
                <p class="text-3xl font-bold text-white">{{ $orders->total() }}</p>
            </div>
        </div>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Payée à</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-800/30">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-white font-medium">#{{ $order->id }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-white">Table {{ $order->table->numero ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-gray-300">{{ $order->user->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-400 max-w-xs truncate">
                                {{ $order->details->pluck('produit.name')->implode(', ') }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-emerald-400 font-bold">{{ number_format($order->total, 2) }} DH</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-gray-400">{{ $order->updated_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
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
</x-layout.app>
