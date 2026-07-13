<x-layout.app title="Commande — Table {{ $table->numero }}">
{{-- x-data="{ activeTab: 'menu' }" initialise AlpineJS pour le basculement dynamique d'onglets sur mobile --}}
<div class="h-[calc(100vh-4rem)] flex flex-col overflow-hidden" x-data="{ activeTab: 'menu' }">
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

                {{-- Products grid --}}
                {{-- Grille fluide s'adaptant à l'espace restant de chaque breakpoint --}}
                <div class="flex-1 overflow-y-auto p-4">
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
                            {{-- Style compact optimisé pour mobile (paddings plus petits, tailles ajustées, hauteur fixe pour alignement) --}}
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

                    {{-- Empty state --}}
                    <div id="emptyFilter" class="hidden text-center py-16 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Aucun produit dans cette catégorie</p>
                    </div>
                </div>
            </div>

            {{-- ─────────────────── RIGHT: Cart ─────────────────── --}}
            {{-- Affiche le panier si l'onglet Commande est actif. Sur grand écran, toujours visible (lg:flex) --}}
            <div :class="activeTab === 'cart' ? 'flex' : 'hidden lg:flex'" class="w-full lg:w-80 xl:w-96 flex-col bg-gray-900/60 flex-shrink-0">

                {{-- Cart header --}}
                <div class="px-5 py-4 border-b border-gray-800 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Commande
                        </h2>
                        <span id="cartCount" class="hidden text-xs bg-amber-500 text-black font-bold px-2 py-0.5 rounded-full">0</span>
                    </div>
                </div>

                {{-- Cart items --}}
                <div class="flex-1 overflow-y-auto px-4 py-3">
                    <div id="emptyCart" class="flex flex-col items-center justify-center h-full text-gray-600 py-12">
                        <svg class="w-16 h-16 mb-3 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-500">Panier vide</p>
                        <p class="text-xs text-gray-600 mt-1">Sélectionnez des produits</p>
                    </div>
                    <div id="cartItems" class="space-y-2 hidden"></div>
                </div>

                {{-- Cart footer --}}
                <div class="flex-shrink-0 border-t border-gray-800 p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Instructions générales</label>
                        <textarea name="waiter_notes"
                                  rows="2"
                                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg text-sm
                                         focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600 resize-none"
                                  placeholder="Notes pour la cuisine..."></textarea>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-gray-800">
                        <span class="text-base font-semibold text-gray-300">Total</span>
                        <span id="cartTotal" class="text-xl font-bold text-amber-400">0.00 DH</span>
                    </div>
                    <button type="submit"
                            id="submitOrder"
                            disabled
                            class="w-full flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-sm
                                   bg-amber-500 text-black hover:bg-amber-400
                                   disabled:bg-gray-700 disabled:text-gray-500 disabled:cursor-not-allowed
                                   transition-all duration-150 shadow-lg shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                        Envoyer à la cuisine
                    </button>
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

@push('scripts')
<script>
let cart = [];
let currentProduct = null;
let modalQuantity = 1;

/* ── Category filtering ─────────────────────── */
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

        const cat = this.dataset.category;
        let visible = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            const show = cat === 'all' || card.dataset.category === cat;
            card.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        document.getElementById('emptyFilter').classList.toggle('hidden', visible > 0);
    });
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
        document.getElementById('cartItemsInput').value = ''; return;
    }

    empty.classList.add('hidden'); container.classList.remove('hidden');
    btn.disabled = false;
    const totalItems = cart.reduce((s, i) => s + i.quantity, 0);
    cnt.textContent = totalItems; cnt.classList.remove('hidden');
    if (mobileBadge) {
        mobileBadge.textContent = totalItems;
        mobileBadge.classList.remove('hidden');
    }

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
    document.getElementById('cartItemsInput').value = JSON.stringify(cart);
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
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
        body: JSON.stringify({ items, waiter_notes: waiterNotes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = '{{ route("waiter.index") }}'; }
        else {
            alert(data.message || 'Une erreur est survenue.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg> Envoyer à la cuisine';
        }
    })
    .catch(() => {
        alert('Erreur réseau. Veuillez réessayer.');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg> Envoyer à la cuisine';
    });
});
</script>
@endpush
</x-layout.app>
