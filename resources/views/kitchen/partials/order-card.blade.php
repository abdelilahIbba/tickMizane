<div class="bg-gray-900/50 backdrop-blur-sm rounded-lg shadow-lg p-4 border-l-4 
    @if($order->status === 'en_cuisine') border-orange-500
    @elseif($order->status === 'en_preparation') border-blue-500
    @elseif($order->status === 'pret') border-emerald-500
    @else border-gray-500
    @endif">
    <!-- Header -->
    <div class="flex justify-between items-start mb-3">
        <div>
            <h3 class="text-lg font-bold text-white">Table {{ $order->table->numero ?? 'N/A' }}</h3>
            <p class="text-sm text-gray-400">{{ $order->table->name ?? 'Non assignée' }}</p>
        </div>
        <div class="text-right">
            <span class="px-2 py-1 text-xs font-semibold rounded-full
                @if($order->status === 'en_cuisine') bg-orange-500/20 text-orange-400 border border-orange-500/30
                @elseif($order->status === 'en_preparation') bg-blue-500/20 text-blue-400 border border-blue-500/30
                @elseif($order->status === 'pret') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                @endif">
                {{ $order->status_label }}
            </span>
            <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Waiter Info -->
    <div class="mb-3 pb-3 border-b border-gray-700">
        <p class="text-sm text-gray-300">
            <span class="font-medium text-gray-400">Serveur:</span> {{ $order->user->name }}
        </p>
        <p class="text-xs text-gray-500">Cmd #{{ $order->id }} - {{ $order->created_at->format('H:i') }}</p>
    </div>

    <!-- Order Items -->
    <div class="space-y-2 mb-3">
        @foreach($order->details as $detail)
        <div class="flex justify-between items-start text-sm">
            <div class="flex-1">
                <span class="font-semibold text-white">{{ $detail->quantity }}x</span>
                <span class="text-gray-300">{{ $detail->produit->name }}</span>
                @if($detail->notes)
                <p class="text-xs text-blue-400 italic mt-1">📝 {{ $detail->notes }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Notes -->
    @if($order->waiter_notes)
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded p-2 mb-3">
        <p class="text-xs text-yellow-400">
            <span class="font-semibold">Notes:</span> {{ $order->waiter_notes }}
        </p>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex gap-2">
        @if($order->status === 'en_cuisine')
        <form action="{{ route('kitchen.order.status', $order) }}" method="POST" class="flex-1">
            @csrf
            <input type="hidden" name="status" value="en_preparation">
            <button type="submit" 
                    class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                ✅ Valider
            </button>
        </form>
        @elseif($order->status === 'en_preparation')
        <form action="{{ route('kitchen.order.ready', $order) }}" method="POST" class="flex-1">
            @csrf
            <button type="submit" 
                    class="w-full px-3 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                🔔 Commande prête
            </button>
        </form>
        @elseif($order->status === 'pret')
        <div class="flex-1 px-3 py-2 bg-emerald-900/50 text-emerald-400 text-sm font-bold rounded-lg border border-emerald-500/30 text-center uppercase tracking-wider">
            PRÊT POUR LA CAISSE
        </div>
        @endif
        <a href="{{ route('kitchen.ticket', $order) }}" 
           target="_blank"
           class="px-3 py-2 bg-gray-800 text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            🖨
        </a>
    </div>
</div>
