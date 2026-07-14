<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>تم تأكيد طلبك - تتبع الطلب</title>
    
    <!-- Google Fonts: Tajawal & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', 'Outfit', 'sans-serif'],
                    },
                    colors: {
                        gold: {
                            400: '#dcab62',
                            500: '#cfa054',
                            600: '#b48641',
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
        }
        .glass-card {
            background: rgba(21, 30, 48, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="h-full text-gray-200" x-data="orderTrackerApp()" x-init="startTracking()">

    <div class="min-h-full flex flex-col justify-between max-w-md mx-auto relative shadow-2xl bg-dark-900 border-x border-gray-800/40">
        
        <div class="flex-1 flex flex-col overflow-y-auto px-5 py-6 space-y-6">
            
            <!-- Success Animation Header -->
            <div class="text-center space-y-2 mt-4">
                <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 rounded-full flex items-center justify-center mx-auto text-emerald-400">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-white">تم إرسال طلبك بنجاح!</h1>
                <p class="text-xs text-gray-500">رقم التيكت: <span class="font-mono text-sm font-semibold text-gray-300" x-text="order.id"></span></p>
                <p class="text-xs text-gold-400 font-semibold">غرفة رقم <span class="font-mono text-sm" x-text="order.room_number"></span></p>
            </div>

            <!-- LIVE STATUS STEPPER CARD -->
            <div class="glass-card rounded-3xl p-5 border border-gold-500/15 space-y-5">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-800 pb-2">حالة الطلب المباشرة (Live Status)</h2>
                
                <!-- Vertical Stepper -->
                <div class="relative pl-2 pr-6 space-y-6">
                    <!-- Vertical Line Connector -->
                    <div class="absolute right-[21px] top-3 bottom-3 w-0.5 bg-gray-800">
                        <div class="w-full bg-gold-500 transition-all duration-500" :style="getLineStyle()"></div>
                    </div>

                    <!-- Step 1: Pending -->
                    <div class="flex gap-4 items-start relative">
                        <div class="absolute right-[-10px] w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                             :class="isStepActive(1) ? 'bg-gold-500 text-dark-900 shadow-lg shadow-gold-500/30' : 'bg-dark-700 text-gray-500 border border-gray-800'">
                            <span class="text-[10px] font-bold">1</span>
                        </div>
                        <div class="mr-4">
                            <h3 class="text-xs font-bold transition-all" :class="isStepActive(1) ? 'text-white' : 'text-gray-500'">في انتظار موافقة الكاشير</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">تم إرسال الطلب، وننتظر مراجعته وقبوله من المحاسب.</p>
                        </div>
                    </div>

                    <!-- Step 2: Approved -->
                    <div class="flex gap-4 items-start relative">
                        <div class="absolute right-[-10px] w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                             :class="isStepActive(2) ? 'bg-gold-500 text-dark-900 shadow-lg shadow-gold-500/30' : 'bg-dark-700 text-gray-500 border border-gray-800'">
                            <span class="text-[10px] font-bold">2</span>
                        </div>
                        <div class="mr-4">
                            <h3 class="text-xs font-bold transition-all" :class="isStepActive(2) ? 'text-white' : 'text-gray-500'">تمت الموافقة من الكاشير</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">تم قبول الطلب وتسجيله بالمحاسبة بنجاح.</p>
                        </div>
                    </div>

                    <!-- Step 3: Preparing -->
                    <div class="flex gap-4 items-start relative">
                        <div class="absolute right-[-10px] w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                             :class="isStepActive(3) ? 'bg-gold-500 text-dark-900 shadow-lg shadow-gold-500/30' : 'bg-dark-700 text-gray-500 border border-gray-800'">
                            <span class="text-[10px] font-bold">3</span>
                        </div>
                        <div class="mr-4">
                            <h3 class="text-xs font-bold transition-all" :class="isStepActive(3) ? 'text-white' : 'text-gray-500'">جاري الطهي في المطبخ</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">يقوم الطهاة الآن بتحضير وجبتك بعناية تامة.</p>
                        </div>
                    </div>

                    <!-- Step 4: Delivering -->
                    <div class="flex gap-4 items-start relative">
                        <div class="absolute right-[-10px] w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                             :class="isStepActive(4) ? 'bg-gold-500 text-dark-900 shadow-lg shadow-gold-500/30' : 'bg-dark-700 text-gray-500 border border-gray-800'">
                            <span class="text-[10px] font-bold">4</span>
                        </div>
                        <div class="mr-4">
                            <h3 class="text-xs font-bold transition-all" :class="isStepActive(4) ? 'text-white' : 'text-gray-500'">جاهز وفي الطريق لغرفتك</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">خرج الطلب من المطبخ وهو في طريقه للتوصيل الآن.</p>
                        </div>
                    </div>

                    <!-- Step 5: Delivered -->
                    <div class="flex gap-4 items-start relative">
                        <div class="absolute right-[-10px] w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300"
                             :class="isStepActive(5) ? 'bg-emerald-500 text-dark-900 shadow-lg shadow-emerald-500/30' : 'bg-dark-700 text-gray-500 border border-gray-800'">
                            <span class="text-[10px] font-bold">✓</span>
                        </div>
                        <div class="mr-4">
                            <h3 class="text-xs font-bold transition-all" :class="isStepActive(5) ? 'text-emerald-400 font-extrabold' : 'text-gray-500'">تم التوصيل بنجاح</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">بالصحة والعافية! تم تسليم الطلب لغرفتك بالكامل.</p>
                        </div>
                    </div>
                </div>

                <!-- Pulse animation loading indicator -->
                <div class="pt-4 border-t border-gray-800/60 flex items-center justify-center gap-2 text-[10px] text-gray-500" x-show="status !== 'delivered'">
                    <span class="flex h-2 w-2 relative">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-gold-500"></span>
                    </span>
                    <span>يقوم النظام بتحديث حالة الطلب تلقائياً...</span>
                </div>
            </div>

            <!-- ORDER RECEIPT SUMMARY -->
            <div class="bg-dark-800/50 border border-gray-800 rounded-3xl p-5 space-y-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-800 pb-2">تفاصيل الفاتورة المجمّعة</h3>
                
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">توقيت الطلب:</span>
                        <span class="font-mono text-gray-300" x-text="order.created_at"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">توقيت التوصيل المطلوب:</span>
                        <span class="font-mono font-bold text-gold-400" x-text="order.delivery_time === 'asap' ? 'في أقرب وقت (ASAP)' : order.delivery_time"></span>
                    </div>
                </div>
                
                <div class="border-t border-dashed border-gray-800 my-3"></div>
                
                <!-- Items list -->
                <div class="space-y-3">
                    <template x-for="item in order.items" :key="item.menu_item_id">
                        <div class="flex justify-between items-start text-xs">
                            <div class="min-w-0 flex-1">
                                <p class="text-white font-semibold">
                                    <span x-text="item.name"></span>
                                    <span class="font-mono text-gray-500 text-[10px]" x-text="'x' + item.quantity"></span>
                                </p>
                                <p class="text-[9px] text-gray-500 mt-0.5">
                                    <span x-text="'طلب لـ: ' + item.guest"></span>
                                    <span class="mx-1">|</span>
                                    <span x-text="'الحرارة: ' + item.temperature"></span>
                                    <template x-if="item.customizations?.length > 0">
                                        <span>
                                            <span class="mx-1">|</span>
                                            <span x-text="'تعديل: ' + item.customizations.join(', ')"></span>
                                        </span>
                                    </template>
                                </p>
                            </div>
                            <span class="font-mono font-bold text-gray-300 w-20 text-left" x-text="formatPrice(item.price * item.quantity)"></span>
                        </div>
                    </template>
                </div>
                
                <div class="border-t border-dashed border-gray-800 my-3"></div>
                
                <div class="flex items-center justify-between text-sm font-extrabold">
                    <span class="text-gray-300">المجموع الكلي</span>
                    <span class="text-gold-400 font-mono text-base" x-text="formatPrice(order.total)"></span>
                </div>
            </div>
            
        </div>

        <!-- Back to menu button at bottom -->
        <div class="p-4 bg-dark-800/90 border-t border-gray-800/60 flex-shrink-0">
            <a href="{{ route('room-service.menu') }}?room={{ $order['room_number'] }}&token=tok{{ $order['room_number'] }}"
               class="w-full py-3.5 bg-dark-700 hover:bg-dark-600 border border-gray-800 text-gray-300 rounded-2xl transition-all text-xs font-bold text-center block">
                طلب وجبة أخرى للغرفة
            </a>
        </div>
        
    </div>

    <script>
        function orderTrackerApp() {
            return {
                order: @json($order),
                status: 'pending',

                startTracking() {
                    this.status = this.order.status;
                    
                    // Poll status every 4 seconds
                    setInterval(() => {
                        this.pollStatus();
                    }, 4000);
                },

                pollStatus() {
                    if (this.status === 'delivered') return;

                    fetch(`/room-service/order/status/${this.order.id}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status) {
                                this.status = data.status;
                            }
                        })
                        .catch(err => console.error("Error polling status:", err));
                },

                formatPrice(price) {
                    return parseFloat(price).toFixed(2) + ' DH';
                },

                // Maps status to step index: pending=1, approved=2, preparing=3, ready=4, delivered=5
                getStatusStep() {
                    switch (this.status) {
                        case 'pending': return 1;
                        case 'approved': return 2;
                        case 'preparing': return 3;
                        case 'ready': return 4;
                        case 'delivered': return 5;
                        default: return 1;
                    }
                },

                isStepActive(stepNum) {
                    return this.getStatusStep() >= stepNum;
                },

                getLineStyle() {
                    const step = this.getStatusStep();
                    // Percentage of vertical line to fill
                    if (step === 1) return 'height: 0%';
                    if (step === 2) return 'height: 25%';
                    if (step === 3) return 'height: 50%';
                    if (step === 4) return 'height: 75%';
                    if (step === 5) return 'height: 100%';
                    return 'height: 0%';
                }
            }
        }
    </script>
</body>
</html>
