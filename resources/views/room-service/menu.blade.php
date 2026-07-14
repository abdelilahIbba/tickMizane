<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Room Service - خدمة الغرف</title>
    
    <!-- Google Fonts: Tajawal for Arabic, Outfit for French/English numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', 'Outfit', 'sans-serif'],
                    },
                    colors: {
                        gold: {
                            50: '#fdfbf7',
                            100: '#fbf7ed',
                            200: '#f5e9d2',
                            300: '#edd5ab',
                            400: '#dcab62',
                            500: '#cfa054', // Brand Gold
                            600: '#b48641',
                            700: '#966d35',
                            800: '#78562c',
                            900: '#634727',
                        },
                        dark: {
                            900: '#070a0f',
                            800: '#0d131f',
                            700: '#151e30',
                            600: '#1f2c44',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Tajawal', 'Outfit', sans-serif;
            background-color: #070a0f;
            -webkit-tap-highlight-color: rgba(207, 160, 84, 0.1);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: #070a0f;
        }
        ::-webkit-scrollbar-thumb {
            background: #1f2c44;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #cfa054;
        }
        .glass-card {
            background: rgba(21, 30, 48, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .active-tab-glow {
            box-shadow: 0 0 15px rgba(207, 160, 84, 0.25);
        }
    </style>
</head>
<body class="h-full text-gray-200 selection:bg-gold-500/30 selection:text-gold-200" 
      x-data="roomServiceApp()" 
      x-init="initData()">

    <!-- Main Container -->
    <div class="min-h-full flex flex-col justify-between max-w-5xl mx-auto relative shadow-2xl bg-dark-900 border-x border-gray-800/40">
        
        <!-- ================= WELCOME ROOM SELECTION ================= -->
        <template x-if="!room">
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 text-center min-h-screen">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center shadow-2xl shadow-gold-500/25 mb-6 border border-gold-300/30">
                    <svg class="w-10 h-10 text-dark-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                </div>
                
                <h1 class="text-2xl font-extrabold text-white mb-1">قصر ميزان للضيافة</h1>
                <p class="text-gold-400 text-xs font-semibold tracking-widest uppercase mb-8">Mizane Palace Room Service</p>
                
                <div class="w-full max-w-md glass-card rounded-3xl p-6 border border-gold-500/10">
                    <h2 class="text-base font-bold text-white mb-4">الرجاء تحديد غرفتك للطلب:</h2>
                    
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-60 overflow-y-auto p-1">
                        <template x-for="r in rooms" :key="r.id">
                            <button @click="selectRoom(r)"
                                    class="py-3 px-2 rounded-xl bg-dark-800 border border-gray-800 text-white font-bold hover:border-gold-500 hover:bg-gold-500/10 transition-all text-xs">
                                غرفة <span class="font-mono text-sm" x-text="r.number"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- ================= MAIN ROOM SERVICE CONTEXT ================= -->
        <template x-if="room">
            <div class="flex-1 flex flex-col h-full overflow-hidden">
                
                <!-- TOP HEADER -->
                <div class="px-6 py-4 bg-dark-800/90 border-b border-gray-800/60 flex items-center justify-between sticky top-0 z-20 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center shadow-lg shadow-gold-500/15">
                            <svg class="w-5 h-5 text-dark-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-sm font-extrabold text-white leading-tight">طلب خدمة الغرف</h1>
                            <p class="text-xs text-gold-400 font-semibold">غرفة رقم <span class="font-mono text-sm" x-text="room.number"></span></p>
                        </div>
                    </div>
                    
                    <button @click="room = null; cart = []" 
                            class="py-1.5 px-3 rounded-lg bg-dark-700 text-gray-400 hover:text-red-400 transition-all text-[11px] border border-gray-800">
                        تغيير الغرفة
                    </button>
                </div>

                <!-- GUEST MANAGEMENT BAR (شريط النزلاء) -->
                <div class="px-6 py-3 bg-dark-900/90 border-b border-gray-800/40 flex-shrink-0">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">النزلاء في الغرفة (اضغط للتبديل والطلب باسم الشخص):</p>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                        <!-- Guest Tabs -->
                        <template x-for="(gName, index) in guestList" :key="index">
                            <div class="flex items-center flex-shrink-0">
                                <button @click="activeGuest = gName"
                                        :class="activeGuest === gName ? 'bg-gold-500 text-dark-900 font-bold active-tab-glow border-gold-500' : 'bg-dark-800 text-gray-400 hover:text-white border-gray-800'"
                                        class="px-4 py-2 rounded-xl text-xs border transition-all flex items-center gap-1.5 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="activeGuest === gName ? 'bg-dark-900' : 'bg-gray-500'"></span>
                                    <span x-text="gName"></span>
                                    
                                    <!-- count items in cart for this guest -->
                                    <span x-show="countGuestItems(gName) > 0" 
                                          :class="activeGuest === gName ? 'bg-dark-900 text-white' : 'bg-gold-500/20 text-gold-400'"
                                          class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" x-text="countGuestItems(gName)"></span>
                                </button>
                                
                                <!-- Delete guest button if list is > 1 -->
                                <button x-show="guestList.length > 1" @click.stop="removeGuest(gName)"
                                        class="p-1 -mr-2 z-10 text-gray-600 hover:text-red-400 bg-dark-900 rounded-full border border-gray-800/50 flex items-center justify-center hover:scale-105 transition-all">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Add Guest Button -->
                        <button @click="addNewGuestPrompt()"
                                class="px-3.5 py-2 rounded-xl bg-dark-800/40 border border-dashed border-gray-700 text-gold-400 hover:border-gold-500 hover:bg-gold-500/5 transition-all text-xs font-bold flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            إضافة نزيل آخر
                        </button>
                    </div>
                    
                    <!-- Alert indicating current active guest -->
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-gold-400 font-semibold bg-gold-500/5 border border-gold-500/10 rounded-xl p-2">
                        <svg class="w-3.5 h-3.5 text-gold-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>الآن تختار طعاماً لـ: <span class="text-white" x-text="activeGuest"></span></span>
                    </div>
                </div>

                <!-- FILTER CHIPS & SCHEDULE INFO -->
                <div class="px-6 py-3 bg-dark-800/40 border-b border-gray-800/40 flex-shrink-0">
                    <!-- Schedule Filters -->
                    <div class="flex gap-2 p-1 bg-dark-900 rounded-xl border border-gray-800 mb-3">
                        <button @click="scheduleTab = 'all'" 
                                :class="scheduleTab === 'all' ? 'bg-gold-500 text-dark-900 font-bold' : 'text-gray-400'"
                                class="flex-1 py-1.5 text-center rounded-lg text-xs font-bold transition-all">
                            الكل (All)
                        </button>
                        <button @click="scheduleTab = 'Breakfast'" 
                                :class="scheduleTab === 'Breakfast' ? 'bg-gold-500 text-dark-900 font-bold' : 'text-gray-400'"
                                class="flex-1 py-1.5 text-center rounded-lg text-xs font-bold transition-all">
                            🍳 فطور (فوري)
                        </button>
                        <button @click="scheduleTab = 'Lunch/Dinner'" 
                                :class="scheduleTab === 'Lunch/Dinner' ? 'bg-gold-500 text-dark-900 font-bold' : 'text-gray-400'"
                                class="flex-1 py-1.5 text-center rounded-lg text-xs font-bold transition-all">
                            🍲 غداء وعشاء (مسبق)
                        </button>
                    </div>

                    <!-- Category Chips -->
                    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                        <button @click="selectedCategory = 'all'"
                                :class="selectedCategory === 'all' ? 'bg-white text-dark-900 font-bold border-white' : 'bg-dark-800/60 text-gray-400 border-gray-800/60 hover:text-white'"
                                class="px-4 py-2 border rounded-xl text-xs transition-all whitespace-nowrap">
                            🍽️ كل الأصناف
                        </button>
                        <template x-for="cat in categories" :key="cat">
                            <button @click="selectedCategory = cat"
                                    :class="selectedCategory === cat ? 'bg-white text-dark-900 font-bold border-white' : 'bg-dark-800/60 text-gray-400 border-gray-800/60 hover:text-white'"
                                    class="px-4 py-2 border rounded-xl text-xs transition-all whitespace-nowrap"
                                    x-text="cat"></button>
                        </template>
                    </div>
                </div>

                <!-- FOOD GRID (4x4 Grid - Responsive) -->
                <div class="flex-1 overflow-y-auto px-6 py-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                        <template x-for="item in filteredMenuItems" :key="item.id">
                            <div class="glass-card rounded-2xl overflow-hidden border border-gray-800/40 hover:border-gold-500/40 transition-all duration-300 flex flex-col group active:scale-[0.98] cursor-pointer"
                                 @click="openCustomizationModal(item)">
                                
                                <!-- Card Image -->
                                <div class="aspect-[4/3] w-full overflow-hidden bg-gray-800 relative">
                                    <img :src="item.image_url" :alt="item.name" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-all duration-500"
                                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=300&q=80'">
                                    
                                    <!-- Schedule Badge -->
                                    <div class="absolute top-2 right-2">
                                        <span :class="item.meal_type === 'Breakfast' ? 'bg-emerald-500/90 text-white' : 'bg-amber-600/90 text-white'"
                                              class="text-[9px] font-bold px-2 py-0.5 rounded-full"
                                              x-text="item.meal_type === 'Breakfast' ? 'فوري' : 'طلب مسبق'"></span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-3 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-extrabold text-white group-hover:text-gold-400 transition-colors line-clamp-1" x-text="item.name"></h3>
                                        <p class="text-[10px] text-gray-500 mt-1 line-clamp-2 leading-relaxed" x-text="item.description"></p>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-800/30">
                                        <span class="text-xs sm:text-sm font-extrabold text-gold-400 font-mono" x-text="formatPrice(item.price)"></span>
                                        <button class="w-6.5 h-6.5 rounded-lg bg-gold-500 text-dark-900 flex items-center justify-center font-bold hover:bg-gold-400 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty Filter State -->
                    <template x-if="filteredMenuItems.length === 0">
                        <div class="text-center py-16 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">لم نجد أي وجبة متوفرة في هذا التصنيف حالياً.</p>
                        </div>
                    </template>
                </div>

                <!-- FIXED FOOTER VIEW TICKET -->
                <div class="p-5 bg-dark-800/90 border-t border-gray-800/60 flex-shrink-0" x-show="cart.length > 0">
                    <button @click="showCartModal = true"
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-gold-400 to-gold-600 text-dark-900 font-extrabold text-sm shadow-xl shadow-gold-500/15 hover:shadow-gold-500/25 active:scale-95 transition-all flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-dark-900 text-white font-bold flex items-center justify-center text-xs" x-text="cart.length"></span>
                            <span>عرض سلة وتأكيد الطلب المجمع للغرفة</span>
                        </div>
                        <span class="font-mono font-black" x-text="formatPrice(cartTotal())"></span>
                    </button>
                </div>

            </div>
        </template>

        <!-- ================= CUSTOMIZATION POPUP MODAL ================= -->
        <div id="customModal" 
             x-show="showCustomModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-12"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-12"
             class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:p-4" 
             x-cloak>
            
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showCustomModal = false"></div>
            
            <div class="relative bg-dark-800 border-t border-gray-800/80 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
                
                <!-- Top Header banner -->
                <div class="relative h-40 bg-gray-900 overflow-hidden flex-shrink-0">
                    <img :src="activeItem?.image_url" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-dark-800 via-dark-800/40 to-transparent"></div>
                    
                    <button @click="showCustomModal = false" class="absolute top-4 right-4 w-9 h-9 bg-black/50 hover:bg-black/70 rounded-full flex items-center justify-center text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    
                    <div class="absolute bottom-4 right-4 left-4">
                        <span :class="activeItem?.meal_type === 'Breakfast' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'"
                              class="text-[9px] font-bold px-2 py-0.5 rounded-full border inline-block mb-1"
                              x-text="activeItem?.meal_type === 'Breakfast' ? 'فطور - فوري' : 'غداء وعشاء - مسبق'"></span>
                        <h3 class="text-base font-bold text-white leading-tight" x-text="activeItem?.name"></h3>
                        <p class="text-xs text-gold-400 font-extrabold mt-0.5 font-mono" x-text="formatPrice(activeItem?.price || 0)"></p>
                    </div>
                </div>
                
                <!-- Scrollable Body options -->
                <div class="p-5 space-y-4 overflow-y-auto flex-1 text-right">
                    
                    <!-- Alert active guest indicator -->
                    <div class="bg-gold-500/10 border border-gold-500/25 rounded-xl p-2.5 flex items-center justify-between text-xs text-gold-400 font-bold mb-1">
                        <span>إضافة هذا الطلب لـ:</span>
                        <span class="bg-gold-500 text-dark-900 px-2 py-0.5 rounded-lg" x-text="activeGuest"></span>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">الكمية المطلوبة</label>
                        <div class="flex items-center justify-center gap-6 py-1.5 bg-dark-900/50 rounded-xl border border-gray-800">
                            <button type="button" @click="activeQty = Math.max(1, activeQty - 1)"
                                    class="w-8 h-8 bg-dark-700 hover:bg-dark-600 border border-gray-700 text-white rounded-lg transition-all text-lg font-bold flex items-center justify-center">−</button>
                            <span class="text-2xl font-extrabold text-white w-10 text-center font-mono" x-text="activeQty"></span>
                            <button type="button" @click="activeQty++"
                                    class="w-8 h-8 bg-dark-700 hover:bg-dark-600 border border-gray-700 text-white rounded-lg transition-all text-lg font-bold flex items-center justify-center">+</button>
                        </div>
                    </div>

                    <!-- Temperature (Hot/Cold) Option -->
                    <template x-if="activeItem?.allow_temperature">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">تفضيل الحرارة</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="temp" value="ساخن" x-model="activeTemp" class="sr-only">
                                    <div :class="activeTemp === 'ساخن' ? 'border-gold-500 bg-gold-500/5 text-gold-400' : 'border-gray-800 bg-dark-900/40 text-gray-400'"
                                         class="py-2.5 px-4 rounded-xl border-2 text-center font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                                        🔥 ساخن (Chaud)
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="temp" value="بارد" x-model="activeTemp" class="sr-only">
                                    <div :class="activeTemp === 'بارد' ? 'border-gold-500 bg-gold-500/5 text-gold-400' : 'border-gray-800 bg-dark-900/40 text-gray-400'"
                                         class="py-2.5 px-4 rounded-xl border-2 text-center font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                                        ❄️ بارد (Froid)
                                    </div>
                                </label>
                            </div>
                        </div>
                    </template>

                    <!-- Customizations Checkboxes -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Personnaliser les ingrédients (Retrait/Ajout)</label>
                        <div class="space-y-3 max-h-60 overflow-y-auto p-0.5">
                            <template x-if="activeItem?.customizations?.length > 0">
                                <template x-for="cust in activeItem.customizations" :key="cust">
                                    <div class="p-3 bg-dark-900/40 rounded-xl border border-gray-800/60 hover:border-gold-500/20 transition-all space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer select-none">
                                            <input type="checkbox" :value="cust" x-model="activeCustomizations"
                                                   class="w-4.5 h-4.5 rounded border-gray-700 bg-dark-800 text-gold-500 focus:ring-gold-500/50">
                                            <span class="text-xs text-gray-300 font-medium" x-text="cust"></span>
                                        </label>
                                        
                                        <!-- Sub-input for detailed exclusion -->
                                        <div x-show="activeCustomizations.includes(cust)" 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="pl-2">
                                            <input type="text" x-model="activeCustomDetails[cust]" 
                                                   :placeholder="'Précisez ce que vous voulez retirer de: ' + cust"
                                                   class="w-full px-3 py-1.5 bg-dark-800 border border-gray-700 text-white rounded-lg text-xs outline-none focus:border-gold-500">
                                        </div>
                                    </div>
                                </template>
                            </template>

                            <!-- General custom exclusion input -->
                            <div class="p-3 bg-dark-900/40 rounded-xl border border-gray-800/60 hover:border-gold-500/20 transition-all space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="checkbox" x-model="hasOtherExclusion"
                                           class="w-4.5 h-4.5 rounded border-gray-700 bg-dark-800 text-gold-500 focus:ring-gold-500/50">
                                    <span class="text-xs text-gray-300 font-medium">Autre ingrédient à retirer (شيء آخر تريد إزالته)</span>
                                </label>
                                
                                <div x-show="hasOtherExclusion" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="pl-2">
                                    <input type="text" x-model="otherExclusionText" 
                                           placeholder="Précisez l'ingrédient à exclure..."
                                           class="w-full px-3 py-1.5 bg-dark-800 border border-gray-700 text-white rounded-lg text-xs outline-none focus:border-gold-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="p-5 bg-dark-800 border-t border-gray-800/80 flex gap-3 flex-shrink-0">
                    <button type="button" @click="showCustomModal = false"
                            class="flex-1 py-3 bg-dark-700 hover:bg-dark-600 text-gray-300 rounded-xl transition-all text-xs font-bold">إلغاء</button>
                    <button type="button" @click="addToCart()"
                            class="flex-1 py-3 bg-gradient-to-r from-gold-400 to-gold-600 hover:from-gold-300 hover:to-gold-500 text-dark-900 rounded-xl transition-all text-xs font-extrabold shadow-lg shadow-gold-500/10">أضف الطلب لـ <span x-text="activeGuest"></span></button>
                </div>
            </div>
        </div>

        <!-- ================= CART MODAL (Combined room order ticket) ================= -->
        <div id="cartModal" 
             x-show="showCartModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-12"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-12"
             class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:p-4" 
             x-cloak>
            
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showCartModal = false"></div>
            
            <div class="relative bg-dark-800 border-t border-gray-800/80 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[95vh] flex flex-col">
                
                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-800/50 flex items-center justify-between flex-shrink-0 bg-dark-800">
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        فاتورة الغرفة المجمعة
                    </h3>
                    <button @click="showCartModal = false" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Body (Scrollable items grouped by Guest) -->
                <div class="p-5 space-y-5 overflow-y-auto flex-1 text-right">
                    
                    <!-- Items grouped by guest -->
                    <template x-for="guestName in getGuestsInCart()" :key="guestName">
                        <div class="bg-dark-900/40 rounded-xl border border-gray-800 p-4 space-y-3">
                            <div class="flex items-center gap-2 border-b border-gray-800 pb-2 justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-gold-500"></span>
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider" x-text="'طلبات: ' + guestName"></h4>
                                </div>
                                <span class="text-[10px] text-gray-500" x-text="countGuestItems(guestName) + ' أصناف'"></span>
                            </div>
                            
                            <div class="space-y-3">
                                <template x-for="(cartItem, idx) in getCartItemsByGuest(guestName)" :key="idx">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-gray-200" x-text="cartItem.name"></p>
                                            <p class="text-[9px] text-gray-500 mt-0.5 flex gap-2">
                                                <span x-text="'الحرارة: ' + cartItem.temperature"></span>
                                                <template x-if="cartItem.customizations.length > 0">
                                                    <span x-text="'تعديل: ' + cartItem.customizations.join('، ')"></span>
                                                </template>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <!-- Qty selector inside cart -->
                                            <div class="flex items-center gap-1.5 bg-dark-800 rounded-lg p-0.5 border border-gray-800">
                                                <button type="button" @click="updateCartQty(cartItem.originalIndex, -1)" class="w-5 h-5 bg-dark-700 rounded text-gray-400 flex items-center justify-center font-bold text-xs">-</button>
                                                <span class="text-xs font-bold text-white w-4 text-center font-mono" x-text="cartItem.quantity"></span>
                                                <button type="button" @click="updateCartQty(cartItem.originalIndex, 1)" class="w-5 h-5 bg-dark-700 rounded text-gray-400 flex items-center justify-center font-bold text-xs">+</button>
                                            </div>
                                            <span class="text-xs font-bold text-gold-400 w-16 text-left font-mono" x-text="formatPrice(cartItem.price * cartItem.quantity)"></span>
                                            <button @click="removeFromCart(cartItem.originalIndex)" class="text-gray-600 hover:text-red-400 p-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Delivery Time Picker (Mandatory if Lunch/Dinner items in cart) -->
                    <div class="bg-dark-900/50 border border-gray-800 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-400">تحديد توقيت توصيل الطلب للغرفة</label>
                            <template x-if="cartHasLunchDinner()">
                                <span class="bg-red-500/15 text-red-400 text-[9px] px-2 py-0.5 rounded-full border border-red-500/25 font-bold">مجدول فقط (توصيل غداء/عشاء)</span>
                            </template>
                            <template x-if="!cartHasLunchDinner()">
                                <span class="bg-emerald-500/15 text-emerald-400 text-[9px] px-2 py-0.5 rounded-full border border-emerald-500/25 font-bold">فوري متاح</span>
                            </template>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3" x-show="!cartHasLunchDinner()">
                            <button type="button" @click="deliveryMode = 'asap'; deliveryTime = 'asap'"
                                    :class="deliveryMode === 'asap' ? 'bg-gold-500 text-dark-900 border-gold-500 font-bold' : 'bg-dark-900 border-gray-800 text-gray-400'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all">
                                ⚡️ الآن (فوري)
                            </button>
                            <button type="button" @click="deliveryMode = 'scheduled'; initDefaultTime()"
                                    :class="deliveryMode === 'scheduled' ? 'bg-gold-500 text-dark-900 border-gold-500 font-bold' : 'bg-dark-900 border-gray-800 text-gray-400'"
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all">
                                🕒 حجز موعد محدد
                            </button>
                        </div>
                        
                        <!-- Scheduled delivery time picker -->
                        <div x-show="deliveryMode === 'scheduled' || cartHasLunchDinner()" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="space-y-2 mt-2">
                            <p class="text-[10px] text-gray-500">اختر موعداً للتوصيل (توقيت محلي):</p>
                            <input type="time" x-model="deliveryTime" @change="validateDeliveryTime()"
                                   class="w-full px-4 py-3 bg-dark-900 border border-gray-800 text-white rounded-xl text-sm focus:ring-2 focus:ring-gold-500/30 focus:border-gold-500 outline-none">
                            
                            <template x-if="timeError">
                                <p class="text-[10px] text-red-400 font-semibold" x-text="timeError"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Total & Submit -->
                <div class="p-5 bg-dark-800 border-t border-gray-800/80 space-y-3 flex-shrink-0">
                    <div class="flex items-center justify-between text-sm font-extrabold">
                        <span class="text-gray-300">مجموع الطلبات المجمعة للغرفة</span>
                        <span class="text-lg text-gold-400 font-mono" x-text="formatPrice(cartTotal())"></span>
                    </div>
                    
                    <button type="button" @click="submitRoomOrder()"
                            :disabled="cart.length === 0 || timeError || isSubmitting"
                            class="w-full py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-gold-400 to-gold-600 text-dark-900 hover:from-gold-300 hover:to-gold-500 transition-all flex items-center justify-center gap-2 shadow-lg shadow-gold-500/10 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-5 w-5 text-dark-900" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'جاري إرسال الطلب...' : 'تأكيد وإرسال التيكت المجمع للمطبخ'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function roomServiceApp() {
            return {
                room: null,
                rooms: [],
                menuItems: [],
                categories: [],
                scheduleTab: 'all', // all, Breakfast, Lunch/Dinner
                selectedCategory: 'all', // all or specific category name (e.g. Tajines)
                
                // Guest List & Switching
                guestList: ['النزيل 1'],
                activeGuest: 'النزيل 1',
                
                cart: [],
                
                // Customization modal values
                showCustomModal: false,
                activeItem: null,
                activeQty: 1,
                activeTemp: 'ساخن',
                activeCustomizations: [],
                activeCustomDetails: {},
                hasOtherExclusion: false,
                otherExclusionText: '',

                // Cart modal values
                showCartModal: false,
                deliveryMode: 'asap', // asap or scheduled
                deliveryTime: 'asap',
                timeError: '',
                isSubmitting: false,

                initData() {
                    this.rooms = @json($rooms);
                    this.menuItems = @json($menuItems);
                    this.categories = @json($categories);
                    
                    const selectedRoom = @json($selectedRoom);
                    if (selectedRoom) {
                        this.room = selectedRoom;
                    }
                },

                selectRoom(r) {
                    this.room = r;
                },

                addNewGuestPrompt() {
                    const defaultName = `النزيل ${this.guestList.length + 1}`;
                    const name = prompt("الرجاء إدخال اسم أو معرّف الشخص الجديد:", defaultName);
                    if (name && name.trim()) {
                        const trimmed = name.trim();
                        if (!this.guestList.includes(trimmed)) {
                            this.guestList.push(trimmed);
                        }
                        this.activeGuest = trimmed;
                    }
                },

                removeGuest(gName) {
                    if (this.guestList.length <= 1) return;
                    if (confirm(`هل أنت متأكد من حذف النزيل "${gName}" وجميع طلباته؟`)) {
                        // Remove items from cart
                        this.cart = this.cart.filter(item => item.guest !== gName);
                        // Remove from list
                        this.guestList = this.guestList.filter(g => g !== gName);
                        // Reset active guest
                        if (this.activeGuest === gName) {
                            this.activeGuest = this.guestList[0];
                        }
                    }
                },

                countGuestItems(gName) {
                    return this.cart.filter(item => item.guest === gName).reduce((sum, item) => sum + item.quantity, 0);
                },

                get filteredMenuItems() {
                    return this.menuItems.filter(item => {
                        if (!item.available) return false;
                        
                        // 1. Meal schedule filter
                        if (this.scheduleTab !== 'all' && item.meal_type !== this.scheduleTab) {
                            return false;
                        }
                        
                        // 2. Category chip filter
                        if (this.selectedCategory !== 'all' && item.category !== this.selectedCategory) {
                            return false;
                        }
                        
                        return true;
                    });
                },

                formatPrice(price) {
                    return parseFloat(price).toFixed(2) + ' DH';
                },

                openCustomizationModal(item) {
                    this.activeItem = item;
                    this.activeQty = 1;
                    this.activeTemp = item.allow_temperature ? 'ساخن' : 'عادي';
                    this.activeCustomizations = [];
                    this.activeCustomDetails = {};
                    this.hasOtherExclusion = false;
                    this.otherExclusionText = '';
                    this.showCustomModal = true;
                },

                addToCart() {
                    const selectedCusts = this.activeCustomizations.map(c => {
                        const detail = this.activeCustomDetails[c];
                        return detail && detail.trim() ? `${c} (${detail.trim()})` : c;
                    });
                    
                    if (this.hasOtherExclusion && this.otherExclusionText.trim()) {
                        selectedCusts.push(`Sans ${this.otherExclusionText.trim()}`);
                    }

                    this.cart.push({
                        menu_item_id: this.activeItem.id,
                        name: this.activeItem.name,
                        price: this.activeItem.price,
                        meal_type: this.activeItem.meal_type,
                        quantity: this.activeQty,
                        temperature: this.activeItem.allow_temperature ? this.activeTemp : 'عادي',
                        customizations: selectedCusts,
                        guest: this.activeGuest
                    });

                    this.showCustomModal = false;
                    
                    if (this.cartHasLunchDinner()) {
                        this.deliveryMode = 'scheduled';
                        this.initDefaultTime();
                    }
                },

                cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                getGuestsInCart() {
                    return [...new Set(this.cart.map(item => item.guest))];
                },

                getCartItemsByGuest(guestName) {
                    return this.cart
                        .map((item, idx) => ({ ...item, originalIndex: idx }))
                        .filter(item => item.guest === guestName);
                },

                updateCartQty(index, delta) {
                    const newQty = this.cart[index].quantity + delta;
                    if (newQty <= 0) {
                        this.removeFromCart(index);
                    } else {
                        this.cart[index].quantity = newQty;
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    if (this.cart.length === 0) {
                        this.showCartModal = false;
                    } else if (!this.cartHasLunchDinner() && this.deliveryMode === 'scheduled' && this.deliveryTime === 'asap') {
                        this.deliveryMode = 'asap';
                        this.deliveryTime = 'asap';
                    }
                },

                cartHasLunchDinner() {
                    return this.cart.some(item => item.meal_type === 'Lunch/Dinner');
                },

                initDefaultTime() {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() + 90); // 1.5 hour min advance
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    this.deliveryTime = `${hours}:${minutes}`;
                    this.validateDeliveryTime();
                },

                validateDeliveryTime() {
                    if (this.deliveryMode === 'asap' && !this.cartHasLunchDinner()) {
                        this.timeError = '';
                        this.deliveryTime = 'asap';
                        return;
                    }

                    if (!this.deliveryTime || this.deliveryTime === 'asap') {
                        this.timeError = 'الرجاء اختيار وقت محدد.';
                        return;
                    }

                    const today = new Date();
                    const [h, m] = this.deliveryTime.split(':');
                    const deliveryDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), parseInt(h), parseInt(m));

                    if (deliveryDate < today) {
                        deliveryDate.setDate(deliveryDate.getDate() + 1);
                    }

                    const minRequiredDate = new Date(today.getTime() + 90 * 60 * 1000); // Current time + 1.5 Hours

                    if (deliveryDate < minRequiredDate) {
                        this.timeError = 'يجب أن يكون وقت التوصيل بعد ساعة ونصف على الأقل من الآن لطهي الوجبات.';
                    } else {
                        this.timeError = '';
                    }
                },

                submitRoomOrder() {
                    this.validateDeliveryTime();
                    if (this.timeError) return;

                    this.isSubmitting = true;

                    const payload = {
                        room_number: this.room.number,
                        delivery_time: this.deliveryTime,
                        items: this.cart,
                        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    };

                    fetch('{{ route("room-service.order.submit") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': payload._token
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isSubmitting = false;
                        if (data.success) {
                            window.location.href = `/room-service/order/success/${data.order_id}`;
                        } else {
                            alert(data.message || "فشل إرسال الطلب. يرجى المحاولة لاحقاً.");
                        }
                    })
                    .catch(error => {
                        this.isSubmitting = false;
                        alert("حدث خطأ في الشبكة. يرجى التحقق من الاتصال.");
                        console.error(error);
                    });
                }
            }
        }
    </script>
</body>
</html>
