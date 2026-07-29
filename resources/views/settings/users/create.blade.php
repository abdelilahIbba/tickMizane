<x-layout.app title="Créer un Utilisateur">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Créer un Utilisateur</h1>
            <p class="text-gray-400 mt-1">Ajouter un nouvel utilisateur au système</p>
        </div>
        <a href="{{ route('settings.users.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
            Retour
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-700 bg-emerald-900/40 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-700 bg-red-900/40 px-4 py-3 text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6">
        <form action="{{ route('settings.users.store') }}" method="POST">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom complet</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom d'utilisateur</label>
                <input type="text" name="username" value="{{ old('username') }}" required
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
                    <option value="serveur" {{ old('role') === 'serveur' ? 'selected' : '' }}>Serveur</option>
                    <option value="caissier" {{ old('role') === 'caissier' ? 'selected' : '' }}>Caissier</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Mot de passe</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
                @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                <select name="status" required
                        class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Bloqué</option>
                </select>
                @error('status')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Force Password Reset -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="force_password_reset" value="1" {{ old('force_password_reset') ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 bg-gray-800 border-gray-700 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-300">Forcer le changement de mot de passe à la première connexion</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Créer l'utilisateur
                </button>
                <a href="{{ route('settings.users.index') }}" class="px-6 py-3 bg-gray-800 text-gray-300 font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
</x-layout.app>
