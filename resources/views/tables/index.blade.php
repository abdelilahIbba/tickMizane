<x-layout.app title="Tables">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
         x-data="tableManager()" 
         x-init="init()">
        
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Tables</h1>
                <p class="text-gray-400 mt-1">Gestion des tables du restaurant</p>
            </div>
            <div class="flex gap-3">
                <button @click="refreshTables()" 
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-xl hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </button>
                <x-ui.button variant="primary" href="{{ route('tables.create') }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle table
                </x-ui.button>
            </div>
        </div>
        
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total tables</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Occupées</p>
                        <p class="text-3xl font-bold text-amber-400 mt-1">{{ $stats['occupied'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Libres</p>
                        <p class="text-3xl font-bold text-emerald-400 mt-1">{{ $stats['free'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Taux d'occupation</p>
                        <p class="text-3xl font-bold text-purple-400 mt-1">{{ $stats['occupancy_rate'] }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="bg-gray-800 rounded-2xl p-4 mb-6 border border-gray-700">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm">Statut:</span>
                    <div class="flex gap-2">
                        <a href="{{ route('tables.index') }}" 
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ !request('status') ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            Toutes
                        </a>
                        <a href="{{ route('tables.index', ['status' => 'free']) }}" 
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'free' ? 'bg-emerald-500 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            Libres
                        </a>
                        <a href="{{ route('tables.index', ['status' => 'occupied']) }}" 
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'occupied' ? 'bg-amber-500 text-gray-900' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                            Occupées
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm">Zone:</span>
                    <select onchange="window.location.href='{{ route('tables.index') }}?zone=' + this.value + '{{ request('status') ? '&status=' . request('status') : '' }}'"
                            class="bg-gray-700 border-0 rounded-lg px-3 py-1.5 text-sm text-white">
                        <option value="">Toutes</option>
                        @foreach($zones as $key => $label)
                            <option value="{{ $key }}" {{ request('zone') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        {{-- Tables Grid --}}
        @if($tables->isEmpty())
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">Aucune table</h3>
                <p class="text-gray-400 mb-6">Commencez par créer des tables pour votre établissement.</p>
                <x-ui.button variant="primary" href="{{ route('tables.create') }}">
                    Créer une table
                </x-ui.button>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($tables as $table)
                    <button 
                        @click="openTableModal({{ json_encode([
                            'id' => $table->id,
                            'name' => $table->name,
                            'places' => $table->places,
                            'zone' => $table->zone,
                            'zone_display' => $table->getZoneDisplayName(),
                            'status' => $table->status,
                            'serveur' => $table->serveur?->name,
                            'serveur_id' => $table->serveur_id,
                            'amount' => $table->getCurrentBillAmount(),
                            'occupied_time' => $table->getOccupiedTimeFormatted(),
                            'current_vente_id' => $table->current_vente_id,
                        ]) }})"
                        class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 touch-manipulation
                               {{ $table->isOccupied() 
                                   ? 'bg-gradient-to-br from-amber-500/20 to-amber-600/10 border-2 border-amber-500/50 shadow-lg shadow-amber-500/10' 
                                   : 'bg-gray-800 border-2 border-gray-700 hover:border-gray-600' }}"
                    >
                        {{-- Table Number --}}
                        <div class="text-3xl font-bold {{ $table->isOccupied() ? 'text-amber-400' : 'text-gray-400' }}">
                            {{ $table->name }}
                        </div>
                        
                        {{-- Places --}}
                        <div class="text-sm {{ $table->isOccupied() ? 'text-amber-300' : 'text-gray-500' }} mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $table->places }}
                        </div>
                        
                        {{-- Status / Amount --}}
                        @if($table->isOccupied())
                            <div class="text-sm text-amber-200 mt-2 font-semibold">
                                {{ number_format($table->getCurrentBillAmount(), 2) }} DH
                            </div>
                            @if($table->serveur)
                                <div class="text-xs text-amber-300/70 mt-1">
                                    {{ $table->serveur->name }}
                                </div>
                            @endif
                        @else
                            <div class="text-xs text-emerald-400 mt-2 font-medium flex items-center gap-1">
                                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                                Libre
                            </div>
                        @endif
                        
                        {{-- Zone Badge --}}
                        @if($table->zone)
                            <div class="absolute top-2 right-2 text-xs px-2 py-0.5 rounded-full bg-gray-700/80 text-gray-400">
                                {{ $table->getZoneDisplayName() }}
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
        
        {{-- Table Action Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Backdrop --}}
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     @click="showModal = false" 
                     class="fixed inset-0 bg-black/70 backdrop-blur-sm"></div>
                
                {{-- Modal Content --}}
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95" 
                     class="relative bg-gray-900 rounded-2xl shadow-xl border border-gray-700 w-full max-w-md p-6">
                    
                    {{-- Header --}}
                    <div class="text-center mb-6">
                        <div class="mx-auto w-20 h-20 rounded-2xl flex items-center justify-center mb-4" 
                             :class="selectedTable?.status === 'occupied' ? 'bg-gradient-to-br from-amber-500/30 to-amber-600/20' : 'bg-gradient-to-br from-emerald-500/30 to-emerald-600/20'">
                            <span class="text-4xl font-bold" 
                                  :class="selectedTable?.status === 'occupied' ? 'text-amber-400' : 'text-emerald-400'" 
                                  x-text="selectedTable?.name"></span>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Table <span x-text="selectedTable?.name"></span></h3>
                        <div class="flex items-center justify-center gap-3 mt-2">
                            <span class="text-gray-400 text-sm" x-text="selectedTable?.places + ' places'"></span>
                            <span class="text-gray-600">•</span>
                            <span class="text-gray-400 text-sm" x-text="selectedTable?.zone_display"></span>
                        </div>
                    </div>
                    
                    {{-- Occupied Table Content --}}
                    <template x-if="selectedTable?.status === 'occupied'">
                        <div class="space-y-4">
                            {{-- Info Card --}}
                            <div class="bg-gray-800 rounded-xl p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">Serveur</span>
                                    <span class="text-white font-medium" x-text="selectedTable?.serveur || 'Non assigné'"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">Durée</span>
                                    <span class="text-white" x-text="selectedTable?.occupied_time || '-'"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-700">
                                    <span class="text-gray-400">Montant</span>
                                    <span class="text-amber-400 font-bold text-xl" x-text="(selectedTable?.amount || 0).toFixed(2) + ' DH'"></span>
                                </div>
                            </div>
                            
                            {{-- Actions --}}
                            <div class="space-y-3">
                                <a :href="'/tables/' + selectedTable?.id" 
                                   class="flex items-center justify-center gap-2 w-full py-3.5 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Voir les détails
                                </a>
                                
                                <a :href="'/pos?table=' + selectedTable?.id" 
                                   class="flex items-center justify-center gap-2 w-full py-3.5 bg-amber-500 text-gray-900 rounded-xl font-semibold hover:bg-amber-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Ajouter des articles
                                </a>
                                
                                <button @click="releaseTable()" 
                                        :disabled="selectedTable?.amount > 0"
                                        class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl font-semibold transition-colors"
                                        :class="selectedTable?.amount > 0 ? 'bg-gray-700/50 text-gray-500 cursor-not-allowed' : 'bg-emerald-500 text-white hover:bg-emerald-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="selectedTable?.amount > 0 ? 'Encaisser d\'abord' : 'Libérer la table'"></span>
                                </button>
                            </div>
                            
                            {{-- Transfer --}}
                            <div class="pt-4 border-t border-gray-700">
                                <button @click="showTransferOptions = !showTransferOptions" 
                                        class="flex items-center justify-center gap-2 w-full py-2 text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    Transférer vers une autre table
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    {{-- Free Table Content --}}
                    <template x-if="selectedTable?.status === 'free'">
                        <div class="space-y-4">
                            <a :href="'/pos?table=' + selectedTable?.id" 
                               class="flex items-center justify-center gap-2 w-full py-3.5 bg-amber-500 text-gray-900 rounded-xl font-semibold hover:bg-amber-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Ouvrir une commande
                            </a>
                            
                            <button @click="occupyTable()" 
                                    class="flex items-center justify-center gap-2 w-full py-3.5 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                </svg>
                                Marquer comme occupée
                            </button>
                            
                            <a :href="'/tables/' + selectedTable?.id + '/edit'" 
                               class="flex items-center justify-center gap-2 w-full py-3.5 bg-gray-800 text-gray-300 rounded-xl font-semibold hover:bg-gray-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier la table
                            </a>
                        </div>
                    </template>
                    
                    {{-- Close Button --}}
                    <button @click="showModal = false" 
                            class="w-full py-3 mt-4 text-gray-400 hover:text-white transition-colors">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function tableManager() {
            return {
                showModal: false,
                showTransferOptions: false,
                selectedTable: null,
                isRefreshing: false,
                
                init() {
                    // Auto-refresh every 30 seconds
                    setInterval(() => this.refreshTables(), 30000);
                },
                
                openTableModal(table) {
                    this.selectedTable = table;
                    this.showModal = true;
                    this.showTransferOptions = false;
                },
                
                async refreshTables() {
                    this.isRefreshing = true;
                    try {
                        window.location.reload();
                    } finally {
                        this.isRefreshing = false;
                    }
                },
                
                async occupyTable() {
                    if (!this.selectedTable) return;
                    
                    try {
                        const response = await fetch(`/tables/${this.selectedTable.id}/occupy`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erreur lors de l\'occupation de la table');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Erreur lors de l\'occupation de la table');
                    }
                },
                
                async releaseTable() {
                    if (!this.selectedTable) return;
                    
                    if (this.selectedTable.amount > 0) {
                        alert('Veuillez d\'abord encaisser la commande en cours.');
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/tables/${this.selectedTable.id}/release`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erreur lors de la libération de la table');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Erreur lors de la libération de la table');
                    }
                },
            }
        }
    </script>
    @endpush
</x-layout.app>
