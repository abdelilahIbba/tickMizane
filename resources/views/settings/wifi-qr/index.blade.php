<x-layout.app title="Wi-Fi & QR Codes — Commande client">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="wifiQrApp()">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/25 flex items-center justify-center text-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
            </span>
            Wi-Fi & Commande Client
        </h1>
        <p class="text-gray-400 mt-1 text-sm">Générez les QR codes Wi-Fi et les liens de commande pour vos clients</p>
    </div>

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-green-500/10 border border-green-500/25 rounded-xl text-green-400 text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         SECTION — Page de commande client
    ════════════════════════════════════════════════════ --}}
    <div class="mb-6 bg-gray-900 border border-gray-800 rounded-3xl p-6">
        <h2 class="text-white font-bold text-base flex items-center gap-2 mb-1">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Page de commande client
        </h2>
        <p class="text-gray-500 text-xs mb-5">Partagez ce lien ou ce QR code avec vos clients pour qu'ils puissent commander directement.</p>

        <div class="flex flex-col sm:flex-row gap-5 items-start">

            {{-- QR Code --}}
            <div class="flex-shrink-0 flex flex-col items-center gap-3">
                <div class="w-36 h-36 bg-white p-2.5 rounded-2xl shadow-lg">
                    <img :src="orderPageQr" class="w-full h-full object-contain" alt="QR page commande">
                </div>
                <a :href="orderPageQr" :download="'qr-commande.png'"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger le QR
                </a>
            </div>

            {{-- Link + location QRs --}}
            <div class="flex-1 space-y-3">
                <div>
                    <label class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Lien de la page de commande</label>
                    <div class="mt-1.5 flex items-center gap-2">
                        <div class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 font-mono text-xs text-gray-300 truncate" x-text="baseUrl + '/order'"></div>
                        <a :href="baseUrl + '/order'" target="_blank"
                           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 rounded-xl text-xs font-semibold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ouvrir
                        </a>
                    </div>
                </div>

                {{-- Per-location QR codes row --}}
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <template x-for="loc in locations" :key="loc.type">
                        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-3 flex flex-col items-center gap-2">
                            <span class="text-base" x-text="loc.emoji"></span>
                            <div class="w-16 h-16 bg-white p-1.5 rounded-lg shadow">
                                <img :src="orderQrUrl(loc.type)" class="w-full h-full object-contain">
                            </div>
                            <span class="text-gray-400 text-[10px] font-semibold text-center" x-text="loc.label"></span>
                            <a :href="orderQrUrl(loc.type)" :download="'qr-' + loc.type + '.png'"
                               class="text-[10px] text-gray-500 hover:text-amber-400 transition-colors">⬇ DL</a>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ═══════════════════════════════════════
             SECTION 1 — Wi-Fi QR Code Generator
        ═══════════════════════════════════════ --}}
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 space-y-5">
            <h2 class="text-white font-bold text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
                Paramètres Wi-Fi
            </h2>
            <p class="text-gray-500 text-xs -mt-3">Le QR code généré permet au client de se connecter automatiquement au réseau.</p>

            <form method="POST" action="{{ route('settings.wifi-qr.save') }}" class="space-y-4">
                @csrf
                {{-- SSID --}}
                <div>
                    <label class="block text-gray-400 text-xs font-semibold mb-1.5 uppercase tracking-wide">Nom du réseau (SSID)</label>
                    <input type="text" name="ssid" x-model="ssid"
                           value="{{ old('ssid', $wifi['ssid']) }}"
                           placeholder="Ex: Hotel_TechMizane"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl px-4 py-2.5 text-white text-sm outline-none transition-colors placeholder-gray-600">
                    @error('ssid') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-gray-400 text-xs font-semibold mb-1.5 uppercase tracking-wide">Mot de passe</label>
                    <div class="relative">
                        <input type="password" :type="showPass ? 'text' : 'password'" name="password" x-model="password"
                               value="{{ old('password', $wifi['password']) }}"
                               placeholder="Mot de passe Wi-Fi"
                               class="w-full bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl px-4 py-2.5 pr-11 text-white text-sm outline-none transition-colors placeholder-gray-600">
                        <button type="button" @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Security Type --}}
                <div>
                    <label class="block text-gray-400 text-xs font-semibold mb-1.5 uppercase tracking-wide">Type de sécurité</label>
                    <select name="security" x-model="security"
                            class="w-full bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl px-4 py-2.5 text-white text-sm outline-none transition-colors">
                        <option value="WPA" @selected(old('security', $wifi['security']) === 'WPA')>WPA / WPA2 (recommandé)</option>
                        <option value="WEP" @selected(old('security', $wifi['security']) === 'WEP')>WEP</option>
                        <option value="nopass" @selected(old('security', $wifi['security']) === 'nopass')>Aucun (réseau ouvert)</option>
                    </select>
                </div>

                <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-400 text-black font-bold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-lg shadow-amber-500/20">
                    Enregistrer les paramètres Wi-Fi
                </button>
            </form>

            {{-- Wi-Fi QR Preview --}}
            <div x-show="ssid.trim()" x-transition class="border-t border-gray-800 pt-5">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-3">Aperçu QR Wi-Fi</p>
                <div class="flex items-start gap-4">
                    <div class="w-28 h-28 bg-white p-2 rounded-2xl flex-shrink-0 shadow-lg">
                        <img :src="wifiQrUrl" class="w-full h-full object-contain" alt="QR Wi-Fi">
                    </div>
                    <div class="flex-1 text-xs text-gray-500 space-y-1">
                        <p class="font-mono break-all text-gray-600" x-text="wifiString"></p>
                        <p class="text-gray-600 mt-2">📱 Le client scanne ce code → son téléphone se connecte automatiquement au réseau Wi-Fi.</p>
                        <a :href="wifiQrUrl" :download="'wifi-qr-' + ssid + '.png'"
                           class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs font-semibold transition-colors">
                            ⬇ Télécharger
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             SECTION 2 — Ordering Page QR Codes
        ═══════════════════════════════════════ --}}
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 space-y-4">
            <h2 class="text-white font-bold text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                QR Codes — Page de commande
            </h2>
            <p class="text-gray-500 text-xs -mt-3">Placez ces QR codes à chaque emplacement. Le client scanne et accède directement à la page de commande.</p>

            <div class="space-y-4">
                {{-- Restaurant --}}
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-24 h-24 bg-white p-2 rounded-xl flex-shrink-0 shadow">
                        <img :src="orderQrUrl('restaurant')" class="w-full h-full object-contain" alt="QR Restaurant">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl">🍽️</span>
                            <span class="text-white font-bold text-sm">Restaurant</span>
                        </div>
                        <p class="text-gray-500 text-xs mb-1">Placé sur chaque table.<br>Le client entre son numéro de table.</p>
                        <p class="text-xs font-mono text-gray-700 truncate" x-text="baseUrl + '/order?type=restaurant'"></p>
                        <div class="flex gap-2 mt-2">
                            <a :href="orderQrUrl('restaurant')" :download="'qr-restaurant.png'"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-xs font-semibold transition-colors">
                                ⬇ DL
                            </a>
                            <a :href="baseUrl + '/order?type=restaurant'" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 rounded-lg text-xs font-semibold transition-colors">
                                🔗 Ouvrir
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Pool --}}
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-24 h-24 bg-white p-2 rounded-xl flex-shrink-0 shadow">
                        <img :src="orderQrUrl('pool')" class="w-full h-full object-contain" alt="QR Piscine">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl">🏊</span>
                            <span class="text-white font-bold text-sm">Piscine</span>
                        </div>
                        <p class="text-gray-500 text-xs mb-1">Placé près de la piscine.<br>Livraison directement à la piscine.</p>
                        <p class="text-xs font-mono text-gray-700 truncate" x-text="baseUrl + '/order?type=pool'"></p>
                        <div class="flex gap-2 mt-2">
                            <a :href="orderQrUrl('pool')" :download="'qr-piscine.png'"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-xs font-semibold transition-colors">
                                ⬇ DL
                            </a>
                            <a :href="baseUrl + '/order?type=pool'" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/20 rounded-lg text-xs font-semibold transition-colors">
                                🔗 Ouvrir
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Room --}}
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-24 h-24 bg-white p-2 rounded-xl flex-shrink-0 shadow">
                        <img :src="orderQrUrl('room')" class="w-full h-full object-contain" alt="QR Chambre">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl">🏨</span>
                            <span class="text-white font-bold text-sm">Chambre d'hôtel</span>
                        </div>
                        <p class="text-gray-500 text-xs mb-1">Placé dans chaque chambre.<br>Le client entre son numéro de chambre.</p>
                        <p class="text-xs font-mono text-gray-700 truncate" x-text="baseUrl + '/order?type=room'"></p>
                        <div class="flex gap-2 mt-2">
                            <a :href="orderQrUrl('room')" :download="'qr-chambre.png'"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-xs font-semibold transition-colors">
                                ⬇ DL
                            </a>
                            <a :href="baseUrl + '/order?type=room'" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-xs font-semibold transition-colors">
                                🔗 Ouvrir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         SECTION 3 — Combined Print Card
    ═══════════════════════════════════════ --}}
    <div class="mt-6 bg-gray-900 border border-gray-800 rounded-3xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-white font-bold text-base">Carte à imprimer pour les clients</h2>
                <p class="text-gray-500 text-xs mt-0.5">Impression combinée : Wi-Fi + lien de commande</p>
            </div>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-bold text-sm rounded-xl transition-colors shadow-lg shadow-amber-500/20 no-print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 print-area" id="print-cards">
            <template x-for="loc in locations" :key="loc.type">
                <div class="border-2 border-dashed border-gray-700 rounded-2xl p-5 text-center flex flex-col items-center gap-3 print-card">
                    <div class="text-3xl" x-text="loc.emoji"></div>
                    <div class="text-white font-extrabold text-sm" x-text="loc.label"></div>

                    {{-- Order QR --}}
                    <div>
                        <p class="text-gray-600 text-[10px] uppercase tracking-wider mb-1.5">📱 Scanner pour commander</p>
                        <div class="w-28 h-28 bg-white p-2 rounded-xl mx-auto shadow">
                            <img :src="orderQrUrl(loc.type)" class="w-full h-full object-contain">
                        </div>
                    </div>

                    <template x-if="ssid.trim()">
                        <div>
                            <p class="text-gray-600 text-[10px] uppercase tracking-wider mb-1.5 mt-1">📶 Wi-Fi</p>
                            <div class="w-20 h-20 bg-white p-1.5 rounded-lg mx-auto shadow">
                                <img :src="wifiQrUrl" class="w-full h-full object-contain">
                            </div>
                            <p class="text-gray-700 text-[9px] mt-1 font-mono" x-text="ssid"></p>
                        </div>
                    </template>

                    <p class="text-gray-600 text-[9px]" x-text="loc.label"></p>
                </div>
            </template>
        </div>
    </div>

</div>

<style>
@media print {
    body * { visibility: hidden !important; }
    #print-cards, #print-cards * { visibility: visible !important; }
    #print-cards { position: fixed; top: 0; left: 0; width: 100%; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1rem; background: white; }
    .print-card { border: 2px dashed #aaa !important; color: black !important; background: white; }
    .print-card * { color: black !important; }
    .no-print { display: none !important; }
}
</style>

<script>
function wifiQrApp() {
    return {
        ssid:     '{{ addslashes($wifi["ssid"]) }}',
        password: '{{ addslashes($wifi["password"]) }}',
        security: '{{ $wifi["security"] }}',
        showPass: false,
        baseUrl:  window.location.origin,
        locations: [
            { type: 'restaurant', emoji: '🍽️', label: 'Restaurant' },
            { type: 'pool',       emoji: '🏊',  label: 'Piscine'    },
            { type: 'room',       emoji: '🏨',  label: 'Chambre'    },
        ],

        get orderPageQr() {
            const url = encodeURIComponent(this.baseUrl + '/order');
            return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + url + '&bgcolor=ffffff&color=000000&margin=5';
        },

        get wifiString() {
            if (!this.ssid || !this.ssid.trim()) return '';
            const s = this.ssid.replace(/[;,:"\\]/g, function(c){ return '\\' + c; });
            const p = (this.password || '').replace(/[;,:"\\]/g, function(c){ return '\\' + c; });
            return 'WIFI:T:' + this.security + ';S:' + s + ';P:' + p + ';;';
        },

        get wifiQrUrl() {
            if (!this.ssid || !this.ssid.trim()) return '';
            return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(this.wifiString) + '&bgcolor=ffffff&color=000000&margin=5';
        },

        orderQrUrl(type) {
            const url = encodeURIComponent(this.baseUrl + '/order?type=' + type);
            return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + url + '&bgcolor=ffffff&color=000000&margin=5';
        },
    };
}
</script>
</x-layout.app>
