@php
    $user = auth()->user();
    $role = $user?->role ?? 'guest';
    
    // Define navigation groups
    $navGroups = [
        'main' => [
            'pos' => ['label' => 'POS', 'route' => 'pos.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
            'ventes' => ['label' => 'Ventes', 'route' => 'ventes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
            'tables' => ['label' => 'Tables', 'route' => 'tables.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
        ],
        'operations' => [
            'waiter' => ['label' => 'Serveur', 'route' => 'waiter.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
            'kitchen' => ['label' => 'Cuisine', 'route' => 'kitchen.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>'],
            'cashier' => ['label' => 'Caisse', 'route' => 'cashier.pending', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'],
        ],
        'inventory' => [
            'products' => ['label' => 'Produits', 'route' => 'products.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
            'categories' => ['label' => 'Catégories', 'route' => 'categories.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
            'stock' => ['label' => 'Stock', 'route' => 'stock.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],
        ],
        'suppliers' => [
            'commandes' => ['label' => 'Commandes', 'route' => 'commandes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            'fournisseurs' => ['label' => 'Fournisseurs', 'route' => 'fournisseurs.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
        ],
        'finance' => [
            'payments' => ['label' => 'Paiements', 'route' => 'payments.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
            'cashier_history' => ['label' => 'Historique Caisse', 'route' => 'cashier.history', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ],
    ];
    
    // Role-based access
    $roleAccess = [
        'admin' => [
            'main' => ['pos', 'ventes', 'tables'],
            'operations' => ['waiter', 'kitchen', 'cashier'],
            'inventory' => ['products', 'categories', 'stock'],
            'suppliers' => ['commandes', 'fournisseurs'],
            'finance' => ['payments', 'cashier_history'],
            'showDashboard' => true,
        ],
        'caissier' => [
            'main' => ['pos', 'ventes'],
            'operations' => ['cashier'],
            'finance' => ['payments', 'cashier_history'],
            'showDashboard' => false,
        ],
        'serveur' => [
            'main' => ['tables', 'ventes'],
            'operations' => ['waiter'],
            'showDashboard' => false,
        ],
    ];
    
    $access = $roleAccess[$role] ?? [];
@endphp

@auth
<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-40 flex-shrink-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.svg') }}" alt="Techmizane" class="w-10 h-10 drop-shadow-xl shadow-amber-500/20">
                    <span class="text-xl font-bold text-white hidden sm:block">
                        Techmizane <span class="text-amber-400">Cash</span>
                    </span>
                </a>
            </div>
            
            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex items-center gap-2">
                {{-- Dashboard (admin only) --}}
                @if($access['showDashboard'] ?? false)
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ request()->routeIs('dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @endif
                
                {{-- Main Links (direct links) --}}
                @foreach($access['main'] ?? [] as $key)
                    @if(isset($navGroups['main'][$key]))
                        @php $item = $navGroups['main'][$key]; @endphp
                        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                                  {{ request()->routeIs($item['route'] . '*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                
                {{-- Operations Links (Waiter, Kitchen, Cashier) --}}
                @foreach($access['operations'] ?? [] as $key)
                    @if(isset($navGroups['operations'][$key]))
                        @php $item = $navGroups['operations'][$key]; @endphp
                        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                                  {{ request()->routeIs(explode('.', $item['route'])[0] . '.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                
                {{-- Finance Link --}}
                @foreach($access['finance'] ?? [] as $key)
                    @if(isset($navGroups['finance'][$key]))
                        @php $item = $navGroups['finance'][$key]; @endphp
                        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                                  {{ request()->routeIs($item['route'] . '*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                
                {{-- Inventory Dropdown (admin only) --}}
                @if(!empty($access['inventory']))
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white hover:bg-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Inventaire</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition x-cloak
                             class="absolute top-full left-0 mt-1 w-48 bg-gray-800 border border-gray-700 rounded-xl shadow-xl py-2 z-50">
                            @foreach($access['inventory'] as $key)
                                @if(isset($navGroups['inventory'][$key]))
                                    @php $item = $navGroups['inventory'][$key]; @endphp
                                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                       class="flex items-center gap-3 px-4 py-2 text-sm transition-all
                                              {{ request()->routeIs($item['route'] . '*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                                        {{ $item['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                
                {{-- Suppliers Dropdown (admin only) --}}
                @if(!empty($access['suppliers']))
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white hover:bg-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Achats</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition x-cloak
                             class="absolute top-full left-0 mt-1 w-48 bg-gray-800 border border-gray-700 rounded-xl shadow-xl py-2 z-50">
                            @foreach($access['suppliers'] as $key)
                                @if(isset($navGroups['suppliers'][$key]))
                                    @php $item = $navGroups['suppliers'][$key]; @endphp
                                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                       class="flex items-center gap-3 px-4 py-2 text-sm transition-all
                                              {{ request()->routeIs($item['route'] . '*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                                        {{ $item['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- User Menu with Dropdown --}}
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                {{-- User Profile Dropdown --}}
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-3 px-2 py-1 rounded-xl hover:bg-gray-800 transition-all cursor-pointer">
                        <div class="text-right">
                            <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                            <div class="text-xs text-gray-400 capitalize">{{ $user->role }}</div>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    {{-- Dropdown Menu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-50">
                        
                        {{-- User Info Header --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ $user->role }}</p>
                        </div>
                        
                        {{-- Settings Link --}}
                        <a href="#" 
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </a>
                        
                        {{-- Divider --}}
                        <div class="border-t border-gray-100 my-1"></div>
                        
                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
                
                {{-- Mobile Menu Button --}}
                <button type="button" class="lg:hidden p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg"
                        x-data @click="$dispatch('toggle-mobile-menu')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    {{-- Mobile Navigation --}}
    <div x-data="{ open: false }" x-show="open" x-on:toggle-mobile-menu.window="open = !open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
         x-cloak class="lg:hidden border-t border-gray-800 max-h-[70vh] overflow-y-auto">
        <div class="px-4 py-4 space-y-2">
            {{-- Mobile User Info --}}
            <div class="flex items-center gap-3 px-4 py-3 mb-2 bg-gray-800/50 rounded-xl">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                    <div class="text-xs text-gray-400 capitalize">{{ $user->role }}</div>
                </div>
            </div>
            
            {{-- Dashboard --}}
            @if($access['showDashboard'] ?? false)
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all
                          {{ request()->routeIs('dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Dashboard
                </a>
            @endif
            
            {{-- All accessible items --}}
            @foreach(['main', 'operations', 'finance', 'inventory', 'suppliers'] as $group)
                @foreach($access[$group] ?? [] as $key)
                    @if(isset($navGroups[$group][$key]))
                        @php $item = $navGroups[$group][$key]; @endphp
                        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all
                                  {{ request()->routeIs(explode('.', $item['route'])[0] . '.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            @endforeach
            
            {{-- Mobile Settings & Logout --}}
            <div class="border-t border-gray-700 mt-4 pt-4 space-y-2">
                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium transition-all text-gray-400 hover:text-white hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-base font-medium transition-all text-red-400 hover:text-red-300 hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endauth
