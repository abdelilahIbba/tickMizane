<x-layout.app title="Dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Dashboard</h1>
            <p class="text-gray-400 mt-1">Vue d'ensemble de votre activité</p>
        </div>
        
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card 
                title="Ventes du jour" 
                value="{{ number_format($dailySales ?? 12500, 2) }} DH"
                color="amber"
                trend="+12%"
                :trend-up="true"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            
            <x-ui.stat-card 
                title="Transactions" 
                value="{{ $totalTransactions ?? 48 }}"
                color="blue"
                trend="+8%"
                :trend-up="true"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
            />
            
            <x-ui.stat-card 
                title="Produits en stock" 
                value="{{ $totalProducts ?? 156 }}"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
            />
            
            <x-ui.stat-card 
                title="Alertes stock" 
                value="{{ $stockAlerts ?? 5 }}"
                color="red"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
            />
        </div>
        
        {{-- Quick Actions --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Actions rapides</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" href="{{ route('pos.index') }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle vente
                </x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('products.create') }}">
                    Ajouter un produit
                </x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('commandes.create') }}">
                    Nouvelle commande
                </x-ui.button>
                <x-ui.button variant="info" href="{{ route('stock.index') }}">
                    Voir le stock
                </x-ui.button>
            </div>
        </div>
        
        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent Sales --}}
            <div class="lg:col-span-2">
                <x-ui.card title="Ventes récentes" subtitle="Dernières transactions">
                    <div class="space-y-4">
                        @for($i = 1; $i <= 5; $i++)
                            <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">Vente #{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</p>
                                        <p class="text-sm text-gray-400">{{ now()->subMinutes($i * 30)->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <span class="text-lg font-semibold text-amber-400">{{ number_format(rand(50, 500), 2) }} DH</span>
                            </div>
                        @endfor
                    </div>
                    
                    <x-slot:footer>
                        <a href="{{ route('ventes.index') }}" class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                            Voir toutes les ventes →
                        </a>
                    </x-slot:footer>
                </x-ui.card>
            </div>
            
            {{-- Stock Alerts --}}
            <div>
                <x-ui.card title="Alertes stock" subtitle="Produits à réapprovisionner">
                    <div class="space-y-3">
                        @foreach(['Eau minérale', 'Café moulu', 'Sucre 1kg', 'Lait 1L', 'Pain'] as $productName)
                            <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-white">{{ $productName }}</p>
                                        <p class="text-sm text-gray-400">Stock: {{ rand(1, 5) }}</p>
                                    </div>
                                    <x-ui.badge variant="danger">Bas</x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <x-slot:footer>
                        <a href="{{ route('stock.index') }}" class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                            Gérer le stock →
                        </a>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
        
        {{-- Pending Orders --}}
        <div class="mt-6">
            <x-ui.card title="Commandes en attente" subtitle="Commandes fournisseurs à recevoir">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @for($i = 1; $i <= 3; $i++)
                        <div class="p-4 bg-gray-900 rounded-xl border border-gray-700">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-white font-medium">Commande #{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</span>
                                <x-ui.badge variant="warning">En attente</x-ui.badge>
                            </div>
                            <p class="text-gray-400 text-sm">Fournisseur {{ $i }}</p>
                            <p class="text-amber-400 font-semibold mt-2">{{ number_format(rand(1000, 5000), 2) }} DH</p>
                        </div>
                    @endfor
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layout.app>
