<x-layout.app title="Réception des commandes">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ tab: '{{ request('tab', 'tables') }}', selectedZone: 'all' }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Réception des commandes</h1>
            <p class="text-gray-400 text-sm mt-0.5">Tables & commandes clients en cours</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('waiter.settings.zones') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-xl text-sm font-semibold transition-colors">
                Zones
            </a>
            <a href="{{ route('waiter.orders') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Historique
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1 scrollbar-hide">

        <button @click="tab='tables'"
                :class="tab==='tables' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Tables
            <span class="text-xs font-black opacity-70">({{ $tables->count() }})</span>
        </button>

        <button @click="tab='restaurant'"
                :class="tab==='restaurant' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
            🍽️ Restaurant
            @if($restaurantOrders->count())
            <span class="bg-red-500 text-white text-xs font-black rounded-full w-5 h-5 flex items-center justify-center">{{ $restaurantOrders->count() }}</span>
            @endif
        </button>

        <button @click="tab='pool'"
                :class="tab==='pool' ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
            🏊 Piscine
            @if($poolOrders->count())
            <span class="bg-red-500 text-white text-xs font-black rounded-full w-5 h-5 flex items-center justify-center">{{ $poolOrders->count() }}</span>
            @endif
        </button>

        <button @click="tab='room'"
                :class="tab==='room' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
            🏨 Chambres
            @if($roomOrders->count())
            <span class="bg-red-500 text-white text-xs font-black rounded-full w-5 h-5 flex items-center justify-center">{{ $roomOrders->count() }}</span>
            @endif
        </button>

    </div>

    {{-- ══════════════════════════════════════
         TAB: TABLES
    ══════════════════════════════════════ --}}
    <div x-show="tab==='tables'" x-transition>

        <div class="flex flex-wrap gap-4 mb-6 p-4 bg-slate-900 rounded-2xl border border-slate-800">
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-emerald-500 rounded-full"></div><span class="text-xs text-slate-400">Disponible</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-red-500 rounded-full"></div><span class="text-xs text-slate-400">Occupée</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-amber-500 rounded-full"></div><span class="text-xs text-slate-400">Réservée</span></div>
        </div>

        <div class="flex flex-wrap gap-2 mb-5">
            <button @click="selectedZone = 'all'"
                    :class="selectedZone === 'all' ? 'bg-amber-500 text-slate-900' : 'bg-slate-800 border border-slate-700 text-slate-300 hover:text-white'"
                    class="px-3 py-2 rounded-xl text-xs font-semibold transition-colors">
                Toutes les zones
            </button>
            @foreach($zones as $zone)
                <button @click="selectedZone = '{{ (string) $zone->id }}'"
                        :class="selectedZone === '{{ (string) $zone->id }}' ? 'bg-amber-500 text-slate-900' : 'bg-slate-800 border border-slate-700 text-slate-300 hover:text-white'"
                        class="px-3 py-2 rounded-xl text-xs font-semibold transition-colors">
                    {{ $zone->name }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($tables as $table)
            @php
                $displayNumber = preg_match('/(\d+)$/', (string) $table->name, $matches)
                    ? (int) $matches[1]
                    : $table->numero;
            @endphp
            <a href="{{ route('waiter.table.order', $table) }}"
               x-show="selectedZone === 'all' || selectedZone === '{{ $table->zone_id !== null ? (string) $table->zone_id : '' }}'"
               class="block bg-slate-900 rounded-2xl shadow hover:shadow-lg transition-all duration-200 p-5 border-2 hover:scale-105 transform
                      @if($table->status === 'free') border-emerald-500/40 hover:border-emerald-500
                      @elseif($table->status === 'occupied') border-red-500/40 hover:border-red-500
                      @else border-amber-500/40 hover:border-amber-500 @endif">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-2xl font-extrabold text-white">{{ $displayNumber }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                 @if($table->status === 'free') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                 @elseif($table->status === 'occupied') bg-red-500/20 text-red-400 border border-red-500/30
                                 @else bg-amber-500/20 text-amber-400 border border-amber-500/30 @endif">
                        @if($table->status === 'free') Libre @elseif($table->status === 'occupied') Occupée @else Réservée @endif
                    </span>
                </div>
                <p class="text-xs text-slate-500 truncate">{{ $table->name }} @if($table->zone) • {{ $table->zone }} @endif</p>
                <div class="mt-3 text-xs font-semibold
                            @if($table->status === 'free') text-emerald-400
                            @elseif($table->status === 'occupied') text-red-400
                            @else text-amber-400 @endif">
                    @if($table->status === 'free') + Prendre commande @elseif($table->status === 'occupied') Voir commande @else Réservée @endif
                </div>
            </a>
            @endforeach
        </div>

        @if($tables->isEmpty())
        <div class="text-center py-16 text-slate-600">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <p>Aucune table configurée</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         TAB: RESTAURANT CLIENT ORDERS
    ══════════════════════════════════════ --}}
    <div x-show="tab==='restaurant'" x-transition>
        @if($restaurantOrders->isEmpty())
        <div class="text-center py-16 text-slate-600">
            <div class="text-5xl mb-4">🍽️</div>
            <p class="text-lg font-semibold text-slate-500">Aucune commande restaurant en attente</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($restaurantOrders as $order)
            <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-mono">#{{ $order->id }}</span>
                        <p class="text-white font-bold text-sm mt-0.5 leading-tight">{{ $order->waiter_notes }}</p>
                    </div>
                    @php $mins = $order->created_at->diffInMinutes(now()); @endphp
                    <span class="flex-shrink-0 ml-3 px-2 py-1 rounded-lg text-[10px] font-bold
                        {{ $order->status === 'en_cuisine' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/25' : '' }}
                        {{ $order->status === 'en_preparation' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/25' : '' }}
                        {{ $order->status === 'pret' ? 'bg-green-500/15 text-green-400 border border-green-500/25' : '' }}">
                        @if($order->status==='en_cuisine') En cuisine
                        @elseif($order->status==='en_preparation') En préparation
                        @elseif($order->status==='pret') Prêt
                        @else {{ $order->status }} @endif
                    </span>
                </div>
                <ul class="space-y-1">
                    @foreach($order->details as $d)
                    <li class="flex justify-between text-xs text-slate-400">
                        <span>{{ $d->produit->name ?? '—' }}</span>
                        <span class="font-semibold text-slate-300">x{{ $d->quantity }}</span>
                    </li>
                    @endforeach
                </ul>
                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <span class="text-amber-400 font-bold text-sm">{{ number_format($order->total, 2) }} MAD</span>
                    <span class="text-slate-600 text-xs">{{ $mins }}min ago</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         TAB: POOL ORDERS
    ══════════════════════════════════════ --}}
    <div x-show="tab==='pool'" x-transition>
        @if($poolOrders->isEmpty())
        <div class="text-center py-16 text-slate-600">
            <div class="text-5xl mb-4">🏊</div>
            <p class="text-lg font-semibold text-slate-500">Aucune commande piscine en attente</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($poolOrders as $order)
            <div class="bg-slate-900 border border-sky-500/30 rounded-2xl p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-mono">#{{ $order->id }}</span>
                        <p class="text-white font-bold text-sm mt-0.5 leading-tight">{{ $order->waiter_notes }}</p>
                    </div>
                    @php $mins = $order->created_at->diffInMinutes(now()); @endphp
                    <span class="flex-shrink-0 ml-3 px-2 py-1 rounded-lg text-[10px] font-bold
                        {{ $order->status === 'en_cuisine' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/25' : '' }}
                        {{ $order->status === 'en_preparation' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/25' : '' }}
                        {{ $order->status === 'pret' ? 'bg-green-500/15 text-green-400 border border-green-500/25' : '' }}">
                        @if($order->status==='en_cuisine') En cuisine
                        @elseif($order->status==='en_preparation') En préparation
                        @elseif($order->status==='pret') Prêt
                        @else {{ $order->status }} @endif
                    </span>
                </div>
                <ul class="space-y-1">
                    @foreach($order->details as $d)
                    <li class="flex justify-between text-xs text-slate-400">
                        <span>{{ $d->produit->name ?? '—' }}</span>
                        <span class="font-semibold text-slate-300">x{{ $d->quantity }}</span>
                    </li>
                    @endforeach
                </ul>
                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <span class="text-sky-400 font-bold text-sm">{{ number_format($order->total, 2) }} MAD</span>
                    <span class="text-slate-600 text-xs">{{ $mins }}min ago</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         TAB: ROOM ORDERS
    ══════════════════════════════════════ --}}
    <div x-show="tab==='room'" x-transition>
        @if($roomOrders->isEmpty())
        <div class="text-center py-16 text-slate-600">
            <div class="text-5xl mb-4">🏨</div>
            <p class="text-lg font-semibold text-slate-500">Aucune commande chambre en attente</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($roomOrders as $order)
            <div class="bg-slate-900 border border-purple-500/30 rounded-2xl p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-mono">#{{ $order->id }}</span>
                        <p class="text-white font-bold text-sm mt-0.5 leading-tight">{{ $order->waiter_notes }}</p>
                    </div>
                    @php $mins = $order->created_at->diffInMinutes(now()); @endphp
                    <span class="flex-shrink-0 ml-3 px-2 py-1 rounded-lg text-[10px] font-bold
                        {{ $order->status === 'en_cuisine' ? 'bg-orange-500/15 text-orange-400 border border-orange-500/25' : '' }}
                        {{ $order->status === 'en_preparation' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/25' : '' }}
                        {{ $order->status === 'pret' ? 'bg-green-500/15 text-green-400 border border-green-500/25' : '' }}">
                        @if($order->status==='en_cuisine') En cuisine
                        @elseif($order->status==='en_preparation') En préparation
                        @elseif($order->status==='pret') Prêt
                        @else {{ $order->status }} @endif
                    </span>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl px-3 py-2 text-xs text-amber-400 font-semibold">
                    ⏱ Livraison en chambre — 2 heures max
                </div>
                <ul class="space-y-1">
                    @foreach($order->details as $d)
                    <li class="flex justify-between text-xs text-slate-400">
                        <span>{{ $d->produit->name ?? '—' }}</span>
                        <span class="font-semibold text-slate-300">x{{ $d->quantity }}</span>
                    </li>
                    @endforeach
                </ul>
                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <span class="text-purple-400 font-bold text-sm">{{ number_format($order->total, 2) }} MAD</span>
                    <span class="text-slate-600 text-xs">{{ $mins }}min ago</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<style>
.scrollbar-hide::-webkit-scrollbar{display:none}
.scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}
</style>

@push('scripts')
<script>
// Auto-refresh every 30 seconds to pick up new orders
setTimeout(() => window.location.reload(), 30000);
</script>
@endpush
</x-layout.app>