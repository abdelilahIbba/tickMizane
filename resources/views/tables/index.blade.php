<x-layout.app title="Tables">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showModal: false, selectedTable: null }">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Tables</h1>
                <p class="text-gray-400 mt-1">Gestion des tables du restaurant</p>
            </div>
            <x-ui.button variant="primary" href="{{ route('tables.create') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle table
            </x-ui.button>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card 
                title="Total tables" 
                value="12"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>'
            />
            <x-ui.stat-card 
                title="Occupées" 
                value="5"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>'
            />
            <x-ui.stat-card 
                title="Libres" 
                value="7"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
            />
            <x-ui.stat-card 
                title="Taux d'occupation" 
                value="42%"
                color="purple"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'
            />
        </div>
        
        {{-- Tables Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach([
                ['id' => 1, 'number' => '01', 'seats' => 4, 'status' => 'occupied', 'server' => 'Youssef', 'amount' => 245.50],
                ['id' => 2, 'number' => '02', 'seats' => 2, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 3, 'number' => '03', 'seats' => 6, 'status' => 'occupied', 'server' => 'Fatima', 'amount' => 680.00],
                ['id' => 4, 'number' => '04', 'seats' => 4, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 5, 'number' => '05', 'seats' => 8, 'status' => 'occupied', 'server' => 'Ahmed', 'amount' => 1250.00],
                ['id' => 6, 'number' => '06', 'seats' => 2, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 7, 'number' => '07', 'seats' => 4, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 8, 'number' => '08', 'seats' => 4, 'status' => 'occupied', 'server' => 'Youssef', 'amount' => 156.00],
                ['id' => 9, 'number' => '09', 'seats' => 6, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 10, 'number' => '10', 'seats' => 2, 'status' => 'occupied', 'server' => 'Fatima', 'amount' => 89.00],
                ['id' => 11, 'number' => '11', 'seats' => 4, 'status' => 'free', 'server' => null, 'amount' => null],
                ['id' => 12, 'number' => '12', 'seats' => 6, 'status' => 'free', 'server' => null, 'amount' => null],
            ] as $table)
                <button 
                    @click="selectedTable = {{ json_encode($table) }}; showModal = true"
                    class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 {{ $table['status'] === 'occupied' ? 'bg-amber-500/20 border-2 border-amber-500/50' : 'bg-gray-800 border-2 border-gray-700 hover:border-gray-600' }}"
                >
                    <div class="text-3xl font-bold {{ $table['status'] === 'occupied' ? 'text-amber-400' : 'text-gray-400' }}">
                        {{ $table['number'] }}
                    </div>
                    <div class="text-sm {{ $table['status'] === 'occupied' ? 'text-amber-300' : 'text-gray-500' }} mt-1">
                        {{ $table['seats'] }} places
                    </div>
                    @if($table['status'] === 'occupied')
                        <div class="text-xs text-amber-200 mt-2 font-medium">
                            {{ number_format($table['amount'], 2) }} DH
                        </div>
                    @else
                        <div class="text-xs text-emerald-400 mt-2 font-medium">
                            Libre
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
        
        {{-- Table Action Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false" class="fixed inset-0 bg-black/70 backdrop-blur-sm"></div>
                
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative bg-gray-900 rounded-2xl shadow-xl border border-gray-700 w-full max-w-md p-6">
                    
                    <div class="text-center mb-6">
                        <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-4" :class="selectedTable?.status === 'occupied' ? 'bg-amber-500/20' : 'bg-emerald-500/20'">
                            <span class="text-3xl font-bold" :class="selectedTable?.status === 'occupied' ? 'text-amber-400' : 'text-emerald-400'" x-text="selectedTable?.number"></span>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Table <span x-text="selectedTable?.number"></span></h3>
                        <p class="text-gray-400"><span x-text="selectedTable?.seats"></span> places</p>
                    </div>
                    
                    <template x-if="selectedTable?.status === 'occupied'">
                        <div class="space-y-4">
                            <div class="bg-gray-800 rounded-xl p-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Serveur</span>
                                    <span class="text-white" x-text="selectedTable?.server"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Montant</span>
                                    <span class="text-amber-400 font-semibold" x-text="selectedTable?.amount?.toFixed(2) + ' DH'"></span>
                                </div>
                            </div>
                            
                            <a :href="'/tables/' + selectedTable?.id" class="block w-full py-3 bg-blue-500 text-white rounded-xl font-semibold text-center hover:bg-blue-400 transition-colors">
                                Voir les détails
                            </a>
                            <a :href="'/pos?table=' + selectedTable?.id" class="block w-full py-3 bg-amber-500 text-gray-900 rounded-xl font-semibold text-center hover:bg-amber-400 transition-colors">
                                Ajouter des articles
                            </a>
                            <button class="w-full py-3 bg-emerald-500 text-white rounded-xl font-semibold hover:bg-emerald-400 transition-colors">
                                Encaisser
                            </button>
                        </div>
                    </template>
                    
                    <template x-if="selectedTable?.status === 'free'">
                        <div class="space-y-4">
                            <a :href="'/pos?table=' + selectedTable?.id" class="block w-full py-3 bg-amber-500 text-gray-900 rounded-xl font-semibold text-center hover:bg-amber-400 transition-colors">
                                Ouvrir une commande
                            </a>
                            <a :href="'/tables/' + selectedTable?.id + '/edit'" class="block w-full py-3 bg-gray-700 text-white rounded-xl font-semibold text-center hover:bg-gray-600 transition-colors">
                                Modifier la table
                            </a>
                        </div>
                    </template>
                    
                    <button @click="showModal = false" class="w-full py-3 mt-4 text-gray-400 hover:text-white transition-colors">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout.app>
