@php
    $role = session('user_role', 'guest');
    
    $allNavItems = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'],
        'categories' => ['label' => 'Catégories', 'route' => 'categories.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
        'products' => ['label' => 'Produits', 'route' => 'products.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        'pos' => ['label' => 'POS', 'route' => 'pos.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
        'ventes' => ['label' => 'Ventes', 'route' => 'ventes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
        'commandes' => ['label' => 'Commandes', 'route' => 'commandes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
        'fournisseurs' => ['label' => 'Fournisseurs', 'route' => 'fournisseurs.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
        'stock' => ['label' => 'Stock', 'route' => 'stock.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],
        'payments' => ['label' => 'Paiements', 'route' => 'payments.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
        'tables' => ['label' => 'Tables', 'route' => 'tables.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
    ];
    
    $roleNavItems = [
        'admin' => ['dashboard', 'categories', 'products', 'pos', 'ventes', 'commandes', 'fournisseurs', 'stock', 'payments', 'tables'],
        'caissier' => ['pos', 'ventes', 'payments'],
        'serveur' => ['tables', 'ventes'],
    ];
    
    $visibleItems = $roleNavItems[$role] ?? [];
@endphp

<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25">
                        <span class="text-gray-900 font-bold text-xl">T</span>
                    </div>
                    <span class="text-xl font-bold text-white hidden sm:block">
                        Techmizane <span class="text-amber-400">Cash</span>
                    </span>
                </a>
            </div>
            
            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex items-center gap-1">
                @foreach($visibleItems as $key)
                    @if(isset($allNavItems[$key]))
                        @php $item = $allNavItems[$key]; @endphp
                        <a 
                            href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                                   {{ request()->routeIs($item['route'] . '*') 
                                      ? 'bg-amber-500/20 text-amber-400' 
                                      : 'text-gray-400 hover:text-white hover:bg-gray-800' }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item['icon'] !!}
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
            
            {{-- User Menu --}}
            <div class="flex items-center gap-4">
                {{-- Role Badge --}}
                @if($role !== 'guest')
                    <span class="hidden sm:inline-flex px-3 py-1 bg-blue-500/20 text-blue-400 text-sm font-medium rounded-full capitalize">
                        {{ $role }}
                    </span>
                @endif
                
                {{-- Logout --}}
                @if($role !== 'guest')
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button 
                            type="submit"
                            class="flex items-center gap-2 px-4 py-2 text-gray-400 hover:text-red-400 hover:bg-gray-800 rounded-xl transition-all duration-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                @endif
                
                {{-- Mobile Menu Button --}}
                <button 
                    type="button"
                    class="lg:hidden p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-xl"
                    x-data
                    @click="$dispatch('toggle-mobile-menu')"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    {{-- Mobile Navigation --}}
    <div 
        x-data="{ open: false }"
        x-show="open"
        x-on:toggle-mobile-menu.window="open = !open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        x-cloak
        class="lg:hidden border-t border-gray-800"
    >
        <div class="px-4 py-4 space-y-2">
            @foreach($visibleItems as $key)
                @if(isset($allNavItems[$key]))
                    @php $item = $allNavItems[$key]; @endphp
                    <a 
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all duration-200
                               {{ request()->routeIs($item['route'] . '*') 
                                  ? 'bg-amber-500/20 text-amber-400' 
                                  : 'text-gray-400 hover:text-white hover:bg-gray-800' }}"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $item['icon'] !!}
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</nav>
