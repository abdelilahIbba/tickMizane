<x-layout.app title="Point de Vente">
    {{-- Table Info Banner --}}
    @if(isset($table) && $table)
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 text-gray-900 shadow-lg">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <div>
                    <h3 class="text-xl font-bold">{{ $table->name }}</h3>
                    <p class="text-sm opacity-90">{{ $table->places }} places • {{ $table->getZoneDisplayName() }}</p>
                </div>
            </div>
            <a href="{{ route('tables.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
                Retour aux tables
            </a>
        </div>
    </div>
    @endif

    <div 
        x-data="posSystem()"
        class="h-[calc(100vh-{{ isset($table) && $table ? '144' : '64' }}px)] flex flex-col lg:flex-row"
    >
        {{-- Barre d'onglets réactive : Visible uniquement sur mobile/tablette (< lg) --}}
        {{-- Permet de basculer l'affichage complet entre le catalogue et le panier pour les caissiers --}}
        <div class="flex lg:hidden bg-gray-900 border-b border-gray-800 flex-shrink-0 w-full">
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
                Panier
                <span x-show="cart.length > 0" class="text-xs bg-amber-500 text-black font-bold px-2 py-0.5 rounded-full transition-all" x-text="cart.reduce((s, i) => s + i.quantity, 0)"></span>
            </button>
        </div>

        {{-- Products Section --}}
        {{-- Affiche la grille si l'onglet Catalogue est actif. Sur grand écran, toujours visible (lg:flex) --}}
        <div :class="activeTab === 'menu' ? 'flex' : 'hidden lg:flex'" class="flex-1 flex-col overflow-hidden w-full">
            {{-- Header & Search --}}
            <div class="p-4 bg-gray-900 border-b border-gray-800">
                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Search --}}
                    <div class="flex-1">
                        <div class="relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input 
                                type="text" 
                                x-model="search"
                                placeholder="Rechercher un produit..."
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            >
                        </div>
                    </div>
                    
                    {{-- Category Filter --}}
                    <div class="flex gap-2 overflow-x-auto pb-2 sm:pb-0">
                        <button 
                            @click="selectedCategory = null"
                            :class="selectedCategory === null ? 'bg-amber-500 text-gray-900' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
                            class="px-4 py-2 rounded-xl font-medium whitespace-nowrap transition-colors"
                        >
                            Tout
                        </button>
                        <template x-for="category in categories" :key="category.id">
                            <button 
                                @click="selectedCategory = category.id"
                                :class="selectedCategory === category.id ? 'bg-amber-500 text-gray-900' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
                                class="px-4 py-2 rounded-xl font-medium whitespace-nowrap transition-colors"
                                x-text="category.name"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- Products Section with Vertical Alphabet Strip on Left --}}
            <div class="flex-1 flex flex-row overflow-hidden relative">

                {{-- Vertical Alphabet Strip (A → Z) --}}
                <div class="w-12 sm:w-14 bg-gray-900/90 border-r border-gray-800 flex flex-col items-center py-2 px-1 gap-1 overflow-y-auto flex-shrink-0 scrollbar-hide select-none">
                    {{-- "TOUS" button --}}
                    <button type="button"
                            @click="setLetter('all')"
                            :class="selectedLetter === 'all' ? 'bg-amber-500 text-gray-950 border-amber-400 shadow-md shadow-amber-500/20 scale-105' : 'text-gray-400 bg-gray-800/60 border-gray-700/60 hover:bg-gray-700/80 hover:text-white'"
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-[10px] sm:text-xs font-bold transition-all duration-200 flex flex-col items-center justify-center border active:scale-95 flex-shrink-0"
                            title="Tous les produits">
                        <span class="leading-none">TOUS</span>
                    </button>

                    <div class="w-7 h-[1px] bg-gray-800 my-1 flex-shrink-0"></div>

                    <template x-for="letter in 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')" :key="letter">
                        <button type="button"
                                @click="setLetter(letter)"
                                :disabled="!lettersWithProducts.has(letter)"
                                :class="{
                                    'bg-amber-500 text-gray-950 border-amber-400 shadow-md shadow-amber-500/20 scale-105': selectedLetter === letter,
                                    'text-gray-400 bg-gray-800/60 border-gray-700/60 hover:bg-gray-700/80 hover:text-white': selectedLetter !== letter && lettersWithProducts.has(letter),
                                    'opacity-30 border-gray-800/40 text-gray-600 bg-gray-900/30 cursor-not-allowed': !lettersWithProducts.has(letter)
                                }"
                                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center justify-center border active:scale-95 flex-shrink-0"
                                x-text="letter">
                        </button>
                    </template>
                </div>

                {{-- Products Grid Area --}}
                <div class="flex-1 overflow-y-auto p-4 flex flex-col">
                    {{-- Active Letter Filter Indicator Badge --}}
                    <div x-show="selectedLetter !== 'all'" class="mb-3 flex items-center justify-between bg-amber-500/10 border border-amber-500/30 rounded-xl px-3 py-2">
                        <div class="flex items-center gap-2 text-xs text-amber-400 font-medium">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Filtre lettre :</span>
                            <span class="px-2 py-0.5 bg-amber-500 text-gray-950 font-bold rounded-md text-xs" x-text="selectedLetter"></span>
                            <span class="text-gray-400 text-xs ml-1" x-text="'(' + filteredProducts.length + ' ' + (filteredProducts.length > 1 ? 'produits' : 'produit') + ')'"></span>
                        </div>
                        <button type="button" @click="setLetter('all')" class="text-xs font-semibold text-amber-400 hover:text-amber-300 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Réinitialiser (Tous)
                        </button>
                    </div>

                    {{-- Grille fluide s'adaptant à l'espace restant de chaque breakpoint --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div 
                                @click="addToCart(product)"
                                class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-pointer group flex flex-col"
                            >
                                {{-- Product Image --}}
                                <div class="aspect-[4/3] sm:aspect-square bg-gray-900 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-10 h-10 sm:w-16 sm:h-16 text-gray-700 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                
                                {{-- Product Info --}}
                                <div class="p-2 sm:p-4 flex-1 flex flex-col justify-between">
                                    <h3 class="text-xs sm:text-sm font-semibold text-white line-clamp-2 h-8 sm:h-10 group-hover:text-amber-400 transition-colors select-none" x-text="product.name"></h3>
                                    <div class="flex items-center justify-between mt-1 sm:mt-2">
                                        <span class="text-sm sm:text-lg font-bold text-amber-400" x-text="product.price.toFixed(2) + ' DH'"></span>
                                        <span class="text-[10px] sm:text-sm text-gray-400" x-text="'Stock: ' + product.stock"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Empty State --}}
                    <div x-show="filteredProducts.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                        <template x-if="selectedLetter !== 'all'">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-amber-400 font-bold text-2xl" x-text="selectedLetter"></div>
                        </template>
                        <template x-if="selectedLetter === 'all'">
                            <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <p class="text-gray-400 text-lg" x-text="selectedLetter !== 'all' ? 'Aucun produit ne commence par la lettre \'' + selectedLetter + '\'' : 'Aucun produit trouvé'"></p>
                        <template x-if="selectedLetter !== 'all'">
                            <button type="button" @click="setLetter('all')" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-amber-500/20 text-amber-400 border border-amber-500/40 rounded-xl text-xs font-semibold hover:bg-amber-500/30 transition-all">
                                Afficher tous les produits
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Cart Sidebar --}}
        {{-- Affiche le panier si l'onglet Panier est actif. Sur grand écran, toujours visible (lg:flex) --}}
        <div :class="activeTab === 'cart' ? 'flex' : 'hidden lg:flex'" class="w-full lg:w-96 xl:w-[420px] flex-col bg-gray-800 border-t lg:border-t-0 lg:border-l border-gray-700">
            {{-- Cart Header --}}
            <div class="px-6 py-4 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Panier
                    </h2>
                    <span class="px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-sm font-semibold" x-text="cart.length + ' articles'"></span>
                </div>
            </div>
            
            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-white truncate" x-text="item.name"></h4>
                                <p class="text-sm text-gray-400" x-text="item.price.toFixed(2) + ' DH'"></p>
                            </div>
                            <button 
                                @click="removeFromCart(index)"
                                class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Quantity Controls --}}
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="updateQuantity(index, -1)"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="w-12 text-center text-lg font-semibold text-white" x-text="item.quantity"></span>
                                <button 
                                    @click="updateQuantity(index, 1)"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="text-lg font-bold text-amber-400" x-text="(item.price * item.quantity).toFixed(2) + ' DH'"></span>
                        </div>
                    </div>
                </template>
                
                {{-- Empty Cart --}}
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-400 text-lg">Panier vide</p>
                    <p class="text-gray-500 text-sm mt-1">Ajoutez des produits pour commencer</p>
                </div>
            </div>
            
            {{-- Cart Footer --}}
            <div class="border-t border-gray-700 p-4 space-y-4 bg-gray-900">
                {{-- Totals --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-400">
                        <span>Sous-total</span>
                        <span x-text="subtotal.toFixed(2) + ' DH'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>TVA (20%)</span>
                        <span x-text="tax.toFixed(2) + ' DH'"></span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-white pt-2 border-t border-gray-700">
                        <span>Total TTC</span>
                        <span class="text-amber-400" x-text="total.toFixed(2) + ' DH'"></span>
                    </div>
                </div>
                
                {{-- Payment Method --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-300">Mode de paiement</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="py-3 px-4 font-semibold rounded-xl transition-colors"
                        >
                            Espèces
                        </button>
                        <button 
                            @click="paymentMethod = 'carte'"
                            :class="paymentMethod === 'carte' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="py-3 px-4 font-semibold rounded-xl transition-colors"
                        >
                            Carte
                        </button>
                        <button 
                            @click="paymentMethod = 'mixte'"
                            :class="paymentMethod === 'mixte' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="py-3 px-4 font-semibold rounded-xl transition-colors"
                        >
                            Mixte
                        </button>
                    </div>
                </div>
                
                @if(isset($table) && $table)
                {{-- Table Order Info --}}
                <div class="bg-blue-500/20 border border-blue-500/50 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-300">
                            <p class="font-semibold mb-1">Commande pour {{ $table->name }}</p>
                            <p class="text-blue-400">Cette commande sera marquée comme impayée. L'encaissement se fera depuis la table.</p>
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Checkout Button --}}
                <button 
                    @click="checkout()"
                    :disabled="cart.length === 0 || loading"
                    class="w-full py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 disabled:from-gray-600 disabled:to-gray-600 text-white font-bold text-lg rounded-xl transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <template x-if="!loading">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="loading">
                        <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Traitement...' : {{ isset($table) && $table ? "'Créer la commande'" : "'Valider la vente'" }}"></span>
                </button>
                
                {{-- Clear Cart --}}
                <button 
                    @click="clearCart()"
                    class="w-full py-3 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-xl transition-colors"
                >
                    Vider le panier
                </button>
            </div>
        </div>
    </div>
    
    @php
        $categoriesJson = $categories->map(function($c) {
            return ['id' => $c->id, 'name' => $c->name];
        })->values();
        
        $productsJson = $products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price_vente,
                'stock' => $p->stock_quantity,
                'category_id' => $p->category_id
            ];
        })->values();
    @endphp
    
    @push('scripts')
    <script>
        function posSystem() {
            return {
                search: '',
                selectedCategory: null,
                selectedLetter: 'all',
                paymentMethod: 'cash',
                cart: [],
                loading: false,
                activeTab: 'menu',
                
                categories: {!! json_encode($categoriesJson) !!},
                
                products: {!! json_encode($productsJson) !!},

                get lettersWithProducts() {
                    const set = new Set();
                    this.products.forEach(p => {
                        if (p.stock > 0 && (this.selectedCategory === null || p.category_id === this.selectedCategory)) {
                            const norm = (p.name || '').trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase();
                            const char = norm[0];
                            if (char >= 'A' && char <= 'Z') set.add(char);
                        }
                    });
                    return set;
                },
                
                get filteredProducts() {
                    return this.products.filter(p => {
                        const matchSearch = this.search === '' || p.name.toLowerCase().includes(this.search.toLowerCase());
                        const matchCategory = this.selectedCategory === null || p.category_id === this.selectedCategory;
                        const normName = (p.name || '').trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase();
                        const matchLetter = this.selectedLetter === 'all' || normName.startsWith(this.selectedLetter);
                        return matchSearch && matchCategory && matchLetter && p.stock > 0;
                    });
                },

                setLetter(letter) {
                    if (this.selectedLetter === letter && letter !== 'all') {
                        this.selectedLetter = 'all';
                    } else {
                        this.selectedLetter = letter;
                    }
                },
                
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                
                get tax() {
                    return 0; // Pas de TVA séparée, prix TTC
                },
                
                get total() {
                    return this.subtotal;
                },
                
                addToCart(product) {
                    if (product.stock <= 0) {
                        this.$dispatch('notify', { type: 'error', message: 'Stock insuffisant pour ' + product.name });
                        return;
                    }
                    
                    const existingIndex = this.cart.findIndex(item => item.id === product.id);
                    if (existingIndex > -1) {
                        // Check stock
                        if (this.cart[existingIndex].quantity >= product.stock) {
                            this.$dispatch('notify', { type: 'error', message: 'Stock insuffisant pour ' + product.name });
                            return;
                        }
                        this.cart[existingIndex].quantity++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: product.price,
                            quantity: 1,
                            maxStock: product.stock
                        });
                    }
                    this.$dispatch('notify', { type: 'success', message: product.name + ' ajouté au panier' });
                },
                
                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },
                
                updateQuantity(index, delta) {
                    const newQty = this.cart[index].quantity + delta;
                    if (newQty <= 0) {
                        this.removeFromCart(index);
                    } else if (newQty > this.cart[index].maxStock) {
                        this.$dispatch('notify', { type: 'error', message: 'Stock insuffisant' });
                    } else {
                        this.cart[index].quantity = newQty;
                    }
                },
                
                clearCart() {
                    this.cart = [];
                },
                
                async checkout() {
                    if (this.cart.length === 0 || this.loading) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('{{ route("pos.checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                items: this.cart.map(item => ({
                                    id: item.id,
                                    quantity: item.quantity
                                })),
                                payment_method: this.paymentMethod,
                                table_id: {{ $table->id ?? 'null' }}
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.$dispatch('notify', { type: 'success', message: data.message || 'Vente validée!' });
                            this.cart = [];
                            
                            // Redirect to tables if this was a table order
                            if (data.redirect_to_tables) {
                                setTimeout(() => window.location.href = '/tables', 1500);
                            } else {
                                // Update local stock for standalone orders
                                this.cart.forEach(item => {
                                    const product = this.products.find(p => p.id === item.id);
                                    if (product) {
                                        product.stock -= item.quantity;
                                    }
                                });
                                
                                // Reload page to get fresh stock data
                                setTimeout(() => window.location.reload(), 1500);
                            }
                        } else {
                            this.$dispatch('notify', { type: 'error', message: data.message || 'Erreur lors de la vente' });
                        }
                    } catch (error) {
                        console.error('Checkout error:', error);
                        this.$dispatch('notify', { type: 'error', message: 'Erreur de connexion' });
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-layout.app>
