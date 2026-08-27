<x-layout.app title="Commandes en attente de paiement">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Commandes en attente de paiement</h1>
                <p class="text-gray-400">Gérez les paiements des commandes cuisine</p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                    POS Direct
                </a>
                <a href="{{ route('cashier.history') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Historique
                </a>
                @if(auth()->user()?->isAdmin())
                <a href="{{ route('cashier.tickets') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Ventes & CA
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-orange-500/30 shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-orange-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Tables à encaisser</p>
                    <p class="text-2xl font-bold text-white">{{ $pendingOrders->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-emerald-500/30 shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-emerald-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Validées en cuisine</p>
                    <p class="text-2xl font-bold text-white">{{ $readyOrders->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-blue-500/30 shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Payées aujourd'hui</p>
                    <p class="text-2xl font-bold text-white">{{ $todayPaid }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-green-500/30 shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">CA aujourd'hui</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($todayRevenue, 2) }} DH</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($pendingOrders as $order)
        @php
            $statusClasses = match($order->status) {
                'en_cuisine' => 'bg-orange-500/20 text-orange-400 border border-orange-500/30',
                'en_preparation' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                'servi' => 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30',
                default => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
            };
        @endphp
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border shadow-lg overflow-hidden
            {{ $order->status === 'pret' || $order->status === 'servi' ? 'border-emerald-500/50' : 'border-gray-800' }}">
            <!-- Order Header -->
            <div class="p-4 border-b border-gray-800 flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-2">
                        @if($order->table)
                            <span class="text-xl font-bold text-white">Table {{ $order->table->numero ?? $order->table->name }}</span>
                        @else
                            @php
                                $loc = \Illuminate\Support\Str::before($order->waiter_notes ?? 'Commande client', ' | ');
                                $icon = str_starts_with($loc, 'Room') ? '🏨' : (str_starts_with($loc, 'Commande client - Piscine') ? '🏊' : '🍽️');
                            @endphp
                            <span class="text-base font-bold text-white">{{ $icon }} {{ $loc }}</span>
                        @endif
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-400">{{ $order->table->name ?? '' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-blue-400">{{ number_format($order->total, 2) }} DH</p>
                    <p class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="p-4 max-h-48 overflow-y-auto">
                <ul class="space-y-2">
                    @foreach($order->details as $detail)
                    <li class="flex justify-between items-start text-sm">
                        <div class="flex-1">
                            <span class="text-white">{{ $detail->quantity }}x {{ $detail->produit->name }}</span>
                            @if($detail->notes)
                            <p class="text-xs text-gray-500 italic">{{ $detail->notes }}</p>
                            @endif
                        </div>
                        <span class="text-gray-400">{{ number_format($detail->price * $detail->quantity, 2) }} DH</span>
                    </li>
                    @endforeach
                </ul>
                @if($order->waiter_notes)
                <div class="mt-3 pt-3 border-t border-gray-700">
                    <p class="text-xs text-gray-400">
                        <span class="font-semibold">Notes:</span> {{ $order->waiter_notes }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Order Footer -->
            <div class="p-4 bg-gray-950/50 border-t border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-400">Serveur: {{ $order->user_names ?? ($order->user->name ?? 'N/A') }}</span>
                    <span class="text-sm text-gray-500">{{ $order->order_refs ?? ('Cmd #' . $order->id) }}</span>
                </div>
                
                @if($order->ready_for_payment ?? true)
                <a href="{{ route('cashier.payment', $order->representative_commande_id ?? $order->id) }}" 
                   class="block w-full px-4 py-3 bg-emerald-600 text-white text-center font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                    Encaisser {{ number_format($order->total, 2) }} DH
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">Aucune commande en attente</h3>
                <p class="text-gray-500">Les commandes envoyées par les serveurs apparaîtront ici.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    window.location.reload();
}, 30000);
</script>
@endpush
</x-layout.app>
