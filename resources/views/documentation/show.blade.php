<x-layout.app title="{{ $doc->title }}">
<div class="flex h-[calc(100vh-64px)] overflow-hidden bg-gray-900">
    <!-- Sidebar Navigation -->
    <aside class="w-80 bg-gray-800 shadow-xl overflow-y-auto hidden lg:block border-r border-gray-700 z-10 w-80">
        <div class="p-6 sticky top-0 bg-gray-800 z-20 border-b border-gray-700 backdrop-blur-sm bg-gray-800/95">
            <a href="{{ route('docs.index') }}" class="flex items-center text-gray-400 hover:text-white transition font-medium group">
                <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
                     <i class="fas fa-arrow-left text-sm"></i>
                </div>
                Retour au sommaire
            </a>
        </div>
        
        <nav class="p-4 space-y-8">
            @foreach($navDocs as $category => $docs)
                <div>
                    <h3 class="px-3 text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                        {{ ucfirst($category) }}
                    </h3>
                    <div class="space-y-1">
                        @foreach($docs as $navDoc)
                            <a href="{{ route('docs.show', $navDoc->slug) }}" 
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ $navDoc->id === $doc->id ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                                <i class="{{ $navDoc->icon ?? 'fas fa-circle text-[0.4rem]' }} w-6 text-center {{ $navDoc->id === $doc->id ? 'text-white' : 'text-gray-600 group-hover:text-gray-400' }}"></i>
                                {{ $navDoc->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto focus:outline-none scroll-smooth bg-gray-900">
        <div class="max-w-4xl mx-auto px-6 py-10 lg:px-12">
            
            <div class="lg:hidden mb-6">
                 <a href="{{ route('docs.index') }}" class="text-sm flex items-center text-gray-400 hover:text-white">
                    <i class="fas fa-arrow-left mr-2"></i> Sommaire
                </a>
            </div>

            <article class="prose prose-invert prose-lg max-w-none prose-headings:font-bold prose-a:text-blue-400 hover:prose-a:text-blue-300 prose-code:text-blue-300 prose-code:bg-gray-800 prose-code:px-1 prose-code:rounded">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-800 text-blue-400 border border-gray-700 capitalize">
                        {{ $doc->category }}
                    </span>
                </div>
                
                <h1 class="text-4xl font-extrabold text-white mb-8 tracking-tight">{{ $doc->title }}</h1>
                
                <div class="bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-700/50">
                     {!! $doc->content !!}
                </div>
            </article>

            <!-- Navigation Links at Bottom -->
            <div class="mt-12 border-t border-gray-800 pt-8 flex justify-between">
                <div>
                    <!-- Could implement prev/next logic here if desired later -->
                </div>
            </div>
        </div>
    </main>
</div>
</x-layout.app>
