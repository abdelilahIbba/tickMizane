<x-layout.app title="Gestion des Permissions">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Gestion des Permissions</h1>
            <p class="text-gray-400 mt-1">Gérer les droits d'accès par utilisateur</p>
        </div>
        <a href="{{ route('settings.users.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
            Retour aux Utilisateurs
        </a>
    </div>

    <!-- Users List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($users as $user)
        <a href="{{ route('settings.permissions.show', $user) }}" 
           class="block bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 hover:border-blue-500 transition-all hover:shadow-xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex-shrink-0 bg-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-white">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-400">{{ $user->username }}</p>
                    <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold rounded-full
                        @if($user->role === 'admin') bg-purple-500/20 text-purple-400
                        @elseif($user->role === 'caissier') bg-blue-500/20 text-blue-400
                        @else bg-green-500/20 text-green-400
                        @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endforeach
    </div>

    @if($users->isEmpty())
    <div class="text-center py-12 bg-gray-900/50 rounded-lg border border-gray-800">
        <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-400">Aucun utilisateur</h3>
    </div>
    @endif
</div>
</x-layout.app>
