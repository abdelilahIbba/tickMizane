<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Commander — TechMizane</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        *{-webkit-tap-highlight-color:transparent;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#0f172a;color:#f1f5f9}
        .scrollbar-hide::-webkit-scrollbar{display:none}
        .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}
        @keyframes slideUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes pop{0%{transform:scale(.8);opacity:0}70%{transform:scale(1.1)}100%{transform:scale(1);opacity:1}}
        .slide-up{animation:slideUp .3s ease}
        .fade-in{animation:fadeIn .25s ease}
        .pop-in{animation:pop .3s ease}
        .card-hover{transition:transform .15s,box-shadow .15s}
        .card-hover:active{transform:scale(.96)}
    </style>
</head>
<body class="min-h-screen" x-data="pos()" x-init="init()">

{{-- ════════════════════════════════════════════
     STEP 1 — Location selection
════════════════════════════════════════════ --}}
<template x-if="step === 1">
<div class="min-h-screen flex flex-col items-center justify-center px-5 py-10 fade-in">

    <div class="text-center mb-10">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-500 flex items-center justify-center shadow-xl shadow-amber-500/30">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Bienvenue</h1>
        <p class="text-slate-400 mt-1 text-sm">Choisissez votre emplacement pour commencer</p>
    </div>

    <div class="w-full max-w-sm space-y-3">

        <button @click="setLocation('restaurant')"
                class="card-hover w-full bg-slate-800 border border-slate-700 hover:border-amber-500/50 rounded-2xl p-5 flex items-center gap-4 text-left transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500/25 transition-colors">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-white font-semibold">Restaurant</div>
                <div class="text-slate-400 text-xs mt-0.5">Commandez depuis votre table</div>
            </div>
            <svg class="w-5 h-5 text-slate-600 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <button @click="setLocation('pool')"
                class="card-hover w-full bg-slate-800 border border-slate-700 hover:border-sky-500/50 rounded-2xl p-5 flex items-center gap-4 text-left transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-sky-500/15 border border-sky-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-sky-500/25 transition-colors">
                <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 17c3-3 6 0 9 0s6-3 9 0M3 12c3-3 6 0 9 0s6-3 9 0M3 7h18"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-white font-semibold">Piscine</div>
                <div class="text-slate-400 text-xs mt-0.5">Commandez depuis la piscine</div>
            </div>
            <svg class="w-5 h-5 text-slate-600 group-hover:text-sky-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <button @click="setLocation('room')"
                class="card-hover w-full bg-slate-800 border border-slate-700 hover:border-purple-500/50 rounded-2xl p-5 flex items-center gap-4 text-left transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-purple-500/15 border border-purple-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-500/25 transition-colors">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-white font-semibold">Chambre d'hôtel</div>
                <div class="text-slate-400 text-xs mt-0.5">Room service — livraison en 2h max</div>
            </div>
            <svg class="w-5 h-5 text-slate-600 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

    </div>
</div>
</template>

{{-- ════════════════════════════════════════════
     STEP 2 — POS Menu
════════════════════════════════════════════ --}}
<template x-if="step === 2">
<div class="flex flex-col h-screen overflow-hidden fade-in">

    {{-- Top Header --}}
    <div class="flex-shrink-0 bg-slate-900 border-b border-slate-800 px-4 py-3 flex items-center gap-3">
        <button @click="step = 1" class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center hover:bg-slate-700 transition-colors">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="flex-1">
            <span class="text-white font-semibold text-sm" x-text="locationLabel"></span>
        </div>
        <template x-if="locationType === 'room'">
            <div class="flex items-center gap-1.5 bg-amber-500/15 border border-amber-500/25 rounded-xl px-3 py-1.5">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="text-amber-400 text-xs font-semibold">Livraison 2h max</span>
            </div>
        </template>
    </div>

    {{-- Category Tabs --}}
    <div class="flex-shrink-0 bg-slate-900 border-b border-slate-800 px-4 py-2">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-0.5">
            <button @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                    class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-semibold transition-all">
                Tout
            </button>
            <template x-for="cat in categories" :key="cat.id">
                <button @click="activeCategory = cat.id"
                        :class="activeCategory === cat.id ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white'"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap"
                        x-text="cat.name">
                </button>
            </template>
        </div>
    </div>

    {{-- Products Grid (scrollable) --}}
    <div class="flex-1 overflow-y-auto p-4" :class="cartCount > 0 ? 'pb-24' : ''">
        <template x-if="filteredProducts.length === 0">
            <div class="text-center py-16 text-slate-600">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <p class="text-sm">Aucun article disponible</p>
            </div>
        </template>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <template x-for="product in filteredProducts" :key="product.id">
                <div class="card-hover bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden cursor-pointer"
                     :class="getQty(product.id) > 0 ? 'border-amber-500/50 shadow-amber-500/10 shadow-lg' : ''"
                     @click="addToCart(product)">
                    <div class="relative">
                        <img :src="product.image" :alt="product.name"
                             class="w-full aspect-square object-cover"
                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80'">
                        <template x-if="getQty(product.id) > 0">
                            <div class="absolute top-2 right-2 w-7 h-7 bg-amber-500 rounded-full flex items-center justify-center text-white text-xs font-black shadow-lg pop-in"
                                 x-text="getQty(product.id)">
                            </div>
                        </template>
                    </div>
                    <div class="p-3">
                        <p class="text-white font-medium text-sm leading-tight line-clamp-2" x-text="product.name"></p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-amber-400 font-bold text-sm" x-text="fmt(product.price)"></p>
                            <template x-if="getQty(product.id) === 0">
                                <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center text-white font-bold text-lg leading-none shadow">+</div>
                            </template>
                            <template x-if="getQty(product.id) > 0">
                                <div class="flex items-center gap-1" @click.stop>
                                    <button @click="adjustQty(product.id, -1)"
                                            class="w-6 h-6 rounded-md bg-slate-700 border border-slate-600 flex items-center justify-center text-white text-sm font-bold hover:bg-slate-600 transition-colors">−</button>
                                    <span class="text-white font-bold text-sm w-4 text-center" x-text="getQty(product.id)"></span>
                                    <button @click="adjustQty(product.id, 1)"
                                            class="w-6 h-6 bg-amber-500 rounded-md flex items-center justify-center text-white text-sm font-bold hover:bg-amber-400 transition-colors">+</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Sticky Cart Bar --}}
    <template x-if="cartCount > 0">
        <div class="fixed bottom-0 left-0 right-0 z-40 p-4 bg-gradient-to-t from-slate-950 via-slate-950/95 to-transparent pt-8">
            <button @click="showCheckout = true"
                    class="card-hover w-full bg-amber-500 hover:bg-amber-400 rounded-2xl py-4 px-5 flex items-center justify-between font-bold shadow-2xl shadow-amber-500/30 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-600/40 rounded-xl px-2.5 py-1 text-sm font-black text-white" x-text="cartCount + ' article' + (cartCount > 1 ? 's' : '')"></div>
                    <span class="text-white text-base">Voir ma commande</span>
                </div>
                <span class="text-white font-black text-base" x-text="fmt(cartTotal)"></span>
            </button>
        </div>
    </template>

</div>
</template>

{{-- ════════════════════════════════════════════
     STEP 3 — Success
════════════════════════════════════════════ --}}
<template x-if="step === 3">
<div class="min-h-screen flex flex-col items-center justify-center px-6 text-center fade-in">

    <div class="w-20 h-20 rounded-full bg-green-500 flex items-center justify-center shadow-2xl shadow-green-500/30 mb-6">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-white">Commande envoyée !</h2>
    <p class="text-slate-400 text-sm mt-2">Votre commande a bien été reçue.</p>

    <div class="mt-3 px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl">
        <span class="text-amber-400 font-mono font-bold text-sm" x-text="'Commande n° ' + orderId"></span>
    </div>

    <template x-if="locationType === 'room'">
        <div class="mt-5 w-full max-w-sm bg-amber-500/10 border border-amber-500/25 rounded-2xl p-5">
            <div class="text-3xl mb-2">⏱</div>
            <div class="text-white font-bold">Délai de livraison</div>
            <div class="text-amber-400 font-black text-2xl mt-1">2 heures maximum</div>
            <div class="text-slate-500 text-xs mt-1">Votre commande sera livrée en chambre.</div>
        </div>
    </template>

    <template x-if="locationType === 'restaurant'">
        <div class="mt-5 w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl p-4 text-left">
            <div class="flex items-center gap-3 text-slate-300 text-sm">
                <span class="text-xl">🍽️</span>
                <span>Livraison à la <strong class="text-white" x-text="'Table n° ' + tableNumber"></strong></span>
            </div>
        </div>
    </template>

    <template x-if="locationType === 'pool'">
        <div class="mt-5 w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl p-4 text-left">
            <div class="flex items-center gap-3 text-slate-300 text-sm">
                <span class="text-xl">🏊</span>
                <span>Livraison à la <strong class="text-white">Piscine</strong></span>
            </div>
        </div>
    </template>

    <button @click="reset()" class="mt-6 w-full max-w-sm bg-slate-800 border border-slate-700 hover:bg-slate-700 rounded-2xl py-3.5 text-slate-300 font-semibold text-sm transition-colors">
        Passer une nouvelle commande
    </button>

</div>
</template>

{{-- ════════════════════════════════════════════
     CHECKOUT MODAL (slide up)
════════════════════════════════════════════ --}}
<template x-if="showCheckout">
<div class="fixed inset-0 z-50 flex flex-col justify-end">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCheckout = false"></div>

    {{-- Panel --}}
    <div class="relative bg-slate-900 rounded-t-3xl max-h-[90vh] flex flex-col slide-up shadow-2xl">

        {{-- Handle --}}
        <div class="flex-shrink-0 pt-3 pb-2 flex justify-center">
            <div class="w-10 h-1 bg-slate-700 rounded-full"></div>
        </div>

        {{-- Header --}}
        <div class="flex-shrink-0 px-5 pb-3 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-white font-bold text-base">Ma commande</h3>
                <p class="text-slate-500 text-xs" x-text="locationLabel"></p>
            </div>
            <button @click="showCheckout = false" class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable content --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 min-h-0">

            {{-- Cart items --}}
            <div class="space-y-2">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl p-3">
                        <img :src="item.image" class="w-12 h-12 rounded-xl object-cover bg-slate-700 flex-shrink-0"
                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=120&q=80'">
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate" x-text="item.name"></p>
                            <p class="text-slate-500 text-xs" x-text="fmt(item.price)"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="flex items-center gap-1" >
                                <button @click="adjustQty(item.id, -1)" class="w-6 h-6 rounded-md bg-slate-700 border border-slate-600 text-white text-sm font-bold flex items-center justify-center hover:bg-slate-600 transition-colors">−</button>
                                <span class="text-white font-bold text-sm w-4 text-center" x-text="item.qty"></span>
                                <button @click="adjustQty(item.id, 1)" class="w-6 h-6 bg-amber-500 rounded-md text-white text-sm font-bold flex items-center justify-center hover:bg-amber-400 transition-colors">+</button>
                            </div>
                            <span class="text-amber-400 font-bold text-sm w-16 text-right" x-text="fmt(item.price * item.qty)"></span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Total --}}
            <div class="flex justify-between items-center bg-slate-800/60 rounded-xl px-4 py-3 border border-slate-700/50">
                <span class="text-slate-400 font-semibold text-sm">Total</span>
                <span class="text-white font-black text-lg" x-text="fmt(cartTotal)"></span>
            </div>

            {{-- 2h delivery alert for rooms --}}
            <template x-if="locationType === 'room'">
                <div class="flex items-start gap-3 bg-amber-500/10 border border-amber-500/25 rounded-xl p-4">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <div>
                        <p class="text-amber-400 font-semibold text-sm">Livraison en chambre</p>
                        <p class="text-slate-400 text-xs mt-0.5">Votre commande sera livrée dans les <strong class="text-white">2 heures</strong> suivant votre confirmation.</p>
                    </div>
                </div>
            </template>

            {{-- Identifier fields --}}
            <div class="space-y-3 bg-slate-800 border border-slate-700 rounded-2xl p-4">
                <p class="text-slate-300 font-semibold text-sm">Informations de livraison</p>

                {{-- Restaurant: table number --}}
                <template x-if="locationType === 'restaurant'">
                    <div>
                        <label class="block text-slate-400 text-xs mb-1.5 font-medium">Numéro de table <span class="text-red-400">*</span></label>
                        <input x-model="tableNumber" type="number" inputmode="numeric" min="1" placeholder="Ex: 5"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-4 py-3 text-white font-bold outline-none transition-colors placeholder-slate-600 text-sm">
                    </div>
                </template>

                {{-- Pool: no input needed --}}
                <template x-if="locationType === 'pool'">
                    <div class="flex items-center gap-3 bg-sky-500/10 border border-sky-500/20 rounded-xl px-4 py-3 text-sky-400 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Votre commande sera livrée à la piscine.
                    </div>
                </template>

                {{-- Room: name + room number --}}
                <template x-if="locationType === 'room'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-slate-400 text-xs mb-1.5 font-medium">Votre nom <span class="text-red-400">*</span></label>
                            <input x-model="clientName" type="text" placeholder="Ex: Jean Dupont"
                                   class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-4 py-3 text-white font-bold outline-none transition-colors placeholder-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-slate-400 text-xs mb-1.5 font-medium">Numéro de chambre <span class="text-red-400">*</span></label>
                            <input x-model="roomNumber" type="text" inputmode="numeric" placeholder="Ex: 204"
                                   class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-4 py-3 text-white font-bold outline-none transition-colors placeholder-slate-600 text-sm">
                        </div>
                    </div>
                </template>

                {{-- Phone number (all types) --}}
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5 font-medium">Numéro de téléphone <span class="text-red-400">*</span></label>
                    <input x-model="phone" type="tel" inputmode="tel" placeholder="Ex: 06 12 34 56 78"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-4 py-3 text-white font-bold outline-none transition-colors placeholder-slate-600 text-sm">
                </div>

                {{-- Notes / description (all types) --}}
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5 font-medium">Remarques / instructions <span class="text-slate-600 font-normal">(optionnel)</span></label>
                    <textarea x-model="orderNotes" rows="2" placeholder="Ex: sans piment, allergie aux noix..."
                              class="w-full bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl px-4 py-3 text-white outline-none transition-colors placeholder-slate-600 text-sm resize-none"></textarea>
                </div>

                {{-- Validation error --}}
                <p x-show="validationError" x-text="validationError" class="text-red-400 text-xs font-medium"></p>
            </div>

        </div>

        {{-- Submit --}}
        <div class="flex-shrink-0 px-5 py-4 border-t border-slate-800 bg-slate-900">
            <button @click="submit()"
                    :disabled="loading"
                    class="card-hover w-full bg-amber-500 hover:bg-amber-400 disabled:opacity-60 rounded-2xl py-4 font-bold text-white text-base flex items-center justify-center gap-3 shadow-xl shadow-amber-500/20 transition-colors">
                <template x-if="loading">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </template>
                <template x-if="!loading">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                <span x-text="loading ? 'Envoi...' : 'Confirmer la commande'"></span>
            </button>
        </div>

    </div>
</div>
</template>

<script>
function pos() {
    return {
        step: 1,
        locationType: null,
        activeCategory: null,
        categories: @json($menuData),
        cart: [],
        showCheckout: false,
        tableNumber: '',
        roomNumber: '',
        clientName: '',
        phone: '',
        orderNotes: '',
        loading: false,
        orderId: null,
        validationError: '',

        init() {
            const p = new URLSearchParams(window.location.search);
            const t = p.get('type');
            if (['restaurant','pool','room'].includes(t)) { this.locationType = t; this.step = 2; }
            if (this.categories.length) this.activeCategory = this.categories[0].id;
        },

        setLocation(type) {
            this.locationType = type;
            if (this.categories.length) this.activeCategory = this.categories[0].id;
            this.step = 2;
        },

        get locationLabel() {
            return { restaurant:'Restaurant', pool:'Piscine', room:"Chambre d'hôtel" }[this.locationType] ?? '';
        },

        get filteredProducts() {
            if (!this.activeCategory) return this.categories.flatMap(c => c.products);
            return this.categories.find(c => c.id == this.activeCategory)?.products ?? [];
        },

        get cartCount() { return this.cart.reduce((s,i) => s + i.qty, 0); },
        get cartTotal() { return this.cart.reduce((s,i) => s + i.price * i.qty, 0); },

        addToCart(p) {
            const e = this.cart.find(i => i.id === p.id);
            if (e) { e.qty++; } else { this.cart.push({ id:p.id, name:p.name, price:p.price, qty:1, image:p.image }); }
        },

        adjustQty(id, d) {
            const i = this.cart.find(x => x.id === id);
            if (!i) return;
            i.qty = Math.max(0, i.qty + d);
            if (i.qty === 0) this.cart = this.cart.filter(x => x.id !== id);
        },

        getQty(id) { return this.cart.find(i => i.id === id)?.qty ?? 0; },

        fmt(n) { return new Intl.NumberFormat('fr-MA', { minimumFractionDigits:2 }).format(n) + ' MAD'; },

        validate() {
            this.validationError = '';
            if (!this.cart.length) { this.validationError = 'Votre panier est vide.'; return false; }
            if (this.locationType === 'restaurant' && !this.tableNumber.trim()) {
                this.validationError = 'Veuillez indiquer le numéro de table.'; return false;
            }
            if (this.locationType === 'room') {
                if (!this.clientName.trim()) { this.validationError = 'Veuillez entrer votre nom.'; return false; }
                if (!this.roomNumber.trim()) { this.validationError = 'Veuillez entrer votre numéro de chambre.'; return false; }
            }
            if (!this.phone.trim()) { this.validationError = 'Veuillez entrer votre numéro de téléphone.'; return false; }
            return true;
        },

        async submit() {
            if (!this.validate() || this.loading) return;
            this.loading = true;
            try {
                const r = await fetch('{{ route("client.order.submit") }}', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({
                        location_type: this.locationType,
                        table_number: this.tableNumber || null,
                        room_number: this.roomNumber || null,
                        client_name: this.clientName || null,
                        phone: this.phone || null,
                        order_notes: this.orderNotes || null,
                        items: this.cart.map(i => ({ id: i.id, qty: i.qty })),
                    }),
                });
                const d = await r.json();
                if (d.success) { this.orderId = d.order_id; this.showCheckout = false; this.step = 3; }
                else { this.validationError = d.message ?? 'Erreur. Réessayez.'; }
            } catch { this.validationError = 'Erreur réseau. Réessayez.'; }
            finally { this.loading = false; }
        },

        reset() {
            this.step = 1; this.locationType = null; this.cart = [];
            this.tableNumber = ''; this.roomNumber = ''; this.clientName = '';
            this.phone = ''; this.orderNotes = '';
            this.orderId = null; this.showCheckout = false; this.validationError = '';
        },
    };
}
</script>
</body>
</html>