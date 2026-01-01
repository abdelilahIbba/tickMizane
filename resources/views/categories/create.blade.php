<x-layout.app title="Nouvelle catégorie">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux catégories
            </a>
            <h1 class="text-3xl font-bold text-white">Nouvelle catégorie</h1>
            <p class="text-gray-400 mt-1">Créer une nouvelle catégorie de produits</p>
        </div>
        
        {{-- Form Card --}}
        <x-ui.card>
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    Veuillez corriger les erreurs ci-dessous.
                </x-ui.alert>
            @endif
            
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <x-form.input 
                    name="name" 
                    label="Nom de la catégorie" 
                    placeholder="Ex: Boissons"
                    required
                />
                
                <x-form.textarea 
                    name="description" 
                    label="Description" 
                    placeholder="Description de la catégorie..."
                    rows="4"
                />
                
                <x-form.select 
                    name="status" 
                    label="Statut"
                    :options="['active' => 'Actif', 'archived' => 'Archivé']"
                    selected="active"
                />
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                    <x-ui.button variant="secondary" href="{{ route('categories.index') }}">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        Créer la catégorie
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layout.app>
