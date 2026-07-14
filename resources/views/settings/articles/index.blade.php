<x-layout.app title="Articles — Gestion du catalogue">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Articles</h1>
            <p class="text-gray-400 mt-1">Gérez les catégories et produits du catalogue</p>
        </div>
        <button onclick="openCategoryModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-black font-semibold rounded-xl hover:bg-amber-400 transition-colors shadow-lg shadow-amber-500/20 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvelle catégorie
        </button>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label' => 'Catégories',  'value' => $stats['categories'],      'color' => 'text-blue-400',  'bg' => 'bg-blue-500/10'],
            ['label' => 'Actives',     'value' => $stats['active_cats'],     'color' => 'text-green-400', 'bg' => 'bg-green-500/10'],
            ['label' => 'Articles',    'value' => $stats['products'],        'color' => 'text-amber-400', 'bg' => 'bg-amber-500/10'],
            ['label' => 'Disponibles', 'value' => $stats['active_products'], 'color' => 'text-purple-400','bg' => 'bg-purple-500/10'],
        ] as $s)
        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 {{ $s['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="{{ $s['color'] }} font-bold text-lg">{{ $s['value'] }}</span>
            </div>
            <span class="text-sm text-gray-400">{{ $s['label'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- ── Alerts ──────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm">
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

    {{-- ── Two-panel layout ───────────────────────────────────── --}}
    <div class="flex gap-6">

        {{-- LEFT: Categories panel --}}
        <div class="w-80 flex-shrink-0">
            <div class="bg-gray-900/50 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Catégories</h2>
                    <span class="text-xs text-gray-500">{{ $categories->count() }}</span>
                </div>
                <div class="divide-y divide-gray-800">
                    @forelse($categories as $cat)
                    <div class="category-row group flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-800/50 transition-colors
                                {{ ($selectedCategory?->id === $cat->id) ? 'bg-amber-500/10 border-l-2 border-amber-500' : '' }}"
                         onclick="selectCategory({{ $cat->id }})">
                        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 bg-gray-800">
                            <img src="{{ $cat->display_image_url }}" alt="{{ $cat->name }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=80&q=60'">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $cat->name }}</p>
                            <p class="text-xs text-gray-500">{{ $cat->produits_count }} article{{ $cat->produits_count != 1 ? 's' : '' }}</p>
                        </div>
                        <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $cat->status === 'active' ? 'bg-green-400' : 'bg-gray-600' }}"></div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                            <button type="button"
                                    onclick="openEditCategoryModal({{ $cat->id }}, @json($cat->name), @json($cat->description ?? ''), '{{ $cat->status }}', @json($cat->display_image_url))"
                                    class="p-1 text-gray-500 hover:text-amber-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @if($cat->produits_count == 0)
                            <form action="{{ route('settings.articles.categories.destroy', $cat) }}" method="POST"
                                  onsubmit="return confirm('Supprimer {{ addslashes($cat->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 text-gray-500 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-gray-500 text-sm">
                        Aucune catégorie.<br>
                        <button onclick="openCategoryModal()" class="text-amber-400 hover:underline mt-1">Créer la première</button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT: Products panel --}}
        <div class="flex-1 min-w-0">
            <div id="noCategorySelected" class="{{ $selectedCategory ? 'hidden' : '' }} flex flex-col items-center justify-center py-24 text-gray-600">
                <svg class="w-16 h-16 mb-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">Sélectionnez une catégorie</p>
                <p class="text-xs text-gray-600 mt-1">pour gérer ses articles</p>
            </div>

            <div id="productsContent" class="{{ $selectedCategory ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $selectedCategory?->name }}</h2>
                        <p class="text-sm text-gray-400">Articles de cette catégorie</p>
                    </div>
                    <button type="button" onclick="openProductModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 border border-gray-700 text-gray-300 hover:text-white hover:border-gray-600 rounded-xl text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvel article
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @if($selectedCategory)
                        @forelse($selectedCategory->produits as $product)
                        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl overflow-hidden hover:border-gray-700 transition-colors">
                            <div class="relative aspect-[16/9] bg-gray-800 overflow-hidden">
                                <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=400&q=60'">
                                <div class="absolute top-2 right-2">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $product->status === 'active' ? 'bg-green-500/90 text-white' : 'bg-gray-700 text-gray-400' }}">
                                        {{ $product->status === 'active' ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-white text-sm leading-tight mb-1">{{ $product->name }}</h3>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-amber-400 font-bold">{{ number_format($product->price_vente, 2) }} DH</span>
                                    <span class="text-xs text-gray-500">Stock: {{ $product->stock_quantity }} {{ $product->unit }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $product->kitchen_active ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30' }}">
                                        {{ $product->kitchen_active ? 'Cuisine' : 'Direct service' }}
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button"
                                            onclick="openEditProductModal({{ $product->id }}, {{ $product->category_id }}, @json($product->name), {{ $product->price_vente }}, {{ $product->price_achat ?? 0 }}, {{ $product->stock_quantity }}, {{ $product->alert_stock }}, @json($product->unit), '{{ $product->status }}', {{ $product->kitchen_active ? '1' : '0' }}, @json($product->display_image_url))"
                                            class="flex-1 py-1.5 text-xs font-medium bg-gray-800 border border-gray-700 text-gray-300 hover:text-white hover:border-gray-600 rounded-lg transition-colors">
                                        Modifier
                                    </button>
                                    <form action="{{ route('settings.articles.products.destroy', $product) }}" method="POST"
                                          class="flex-1" onsubmit="return confirm('Supprimer cet article ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full py-1.5 text-xs font-medium bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-3 text-center py-16 text-gray-600">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Aucun article</p>
                            <button onclick="openProductModal()" class="text-amber-400 hover:underline text-xs mt-1">Ajouter le premier article</button>
                        </div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════ CATEGORY MODAL (single form) ════════════════ --}}
<div id="categoryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeCategoryModal()"></div>
    <div class="relative bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <h3 id="catModalTitle" class="text-lg font-bold text-white">Nouvelle catégorie</h3>
            <button type="button" onclick="closeCategoryModal()" class="text-gray-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="catForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="catMethodInput" value="POST">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="catName" required maxlength="255"
                       class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                              focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
                       placeholder="Ex : Tajines">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Description</label>
                <textarea name="description" id="catDescription" rows="2" maxlength="500"
                          class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                 focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600 resize-none"
                          placeholder="Description courte..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Statut</label>
                <select name="status" id="catStatus"
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                               focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                    <option value="active">Actif</option>
                    <option value="archived">Archivé</option>
                </select>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-medium text-gray-400">Image</label>
                <input type="url" name="image_url" id="catImageUrl" maxlength="2048"
                       class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                              focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
                       placeholder="https://... (URL de l'image)">

                <label class="flex items-center gap-2 px-3 py-2.5 bg-gray-800 border border-dashed border-gray-600
                              rounded-xl cursor-pointer hover:border-gray-500 transition-colors text-sm text-gray-500 hover:text-gray-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="catFileLabel" class="flex-1 truncate">Choisir un fichier…</span>
                    <input type="file" name="image_file" id="catFileInput" accept="image/*"
                           class="sr-only">
                </label>

                <div id="catImagePreview" class="hidden">
                    <img id="catPreviewImg" src="" alt="Aperçu"
                         class="h-32 w-full object-cover rounded-xl border border-gray-700"
                         onerror="this.parentElement.classList.add('hidden')">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCategoryModal()"
                        class="flex-1 py-2.5 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">Annuler</button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-500 text-black rounded-xl hover:bg-amber-400 transition-colors text-sm font-semibold">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════ PRODUCT MODAL (single form) ════════════════ --}}
<div id="productModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeProductModal()"></div>
    <div class="relative bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900 z-10">
            <h3 id="prodModalTitle" class="text-lg font-bold text-white">Nouvel article</h3>
            <button type="button" onclick="closeProductModal()" class="text-gray-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="prodForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method"   id="prodMethodInput"   value="POST">
            <input type="hidden" name="category_id" id="prodCategoryId">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="prodName" required maxlength="255"
                       class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                              focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
                       placeholder="Ex : Tajine de poulet">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Prix de vente (DH) <span class="text-red-400">*</span></label>
                    <input type="number" name="price_vente" id="prodPriceVente" required min="0" step="0.01"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Prix d'achat (DH)</label>
                    <input type="number" name="price_achat" id="prodPriceAchat" min="0" step="0.01"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Stock initial <span class="text-red-400">*</span></label>
                    <input type="number" name="stock_quantity" id="prodStock" required min="0"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Seuil d'alerte</label>
                    <input type="number" name="alert_stock" id="prodAlertStock" min="0"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="10">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Unité</label>
                    <input type="text" name="unit" id="prodUnit" maxlength="50"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
                           placeholder="portion, bol, pièce…">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Statut</label>
                    <select name="status" id="prodStatus"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                                   focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Cuisine</label>
                <select name="kitchen_active" id="prodKitchenActive"
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                               focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                    <option value="1">Passe par la cuisine</option>
                    <option value="0">Direct service</option>
                </select>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-medium text-gray-400">Image</label>
                <input type="url" name="image_url" id="prodImageUrl" maxlength="2048"
                       class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                              focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
                       placeholder="https://... (URL de l'image)">

                <label class="flex items-center gap-2 px-3 py-2.5 bg-gray-800 border border-dashed border-gray-600
                              rounded-xl cursor-pointer hover:border-gray-500 transition-colors text-sm text-gray-500 hover:text-gray-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="prodFileLabel" class="flex-1 truncate">Choisir un fichier…</span>
                    <input type="file" name="image_file" id="prodFileInput" accept="image/*"
                           class="sr-only">
                </label>

                <div id="prodImagePreview" class="hidden">
                    <img id="prodPreviewImg" src="" alt="Aperçu"
                         class="h-32 w-full object-cover rounded-xl border border-gray-700"
                         onerror="this.parentElement.classList.add('hidden')">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeProductModal()"
                        class="flex-1 py-2.5 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors text-sm font-medium">Annuler</button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-500 text-black rounded-xl hover:bg-amber-400 transition-colors text-sm font-semibold">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE   = '{{ rtrim(url('/'), '/') }}';
let activeCategoryId = {{ $selectedCategory?->id ?? 'null' }};

/* ── Navigation ─────────────────────── */
function selectCategory(id) {
    window.location.href = BASE + '/settings/articles?category=' + id;
}

/* ═══════════════ CATEGORY MODAL ═══════════════ */
function openCategoryModal() {
    const form = document.getElementById('catForm');
    form.reset();
    form.action = BASE + '/settings/articles/categories';
    document.getElementById('catMethodInput').value = 'POST';
    document.getElementById('catModalTitle').textContent = 'Nouvelle catégorie';
    document.getElementById('catImagePreview').classList.add('hidden');
    document.getElementById('catFileLabel').textContent = 'Choisir un fichier…';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function openEditCategoryModal(id, name, description, status, imageUrl) {
    const form = document.getElementById('catForm');
    form.reset();
    form.action = BASE + '/settings/articles/categories/' + id;
    document.getElementById('catMethodInput').value = 'PUT';
    document.getElementById('catModalTitle').textContent = 'Modifier : ' + name;
    document.getElementById('catName').value        = name;
    document.getElementById('catDescription').value = description;
    document.getElementById('catStatus').value      = status;
    document.getElementById('catFileLabel').textContent = 'Choisir un fichier…';

    const isUrl = imageUrl && imageUrl.startsWith('http');
    document.getElementById('catImageUrl').value = isUrl ? imageUrl : '';

    const preview = document.getElementById('catImagePreview');
    const img     = document.getElementById('catPreviewImg');
    if (isUrl) { img.src = imageUrl; preview.classList.remove('hidden'); }
    else       { preview.classList.add('hidden'); }

    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

/* ═══════════════ PRODUCT MODAL ═══════════════ */
function openProductModal() {
    if (!activeCategoryId) return;
    const form = document.getElementById('prodForm');
    form.reset();
    form.action = BASE + '/settings/articles/categories/' + activeCategoryId + '/products';
    document.getElementById('prodMethodInput').value = 'POST';
    document.getElementById('prodCategoryId').value  = activeCategoryId;
    document.getElementById('prodModalTitle').textContent = 'Nouvel article';
    document.getElementById('prodImagePreview').classList.add('hidden');
    document.getElementById('prodFileLabel').textContent = 'Choisir un fichier…';
    document.getElementById('productModal').classList.remove('hidden');
}

function openEditProductModal(id, categoryId, name, priceVente, priceAchat, stock, alertStock, unit, status, kitchenActive, imageUrl) {
    const form = document.getElementById('prodForm');
    form.reset();
    form.action = BASE + '/settings/articles/products/' + id;
    document.getElementById('prodMethodInput').value  = 'PUT';
    document.getElementById('prodCategoryId').value   = categoryId;
    document.getElementById('prodModalTitle').textContent = 'Modifier : ' + name;
    document.getElementById('prodName').value        = name;
    document.getElementById('prodPriceVente').value  = priceVente;
    document.getElementById('prodPriceAchat').value  = priceAchat || '';
    document.getElementById('prodStock').value       = stock;
    document.getElementById('prodAlertStock').value  = alertStock;
    document.getElementById('prodUnit').value        = unit;
    document.getElementById('prodStatus').value      = status;
    document.getElementById('prodKitchenActive').value = kitchenActive ? '1' : '0';
    document.getElementById('prodFileLabel').textContent = 'Choisir un fichier…';

    const isUrl = imageUrl && imageUrl.startsWith('http');
    document.getElementById('prodImageUrl').value = isUrl ? imageUrl : '';

    const preview = document.getElementById('prodImagePreview');
    const img     = document.getElementById('prodPreviewImg');
    if (isUrl) { img.src = imageUrl; preview.classList.remove('hidden'); }
    else       { preview.classList.add('hidden'); }

    document.getElementById('productModal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

/* ═══════════════ IMAGE PREVIEW LISTENERS ═══════════════ */
document.addEventListener('DOMContentLoaded', function () {

    // Category – URL preview
    document.getElementById('catImageUrl').addEventListener('input', function () {
        const val = this.value.trim();
        const preview = document.getElementById('catImagePreview');
        const img     = document.getElementById('catPreviewImg');
        if (val.startsWith('http')) { img.src = val; preview.classList.remove('hidden'); }
        else { preview.classList.add('hidden'); }
    });

    // Category – file picker label + preview
    document.getElementById('catFileInput').addEventListener('change', function () {
        const file = this.files[0];
        document.getElementById('catFileLabel').textContent = file ? file.name : 'Choisir un fichier…';
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('catPreviewImg').src = e.target.result;
                document.getElementById('catImagePreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Product – URL preview
    document.getElementById('prodImageUrl').addEventListener('input', function () {
        const val = this.value.trim();
        const preview = document.getElementById('prodImagePreview');
        const img     = document.getElementById('prodPreviewImg');
        if (val.startsWith('http')) { img.src = val; preview.classList.remove('hidden'); }
        else { preview.classList.add('hidden'); }
    });

    // Product – file picker label + preview
    document.getElementById('prodFileInput').addEventListener('change', function () {
        const file = this.files[0];
        document.getElementById('prodFileLabel').textContent = file ? file.name : 'Choisir un fichier…';
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('prodPreviewImg').src = e.target.result;
                document.getElementById('prodImagePreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    @if($selectedCategory)
    activeCategoryId = {{ $selectedCategory->id }};
    @endif
});
</script>
</x-layout.app>
