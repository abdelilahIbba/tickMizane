@extends('layouts.app')

@section('title', 'Commande - Table ' . $table->numero)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <form id="orderForm" method="POST" action="{{ route('waiter.order.store', $table) }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Products Section (Left) -->
            <div class="lg:col-span-2">
                <!-- Header -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Table {{ $table->numero }}</h1>
                            <p class="text-gray-600">{{ $table->name }}</p>
                        </div>
                        <a href="{{ route('waiter.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Retour
                        </a>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <div class="flex overflow-x-auto gap-2 pb-2">
                        <button type="button" 
                                class="category-tab px-4 py-2 rounded-lg font-medium whitespace-nowrap active" 
                                data-category="all">
                            Tous
                        </button>
                        @foreach($categories as $category)
                        <button type="button" 
                                class="category-tab px-4 py-2 rounded-lg font-medium whitespace-nowrap" 
                                data-category="{{ $category->id }}">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="productsGrid">
                    @foreach($products as $product)
                    <div class="product-card bg-white rounded-lg shadow-sm p-4 cursor-pointer hover:shadow-md transition-shadow border-2 border-transparent hover:border-blue-500"
                         data-category="{{ $product->category_id }}"
                         data-product-id="{{ $product->id }}"
                         data-product-name="{{ $product->name }}"
                         data-product-price="{{ $product->price_vente }}"
                         data-stock="{{ $product->stock_quantity }}">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ $product->name }}</h3>
                            <p class="text-lg font-bold text-blue-600">{{ number_format($product->price_vente, 2) }} DH</p>
                            <p class="text-xs text-gray-500 mt-1">Stock: {{ $product->stock_quantity }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Cart (Right) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Panier</h2>

                    <!-- Cart Items -->
                    <div id="cartItems" class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                        <!-- Cart items will be added here dynamically -->
                        <div id="emptyCart" class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p>Panier vide</p>
                        </div>
                    </div>

                    <!-- Waiter Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes générales</label>
                        <textarea name="waiter_notes" 
                                  rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Instructions spéciales..."></textarea>
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between items-center text-xl font-bold">
                            <span>Total:</span>
                            <span id="cartTotal">0.00 DH</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            id="submitOrder"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
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
<div id="notesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Notes pour <span id="modalProductName"></span></h3>
        <textarea id="productNotes" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4"
                  placeholder="Ex: sans sucre, extra sauce..."></textarea>
        <div class="flex gap-3">
            <button type="button" onclick="closeNotesModal()" class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                Annuler
            </button>
            <button type="button" onclick="saveProductNotes()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Ajouter
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let currentProduct = null;

// Category filtering
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active', 'bg-blue-600', 'text-white'));
        this.classList.add('active', 'bg-blue-600', 'text-white');
        
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
document.querySelector('.category-tab.active').classList.add('bg-blue-600', 'text-white');

// Add product to cart
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function() {
        currentProduct = {
            id: this.dataset.productId,
            name: this.dataset.productName,
            price: parseFloat(this.dataset.productPrice),
            stock: parseInt(this.dataset.stock)
        };
        
        // Show notes modal
        document.getElementById('modalProductName').textContent = currentProduct.name;
        document.getElementById('productNotes').value = '';
        document.getElementById('notesModal').classList.remove('hidden');
    });
});

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
    currentProduct = null;
}

function saveProductNotes() {
    if (!currentProduct) return;
    
    const notes = document.getElementById('productNotes').value;
    
    // Check if product already in cart
    const existingIndex = cart.findIndex(item => item.produit_id === currentProduct.id && item.notes === notes);
    
    if (existingIndex >= 0) {
        cart[existingIndex].quantity++;
    } else {
        cart.push({
            produit_id: currentProduct.id,
            name: currentProduct.name,
            price: currentProduct.price,
            quantity: 1,
            notes: notes
        });
    }
    
    updateCart();
    closeNotesModal();
}

function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const submitButton = document.getElementById('submitOrder');
    
    if (cart.length === 0) {
        emptyCart.classList.remove('hidden');
        submitButton.disabled = true;
        document.getElementById('cartTotal').textContent = '0.00 DH';
        return;
    }
    
    emptyCart.classList.add('hidden');
    submitButton.disabled = false;
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="bg-gray-50 rounded-lg p-3 border">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${item.name}</h4>
                        ${item.notes ? `<p class="text-xs text-gray-600 italic">${item.notes}</p>` : ''}
                    </div>
                    <button type="button" onclick="removeItem(${index})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="updateQuantity(${index}, -1)" class="w-7 h-7 bg-gray-200 rounded hover:bg-gray-300">-</button>
                        <span class="w-8 text-center font-semibold">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${index}, 1)" class="w-7 h-7 bg-gray-200 rounded hover:bg-gray-300">+</button>
                    </div>
                    <span class="font-bold text-blue-600">${itemTotal.toFixed(2)} DH</span>
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = html;
    document.getElementById('cartTotal').textContent = total.toFixed(2) + ' DH';
    document.getElementById('cartItemsInput').value = JSON.stringify(cart);
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateQuantity(index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
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
