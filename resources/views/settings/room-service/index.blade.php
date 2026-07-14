<x-layout.app title="Room Service — Gestion et Commandes">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ currentTab: 'validation' }">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Service de chambre (Room Service)</h1>
            <p class="text-gray-400 mt-1">Gérez le menu public et suivez les commandes des chambres d'hôtel</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.room-service.qr-codes') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-xl border border-gray-700 transition-colors text-sm">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/>
                </svg>
                Codes QR (كود باك)
            </a>
            <button onclick="openAddMenuItemModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-semibold rounded-xl transition-colors shadow-lg shadow-amber-500/25 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter au Menu
            </button>
        </div>
    </div>

    {{-- ── Alert Notifications ─────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Dynamic Statistics ────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-amber-500/10 text-amber-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">{{ count($pendingOrders) }}</span>
            </div>
            <div>
                <span class="block text-xs text-gray-500 uppercase font-semibold">En attente</span>
                <span class="text-sm font-bold text-white">Validation Caissier</span>
            </div>
        </div>

        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">
                    {{ collect($activeOrders)->whereIn('status', ['approved', 'preparing'])->count() }}
                </span>
            </div>
            <div>
                <span class="block text-xs text-gray-500 uppercase font-semibold">En Cuisine</span>
                <span class="text-sm font-bold text-white">Préparation active</span>
            </div>
        </div>

        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">
                    {{ collect($activeOrders)->where('status', 'ready')->count() }}
                </span>
            </div>
            <div>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Prêt / En route</span>
                <span class="text-sm font-bold text-white">Prêt pour livraison</span>
            </div>
        </div>

        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-purple-500/10 text-purple-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">{{ count($menuItems) }}</span>
            </div>
            <div>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Articles Menu</span>
                <span class="text-sm font-bold text-white">Publiés sur la carte</span>
            </div>
        </div>
    </div>

    {{-- ── Tab Switcher Menu ───────────────────────────────────── --}}
    <div class="border-b border-gray-800 mb-6 flex gap-4 overflow-x-auto">
        <button @click="currentTab = 'validation'"
                :class="currentTab === 'validation' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="py-3 px-4 border-b-2 font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
            🧾 Validation Caissier
            <span class="bg-amber-500 text-dark-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full" x-show="{{ count($pendingOrders) }} > 0">{{ count($pendingOrders) }}</span>
        </button>
        <button @click="currentTab = 'kitchen'"
                :class="currentTab === 'kitchen' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="py-3 px-4 border-b-2 font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
            🍳 Cuisine & Livraison
            <span class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full" x-show="{{ count($activeOrders) }} > 0">{{ count($activeOrders) }}</span>
        </button>
        <button @click="currentTab = 'menu'"
                :class="currentTab === 'menu' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="py-3 px-4 border-b-2 font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
            🍔 Gestion de la Carte (CRUD)
        </button>
        <button @click="currentTab = 'history'"
                :class="currentTab === 'history' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="py-3 px-4 border-b-2 font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
            ✅ Historique des Livraisons
        </button>
    </div>

    {{-- ───────────────── TAB 1: VALIDATION CAISSIER ───────────────── --}}
    <div x-show="currentTab === 'validation'" x-cloak class="space-y-4">
        @if(count($pendingOrders) === 0)
            <div class="text-center py-16 bg-gray-900/40 border border-gray-800 rounded-3xl text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold">Aucune commande de chambre en attente de validation.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($pendingOrders as $order)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden flex flex-col justify-between">
                        
                        <!-- Top bar -->
                        <div class="px-5 py-3.5 bg-gray-950/60 border-b border-gray-800/60 flex justify-between items-center">
                            <div>
                                <span class="text-xs text-gray-500">Ticket</span>
                                <h3 class="text-base font-extrabold text-white font-mono">{{ $order['id'] }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] text-gray-500 uppercase font-bold">Chambre</span>
                                <span class="text-lg font-extrabold text-amber-500 font-mono">{{ $order['room_number'] }}</span>
                            </div>
                        </div>

                        <!-- Ticket Details -->
                        <div class="p-5 space-y-4 flex-1">
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>Reçu le : {{ $order['created_at'] }}</span>
                                <span class="bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold px-2 py-0.5 rounded-full">
                                    Livraison : {{ $order['delivery_time'] === 'asap' ? 'ASAP' : $order['delivery_time'] }}
                                </span>
                            </div>

                            <!-- Group items by Guest -->
                            <div class="space-y-3 pt-2">
                                @php
                                    $guests = collect($order['items'])->pluck('guest')->unique();
                                @endphp
                                @foreach($guests as $guest)
                                    <div class="bg-gray-950/40 rounded-xl p-3 border border-gray-800/40">
                                        <h4 class="text-xs font-bold text-amber-400/80 mb-2 border-b border-gray-800 pb-1">
                                            👤 Client : {{ $guest }}
                                        </h4>
                                        <div class="space-y-1">
                                            @foreach(collect($order['items'])->where('guest', $guest) as $item)
                                                <div class="flex justify-between text-xs text-gray-300">
                                                    <span>
                                                        {{ $item['name'] }} 
                                                        <span class="font-mono text-gray-500">x{{ $item['quantity'] }}</span>
                                                        <span class="text-[10px] text-gray-500 bg-gray-800 px-1.5 py-0.5 rounded ml-1">
                                                            {{ $item['temperature'] }}
                                                        </span>
                                                    </span>
                                                    @if(!empty($item['customizations']))
                                                        <span class="text-[10px] text-gray-500">
                                                            ({{ implode(', ', $item['customizations']) }})
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Bottom Total & Action -->
                        <div class="px-5 py-4 bg-gray-950/40 border-t border-gray-800/60 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-500">Montant Total</span>
                                <p class="text-lg font-bold text-white font-mono">{{ number_format($order['total'], 2) }} DH</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('settings.room-service.order.delete', $order['id']) }}" method="POST"
                                      onsubmit="return confirm('Annuler la commande {{ $order['id'] }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="px-3.5 py-2 border border-gray-800 bg-gray-900 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl text-xs font-semibold transition-all">
                                        Rejeter
                                    </button>
                                </form>
                                <form action="{{ route('settings.room-service.order.status', $order['id']) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit"
                                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-all shadow-md shadow-amber-500/10">
                                        Accepter & Envoyer Cuisine
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ───────────────── TAB 2: CUISINE & LIVRAISON ───────────────── --}}
    <div x-show="currentTab === 'kitchen'" x-cloak class="space-y-4">
        @if(count($activeOrders) === 0)
            <div class="text-center py-16 bg-gray-900/40 border border-gray-800 rounded-3xl text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm font-semibold">Aucune commande de chambre en cuisine ou en cours de livraison.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeOrders as $order)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden flex flex-col justify-between relative">
                        
                        <!-- Status indicator strip -->
                        <div class="h-1 w-full @if($order['status'] === 'approved') bg-blue-500 @elseif($order['status'] === 'preparing') bg-orange-500 @else bg-emerald-500 @endif"></div>

                        <!-- Top Info -->
                        <div class="px-5 py-3.5 bg-gray-950/60 border-b border-gray-800/60 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-extrabold text-white font-mono">{{ $order['id'] }}</h3>
                                <span class="text-[10px] text-gray-500">Reçu le : {{ $order['created_at'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] text-gray-400 font-bold">Chambre {{ $order['room_number'] }}</span>
                                <span class="text-xs font-bold @if($order['status'] === 'approved') text-blue-400 @elseif($order['status'] === 'preparing') text-orange-400 @else text-emerald-400 @endif">
                                    @if($order['status'] === 'approved') Validé par Caissier @elseif($order['status'] === 'preparing') En Préparation @else Prêt pour livraison @endif
                                </span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="p-5 flex-1 space-y-3">
                            <div class="flex items-center justify-between text-xs border-b border-gray-800 pb-2">
                                <span class="text-gray-400">Heure de livraison souhaitée :</span>
                                <span class="font-bold text-amber-500">{{ $order['delivery_time'] === 'asap' ? 'Immédiate (ASAP)' : $order['delivery_time'] }}</span>
                            </div>

                            <div class="space-y-3">
                                @php
                                    $guests = collect($order['items'])->pluck('guest')->unique();
                                @endphp
                                @foreach($guests as $guest)
                                    <div class="space-y-1">
                                        <p class="text-[11px] font-bold text-gray-400">Pour : {{ $guest }}</p>
                                        @foreach(collect($order['items'])->where('guest', $guest) as $item)
                                            <div class="flex justify-between text-xs pl-3 border-r border-gray-800">
                                                <span class="text-gray-300">
                                                    <strong class="font-extrabold text-white">{{ $item['quantity'] }}x</strong> {{ $item['name'] }}
                                                    <span class="text-[10px] text-gray-500">({{ $item['temperature'] }})</span>
                                                </span>
                                                @if(!empty($item['customizations']))
                                                    <span class="text-[10px] text-orange-400 italic">
                                                        * {{ implode(', ', $item['customizations']) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Actions based on status -->
                        <div class="px-5 py-4 bg-gray-950/40 border-t border-gray-800/60 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-500">Total</span>
                                <p class="text-sm font-bold text-white font-mono">{{ number_format($order['total'], 2) }} DH</p>
                            </div>
                            <form action="{{ route('settings.room-service.order.status', $order['id']) }}" method="POST">
                                @csrf
                                @if($order['status'] === 'approved')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit"
                                            class="px-4 py-2 bg-orange-500 hover:bg-orange-400 text-black font-extrabold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-md shadow-orange-500/10">
                                        👩‍🍳 Commencer le طهي
                                    </button>
                                @elseif($order['status'] === 'preparing')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit"
                                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-black font-extrabold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-md shadow-emerald-500/10">
                                        🔔 Signaler comme prêt
                                    </button>
                                @elseif($order['status'] === 'ready')
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit"
                                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-md shadow-amber-500/10">
                                        🛵 Confirmer la livraison
                                    </button>
                                @endif
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ───────────────── TAB 3: GESTION DU MENU (CRUD) ───────────────── --}}
    <div x-show="currentTab === 'menu'" x-cloak class="space-y-4">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 bg-gray-950/60 flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Liste des Plats du Room Service</h2>
                <span class="text-xs bg-gray-800 px-3 py-1 rounded-full text-gray-400">{{ count($menuItems) }} plats</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs text-gray-300">
                    <thead class="bg-gray-950/40 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-right">Plat</th>
                            <th class="px-6 py-4 text-center">Catégorie</th>
                            <th class="px-6 py-4 text-center">Prix</th>
                            <th class="px-6 py-4 text-center">Préparation m提前</th>
                            <th class="px-6 py-4 class-center text-center">Options Température</th>
                            <th class="px-6 py-4 text-right">Tuning / Ingrédients exclus</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($menuItems as $item)
                            <tr class="hover:bg-gray-800/20 transition-colors">
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-800 border border-gray-700/50">
                                        <img src="{{ $item['image_url'] }}" alt="" class="w-full h-full object-cover"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=80&q=60'">
                                    </div>
                                    <div>
                                        <p class="font-bold text-white text-sm">{{ $item['name'] }}</p>
                                        <p class="text-gray-500 text-[10px] line-clamp-1 max-w-xs mt-0.5">{{ $item['description'] }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <p class="font-bold text-white mb-1">{{ $item['category'] }}</p>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold border {{ ($item['meal_type'] ?? 'Breakfast') === 'Breakfast' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400' }}">
                                        {{ ($item['meal_type'] ?? 'Breakfast') === 'Breakfast' ? 'Ftour' : 'Lunch/Dinner' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-white text-sm">
                                    {{ number_format($item['price'], 2) }} DH
                                </td>
                                <td class="px-6 py-4 text-center font-semibold">
                                    {{ $item['requires_advance_time'] ? 'Oui (1.5h)' : 'Non (ASAP)' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $item['allow_temperature'] ? 'Chaud / Froid' : 'Standard' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(!empty($item['customizations']))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($item['customizations'] as $cust)
                                                <span class="text-[9px] bg-dark-700 px-1.5 py-0.5 rounded border border-gray-800 text-gray-400">{{ $cust }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                onclick="openEditMenuItemModal({{ json_encode($item) }})"
                                                class="p-1.5 rounded bg-gray-800 text-gray-400 hover:text-amber-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('settings.room-service.delete', $item['id']) }}" method="POST"
                                              onsubmit="return confirm('Supprimer {{ addslashes($item['name']) }} ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded bg-gray-800 text-gray-500 hover:text-red-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-gray-500">
                                    Aucun article de menu trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ───────────────── TAB 4: HISTORIQUE DES LIVRAISONS ───────────────── --}}
    <div x-show="currentTab === 'history'" x-cloak class="space-y-4">
        @if(count($deliveredOrders) === 0)
            <div class="text-center py-16 bg-gray-900/40 border border-gray-800 rounded-3xl text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <p class="text-sm font-semibold">Aucune commande livrée enregistrée dans l'historique.</p>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 bg-gray-950/60 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Commandes Livrées</h2>
                    <span class="text-xs bg-emerald-500/10 border border-emerald-500/25 px-3 py-1 rounded-full text-emerald-400 font-bold">{{ count($deliveredOrders) }} livrées</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-gray-300">
                        <thead class="bg-gray-950/40 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800">
                            <tr>
                                <th class="px-6 py-4">Ticket</th>
                                <th class="px-6 py-4 text-center">Chambre</th>
                                <th class="px-6 py-4">Date de commande</th>
                                <th class="px-6 py-4">Heure de Livraison</th>
                                <th class="px-6 py-4">Détails des Articles</th>
                                <th class="px-6 py-4 text-center">Total payé</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($deliveredOrders as $order)
                                <tr class="hover:bg-gray-800/10 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-white">{{ $order['id'] }}</td>
                                    <td class="px-6 py-4 text-center font-mono font-extrabold text-amber-400 text-sm">{{ $order['room_number'] }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $order['created_at'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                            Livré ({{ $order['delivery_time'] === 'asap' ? 'ASAP' : $order['delivery_time'] }})
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-sm">
                                        <div class="space-y-1">
                                            @foreach($order['items'] as $item)
                                                <p class="text-xs text-gray-400 leading-snug">
                                                    <span class="font-bold text-white">{{ $item['quantity'] }}x</span> {{ $item['name'] }}
                                                    <span class="text-[10px] text-gray-600">({{ $item['guest'] }} | {{ $item['temperature'] }})</span>
                                                </p>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono font-bold text-white text-sm">
                                        {{ number_format($order['total'], 2) }} DH
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('settings.room-service.order.delete', $order['id']) }}" method="POST"
                                              onsubmit="return confirm('Archiver ou supprimer ce ticket de l\'historique ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1 rounded hover:bg-gray-800 text-gray-500 hover:text-red-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- ──────────────── ADD MENU ITEM MODAL ──────────────── --}}
<div id="addMenuItemModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeAddMenuItemModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-gray-800 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-gray-950 border-b border-gray-800 flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Ajouter un article au Room Service</h3>
            <button onclick="closeAddMenuItemModal()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('settings.room-service.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Nom du Plat *</label>
                <input type="text" name="name" required
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Prix (DH) *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Catégorie (Tajines, Boissons...) *</label>
                    <input type="text" name="category" required placeholder="Tajines"
                           class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Type de repas *</label>
                    <select name="meal_type" required
                            class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                        <option value="Breakfast">🍳 Breakfast (Ftour)</option>
                        <option value="Lunch/Dinner">🍲 Lunch / Dinner (Ghada-Achaa)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Lien Image URL</label>
                <input type="url" name="image_url" placeholder="https://..."
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Ingrédients optionnels à exclure (séparés par virgules)</label>
                <input type="text" name="customizations" placeholder="sans oignons, sans sel, extra fromage"
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="requires_advance_time" value="1"
                           class="w-4.5 h-4.5 rounded border-gray-800 bg-gray-950 text-amber-500 focus:ring-amber-500/30">
                    <span class="text-xs text-gray-300">Nécessite commande à l'avance (الغداء والعشاء)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="allow_temperature" value="1"
                           class="w-4.5 h-4.5 rounded border-gray-800 bg-gray-950 text-amber-500 focus:ring-amber-500/30">
                    <span class="text-xs text-gray-300">Autoriser choix température (ساخن / بارد)</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800/60">
                <button type="button" onclick="closeAddMenuItemModal()"
                        class="flex-1 py-3 bg-gray-850 hover:bg-gray-800 text-gray-300 font-bold rounded-xl text-xs transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 py-3 bg-amber-500 hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ──────────────── EDIT MENU ITEM MODAL ──────────────── --}}
<div id="editMenuItemModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeEditMenuItemModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-gray-800 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-gray-950 border-b border-gray-800 flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Modifier l'article</h3>
            <button onclick="closeEditMenuItemModal()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Nom du Plat *</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Description</label>
                <textarea id="edit_description" name="description" rows="2"
                          class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Prix (DH) *</label>
                    <input type="number" id="edit_price" name="price" step="0.01" min="0" required
                           class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Catégorie *</label>
                    <input type="text" id="edit_category" name="category" required
                           class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Type de repas *</label>
                    <select id="edit_meal_type" name="meal_type" required
                            class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                        <option value="Breakfast">🍳 Breakfast (Ftour)</option>
                        <option value="Lunch/Dinner">🍲 Lunch / Dinner (Ghada-Achaa)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Lien Image URL</label>
                <input type="url" id="edit_image_url" name="image_url"
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Ingrédients optionnels (séparés par virgules)</label>
                <input type="text" id="edit_customizations" name="customizations"
                       class="w-full px-3 py-2.5 bg-gray-950 border border-gray-800 text-white rounded-xl text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="edit_requires_advance_time" name="requires_advance_time" value="1"
                           class="w-4.5 h-4.5 rounded border-gray-800 bg-gray-950 text-amber-500 focus:ring-amber-500/30">
                    <span class="text-xs text-gray-300">Nécessite commande à l'avance (الغداء والعشاء)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="edit_allow_temperature" name="allow_temperature" value="1"
                           class="w-4.5 h-4.5 rounded border-gray-800 bg-gray-950 text-amber-500 focus:ring-amber-500/30">
                    <span class="text-xs text-gray-300">Autoriser choix température (ساخن / بارد)</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800/60">
                <button type="button" onclick="closeEditMenuItemModal()"
                        class="flex-1 py-3 bg-gray-850 hover:bg-gray-800 text-gray-300 font-bold rounded-xl text-xs transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 py-3 bg-amber-500 hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Add modal controls
    function openAddMenuItemModal() {
        document.getElementById('addMenuItemModal').classList.remove('hidden');
    }
    function closeAddMenuItemModal() {
        document.getElementById('addMenuItemModal').classList.add('hidden');
    }

    // Edit modal controls
    function openEditMenuItemModal(item) {
        document.getElementById('editForm').action = `/settings/room-service/${item.id}/update`;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_description').value = item.description || '';
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_category').value = item.category;
        document.getElementById('edit_meal_type').value = item.meal_type || 'Breakfast';
        document.getElementById('edit_image_url').value = item.image_url || '';
        document.getElementById('edit_customizations').value = item.customizations ? item.customizations.join(', ') : '';
        
        document.getElementById('edit_requires_advance_time').checked = item.requires_advance_time;
        document.getElementById('edit_allow_temperature').checked = item.allow_temperature;
        
        document.getElementById('editMenuItemModal').classList.remove('hidden');
    }
    function closeEditMenuItemModal() {
        document.getElementById('editMenuItemModal').classList.add('hidden');
    }
</script>
@endpush
</x-layout.app>
