<x-layout.app title="Modifier le produit">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('products.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux produits
            </a>
            <h1 class="text-3xl font-bold text-white">Modifier le produit</h1>
            <p class="text-gray-400 mt-1">Modifier les informations du produit</p>
        </div>
        
        {{-- Form Card --}}
        <x-ui.card>
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    Veuillez corriger les erreurs ci-dessous.
                </x-ui.alert>
            @endif
            
            <form action="{{ route('products.update', $product ?? 1) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <x-form.input 
                    name="name" 
                    label="Nom du produit" 
                    placeholder="Ex: Eau minérale 1.5L"
                    value="{{ $product->name ?? '' }}"
                    required
                />
                
                <x-form.select 
                    name="category_id" 
                    label="Catégorie"
                    :options="$categories->pluck('name', 'id')->toArray()"
                    selected="{{ $product->category_id ?? '' }}"
                    required
                />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input 
                        type="number" 
                        name="price_achat" 
                        label="Prix d'achat" 
                        placeholder="0.00"
                        value="{{ $product->price_achat ?? '' }}"
                        suffix="DH"
                    />
                    
                    <x-form.input 
                        type="number" 
                        name="price_vente" 
                        label="Prix de vente" 
                        placeholder="0.00"
                        value="{{ $product->price_vente ?? '' }}"
                        suffix="DH"
                        required
                    />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input 
                        type="number" 
                        name="stock_quantity" 
                        label="Stock actuel" 
                        placeholder="0"
                        value="{{ $product->stock_quantity ?? '0' }}"
                        disabled
                    />
                    
                    <x-form.input 
                        type="number" 
                        name="alert_stock" 
                        label="Seuil d'alerte" 
                        placeholder="10"
                        value="{{ $product->alert_stock ?? '10' }}"
                    />
                </div>
                
                <x-form.select 
                    name="unit" 
                    label="Unité"
                    :options="['pcs' => 'Pièce', 'kg' => 'Kilogramme', 'l' => 'Litre']"
                    selected="{{ $product->unit ?? 'pcs' }}"
                />
                
                <x-form.select 
                    name="status" 
                    label="Statut"
                    :options="['active' => 'Actif', 'inactive' => 'Inactif']"
                    selected="{{ $product->status ?? 'active' }}"
                />

                <x-form.select
                    name="kitchen_active"
                    label="Cuisine"
                    :options="['1' => 'Passe par la cuisine', '0' => 'Direct service']"
                    selected="{{ old('kitchen_active', isset($product) ? (int) $product->kitchen_active : 1) }}"
                />

                <x-form.input
                    type="url"
                    name="image_url"
                    label="URL de l'image (optionnel)"
                    placeholder="https://..."
                    :value="old('image_url', \Illuminate\Support\Str::startsWith((string) ($product->image ?? ''), ['http://', 'https://']) ? $product->image : '')"
                />

                <div class="mb-4">
                    <label for="image_file" class="block text-sm font-medium text-gray-400 mb-1">Image du produit</label>
                    @if(isset($product) && $product->image_url)
                        <div class="mb-3">
                            <img src="{{ $product->image_url }}" alt="Image du produit" class="h-20 w-20 object-cover rounded-lg border border-gray-700">
                        </div>
                    @endif
                    <input type="file" name="image_file" id="image_file" accept="image/*" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-amber-500 text-sm">
                    <p class="text-xs text-gray-500 mt-2">Vous pouvez uploader un nouveau fichier local ou remplacer via URL.</p>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                    <x-ui.button variant="secondary" href="{{ route('products.index') }}">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        Enregistrer les modifications
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layout.app>
