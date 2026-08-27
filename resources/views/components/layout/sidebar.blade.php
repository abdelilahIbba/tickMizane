@php
    $user = auth()->user();
    $role = $user?->role ?? 'guest';

    // Explicit active patterns avoid broad prefix matches that can highlight multiple buttons.
    $activePatterns = [
        'dashboard' => ['dashboard'],
        'pos' => ['pos.*'],
        'ventes' => ['ventes.*'],
        'tables' => ['tables.*'],
        'waiter' => ['waiter.*'],
        'kitchen' => ['kitchen.index', 'kitchen.order.*', 'kitchen.orders.*', 'kitchen.stats'],
        'display' => ['kitchen.display'],
        'cashier' => ['cashier.pending', 'cashier.payment', 'cashier.process-payment', 'cashier.history', 'cashier.receipt', 'cashier.receipt.*', 'cashier.stats'],
        'products' => ['products.*'],
        'categories' => ['categories.*'],
        'stock' => ['stock.*'],
        'commandes' => ['commandes.*', 'orders.*'],
        'fournisseurs' => ['fournisseurs.*'],
        'payments' => ['payments.*'],
        'cashier_history' => ['cashier.history'],
        'articles' => ['settings.articles.*'],
        'zones' => ['waiter.settings.zones*'],
        'system' => ['settings.system.*'],
        'users' => ['settings.users.*'],
        'permissions' => ['settings.permissions.*'],
        'wifi_qr' => ['settings.wifi-qr.*'],
        'licenses' => ['settings.licenses.*'],
    ];
    
    // Define navigation groups with labels
    $navGroups = [
        'main' => [
            'label' => 'Principal',
            'items' => [
                'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'],
                'pos' => ['label' => 'POS', 'route' => 'pos.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
                'ventes' => ['label' => 'Ventes', 'route' => 'ventes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
                'tables' => ['label' => 'Tables', 'route' => 'tables.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
            ],
        ],
        'operations' => [
            'label' => 'Opérations',
            'items' => [
                'waiter' => ['label' => 'Prise de commande', 'route' => 'waiter.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
                'kitchen' => ['label' => 'Cuisine', 'route' => 'kitchen.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>'],
                'display' => ['label' => 'Écran cuisine', 'route' => 'kitchen.display', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 4h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>'],
                'cashier' => ['label' => 'Encaissement', 'route' => 'cashier.pending', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'],
                'commandes' => ['label' => 'Commandes', 'route' => 'commandes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            ],
        ],
        'inventory' => [
            'label' => 'Inventaire',
            'items' => [
                'products' => ['label' => 'Produits', 'route' => 'products.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                'categories' => ['label' => 'Catégories', 'route' => 'categories.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                'stock' => ['label' => 'Stock', 'route' => 'stock.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],
            ],
        ],
        'suppliers' => [
            'label' => 'Fournisseurs',
            'items' => [
                'fournisseurs' => ['label' => 'Fournisseurs', 'route' => 'fournisseurs.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
            ],
        ],
        'finance' => [
            'label' => 'Finance',
            'items' => [
                'payments' => ['label' => 'Paiements', 'route' => 'payments.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                'cashier_history' => ['label' => 'Historique Caisse', 'route' => 'cashier.history', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
            ],
        ],
        'settings' => [
            'label' => 'Paramètres',
            'items' => [
                'articles' => ['label' => 'Articles', 'route' => 'settings.articles.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
                'zones' => ['label' => 'Zones', 'route' => 'waiter.settings.zones', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h8v6H3V7zm10 0h8v4h-8V7zM3 15h6v4H3v-4zm8-2h10v6H11v-6z"/>'],
                'system' => ['label' => 'Système', 'route' => 'settings.system.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                'users' => ['label' => 'Utilisateurs', 'route' => 'settings.users.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
                'permissions' => ['label' => 'Permissions', 'route' => 'settings.permissions.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
                'wifi_qr' => ['label' => 'Wi-Fi & Commande Client', 'route' => 'settings.wifi-qr.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>'],
                'licenses' => ['label' => 'Licences clients', 'route' => 'settings.licenses.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'],
            ],
        ],
    ];
    
    // Role-based access
    // Super User = admin (uses activated licenses, no license management)
    // Super Admin = synthetic role (exclusive license management)
    $roleAccess = [
        'super_admin' => [
            'main' => ['dashboard'],
            'operations' => ['waiter', 'kitchen', 'display', 'cashier', 'commandes'],
            'inventory' => ['products', 'categories', 'stock'],
            'suppliers' => ['fournisseurs'],
            'finance' => ['payments', 'cashier_history'],
            'settings' => ['licenses', 'articles', 'zones', 'system', 'users', 'permissions', 'wifi_qr'],
        ],
        'admin' => [
            'main' => ['dashboard'],
            'operations' => ['waiter', 'kitchen', 'display', 'cashier', 'commandes'],
            'inventory' => ['products', 'categories', 'stock'],
            'suppliers' => ['fournisseurs'],
            'finance' => ['payments', 'cashier_history'],
            'settings' => ['articles', 'zones', 'system', 'users', 'permissions', 'wifi_qr'],
        ],
        'caissier' => [
            'operations' => ['kitchen', 'cashier', 'commandes'],
        ],
        'serveur' => [
            'operations' => ['waiter', 'kitchen', 'cashier', 'commandes'],
        ],
    ];
    
    $access = $roleAccess[$role] ?? [];
    $roleLabel = match ($role) {
        'super_admin' => 'Super Admin',
        'admin' => 'Super User',
        'caissier' => 'Caissier',
        'serveur' => 'Serveur',
        default => $role,
    };
@endphp

@auth
{{-- Mobile Header --}}
<header class="md:hidden bg-gray-900 border-b border-gray-800 sticky top-0 z-50 w-full flex-shrink-0">
    <div class="flex items-center justify-between h-16 px-4 w-full">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25">
                <span class="text-gray-900 font-bold text-xl">T</span>
            </div>
            <span class="text-lg font-bold text-white">
                Tech<span class="text-amber-400">mizane</span>
            </span>
        </a>
        
        {{-- Mobile Menu Button --}}
        <button 
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
        >
            <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="sidebarOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</header>

{{-- Sidebar Overlay (Mobile) --}}
<div 
    x-show="sidebarOpen" 
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden"
    x-cloak
></div>

{{-- Sidebar --}}
<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-gray-900 border-r border-gray-800 transform transition-transform duration-300 ease-in-out flex flex-col h-full"
>
    {{-- Sidebar Header (Desktop) --}}
    <div class="hidden md:flex items-center gap-3 h-16 px-6 border-b border-gray-800 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('logo.svg') }}" alt="Techmizane" class="w-10 h-10 drop-shadow-xl shadow-amber-500/20">
            <span class="text-lg font-bold text-white">
                Tech<span class="text-amber-400">mizane</span>
            </span>
        </a>
    </div>
    
    {{-- Close button (Mobile) --}}
    <div class="md:hidden flex items-center justify-between h-16 px-4 border-b border-gray-800 flex-shrink-0">
        <span class="text-lg font-bold text-white">Menu</span>
        <button @click="sidebarOpen = false" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    
    {{-- Navigation - Scrollable --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
        @foreach($navGroups as $groupKey => $group)
            @if(!empty($access[$groupKey]))
                <div>
                    {{-- Group Label --}}
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ $group['label'] }}
                    </h3>
                    
                    {{-- Group Items --}}
                    <div class="space-y-1">
                        @foreach($access[$groupKey] as $key)
                            @if(isset($group['items'][$key]))
                                @php
                                    $item = $group['items'][$key];
                                    $patterns = $activePatterns[$key] ?? [$item['route'], $item['route'] . '.*'];
                                    $isActive = false;
                                    foreach ($patterns as $pattern) {
                                        if (request()->routeIs($pattern)) {
                                            $isActive = true;
                                            break;
                                        }
                                    }
                                @endphp
                                <a 
                                    href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                    @click="sidebarOpen = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group
                                           {{ $isActive
                                              ? 'bg-amber-500/20 text-amber-400 border-l-2 border-amber-400' 
                                              : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}"
                                >
                                    <svg class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'text-amber-400' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $item['icon'] !!}
                                    </svg>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>
    
    {{-- User Section --}}
    <div class="flex-shrink-0 border-t border-gray-800 p-4">
        <div class="flex items-center gap-3 px-3 py-3 mb-3 bg-gray-800/50 rounded-xl">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-white truncate">{{ $user->name }}</div>
                <div class="text-xs text-gray-400">{{ $roleLabel }}</div>
            </div>
        </div>
        
        {{-- Logout --}}
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-red-400 hover:text-red-300 hover:bg-gray-800/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>
@endauth
