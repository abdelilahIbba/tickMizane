<x-layout.app title="Commande — Table {{ $table->numero }}">
{{-- x-data="{ activeTab: 'menu' }" initialise AlpineJS pour le basculement dynamique d'onglets sur mobile --}}
<div class="h-full flex flex-col overflow-hidden" x-data="{ activeTab: 'menu' }">
    <form id="orderForm" method="POST" action="{{ route('waiter.order.store', $table) }}" class="flex flex-col flex-1 overflow-hidden">
        @csrf

        {{-- Barre d'onglets responsive : Visible uniquement sur mobile/tablette (< lg) --}}
        {{-- Permet de basculer l'affichage complet entre le catalogue et le panier --}}
        <div class="flex lg:hidden bg-gray-900 border-b border-gray-800 flex-shrink-0">
            <button type="button" 
                    @click="activeTab = 'menu'"
                    :class="activeTab === 'menu' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-300'"
                    class="flex-1 py-3 text-center font-semibold text-sm border-b-2 transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Catalogue
            </button>
            <button type="button" 
                    @click="activeTab = 'cart'"
                    :class="activeTab === 'cart' ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-gray-400 hover:text-gray-300'"
                    class="flex-1 py-3 text-center font-semibold text-sm border-b-2 transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Commande
                <span id="cartCountBadge" class="hidden text-xs bg-amber-500 text-black font-bold px-2 py-0.5 rounded-full transition-all">0</span>
            </button>
        </div>

        {{-- Conteneur principal adaptatif : Flex-col (empilé) sur mobile, Flex-row (côte à côte) sur grand écran --}}
        <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">

            {{-- ─────────────────── LEFT: Menu ─────────────────── --}}
            {{-- Affiche la grille si l'onglet Catalogue est actif. Sur grand écran, toujours visible (lg:flex) --}}
            <div :class="activeTab === 'menu' ? 'flex' : 'hidden lg:flex'" class="flex-col flex-1 overflow-hidden border-r border-gray-800 w-full">

                {{-- Top bar --}}
                <div class="flex items-center justify-between px-6 py-3 border-b border-gray-800 bg-gray-900/70 backdrop-blur-sm flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('waiter.index') }}"
                           class="p-2 rounded-lg bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-xl font-bold text-white leading-tight">Table {{ $table->numero }}</h1>
                            <p class="text-xs text-gray-400">{{ $table->name }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-800 px-3 py-1 rounded-full">Prise de commande</span>
                </div>

                {{-- Category strip --}}
                <div class="flex-shrink-0 px-4 pt-3 pb-0 border-b border-gray-800 bg-gray-900/50">
                    <div class="flex gap-3 overflow-x-auto pb-3 scrollbar-hide">

                        {{-- "Tous" tab --}}
                        <button type="button"
                                class="category-tab flex-shrink-0 flex flex-col items-center gap-1.5 px-3 py-2 rounded-xl border-2 transition-all duration-150
                                       border-amber-500 bg-amber-500/10"
                                data-category="all">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-amber-500 flex items-center justify-center bg-gray-800">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-amber-400 whitespace-nowrap">Tous</span>
                        </button>

                        @foreach($categories as $category)
                        <button type="button"
                                class="category-tab flex-shrink-0 flex flex-col items-center gap-1.5 px-3 py-2 rounded-xl border-2 transition-all duration-150
                                       border-gray-700 bg-gray-800/50 hover:border-gray-500"
                                data-category="{{ $category->id }}">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-600 flex-shrink-0">
                                <img src="{{ $category->display_image_url }}"
                                     alt="{{ $category->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=120&q=80'">
                            </div>
                            <span class="text-xs font-medium text-gray-300 whitespace-nowrap max-w-[80px] truncate">{{ $category->name }}</span>
                        </button>
                        @endforeach

                    </div>
                </div>

                {{-- Products Section with Vertical Alphabet Filter Bar on Left --}}
                <div class="flex-1 flex flex-row overflow-hidden relative">

                    {{-- Vertical Alphabet Strip (A → Z) --}}
                    <div id="alphabetBar" class="w-12 sm:w-14 bg-gray-900/90 border-r border-gray-800 flex flex-col items-center py-2 px-1 gap-1 overflow-y-auto flex-shrink-0 scrollbar-hide select-none">
                        {{-- "TOUS" button --}}
                        <button type="button"
                                id="alpha-btn-all"
                                data-letter="all"
                                onclick="setAlphabetFilter('all')"
                                class="alpha-btn active-alpha-btn w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-[10px] sm:text-xs font-bold transition-all duration-200 flex flex-col items-center justify-center border bg-amber-500 text-gray-950 border-amber-400 shadow-md shadow-amber-500/20 active:scale-95 flex-shrink-0"
                                title="Tous les produits">
                            <span class="leading-none">TOUS</span>
                        </button>

                        <div class="w-7 h-[1px] bg-gray-800 my-1 flex-shrink-0"></div>

                        @foreach(range('A', 'Z') as $letter)
                        <button type="button"
                                id="alpha-btn-{{ $letter }}"
                                data-letter="{{ $letter }}"
                                onclick="setAlphabetFilter('{{ $letter }}')"
                                class="alpha-btn w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center justify-center border text-gray-400 bg-gray-800/60 border-gray-700/60 hover:bg-gray-700/80 hover:text-white hover:border-gray-500 active:scale-95 flex-shrink-0">
                            {{ $letter }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Products Grid & Filters Area --}}
                    <div class="flex-1 overflow-y-auto p-3 sm:p-4 flex flex-col">
                        {{-- Active Letter Filter Indicator Badge --}}
                        <div id="activeLetterBanner" class="hidden mb-3 flex items-center justify-between bg-amber-500/10 border border-amber-500/30 rounded-xl px-3 py-2">
                            <div class="flex items-center gap-2 text-xs text-amber-400 font-medium">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Filtre lettre :</span>
                                <span id="activeLetterTag" class="px-2 py-0.5 bg-amber-500 text-gray-950 font-bold rounded-md text-xs">G</span>
                                <span id="letterCountTag" class="text-gray-400 text-xs ml-1">(0 produit)</span>
                            </div>
                            <button type="button" onclick="setAlphabetFilter('all')" class="text-xs font-semibold text-amber-400 hover:text-amber-300 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Réinitialiser (Tous)
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-4" id="productsGrid">
                            @foreach($products as $product)
                            <div class="product-card group relative bg-gray-900 rounded-2xl overflow-hidden border border-gray-800 cursor-pointer
                                        hover:border-amber-500/60 hover:shadow-lg hover:shadow-amber-500/10
                                        transition-all duration-200 hover:-translate-y-0.5 active:scale-95"
                                 data-category="{{ $product->category_id }}"
                                 data-product-id="{{ $product->id }}"
                                 data-product-name="{{ $product->name }}"
                                 data-product-price="{{ $product->price_vente }}"
                                 data-product-image="{{ $product->display_image_url }}"
                                 data-stock="{{ $product->stock_quantity }}">

                                {{-- Product image --}}
                                <div class="relative aspect-[4/3] overflow-hidden bg-gray-800">
                                    <img src="{{ $product->display_image_url }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=400&q=80'">

                                    {{-- Add overlay --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                        <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-200">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                    </div>

                                    {{-- Low stock badge --}}
                                    @if($product->stock_quantity <= 5)
                                    <div class="absolute top-2 left-2">
                                        <span class="text-xs font-semibold bg-red-500/90 text-white px-2 py-0.5 rounded-full">
                                            Stock: {{ $product->stock_quantity }}
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Product info --}}
                                <div class="p-2 sm:p-3">
                                    <h3 class="text-xs sm:text-sm font-semibold text-white leading-tight line-clamp-2 mb-1 sm:mb-1.5 h-8 sm:h-10 select-none">{{ $product->name }}</h3>
                                    <div class="flex items-center justify-between mt-1 sm:mt-2">
                                        <span class="text-sm sm:text-base font-bold text-amber-400">{{ number_format($product->price_vente, 2) }} DH</span>
                                        <span class="text-[10px] sm:text-xs text-gray-500">{{ $product->unit }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Empty state for category --}}
                        <div id="emptyFilter" class="hidden text-center py-16 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">Aucun produit dans cette catégorie</p>
                        </div>

                        {{-- Empty state for alphabet filter --}}
                        <div id="emptyAlphabetFilter" class="hidden text-center py-16 px-4">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-800/80 border border-gray-700/60 flex items-center justify-center text-amber-400 font-bold text-2xl shadow-inner">
                                <span id="emptyLetterChar">G</span>
                            </div>
                            <h4 class="text-base font-semibold text-white mb-1">Aucun produit trouvé</h4>
                            <p class="text-xs text-gray-400 mb-4" id="emptyLetterText">Aucun produit ne commence par la lettre "<span class="font-bold text-amber-400">G</span>"</p>
                            <button type="button" onclick="setAlphabetFilter('all')" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/20 text-amber-400 border border-amber-500/40 rounded-xl text-xs font-semibold hover:bg-amber-500/30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Afficher tous les produits
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        {{-- ─────────────────── RIGHT: Commande Panel ─────────────────── --}}
        <div :class="activeTab === 'cart' ? 'flex' : 'hidden lg:flex'"
             class="w-full lg:w-96 xl:w-[440px] flex-col bg-gray-900/60 flex-shrink-0 overflow-hidden h-full">

            @if($existingOrder)
            {{-- ── Section 1 : Commande actuelle ── --}}
            <div class="flex flex-col border-b border-gray-700 flex-shrink min-h-0 max-h-[38vh] lg:max-h-[40%]">

                {{-- Header commande actuelle --}}
                <div class="px-4 py-2.5 bg-gray-800/80 border-b border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-sm font-bold text-white truncate">
                            Commande #{{ $existingOrder->id }}
                            @if($existingOrder->venteNumber())
                                <span class="text-amber-300 font-medium">· Vente {{ $existingOrder->venteNumber() }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @php
                            $sc = ['en_cuisine'=>'bg-orange-500/20 text-orange-400 border-orange-500/30','en_preparation'=>'bg-blue-500/20 text-blue-400 border-blue-500/30','pret'=>'bg-emerald-500/20 text-emerald-400 border-emerald-500/30','servi'=>'bg-cyan-500/20 text-cyan-400 border-cyan-500/30','payee'=>'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'];
                            $sc = $sc[$existingOrder->status] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30';
                        @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $sc }}">
                            {{ $existingOrder->status_label }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $existingOrder->created_at->format('H:i') }}</span>
                    </div>
                </div>

                {{-- Items table --}}
                <div class="overflow-y-auto flex-1 px-3 py-1.5 min-h-0">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-500 border-b border-gray-700/60">
                                <th class="text-left pb-1.5 font-medium">Article</th>
                                <th class="text-center pb-1.5 font-medium w-10">Qté</th>
                                <th class="text-right pb-1.5 font-medium w-16">P.U.</th>
                                <th class="text-right pb-1.5 font-medium w-16">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/60">
                            @foreach($existingOrder->details as $detail)
                            <tr class="group">
                                <td class="py-1.5 pr-2">
                                    <p class="font-medium text-white leading-tight">{{ $detail->produit->name }}</p>
                                    @if($detail->notes)
                                    <p class="text-gray-500 italic text-[10px] mt-0.5">{{ $detail->notes }}</p>
                                    @endif
                                </td>
                                <td class="py-1.5 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-gray-800 rounded-lg text-white font-bold">{{ $detail->quantity }}</span>
                                </td>
                                <td class="py-1.5 text-right text-gray-400">{{ number_format($detail->price, 2) }}</td>
                                <td class="py-1.5 text-right font-bold text-amber-400">{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Subtotal + actions --}}
                <div class="flex-shrink-0 px-3 py-2 bg-gray-800/50 border-t border-gray-700/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-400 font-medium">Total actuel</span>
                        <span class="text-sm font-bold text-white">{{ number_format($existingOrder->total, 2) }} DH</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5">
                        <a href="{{ route('cashier.payment', $existingOrder) }}"
                                class="flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 transition-colors text-xs font-semibold"
                                title="Encaisser cette commande">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Encaisser</span>
                        </a>
                        <button type="button"
                                onclick="openTransferModal()"
                                class="flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 transition-colors text-xs font-semibold"
                                title="Transférer la commande">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="truncate">Transférer</span>
                        </button>
                        <button type="button"
                                onclick="openCancelModal()"
                                class="flex items-center justify-center gap-1 px-2 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition-colors text-xs font-semibold"
                                title="Annuler la commande">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <span class="truncate">Annuler</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Divider label --}}
            <div class="flex items-center gap-2 px-3 py-1 flex-shrink-0 bg-gray-900/80">
                <div class="flex-1 h-px bg-gray-700"></div>
                <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">+ Nouveaux articles</span>
                <div class="flex-1 h-px bg-gray-700"></div>
            </div>
            @endif

            {{-- ── Section 2 : Nouveau panier ── --}}
            <div class="flex flex-col flex-1 min-h-0 overflow-hidden">

                {{-- Cart header --}}
                @unless($existingOrder)
                <div class="px-4 py-3 border-b border-gray-800 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Commande
                        </h2>
                        <span id="cartCount" class="hidden text-xs bg-amber-500 text-black font-bold px-2 py-0.5 rounded-full">0</span>
                    </div>
                </div>
                @else
                <div class="px-3 py-1.5 border-b border-gray-800 flex items-center justify-between flex-shrink-0">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Panier</span>
                    <span id="cartCount" class="hidden text-xs bg-amber-500 text-black font-bold px-2 py-0.5 rounded-full">0</span>
                </div>
                @endunless

                {{-- Cart items --}}
                <div class="flex-1 overflow-y-auto px-3 py-2 min-h-0">
                    <div id="emptyCart" class="flex flex-col items-center justify-center h-full text-gray-600 {{ $existingOrder ? 'py-2' : 'py-6' }}">
                        <svg class="{{ $existingOrder ? 'w-7 h-7 mb-1' : 'w-10 h-10 mb-2' }} text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-xs font-medium text-gray-500">Panier vide</p>
                        <p class="text-[11px] text-gray-600 mt-0.5">Sélectionnez des produits</p>
                    </div>
                    <div id="cartItems" class="space-y-2 hidden"></div>
                </div>

                {{-- Cart footer --}}
                <div class="flex-shrink-0 border-t border-gray-800 p-3 space-y-2.5 bg-gray-900/95">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-400 mb-1">Instructions générales</label>
                        <textarea name="waiter_notes"
                                  rows="1"
                                  class="w-full px-3 py-1.5 bg-gray-800 border border-gray-700 text-white rounded-lg text-xs
                                         focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600 resize-none transition-all focus:rows-2"
                                  placeholder="Notes pour la cuisine..."></textarea>
                    </div>
                    <div class="flex items-center justify-between py-1 border-t border-gray-800/80">
                        <span class="text-xs font-semibold text-gray-400">
                            @if($existingOrder) Ajout @else Total @endif
                        </span>
                        <span id="cartTotal" class="text-lg font-bold text-amber-400">0.00 DH</span>
                    </div>
                    @if($existingOrder)
                    <div class="flex items-center justify-between -mt-1 pb-0.5 text-xs">
                        <span class="text-gray-500">Total cumulé</span>
                        <span id="grandTotal" class="font-bold text-gray-300">{{ number_format($existingOrder->total, 2) }} DH</span>
                    </div>
                    @endif
                    <button type="submit"
                            id="submitOrder"
                            disabled
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm
                                   bg-amber-500 text-black hover:bg-amber-400
                                   disabled:bg-gray-700 disabled:text-gray-500 disabled:cursor-not-allowed
                                   transition-all duration-150 shadow-lg shadow-amber-500/20 flex-shrink-0">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                        <span>{{ $existingOrder ? 'Ajouter à la commande' : 'Envoyer à la cuisine' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="items" id="cartItemsInput">
    </form>
</div>

{{-- ──────────────── Product Notes Modal ──────────────── --}}
<div id="notesModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="window.closeNotesModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-gray-800 w-full max-w-sm overflow-hidden">
        <div class="relative h-40 bg-gray-800 overflow-hidden">
            <img id="modalProductImage" src="" alt=""
                 class="w-full h-full object-cover"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=400&q=80'">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-4">
                <h3 id="modalProductName" class="text-lg font-bold text-white leading-tight"></h3>
                <p id="modalProductPrice" class="text-amber-400 font-semibold text-sm"></p>
            </div>
            <button onclick="window.closeNotesModal()" class="absolute top-3 right-3 w-8 h-8 bg-black/50 rounded-full flex items-center justify-center text-white hover:bg-black/70 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Quantité</label>
                <div class="flex items-center justify-center gap-5">
                    <button type="button" onclick="updateModalQuantity(-1)"
                            class="w-10 h-10 bg-gray-800 border border-gray-700 text-white rounded-xl hover:bg-gray-700 transition-colors text-xl font-bold flex items-center justify-center">−</button>
                    <span id="modalQuantity" class="text-3xl font-bold text-white w-14 text-center tabular-nums">1</span>
                    <button type="button" onclick="updateModalQuantity(1)"
                            class="w-10 h-10 bg-gray-800 border border-gray-700 text-white rounded-xl hover:bg-gray-700 transition-colors text-xl font-bold flex items-center justify-center">+</button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Notes (optionnel)</label>
                <textarea id="productNotes" rows="2"
                          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-xl text-sm
                                 focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600 resize-none"
                          placeholder="Ex: sans piment, bien cuit..."></textarea>
            </div>
            <div class="flex items-center justify-between bg-gray-800/50 rounded-xl px-4 py-2.5">
                <span class="text-sm text-gray-400">Sous-total</span>
                <span id="modalSubtotal" class="text-base font-bold text-amber-400">0.00 DH</span>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="window.closeNotesModal()"
                        class="flex-1 px-4 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">Annuler</button>
                <button type="button" onclick="window.saveProductNotes()"
                        class="flex-1 px-4 py-3 bg-amber-500 text-black rounded-xl hover:bg-amber-400 transition-colors text-sm font-semibold">Ajouter</button>
            </div>
        </div>
    </div>
</div>

@if($existingOrder)
{{-- ──────────────── Finalize Modal ──────────────── --}}
<div id="finalizeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" onclick="closeFinalizeModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-emerald-900/40 w-full max-w-sm overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Finaliser pour encaissement</h3>
                    <p class="text-xs text-gray-400">Commande #{{ $existingOrder->id }} — Table {{ $table->numero }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-400 mb-1">La commande sera envoyée <span class="text-white font-semibold">directement à l'encaissement</span> sans passer par la validation cuisine.</p>
            <div class="flex items-center justify-between bg-gray-800/50 rounded-xl px-4 py-3 my-4">
                <span class="text-sm text-gray-400">Total à encaisser</span>
                <span class="text-lg font-bold text-emerald-400">{{ number_format($existingOrder->total, 2) }} DH</span>
            </div>
            <p id="finalizeError" class="hidden text-xs text-red-400 mb-3 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"></p>
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeFinalizeModal()"
                        class="flex-1 px-4 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">
                    Retour
                </button>
                <button type="button"
                        id="finalizeConfirmBtn"
                        onclick="confirmFinalize()"
                        class="flex-1 px-4 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors text-sm font-semibold">
                    Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ──────────────── Cancel Modal ──────────────── --}}
<div id="cancelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-red-900/40 w-full max-w-sm overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Annuler la commande</h3>
                    <p class="text-xs text-gray-400">Commande #{{ $existingOrder->id }} — Table {{ $table->numero }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-400 mb-5">Cette action est irréversible. La commande sera marquée comme annulée.</p>

            @if(Auth::user()->role !== 'admin')
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Code PIN administrateur requis
                </label>
                <input type="password"
                       id="cancelPinInput"
                       inputmode="numeric"
                       maxlength="20"
                       placeholder="Entrez le code PIN admin"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl text-sm
                              focus:ring-2 focus:ring-red-500/50 focus:border-red-500 placeholder-gray-600
                              tracking-widest text-center text-lg font-mono"
                       onkeydown="if(event.key==='Enter') confirmCancel()">
            </div>
            @endif
            <p id="cancelPinError" class="hidden text-xs text-red-400 mb-3 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"></p>

            <div class="flex gap-3">
                <button type="button"
                        onclick="closeCancelModal()"
                        class="flex-1 px-4 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">
                    Retour
                </button>
                <button type="button"
                        id="cancelConfirmBtn"
                        onclick="confirmCancel()"
                        class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors text-sm font-semibold">
                    Confirmer l'annulation
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ──────────────── Transfer Modal ──────────────── --}}
<div id="transferModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" onclick="closeTransferModal()"></div>
    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-blue-900/40 w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Transférer la commande</h3>
                    <p class="text-xs text-gray-400">De Table {{ $table->numero }} → choisir une table</p>
                </div>
            </div>
            <button onclick="closeTransferModal()" class="w-7 h-7 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-5">
            <p class="text-xs text-gray-500 mb-4">Sélectionnez la table cible :</p>
            <input type="hidden" id="selectedTargetTable" value="">
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-2 max-h-64 overflow-y-auto">
                @foreach($availableTables as $tbl)
                <button type="button"
                        class="transfer-table-btn flex flex-col items-center justify-center gap-1 p-2 rounded-xl border transition-all
                               {{ $tbl->status === 'free' || $tbl->status === 'libre'
                                    ? 'border-gray-700 bg-gray-800/60 hover:border-gray-500'
                                    : 'border-orange-700/50 bg-orange-900/20 hover:border-orange-600' }}"
                        data-table-id="{{ $tbl->id }}">
                    <span class="text-base font-bold {{ $tbl->status === 'free' || $tbl->status === 'libre' ? 'text-white' : 'text-orange-300' }}">
                        {{ $tbl->numero }}
                    </span>
                    <span class="text-[10px] {{ $tbl->status === 'free' || $tbl->status === 'libre' ? 'text-gray-500' : 'text-orange-500' }}">
                        {{ $tbl->status === 'free' || $tbl->status === 'libre' ? 'Libre' : 'Occupée' }}
                    </span>
                </button>
                @endforeach
            </div>
            <p id="transferError" class="hidden text-xs text-red-400 mt-3 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"></p>
        </div>

        <div class="flex gap-3 px-5 pb-5">
            <button type="button"
                    onclick="closeTransferModal()"
                    class="flex-1 px-4 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">
                Annuler
            </button>
            <button type="button"
                    id="transferConfirmBtn"
                    disabled
                    onclick="confirmTransfer()"
                    class="flex-1 px-4 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors text-sm font-semibold
                           disabled:bg-gray-700 disabled:text-gray-500 disabled:cursor-not-allowed">
                Confirmer le transfert
            </button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
let cart = [];
let currentProduct = null;
let modalQuantity = 1;
const existingOrderTotal = {{ $existingOrder ? $existingOrder->total : 0 }};
const isAdmin = {{ Auth::user()->role === 'admin' ? 'true' : 'false' }};
@if($existingOrder)
const existingOrderId = {{ $existingOrder->id }};
const cancelUrl = '{{ route("waiter.order.cancel", $existingOrder) }}';
const transferUrl = '{{ route("waiter.order.transfer", $existingOrder) }}';
@endif
const csrfToken = document.querySelector('input[name="_token"]').value;

/* ── Category & Alphabet filtering ──────────── */
let currentCategory = 'all';
let currentLetter = 'all';

function normalizeStr(str) {
    if (!str) return '';
    return str.trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase();
}

window.setAlphabetFilter = function (letter) {
    if (letter === currentLetter && letter !== 'all') {
        currentLetter = 'all';
    } else {
        currentLetter = letter;
    }
    applyCombinedFilters();
};

function applyCombinedFilters() {
    let visibleCount = 0;
    
    document.querySelectorAll('.product-card').forEach(card => {
        const cardCat = card.dataset.category;
        const cardName = card.dataset.productName || '';
        const normalizedName = normalizeStr(cardName);
        
        const matchCategory = currentCategory === 'all' || cardCat === currentCategory;
        const matchLetter = currentLetter === 'all' || normalizedName.startsWith(currentLetter);
        
        const show = matchCategory && matchLetter;
        card.classList.toggle('hidden', !show);
        if (show) visibleCount++;
    });

    // Update Alphabet bar active styles
    document.querySelectorAll('.alpha-btn').forEach(btn => {
        const letter = btn.dataset.letter || 'all';
        const isActive = (letter === currentLetter) || (letter === 'all' && currentLetter === 'all');
        
        if (isActive) {
            btn.classList.add('bg-amber-500', 'text-gray-950', 'border-amber-400', 'shadow-md', 'shadow-amber-500/20', 'scale-105');
            btn.classList.remove('text-gray-400', 'bg-gray-800/60', 'border-gray-700/60');
        } else {
            btn.classList.remove('bg-amber-500', 'text-gray-950', 'border-amber-400', 'shadow-md', 'shadow-amber-500/20', 'scale-105');
            btn.classList.add('text-gray-400', 'bg-gray-800/60', 'border-gray-700/60');
        }
    });

    // Active letter banner indicator
    const banner = document.getElementById('activeLetterBanner');
    const tag = document.getElementById('activeLetterTag');
    const countTag = document.getElementById('letterCountTag');
    if (banner && tag && countTag) {
        if (currentLetter !== 'all') {
            tag.textContent = currentLetter;
            countTag.textContent = `(${visibleCount} ${visibleCount > 1 ? 'produits' : 'produit'})`;
            banner.classList.remove('hidden');
        } else {
            banner.classList.add('hidden');
        }
    }

    // Empty states handling
    const emptyCat = document.getElementById('emptyFilter');
    const emptyAlpha = document.getElementById('emptyAlphabetFilter');

    if (visibleCount === 0) {
        if (currentLetter !== 'all') {
            if (emptyCat) emptyCat.classList.add('hidden');
            if (emptyAlpha) {
                document.getElementById('emptyLetterChar').textContent = currentLetter;
                document.getElementById('emptyLetterText').innerHTML = `Aucun produit ne commence par la lettre "<span class="font-bold text-amber-400">${currentLetter}</span>"`;
                emptyAlpha.classList.remove('hidden');
            }
        } else {
            if (emptyAlpha) emptyAlpha.classList.add('hidden');
            if (emptyCat) emptyCat.classList.remove('hidden');
        }
    } else {
        if (emptyCat) emptyCat.classList.add('hidden');
        if (emptyAlpha) emptyAlpha.classList.add('hidden');
    }

    updateAlphabetAvailability();
}

function updateAlphabetAvailability() {
    const lettersWithProducts = new Set();

    document.querySelectorAll('.product-card').forEach(card => {
        const cardCat = card.dataset.category;
        if (currentCategory === 'all' || cardCat === currentCategory) {
            const name = card.dataset.productName;
            if (name) {
                const firstChar = normalizeStr(name)[0];
                if (firstChar >= 'A' && firstChar <= 'Z') {
                    lettersWithProducts.add(firstChar);
                }
            }
        }
    });

    document.querySelectorAll('.alpha-btn[data-letter]').forEach(btn => {
        const letter = btn.dataset.letter;
        if (letter === 'all') return;
        const hasProducts = lettersWithProducts.has(letter);
        if (hasProducts) {
            btn.classList.remove('opacity-30', 'border-gray-800/40', 'text-gray-600', 'bg-gray-900/30', 'cursor-not-allowed', 'pointer-events-none');
        } else {
            btn.classList.add('opacity-30', 'border-gray-800/40', 'text-gray-600', 'bg-gray-900/30', 'cursor-not-allowed', 'pointer-events-none');
            btn.classList.remove('bg-amber-500', 'text-gray-950', 'border-amber-400', 'shadow-md', 'shadow-amber-500/20', 'scale-105');
        }
    });
}

const tabs = document.querySelectorAll('.category-tab');
tabs.forEach(tab => {
    tab.addEventListener('click', function () {
        tabs.forEach(t => {
            t.classList.remove('border-amber-500', 'bg-amber-500/10');
            t.classList.add('border-gray-700', 'bg-gray-800/50');
            const sp = t.querySelector('span');
            if (sp) { sp.classList.remove('text-amber-400'); sp.classList.add('text-gray-300'); }
            const imgDiv = t.querySelector('div');
            if (imgDiv) { imgDiv.classList.remove('border-amber-500'); imgDiv.classList.add('border-gray-600'); }
        });
        this.classList.add('border-amber-500', 'bg-amber-500/10');
        this.classList.remove('border-gray-700', 'bg-gray-800/50');
        const sp = this.querySelector('span');
        if (sp) { sp.classList.add('text-amber-400'); sp.classList.remove('text-gray-300'); }
        const imgDiv = this.querySelector('div');
        if (imgDiv) { imgDiv.classList.add('border-amber-500'); imgDiv.classList.remove('border-gray-600'); }
        
        currentCategory = this.dataset.category;
        applyCombinedFilters();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    updateAlphabetAvailability();
});

/* ── Open product modal ─────────────────────── */
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function () {
        currentProduct = {
            id: this.dataset.productId,
            name: this.dataset.productName,
            price: parseFloat(this.dataset.productPrice),
            stock: parseInt(this.dataset.stock),
            image: this.dataset.productImage,
        };
        modalQuantity = 1;
        document.getElementById('modalQuantity').textContent = 1;
        document.getElementById('modalProductName').textContent = currentProduct.name;
        document.getElementById('modalProductPrice').textContent = currentProduct.price.toFixed(2) + ' DH';
        document.getElementById('modalProductImage').src = currentProduct.image;
        document.getElementById('modalProductImage').alt = currentProduct.name;
        document.getElementById('productNotes').value = '';
        updateModalSubtotal();
        document.getElementById('notesModal').classList.remove('hidden');
    });
});

window.updateModalQuantity = function (delta) {
    if (!currentProduct) return;
    modalQuantity = Math.max(1, Math.min(currentProduct.stock, modalQuantity + delta));
    document.getElementById('modalQuantity').textContent = modalQuantity;
    updateModalSubtotal();
};
function updateModalSubtotal() {
    if (!currentProduct) return;
    document.getElementById('modalSubtotal').textContent = (currentProduct.price * modalQuantity).toFixed(2) + ' DH';
}
window.closeNotesModal = function () {
    document.getElementById('notesModal').classList.add('hidden');
    modalQuantity = 1; currentProduct = null;
};
window.saveProductNotes = function () {
    if (!currentProduct) return;
    const notes = document.getElementById('productNotes').value.trim();
    const idx = cart.findIndex(i => i.produit_id === currentProduct.id && i.notes === notes);
    if (idx >= 0) { cart[idx].quantity += modalQuantity; }
    else { cart.push({ produit_id: currentProduct.id, name: currentProduct.name, price: currentProduct.price, quantity: modalQuantity, notes }); }
    updateCart();
    window.closeNotesModal();
};

/* ── Cart rendering ─────────────────────────── */
function updateCart() {
    const container = document.getElementById('cartItems');
    const empty = document.getElementById('emptyCart');
    const btn = document.getElementById('submitOrder');
    const cnt = document.getElementById('cartCount');
    const mobileBadge = document.getElementById('cartCountBadge');

    if (cart.length === 0) {
        empty.classList.remove('hidden'); container.classList.add('hidden');
        container.innerHTML = ''; btn.disabled = true; cnt.classList.add('hidden');
        if (mobileBadge) mobileBadge.classList.add('hidden');
        document.getElementById('cartTotal').textContent = '0.00 DH';
        updateGrandTotal(0);
        document.getElementById('cartItemsInput').value = ''; return;
    }

    empty.classList.add('hidden'); container.classList.remove('hidden');
    btn.disabled = false;
    const totalItems = cart.reduce((s, i) => s + i.quantity, 0);
    cnt.textContent = totalItems; cnt.classList.remove('hidden');
    if (mobileBadge) { mobileBadge.textContent = totalItems; mobileBadge.classList.remove('hidden'); }

    let html = '', total = 0;
    cart.forEach((item, index) => {
        const it = item.price * item.quantity; total += it;
        html += `<div class="bg-gray-800/60 rounded-xl p-3 border border-gray-700/50">
            <div class="flex justify-between items-start gap-2 mb-2">
                <p class="text-sm font-semibold text-white leading-tight flex-1">${escapeHtml(item.name)}</p>
                <button type="button" onclick="window.removeItem(${index})" class="flex-shrink-0 text-gray-600 hover:text-red-400 transition-colors p-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            ${item.notes ? `<p class="text-xs text-gray-500 italic mb-2">${escapeHtml(item.notes)}</p>` : ''}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.updateQuantity(${index},-1)" class="w-7 h-7 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-bold flex items-center justify-center">−</button>
                    <span class="w-7 text-center text-sm font-bold text-white tabular-nums">${item.quantity}</span>
                    <button type="button" onclick="window.updateQuantity(${index},1)" class="w-7 h-7 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-bold flex items-center justify-center">+</button>
                </div>
                <span class="text-sm font-bold text-amber-400">${it.toFixed(2)} DH</span>
            </div></div>`;
    });
    container.innerHTML = html;
    document.getElementById('cartTotal').textContent = total.toFixed(2) + ' DH';
    updateGrandTotal(total);
    document.getElementById('cartItemsInput').value = JSON.stringify(cart);
}
function updateGrandTotal(cartTotal) {
    const gt = document.getElementById('grandTotal');
    if (gt) gt.textContent = (existingOrderTotal + cartTotal).toFixed(2) + ' DH';
}
window.removeItem = function (i) { cart.splice(i, 1); updateCart(); };
window.updateQuantity = function (i, d) { const q = cart[i].quantity + d; if (q <= 0) cart.splice(i,1); else cart[i].quantity = q; updateCart(); };
function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ── Form submit via AJAX ───────────────────── */
document.getElementById('orderForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (cart.length === 0) { alert('Veuillez ajouter au moins un produit.'); return; }
    const btn = document.getElementById('submitOrder');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Envoi…';
    const items = cart.map(i => ({ produit_id: parseInt(i.produit_id), quantity: parseInt(i.quantity), notes: i.notes || null }));
    const waiterNotes = document.querySelector('[name="waiter_notes"]').value;
    fetch('{{ route("waiter.order.store", $table) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ items, waiter_notes: waiterNotes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = '{{ route("waiter.index") }}'; }
        else {
            alert(data.message || 'Une erreur est survenue.');
            btn.disabled = false;
            btn.innerHTML = restoreSubmitLabel();
        }
    })
    .catch(() => {
        alert('Erreur réseau. Veuillez réessayer.');
        btn.disabled = false;
        btn.innerHTML = restoreSubmitLabel();
    });
});
function restoreSubmitLabel() {
    const icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>';
    return icon + (existingOrderTotal > 0 ? ' Ajouter à la commande' : ' Envoyer à la cuisine');
}

/* ── Finalize modal ─────────────────────────── */
@if($existingOrder)
const finalizeUrl = '{{ route("waiter.order.finalize", $existingOrder) }}';
window.openFinalizeModal = function () {
    document.getElementById('finalizeModal').classList.remove('hidden');
    document.getElementById('finalizeError').classList.add('hidden');
};
window.closeFinalizeModal = function () {
    document.getElementById('finalizeModal').classList.add('hidden');
};
window.confirmFinalize = function () {
    const btn = document.getElementById('finalizeConfirmBtn');
    btn.disabled = true;
    btn.textContent = 'Finalisation…';
    fetch(finalizeUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '{{ route("waiter.index") }}';
        } else {
            document.getElementById('finalizeError').textContent = data.message;
            document.getElementById('finalizeError').classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Confirmer';
        }
    })
    .catch(() => {
        document.getElementById('finalizeError').textContent = 'Erreur réseau. Réessayez.';
        document.getElementById('finalizeError').classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Confirmer';
    });
};

/* ── Cancel modal ───────────────────────────── */
window.openCancelModal = function () {
    document.getElementById('cancelModal').classList.remove('hidden');
    document.getElementById('cancelPinInput').value = '';
    document.getElementById('cancelPinError').classList.add('hidden');
};
window.closeCancelModal = function () {
    document.getElementById('cancelModal').classList.add('hidden');
};
window.confirmCancel = function () {
    const pin = isAdmin ? null : document.getElementById('cancelPinInput').value.trim();
    const btn = document.getElementById('cancelConfirmBtn');
    btn.disabled = true;
    btn.textContent = 'Annulation…';
    const body = isAdmin ? {} : { admin_pin: pin };
    fetch(cancelUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '{{ route("waiter.index") }}';
        } else {
            document.getElementById('cancelPinError').textContent = data.message;
            document.getElementById('cancelPinError').classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Confirmer l\'annulation';
        }
    })
    .catch(() => {
        document.getElementById('cancelPinError').textContent = 'Erreur réseau. Réessayez.';
        document.getElementById('cancelPinError').classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Confirmer l\'annulation';
    });
};

/* ── Transfer modal ─────────────────────────── */
window.openTransferModal = function () {
    document.getElementById('transferModal').classList.remove('hidden');
    document.getElementById('transferError').classList.add('hidden');
    document.querySelectorAll('.transfer-table-btn').forEach(b => b.classList.remove('ring-2','ring-blue-500','bg-blue-500/20'));
    document.getElementById('selectedTargetTable').value = '';
    document.getElementById('transferConfirmBtn').disabled = true;
};
window.closeTransferModal = function () {
    document.getElementById('transferModal').classList.add('hidden');
};
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.transfer-table-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.transfer-table-btn').forEach(b => b.classList.remove('ring-2','ring-blue-500','bg-blue-500/20'));
            this.classList.add('ring-2','ring-blue-500','bg-blue-500/20');
            document.getElementById('selectedTargetTable').value = this.dataset.tableId;
            document.getElementById('transferConfirmBtn').disabled = false;
        });
    });
});
window.confirmTransfer = function () {
    const targetId = document.getElementById('selectedTargetTable').value;
    if (!targetId) return;
    const btn = document.getElementById('transferConfirmBtn');
    btn.disabled = true;
    btn.textContent = 'Transfert…';
    fetch(transferUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ target_table_id: parseInt(targetId) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '{{ route("waiter.index") }}';
        } else {
            document.getElementById('transferError').textContent = data.message;
            document.getElementById('transferError').classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Confirmer le transfert';
        }
    })
    .catch(() => {
        document.getElementById('transferError').textContent = 'Erreur réseau. Réessayez.';
        document.getElementById('transferError').classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Confirmer le transfert';
    });
};
@endif
</script>
@endpush
</x-layout.app>

