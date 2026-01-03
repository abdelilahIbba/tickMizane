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
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Numéro/Nom de table *</label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="Ex: 01, A1, Terrasse 1..."
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Nombre de places *</label>
                    <select name="places" 
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            required>
                        @foreach($placesOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('places', 4) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('places')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Zone</label>
                    <select name="zone" 
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">-- Sélectionner une zone --</option>
                        @foreach($zones as $key => $label)
                            <option value="{{ $key }}" {{ old('zone') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('zone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Notes</label>
                    <textarea name="notes" 
                              rows="2"
                              placeholder="Notes supplémentaires (ex: près de la fenêtre, accessible PMR...)"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-amber-500 focus:border-transparent">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
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
