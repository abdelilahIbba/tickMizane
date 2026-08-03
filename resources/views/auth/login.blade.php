<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Connexion' }} - TechMizane Cash</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        
        /* Custom Scrollbar Hide */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="h-full bg-gray-950 text-white antialiased overflow-hidden">

    <div class="h-full w-full flex" x-data="{
        loading: false,
        focusField: '',
        pin: '',
        appendDigit(digit) {
            if (this.pin.length >= 10) return;
            this.pin += String(digit);
        },
        backspacePin() {
            this.pin = this.pin.slice(0, -1);
        },
        clearPin() {
            this.pin = '';
        },
        maskedPin() {
            return this.pin.length ? '●'.repeat(this.pin.length) : '••••';
        }
    }"
    @keydown.window="
        if ($event.key >= '0' && $event.key <= '9') { appendDigit($event.key); $event.preventDefault(); }
        else if ($event.key === 'Backspace') { backspacePin(); $event.preventDefault(); }
        else if ($event.key === 'Delete') { clearPin(); $event.preventDefault(); }
        else if ($event.key === 'Enter' && pin.length > 0 && !loading) { $refs.pinSubmit.click(); }
    ">
        
        <!-- Left Section: Visual & Brand (Hidden on mobile, 45% width on desktop) -->
        <div class="hidden lg:flex lg:w-[45%] relative flex-col justify-between p-12 overflow-hidden border-r border-gray-800 shadow-2xl bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <!-- Overlay to make text readable -->
            <div class="absolute inset-0 bg-gradient-to-b from-gray-900/90 via-gray-900/70 to-gray-950/95 z-0"></div>
            <div class="absolute inset-0 bg-amber-900/10 mix-blend-overlay z-0"></div>

            <!-- Header Brand -->
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-10 h-10 drop-shadow-xl shadow-amber-500/20" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 8C6 5.79086 7.79086 4 10 4H30C32.2091 4 34 5.79086 34 8V12C34 14.2091 32.2091 16 30 16H26V32C26 34.2091 24.2091 36 22 36H18C15.7909 36 14 34.2091 14 32V16H10C7.79086 16 6 14.2091 6 12V8Z" fill="url(#logo_grad_desktop)" />
                        <rect x="24" y="8" width="4" height="4" rx="1" fill="#000000" fill-opacity="0.2"/>
                        <defs>
                            <linearGradient id="logo_grad_desktop" x1="6" y1="4" x2="34" y2="36" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#fbbf24"/>
                                <stop offset="1" stop-color="#d97706"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="text-xl font-bold tracking-tight text-white drop-shadow-md">TechMizane</span>
                </div>
            </div>

            <!-- Central Visual -->
            <div class="relative z-10 flex-1 flex flex-col justify-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 backdrop-blur-md w-fit mb-6 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse shadow-[0_0_8px_rgba(251,191,36,0.8)]"></span>
                    <span class="text-xs font-semibold text-amber-100 tracking-wide uppercase">POS Restaurant</span>
                </div>

                <h1 class="text-5xl font-bold leading-tight mb-6 text-white drop-shadow-lg">
                    Le POS qui pilote <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-amber-500">votre restaurant en temps réel.</span>
                </h1>
                <p class="text-gray-200 text-lg max-w-md leading-relaxed font-medium drop-shadow-md">
                    Système de caisse POS conçu pour la restauration: gérez les commandes,
                    suivez la consommation de stock à chaque vente et coordonnez votre équipe sans friction.
                </p>
                
                <div class="mt-12 flex gap-4">
                    <div class="glass-panel bg-white/10 border-white/20 p-4 rounded-xl flex items-center gap-3 backdrop-blur-md shadow-lg">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></div>
                        <div>
                            <div class="text-xs text-gray-300 uppercase tracking-wider font-semibold">Stocks</div>
                            <div class="text-sm font-bold text-emerald-400 drop-shadow-sm">Consommation maîtrisée</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Copyright -->
            <div class="relative z-10 text-xs text-gray-400 font-medium">
                © 2026 TechMizane Solutions — All rights reserved. Produced by DevNApp Company.
            </div>
        </div>

        <!-- Right Section: Login Form (100% on mobile, 55% on desktop) -->
        <div class="w-full lg:w-[55%] bg-gray-950 relative flex flex-col justify-center px-8 sm:px-16 xl:px-32">
            
            <!-- Mobile Brand (Visible only small screens) -->
            <div class="lg:hidden absolute top-8 left-8">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 drop-shadow-xl shadow-amber-500/20" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 8C6 5.79086 7.79086 4 10 4H30C32.2091 4 34 5.79086 34 8V12C34 14.2091 32.2091 16 30 16H26V32C26 34.2091 24.2091 36 22 36H18C15.7909 36 14 34.2091 14 32V16H10C7.79086 16 6 14.2091 6 12V8Z" fill="url(#logo_grad_mobile)" />
                        <rect x="24" y="8" width="4" height="4" rx="1" fill="#000000" fill-opacity="0.2"/>
                        <defs>
                            <linearGradient id="logo_grad_mobile" x1="6" y1="4" x2="34" y2="36" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#fbbf24"/>
                                <stop offset="1" stop-color="#d97706"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <!-- Main Content Container -->
            <div class="w-full max-w-lg mx-auto">
                
                <!-- Welcome Text -->
                <div class="mb-10">
                    <h2 class="text-3xl font-semibold text-white mb-2">Bienvenue</h2>
                    <p class="text-gray-500">Veuillez vous identifier pour accéder au terminal.</p>
                </div>

                <!-- Single PIN login indicator (visual replacement for old mode selector) -->
                <div class="flex p-1 bg-gray-900/50 border border-gray-800 rounded-xl mb-10 w-fit">
                    <div class="px-6 py-2.5 rounded-lg text-sm font-medium bg-gray-800 text-white shadow-lg border border-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Connexion par PIN
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('error'))
                <div class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                @endif
                
                @if($errors->any())
                <div class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ $errors->first() }}</span>
                </div>
                @endif

                <!-- Form Area -->
                <div class="relative min-h-[460px] lg:min-h-[500px]">
                    
                    <!-- PIN FORM -->
                    <form 
                        method="POST" 
                        action="{{ route('login.submit') }}" 
                        @submit="loading = true"
                        class="w-full"
                    >
                        @csrf
                        
                        <div class="space-y-6">
                            <div class="group">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-3">Code d'accès (PIN)</label>
                                <div class="relative group-focus-within:text-amber-500 transition-colors duration-300">
                                    <input
                                        type="text"
                                        x-bind:value="maskedPin()"
                                        readonly
                                        inputmode="numeric"
                                        autocomplete="off"
                                        placeholder="••••"
                                             class="w-full bg-transparent border-b-2 border-gray-800 text-3xl lg:text-4xl font-light text-white pb-3 placeholder-gray-800
                                               focus:outline-none focus:border-amber-500 focus:placeholder-gray-700
                                                 transition-all duration-300 tracking-[0.45em]"
                                    >
                                    <input type="hidden" name="password" x-bind:value="pin">
                                    <div class="absolute right-0 top-0 h-full flex items-center">
                                        <svg class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-4 text-sm text-gray-600">Utilisez le pavé numérique tactile pour saisir votre code PIN.</p>
                            </div>

                            <div class="grid grid-cols-3 gap-3" role="group" aria-label="Pavé numérique">
                                <button type="button" @click="appendDigit(1)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">1</button>
                                <button type="button" @click="appendDigit(2)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">2</button>
                                <button type="button" @click="appendDigit(3)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">3</button>
                                <button type="button" @click="appendDigit(4)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">4</button>
                                <button type="button" @click="appendDigit(5)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">5</button>
                                <button type="button" @click="appendDigit(6)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">6</button>
                                <button type="button" @click="appendDigit(7)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">7</button>
                                <button type="button" @click="appendDigit(8)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">8</button>
                                <button type="button" @click="appendDigit(9)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">9</button>
                                <button type="button" @click="clearPin()" class="h-14 rounded-xl bg-red-500/10 border border-red-500/30 text-sm font-semibold text-red-400 hover:bg-red-500/20 active:scale-95 transition">Effacer</button>
                                <button type="button" @click="appendDigit(0)" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-xl font-semibold text-white hover:bg-gray-800 active:scale-95 transition">0</button>
                                <button type="button" @click="backspacePin()" class="h-14 rounded-xl bg-gray-900 border border-gray-800 text-sm font-semibold text-gray-300 hover:bg-gray-800 active:scale-95 transition">Suppr</button>
                            </div>

                            <button 
                                type="submit" 
                                x-ref="pinSubmit"
                                class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-xl text-black bg-white hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300 overflow-hidden"
                                x-bind:disabled="loading || pin.length === 0"
                                x-bind:class="(loading || pin.length === 0) ? 'opacity-50 cursor-not-allowed hover:bg-white' : ''"
                            >
                                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-amber-400 to-amber-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative flex items-center gap-2" x-show="!loading">
                                    Déverrouiller le système
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </span>
                                <span x-show="loading" class="relative flex items-center justify-center gap-2">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer Links -->
                <div class="mt-6 pt-6 border-t border-gray-900 flex justify-between items-center text-xs text-gray-600">
                    <a href="#" class="hover:text-amber-500 transition-colors">Besoin d'aide ?</a>
                </div>
            </div>
            
            <!-- Bottom decorative line -->
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-gray-950 via-amber-900/30 to-gray-950"></div>
        </div>
    </div>
</body>
</html>
