<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Techmizane Cash' }}</title>
    
    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            750: '#2d3748',
                            850: '#1a202c',
                            950: '#0d1117',
                        }
                    }
                }
            }
        }
    </script>
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Custom Styles --}}
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1f2937;
        }
        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Touch-friendly tap highlight */
        * {
            -webkit-tap-highlight-color: rgba(251, 191, 36, 0.2);
        }
        
        /* Smooth transitions */
        .transition-page {
            transition: opacity 0.2s ease-in-out;
        }
    </style>
    
    @stack('styles')
</head>
<body class="h-full bg-gray-950 text-gray-100 antialiased overflow-hidden">
    <div class="h-full flex flex-col">
        {{-- Include Navbar --}}
        <x-layout.navbar />
        
        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
        
        {{-- Footer --}}
        <footer class="bg-gray-900 border-t border-gray-800 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    © {{ date('Y') }} Techmizane Cash. Tous droits réservés.
                </p>
            </div>
        </footer>
    </div>
    
    {{-- Global Notifications --}}
    <div 
        x-data="{ notifications: [] }"
        x-on:notify.window="notifications.push($event.detail); setTimeout(() => notifications.shift(), 5000)"
        class="fixed bottom-4 right-4 z-50 space-y-2"
    >
        <template x-for="(notification, index) in notifications" :key="index">
            <div 
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                :class="{
                    'bg-emerald-500/20 border-emerald-500/50 text-emerald-400': notification.type === 'success',
                    'bg-red-500/20 border-red-500/50 text-red-400': notification.type === 'error',
                    'bg-amber-500/20 border-amber-500/50 text-amber-400': notification.type === 'warning',
                    'bg-blue-500/20 border-blue-500/50 text-blue-400': notification.type === 'info'
                }"
                class="px-6 py-4 rounded-xl border shadow-lg backdrop-blur-sm"
            >
                <p x-text="notification.message"></p>
            </div>
        </template>
    </div>
    
    @stack('scripts')
</body>
</html>
