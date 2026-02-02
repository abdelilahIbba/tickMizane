<x-layout.app title="Commande #{{ $commande->id }}">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Commande #{{ $commande->id }}</h1>
            <p class="text-gray-600 mt-1">Table {{ $commande->table->numero ?? 'N/A' }} - {{ $commande->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('waiter.orders') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Statut</h2>
                <div class="flex items-center gap-3">
                    @if($commande->status === 'en_preparation')
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                            🔥 En préparation
                        </span>
                    @elseif($commande->status === 'servi')
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            ✓ Servi
                        </span>
                    @else
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ ucfirst($commande->status) }}
                        </span>
                    @endif
                    <span class="text-sm text-gray-500">{{ $commande->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Articles commandés</h2>
                <div class="space-y-3">
                    @foreach($commande->details as $detail)
                    <div class="flex justify-between items-start pb-3 border-b last:border-0">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ $detail->quantity }}x</span>
                                <span class="text-gray-900">{{ $detail->produit->name }}</span>
                            </div>
                            @if($detail->notes)
                            <p class="text-sm text-blue-600 italic mt-1">📝 {{ $detail->notes }}</p>
                            @endif
                        </div>
                        <span class="text-gray-900 font-semibold">
                            {{ number_format($detail->quantity * $detail->price, 2) }} DH
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            @if($commande->waiter_notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="font-semibold text-yellow-900 mb-2">Notes générales</h3>
                <p class="text-yellow-800">{{ $commande->waiter_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Résumé</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Table:</span>
                        <span class="font-semibold">{{ $commande->table->numero ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Serveur:</span>
                        <span class="font-semibold">{{ $commande->user->name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Articles:</span>
                        <span class="font-semibold">{{ $commande->details->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Date:</span>
                        <span class="font-semibold">{{ $commande->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Heure:</span>
                        <span class="font-semibold">{{ $commande->created_at->format('H:i') }}</span>
                    </div>
                </div>

                <div class="border-t pt-4 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total:</span>
                        <span class="text-2xl font-bold text-blue-600">{{ number_format($commande->total, 2) }} DH</span>
                    </div>
                </div>

                <!-- Print Ticket -->
                <a href="{{ route('kitchen.ticket', $commande) }}" 
                   target="_blank"
                   class="block w-full px-4 py-3 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300">
                    🖨 Imprimer ticket
                </a>
            </div>
        </div>
    </div>
</div>
</x-layout.app>
