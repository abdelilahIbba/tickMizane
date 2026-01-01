<x-layout.app title="Point de Vente">
    <div 
        x-data="posSystem()"
        class="h-[calc(100vh-64px)] flex flex-col lg:flex-row"
    >
        {{-- Products Section --}}
        <div class="flex-1 flex flex-col overflow-hidden">
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
            
            {{-- Products Grid --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div 
                            @click="addToCart(product)"
                            class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-pointer group"
                        >
                            {{-- Product Image --}}
                            <div class="aspect-square bg-gray-900 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-700 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            
                            {{-- Product Info --}}
                            <div class="p-4">
                                <h3 class="font-semibold text-white truncate group-hover:text-amber-400 transition-colors" x-text="product.name"></h3>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xl font-bold text-amber-400" x-text="product.price.toFixed(2) + ' DH'"></span>
                                    <span class="text-sm text-gray-400" x-text="'Stock: ' + product.stock"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                {{-- Empty State --}}
                <div x-show="filteredProducts.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-400 text-lg">Aucun produit trouvé</p>
                </div>
            </div>
        </div>
        
        {{-- Cart Sidebar --}}
        <div class="w-full lg:w-96 xl:w-[420px] flex flex-col bg-gray-800 border-t lg:border-t-0 lg:border-l border-gray-700">
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
                            @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="py-3 px-4 font-semibold rounded-xl transition-colors"
                        >
                            Carte
                        </button>
                        <button 
                            @click="paymentMethod = 'mixed'"
                            :class="paymentMethod === 'mixed' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                            class="py-3 px-4 font-semibold rounded-xl transition-colors"
                        >
                            Mixte
                        </button>
                    </div>
                </div>
                
                {{-- Checkout Button --}}
                <button 
                    @click="checkout()"
                    :disabled="cart.length === 0"
                    class="w-full py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 disabled:from-gray-600 disabled:to-gray-600 text-white font-bold text-lg rounded-xl transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Valider la vente
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
    
    @push('scripts')
    <script>
        function posSystem() {
            return {
                search: '',
                selectedCategory: null,
                paymentMethod: 'cash',
                cart: [],
                
                categories: [
                    { id: 1, name: 'Boissons' },
                    { id: 2, name: 'Snacks' },
                    { id: 3, name: 'Épicerie' },
                    { id: 4, name: 'Hygiène' },
                ],
                
                products: [
                    { id: 1, name: 'Eau minérale 1.5L', price: 5.00, stock: 50, category_id: 1 },
                    { id: 2, name: 'Coca-Cola 33cl', price: 8.00, stock: 45, category_id: 1 },
                    { id: 3, name: 'Jus d\'orange 1L', price: 15.00, stock: 30, category_id: 1 },
                    { id: 4, name: 'Café Express', price: 12.00, stock: 100, category_id: 1 },
                    { id: 5, name: 'Chips Lays 150g', price: 15.00, stock: 28, category_id: 2 },
                    { id: 6, name: 'Biscuits Oreo', price: 12.00, stock: 35, category_id: 2 },
                    { id: 7, name: 'Chocolat Milka', price: 25.00, stock: 20, category_id: 2 },
                    { id: 8, name: 'Cacahuètes 200g', price: 18.00, stock: 40, category_id: 2 },
                    { id: 9, name: 'Pain de mie', price: 12.00, stock: 15, category_id: 3 },
                    { id: 10, name: 'Lait 1L', price: 10.00, stock: 60, category_id: 3 },
                    { id: 11, name: 'Œufs (6pcs)', price: 18.00, stock: 25, category_id: 3 },
                    { id: 12, name: 'Beurre 250g', price: 35.00, stock: 18, category_id: 3 },
                ],
                
                get filteredProducts() {
                    return this.products.filter(p => {
                        const matchSearch = this.search === '' || p.name.toLowerCase().includes(this.search.toLowerCase());
                        const matchCategory = this.selectedCategory === null || p.category_id === this.selectedCategory;
                        return matchSearch && matchCategory;
                    });
                },
                
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                
                get tax() {
                    return this.subtotal * 0.2;
                },
                
                get total() {
                    return this.subtotal + this.tax;
                },
                
                addToCart(product) {
                    const existingIndex = this.cart.findIndex(item => item.id === product.id);
                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: product.price,
                            quantity: 1
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
                    } else {
                        this.cart[index].quantity = newQty;
                    }
                },
                
                clearCart() {
                    this.cart = [];
                },
                
                checkout() {
                    if (this.cart.length === 0) return;
                    
                    // Here you would typically submit to the server
                    this.$dispatch('notify', { type: 'success', message: 'Vente validée! Total: ' + this.total.toFixed(2) + ' DH' });
                    this.cart = [];
                }
            }
        }
    </script>
    @endpush
</x-layout.app>
