<x-layout.app title="Documentation">
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-extrabold text-white mb-2">Guide d'utilisation techMizane</h1>
        <p class="text-gray-400 text-lg">Apprenez à maîtriser votre système de point de vente.</p>
    </div>

    @if($docs->isEmpty())
        <div class="bg-gray-800 border-l-4 border-yellow-500 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-yellow-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-200">
                        Aucune documentation n'est disponible pour votre rôle actuellement.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($docs as $category => $categoryDocs)
                <div class="bg-gray-800 rounded-xl shadow-sm hover:shadow-lg hover:shadow-blue-900/20 transition-all duration-300 overflow-hidden border border-gray-700">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 border-b border-gray-700">
                        <h2 class="text-xl font-bold text-white capitalize flex items-center">
                            @if($category == 'page') <i class="fas fa-desktop mr-2 opacity-80 text-blue-300"></i>
                            @elseif($category == 'workflow') <i class="fas fa-project-diagram mr-2 opacity-80 text-blue-300"></i>
                            @elseif($category == 'configuration') <i class="fas fa-cogs mr-2 opacity-80 text-blue-300"></i>
                            @elseif($category == 'role') <i class="fas fa-users mr-2 opacity-80 text-blue-300"></i>
                            @else <i class="fas fa-book mr-2 opacity-80 text-blue-300"></i>
                            @endif
                            {{ ucfirst($category) }}
                        </h2>
                    </div>
                    
                    <div class="p-0">
                        @foreach($categoryDocs as $doc)
                            <a href="{{ route('docs.show', $doc->slug) }}" 
                               class="group block px-6 py-4 border-b border-gray-700 last:border-b-0 hover:bg-gray-750 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-gray-700 text-blue-400 rounded-lg group-hover:bg-gray-600 transition-colors">
                                            <i class="{{ $doc->icon ?? 'fas fa-file-alt' }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-200 group-hover:text-white transition-colors">
                                                {{ $doc->title }}
                                            </h3>
                                        </div>

                                    </div>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layout.app>
