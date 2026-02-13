<x-layout.app title="Gestion des Utilisateurs">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Gestion des Utilisateurs</h1>
            <p class="text-gray-400 mt-1">Créer, modifier et gérer les comptes utilisateurs</p>
        </div>
        <a href="{{ route('settings.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvel Utilisateur
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead class="bg-gray-950/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Rôle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Dernière connexion</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 flex-shrink-0 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-400">{{ $user->username }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($user->role === 'admin') bg-purple-500/20 text-purple-400 border border-purple-500/30
                            @elseif($user->role === 'caissier') bg-blue-500/20 text-blue-400 border border-blue-500/30
                            @else bg-green-500/20 text-green-400 border border-green-500/30
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($user->status === 'active') bg-green-500/20 text-green-400 border border-green-500/30
                            @else bg-red-500/20 text-red-400 border border-red-500/30
                            @endif">
                            @if($user->status === 'active') Actif @else Bloqué @endif
                        </span>
                        @if($user->force_password_reset)
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                            Réinitialisation requise
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('settings.users.edit', $user) }}" class="text-blue-400 hover:text-blue-300" title="Modifier">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="{{ route('settings.permissions.show', $user) }}" class="text-purple-400 hover:text-purple-300" title="Permissions">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </a>
                            <a href="{{ route('settings.users.reset-password', $user) }}" class="text-yellow-400 hover:text-yellow-300" title="Réinitialiser mot de passe">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </a>
                            @if($user->status === 'active' && $user->id !== auth()->id())
                            <form action="{{ route('settings.users.deactivate', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-orange-400 hover:text-orange-300" title="Désactiver" onclick="return confirm('Désactiver cet utilisateur?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                            @elseif($user->status === 'blocked')
                            <form action="{{ route('settings.users.activate', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-400 hover:text-green-300" title="Activer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @if($user->id !== auth()->id())
                            <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-400">Aucun utilisateur</h3>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layout.app>
