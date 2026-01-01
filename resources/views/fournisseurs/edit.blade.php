<x-layout.app title="Modifier le fournisseur">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('fournisseurs.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux fournisseurs
            </a>
            <h1 class="text-3xl font-bold text-white">Modifier le fournisseur</h1>
            <p class="text-gray-400 mt-1">Modifier les informations du fournisseur</p>
        </div>
        
        {{-- Form Card --}}
        <x-ui.card>
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    Veuillez corriger les erreurs ci-dessous.
                </x-ui.alert>
            @endif
            
            <form action="{{ route('fournisseurs.update', $fournisseur ?? 1) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <x-form.input 
                    name="name" 
                    label="Nom de l'entreprise" 
                    placeholder="Ex: Boissons Maroc SARL"
                    value="{{ $fournisseur->name ?? 'Boissons Maroc SARL' }}"
                    required
                />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input 
                        type="tel" 
                        name="phone" 
                        label="Téléphone" 
                        placeholder="+212 5XX-XXXXXX"
                        value="{{ $fournisseur->phone ?? '+212 5XX-XXXXXX' }}"
                    />
                    
                    <x-form.input 
                        type="email" 
                        name="email" 
                        label="Email" 
                        placeholder="contact@entreprise.ma"
                        value="{{ $fournisseur->email ?? 'contact@boissons.ma' }}"
                    />
                </div>
                
                <x-form.textarea 
                    name="address" 
                    label="Adresse" 
                    placeholder="Adresse complète du fournisseur..."
                    value="{{ $fournisseur->address ?? 'Zone Industrielle, Casablanca' }}"
                    rows="3"
                />
                
                <x-form.textarea 
                    name="notes" 
                    label="Notes" 
                    placeholder="Notes ou informations supplémentaires..."
                    value="{{ $fournisseur->notes ?? '' }}"
                    rows="2"
                />
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                    <x-ui.button variant="secondary" href="{{ route('fournisseurs.index') }}">
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
