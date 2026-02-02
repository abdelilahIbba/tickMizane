<x-layout.app title="Commande - Table {{ $table->numero }}">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <form id="orderForm" method="POST" action="{{ route('waiter.order.store', $table) }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Products Section (Left) -->
            <div class="lg:col-span-2">
                <!-- Header -->
                <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Table {{ $table->numero }}</h1>
                            <p class="text-gray-400">{{ $table->name }}</p>
                        </div>
                        <a href="{{ route('waiter.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                            Retour
                        </a>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-4 mb-4">
                    <div class="flex overflow-x-auto gap-2 pb-2">
                        <button type="button" 
                                class="category-tab px-4 py-2 rounded-lg font-medium whitespace-nowrap active bg-gray-800 text-gray-300 hover:bg-gray-700" 
                                data-category="all">
                            Tous
                        </button>
                        @foreach($categories as $category)
                        <button type="button" 
                                class="category-tab px-4 py-2 rounded-lg font-medium whitespace-nowrap bg-gray-800 text-gray-300 hover:bg-gray-700" 
                                data-category="{{ $category->id }}">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="productsGrid">
                    @foreach($products as $product)
                    <div class="product-card bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-4 cursor-pointer hover:shadow-xl transition-all duration-200 hover:border-blue-500 hover:scale-105 transform"
                         data-category="{{ $product->category_id }}"
                         data-product-id="{{ $product->id }}"
                         data-product-name="{{ $product->name }}"
                         data-product-price="{{ $product->price_vente }}"
                         data-stock="{{ $product->stock_quantity }}">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 bg-blue-500/20 rounded-full flex items-center justify-center border border-blue-500/30">
                                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-white mb-1">{{ $product->name }}</h3>
                            <p class="text-lg font-bold text-blue-400">{{ number_format($product->price_vente, 2) }} DH</p>
                            <p class="text-xs text-gray-500 mt-1">Stock: {{ $product->stock_quantity }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Cart (Right) -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-white mb-4">Panier</h2>

                    <!-- Cart Items -->
                    <div id="emptyCart" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>Panier vide</p>
                    </div>
                    <div id="cartItems" class="space-y-3 mb-4 max-h-96 overflow-y-auto hidden">
                        <!-- Cart items will be added here dynamically -->
                    </div>

                    <!-- Waiter Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Notes générales</label>
                        <textarea name="waiter_notes" 
                                  rows="2" 
                                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500"
                                  placeholder="Instructions spéciales..."></textarea>
                    </div>

                    <!-- Total -->
                    <div class="border-t border-gray-700 pt-4 mb-4">
                        <div class="flex justify-between items-center text-xl font-bold text-white">
                            <span>Total:</span>
                            <span id="cartTotal" class="text-blue-400">0.00 DH</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            id="submitOrder"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-700 disabled:cursor-not-allowed transition-colors shadow-lg hover:shadow-xl transform hover:scale-105"
                            disabled>
                        Envoyer à la cuisine
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden input for cart items -->
        <input type="hidden" name="items" id="cartItemsInput">
    </form>
</div>

<!-- Product Notes Modal -->
<div id="notesModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg shadow-xl border border-gray-800 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-white mb-4">Notes pour <span id="modalProductName" class="text-blue-400"></span></h3>
        
        <!-- Quantity Selector -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Quantité</label>
            <div class="flex items-center justify-center gap-4">
                <button type="button" onclick="updateModalQuantity(-1)" class="w-10 h-10 bg-gray-700 text-white rounded-lg hover:bg-gray-600 font-bold text-xl">
                    -
                </button>
                <span id="modalQuantity" class="text-2xl font-bold text-white w-12 text-center">1</span>
                <button type="button" onclick="updateModalQuantity(1)" class="w-10 h-10 bg-gray-700 text-white rounded-lg hover:bg-gray-600 font-bold text-xl">
                    +
                </button>
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Notes (optionnel)</label>
            <textarea id="productNotes" 
                      rows="3" 
                      class="w-full px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-500"
                      placeholder="Ex: sans sucre, extra sauce..."></textarea>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="window.closeNotesModal()" class="flex-1 px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                Annuler
            </button>
            <button type="button" onclick="window.saveProductNotes()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Ajouter
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];
let currentProduct = null;
let modalQuantity = 1;

// Category filtering
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(t => {
            t.classList.remove('active', 'bg-gray-800', 'text-gray-300');
        });
        this.classList.add('active', 'bg-gray-800', 'text-gray-300');
        
        const category = this.dataset.category;
        
        // Filter products
        document.querySelectorAll('.product-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
});

// Set initial active state
document.querySelector('.category-tab.active').classList.add('bg-gray-800', 'text-gray-300');

// Add product to cart
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function() {
        currentProduct = {
            id: this.dataset.productId,
            name: this.dataset.productName,
            price: parseFloat(this.dataset.productPrice),
            stock: parseInt(this.dataset.stock)
        };
        
        // Reset modal
        modalQuantity = 1;
        document.getElementById('modalQuantity').textContent = modalQuantity;
        document.getElementById('modalProductName').textContent = currentProduct.name;
        document.getElementById('productNotes').value = '';
        document.getElementById('notesModal').classList.remove('hidden');
    });
});

window.updateModalQuantity = function(delta) {
    if (!currentProduct) return;
    modalQuantity += delta;
    if (modalQuantity < 1) modalQuantity = 1;
    if (modalQuantity > currentProduct.stock) modalQuantity = currentProduct.stock;
    document.getElementById('modalQuantity').textContent = modalQuantity;
}

window.closeNotesModal = function() {
    document.getElementById('notesModal').classList.add('hidden');
    modalQuantity = 1;
    currentProduct = null;
}

window.saveProductNotes = function() {
    if (!currentProduct) {
        console.error('No product selected');
        return;
    }
    
    const notes = document.getElementById('productNotes').value.trim();
    
    // Check if same product with same notes exists in cart
    const existingIndex = cart.findIndex(item => 
        item.produit_id === currentProduct.id && item.notes === notes
    );
    
    if (existingIndex >= 0) {
        // Add to existing quantity
        cart[existingIndex].quantity += modalQuantity;
    } else {
        // Add new item to cart
        cart.push({
            produit_id: currentProduct.id,
            name: currentProduct.name,
            price: currentProduct.price,
            quantity: modalQuantity,
            notes: notes
        });
    }
    
    updateCart();
    window.closeNotesModal();
}

function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const submitButton = document.getElementById('submitOrder');
    
    if (cart.length === 0) {
        emptyCart.classList.remove('hidden');
        cartContainer.classList.add('hidden');
        submitButton.disabled = true;
        document.getElementById('cartTotal').textContent = '0.00 DH';
        cartContainer.innerHTML = '';
        return;
    }
    
    emptyCart.classList.add('hidden');
    cartContainer.classList.remove('hidden');
    submitButton.disabled = false;
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h4 class="font-semibold text-white">${item.name}</h4>
                        ${item.notes ? `<p class="text-xs text-gray-400 italic">${item.notes}</p>` : ''}
                    </div>
                    <button type="button" onclick="window.removeItem(${index})" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.updateQuantity(${index}, -1)" class="w-7 h-7 bg-gray-700 text-white rounded hover:bg-gray-600">-</button>
                        <span class="w-8 text-center font-semibold text-white">${item.quantity}</span>
                        <button type="button" onclick="window.updateQuantity(${index}, 1)" class="w-7 h-7 bg-gray-700 text-white rounded hover:bg-gray-600">+</button>
                    </div>
                    <span class="font-bold text-blue-400">${itemTotal.toFixed(2)} DH</span>
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = html;
    document.getElementById('cartTotal').textContent = total.toFixed(2) + ' DH';
    document.getElementById('cartItemsInput').value = JSON.stringify(cart);
}

window.removeItem = function(index) {
    cart.splice(index, 1);
    updateCart();
}

window.updateQuantity = function(index, delta) {
    const newQuantity = cart[index].quantity + delta;
    if (newQuantity <= 0) {
        cart.splice(index, 1);
    } else {
        cart[index].quantity = newQuantity;
    }
    updateCart();
}

// Form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins un produit');
    }
});
</script>
@endpush
</x-layout.app>
