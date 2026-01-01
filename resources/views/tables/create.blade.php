<x-layout.app title="Nouvelle table">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('tables.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux tables
            </a>
            <h1 class="text-3xl font-bold text-white">Nouvelle table</h1>
            <p class="text-gray-400 mt-1">Ajouter une nouvelle table au restaurant</p>
        </div>
        
        {{-- Form Card --}}
        <x-ui.card>
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    Veuillez corriger les erreurs ci-dessous.
                </x-ui.alert>
            @endif
            
            <form action="{{ route('tables.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <x-form.input 
                    name="number" 
                    label="Numéro de table" 
                    placeholder="Ex: 13"
                    required
                />
                
                <x-form.select 
                    name="seats" 
                    label="Nombre de places"
                    :options="[
                        '2' => '2 places',
                        '4' => '4 places',
                        '6' => '6 places',
                        '8' => '8 places',
                        '10' => '10 places',
                        '12' => '12 places',
                    ]"
                    required
                />
                
                <x-form.select 
                    name="zone" 
                    label="Zone"
                    :options="[
                        'interieur' => 'Intérieur',
                        'terrasse' => 'Terrasse',
                        'salon' => 'Salon privé',
                    ]"
                />
                
                <x-form.textarea 
                    name="notes" 
                    label="Notes" 
                    placeholder="Notes supplémentaires (ex: près de la fenêtre, accessible PMR...)"
                    rows="2"
                />
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                    <x-ui.button variant="secondary" href="{{ route('tables.index') }}">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        Créer la table
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layout.app>
