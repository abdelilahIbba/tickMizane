<x-layout.app title="Mes Commandes">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Mes Commandes</h1>
            <p class="text-gray-600 mt-1">Historique de vos commandes</p>
        </div>
        <a href="{{ route('waiter.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Retour aux tables
        </a>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Heure</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        Table {{ $order->table->numero ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $order->details->count() }} articles
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                        {{ number_format($order->total, 2) }} DH
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->status === 'en_preparation')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                En préparation
                            </span>
                        @elseif($order->status === 'servi')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Servi
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($order->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('waiter.order.show', $order) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
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

    <!-- Pagination -->
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
</x-layout.app>
