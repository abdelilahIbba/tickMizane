<x-layout.guest title="Connexion">
    <div class="w-full max-w-md" x-data="{ loading: false, loginMode: 'admin' }">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl shadow-2xl shadow-amber-500/30 mb-4">
                <span class="text-gray-900 font-bold text-4xl">T</span>
            </div>
            <h1 class="text-3xl font-bold text-white">Techmizane <span class="text-amber-400">Cash</span></h1>
            <p class="text-gray-400 mt-2">Système de gestion de caisse</p>
        </div>
        
        {{-- Login Card --}}
        <div class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-white text-center mb-6">Connexion</h2>
            
            {{-- Mode Toggle --}}
            <div class="flex gap-2 mb-6 p-1 bg-gray-900 rounded-xl">
                <button 
                    type="button"
                    @click="loginMode = 'admin'"
                    :class="loginMode === 'admin' ? 'bg-amber-500 text-gray-900' : 'text-gray-400 hover:text-white'"
                    class="flex-1 py-3 px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Admin
                </button>
                <button 
                    type="button"
                    @click="loginMode = 'staff'"
                    :class="loginMode === 'staff' ? 'bg-amber-500 text-gray-900' : 'text-gray-400 hover:text-white'"
                    class="flex-1 py-3 px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personnel
                </button>
            </div>
            
            {{-- Error Alert --}}
            @if(session('error'))
                <x-ui.alert type="error" class="mb-6">
                    {{ session('error') }}
                </x-ui.alert>
            @endif
            
            @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    {{ $errors->first() }}
                </x-ui.alert>
            @endif
            
            {{-- Admin Login Form (PIN Only) --}}
            <form 
                x-show="loginMode === 'admin'" 
                x-transition
                method="POST" 
                action="{{ route('login.submit') }}" 
                @submit="loading = true"
            >
                @csrf
                <input type="hidden" name="login_mode" value="admin">
                
                {{-- PIN Input --}}
                <div class="space-y-2 mb-6">
                    <label for="admin_password" class="block text-sm font-medium text-gray-300">
                        Code PIN Administrateur
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="admin_password"
                            placeholder="Entrez le code PIN"
                            required
                            autofocus
                            maxlength="10"
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-4 text-white text-lg text-center tracking-widest placeholder-gray-500 
                                   focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent
                                   transition-all duration-200"
                        >
                        <button 
                            type="button"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-white"
                            @click="$el.previousElementSibling.type = $el.previousElementSibling.type === 'password' ? 'text' : 'password'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 text-center">Accès complet au système</p>
                </div>
                
                {{-- Submit Button --}}
                <x-ui.button 
                    type="submit" 
                    variant="primary" 
                    size="lg" 
                    class="w-full"
                    ::disabled="loading"
                >
                    <span x-show="!loading">Accès Administrateur</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Connexion...
                    </span>
                </x-ui.button>
            </form>
            
            {{-- Staff Login Form (Username + Password) --}}
            <form 
                x-show="loginMode === 'staff'" 
                x-transition
                method="POST" 
                action="{{ route('login.submit') }}" 
                @submit="loading = true"
            >
                @csrf
                <input type="hidden" name="login_mode" value="staff">
                
                {{-- Username Input --}}
                <div class="space-y-2 mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-300">
                        Nom d'utilisateur
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Entrez votre nom d'utilisateur"
                        required
                        value="{{ old('username') }}"
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-4 text-white text-lg placeholder-gray-500 
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent
                               transition-all duration-200"
                    >
                </div>
                
                {{-- Password Input --}}
                <div class="space-y-2 mb-6">
                    <label for="staff_password" class="block text-sm font-medium text-gray-300">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="staff_password"
                            placeholder="Entrez votre mot de passe"
                            required
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-4 text-white text-lg placeholder-gray-500 
                                   focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent
                                   transition-all duration-200"
                        >
                        <button 
                            type="button"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-white"
                            @click="$el.previousElementSibling.type = $el.previousElementSibling.type === 'password' ? 'text' : 'password'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Submit Button --}}
                <x-ui.button 
                    type="submit" 
                    variant="primary" 
                    size="lg" 
                    class="w-full"
                    ::disabled="loading"
                >
                    <span x-show="!loading">Se connecter</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Connexion...
                    </span>
                </x-ui.button>
            </form>
            
            {{-- Role Info --}}
            <div class="mt-8 pt-6 border-t border-gray-700">
                <p class="text-sm text-gray-400 text-center mb-4">Types de comptes:</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-200 font-medium block">Caissier</span>
                                <span class="text-xs text-gray-500">POS, Ventes, Paiements</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-emerald-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-gray-200 font-medium block">Serveur</span>
                                <span class="text-xs text-gray-500">Gestion des tables</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Footer --}}
        <p class="text-center text-sm text-gray-500 mt-8">
            © {{ date('Y') }} Techmizane Cash
        </p>
    </div>
</x-layout.guest>
