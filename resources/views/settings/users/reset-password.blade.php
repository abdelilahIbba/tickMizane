<x-layout.app title="Réinitialiser le Mot de Passe">
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Réinitialiser le Mot de Passe</h1>
            <p class="text-gray-400 mt-1">{{ $user->name }}</p>
        </div>
        <a href="{{ route('settings.users.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
            Retour
        </a>
    </div>

    <!-- Form -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6">
        <form action="{{ route('settings.users.reset-password.submit', $user) }}" method="POST">
            @csrf

            <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-400">Options de réinitialisation</h3>
                        <p class="mt-1 text-sm text-gray-300">
                            Vous pouvez soit définir un nouveau mot de passe, soit laisser vide pour générer un mot de passe temporaire automatique.
                        </p>
                    </div>
                </div>
            </div>

            <!-- New Password (Optional) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nouveau mot de passe (optionnel)</label>
                <input type="password" name="new_password" minlength="8"
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Laisser vide pour générer un mot de passe temporaire</p>
                @error('new_password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Confirmer le mot de passe</label>
                <input type="password" name="new_password_confirmation" minlength="8"
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Force Reset -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="force_reset" value="1" checked
                           class="w-4 h-4 text-blue-600 bg-gray-800 border-gray-700 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-300">Forcer l'utilisateur à changer son mot de passe à la prochaine connexion</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                    Réinitialiser le mot de passe
                </button>
                <a href="{{ route('settings.users.index') }}" class="px-6 py-3 bg-gray-800 text-gray-300 font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
</x-layout.app>
