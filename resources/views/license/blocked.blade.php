<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Licence expirée — TechMizane</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 text-white flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-gray-900 border border-amber-700/50 rounded-2xl p-8 text-center shadow-xl">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>

        <p class="text-xs font-semibold uppercase tracking-wider text-amber-400 mb-2">Alerte licence</p>
        <h1 class="text-2xl font-bold mb-3">Système bloqué</h1>

        <p class="text-gray-300 mb-4 leading-relaxed">
            {{ $message }}
        </p>

        @if($expiredLicense)
            <div class="mb-6 rounded-lg border border-gray-800 bg-gray-950/60 px-4 py-3 text-left text-sm">
                <div class="flex justify-between gap-3 text-gray-400">
                    <span>Client</span>
                    <span class="text-white font-medium">{{ $expiredLicense->client_name }}</span>
                </div>
                <div class="mt-2 flex justify-between gap-3 text-gray-400">
                    <span>Expirée le</span>
                    <span class="text-amber-300">{{ $expiredLicense->expires_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @endif

        <div class="mb-6 rounded-lg border border-amber-700/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
            Pour continuer à utiliser TechMizane, contactez
            <span class="font-semibold text-amber-300">DevNApp</span>
            et réglez la prolongation de licence.
        </div>

        @if($user)
            <p class="text-sm text-gray-500 mb-6">
                Connecté en tant que {{ $user->name }}
                ({{ $user->role === 'admin' ? 'Super User' : $user->role }}).
            </p>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-white">
                Se déconnecter
            </button>
        </form>
    </div>
</body>
</html>
