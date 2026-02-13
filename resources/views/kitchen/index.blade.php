<x-layout.app title="Cuisine - Dashboard">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Cuisine Dashboard</h1>
            <p class="text-gray-400 mt-1">Gestion des commandes en temps réel</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-orange-500/30 shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">En cuisine</p>
                    <p class="text-2xl font-bold text-orange-400" id="statsActive">{{ $activeOrders->where('status', 'en_cuisine')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-blue-500/30 shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">En préparation</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $activeOrders->where('status', 'en_preparation')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-green-500/30 shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Servis aujourd'hui</p>
                    <p class="text-2xl font-bold text-green-400" id="statsServed">{{ $completedOrders->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-purple-500/30 shadow-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Total actif</p>
                    <p class="text-2xl font-bold text-purple-400" id="statsTotal">{{ $activeOrders->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Orders -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Commandes actives</h2>
        
        <div id="activeOrdersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($activeOrders as $order)
            @include('kitchen.partials.order-card', ['order' => $order])
            @empty
            <div class="col-span-full bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500">Aucune commande en attente</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Completed Orders Today -->
    <div>
        <h2 class="text-xl font-bold text-white mb-4">Commandes servies aujourd'hui</h2>
        
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-950/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Table</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Serveur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($completedOrders as $order)
                    <tr class="hover:bg-gray-800/30">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">#{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Table {{ $order->table->numero ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $order->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ $order->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ $order->details->count() }} articles</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('kitchen.ticket', $order) }}" target="_blank" class="text-blue-400 hover:text-blue-300">
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
document.addEventListener('DOMContentLoaded', () => {
    // Initial load
    let currentOrderIds = new Set();
    
    // Audio function
    function announceOrder(orderId) {
        if ('speechSynthesis' in window) {
            const text = `Commande numéro ${orderId} prête`;
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            
            // Repeat 5 times
            let count = 0;
            utterance.onend = function() {
                count++;
                if (count < 5) {
                    window.speechSynthesis.speak(utterance);
                }
            };
            
            window.speechSynthesis.speak(utterance);
        }
    }

    // Polling function
    function refreshOrders() {
        fetch('{{ route('kitchen.orders.active') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update Stats
            if (data.stats) {
                document.getElementById('statsActive').textContent = data.stats.active_orders;
                document.getElementById('statsServed').textContent = data.stats.served_today;
                document.getElementById('statsTotal').textContent = data.stats.total_today;
            }
            
            // Update Grid
            const grid = document.getElementById('activeOrdersGrid');
            grid.innerHTML = data.html;
            
            // Re-attach listeners for new content
            attachFormListeners();
        })
        .catch(error => console.error('Error refreshing orders:', error));
    }

    // Handle AJAX forms for "Ready" status
    function attachFormListeners() {
        document.querySelectorAll('form[action*="/ready"]').forEach(form => {
            if (form.getAttribute('data-ajax-attached')) return;
            form.setAttribute('data-ajax-attached', 'true');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Play audio
                        announceOrder(data.order_id);
                        // Refresh grid immediately
                        refreshOrders();
                    } else {
                        alert('Erreur: ' + (data.message || 'Unknown error'));
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur de connexion');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        });
    }

    // Start polling
    setInterval(refreshOrders, 10000);
    
    // Initial attachment
    attachFormListeners();
});
</script>
@endpush
</x-layout.app>