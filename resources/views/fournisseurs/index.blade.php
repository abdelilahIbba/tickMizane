<x-layout.app title="Fournisseurs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Fournisseurs</h1>
                <p class="text-gray-400 mt-1">Gérez vos fournisseurs</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('fournisseurs.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau fournisseur
            </x-ui.button>
        </div>
        
        {{-- Success Alert --}}
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        
        {{-- Fournisseurs Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['id' => 1, 'name' => 'Boissons Maroc SARL', 'phone' => '+212 5XX-XXXXXX', 'email' => 'contact@boissons.ma', 'address' => 'Zone Industrielle, Casablanca', 'orders' => 24],
                ['id' => 2, 'name' => 'Épicerie Gros', 'phone' => '+212 5XX-XXXXXX', 'email' => 'info@epiceriegros.ma', 'address' => 'Hay Mohammadi, Casablanca', 'orders' => 18],
                ['id' => 3, 'name' => 'Snacks Distribution', 'phone' => '+212 5XX-XXXXXX', 'email' => 'contact@snacks.ma', 'address' => 'Ain Sebaa, Casablanca', 'orders' => 15],
                ['id' => 4, 'name' => 'Hygiène Plus', 'phone' => '+212 5XX-XXXXXX', 'email' => 'vente@hygieneplus.ma', 'address' => 'Sidi Bernoussi, Casablanca', 'orders' => 8],
                ['id' => 5, 'name' => 'Électro Accessoires', 'phone' => '+212 5XX-XXXXXX', 'email' => 'info@electro.ma', 'address' => 'Derb Omar, Casablanca', 'orders' => 5],
            ] as $fournisseur)
                <x-ui.card :hover="true">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex gap-2">
                            <x-ui.button variant="ghost" size="sm" href="{{ route('fournisseurs.edit', $fournisseur['id']) }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </x-ui.button>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-white mb-2">{{ $fournisseur['name'] }}</h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $fournisseur['phone'] }}
                        </div>
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $fournisseur['email'] }}
                        </div>
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $fournisseur['address'] }}
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-700 flex justify-between items-center">
                        <span class="text-sm text-gray-400">Commandes</span>
                        <span class="text-amber-400 font-semibold">{{ $fournisseur['orders'] }}</span>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    </div>
</x-layout.app>
