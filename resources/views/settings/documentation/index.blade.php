<x-layout.app title="Settings Documentation">
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestion de la Visibilité Documentation</h1>
            <p class="text-gray-600 mt-1">Gérez quels rôles peuvent voir quelles sections du guide.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Succès</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Titre</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Visible pour Admin</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Visible pour Caissier</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Visible pour Serveur</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($docs as $category => $items)
                        <tr class="bg-gray-50">
                            <td colspan="6" class="px-6 py-2 text-sm font-bold text-gray-700 uppercase bg-gray-100">
                                {{ ucfirst($category) }}
                            </td>
                        </tr>
                        @foreach($items as $doc)
                            <form action="{{ route('settings.documentation.updateVisibility', $doc) }}" method="POST">
                                @csrf
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                <i class="{{ $doc->icon ?? 'fas fa-file' }}"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $doc->title }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($doc->category) }}
                                        </span>
                                    </td>
                                    @php
                                        $roles = $doc->visible_to_roles ?? [];
                                    @endphp
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="checkbox" name="visible_to_roles[]" value="admin" 
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            {{ in_array('admin', $roles) ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="checkbox" name="visible_to_roles[]" value="caissier" 
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            {{ in_array('caissier', $roles) ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="checkbox" name="visible_to_roles[]" value="serveur" 
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            {{ in_array('serveur', $roles) ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded-md text-sm transition">
                                            Enregistrer
                                        </button>
                                    </td>
                                </tr>
                            </form>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Aucune documentation trouvée. Démarrez le seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layout.app>
