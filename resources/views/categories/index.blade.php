<x-layout.app title="Catégories">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Catégories</h1>
                <p class="text-gray-400 mt-1">Gérez les catégories de produits</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('categories.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle catégorie
            </x-ui.button>
        </div>
        
        {{-- Filters --}}
        <form method="GET" action="{{ route('categories.index') }}" class="mb-6 flex flex-wrap gap-4">
            <x-form.input 
                type="text" 
                name="search" 
                placeholder="Rechercher une catégorie..."
                :value="request('search')"
                class="w-64"
            />
            <x-ui.button type="submit" variant="secondary">Rechercher</x-ui.button>
            @if(request('search'))
                <x-ui.button variant="ghost" href="{{ route('categories.index') }}">Réinitialiser</x-ui.button>
            @endif
        </form>
        
        {{-- Success/Error Alerts --}}
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif
        
        {{-- Categories Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['ID', 'Nom', 'Description', 'Statut', 'Produits', 'Actions']">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-gray-300">#{{ $category->id }}</td>
                        <td class="px-6 py-4 text-white font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-gray-400">{{ $category->description ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($category->status === 'active')
                                <x-ui.badge variant="success">Actif</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Archivé</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $category->produits_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" size="sm" href="{{ route('categories.edit', $category) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </x-ui.button>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm" type="submit">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <p>Aucune catégorie trouvée</p>
                            <x-ui.button variant="primary" href="{{ route('categories.create') }}" class="mt-4">
                                Créer une catégorie
                            </x-ui.button>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
            
            {{-- Pagination --}}
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $categories->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layout.app>
