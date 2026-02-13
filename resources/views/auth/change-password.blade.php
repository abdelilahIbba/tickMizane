<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Changer le mot de passe - TechMizane</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-amber-500/25">
                    <span class="text-gray-900 font-bold text-3xl">T</span>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-white">
                    Changer le mot de passe
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    @if(auth()->user()->force_password_reset)
                        Vous devez définir un nouveau mot de passe pour continuer
                    @else
                        Définissez un nouveau mot de passe sécurisé
                    @endif
                </p>
            </div>

            <!-- Form -->
            <div class="bg-gray-900/50 backdrop-blur-sm rounded-xl border border-gray-800 shadow-lg p-8">
                <form method="POST" action="{{ route('password.change.submit') }}" class="space-y-6">
                    @csrf

                    @if(session('warning'))
                    <div class="p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                        <p class="text-yellow-400 text-sm">{{ session('warning') }}</p>
                    </div>
                    @endif

                    @if(!auth()->user()->force_password_reset)
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">
                            Mot de passe actuel
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        @error('current_password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                    <input type="hidden" name="current_password" value="skip">
                    @endif

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Nouveau mot de passe
                        </label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
                        @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                            Confirmer le mot de passe
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 text-gray-900 font-bold rounded-lg hover:from-amber-400 hover:to-amber-500 transition-all shadow-lg shadow-amber-500/25">
                        Changer le mot de passe
                    </button>
                </form>

                @if(!auth()->user()->force_password_reset)
                <div class="mt-4 text-center">
                    <a href="{{ url()->previous() }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                        Annuler
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
