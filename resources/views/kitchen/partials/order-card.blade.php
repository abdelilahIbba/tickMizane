<div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-orange-500">
    <!-- Header -->
    <div class="flex justify-between items-start mb-3">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Table {{ $order->table->numero ?? 'N/A' }}</h3>
            <p class="text-sm text-gray-600">{{ $order->table->name ?? 'Non assignée' }}</p>
        </div>
        <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full">
            {{ $order->created_at->diffForHumans() }}
        </span>
    </div>

    <!-- Waiter Info -->
    <div class="mb-3 pb-3 border-b">
        <p class="text-sm text-gray-600">
            <span class="font-medium">Serveur:</span> {{ $order->user->name }}
        </p>
        <p class="text-xs text-gray-500">Commande #{{ $order->id }} - {{ $order->created_at->format('H:i') }}</p>
    </div>

    <!-- Order Items -->
    <div class="space-y-2 mb-3">
        @foreach($order->details as $detail)
        <div class="flex justify-between items-start text-sm">
            <div class="flex-1">
                <span class="font-semibold">{{ $detail->quantity }}x</span>
                <span class="text-gray-900">{{ $detail->produit->name }}</span>
                @if($detail->notes)
                <p class="text-xs text-blue-600 italic mt-1">📝 {{ $detail->notes }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Notes -->
    @if($order->waiter_notes)
    <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-3">
        <p class="text-xs text-yellow-800">
            <span class="font-semibold">Notes:</span> {{ $order->waiter_notes }}
        </p>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex gap-2">
        <form action="{{ route('kitchen.order.served', $order) }}" method="POST" class="flex-1">
            @csrf
            <button type="submit" 
                    class="w-full px-3 py-2 bg-green-600 text-white text-sm font-semibold rounded hover:bg-green-700">
                ✓ Servi
            </button>
        </form>
        <a href="{{ route('kitchen.ticket', $order) }}" 
           target="_blank"
           class="px-3 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded hover:bg-gray-300">
            🖨
        </a>
    </div>
</div>
