<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Connexion' }} - Techmizane Cash</title>
    
    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                    },
                    colors: {
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
<body class="h-full bg-black text-white antialiased overflow-hidden">

    <div class="h-full w-full flex" x-data="{ 
        loading: false, 
        loginMode: 'admin',
        focusField: '' 
    }">
        
        <!-- Left Section: Visual & Brand (Hidden on mobile, 45% width on desktop) -->
        <div class="hidden lg:flex lg:w-[45%] bg-[#050505] relative flex-col justify-between p-12 overflow-hidden border-r border-gray-900">
            <!-- Background Decorative Elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
                <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-[120px] animate-pulse-slow"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-amber-600/5 rounded-full blur-[100px]"></div>
                <!-- Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#333 1px, transparent 1px), linear-gradient(90deg, #333 1px, transparent 1px); background-size: 40px 40px;"></div>
            </div>

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
                    <span class="text-xl font-bold tracking-tight text-white/90">Techmizane</span>
                </div>
            </div>

            <!-- Central Visual / 3D Composition Placeholder -->
            <div class="relative z-10 flex-1 flex flex-col justify-center">
                <h1 class="text-5xl font-bold leading-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-500">
                    Gestion future <br>
                    <span class="text-amber-500">Intelligente.</span>
                </h1>
                <p class="text-gray-400 text-lg max-w-md leading-relaxed">
                    Plateforme de gestion centralisée nouvelle génération. 
                    Optimisez votre flux de travail restaurant avec une précision absolue.
                </p>
                
                <div class="mt-12 flex gap-4">
                    <div class="glass-panel p-4 rounded-xl flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Système</div>
                            <div class="text-sm font-medium text-emerald-400">Opérationnel</div>
                        </div>
                    </div>
                    <div class="glass-panel p-4 rounded-xl flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Version</div>
                            <div class="text-sm font-medium text-gray-300">v2.4.0-2026</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Copyright -->
            <div class="relative z-10 text-xs text-gray-600">
                &copy; {{ date('Y') }} Techmizane Solutions. All rights reserved.
            </div>
        </div>

        <!-- Right Section: Login Form (100% on mobile, 55% on desktop) -->
        <div class="w-full lg:w-[55%] bg-black relative flex flex-col justify-center px-8 sm:px-16 xl:px-32">
            
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

                <!-- Mode Selector (Tabs) -->
                <div class="flex p-1 bg-gray-900/50 border border-gray-800 rounded-xl mb-10 w-fit">
                    <button 
                        @click="loginMode = 'admin'"
                        :class="loginMode === 'admin' ? 'bg-gray-800 text-white shadow-lg border border-gray-700' : 'text-gray-500 hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Administrateur
                    </button>
                    <button 
                        @click="loginMode = 'staff'"
                        :class="loginMode === 'staff' ? 'bg-gray-800 text-white shadow-lg border border-gray-700' : 'text-gray-500 hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Personnel
                    </button>
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
                <div class="relative h-[320px]"> <!-- Fixed height container for stability -->
                    
                    <!-- ADMIN FORM -->
                    <form 
                        x-show="loginMode === 'admin'" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-4"
                        method="POST" 
                        action="{{ route('login.submit') }}" 
                        @submit="loading = true"
                        class="absolute inset-0"
                    >
                        @csrf
                        <input type="hidden" name="login_mode" value="admin">
                        
                        <div class="space-y-8">
                            <div class="group">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-3">Code d'accès</label>
                                <div class="relative group-focus-within:text-amber-500 transition-colors duration-300">
                                    <input
                                        type="password"
                                        name="password"
                                        required
                                        autofocus
                                        maxlength="10"
                                        placeholder="••••"
                                        class="w-full bg-transparent border-b-2 border-gray-800 text-4xl font-light text-white pb-3 placeholder-gray-800
                                               focus:outline-none focus:border-amber-500 focus:placeholder-gray-700
                                               transition-all duration-300 tracking-[1em]"
                                    >
                                    <div class="absolute right-0 top-0 h-full flex items-center">
                                        <svg class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-4 text-sm text-gray-600">Saisissez votre code PIN administrateur sécurisé.</p>
                            </div>

                            <button 
                                type="submit" 
                                class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-xl text-black bg-white hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300 overflow-hidden"
                                :disabled="loading"
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

                    <!-- STAFF FORM -->
                    <form 
                        x-show="loginMode === 'staff'" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-4"
                        method="POST" 
                        action="{{ route('login.submit') }}" 
                        @submit="loading = true"
                        class="absolute inset-0"
                        x-cloak
                    >
                        @csrf
                        <input type="hidden" name="login_mode" value="staff">
                        
                        <div class="space-y-6">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-2">Identifiant</label>
                                    <input
                                        type="text"
                                        name="username"
                                        required
                                        value="{{ old('username') }}"
                                        placeholder="user.name"
                                        class="w-full bg-gray-900/50 border border-gray-800 rounded-xl px-4 py-3.5 text-white placeholder-gray-600
                                               focus:outline-none focus:border-amber-500/50 focus:bg-gray-900 focus:ring-1 focus:ring-amber-500/50
                                               transition-all duration-300"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-2">Mot de passe</label>
                                    <input
                                        type="password"
                                        name="password"
                                        required
                                        placeholder="••••••••"
                                        class="w-full bg-gray-900/50 border border-gray-800 rounded-xl px-4 py-3.5 text-white placeholder-gray-600
                                               focus:outline-none focus:border-amber-500/50 focus:bg-gray-900 focus:ring-1 focus:ring-amber-500/50
                                               transition-all duration-300"
                                    >
                                </div>
                            </div>

                            <button 
                                type="submit" 
                                class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-xl text-black bg-white hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300 overflow-hidden mt-8"
                                :disabled="loading"
                            >
                                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-amber-400 to-amber-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative flex items-center gap-2" x-show="!loading">
                                    Connexion Session
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
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
                <div class="mt-8 pt-8 border-t border-gray-900 flex justify-between items-center text-xs text-gray-600">
                    <a href="#" class="hover:text-amber-500 transition-colors">Besoin d'aide ?</a>
                </div>
            </div>
            
            <!-- Bottom decorative line -->
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-black via-amber-900/20 to-black"></div>
        </div>
    </div>
</body>
</html>
