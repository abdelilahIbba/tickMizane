<x-layout.app title="Cuisine - Grand Écran" :sidebar="false">
    <div class="h-screen bg-gray-950 overflow-hidden flex flex-col" x-data="kitchenDisplay()">
        <!-- Header -->
        <div class="bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center shadow-md z-10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <span class="text-gray-900 font-bold text-xl">T</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-wide">Cuisine Display</h1>
                    <p class="text-gray-400 text-sm" x-text="'Mise à jour: ' + lastUpdate"></p>
                </div>
            </div>
            <div class="flex gap-4 items-center">
                <div class="text-right mr-4">
                    <p class="text-3xl font-mono mobile-clock text-blue-400" x-text="clock"></p>
                </div>
                <button @click="toggleAudio" class="p-3 rounded-full transition-colors" :class="audioEnabled ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400'">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="audioEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path x-show="!audioEnabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z m9.9 2.121l-7.07-7.07"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 p-4 grid grid-cols-3 gap-4 overflow-y-auto">
            <!-- En Cuisine (Pending) -->
            <div class="bg-gray-900/50 rounded-xl border border-orange-500/20 flex flex-col h-full">
                <div class="p-3 bg-orange-500/10 border-b border-orange-500/20 rounded-t-xl flex justify-between items-center">
                    <h2 class="text-xl font-bold text-orange-400">En Cuisine</h2>
                    <span class="px-3 py-1 bg-orange-500/20 text-orange-300 rounded-full font-mono font-bold" x-text="orders.filter(o => o.status === 'en_cuisine').length">0</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                    <template x-for="order in orders.filter(o => o.status === 'en_cuisine')" :key="order.id">
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 shadow-md transform transition-all hover:scale-[1.01]">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl font-bold text-white" x-text="'Cmd #' + order.id"></span>
                                <span class="text-lg font-mono text-gray-400" x-text="formatTime(order.created_at)"></span>
                            </div>
                            <div class="mb-3 flex justify-between items-center bg-gray-900/50 p-2 rounded">
                                <span class="text-xl font-bold text-blue-300" x-text="'Table ' + (order.table ? order.table.numero : 'N/A')"></span>
                                <span class="text-sm text-gray-500" x-text="getTimeElapsed(order.created_at)"></span>
                            </div>
                            <div class="space-y-2 text-lg">
                                <template x-for="detail in order.details.filter(detail => detail.produit && detail.produit.kitchen_active)" :key="detail.id">
                                    <div class="flex items-start">
                                        <span class="font-bold text-white w-8" x-text="detail.quantity + 'x'"></span>
                                        <div class="flex-1">
                                            <span class="text-gray-300" x-text="detail.produit.name"></span>
                                            <template x-if="detail.notes">
                                                <p class="text-sm text-yellow-400 italic" x-text="'📝 ' + detail.notes"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <template x-if="order.waiter_notes">
                                <div class="mt-3 p-2 bg-yellow-500/10 border border-yellow-500/20 rounded text-yellow-300 italic text-sm" x-text="'Note: ' + order.waiter_notes"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- En Préparation (In Progress) -->
            <div class="bg-gray-900/50 rounded-xl border border-blue-500/20 flex flex-col h-full">
                <div class="p-3 bg-blue-500/10 border-b border-blue-500/20 rounded-t-xl flex justify-between items-center">
                    <h2 class="text-xl font-bold text-blue-400">En Préparation</h2>
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full font-mono font-bold" x-text="orders.filter(o => o.status === 'en_preparation').length">0</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                    <template x-for="order in orders.filter(o => o.status === 'en_preparation')" :key="order.id">
                        <div class="bg-gray-800 rounded-lg p-4 border border-l-4 border-l-blue-500 border-gray-700 shadow-md">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl font-bold text-white" x-text="'Cmd #' + order.id"></span>
                                <span class="text-lg font-mono text-gray-400" x-text="formatTime(order.created_at)"></span>
                            </div>
                            <div class="mb-3 flex justify-between items-center bg-gray-900/50 p-2 rounded">
                                <span class="text-xl font-bold text-blue-300" x-text="'Table ' + (order.table ? order.table.numero : 'N/A')"></span>
                                <span class="text-sm text-blue-400" x-text="getTimeElapsed(order.validated_at || order.updated_at)"></span>
                            </div>
                            <div class="space-y-2 text-lg">
                                <template x-for="detail in order.details.filter(detail => detail.produit && detail.produit.kitchen_active)" :key="detail.id">
                                    <div class="flex items-start">
                                        <span class="font-bold text-white w-8" x-text="detail.quantity + 'x'"></span>
                                        <div class="flex-1">
                                            <span class="text-gray-300" x-text="detail.produit.name"></span>
                                            <template x-if="detail.notes">
                                                <p class="text-sm text-yellow-400 italic" x-text="'📝 ' + detail.notes"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Prêt (Ready) -->
            <div class="bg-gray-900/50 rounded-xl border border-emerald-500/20 flex flex-col h-full">
                <div class="p-3 bg-emerald-500/10 border-b border-emerald-500/20 rounded-t-xl flex justify-between items-center">
                    <h2 class="text-xl font-bold text-emerald-400">Prêt à servir</h2>
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full font-mono font-bold" x-text="orders.filter(o => o.status === 'pret').length">0</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                    <template x-for="order in orders.filter(o => o.status === 'pret')" :key="order.id">
                        <div class="bg-gray-800 rounded-lg p-4 border border-l-4 border-l-emerald-500 border-gray-700 shadow-md animate-pulse-slow">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-2xl font-bold text-white" x-text="'Cmd #' + order.id"></span>
                                <span class="text-lg font-mono text-emerald-400" x-text="formatTime(order.ready_at)"></span>
                            </div>
                            <div class="mb-3 flex justify-between items-center bg-gray-900/50 p-2 rounded">
                                <span class="text-xl font-bold text-emerald-300" x-text="'Table ' + (order.table ? order.table.numero : 'N/A')"></span>
                                <span class="text-sm text-emerald-500 font-bold">PRÊT!</span>
                            </div>
                            <div class="space-y-2 text-lg">
                                <template x-for="detail in order.details.filter(detail => detail.produit && detail.produit.kitchen_active)" :key="detail.id">
                                    <div class="flex items-start opacity-75">
                                        <span class="font-bold text-white w-8" x-text="detail.quantity + 'x'"></span>
                                        <div class="flex-1">
                                            <span class="text-gray-300" x-text="detail.produit.name"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kitchenDisplay() {
            return {
                orders: @json($orders),
                lastUpdate: new Date().toLocaleTimeString(),
                clock: new Date().toLocaleTimeString(),
                audioEnabled: false,
                announcedOrders: [],

                init() {
                    // Start clock
                    setInterval(() => {
                        this.clock = new Date().toLocaleTimeString();
                    }, 1000);

                    // Start polling
                    setInterval(() => {
                        this.fetchOrders();
                    }, 5000); // 5 seconds polling

                    // Initialize already announced orders to avoid noise on reload
                    this.announcedOrders = this.orders
                        .filter(o => o.status === 'pret')
                        .map(o => o.id);
                },

                toggleAudio() {
                    this.audioEnabled = !this.audioEnabled;
                    if (this.audioEnabled) {
                        this.speak("Audio activé");
                    }
                },

                async fetchOrders() {
                    try {
                        const response = await fetch('{{ route("kitchen.orders.active") }}');
                        const data = await response.json();
                        
                        // Check for new ready orders
                        this.checkNewReadyOrders(data.orders);
                        
                        this.orders = data.orders;
                        this.lastUpdate = new Date().toLocaleTimeString();
                    } catch (error) {
                        console.error('Error fetching orders:', error);
                    }
                },

                checkNewReadyOrders(newOrders) {
                    if (!this.audioEnabled) return;

                    const readyOrders = newOrders.filter(o => o.status === 'pret');
                    
                    readyOrders.forEach(order => {
                        if (!this.announcedOrders.includes(order.id)) {
                            this.announceOrder(order.id);
                            this.announcedOrders.push(order.id);
                        }
                    });
                },

                announceOrder(id) {
                    const text = `Commande numéro ${id} prête`;
                    // Repeat 5 times
                    for (let i = 0; i < 5; i++) {
                        setTimeout(() => this.speak(text), i * 1500); // 1.5s delay between repeats
                    }
                },

                speak(text) {
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'fr-FR';
                        utterance.rate = 1.0;
                        window.speechSynthesis.speak(utterance);
                    }
                },

                formatTime(dateString) {
                    if (!dateString) return '--:--';
                    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                },

                getTimeElapsed(dateString) {
                    if (!dateString) return '';
                    const start = new Date(dateString);
                    const now = new Date();
                    const diff = Math.floor((now - start) / 60000); // minutes
                    return diff + ' min';
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #111827; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151; 
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #4B5563; 
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.95; }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</x-layout.app>
