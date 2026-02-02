<x-layout.app title="Cuisine - Dashboard">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cuisine Dashboard</h1>
            <p class="text-gray-600 mt-1">Gestion des commandes en temps réel</p>
        </div>
        <div class="flex gap-3">
            <button onclick="refreshOrders()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En préparation</p>
                    <p class="text-2xl font-bold text-orange-600" id="statsActive">{{ $activeOrders->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Servis aujourd'hui</p>
                    <p class="text-2xl font-bold text-green-600" id="statsServed">{{ $completedOrders->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total aujourd'hui</p>
                    <p class="text-2xl font-bold text-blue-600" id="statsTotal">{{ $activeOrders->count() + $completedOrders->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Temps moyen</p>
                    <p class="text-2xl font-bold text-purple-600" id="statsAvgTime">--</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Orders -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Commandes en préparation</h2>
        
        <div id="activeOrdersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($activeOrders as $order)
            @include('kitchen.partials.order-card', ['order' => $order])
            @empty
            <div class="col-span-full bg-white rounded-lg shadow-sm p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500">Aucune commande en préparation</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Completed Orders Today -->
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Commandes servies aujourd'hui</h2>
        
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serveur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($completedOrders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Table {{ $order->table->numero ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $order->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $order->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $order->details->count() }} articles</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('kitchen.ticket', $order) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                Imprimer
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Aucune commande servie aujourd'hui
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh every 15 seconds
let refreshInterval = setInterval(() => {
    refreshOrders();
}, 15000);

function refreshOrders() {
    fetch('{{ route('kitchen.orders.active') }}')
        .then(response => response.json())
        .then(data => {
            updateActiveOrders(data.orders);
        })
        .catch(error => console.error('Error refreshing orders:', error));
    
    // Refresh stats
    fetch('{{ route('kitchen.stats') }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('statsActive').textContent = data.active_orders;
            document.getElementById('statsServed').textContent = data.served_today;
            document.getElementById('statsTotal').textContent = data.total_today;
            document.getElementById('statsAvgTime').textContent = data.average_time + ' min';
        });
}

function updateActiveOrders(orders) {
    const grid = document.getElementById('activeOrdersGrid');
    
    if (orders.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full bg-white rounded-lg shadow-sm p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500">Aucune commande en préparation</p>
            </div>
        `;
        return;
    }
    
    // Note: Full update would require reloading page for proper Blade rendering
    // For now, just show count update
    console.log('Active orders updated:', orders.length);
}

function markOrderServed(orderId) {
    if (!confirm('Marquer cette commande comme servie?')) return;
    
    fetch(`/kitchen/order/${orderId}/served`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush
</x-layout.app>
