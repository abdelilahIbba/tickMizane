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
        
        {{-- Success Alert --}}
        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        
        {{-- Categories Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['ID', 'Nom', 'Description', 'Statut', 'Produits', 'Actions']">
                @foreach([
                    ['id' => 1, 'name' => 'Boissons', 'description' => 'Eau, jus, sodas et boissons chaudes', 'status' => 'active', 'count' => 24],
                    ['id' => 2, 'name' => 'Snacks', 'description' => 'Chips, biscuits et confiseries', 'status' => 'active', 'count' => 18],
                    ['id' => 3, 'name' => 'Épicerie', 'description' => 'Produits alimentaires de base', 'status' => 'active', 'count' => 42],
                    ['id' => 4, 'name' => 'Hygiène', 'description' => 'Produits d\'hygiène et soins', 'status' => 'archived', 'count' => 15],
                    ['id' => 5, 'name' => 'Électronique', 'description' => 'Accessoires et petits appareils', 'status' => 'active', 'count' => 8],
                ] as $cat)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-gray-300">#{{ $cat['id'] }}</td>
                        <td class="px-6 py-4 text-white font-medium">{{ $cat['name'] }}</td>
                        <td class="px-6 py-4 text-gray-400">{{ $cat['description'] }}</td>
                        <td class="px-6 py-4">
                            @if($cat['status'] === 'active')
                                <x-ui.badge variant="success">Actif</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Archivé</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $cat['count'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" size="sm" href="{{ route('categories.edit', $cat['id']) }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
