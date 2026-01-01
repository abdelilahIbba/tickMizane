<x-layout.app title="Table #{{ $table->number ?? '01' }}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('tables.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux tables
            </a>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-amber-500/20 rounded-2xl flex items-center justify-center">
                    <span class="text-2xl font-bold text-amber-400">{{ $table->number ?? '01' }}</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">Table {{ $table->number ?? '01' }}</h1>
                    <p class="text-gray-400">{{ $table->seats ?? 4 }} places • Occupée depuis 45 min</p>
                </div>
            </div>
        </div>
        
        {{-- Quick Info --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Serveur</span>
                    <div class="text-white font-semibold mt-1">{{ $table->server ?? 'Youssef' }}</div>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Couverts</span>
                    <div class="text-white font-semibold mt-1">{{ $table->guests ?? 3 }}</div>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Total</span>
                    <div class="text-amber-400 font-bold mt-1">{{ number_format($table->amount ?? 245.50, 2) }} DH</div>
                </div>
            </x-ui.card>
        </div>
        
        {{-- Current Order Items --}}
        <x-ui.card title="Commande en cours" class="mb-6">
            <div class="space-y-4">
                @foreach([
                    ['name' => 'Tagine Agneau', 'qty' => 2, 'price' => 85.00, 'status' => 'served'],
                    ['name' => 'Couscous Royal', 'qty' => 1, 'price' => 95.00, 'status' => 'served'],
                    ['name' => 'Salade Marocaine', 'qty' => 3, 'price' => 25.00, 'status' => 'served'],
                    ['name' => 'Thé à la menthe', 'qty' => 4, 'price' => 15.00, 'status' => 'pending'],
                    ['name' => 'Pastilla', 'qty' => 1, 'price' => 65.00, 'status' => 'preparing'],
                ] as $item)
                    <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center text-white font-medium">
                                {{ $item['qty'] }}
                            </span>
                            <span class="text-white">{{ $item['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            @if($item['status'] === 'served')
                                <x-ui.badge variant="success">Servi</x-ui.badge>
                            @elseif($item['status'] === 'preparing')
                                <x-ui.badge variant="warning">En préparation</x-ui.badge>
                            @else
                                <x-ui.badge variant="info">En attente</x-ui.badge>
                            @endif
                            <span class="text-amber-400 font-semibold">{{ number_format($item['qty'] * $item['price'], 2) }} DH</span>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-700">
                <div class="flex justify-between text-lg">
                    <span class="text-white font-semibold">Total</span>
                    <span class="text-amber-400 font-bold">{{ number_format(245.50, 2) }} DH</span>
                </div>
            </div>
        </x-ui.card>
        
        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-4">
            <x-ui.button variant="primary" size="xl" href="{{ route('pos.index', ['table' => $table->id ?? 1]) }}" class="w-full justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter des articles
            </x-ui.button>
            <x-ui.button variant="success" size="xl" class="w-full justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Encaisser
            </x-ui.button>
            <x-ui.button variant="info" size="xl" class="w-full justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Changer de table
            </x-ui.button>
            <x-ui.button variant="secondary" size="xl" class="w-full justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer addition
            </x-ui.button>
        </div>
    </div>
</x-layout.app>
