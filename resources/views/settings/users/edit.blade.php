<x-layout.app title="Modifier l'Utilisateur">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Modifier l'Utilisateur</h1>
            <p class="text-gray-400 mt-1">{{ $user->name }}</p>
        </div>
        <a href="{{ route('settings.users.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
            Retour
        </a>
    </div>

    <!-- Form -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6">
        <form action="{{ route('settings.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom d'utilisateur</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('username')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Rôle</label>
                <select name="role" required
                        class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="serveur" {{ old('role', $user->role) === 'serveur' ? 'selected' : '' }}>Serveur</option>
                    <option value="caissier" {{ old('role', $user->role) === 'caissier' ? 'selected' : '' }}>Caissier</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                <select name="status" required
                        class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="blocked" {{ old('status', $user->status) === 'blocked' ? 'selected' : '' }}>Bloqué</option>
                </select>
                @error('status')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('settings.users.index') }}" class="px-6 py-3 bg-gray-800 text-gray-300 font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
</x-layout.app>
