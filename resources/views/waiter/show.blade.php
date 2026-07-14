<x-layout.app title="Commande #{{ $commande->id }}">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Commande #{{ $commande->id }}</h1>
            <p class="text-gray-400 mt-1">Table {{ $commande->table->numero ?? 'N/A' }} - {{ $commande->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('waiter.orders') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors font-medium">
            Retour
        </a>
    </div>

    {{-- Disposition réactive : Empilement vertical sur mobile, et grille à 3 colonnes sur grand écran --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-gray-900/50 backdrop-blur-sm rounded-xl border border-gray-800 shadow-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Statut</h2>
                <div class="flex items-center gap-3">
                    @if($commande->status === 'en_preparation')
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30">
                            🔥 En préparation
                        </span>
                    @elseif($commande->status === 'servi')
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                            ✓ Servi
                        </span>
                    @else
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gray-800 text-gray-400 border border-gray-700">
                            {{ ucfirst($commande->status) }}
                        </span>
                    @endif
                    <span class="text-sm text-gray-500">{{ $commande->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-gray-900/50 backdrop-blur-sm rounded-xl border border-gray-800 shadow-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Articles commandés</h2>
                <div class="space-y-3">
                    @foreach($commande->details as $detail)
                    <div class="flex justify-between items-start pb-3 border-b border-gray-800 last:border-0">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-amber-400">{{ $detail->quantity }}x</span>
                                <span class="text-gray-200">{{ $detail->produit->name }}</span>
                            </div>
                            @if($detail->notes)
                            <p class="text-sm text-blue-400 italic mt-1">📝 {{ $detail->notes }}</p>
                            @endif
                        </div>
                        <span class="text-white font-semibold">
                            {{ number_format($detail->quantity * $detail->price, 2) }} DH
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            @if($commande->waiter_notes)
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                <h3 class="font-semibold text-amber-400 mb-2">Notes générales</h3>
                <p class="text-amber-300">{{ $commande->waiter_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-gray-900/50 backdrop-blur-sm rounded-xl border border-gray-800 shadow-lg p-6 sticky top-6">
                <h2 class="text-lg font-bold text-white mb-4">Résumé</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Table:</span>
                        <span class="font-semibold text-white">{{ $commande->table->numero ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Serveur:</span>
                        <span class="font-semibold text-white">{{ $commande->user->name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Articles:</span>
                        <span class="font-semibold text-white">{{ $commande->details->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Date:</span>
                        <span class="font-semibold text-white">{{ $commande->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Heure:</span>
                        <span class="font-semibold text-white">{{ $commande->created_at->format('H:i') }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-800 pt-4 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-white">Total:</span>
                        <span class="text-2xl font-bold text-amber-400">{{ number_format($commande->total, 2) }} DH</span>
                    </div>
                </div>

                <!-- Print Ticket -->
                <a href="{{ route('kitchen.ticket', $commande) }}" 
                   target="_blank"
                   class="block w-full px-4 py-3 bg-gray-800 text-gray-300 text-center font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                    🖨 Imprimer ticket
                </a>
            </div>
        </div>
    </div>
</div>
</x-layout.app>
