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