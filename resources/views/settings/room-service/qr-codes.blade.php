<x-layout.app title="Codes QR — Service de chambre">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="qrCodesApp()">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 no-print">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('settings.room-service.index') }}"
                   class="p-2 rounded-lg bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-white">Codes QR (كود باك)</h1>
            </div>
            <p class="text-gray-400 mt-1">Générez, téléchargez et imprimez les cartes QR pour chaque chambre d'hôtel</p>
        </div>
        <button @click="printAllCards()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-semibold rounded-xl transition-colors shadow-lg shadow-amber-500/25 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer tous les codes (طباعة الكل)
        </button>
    </div>

    {{-- ── Grid Rooms ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 no-print">
        <template x-for="room in rooms" :key="room.id">
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 flex flex-col items-center justify-between text-center hover:border-amber-500/30 transition-all duration-200 group">
                
                <span class="text-xs text-gray-500 uppercase tracking-widest font-semibold">Chambre d'hôtel</span>
                <h3 class="text-2xl font-extrabold text-white mt-1 mb-4" x-text="'غرفة ' + room.number"></h3>
                
                <!-- QR Image Wrapper with border -->
                <div class="w-36 h-36 bg-white p-2.5 rounded-2xl flex items-center justify-center shadow-lg shadow-black/40 group-hover:scale-105 transition-all duration-300">
                    <img :src="getQrCodeUrl(room)" 
                         alt="QR Code" 
                         class="w-full h-full object-contain">
                </div>
                
                <span class="text-[9px] font-mono text-gray-600 mt-2.5 break-all max-w-[160px] truncate" x-text="getMenuUrl(room)"></span>

                <div class="grid grid-cols-2 gap-2 mt-5 w-full">
                    <button @click="downloadQrCode(room)"
                            class="py-2 px-3 border border-gray-800 bg-gray-950/60 hover:bg-gray-800 text-gray-300 hover:text-white rounded-xl text-[10px] font-bold transition-all flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Télécharger
                    </button>
                    <button @click="printSingleCard(room)"
                            class="py-2 px-3 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-black border border-amber-500/20 hover:border-transparent rounded-xl text-[10px] font-bold transition-all flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimer
                    </button>
                </div>

            </div>
        </template>
    </div>

    {{-- ================= PRINT LAYOUT (Hidden on Screen, Visible on Print) ================= --}}
    <div id="print-area" class="hidden print-block font-sans text-black" dir="rtl">
        <!-- Single Card Print Template -->
        <template x-if="printMode === 'single' && activePrintRoom">
            <div class="print-card-wrapper flex flex-col items-center justify-between border-4 border-double border-amber-600 rounded-3xl p-8 text-center mx-auto" style="width: 10.5cm; height: 16cm; page-break-inside: avoid; background-color: #fff;">
                <div>
                    <!-- Luxury Banner -->
                    <h2 class="text-xl font-extrabold tracking-wide text-amber-700">قصر ميزان للضيافة</h2>
                    <p class="text-[10px] font-bold tracking-widest text-gray-500 uppercase mt-0.5" style="font-family: 'Outfit';">Mizane Palace Hotel</p>
                </div>

                <div class="my-4 border-t border-b border-amber-600/20 py-2 w-full">
                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">خدمة الغرف المباشرة</span>
                    <h1 class="text-2xl font-black text-black mt-1" x-text="'غرفة رقم ' + activePrintRoom.number"></h1>
                </div>

                <!-- QR Code image -->
                <div class="w-48 h-48 border-2 border-amber-600/30 p-2.5 rounded-2xl flex items-center justify-center bg-white shadow-md">
                    <img :src="getQrCodeUrl(activePrintRoom, 300)" class="w-full h-full object-contain">
                </div>

                <div class="mt-4">
                    <p class="text-xs font-bold text-gray-800">امسح الكود لطلب الطعام والشراب مباشرة لغرفتك</p>
                    <p class="text-[9px] text-gray-500 mt-1" style="font-family: 'Outfit';">Scan the code to order food & drinks directly to your room.</p>
                </div>
            </div>
        </template>

        <!-- All Cards Print Template -->
        <template x-if="printMode === 'all'">
            <div class="grid grid-cols-2 gap-x-6 gap-y-12 p-4">
                <template x-for="room in rooms" :key="room.id">
                    <div class="print-card-wrapper flex flex-col items-center justify-between border-4 border-double border-amber-600 rounded-3xl p-8 text-center mx-auto" style="width: 10.5cm; height: 16cm; page-break-inside: avoid; background-color: #fff;">
                        <div>
                            <h2 class="text-xl font-extrabold tracking-wide text-amber-700">قصر ميزان للضيافة</h2>
                            <p class="text-[10px] font-bold tracking-widest text-gray-500 uppercase mt-0.5" style="font-family: 'Outfit';">Mizane Palace Hotel</p>
                        </div>

                        <div class="my-4 border-t border-b border-amber-600/20 py-2 w-full">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">خدمة الغرف المباشرة</span>
                            <h1 class="text-2xl font-black text-black mt-1" x-text="'غرفة رقم ' + room.number"></h1>
                        </div>

                        <!-- QR Code image -->
                        <div class="w-48 h-48 border-2 border-amber-600/30 p-2.5 rounded-2xl flex items-center justify-center bg-white shadow-md">
                            <img :src="getQrCodeUrl(room, 300)" class="w-full h-full object-contain">
                        </div>

                        <div class="mt-4">
                            <p class="text-xs font-bold text-gray-800">امسح الكود لطلب الطعام والشراب مباشرة لغرفتك</p>
                            <p class="text-[9px] text-gray-500 mt-1" style="font-family: 'Outfit';">Scan the code to order food & drinks directly to your room.</p>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

</div>

@push('styles')
<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        .no-print {
            display: none !important;
        }
        .print-block {
            display: block !important;
        }
        main {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        /* Hide navbar/sidebar in print */
        nav, header, sidebar, footer {
            display: none !important;
        }
        .print-card-wrapper {
            box-shadow: none !important;
            break-inside: avoid;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function qrCodesApp() {
        return {
            rooms: @json($rooms),
            printMode: 'all', // single or all
            activePrintRoom: null,

            getMenuUrl(room) {
                // Generate menu link pointing to the guest room service menu route
                const baseUrl = window.location.origin;
                return `${baseUrl}/room-service/menu?room=${room.number}&token=${room.token}`;
            },

            getQrCodeUrl(room, size = 200) {
                const url = encodeURIComponent(this.getMenuUrl(room));
                return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${url}`;
            },

            downloadQrCode(room) {
                const url = this.getQrCodeUrl(room, 400);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                // Trigger download via open or direct link
                window.open(url, '_blank');
            },

            printSingleCard(room) {
                this.printMode = 'single';
                this.activePrintRoom = room;
                this.$nextTick(() => {
                    window.print();
                });
            },

            printAllCards() {
                this.printMode = 'all';
                this.activePrintRoom = null;
                this.$nextTick(() => {
                    window.print();
                });
            }
        }
    }
</script>
@endpush
</x-layout.app>
